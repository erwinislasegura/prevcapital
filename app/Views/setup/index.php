<div class="auth-card auth-card--wide">
    <span class="admin-eyebrow">Configuración inicial</span>
    <h2>Crear superadministrador</h2>
    <p>Esta pantalla quedará deshabilitada automáticamente después de crear el primer usuario.</p>
    <?php if ($message = flash('error')): ?><div class="alert admin-alert admin-alert--error"><?= e($message) ?></div><?php endif; ?>
    <?php if ($errors = flash('errors')): ?><div class="alert admin-alert admin-alert--error"><ul class="mb-0"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul></div><?php endif; ?>
    <form method="post" action="<?= url('/setup') ?>" class="admin-form">
        <?= csrf_field() ?>
        <label class="form-label" for="name">Nombre completo</label>
        <input class="form-control" id="name" name="name" value="<?= e(old('name')) ?>" required>
        <label class="form-label mt-3" for="email">Correo electrónico</label>
        <input class="form-control" id="email" name="email" type="email" value="<?= e(old('email')) ?>" required>
        <div class="row g-3 mt-0">
            <div class="col-md-6"><label class="form-label mt-3" for="password">Contraseña</label><input class="form-control" id="password" name="password" type="password" minlength="10" required></div>
            <div class="col-md-6"><label class="form-label mt-3" for="password_confirmation">Confirmar contraseña</label><input class="form-control" id="password_confirmation" name="password_confirmation" type="password" minlength="10" required></div>
        </div>
        <button class="btn admin-primary-btn w-100 mt-4" type="submit">Configurar PrevCapital</button>
    </form>
</div>
