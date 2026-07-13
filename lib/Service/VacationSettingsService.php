<?php

declare(strict_types=1);

namespace OCA\AdUrlaub\Service;

use OCA\AdUrlaub\AppInfo\Application;
use OCA\LocalBase\Organization\AdOrganizationDefinition;
use OCA\LocalBase\Organization\AdOrganizationSettingsService;
use OCP\IAppConfig;

/** Zweck: Speichert administrativ freigeschaltete direkte Genehmigung unter Kolleg*innen. */
final class VacationSettingsService {
    public function __construct(private IAppConfig $config, private ?AdOrganizationSettingsService $organization = null) {}
    public function asnPeerGroup(): string { return $this->definition()->teamGroupPrefix() . '*'; }
    public function peerApproval(): array { $result = []; foreach ($this->peerGroups() as $group) $result[$group] = $this->config->getValueBool(Application::APP_ID, $this->key($group), false); return $result; }
    public function enabledPeerGroups(): array { return array_keys(array_filter($this->peerApproval())); }
    public function savePeerApproval(array $values): array { foreach ($this->peerGroups() as $group) $this->config->setValueBool(Application::APP_ID, $this->key($group), filter_var($values[$group] ?? false, FILTER_VALIDATE_BOOL)); return $this->peerApproval(); }
    public function peerOptions(): array { $definition = $this->definition(); return array_map(fn(string $group): array => ['groupId' => $group, 'label' => $group === $this->asnPeerGroup() ? $definition->teamLabelPrefix() . '-Kolleg*innen' : $definition->roleLabelForGroup($group)], $this->peerGroups()); }
    private function peerGroups(): array { return array_merge([$this->asnPeerGroup()], $this->definition()->roleGroupIds(static fn(array $role): bool => $role['peerEnabled'])); }
    private function definition(): AdOrganizationDefinition { return $this->organization?->definition() ?? AdOrganizationDefinition::defaults(); }
    private function key(string $group): string { return 'peer_approval_' . strtolower(str_replace(['ad-','-'],['','_'],$group)); }
}
