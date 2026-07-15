<?php

declare(strict_types=1);

namespace OCA\AdUrlaub\AppInfo;

use OCA\AdUrlaub\Listener\AbsenceQueryListener;
use OCA\AdUrlaub\Listener\IntegrationCapabilityQueryListener;
use OCA\AdUrlaub\Listener\StandaloneNavigationListener;
use OCA\LocalBase\Calendar\AbsenceQueryEvent;
use OCA\LocalBase\Integration\IntegrationCapabilityQueryEvent;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Navigation\Events\LoadAdditionalEntriesEvent;

/** Zweck: Registriert Abwesenheits-, Capability- und Standalone-Navigationsverträge im Nextcloud-Bootstrap. */
final class Application extends App implements IBootstrap {
    public const APP_ID = 'adurlaub';
    public function __construct(array $urlParams = []) { parent::__construct(self::APP_ID, $urlParams); }
    public function register(IRegistrationContext $context): void {
        $context->registerEventListener(AbsenceQueryEvent::class, AbsenceQueryListener::class);
        $context->registerEventListener(IntegrationCapabilityQueryEvent::class, IntegrationCapabilityQueryListener::class);
        $context->registerEventListener(LoadAdditionalEntriesEvent::class, StandaloneNavigationListener::class);
    }
    public function boot(IBootContext $context): void {}
}
