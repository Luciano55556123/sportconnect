<?php
$isEdit = !empty($championship['id']);
$selectedStatus = $championship['status'] ?? 'ativo';
$selectedModality = $championship['modality'] ?? 'misto';
$currentImageUrl = $isEdit ? championship_image_url($championship) : null;
?>
<section class="container py-4">
    <form class="panel row g-3" method="post" enctype="multipart/form-data" action="<?= $isEdit ? url('/organizador/campeonatos/' . $championship['id']) : url('/organizador/campeonatos') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="MAX_FILE_SIZE" value="5242880">
        <div class="col-md-6"><input class="form-control" name="name" value="<?= e($championship['name'] ?? '') ?>" required placeholder="Nome"></div>
        <div class="col-md-3">
            <select class="form-select" name="sport_id" required>
                <?php foreach ($sports as $sport): ?>
                    <option value="<?= (int) $sport['id'] ?>" <?= ($championship['sport_id'] ?? '') == $sport['id'] ? 'selected' : '' ?>><?= e($sport['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select class="form-select" name="status">
                <option value="ativo" <?= $selectedStatus === 'ativo' ? 'selected' : '' ?>>Ativo</option>
                <option value="encerrado" <?= $selectedStatus === 'encerrado' ? 'selected' : '' ?>>Encerrado</option>
                <option value="cancelado" <?= $selectedStatus === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
            </select>
        </div>
        <div class="col-md-4"><input class="form-control" name="city" value="<?= e($championship['city'] ?? '') ?>" required placeholder="Cidade"></div>
        <div class="col-md-4"><input class="form-control" name="location" value="<?= e($championship['location'] ?? '') ?>" required placeholder="Local"></div>
        <div class="col-md-4"><input class="form-control" name="map_link" value="<?= e($championship['map_link'] ?? '') ?>" placeholder="Link do mapa"></div>
        <div class="col-md-3"><input class="form-control" type="date" name="event_date" value="<?= e($championship['event_date'] ?? '') ?>" required></div>
        <div class="col-md-3"><input class="form-control" type="time" name="event_time" value="<?= e($championship['event_time'] ?? '') ?>" required></div>
        <div class="col-md-3"><input class="form-control" type="number" step="0.01" name="registration_fee" value="<?= e($championship['registration_fee'] ?? '') ?>" placeholder="Valor"></div>
        <div class="col-md-3"><input class="form-control" type="number" name="max_participants" value="<?= e($championship['max_participants'] ?? '') ?>" placeholder="Max. participantes"></div>
        <div class="col-md-4"><input class="form-control" name="category" value="<?= e($championship['category'] ?? '') ?>" placeholder="Categoria"></div>
        <div class="col-md-4">
            <select class="form-select" name="modality">
                <option value="misto" <?= $selectedModality === 'misto' ? 'selected' : '' ?>>Misto</option>
                <option value="masculino" <?= $selectedModality === 'masculino' ? 'selected' : '' ?>>Masculino</option>
                <option value="feminino" <?= $selectedModality === 'feminino' ? 'selected' : '' ?>>Feminino</option>
            </select>
        </div>
        <div class="col-md-4"><input class="form-control" name="prize" value="<?= e($championship['prize'] ?? '') ?>" placeholder="Premiacao"></div>
        <div class="col-md-6">
            <label class="form-label" for="whatsapp_contato">Numero de WhatsApp do responsavel</label>
            <input
                class="form-control"
                id="whatsapp_contato"
                type="tel"
                name="whatsapp_contato"
                value="<?= e($championship['whatsapp_contato'] ?? '') ?>"
                required
                inputmode="tel"
                autocomplete="tel"
                placeholder="(42) 99999-9999"
                pattern="^\(?[1-9]{2}\)?\s?9?\d{4}-?\d{4}$"
                data-whatsapp-mask
            >
            <small class="text-muted">Esse numero ficara disponivel para os atletas entrarem em contato.</small>
        </div>
        <div class="col-md-6">
            <label class="form-label" for="imagem">Imagem do campeonato</label>
            <?php if ($currentImageUrl): ?>
                <img class="detail-img mb-2" src="<?= e($currentImageUrl) ?>" alt="<?= e($championship['name'] ?? 'Imagem atual do campeonato') ?>">
            <?php endif; ?>
            <input class="form-control" id="imagem" type="file" name="imagem" accept="image/jpeg,image/png,image/webp">
            <small class="text-muted">Formatos aceitos: JPG, JPEG, PNG e WEBP. Tamanho maximo: 5 MB.</small>
        </div>
        <div class="col-md-6"><label class="form-label">Regulamento PDF</label><input class="form-control" type="file" name="rules_file" accept=".pdf"></div>
        <div class="col-12"><textarea class="form-control" rows="5" name="description" required placeholder="Descricao"><?= e($championship['description'] ?? '') ?></textarea></div>
        <input type="hidden" name="current_image" value="<?= e($championship['image'] ?? 'assets/img/default-event.svg') ?>">
        <input type="hidden" name="current_imagem" value="<?= e($championship['imagem'] ?? '') ?>">
        <input type="hidden" name="current_rules_file" value="<?= e($championship['rules_file'] ?? '') ?>">
        <div class="col-12"><button class="btn btn-primary">Salvar campeonato</button></div>
    </form>
</section>
