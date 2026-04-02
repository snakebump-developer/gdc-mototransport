<?php

/**
 * Componente <head> riutilizzabile.
 *
 * Variabili attese (definite prima dell'include):
 *   $pageTitle   — Titolo della pagina (obbligatorio)
 *   $extraCss    — Array di path CSS aggiuntivi (opzionale)
 *   $noFontAwesome — Se true, non carica Font Awesome (opzionale)
 */
$pageTitle     = $pageTitle     ?? 'MotoTransport';
$extraCss      = $extraCss      ?? [];
$noFontAwesome = $noFontAwesome ?? false;
?>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= htmlspecialchars($pageTitle) ?></title>
<?php if (!$noFontAwesome): ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<?php endif; ?>
<link rel="stylesheet" href="/css/modules/base.css">
<link rel="stylesheet" href="/css/modules/components.css">
<link rel="stylesheet" href="/css/modules/navbar.css">
<?php foreach ($extraCss as $css): ?>
    <link rel="stylesheet" href="/<?= htmlspecialchars(ltrim($css, '/')) ?>">
<?php endforeach; ?>
<link rel="stylesheet" href="/css/modules/responsive.css">