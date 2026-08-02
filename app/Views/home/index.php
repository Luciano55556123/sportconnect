<section class="hero">
    <div class="container hero-content">
        <div class="row align-items-center g-4">
            <div class="col-lg-7">
                <span class="eyebrow">Campeonatos regionais em um so lugar</span>
                <h1>SportConnect</h1>
                <p class="lead">Encontre competicoes reais, acompanhe tabelas e conecte atletas, equipes e organizadores com seguranca.</p>
                <form class="hero-search" action="<?= url('/campeonatos') ?>" method="get">
                    <label><span>Buscar</span><input class="form-control form-control-lg" name="q" placeholder="Nome, cidade ou organizador"></label>
                    <label><span>Cidade</span><input class="form-control form-control-lg" name="city" placeholder="Sua cidade"></label>
                    <label><span>Esporte</span><select class="form-select form-select-lg" name="sport_id"><option value="">Todos</option><?php foreach ($sports as $sport): ?><option value="<?= (int) $sport['id'] ?>"><?= e($sport['name']) ?></option><?php endforeach; ?></select></label>
                    <button class="btn btn-warning btn-lg"><i class="fa-solid fa-magnifying-glass"></i> Pesquisar</button>
                </form>
                <div class="hero-actions"><a class="btn btn-outline-light" href="<?= url('/solicitar-organizador') ?>">Quero organizar</a><a class="btn btn-light" href="<?= url('/cadastro') ?>">Criar conta</a></div>
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

<section class="sports-categories-section">
    <div class="container">
        <div class="sports-categories-heading">
            <h2>Categorias</h2>
            <a href="<?= url('/campeonatos') ?>">Explore todas as categorias</a>
        </div>

        <?php if (empty($categorySports)): ?>
            <div class="empty-state">Nenhuma categoria esportiva disponivel no momento.</div>
        <?php else: ?>
            <div class="sports-category-grid">
                <?php foreach ($categorySports as $sport): ?>
                    <a class="sport-category-card"
                       href="<?= url('/campeonatos?sport_id=' . (int) $sport['id']) ?>"
                       aria-label="Ver campeonatos de <?= e($sport['name']) ?>">
                        <img src="<?= e(sport_image_url($sport)) ?>" alt="<?= e($sport['name']) ?>" loading="lazy">
                        <span class="sport-category-overlay" aria-hidden="true"></span>
                        <span class="sport-category-name"><?= e($sport['name']) ?></span>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>

<section class="container pb-4">
    <div class="section-heading"><div><span>Agenda</span><h2>Proximos campeonatos</h2></div><a href="<?= url('/campeonatos?sort=recentes') ?>">Mais recentes</a></div>
    <div class="row g-4">
        <?php foreach ($featured as $event): require BASE_PATH . '/app/Views/championships/_card.php'; endforeach; ?>
        <?php if (!$featured): ?><div class="col-12"><div class="empty-state">Nenhum campeonato publicado no momento.</div></div><?php endif; ?>
    </div>
</section>

<section class="container py-4">
    <div class="section-heading"><div><span>Inscricoes</span><h2>Campeonatos com vagas abertas</h2></div><a href="<?= url('/campeonatos?registrations_open=1') ?>">Ver vagas</a></div>
    <div class="row g-4">
        <?php foreach ($openRegistrations as $event): require BASE_PATH . '/app/Views/championships/_card.php'; endforeach; ?>
        <?php if (!$openRegistrations): ?><div class="col-12"><div class="empty-state">Nenhuma inscricao aberta agora.</div></div><?php endif; ?>
    </div>
</section>

<section class="container py-4">
    <div class="workflow-grid">
        <article><i class="fa-solid fa-magnifying-glass"></i><h2>Para atletas</h2><p>Pesquise campeonatos, salve favoritos, envie inscricoes e acompanhe resultados.</p></article>
        <article><i class="fa-solid fa-clipboard-check"></i><h2>Para organizadores</h2><p>Solicite aprovacao, publique eventos, valide inscricoes e gerencie partidas.</p></article>
        <article><i class="fa-solid fa-shield-halved"></i><h2>Como funciona</h2><p>Fluxo com revisao administrativa, notificacoes internas e paginas publicas padronizadas.</p></article>
    </div>
</section>

<section class="cta-band">
    <div class="container d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3">
        <div><span class="eyebrow">Plataforma regional</span><h2>Participe do proximo campeonato da sua cidade.</h2></div>
        <a class="btn btn-warning btn-lg" href="<?= url('/cadastro') ?>">Comecar agora</a>
    </div>
</section>
