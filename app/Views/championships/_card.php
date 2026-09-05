<div class="col-md-6 col-xl-4">
    <?php $status = (string) ($event['status'] ?? ''); ?>
    <article class="event-card h-100">
        <div class="event-image"><img src="<?= url($event['image'] ?? 'assets/img/default-event.svg') ?>" alt=""></div>
        <div class="p-3">
            <div class="d-flex justify-content-between gap-2 mb-2">
                <span class="badge text-bg-primary"><?= e($event['sport_name']) ?></span>
                <span class="d-flex align-items-center gap-2">
                    <?php if ($status === 'encerrado'): ?><span class="badge text-bg-secondary">Encerrado</span><?php endif; ?>
                    <span class="small text-muted"><?= e(date('d/m/Y', strtotime($event['event_date']))) ?></span>
                </span>
            </div>
            <h3><?= e($event['name']) ?></h3>
            <p class="text-muted mb-2"><i class="fa-solid fa-location-dot me-1"></i><?= e($event['city']) ?> - <?= e($event['location']) ?></p>
            <?php if (isset($event['compatibility'])): ?>
                <div class="compat mb-3"><span style="width: <?= (int) $event['compatibility'] ?>%"></span></div>
                <strong><?= (int) $event['compatibility'] ?>% de compatibilidade</strong>
            <?php endif; ?>
            <div class="d-flex justify-content-between align-items-center mt-3">
                <span class="price">R$ <?= number_format((float) $event['registration_fee'], 2, ',', '.') ?></span>
                <a class="btn btn-sm btn-outline-primary" href="<?= url('/campeonatos/' . $event['id']) ?>">Detalhes</a>
            </div>
        </div>
    </article>
</div>
