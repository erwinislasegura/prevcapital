<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#071826">
    <meta name="description" content="<?= e($metaDescription ?? 'Consultoría estratégica en Seguridad y Salud en el Trabajo para empresas.') ?>">
    <title><?= e($pageTitle ?? 'PrevCapital') ?></title>
    <link rel="icon" type="image/png" href="<?= asset('assets/images/favicon.png') ?>">
    <link rel="preload" as="image" href="<?= asset('assets/images/hero-industrial.webp') ?>" type="image/webp">
    <link rel="preload" as="image" href="<?= asset('assets/images/logo-prevcapital.png') ?>" type="image/png">
    <?php if (($showCampaignPopup ?? false) === true): ?>
        <link rel="preload" as="image" href="<?= asset('assets/images/popup-ds44-prevcapital-v3.webp') ?>" type="image/webp">
    <?php endif; ?>
    <link rel="stylesheet" href="<?= asset('assets/css/app.css') ?>">
</head>
<body>
<main id="inicio">
<?php require APP_ROOT . '/app/Views/layouts/public_header_content.php'; ?>
