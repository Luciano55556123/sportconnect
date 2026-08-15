<?php
$old = $old ?? [];
$errors = $errors ?? [];
$value = static function (string $field, string $fallback = '') use ($old): string {
    return e((string) ($old[$field] ?? $fallback));
};
$error = static function (string $field) use ($errors): void {
    if (!empty($errors[$field])) {
        echo '<div class="invalid-feedback d-block">' . e($errors[$field]) . '</div>';
    }
};
?>
<section class="page-band"><div class="container"><h1>Solicitar perfil de organizador</h1><p>Envie seus dados para analise da administracao.</p></div></section>
<section class="container py-4">
    <form class="panel organizer-request-form" method="post" action="<?= url('/organizador/solicitar') ?>">
        <?= csrf_field() ?>
        <div class="section-heading">
            <h2>Dados da solicitacao</h2>
            <a class="btn btn-outline-secondary btn-sm" href="<?= url('/atleta') ?>">Voltar ao painel</a>
        </div>
        <div class="row g-3">
            <div class="col-md-6">
                <label class="form-label" for="responsible_name">Nome do responsavel</label>
                <input class="form-control" id="responsible_name" name="responsible_name" value="<?= $value('responsible_name', $user['name'] ?? '') ?>" required>
                <?php $error('responsible_name'); ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="document">CPF/CNPJ</label>
                <input class="form-control" id="document" name="document" value="<?= $value('document') ?>" required>
                <?php $error('document'); ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="organization_name">Nome da organizacao</label>
                <input class="form-control" id="organization_name" name="organization_name" value="<?= $value('organization_name') ?>" required>
                <?php $error('organization_name'); ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="organization_type">Tipo de organizacao</label>
                <select class="form-select" id="organization_type" name="organization_type" required>
                    <?php $selectedType = $old['organization_type'] ?? ''; ?>
                    <?php foreach (['Pessoa fisica', 'Empresa', 'Associacao', 'Clube', 'Escola', 'Outro'] as $type): ?>
                        <option value="<?= e($type) ?>" <?= $selectedType === $type ? 'selected' : '' ?>><?= e($type) ?></option>
                    <?php endforeach; ?>
                </select>
                <?php $error('organization_type'); ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="contact_email">E-mail de contato</label>
                <input class="form-control" id="contact_email" type="email" name="contact_email" value="<?= $value('contact_email', $user['email'] ?? '') ?>" required>
                <?php $error('contact_email'); ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="phone">Telefone</label>
                <input class="form-control" id="phone" name="phone" value="<?= $value('phone', $user['phone'] ?? '') ?>" required>
                <?php $error('phone'); ?>
            </div>
            <div class="col-md-6">
                <label class="form-label" for="whatsapp">WhatsApp</label>
                <input class="form-control" id="whatsapp" name="whatsapp" value="<?= $value('whatsapp', $user['phone'] ?? '') ?>" required>
                <?php $error('whatsapp'); ?>
            </div>
            <div class="col-md-8">
                <label class="form-label" for="city">Cidade</label>
                <input class="form-control" id="city" name="city" value="<?= $value('city', $user['city'] ?? '') ?>" required>
                <?php $error('city'); ?>
            </div>
            <div class="col-md-4">
                <label class="form-label" for="state">Estado</label>
                <input class="form-control" id="state" name="state" maxlength="2" value="<?= $value('state') ?>" required placeholder="PR">
                <?php $error('state'); ?>
            </div>
            <div class="col-12">
                <label class="form-label" for="experience">Experiencia com eventos/campeonatos</label>
                <textarea class="form-control" id="experience" name="experience" rows="4" required><?= $value('experience') ?></textarea>
                <?php $error('experience'); ?>
            </div>
            <div class="col-12">
                <label class="form-label" for="request_reason">Motivo para solicitar perfil de organizador</label>
                <textarea class="form-control" id="request_reason" name="request_reason" rows="4" required><?= $value('request_reason') ?></textarea>
                <?php $error('request_reason'); ?>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2 mt-4">
            <a class="btn btn-outline-secondary" href="<?= url('/atleta') ?>">Cancelar</a>
            <button class="btn btn-primary">Enviar solicitacao</button>
        </div>
    </form>
</section>
