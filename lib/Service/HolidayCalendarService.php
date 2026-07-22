<?php

declare(strict_types=1);

namespace OCA\AdUrlaub\Service;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;
use OCA\AdUrlaub\Calendar\OpenHolidaysClient;
use OCA\AdUrlaub\Store\HolidayCalendarCacheStore;
use OCP\AppFramework\Utility\ITimeFactory;
use Psr\Log\LoggerInterface;

/** Zweck: Liefert dynamische Berliner Kalenderdaten mit täglichem DB-Cache und ausfallsicherem Altbestand. */
final class HolidayCalendarService {
    private const CACHE_TTL_SECONDS = 24 * 3600;
    private const FAILURE_RETRY_SECONDS = 15 * 60;

    public function __construct(
        private OpenHolidaysClient $provider,
        private HolidayCalendarCacheStore $cache,
        private ITimeFactory $time,
        private LoggerInterface $logger,
    ) {}

    public function forYear(int $year, bool $forceRefresh = false): array {
        if ($year < 2000 || $year > 2100) throw new InvalidArgumentException('Ungültiges Kalenderjahr.');
        $cached = $this->cache->get($year);
        if (!$forceRefresh && $cached !== null && $this->isCurrent($cached)) return $cached + ['cacheStatus' => 'current'];
        if (!$forceRefresh && $cached !== null && !$this->retryDue($cached)) {
            return $cached + ['cacheStatus' => is_string($cached['fetchedAt'] ?? null) ? 'stale' : 'unavailable'];
        }

        try {
            $remote = $this->provider->fetchYear($year);
            $now = $this->dateTime($this->time->getTime());
            $payload = [
                'year' => $year,
                'fetchedAt' => $now,
                'refreshAttemptedAt' => $now,
                'source' => [
                    'name' => OpenHolidaysClient::SOURCE_NAME,
                    'url' => OpenHolidaysClient::SOURCE_URL,
                    'license' => OpenHolidaysClient::SOURCE_LICENSE,
                ],
                'schoolHolidays' => $remote['schoolHolidays'],
                'publicHolidays' => $remote['publicHolidays'],
            ];
            $this->cache->save($year, $payload);
            return $payload + ['cacheStatus' => 'fresh'];
        } catch (\Throwable $error) {
            $this->logger->warning('Berliner Ferien- und Feiertagsdaten konnten nicht aktualisiert werden.', [
                'year' => $year,
                'exception' => $error,
            ]);
            $fallback = $cached ?? [
                'year' => $year,
                'fetchedAt' => null,
                'source' => [
                    'name' => OpenHolidaysClient::SOURCE_NAME,
                    'url' => OpenHolidaysClient::SOURCE_URL,
                    'license' => OpenHolidaysClient::SOURCE_LICENSE,
                ],
                'schoolHolidays' => [],
                'publicHolidays' => [],
            ];
            $fallback['refreshAttemptedAt'] = $this->dateTime($this->time->getTime());
            $this->cache->save($year, $fallback);
            return $fallback + ['cacheStatus' => $cached !== null ? 'stale' : 'unavailable'];
        }
    }

    private function isCurrent(array $payload): bool {
        $fetchedAt = is_string($payload['fetchedAt'] ?? null) ? strtotime($payload['fetchedAt']) : false;
        return $fetchedAt !== false && $fetchedAt >= $this->time->getTime() - self::CACHE_TTL_SECONDS;
    }

    private function retryDue(array $payload): bool {
        $attemptedAt = is_string($payload['refreshAttemptedAt'] ?? null) ? strtotime($payload['refreshAttemptedAt']) : false;
        return $attemptedAt === false || $attemptedAt < $this->time->getTime() - self::FAILURE_RETRY_SECONDS;
    }

    private function dateTime(int $timestamp): string {
        return (new DateTimeImmutable('@' . $timestamp))->setTimezone(new DateTimeZone('UTC'))->format(DATE_ATOM);
    }
}
