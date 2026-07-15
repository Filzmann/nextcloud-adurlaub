<?php

declare(strict_types=1);

namespace OCP\EventDispatcher { class Event { public function __construct() {} } interface IEventListener { public function handle(Event $event): void; } }
namespace OCA\AdUrlaub\AppInfo { final class Application { public const APP_ID = 'adurlaub'; } }

namespace {
    require_once __DIR__ . '/../../localbase/lib/Integration/AdIntegrationCapabilities.php';
    require_once __DIR__ . '/../../localbase/lib/Integration/IntegrationCapabilityQueryEvent.php';
    require_once __DIR__ . '/../lib/Listener/IntegrationCapabilityQueryListener.php';

    use OCA\AdUrlaub\Listener\IntegrationCapabilityQueryListener;
    use OCA\LocalBase\Integration\AdIntegrationCapabilities;
    use OCA\LocalBase\Integration\IntegrationCapabilityQueryEvent;

    $event = new IntegrationCapabilityQueryEvent(AdIntegrationCapabilities::all());
    (new IntegrationCapabilityQueryListener())->handle($event);
    if ($event->providersFor(AdIntegrationCapabilities::ABSENCE_READ) !== ['adurlaub']) throw new RuntimeException('Urlaubsfähigkeit fehlt.');
    if ($event->isAvailable(AdIntegrationCapabilities::SCHEDULE_CONFLICT_READ)) throw new RuntimeException('Urlaub meldet eine fremde Fähigkeit.');

    echo "AD Urlaub capability listener test passed\n";
}
