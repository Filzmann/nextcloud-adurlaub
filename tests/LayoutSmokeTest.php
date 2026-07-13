<?php

declare(strict_types=1);

$css = file_get_contents(__DIR__ . '/../css/style.css'); $template = file_get_contents(__DIR__ . '/../templates/index.php');
foreach (['height:100%','min-height:0','overflow-y:auto','background:var(--color-main-background)','overflow-x:auto','width:max-content'] as $contract) if (!str_contains($css, $contract)) throw new RuntimeException("Scrollvertrag fehlt: {$contract}");
foreach (['<main','<form id="adu-own-form"','aria-live="polite"','<caption>'] as $contract) if (!str_contains($template, $contract)) throw new RuntimeException("UI-Vertrag fehlt: {$contract}");
echo "LayoutSmokeTest: OK\n";
