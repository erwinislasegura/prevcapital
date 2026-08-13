<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#071826">
    <title><?= e($pageTitle ?? 'Acceso administrativo') ?> | PrevCapital</title>
    <link rel="icon" type="image/png" href="<?= asset('assets/images/favicon.png') ?>">
    <link rel="stylesheet" href="<?= asset('assets/vendor/bootstrap/bootstrap.min.css') ?>">
    <link rel="stylesheet" href="<?= asset('assets/css/admin.css') ?>">
</head>
<body class="auth-page">
    <div class="auth-shell">
        <section class="auth-brand-panel">
            <a href="<?= url('/') ?>" class="auth-logo"><img src="<?= asset('assets/images/logo-prevcapital.png') ?>" alt="PrevCapital"></a>
            <div>
                <span class="admin-eyebrow">Gestión corporativa</span>
                <h1>Seguridad y prevención con control centralizado.</h1>
                <p>Administre los accesos de su equipo con roles, permisos y trazabilidad.</p>
            </div>
            <small>Seguridad y Salud en el Trabajo · Chile</small>
        </section>
        <main class="auth-content">
            <?= $content ?>
        </main>
    </div>
</body>
</html>
