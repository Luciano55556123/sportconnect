<section class="page-band"><div class="container"><h1>Painel administrativo</h1><p>Controle profissional da plataforma SportConnect.</p></div></section>
<section class="container py-4">
    <div class="admin-shell">
        <aside class="app-sidebar" aria-label="Menu administrativo">
            <a href="<?= url('/admin') ?>"><i class="fa-solid fa-gauge-high"></i>Resumo</a>
            <a href="<?= url('/admin/solicitacoes-organizadores') ?>"><i class="fa-solid fa-user-check"></i>Organizadores</a>
            <a href="<?= url('/admin/campeonatos-pendentes') ?>"><i class="fa-solid fa-clipboard-check"></i>Campeonatos</a>
            <a href="<?= url('/admin/denuncias') ?>"><i class="fa-solid fa-flag"></i>Denuncias</a>
            <a href="<?= url('/admin/usuarios') ?>"><i class="fa-solid fa-users"></i>Usuarios</a>
        </aside>
        <div>
            <div class="metric-grid">
                <div><strong><?= count($users) ?></strong><span>Usuarios</span></div>
                <div><strong><?= count(array_filter($users, fn ($u) => ($u['role'] ?? '') === 'athlete')) ?></strong><span>Atletas</span></div>
                <div><strong><?= (int) ($organizerRequestCounts['pending'] ?? 0) ?></strong><span>Organizadores pendentes</span></div>
                <div><strong><?= (int) ($organizerRequestCounts['approved'] ?? 0) ?></strong><span>Organizadores aprovados</span></div>
                <div><strong><?= (int) ($championshipCounts['pending'] ?? 0) ?></strong><span>Campeonatos pendentes</span></div>
                <div><strong><?= (int) ($championshipCounts['published'] ?? 0) ?></strong><span>Campeonatos publicados</span></div>
                <div><strong><?= (int) ($openReports ?? 0) ?></strong><span>Denuncias abertas</span></div>
            </div>
            <div class="panel mt-4">
                <h2>Atalhos de moderacao</h2>
                <div class="admin-links">
                    <a href="<?= url('/admin/solicitacoes-organizadores') ?>"><i class="fa-solid fa-user-check"></i>Aprovar organizadores</a>
                    <a href="<?= url('/admin/campeonatos-pendentes') ?>"><i class="fa-solid fa-clipboard-check"></i>Aprovar campeonatos</a>
                    <a href="<?= url('/admin/denuncias') ?>"><i class="fa-solid fa-flag"></i>Analisar denuncias</a>
                    <a href="<?= url('/admin/pagamentos') ?>"><i class="fa-solid fa-money-check-dollar"></i>Ver pagamentos</a>
                    <a href="<?= url('/admin/historico') ?>"><i class="fa-solid fa-clock-rotate-left"></i>Ver historico</a>
                </div>
            </div>
        </div>
    </div>
</section>
