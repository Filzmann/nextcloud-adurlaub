<?php

declare(strict_types=1);

namespace OCP\EventDispatcher {
    class Event { public function __construct() {} }
    interface IEventDispatcher { public function dispatchTyped(object $event): object; }
}

namespace {
    require_once __DIR__ . '/../../localbase/lib/Integration/AdIntegrationCapabilities.php';
    require_once __DIR__ . '/../../localbase/lib/Integration/IntegrationCapabilityQueryEvent.php';
    require_once __DIR__ . '/../../localbase/lib/Service/IntegrationCapabilityService.php';
    require_once __DIR__ . '/../lib/Service/IntegrationStatusService.php';

    use OCA\AdUrlaub\Service\IntegrationStatusService;
    use OCA\LocalBase\Integration\AdIntegrationCapabilities;
    use OCA\LocalBase\Integration\IntegrationCapabilityQueryEvent;
    use OCA\LocalBase\Service\IntegrationCapabilityService;
    use OCP\EventDispatcher\IEventDispatcher;

    $dispatcher = new class implements IEventDispatcher {
        public bool $available = false;

        public function dispatchTyped(object $event): object {
            if ($this->available && $event instanceof IntegrationCapabilityQueryEvent) {
                $event->provide('adcalendar', [AdIntegrationCapabilities::SCHEDULE_CONFLICT_READ]);
            }
            return $event;
        }
    };
    $status = new IntegrationStatusService(new IntegrationCapabilityService($dispatcher));

    if ($status->calendarConflictCheck() !== [
        'available' => false,
        'providers' => [],
    ]) {
        throw new RuntimeException('Der Standalone-Status ohne Kalender ist falsch.');
    }

    $dispatcher->available = true;
    if ($status->calendarConflictCheck() !== [
        'available' => true,
        'providers' => ['adcalendar'],
    ]) {
        throw new RuntimeException('Der integrierte Kalenderstatus ist falsch.');
    }

    echo "AD Urlaub integration status test passed\n";
}
