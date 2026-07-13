<?php

declare(strict_types=1);

namespace OCA\AdUrlaub\Service;

use OCA\LocalBase\Organization\AdOrganizationPermissionPolicy;
use OCP\IGroupManager;
use OCP\IUser;
use OCP\IUserManager;
use OCP\IUserSession;

/** Zweck: Erzwingt Sichtbarkeit aller AD-Gruppen und minimale eigene/Admin-Schreibrechte. */
final class VacationAccessService {
    public const ROLE_GROUPS = ['ad-EB','ad-PFK','ad-Buero','ad-Stab-HR','ad-Stab-QMB','ad-GF-AS','ad-GF-Digi','ad-AsdGF-Digi','ad-Leitung-Finanzen-Lohn','ad-Finanzen-Lohn','ad-IT','ad-Sekretariat','ad-PDL','ad-BL','ad-StvBL'];
    public function __construct(private IGroupManager $groups, private IUserSession $session, private IUserManager $users, private AdOrganizationPermissionPolicy $policy, private VacationSettingsService $settings) {}
    public function currentUser(): ?IUser { return $this->session->getUser(); }
    public function canView(): bool { return $this->currentUser() !== null; }
    public function canManage(string $employeeUid): bool { return $this->decide($employeeUid, true); }
    public function canApprove(string $employeeUid): bool { return $this->decide($employeeUid, false); }
    public function canManageStatus(string $employeeUid, string $status): bool { return $status === 'approved' ? $this->canApprove($employeeUid) : $this->canManage($employeeUid); }
    public function isVisibleEmployee(string $employeeUid): bool { return in_array($employeeUid, array_column($this->visibleEmployees(), 'uid'), true); }
    /** @return list<array{uid:string,displayName:string,roles:list<string>,areas:list<string>,canManage:bool}> */
    public function visibleEmployees(): array {
        $users = [];
        foreach (self::ROLE_GROUPS as $role) foreach ($this->groups->get($role)?->getUsers() ?? [] as $user) $users[$user->getUID()] = $user;
        foreach ($this->groups->search('ad-ASN-') as $group) {
            if (!preg_match('/^ad-ASN-.+/u', (string)$group->getGID())) continue;
            foreach ($group->getUsers() as $user) $users[$user->getUID()] = $user;
        }
        $result = [];
        foreach ($users as $user) { $ids = array_map('strval', $this->groups->getUserGroupIds($user)); $result[] = ['uid' => $user->getUID(), 'displayName' => $user->getDisplayName(), 'roles' => array_values(array_intersect(self::ROLE_GROUPS, $ids)), 'areas' => array_values(array_filter($ids, static fn(string $id): bool => str_starts_with($id, 'ad-Bereich-'))), 'canManage' => $this->canManage($user->getUID()), 'canApprove' => $this->canApprove($user->getUID())]; }
        usort($result, static fn(array $a, array $b): int => strnatcasecmp($a['displayName'], $b['displayName']));
        return $result;
    }

    private function decide(string $employeeUid, bool $allowSelf): bool {
        $actor = $this->currentUser(); $target = $this->users->get($employeeUid);
        if ($actor === null || $target === null) return false;
        $actorGroups = $this->groupIds($actor); $targetGroups = $this->groupIds($target);
        if ($this->policy->canManage($actor->getUID(), $this->groups->isAdmin($actor->getUID()), $actorGroups, $employeeUid, $targetGroups, $this->settings->enabledPeerGroups(), $allowSelf)) return true;
        if ($actor->getUID() === $employeeUid) return false;
        $sharedAsnTeams = array_intersect($this->asnTeamCodes($actorGroups), $this->asnTeamCodes($targetGroups));
        if ($sharedAsnTeams === []) return false;
        if (in_array('ad-EB', $actorGroups, true)) return true;
        return in_array(VacationSettingsService::ASN_PEER_GROUP, $this->settings->enabledPeerGroups(), true);
    }
    private function groupIds(IUser $user): array { return array_map('strval', $this->groups->getUserGroupIds($user)); }
    private function asnTeamCodes(array $groupIds): array {
        $codes = [];
        foreach ($groupIds as $groupId) if (str_starts_with($groupId, 'ad-ASN-')) $codes[] = preg_replace('/-Urlaub$/u', '', substr($groupId, 7));
        return array_values(array_unique($codes));
    }
}
