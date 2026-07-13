<?php

declare(strict_types=1);

namespace OCA\AdUrlaub\Service;

use OCA\AdUrlaub\Model\VacationTeam;
use OCA\LocalBase\Organization\AdOrganizationHierarchy;
use OCP\IGroupManager;

/**
 * Zweck: Fuehrt ASN-Gruppen und organisatorische Fachgruppen in einem Urlaubsteam-Katalog zusammen.
 * Vertrag: Teamzuordnung filtert nur die Ansicht; Schreibrechte bleiben im VacationAccessService.
 */
final class VacationTeamService {
    public function __construct(private IGroupManager $groups, private VacationAccessService $access) {}

    /** @return list<VacationTeam> */
    public function all(): array {
        $employees = $this->access->visibleEmployees();
        $teams = $this->asnTeams($employees);
        foreach ($this->organizationDefinitions() as $definition) {
            $members = array_values(array_filter($employees, fn(array $employee): bool => $this->matchesOrganizationTeam($employee, $definition)));
            if ($members !== []) $teams[] = VacationTeam::get($definition + ['employees' => $members]);
        }
        usort($teams, static function (VacationTeam $a, VacationTeam $b): int {
            $category = ($a->toArray()['category'] <=> $b->toArray()['category']);
            return $category !== 0 ? $category : strnatcasecmp($a->displayName(), $b->displayName());
        });
        return $teams;
    }

    public function get(string $id): ?VacationTeam {
        foreach ($this->all() as $team) if ($team->id() === $id) return $team;
        return null;
    }

    private function asnTeams(array $employees): array {
        $employeeMap = array_column($employees, null, 'uid');
        $result = [];
        foreach ($this->groups->search('ad-ASN-') as $group) {
            $groupId = (string)$group->getGID();
            if (!preg_match('/^ad-ASN-(.+)$/u', $groupId, $matches) || str_ends_with($groupId, '-Urlaub')) continue;
            $code = $matches[1];
            $vacationGroup = $this->groups->get($groupId . '-Urlaub') ?? $group;
            $members = [];
            foreach ($vacationGroup->getUsers() as $user) if (isset($employeeMap[$user->getUID()])) $members[] = $employeeMap[$user->getUID()];
            if ($members === []) continue;
            usort($members, static fn(array $a, array $b): int => strnatcasecmp($a['displayName'], $b['displayName']));
            $result[] = VacationTeam::get(['id' => 'asn-' . $code, 'code' => $code, 'displayName' => 'ASN ' . $code, 'category' => 'asn', 'employees' => $members]);
        }
        return $result;
    }

    private function organizationDefinitions(): array {
        return [
            ['id' => 'office-now', 'code' => 'office-now', 'displayName' => 'Büro NOW', 'category' => 'organization', 'areas' => ['ad-Bereich-Nordost','ad-Bereich-West'], 'roles' => [AdOrganizationHierarchy::ROLE_OFFICE,AdOrganizationHierarchy::BL,AdOrganizationHierarchy::DEPUT_BL]],
            ['id' => 'office-south', 'code' => 'office-south', 'displayName' => 'Büro Süd', 'category' => 'organization', 'areas' => ['ad-Bereich-Sued'], 'roles' => [AdOrganizationHierarchy::ROLE_OFFICE,AdOrganizationHierarchy::BL,AdOrganizationHierarchy::DEPUT_BL]],
            ['id' => 'eb', 'code' => 'eb', 'displayName' => 'Einsatzbegleitungen', 'category' => 'organization', 'areas' => [], 'roles' => [AdOrganizationHierarchy::ROLE_EB]],
            ['id' => 'pfk', 'code' => 'pfk', 'displayName' => 'Pflegefachkräfte', 'category' => 'organization', 'areas' => [], 'roles' => [AdOrganizationHierarchy::ROLE_PFK]],
            ['id' => 'staff', 'code' => 'staff', 'displayName' => 'Stab', 'category' => 'organization', 'areas' => [], 'roles' => [AdOrganizationHierarchy::GF_AS,AdOrganizationHierarchy::GF_DIGI,AdOrganizationHierarchy::ASSISTANT_GF_DIGI,AdOrganizationHierarchy::FINANCE_LEAD,AdOrganizationHierarchy::FINANCE,AdOrganizationHierarchy::IT,AdOrganizationHierarchy::SECRETARIAT,AdOrganizationHierarchy::PDL,AdOrganizationHierarchy::ROLE_STAFF_HR,AdOrganizationHierarchy::ROLE_STAFF_QMB]],
        ];
    }

    private function matchesOrganizationTeam(array $employee, array $definition): bool {
        if (array_intersect($employee['roles'], $definition['roles']) === []) return false;
        return $definition['areas'] === [] || array_intersect($employee['areas'], $definition['areas']) !== [];
    }
}
