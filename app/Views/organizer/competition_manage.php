<?php
$isOrganizerManage = true;
$progress = (int) (($competitionData['summary']['progress'] ?? 0));
?>
<section class="manage-hero">
    <div class="container">
        <div class="manage-hero-inner">
            <div>
                <span class="badge text-bg-warning mb-2"><?= e($championship['status'] ?? '') ?></span>
                <h1><?= e($championship['name'] ?? '') ?></h1>
                <div class="manage-meta">
                    <span><i class="fa-solid fa-trophy"></i><?= e($championship['sport_name'] ?? '') ?></span>
                    <span><i class="fa-solid fa-layer-group"></i><?= e($championship['modality'] ?? '') ?></span>
                    <span><i class="fa-solid fa-calendar"></i><?= !empty($championship['event_date']) ? e(date('d/m/Y', strtotime($championship['event_date']))) : 'Data a definir' ?></span>
                    <span><i class="fa-solid fa-location-dot"></i><?= e($championship['location'] ?? '') ?></span>
                </div>
            </div>
            <div class="manage-actions">
                <a class="btn btn-light" href="<?= url('/campeonatos/' . $championship['id']) ?>">Pagina publica</a>
                <a class="btn btn-warning" href="<?= url('/organizador/campeonatos/' . $championship['id'] . '/editar') ?>">Editar campeonato</a>
            </div>
        </div>
        <div class="manage-progress">
            <div><strong><?= $progress ?>%</strong><span>progresso do campeonato</span></div>
            <div class="progress"><div class="progress-bar" style="width: <?= $progress ?>%"></div></div>
        </div>
    </div>
</section>

<section class="container py-4">
    <?php require BASE_PATH . '/app/Views/championships/_competition.php'; ?>
    <?php if (array_sum(array_map('intval', $competitionCounts ?? [])) === 0): ?>
        <div class="panel">
            <h2>Nenhum dado de competicao encontrado</h2>
            <p class="text-muted mb-0">Este campeonato ainda nao possui equipes, partidas, classificacao ou eventos registrados.</p>
        </div>
    <?php endif; ?>
</section>
