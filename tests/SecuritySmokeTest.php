<?php

declare(strict_types=1);

$controller = file_get_contents(__DIR__ . '/../lib/Controller/ApiController.php');
$access = file_get_contents(__DIR__ . '/../lib/Service/VacationAccessService.php');
$listener = file_get_contents(__DIR__ . '/../lib/Listener/AbsenceQueryListener.php');
$page = file_get_contents(__DIR__ . '/../lib/Controller/PageController.php');
foreach (["canManageStatus(\$payload['employeeUid']", 'canManageStatus($existing->employeeUid()', 'canManageStatus($vacation->employeeUid()'] as $contract) if (!str_contains($controller, $contract)) throw new RuntimeException("Serverseitige Rechteprüfung fehlt: {$contract}");
foreach (['isAdmin', 'enabledPeerGroups()', 'false);', 'VacationVisibilityPolicy', 'visibility->canView'] as $contract) if (!str_contains($access, $contract)) throw new RuntimeException("Access-Vertrag fehlt: {$contract}");
if (!str_contains($listener, 'AbsenceQueryEvent') || !str_contains($listener, 'AbsenceInterval')) throw new RuntimeException('Read-only Integrationsvertrag fehlt.');
if (!preg_match('/#\[NoCSRFRequired\]\s+#\[NoAdminRequired\]\s+public function index/s', $page)) throw new RuntimeException('Lesende App-Seite besitzt keinen expliziten Nextcloud-CSRF-Vertrag.');
if (preg_match('/#\[NoCSRFRequired\][\s\S]{0,80}public function (create|update|delete|setDayStatus)/', $controller)) throw new RuntimeException('Schreibender Urlaubs-Endpunkt umgeht CSRF-Schutz.');
foreach (['$team->contains($employeeUid)', 'canManageStatus($employeeUid,$existing->status())', 'canManageStatus($employeeUid,$status)'] as $contract) if (!str_contains($controller, $contract)) throw new RuntimeException("Team- oder Statusrecht fehlt: {$contract}");
if (!str_contains($controller, "isVisibleEmployee(\$payload['employeeUid'])")) throw new RuntimeException('Schreibzugriff ist nicht auf sichtbare AD-Personen begrenzt.');
if (!str_contains($controller, 'isVisibleEmployee($vacation->employeeUid())')) throw new RuntimeException('Löschzugriff ist nicht auf sichtbare AD-Personen begrenzt.');
if (!str_contains($controller, 'isVisibleEmployee($existing->employeeUid())') || !str_contains($controller, 'isVisibleEmployee($employeeUid)')) throw new RuntimeException('Änderungszugriff ist nicht auf sichtbare AD-Personen begrenzt.');
echo "SecuritySmokeTest: OK\n";
