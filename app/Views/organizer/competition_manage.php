<?php
$isOrganizerManage = true;
$progress = (int) (($competitionData['summary']['progress'] ?? 0));
$completed = (int) (($competitionData['summary']['counts']['completed_matches'] ?? 0));
$matchesTotal = (int) (($competitionData['summary']['counts']['matches'] ?? 0));
?>
<section class="sc-manage-hero">
    <div class="container">
        <div class="sc-manage-grid">
            <div class="sc-manage-copy">
                <a class="sc-back-link" href="<?= url('/organizador') ?>"><i class="fa-solid fa-arrow-left"></i> Voltar ao painel</a>
                <span class="sc-status-chip"><?= e($championship['status'] ?? '') ?></span>
                <h1><?= e($championship['name'] ?? '') ?></h1>
                <div class="sc-hero-meta">
                    <span><i class="fa-solid fa-trophy"></i><?= e($championship['sport_name'] ?? '') ?></span>
                    <span><i class="fa-solid fa-layer-group"></i><?= e($championship['modality'] ?? '') ?></span>
                    <span><i class="fa-solid fa-calendar"></i><?= !empty($championship['event_date']) ? e(date('d/m/Y', strtotime($championship['event_date']))) : 'Data a definir' ?></span>
                    <span><i class="fa-solid fa-location-dot"></i><?= e($championship['location'] ?? '') ?></span>
                    <span><i class="fa-solid fa-user-tie"></i><?= e($championship['organizer_name'] ?? 'Organizador') ?></span>
                </div>
            </div>
            <div class="sc-manage-media">
                <img src="<?= url($championship['image'] ?? 'assets/img/default-event.svg') ?>" alt="Imagem do campeonato <?= e($championship['name'] ?? '') ?>">
            </div>
        </div>
        <div class="sc-manage-actions" aria-label="Acoes do campeonato">
            <a class="btn btn-warning" href="<?= url('/organizador/campeonatos/' . $championship['id'] . '/editar') ?>"><i class="fa-solid fa-pen"></i> Editar campeonato</a>
            <a class="btn btn-light" href="#comp-matches" data-bs-toggle="pill" data-bs-target="#comp-matches"><i class="fa-solid fa-calendar-days"></i> Gerenciar partidas</a>
            <a class="btn btn-light" href="<?= url('/organizador/inscricoes') ?>"><i class="fa-solid fa-id-card"></i> Ver inscricoes</a>
            <a class="btn btn-outline-light" href="<?= url('/campeonatos/' . $championship['id']) ?>"><i class="fa-solid fa-eye"></i> Pagina publica</a>
            <button class="btn btn-outline-light" type="button" onclick="navigator.share ? navigator.share({title: document.title, url: '<?= url('/campeonatos/' . $championship['id']) ?>'}) : navigator.clipboard.writeText('<?= url('/campeonatos/' . $championship['id']) ?>')"><i class="fa-solid fa-share-nodes"></i> Compartilhar</button>
        </div>
        <div class="sc-manage-progress">
            <div><strong><?= $progress ?>%</strong><span><?= $completed ?> de <?= $matchesTotal ?> partidas concluidas</span></div>
            <div class="sc-progress-track"><span style="--progress: <?= $progress ?>%"></span></div>
        </div>
    </div>
</section>

<section class="container py-4">
    <?php require BASE_PATH . '/app/Views/championships/_competition.php'; ?>
    <?php if (array_sum(array_map('intval', $competitionCounts ?? [])) === 0): ?>
        <article class="sc-empty-state">
            <i class="fa-solid fa-database"></i>
            <strong>Nenhum dado de competicao encontrado</strong>
            <span>Este campeonato ainda nao possui equipes, partidas, classificacao ou eventos registrados.</span>
        </article>
    <?php endif; ?>
</section>
