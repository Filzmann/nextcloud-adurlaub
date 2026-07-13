<?php

declare(strict_types=1);

namespace OCA\AdUrlaub\Service;

use DateTimeImmutable;
use InvalidArgumentException;
use DateTimeZone;
use OCA\AdUrlaub\Exception\VacationConflictException;
use OCA\AdUrlaub\Model\Vacation;
use OCA\AdUrlaub\Repository\VacationRepository;
use OCA\LocalBase\Calendar\ScheduleConflictQueryEvent;
use OCP\EventDispatcher\IEventDispatcher;

/** Zweck: Orchestriert Wochenabfragen und validierte Urlaubspersistenz. */
final class VacationService {
    public function __construct(private VacationRepository $vacations, private IEventDispatcher $events) {}
    public function week(DateTimeImmutable $start, array $employees): array { $end = $start->modify('+6 days'); return ['start' => $start->format('Y-m-d'), 'end' => $end->format('Y-m-d'), 'employees' => $employees, 'vacations' => array_map(static fn(Vacation $vacation): array => $vacation->toArray(), $this->vacations->findRange($start->format('Y-m-d'), $end->format('Y-m-d'), array_column($employees, 'uid')))]; }
    public function existing(int $id): Vacation { return $this->vacations->find($id) ?? throw new InvalidArgumentException('Urlaub nicht gefunden.'); }
    public function save(array $payload, ?int $id, string $actorUid): int { if ($id !== null) $payload['id'] = $id; $vacation = Vacation::get($payload); if ($vacation->status() === Vacation::STATUS_APPROVED) $this->assertNoScheduleConflicts($vacation); return $this->vacations->save($vacation, $actorUid); }
    public function delete(int $id): void { $this->vacations->delete($id); }
    private function assertNoScheduleConflicts(Vacation $vacation): void { $utc = new DateTimeZone('UTC'); $start = new DateTimeImmutable($vacation->startDate() . ' 00:00:00',$utc); $end = (new DateTimeImmutable($vacation->endDate() . ' 00:00:00',$utc))->modify('+1 day'); $event = new ScheduleConflictQueryEvent($vacation->employeeUid(),$start,$end); $this->events->dispatchTyped($event); $conflicts = array_map(static fn($item): array => $item->toArray(),$event->conflicts()); if ($conflicts !== []) throw new VacationConflictException($conflicts); }
}
