<div class="auth-card">
    <span class="admin-eyebrow">Acceso seguro</span>
    <h2>Ingresar al panel</h2>
    <p>Utilice las credenciales asignadas para administrar PrevCapital.</p>
    <?php if ($message = flash('success')): ?><div class="alert admin-alert admin-alert--success"><?= e($message) ?></div><?php endif; ?>
    <?php if ($message = flash('error')): ?><div class="alert admin-alert admin-alert--error"><?= e($message) ?></div><?php endif; ?>
    <form method="post" action="<?= url('/login') ?>" class="admin-form">
        <?= csrf_field() ?>
        <label class="form-label" for="email">Correo electrónico</label>
        <input class="form-control" id="email" name="email" type="email" value="<?= e(old('email')) ?>" autocomplete="email" required autofocus>
        <label class="form-label mt-3" for="password">Contraseña</label>
        <input class="form-control" id="password" name="password" type="password" autocomplete="current-password" required>
        <button class="btn admin-primary-btn w-100 mt-4" type="submit">Ingresar al panel</button>
    </form>
    <div class="auth-card__footer"><a href="<?= url('/') ?>">Volver al sitio</a><a href="<?= url('/setup') ?>">Primera instalación</a></div>
</div>
