<section class="auth-page">
    <form class="auth-card wide" method="post" novalidate>
        <?= csrf_field() ?>
        <input type="hidden" name="role" value="athlete">
        <span class="eyebrow">Novo atleta</span>
        <h1>Criar conta</h1>
        <div class="row g-3">
            <div class="col-md-6"><label class="form-label">Nome completo<input class="form-control" name="name" autocomplete="name" required></label></div>
            <div class="col-md-6"><label class="form-label">Email<input class="form-control" type="email" name="email" autocomplete="email" required></label></div>
            <div class="col-md-6">
                <label class="form-label">Senha</label>
                <div class="input-group">
                    <input id="registerPassword" class="form-control" type="password" name="password" autocomplete="new-password" minlength="8" required>
                    <button class="btn btn-outline-secondary" type="button" data-password-toggle="#registerPassword" aria-label="Mostrar ou ocultar senha"><i class="fa-solid fa-eye"></i></button>
                </div>
                <div class="form-text">Use pelo menos 8 caracteres.</div>
            </div>
            <div class="col-md-6"><label class="form-label">Telefone<input class="form-control" name="phone" autocomplete="tel" placeholder="Opcional"></label></div>
            <div class="col-md-6"><label class="form-label">Cidade<input class="form-control" name="city" autocomplete="address-level2"></label></div>
            <div class="col-md-6"><label class="form-label">Nascimento<input class="form-control" type="date" name="birth_date"></label></div>
            <div class="col-md-6"><label class="form-label">Valor maximo preferido<input class="form-control" type="number" step="0.01" name="preferred_price_max"></label></div>
        </div>
        <label class="form-label mt-3">Esportes favoritos</label>
        <div class="checks"><?php foreach ($sports as $sport): ?><label><input type="checkbox" name="sports[]" value="<?= (int) $sport['id'] ?>"> <?= e($sport['name']) ?></label><?php endforeach; ?></div>
        <button class="btn btn-primary w-100 mt-3" type="submit">Cadastrar como atleta</button>
        <p class="text-muted mt-3 mb-0">Quer organizar campeonatos? Crie sua conta e solicite o perfil de organizador no painel.</p>
    </form>
</section>
