<section class="page-band"><div class="container d-flex justify-content-between align-items-center"><div><h1>Painel do organizador</h1><p>Indicadores, campeonatos e gestao de inscricoes.</p></div><a class="btn btn-warning" href="<?= url('/organizador/campeonatos/novo') ?>"><i class="fa-solid fa-plus"></i> Novo</a></div></section>
<section class="container py-4">
    <div class="organizer-shell">
        <aside class="app-sidebar" aria-label="Menu do organizador">
            <a href="<?= url('/organizador') ?>"><i class="fa-solid fa-chart-line"></i>Resumo</a>
            <a href="<?= url('/organizador') ?>"><i class="fa-solid fa-trophy"></i>Campeonatos</a>
            <a href="<?= url('/organizador/inscricoes') ?>"><i class="fa-solid fa-clipboard-list"></i>Inscricoes</a>
            <a href="<?= url('/organizador/relatorios/inscricoes') ?>"><i class="fa-solid fa-file-csv"></i>Relatorios</a>
            <a href="<?= url('/notificacoes') ?>"><i class="fa-solid fa-bell"></i>Notificacoes</a>
        </aside>
        <div>
            <div class="metric-grid">
                <div><strong><?= (int) ($stats['total_championships'] ?? 0) ?></strong><span>Total</span></div><div><strong><?= (int) ($stats['active_events'] ?? 0) ?></strong><span>Ativos</span></div><div><strong><?= (int) ($stats['total_registrations'] ?? 0) ?></strong><span>Inscricoes</span></div><div><strong>R$ <?= number_format((float) ($stats['revenue'] ?? 0), 2, ',', '.') ?></strong><span>Arrecadado</span></div>
            </div>
            <div class="panel">
                <h2>Campeonatos</h2>
                <div class="table-responsive">
                    <table class="table align-middle">
                        <tbody>
                            <?php foreach ($championships as $c): ?>
                                <tr>
                                    <td><img class="table-thumb" src="<?= e(championship_image_url($c)) ?>" alt="<?= e($c['name']) ?>"></td>
                                    <td><?= e($c['name']) ?><br><small><?= e($c['sport_name']) ?></small></td>
                                    <td><span class="badge text-bg-secondary"><?= e($c['editorial_status'] ?? 'draft') ?></span><br><small><?= e($c['status']) ?></small></td>
                                    <td><?= (int) $c['registrations_count'] ?> inscritos</td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <a class="btn btn-sm btn-outline-primary" href="<?= url('/organizador/campeonatos/' . $c['id'] . '/editar') ?>">Editar</a>
                                            <a class="btn btn-sm btn-primary" href="<?= url('/organizador/campeonatos/' . $c['id'] . '/gerenciar') ?>">Gerenciar</a>
                                            <?php if (in_array($c['editorial_status'] ?? 'draft', ['draft', 'rejected'], true)): ?>
                                                <form method="post" action="<?= url('/organizador/campeonatos/' . $c['id'] . '/enviar-aprovacao') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-warning">Enviar para aprovacao</button></form>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            <?= !empty($c['rules_file']) ? 'Regulamento anexado' : 'Regulamento pendente' ?> ·
                                            <?= !empty($c['location']) ? 'Local definido' : 'Local pendente' ?> ·
                                            <?= (int) $c['registrations_count'] > 0 ? 'Inscricoes recebidas' : 'Sem inscricoes' ?>
                                        </small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="panel mt-4"><h2>Indicadores</h2><canvas id="organizerChart" data-active="<?= (int) ($stats['active_events'] ?? 0) ?>" data-closed="<?= (int) ($stats['closed_events'] ?? 0) ?>"></canvas><a class="btn btn-outline-secondary mt-3" href="<?= url('/organizador/relatorios/inscricoes') ?>">Exportar CSV</a><a class="btn btn-outline-secondary mt-3" href="<?= url('/organizador/inscricoes') ?>">Gerenciar inscricoes</a></div>
        </div>
    </div>
</section>
