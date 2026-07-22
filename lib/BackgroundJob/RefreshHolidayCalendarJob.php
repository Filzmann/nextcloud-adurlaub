<?php

declare(strict_types=1);

namespace OCA\AdUrlaub\BackgroundJob;

use OCA\AdUrlaub\Service\HolidayCalendarService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\IJob;
use OCP\BackgroundJob\TimedJob;
use Override;

/** Zweck: Aktualisiert einmal täglich das aktuelle und die zwei folgenden Berliner Kalenderjahre. */
final class RefreshHolidayCalendarJob extends TimedJob {
    public function __construct(private ITimeFactory $clock, private HolidayCalendarService $holidays) {
        parent::__construct($clock);
        $this->setInterval(24 * 3600);
        $this->setTimeSensitivity(IJob::TIME_INSENSITIVE);
        $this->setAllowParallelRuns(false);
    }

    #[Override]
    protected function run($argument): void {
        $year = (int)gmdate('Y', $this->clock->getTime());
        for ($offset = 0; $offset <= 2; $offset++) $this->holidays->forYear($year + $offset, true);
    }
}
