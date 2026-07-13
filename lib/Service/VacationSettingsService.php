<?php

declare(strict_types=1);

namespace OCA\AdUrlaub\Service;

use OCA\LocalBase\Organization\AdSuiteAdminSettingsService;

/** Zweck: Stellt AD Urlaub die zentral in OrgSuite administrierten Kolleg*innen-Freigaben read-only bereit. */
final class VacationSettingsService {
    public function __construct(private AdSuiteAdminSettingsService $adminSettings) {}
    public function asnPeerGroup(): string { return $this->adminSettings->asnPeerGroup(); }
    public function peerApproval(): array { return $this->adminSettings->vacationPeerApproval(); }
    public function enabledPeerGroups(): array { return $this->adminSettings->enabledVacationPeerGroups(); }
}
