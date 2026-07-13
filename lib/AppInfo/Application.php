<?php

declare(strict_types=1);

namespace OCA\AdUrlaub\AppInfo;

use OCA\AdUrlaub\Listener\AbsenceQueryListener;
use OCA\LocalBase\Calendar\AbsenceQueryEvent;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;

final class Application extends App implements IBootstrap {
    public const APP_ID = 'adurlaub';
    public function __construct(array $urlParams = []) { parent::__construct(self::APP_ID, $urlParams); }
    public function register(IRegistrationContext $context): void { $context->registerEventListener(AbsenceQueryEvent::class, AbsenceQueryListener::class); }
    public function boot(IBootContext $context): void {}
}
