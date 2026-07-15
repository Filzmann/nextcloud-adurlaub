<?php

declare(strict_types=1);

namespace OCA\AdUrlaub\Service;

use DateTimeImmutable;
use OCA\AdUrlaub\Model\Vacation;
use OCA\AdUrlaub\Repository\VacationRepository;
use OCA\LocalBase\Service\AdDemoFixtureCatalog;
use OCA\LocalBase\Service\DemoAccountProvisioningService;

/**
 * Zweck: Installiert synthetische Urlaubsfälle für jede konfigurierte AD-Fachgruppe.
 * Vertrag: Es werden ausschließlich registrierte Suite-Demokonten verwendet; reale Gruppenmitglieder werden nie ausgewählt.
 */
final class VacationDemoPackService {
    public function __construct(
        private DemoAccountProvisioningService $accounts,
        private AdDemoFixtureCatalog $fixtures,
        private VacationRepository $vacations,
    ) {}

    /** @return array{accounts:array,coveredGroups:int,createdVacations:int,skippedVacations:int} */
    public function install(): array {
        $fixtures = $this->fixtures->all();
        $accounts = $this->accounts->provision('ad-suite-demo', $fixtures);
        $monday = new DateTimeImmutable('monday next week');
        $coveredGroups = count(array_unique(array_merge(...array_column($fixtures, 'groups'))));
        $createdVacations = 0;
        $skippedVacations = 0;
        foreach ($fixtures as $index => $fixture) {
            if ($this->vacations->existsForEmployee($fixture['uid'])) {
                $skippedVacations++;
                continue;
            }
            $day = $monday->modify('+' . ($index % 5) . ' days')->format('Y-m-d');
            $status = $index % 2 === 0 ? Vacation::STATUS_PLANNED : Vacation::STATUS_APPROVED;
            $this->vacations->save(Vacation::get([
                'employeeUid' => $fixture['uid'],
                'startDate' => $day,
                'endDate' => $day,
                'status' => $status,
                'note' => 'Neutraler Demo-Urlaub',
            ]), 'demo-seed');
            $createdVacations++;
        }
        return compact('accounts', 'coveredGroups', 'createdVacations', 'skippedVacations');
    }
}
