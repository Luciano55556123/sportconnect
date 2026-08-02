<section class="page-band"><div class="container"><h1>Calendario esportivo</h1><p>Eventos organizados por data.</p></div></section>
<section class="container py-4">
    <div class="calendar-list">
        <?php foreach ($events as $event): ?>
            <a href="<?= url('/campeonatos/' . $event['id']) ?>" class="calendar-item">
                <time><?= e(date('d/m', strtotime($event['event_date']))) ?></time>
                <div><strong><?= e($event['name']) ?></strong><span><?= e($event['sport_name']) ?> em <?= e($event['city']) ?></span></div>
            </a>
        <?php endforeach; ?>
    </div>
</section>
