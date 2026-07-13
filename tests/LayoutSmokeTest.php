<?php

declare(strict_types=1);

$css = file_get_contents(__DIR__ . '/../css/style.css'); $template = file_get_contents(__DIR__ . '/../templates/index.php'); $info = file_get_contents(__DIR__ . '/../appinfo/info.xml');
if ($info === false || !str_contains($info, '<app>orgsuite</app>') || str_contains($info, '<navigations>')) throw new RuntimeException('OrgSuite-Appvertrag fehlt.');
foreach (['height:100%','min-height:0','overflow-y:auto','background:var(--color-main-background)','overflow-x:auto','width:max-content'] as $contract) if (!str_contains($css, $contract)) throw new RuntimeException("Scrollvertrag fehlt: {$contract}");
foreach (['<main','<form id="adu-own-form"','aria-live="polite"','<caption>', "script('orgsuite', 'suite-navigation')", "style('orgsuite', 'suite-navigation')", 'data-orgsuite data-suite="ad" data-current-app="adurlaub"'] as $contract) if (!str_contains($template, $contract)) throw new RuntimeException("UI-Vertrag fehlt: {$contract}");
echo "LayoutSmokeTest: OK\n";
