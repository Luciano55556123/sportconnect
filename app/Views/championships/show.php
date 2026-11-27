<section class="sc-public-hero">
    <?php $registrationsOpen = in_array($championship['registrations_open'] ?? false, [true, 1, '1', 't', 'true', 'yes', 'on'], true); ?>
    <?php $maximumSpots = (int) ($championship['max_participants'] ?? 0); ?>
    <?php $isFull = $maximumSpots > 0 && (int) ($occupiedSpots ?? 0) >= $maximumSpots; ?>
    <?php $registrationsClosed = !championship_registration_open($championship) || $isFull; ?>
    <?php $closedLabel = in_array($championship['status'] ?? '', ['encerrado', 'finished'], true) ? 'Campeonato encerrado' : (!championship_registration_open($championship) ? 'Inscrições encerradas' : 'Vagas esgotadas'); ?>
    <?php $contactWhatsapp = (string) (($championship['whatsapp_contato'] ?? '') ?: ($championship['organizer_phone'] ?? '')); ?>
    <?php $contactWhatsappNumber = whatsapp_number($contactWhatsapp); ?>
    <div class="container">
        <div class="sc-public-hero-grid">
            <div class="sc-public-copy">
                <span class="sc-status-chip"><?= e(championship_status_label($championship)) ?></span>
                <h1><?= e($championship['name']) ?></h1>
                <?php if (championship_is_demo($championship)): ?><span class="badge text-bg-warning">Campeonato demonstrativo — dados fictícios</span><?php endif; ?>
                <p><?= e($championship['description']) ?></p>
                <div class="sc-hero-meta">
                    <span><i class="fa-solid fa-trophy"></i><?= e(in_array((int) ($championship['sport_id'] ?? 0), [3, 15], true) ? 'Vôlei' : ($championship['sport_name'] ?? '')) ?></span>
                    <span><i class="fa-solid fa-layer-group"></i><?= e(ucfirst($championship['modality'] ?? 'misto')) ?></span>
                    <span><i class="fa-solid fa-calendar"></i><?= e(date('d/m/Y', strtotime($championship['event_date']))) ?></span>
                    <span><i class="fa-solid fa-location-dot"></i><?= e($championship['location']) ?></span>
                    <span><i class="fa-solid fa-user-tie"></i><?= e($championship['organizer_name'] ?? 'Organizador') ?></span>
                </div>
                <div class="sc-public-actions">
                    <?php if (!$registrationsClosed): ?>
                        <button class="btn btn-warning btn-lg" data-bs-toggle="collapse" data-bs-target="#registerForm"><i class="fa-solid fa-user-plus"></i> Participar</button>
                    <?php else: ?>
                        <span class="badge text-bg-secondary"><?= $closedLabel ?></span>
                    <?php endif; ?>
                    <?php $mapLink = (string) ($championship['map_link'] ?? ''); ?>
                    <?php if (filter_var($mapLink, FILTER_VALIDATE_URL) && preg_match('#^https?://#i', $mapLink)): ?><a class="btn btn-outline-light btn-lg" target="_blank" rel="noopener" href="<?= e($mapLink) ?>">Abrir mapa</a><?php endif; ?>
                    <?php if ($championship['rules_file']): ?><a class="btn btn-outline-light btn-lg" href="<?= url('/campeonatos/' . (int) $championship['id'] . '/regulamento') ?>" target="_blank" rel="noopener">Regulamento</a><?php endif; ?>
                </div>
            </div>
            <aside class="sc-public-media">
                <img src="<?= url($championship['image']) ?>" alt="Imagem do campeonato <?= e($championship['name']) ?>">
                <div class="sc-public-price">
                    <span>Inscrição</span>
                    <strong>R$ <?= number_format((float) $championship['registration_fee'], 2, ',', '.') ?></strong>
                </div>
            </aside>
        </div>
    </div>
</section>

<section class="container py-4">
    <div class="row g-4">
        <div class="col-xl-8">
            <?php require BASE_PATH . '/app/Views/championships/_competition.php'; ?>
            <section class="sc-panel mt-4">
                <div class="sc-panel-head"><div><span class="sc-eyebrow">Comunidade</span><h2>Comentários e avaliações</h2></div></div>
                <?php foreach ($reviews as $review): ?>
                    <article class="sc-log"><strong><?= e($review['name']) ?> - <?= str_repeat('*', (int) $review['rating']) ?></strong><span><?= e($review['comment']) ?></span></article>
                <?php endforeach; ?>
                <?php if (!$reviews): ?><p class="text-muted mb-0">Nenhuma avaliação publicada.</p><?php endif; ?>
            </section>
        </div>

        <aside class="col-xl-4">
            <div class="sc-side-cta">
                <div class="sc-side-price"><span>Valor da inscrição</span><strong>R$ <?= number_format((float) $championship['registration_fee'], 2, ',', '.') ?></strong></div>
                <div class="sc-side-info">
                    <span><i class="fa-solid fa-users"></i><?= (int) ($occupiedSpots ?? 0) ?><?= $maximumSpots > 0 ? ' / ' . $maximumSpots : '' ?> vagas ocupadas</span>
                    <span><i class="fa-solid fa-city"></i><?= e($championship['city']) ?></span>
                    <span><i class="fa-brands fa-whatsapp"></i><?= e($contactWhatsapp) ?></span>
                </div>
                <?php if (!$registrationsClosed && !empty($championship['requires_payment']) && (float) ($championship['registration_fee'] ?? 0) > 0): ?>
                    <div class="alert alert-warning">Pagamento via PIX após a inscrição. Status inicial: aguardando pagamento.</div>
                <?php endif; ?>
                <form method="post" action="<?= url('/campeonatos/' . $championship['id'] . '/favoritar') ?>"><?= csrf_field() ?><button class="btn btn-outline-danger w-100"><i class="fa-solid fa-heart"></i> Favoritar</button></form>
                <?php if ($contactWhatsappNumber !== ''): ?><a class="btn btn-success w-100" target="_blank" href="https://wa.me/<?= e($contactWhatsappNumber) ?>"><i class="fa-brands fa-whatsapp"></i> WhatsApp</a><?php endif; ?>
                <button class="btn btn-outline-primary w-100" onclick="navigator.share ? navigator.share({title: document.title, url: location.href}) : navigator.clipboard.writeText(location.href)"><i class="fa-solid fa-share-nodes"></i> Compartilhar</button>
                <?php if (!$registrationsClosed): ?>
                    <div class="collapse mt-3" id="registerForm">
                        <form method="post" action="<?= url('/campeonatos/' . $championship['id'] . '/inscrever') ?>" class="stack-form">
                            <?= csrf_field() ?>
                            <input class="form-control" name="name" required placeholder="Nome">
                            <input class="form-control" name="phone" required placeholder="Telefone">
                            <input class="form-control" type="email" name="email" required placeholder="Email">
                            <input class="form-control" name="team" placeholder="Equipe">
                            <input class="form-control" name="category" placeholder="Categoria">
                            <input class="form-control" name="city" required placeholder="Cidade">
                            <input class="form-control" name="cpf" placeholder="CPF opcional">
                            <textarea class="form-control" name="notes" placeholder="Observações"></textarea>
                            <button class="btn btn-primary">Enviar inscrição</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="alert alert-secondary mb-0"><?= $closedLabel ?></div>
                <?php endif; ?>
                <hr>
                <form method="post" action="<?= url('/campeonatos/' . $championship['id'] . '/avaliar') ?>" class="stack-form">
                    <?= csrf_field() ?>
                    <label class="form-label">Avaliar campeonato</label>
                    <select class="form-select" name="rating"><?php foreach ([5, 4, 3, 2, 1] as $stars): ?><option value="<?= $stars ?>"><?= $stars ?> <?= $stars === 1 ? 'estrela' : 'estrelas' ?></option><?php endforeach; ?></select>
                    <textarea class="form-control" name="comment" maxlength="1500" placeholder="Comentário (opcional)"></textarea>
                    <button class="btn btn-outline-primary">Avaliar</button>
                </form>
            </div>
        </aside>
    </div>
</section>
