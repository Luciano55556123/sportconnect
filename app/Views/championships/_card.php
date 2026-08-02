<div class="col-md-6 col-xl-4">
    <article class="event-card h-100">
        <div class="event-image"><img src="<?= e(championship_image_url($event)) ?>" alt="<?= e($event['name'] ?? 'Campeonato') ?>"></div>
        <div class="p-3">
            <div class="d-flex justify-content-between gap-2 mb-2"><span class="badge text-bg-primary"><?= e($event['sport_name']) ?></span><span class="badge text-bg-light"><?= e($event['editorial_status'] ?? $event['status'] ?? 'publicado') ?></span></div>
            <h3><?= e($event['name']) ?></h3>
            <p class="text-muted mb-2"><i class="fa-solid fa-location-dot me-1"></i><?= e($event['city']) ?> - <?= e($event['location']) ?></p>
            <div class="card-meta">
                <span><i class="fa-regular fa-calendar"></i><?= e(date('d/m/Y', strtotime($event['event_date']))) ?></span>
                <span><i class="fa-solid fa-users"></i><?= (int) ($event['registrations_count'] ?? 0) ?> / <?= (int) ($event['maximum_registrations'] ?? $event['max_participants'] ?? 0) ?></span>
            </div>
            <?php if (isset($event['compatibility'])): ?>
                <div class="compat mb-3"><span style="width: <?= (int) $event['compatibility'] ?>%"></span></div>
                <strong><?= (int) $event['compatibility'] ?>% de compatibilidade</strong>
            <?php endif; ?>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <span class="price"><?= (float) $event['registration_fee'] > 0 ? 'R$ ' . number_format((float) $event['registration_fee'], 2, ',', '.') : 'Gratuito' ?></span>
                <a class="btn btn-sm btn-outline-primary" href="<?= url('/campeonatos/' . $event['id']) ?>">Ver detalhes</a>
            </div>
        </div>
    </article>
</div>
