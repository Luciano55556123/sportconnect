<section class="page-band"><div class="container d-flex justify-content-between align-items-center"><div><h1>Painel do organizador</h1><p>Indicadores, campeonatos e gestao de inscricoes.</p></div><a class="btn btn-warning" href="<?= url('/organizador/campeonatos/novo') ?>"><i class="fa-solid fa-plus"></i> Novo</a></div></section>
<section class="container py-4">
    <div class="metric-grid">
        <div><strong><?= (int) ($stats['total_championships'] ?? 0) ?></strong><span>Total</span></div>
        <div><strong><?= (int) ($stats['active_events'] ?? 0) ?></strong><span>Ativos</span></div>
        <div><strong><?= (int) ($stats['total_registrations'] ?? 0) ?></strong><span>Inscricoes</span></div>
        <div><strong>R$ <?= number_format((float) ($stats['revenue'] ?? 0), 2, ',', '.') ?></strong><span>Arrecadado</span></div>
    </div>
    <div class="row g-4 mt-2">
        <div class="col-lg-7">
            <div class="panel">
                <h2>Campeonatos</h2>
                <div class="table-responsive">
                    <table class="table">
                        <tbody>
                            <?php foreach ($championships as $c): ?>
                                <tr>
                                    <td><?= e($c['name']) ?><br><small><?= e($c['sport_name']) ?></small></td>
                                    <td><?= e($c['status']) ?></td>
                                    <td><?= (int) $c['registrations_count'] ?> inscritos</td>
                                    <td class="text-nowrap">
                                        <a class="btn btn-sm btn-outline-primary" href="<?= url('/organizador/campeonatos/' . $c['id'] . '/gerenciar') ?>">Gerenciar competicao</a>
                                        <a class="btn btn-sm btn-outline-secondary" href="<?= url('/organizador/campeonatos/' . $c['id'] . '/editar') ?>">Editar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if (!$championships): ?><tr><td class="text-muted">Nenhum campeonato cadastrado.</td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="panel mb-4">
                <h2>Graficos</h2>
                <canvas id="organizerChart" data-active="<?= (int) ($stats['active_events'] ?? 0) ?>" data-closed="<?= (int) ($stats['closed_events'] ?? 0) ?>"></canvas>
                <a class="btn btn-outline-secondary mt-3" href="<?= url('/organizador/relatorios/inscricoes') ?>">Exportar CSV</a>
                <a class="btn btn-outline-secondary mt-3" href="<?= url('/organizador/inscricoes') ?>">Gerenciar inscricoes</a>
            </div>
            <div class="panel">
                <h2>Notificacoes</h2>
                <?php foreach (($notifications ?? []) as $notification): ?>
                    <?php $link = $notification['link'] ?? ''; ?>
                    <?php if ($link !== ''): ?>
                        <a class="note notification-link" href="<?= url($link) ?>">
                            <strong><?= e($notification['title'] ?? 'Notificacao') ?></strong>
                            <span><?= e($notification['message']) ?></span>
                        </a>
                    <?php else: ?>
                        <p class="note"><strong><?= e($notification['title'] ?? 'Notificacao') ?></strong><br><?= e($notification['message']) ?></p>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if (empty($notifications)): ?><p class="text-muted mb-0">Nenhuma notificacao recente.</p><?php endif; ?>
            </div>
        </div>
    </div>
</section>
