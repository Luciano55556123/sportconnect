<section class="hero">
    <div class="container hero-content">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="badge text-bg-warning mb-3">Campeonatos regionais em um so lugar</span>
                <h1>Ponto Competitivo</h1>
                <p class="lead">Encontre eventos esportivos, inscreva sua equipe e receba recomendacoes inteligentes conforme seu perfil.</p>
                <form class="search-bar" action="<?= url('/campeonatos') ?>" method="get">
                    <input class="form-control form-control-lg" name="q" placeholder="Buscar por nome, cidade ou organizador">
                    <button class="btn btn-warning btn-lg"><i class="fa-solid fa-magnifying-glass"></i></button>
                </form>
            </div>
            <div class="col-lg-5">
                <div class="hero-panel">
                    <div class="stat"><strong><?= count($featured) ?></strong><span>eventos ativos</span></div>
                    <div class="stat"><strong><?= count($sports) ?></strong><span>modalidades</span></div>
                    <div class="stat"><strong>99%</strong><span>compatibilidade maxima</span></div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="container py-5">
    <div class="section-heading"><h2>Categorias por esporte</h2><a href="<?= url('/campeonatos') ?>">Ver todos</a></div>
    <div class="sport-grid">
        <?php foreach ($sports as $sport): ?>
            <a class="sport-chip" href="<?= url('/campeonatos?sport_id=' . $sport['id']) ?>"><i class="fa-solid fa-medal"></i><?= e($sport['name']) ?></a>
        <?php endforeach; ?>
    </div>
</section>

<section class="container pb-4">
    <div class="section-heading"><h2>Proximos campeonatos</h2><span>Eventos em destaque</span></div>
    <div class="row g-4">
        <?php foreach ($featured as $event): require BASE_PATH . '/app/Views/championships/_card.php'; endforeach; ?>
    </div>
</section>

<section class="container py-4">
    <div class="section-heading"><h2>Mais procurados</h2><span>Ordenados por visualizacoes</span></div>
    <div class="row g-4">
        <?php foreach ($mostViewed as $event): require BASE_PATH . '/app/Views/championships/_card.php'; endforeach; ?>
    </div>
</section>
