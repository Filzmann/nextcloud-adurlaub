<?php

declare(strict_types=1);

namespace OCA\AdUrlaub\Command;

use DateTimeImmutable;
use OCA\AdUrlaub\Model\Vacation;
use OCA\AdUrlaub\Repository\VacationRepository;
use OCA\LocalBase\Organization\AdOrganizationDefinition;
use OCA\LocalBase\Organization\AdOrganizationSettingsService;
use OCP\IGroupManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/** Zweck: Erzeugt idempotent je kanonischer Fachgruppe mindestens einen neutralen Demo-Urlaub. */
final class SeedDemoCommand extends Command {
    public function __construct(private IGroupManager $groups, private VacationRepository $vacations, private ?AdOrganizationSettingsService $organization = null) { parent::__construct(); }
    protected function configure(): void { $this->setName('adurlaub:demo:seed')->setDescription('Erzeugt Urlaubsdemos für alle AD-Fachgruppen.'); }
    protected function execute(InputInterface $input, OutputInterface $output): int {
        $monday = new DateTimeImmutable('monday next week'); $created = 0; $covered = 0;
        $definition = $this->organization?->definition() ?? AdOrganizationDefinition::defaults();
        foreach ($definition->roleGroupIds() as $index => $groupId) {
            $user = array_values($this->groups->get($groupId)?->getUsers() ?? [])[0] ?? null;
            if ($user === null) continue; $covered++;
            if ($this->vacations->existsForEmployee($user->getUID())) continue;
            $day = $monday->modify('+' . ($index % 5) . ' days')->format('Y-m-d');
            $status = $index % 2 === 0 ? Vacation::STATUS_PLANNED : Vacation::STATUS_APPROVED;
            $this->vacations->save(Vacation::get(['employeeUid' => $user->getUID(), 'startDate' => $day, 'endDate' => $day, 'status' => $status, 'note' => 'Neutraler Demo-Urlaub']), 'demo-seed'); $created++;
        }
        $output->writeln("<info>{$covered} Fachgruppen abgedeckt; {$created} Demo-Urlaube erzeugt.</info>"); return self::SUCCESS;
    }
}
