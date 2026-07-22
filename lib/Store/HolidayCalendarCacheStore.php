<?php

declare(strict_types=1);

namespace OCA\AdUrlaub\Store;

use OCP\AppFramework\Services\IAppConfig;

/** Zweck: Speichert kleine normalisierte Jahreskalender lazy in der Nextcloud-AppConfig-Datenbank. */
final class HolidayCalendarCacheStore {
    public function __construct(private IAppConfig $config) {}

    public function get(int $year): ?array {
        $payload = $this->config->getAppValueArray($this->key($year), [], lazy: true);
        return ($payload['year'] ?? null) === $year && is_array($payload['schoolHolidays'] ?? null) && is_array($payload['publicHolidays'] ?? null)
            ? $payload
            : null;
    }

    public function save(int $year, array $payload): void {
        $this->config->setAppValueArray($this->key($year), $payload, lazy: true, sensitive: false);
    }

    private function key(int $year): string {
        return 'holiday_calendar_' . $year;
    }
}
