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
    /** @return list<array{uid:string,displayName:string,roles:list<string>,areas:list<string>,canManage:bool}> */
    public function visibleEmployees(): array {
        $users = [];
        foreach (self::ROLE_GROUPS as $role) foreach ($this->groups->get($role)?->getUsers() ?? [] as $user) $users[$user->getUID()] = $user;
        $result = [];
        foreach ($users as $user) { $ids = array_map('strval', $this->groups->getUserGroupIds($user)); $result[] = ['uid' => $user->getUID(), 'displayName' => $user->getDisplayName(), 'roles' => array_values(array_intersect(self::ROLE_GROUPS, $ids)), 'areas' => array_values(array_filter($ids, static fn(string $id): bool => str_starts_with($id, 'ad-Bereich-'))), 'canManage' => $this->canManage($user->getUID()), 'canApprove' => $this->canApprove($user->getUID())]; }
        usort($result, static fn(array $a, array $b): int => strnatcasecmp($a['displayName'], $b['displayName']));
        return $result;
    }

    private function decide(string $employeeUid, bool $allowSelf): bool {
        $actor = $this->currentUser(); $target = $this->users->get($employeeUid);
        if ($actor === null || $target === null) return false;
        return $this->policy->canManage($actor->getUID(), $this->groups->isAdmin($actor->getUID()), $this->groupIds($actor), $employeeUid, $this->groupIds($target), $this->settings->enabledPeerGroups(), $allowSelf);
    }
    private function groupIds(IUser $user): array { return array_map('strval', $this->groups->getUserGroupIds($user)); }
}
