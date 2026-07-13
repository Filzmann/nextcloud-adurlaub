<?php

declare(strict_types=1);

$controller = file_get_contents(__DIR__ . '/../lib/Controller/ApiController.php');
$access = file_get_contents(__DIR__ . '/../lib/Service/VacationAccessService.php');
$listener = file_get_contents(__DIR__ . '/../lib/Listener/AbsenceQueryListener.php');
foreach (["canManageStatus(\$payload['employeeUid']", 'canManageStatus($existing->employeeUid()', 'canManageStatus($vacation->employeeUid()'] as $contract) if (!str_contains($controller, $contract)) throw new RuntimeException("Serverseitige Rechteprüfung fehlt: {$contract}");
foreach (['isAdmin', 'enabledPeerGroups()', 'false);'] as $contract) if (!str_contains($access, $contract)) throw new RuntimeException("Access-Vertrag fehlt: {$contract}");
if (!str_contains($listener, 'AbsenceQueryEvent') || !str_contains($listener, 'AbsenceInterval')) throw new RuntimeException('Read-only Integrationsvertrag fehlt.');
echo "SecuritySmokeTest: OK\n";
