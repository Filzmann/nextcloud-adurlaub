<?php

declare(strict_types=1);

namespace OCA\AdUrlaub\Service;

use OCA\AdUrlaub\AppInfo\Application;
use OCA\LocalBase\Organization\AdOrganizationHierarchy;
use OCP\IAppConfig;

/** Zweck: Speichert administrativ freigeschaltete direkte Genehmigung unter Kolleg*innen. */
final class VacationSettingsService {
    public const ASN_PEER_GROUP = 'ad-ASN-*';
    public const PEER_GROUPS = [self::ASN_PEER_GROUP,AdOrganizationHierarchy::ROLE_OFFICE,AdOrganizationHierarchy::ROLE_PFK,AdOrganizationHierarchy::ROLE_EB,AdOrganizationHierarchy::ROLE_STAFF_HR,AdOrganizationHierarchy::ROLE_STAFF_QMB,AdOrganizationHierarchy::FINANCE,AdOrganizationHierarchy::IT,AdOrganizationHierarchy::SECRETARIAT];
    public function __construct(private IAppConfig $config) {}
    public function peerApproval(): array { $result = []; foreach (self::PEER_GROUPS as $group) $result[$group] = $this->config->getValueBool(Application::APP_ID, $this->key($group), false); return $result; }
    public function enabledPeerGroups(): array { return array_keys(array_filter($this->peerApproval())); }
    public function savePeerApproval(array $values): array { foreach (self::PEER_GROUPS as $group) $this->config->setValueBool(Application::APP_ID, $this->key($group), filter_var($values[$group] ?? false, FILTER_VALIDATE_BOOL)); return $this->peerApproval(); }
    private function key(string $group): string { return 'peer_approval_' . strtolower(str_replace(['ad-','-'],['','_'],$group)); }
}
