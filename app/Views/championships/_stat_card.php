<article class="sc-stat-card">
    <div class="sc-stat-icon" aria-hidden="true"><i class="<?= e($icon ?? 'fa-solid fa-chart-simple') ?>"></i></div>
    <div>
        <strong><?= e((string) ($value ?? 0)) ?></strong>
        <span><?= e($label ?? '') ?></span>
    </div>
</article>
