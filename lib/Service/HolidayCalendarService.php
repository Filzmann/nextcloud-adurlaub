<?php

declare(strict_types=1);

namespace OCA\AdUrlaub\Service;

use OCA\LocalBase\Calendar\HolidayCalendarService as SharedHolidayCalendarService;

/** Dünner Consumeradapter: AD Urlaub behält sein API-Array und liest den gemeinsamen LocalBase-Kalendervertrag. */
final class HolidayCalendarService {
    public function __construct(private SharedHolidayCalendarService $shared) {}

    public function forYear(int $year, bool $forceRefresh = false): array {
        return $this->shared->forYear($year, $forceRefresh)->toArray();
    }
}
