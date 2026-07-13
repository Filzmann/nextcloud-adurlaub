<?php

declare(strict_types=1);

namespace OCA\AdUrlaub\Listener;

use DateTimeImmutable;
use DateTimeZone;
use OCA\AdUrlaub\Model\Vacation;
use OCA\AdUrlaub\Repository\VacationRepository;
use OCA\LocalBase\Calendar\AbsenceInterval;
use OCA\LocalBase\Calendar\AbsenceQueryEvent;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;

/** @template-implements IEventListener<AbsenceQueryEvent> */
final class AbsenceQueryListener implements IEventListener {
    public function __construct(private VacationRepository $vacations) {}
    public function handle(Event $event): void {
        if (!$event instanceof AbsenceQueryEvent) return;
        $timezone = new DateTimeZone('UTC');
        $startDate = $event->start()->setTimezone($timezone)->format('Y-m-d');
        $endDate = $event->end()->modify('-1 second')->setTimezone($timezone)->format('Y-m-d');
        foreach ($this->vacations->findRange($startDate, $endDate, $event->employeeUids()) as $vacation) {
            $event->add(new AbsenceInterval($vacation->employeeUid(), new DateTimeImmutable($vacation->startDate() . ' 00:00:00', $timezone), (new DateTimeImmutable($vacation->endDate() . ' 00:00:00', $timezone))->modify('+1 day'), $vacation->status() === Vacation::STATUS_APPROVED ? AbsenceInterval::STATUS_APPROVED : AbsenceInterval::STATUS_PLANNED));
        }
    }
}
