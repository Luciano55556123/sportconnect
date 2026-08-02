<section class="page-band"><div class="container"><h1><?= e($team['name']) ?></h1><p><?= e($championship['name']) ?></p></div></section>
<section class="container py-4">
    <div class="panel">
        <div class="info-grid">
            <span><i class="fa-solid fa-city"></i><?= e($team['city'] ?? 'Nao informado') ?></span>
            <span><i class="fa-solid fa-user-tie"></i><?= e($team['responsible_name'] ?? 'Nao informado') ?></span>
            <span><i class="fa-solid fa-phone"></i><?= e($team['responsible_phone'] ?? 'Nao informado') ?></span>
            <span><i class="fa-solid fa-signal"></i><?= e($team['status']) ?></span>
        </div>
    </div>
    <div class="panel mt-3 table-responsive">
        <h2>Elenco</h2>
        <?php if (!$athletes): ?><p class="text-muted">Nenhum atleta cadastrado.</p><?php else: ?>
            <table class="table align-middle"><thead><tr><th>#</th><th>Nome</th><th>Posicao</th><th>Cidade</th><th>Status</th></tr></thead><tbody><?php foreach ($athletes as $athlete): ?><tr><td><?= e((string) ($athlete['shirt_number'] ?? '')) ?></td><td><?= e($athlete['name']) ?></td><td><?= e($athlete['position'] ?? '') ?></td><td><?= e($athlete['city'] ?? '') ?></td><td><?= e($athlete['status']) ?></td></tr><?php endforeach; ?></tbody></table>
        <?php endif; ?>
    </div>
</section>
