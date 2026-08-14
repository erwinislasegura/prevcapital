<?php
use App\Core\Auth;
$authUser = Auth::user();
$requestPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$isUsers = str_contains($requestPath, '/admin/users');
$isRoles = str_contains($requestPath, '/admin/roles');
$isContacts = str_contains($requestPath, '/admin/contactos');
$isQuotes = str_contains($requestPath, '/admin/cotizaciones');
$isClients = str_contains($requestPath, '/admin/clientes');
$isDashboard = !$isUsers && !$isRoles && !$isContacts && !$isQuotes && !$isClients;
?>
<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#071826">
    <title><?= e($pageTitle ?? 'Panel') ?> | PrevCapital</title>
    <link rel="icon" type="image/png" href="<?= asset('assets/images/favicon.png') ?>">
    <link rel="stylesheet" href="<?= asset('assets/vendor/bootstrap/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>">
</head>
<body class="admin-page">
<div class="admin-layout">
    <aside class="admin-sidebar d-none d-lg-flex">
        <?php require APP_ROOT . '/app/Views/layouts/admin_navigation.php'; ?>
    </aside>
    <div class="offcanvas offcanvas-start admin-offcanvas" tabindex="-1" id="adminMenu" aria-labelledby="adminMenuLabel">
        <div class="offcanvas-header">
            <span id="adminMenuLabel" class="visually-hidden">Menú administrativo</span>
            <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="offcanvas" aria-label="Cerrar"></button>
        </div>
        <div class="offcanvas-body p-0">
            <?php require APP_ROOT . '/app/Views/layouts/admin_navigation.php'; ?>
        </div>
    </div>

    <main class="admin-main">
        <header class="admin-topbar">
            <button class="btn admin-menu-button d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#adminMenu" aria-controls="adminMenu" aria-label="Abrir menú">
                <span></span><span></span><span></span>
            </button>
            <div>
                <span class="admin-topbar__label">Panel administrativo</span>
                <strong><?= e($pageTitle ?? 'Resumen') ?></strong>
            </div>
            <div class="admin-user">
                <span class="admin-user__avatar"><?= e(mb_strtoupper(mb_substr($authUser['name'] ?? 'U', 0, 1))) ?></span>
                <div class="d-none d-sm-block"><strong><?= e($authUser['name'] ?? '') ?></strong><small><?= e($authUser['email'] ?? '') ?></small></div>
            </div>
        </header>
        <div class="admin-content container-fluid">
            <?php if ($message = flash('success')): ?><div class="alert admin-alert admin-alert--success" role="alert"><?= e($message) ?></div><?php endif; ?>
            <?php if ($message = flash('error')): ?><div class="alert admin-alert admin-alert--error" role="alert"><?= e($message) ?></div><?php endif; ?>
            <?php if ($errors = flash('errors')): ?>
                <div class="alert admin-alert admin-alert--error" role="alert"><strong>Revise la información:</strong><ul class="mb-0 mt-2"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div>
            <?php endif; ?>
            <?= $content ?>
        </div>
    </main>
</div>
<script src="<?= asset('assets/vendor/bootstrap/bootstrap.bundle.min.js') ?>"></script>
</body>
</html>
