<?php

declare(strict_types=1);

namespace OCP\Http\Client {
    interface IResponse { public function getBody(); public function getStatusCode(): int; }
    interface IClient { public function get(string $url, array $options = []): IResponse; }
    interface IClientService { public function newClient(): IClient; }
}

namespace OCP\AppFramework\Services {
    interface IAppConfig {
        public function getAppValueArray(string $key, array $default = [], bool $lazy = false): array;
        public function setAppValueArray(string $key, array $value, bool $lazy = false, bool $sensitive = false): bool;
    }
}

namespace OCP\AppFramework\Utility {
    interface ITimeFactory { public function getTime(): int; }
}

namespace Psr\Log {
    interface LoggerInterface { public function warning(string $message, array $context = []): void; }
}

namespace {
    require_once __DIR__ . '/../lib/Calendar/OpenHolidaysClient.php';
    require_once __DIR__ . '/../lib/Store/HolidayCalendarCacheStore.php';
    require_once __DIR__ . '/../lib/Service/HolidayCalendarService.php';

    use OCA\AdUrlaub\Calendar\OpenHolidaysClient;
    use OCA\AdUrlaub\Service\HolidayCalendarService;
    use OCA\AdUrlaub\Store\HolidayCalendarCacheStore;
    use OCP\AppFramework\Services\IAppConfig;
    use OCP\AppFramework\Utility\ITimeFactory;
    use OCP\Http\Client\IClient;
    use OCP\Http\Client\IClientService;
    use OCP\Http\Client\IResponse;
    use Psr\Log\LoggerInterface;

    final class HolidayResponse implements IResponse {
        public function __construct(private array $body, private int $status = 200) {}
        public function getBody(): string { return json_encode($this->body, JSON_THROW_ON_ERROR); }
        public function getStatusCode(): int { return $this->status; }
    }

    final class HolidayHttpClient implements IClient {
        public array $urls = [];
        public bool $fail = false;
        public bool $invalid = false;
        public function get(string $url, array $options = []): IResponse {
            $this->urls[] = [$url, $options];
            if ($this->fail) throw new RuntimeException('synthetischer Netzfehler');
            $isSchool = str_contains($url, '/SchoolHolidays?');
            return new HolidayResponse($isSchool ? [[
                'id' => 'school-1',
                'startDate' => $this->invalid ? '02.02.2026' : '2026-02-02',
                'endDate' => '2026-02-07',
                'type' => 'School',
                'name' => [['language' => 'DE', 'text' => 'Winterferien']],
            ]] : [[
                'id' => 'public-1',
                'startDate' => '2026-03-08',
                'endDate' => '2026-03-08',
                'type' => 'Public',
                'name' => [['language' => 'DE', 'text' => 'Internationaler Frauentag']],
            ]]);
        }
    }

    final class HolidayAppConfig implements IAppConfig {
        public array $values = [];
        public array $writes = [];
        public function getAppValueArray(string $key, array $default = [], bool $lazy = false): array {
            if ($lazy !== true) throw new RuntimeException('Feriencache muss lazy gelesen werden.');
            return $this->values[$key] ?? $default;
        }
        public function setAppValueArray(string $key, array $value, bool $lazy = false, bool $sensitive = false): bool {
            if ($lazy !== true || $sensitive !== false) throw new RuntimeException('Feriencache hat falsche AppConfig-Flags.');
            $this->values[$key] = $value;
            $this->writes[] = $key;
            return true;
        }
    }

    $http = new HolidayHttpClient();
    $clients = new class($http) implements IClientService {
        public function __construct(private IClient $client) {}
        public function newClient(): IClient { return $this->client; }
    };
    $now = strtotime('2026-07-22T12:00:00Z');
    $time = new class($now) implements ITimeFactory {
        public function __construct(public int $now) {}
        public function getTime(): int { return $this->now; }
    };
    $logger = new class implements LoggerInterface {
        public array $warnings = [];
        public function warning(string $message, array $context = []): void { $this->warnings[] = [$message, $context]; }
    };
    $config = new HolidayAppConfig();
    $service = new HolidayCalendarService(
        new OpenHolidaysClient($clients),
        new HolidayCalendarCacheStore($config),
        $time,
        $logger,
    );

    $fresh = $service->forYear(2026);
    if (($fresh['cacheStatus'] ?? '') !== 'fresh') {
        $reason = $logger->warnings[0][1]['exception']->getMessage() ?? 'unbekannt';
        throw new RuntimeException('Ein Erstabruf wird nicht als frisch markiert: ' . $reason);
    }
    if (($fresh['schoolHolidays'][0]['name'] ?? '') !== 'Winterferien') throw new RuntimeException('Schulferien werden nicht normalisiert.');
    if (($fresh['publicHolidays'][0]['name'] ?? '') !== 'Internationaler Frauentag') throw new RuntimeException('Feiertage werden nicht normalisiert.');
    if (($fresh['source']['name'] ?? '') !== 'OpenHolidays API') throw new RuntimeException('Quellenangabe fehlt.');
    if (count($http->urls) !== 2) throw new RuntimeException('Die beiden OpenHolidays-Endpunkte werden nicht genau einmal geladen.');
    if ($config->writes !== ['holiday_calendar_2026']) throw new RuntimeException('Jahresdaten werden nicht im DB-AppConfig-Cache gespeichert.');
    foreach ($http->urls as [$url, $options]) {
        if (!str_contains($url, 'countryIsoCode=DE')
            || !str_contains($url, 'subdivisionCode=DE-BE')
            || !str_contains($url, 'languageIsoCode=DE')
            || ($options['timeout'] ?? null) !== 10
            || ($options['headers']['Accept'] ?? '') !== 'application/json') {
            throw new RuntimeException('OpenHolidays wird nicht fest auf Berlin, Deutsch und sichere Zeitlimits begrenzt.');
        }
    }

    $cached = $service->forYear(2026);
    if (($cached['cacheStatus'] ?? '') !== 'current' || count($http->urls) !== 2) {
        throw new RuntimeException('Ein frischer DB-Cache löst unnötig eine externe Anfrage aus.');
    }

    $time->now += 25 * 3600;
    $http->fail = true;
    $stale = $service->forYear(2026);
    if (($stale['cacheStatus'] ?? '') !== 'stale'
        || ($stale['schoolHolidays'][0]['name'] ?? '') !== 'Winterferien'
        || $logger->warnings === []) {
        throw new RuntimeException('Bei Dienstausfall bleibt der letzte gültige Cache nicht sicher verfügbar.');
    }
    $requestsAfterFailure = count($http->urls);
    $staleWithoutRetry = $service->forYear(2026);
    if (($staleWithoutRetry['cacheStatus'] ?? '') !== 'stale' || count($http->urls) !== $requestsAfterFailure) {
        throw new RuntimeException('Ein Dienstausfall löst innerhalb der Rückoffzeit unnötige Folgeanfragen aus.');
    }

    $unavailable = $service->forYear(2027);
    if (($unavailable['cacheStatus'] ?? '') !== 'unavailable'
        || ($unavailable['schoolHolidays'] ?? null) !== []
        || ($unavailable['publicHolidays'] ?? null) !== []) {
        throw new RuntimeException('Ein Erstabruf-Fehler wird nicht als leerer, transparenter Ausfall behandelt.');
    }

    $http->fail = false;
    $http->invalid = true;
    try {
        (new OpenHolidaysClient($clients))->fetchYear(2028);
        throw new RuntimeException('Ungültige externe Datumswerte werden akzeptiert.');
    } catch (RuntimeException $error) {
        if (!str_contains($error->getMessage(), 'ungültiges Datum')) throw $error;
    }

    echo "HolidayCalendarServiceTest: OK\n";
}
