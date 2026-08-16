<section class="page-band">
    <div class="container d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
        <div>
            <h1>Gestao do campeonato</h1>
            <p><?= e($championship['name'] ?? '') ?></p>
        </div>
        <div class="d-flex gap-2">
            <a class="btn btn-outline-primary" href="<?= url('/campeonatos/' . $championship['id']) ?>">Ver pagina</a>
            <a class="btn btn-warning" href="<?= url('/organizador/campeonatos/' . $championship['id'] . '/editar') ?>">Editar campeonato</a>
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
