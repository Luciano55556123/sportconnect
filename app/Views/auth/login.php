<section class="auth-page">
    <form class="auth-card" method="post" novalidate>
        <?= csrf_field() ?>
        <span class="eyebrow">Acesso seguro</span>
        <h1>Entrar</h1>
        <label class="form-label">Email<input class="form-control" type="email" name="email" autocomplete="email" required></label>
        <label class="form-label">Senha</label>
        <div class="input-group">
            <input id="loginPassword" class="form-control" type="password" name="password" autocomplete="current-password" required>
            <button class="btn btn-outline-secondary" type="button" data-password-toggle="#loginPassword" aria-label="Mostrar ou ocultar senha"><i class="fa-solid fa-eye"></i></button>
        </div>
        <button class="btn btn-primary w-100" type="submit">Acessar</button>
        <a href="<?= url('/cadastro') ?>">Criar conta</a>
    </form>
</section>
