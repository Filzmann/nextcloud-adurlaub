<?php

declare(strict_types=1);

$css = file_get_contents(__DIR__ . '/../css/style.css'); $template = file_get_contents(__DIR__ . '/../templates/index.php'); $info = file_get_contents(__DIR__ . '/../appinfo/info.xml');
if ($info === false || str_contains($info, '<app>') || str_contains($info, '<navigations>')) throw new RuntimeException('Standalone-Appvertrag fehlt.');
foreach (['height:100%','min-height:0','overflow-y:auto','background:var(--color-main-background)','overflow-x:auto','width:max-content'] as $contract) if (!str_contains($css, $contract)) throw new RuntimeException("Scrollvertrag fehlt: {$contract}");
foreach (['.adu-notice:empty{display:none}', '.adu-notice--error{color:var(--color-error-text)', 'background:var(--color-error)', 'border:2px solid var(--color-error)'] as $contract) if (!str_contains($css, $contract)) throw new RuntimeException("Kontrastreicher Fehlerhinweis fehlt: {$contract}");
foreach (['<main','id="adu-calendar-view"','<form id="adu-own-form"','aria-live="polite"','<caption>', "\\OCP\\Util::addScript('adurlaub', 'components/vacation-plan')", "\\OCP\\Util::addScript('adurlaub', 'modules/vacation-app')", 'data-orgsuite data-suite="ad" data-current-app="adurlaub"'] as $contract) if (!str_contains($template, $contract)) throw new RuntimeException("UI-Vertrag fehlt: {$contract}");
if (str_contains($template, "addScript('orgsuite'") || str_contains($template, "addStyle('orgsuite'")) throw new RuntimeException('Direkte OrgSuite-Assetkopplung vorhanden.');
if (preg_match('/^\\s*(?:script|style)\\s*\\(/m', $template) === 1) throw new RuntimeException('Veralteter globaler Templatehelfer gefunden.');
foreach (['adu-tab-settings', 'adu-settings-view', '>Einstellungen</button>'] as $removed) if (str_contains($template, $removed)) throw new RuntimeException("Organisationsweite Einstellung liegt noch in AD Urlaub: {$removed}");
echo "LayoutSmokeTest: OK\n";
