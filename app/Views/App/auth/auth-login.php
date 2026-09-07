<?= $this->layout("auth/auth-app", [
        "title" => $title ?? "Entrar | " . APP_NAME,
]) ?>

<p class="auth-subtitle mb-5 mt-5">Informe seus dados para fazer login.</p>

<?= \App\Core\Message::render() ?>

<form action="<?= url('/entrar') ?>" method="post">

    <?= csrf_input() ?>

    <div class="form-group position-relative has-icon-left mb-4">
        <input type="email" name="email" class="form-control form-control-xl" placeholder="Email" required>
        <div class="form-control-icon">
            <i class="bi bi-person"></i>
        </div>
    </div>
    <div class="form-group position-relative has-icon-left mb-4">
        <input type="password" name="password" id="password"
               class="form-control form-control-xl"
               placeholder="Senha"
               style="padding-right: 3rem;"
               required>
        <div class="form-control-icon">
            <i class="bi bi-shield-lock"></i>
        </div>
        <div class="form-control-icon"
             id="togglePassword"
             style="left: auto; right: 1rem; cursor: pointer;">
            <i class="bi bi-eye-slash" id="eyeIcon"></i>
        </div>
    </div>

    <button class="btn btn-primary btn-block btn-lg shadow-lg mt-5">Entrar</button>
</form>