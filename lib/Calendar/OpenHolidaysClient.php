<?php

declare(strict_types=1);

namespace OCA\AdUrlaub\Calendar;

use DateTimeImmutable;
use OCP\Http\Client\IClientService;
use RuntimeException;

/** Zweck: Liest und normalisiert Berliner Schulferien und gesetzliche Feiertage aus der fest vorgegebenen OpenHolidays-API. */
final class OpenHolidaysClient {
    public const SOURCE_NAME = 'OpenHolidays API';
    public const SOURCE_URL = 'https://www.openholidaysapi.org/';
    public const SOURCE_LICENSE = 'ODbL';
    private const API_URL = 'https://openholidaysapi.org';
    private const MAX_RESPONSE_BYTES = 1_000_000;

    public function __construct(private IClientService $clients) {}

    /** @return array{schoolHolidays: list<array{name: string, startDate: string, endDate: string}>, publicHolidays: list<array{name: string, startDate: string, endDate: string}>} */
    public function fetchYear(int $year): array {
        if ($year < 2000 || $year > 2100) throw new RuntimeException('Ungültiges Kalenderjahr.');
        $query = [
            'countryIsoCode' => 'DE',
            'subdivisionCode' => 'DE-BE',
            'languageIsoCode' => 'DE',
            'validFrom' => sprintf('%04d-01-01', $year),
            'validTo' => sprintf('%04d-12-31', $year),
        ];

        return [
            'schoolHolidays' => $this->request('SchoolHolidays', 'School', $query),
            'publicHolidays' => $this->request('PublicHolidays', 'Public', $query),
        ];
    }

    /** @return list<array{name: string, startDate: string, endDate: string}> */
    private function request(string $endpoint, string $expectedType, array $query): array {
        $url = self::API_URL . '/' . $endpoint . '?' . http_build_query($query, '', '&', PHP_QUERY_RFC3986);
        try {
            $response = $this->clients->newClient()->get($url, [
                'headers' => ['Accept' => 'application/json'],
                'timeout' => 10,
            ]);
        } catch (\Throwable $error) {
            throw new RuntimeException('OpenHolidays API ist nicht erreichbar.', 0, $error);
        }
        if ($response->getStatusCode() !== 200) throw new RuntimeException('OpenHolidays API hat unerwartet geantwortet.');
        $body = $response->getBody();
        if (is_resource($body)) $body = stream_get_contents($body) ?: '';
        if (!is_string($body) || strlen($body) > self::MAX_RESPONSE_BYTES) throw new RuntimeException('OpenHolidays API lieferte eine ungültige Antwortgröße.');
        try {
            $decoded = json_decode($body, true, 128, JSON_THROW_ON_ERROR);
        } catch (\JsonException $error) {
            throw new RuntimeException('OpenHolidays API lieferte ungültiges JSON.', 0, $error);
        }
        if (!is_array($decoded) || !array_is_list($decoded)) throw new RuntimeException('OpenHolidays API lieferte ein ungültiges Datenformat.');

        $holidays = [];
        foreach ($decoded as $item) {
            if (!is_array($item) || ($item['type'] ?? null) !== $expectedType) throw new RuntimeException('OpenHolidays API lieferte einen unerwarteten Kalendertyp.');
            $startDate = $this->date($item['startDate'] ?? null);
            $endDate = $this->date($item['endDate'] ?? null);
            if ($endDate < $startDate) throw new RuntimeException('OpenHolidays API lieferte einen ungültigen Datumsbereich.');
            $name = $this->germanName($item['name'] ?? null);
            $holidays[] = compact('name', 'startDate', 'endDate');
        }
        usort($holidays, static fn(array $left, array $right): int => [$left['startDate'], $left['endDate'], $left['name']] <=> [$right['startDate'], $right['endDate'], $right['name']]);
        return $holidays;
    }

    private function date(mixed $value): string {
        if (!is_string($value) || preg_match('/^\d{4}-\d{2}-\d{2}$/', $value) !== 1) throw new RuntimeException('OpenHolidays API lieferte ein ungültiges Datum.');
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        if ($date === false || $date->format('Y-m-d') !== $value) throw new RuntimeException('OpenHolidays API lieferte ein ungültiges Datum.');
        return $value;
    }

    private function germanName(mixed $names): string {
        if (!is_array($names)) throw new RuntimeException('OpenHolidays API lieferte keinen Namen.');
        foreach ($names as $name) {
            if (!is_array($name) || strtoupper((string)($name['language'] ?? '')) !== 'DE') continue;
            $text = trim((string)($name['text'] ?? ''));
            if ($text !== '' && strlen($text) <= 320) return $text;
        }
        throw new RuntimeException('OpenHolidays API lieferte keinen deutschen Namen.');
    }
}
