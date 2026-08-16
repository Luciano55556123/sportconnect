<article class="sc-team-card">
    <div class="sc-team-shield">
        <?php if (!empty($team['shield'])): ?>
            <img src="<?= url($team['shield']) ?>" alt="Escudo de <?= e($team['name'] ?? 'equipe') ?>">
        <?php else: ?>
            <i class="fa-solid fa-shield-halved" aria-hidden="true"></i>
        <?php endif; ?>
    </div>
    <div class="sc-team-info">
        <strong><?= e($team['name'] ?? '') ?></strong>
        <span><?= e($team['city'] ?? 'Cidade nao informada') ?></span>
        <small><?= (int) ($team['athletes_count'] ?? 0) ?> atletas cadastrados</small>
    </div>
    <span class="sc-status-chip"><?= e($team['status'] ?? 'status') ?></span>
</article>
