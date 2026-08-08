<section class="page-band"><div class="container"><h1><?= e($title) ?></h1></div></section>
<section class="container py-4">
    <form class="panel row g-3" method="post" enctype="multipart/form-data" action="<?= !empty($championship['id']) ? url('/organizador/campeonatos/' . $championship['id']) : url('/organizador/campeonatos') ?>">
        <?= csrf_field() ?>
        <div class="col-md-6"><input class="form-control" name="name" value="<?= e($championship['name'] ?? '') ?>" required placeholder="Nome"></div>
        <div class="col-md-3">
            <select class="form-select" name="sport_id">
                <?php foreach ($sports as $sport): ?><option value="<?= $sport['id'] ?>" <?= ($championship['sport_id'] ?? '') == $sport['id'] ? 'selected' : '' ?>><?= e($sport['name']) ?></option><?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" name="status">
                <?php foreach (['ativo' => 'Ativo', 'encerrado' => 'Encerrado', 'cancelado' => 'Cancelado'] as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= ($championship['status'] ?? 'ativo') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4"><input class="form-control" name="city" value="<?= e($championship['city'] ?? '') ?>" required placeholder="Cidade"></div>
        <div class="col-md-4"><input class="form-control" name="location" value="<?= e($championship['location'] ?? '') ?>" required placeholder="Local"></div>
        <div class="col-md-4"><input class="form-control" name="map_link" value="<?= e($championship['map_link'] ?? '') ?>" placeholder="Link do mapa"></div>
        <div class="col-md-3"><input class="form-control" type="date" name="event_date" value="<?= e($championship['event_date'] ?? '') ?>" required></div>
        <div class="col-md-3"><input class="form-control" type="time" name="event_time" value="<?= e($championship['event_time'] ?? '') ?>" required></div>
        <div class="col-md-3"><input class="form-control" type="number" name="max_participants" value="<?= e($championship['max_participants'] ?? '') ?>" placeholder="Max. participantes"></div>
        <div class="col-md-4"><input class="form-control" name="category" value="<?= e($championship['category'] ?? '') ?>" placeholder="Categoria"></div>
        <div class="col-md-4">
            <select class="form-select" name="modality">
                <?php foreach (['misto' => 'Misto', 'masculino' => 'Masculino', 'feminino' => 'Feminino'] as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= ($championship['modality'] ?? 'misto') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4"><input class="form-control" name="prize" value="<?= e($championship['prize'] ?? '') ?>" placeholder="Premiacao"></div>
        <div class="col-12">
            <div class="border rounded p-3">
                <h2 class="h5 mb-3">Inscricao e pagamento</h2>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">E-mail para receber inscricoes *</label>
                        <input class="form-control" type="email" name="email_contato" value="<?= e($championship['email_contato'] ?? '') ?>" required placeholder="organizador@email.com">
                        <div class="form-text">As novas inscricoes, comprovantes de pagamento e outras atualizacoes deste campeonato serao enviadas para este e-mail.</div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">WhatsApp de contato *</label>
                        <input class="form-control" name="whatsapp_contato" value="<?= e($championship['whatsapp_contato'] ?? $championship['organizer_phone'] ?? '') ?>" required placeholder="WhatsApp de contato">
                    </div>
                    <div class="col-md-12 d-flex align-items-center">
                        <label class="form-check mb-0"><input class="form-check-input js-payment-toggle" type="checkbox" name="requires_payment" value="1" <?= !empty($championship['requires_payment']) || (float) ($championship['registration_fee'] ?? 0) > 0 ? 'checked' : '' ?>> Campeonato pago via PIX</label>
                    </div>
                    <div class="col-md-4"><input class="form-control js-payment-field" type="number" step="0.01" min="0" name="registration_fee" value="<?= e($championship['registration_fee'] ?? '0') ?>" placeholder="Valor da inscricao em R$"></div>
                    <div class="col-md-4 js-pix-area">
                        <select class="form-select js-payment-field" name="pix_key_type">
                            <option value="">Tipo da chave PIX</option>
                            <?php foreach (['cpf' => 'CPF', 'cnpj' => 'CNPJ', 'email' => 'E-mail', 'telefone' => 'Telefone', 'aleatoria' => 'Chave aleatoria'] as $value => $label): ?>
                                <option value="<?= e($value) ?>" <?= ($championship['pix_key_type'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 js-pix-area"><input class="form-control js-payment-field" name="pix_key" value="<?= e($championship['pix_key'] ?? '') ?>" placeholder="Chave PIX"></div>
                    <div class="col-md-6 js-pix-area"><input class="form-control js-payment-field" name="pix_holder_name" value="<?= e($championship['pix_holder_name'] ?? '') ?>" placeholder="Nome do titular"></div>
                    <div class="col-md-6 js-pix-area"><textarea class="form-control js-payment-field" rows="2" name="pix_instructions" placeholder="Instrucoes de pagamento"><?= e($championship['pix_instructions'] ?? '') ?></textarea></div>
                </div>
            </div>
        </div>
        <div class="col-md-6"><label class="form-label">Imagem</label><input class="form-control" type="file" name="image" accept=".jpg,.jpeg,.png,.webp"></div>
        <div class="col-md-6"><label class="form-label">Regulamento PDF</label><input class="form-control" type="file" name="rules_file" accept=".pdf"></div>
        <div class="col-12"><textarea class="form-control" rows="5" name="description" required placeholder="Descricao"><?= e($championship['description'] ?? '') ?></textarea></div>
        <input type="hidden" name="current_image" value="<?= e($championship['image'] ?? 'assets/img/default-event.svg') ?>">
        <input type="hidden" name="current_rules_file" value="<?= e($championship['rules_file'] ?? '') ?>">
        <div class="col-12"><button class="btn btn-primary">Salvar campeonato</button></div>
    </form>
</section>
