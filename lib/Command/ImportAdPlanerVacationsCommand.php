<?php

declare(strict_types=1);

namespace OCA\AdUrlaub\Command;

use OCA\AdUrlaub\Repository\VacationRepository;
use OCP\IDBConnection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Zweck: Uebernimmt alte AdPlaner-Urlaube idempotent in die gemeinsame Urlaubsquelle.
 * Vertrag: Die Quelltabelle bleibt als Rueckbaureserve unveraendert.
 */
final class ImportAdPlanerVacationsCommand extends Command {
    public function __construct(private IDBConnection $db, private VacationRepository $vacations) { parent::__construct(); }
    protected function configure(): void { $this->setName('adurlaub:import-adplaner')->setDescription('Importiert bisherige AdPlaner-Urlaube idempotent.'); }
    protected function execute(InputInterface $input, OutputInterface $output): int {
        try { $qb = $this->db->getQueryBuilder(); $rows = $qb->select('id','assistant_uid','date_from','date_to','status','note','updated_by_uid')->from('adp_vacation_requests')->orderBy('id','ASC')->executeQuery()->fetchAllAssociative(); }
        catch (\Throwable) { $output->writeln('<comment>Keine AdPlaner-Urlaubstabelle gefunden.</comment>'); return self::SUCCESS; }
        $created = 0; foreach ($rows as $row) if ($this->vacations->importLegacy($row)) $created++;
        $output->writeln("<info>{$created} von " . count($rows) . ' AdPlaner-Urlauben importiert.</info>'); return self::SUCCESS;
    }
}
