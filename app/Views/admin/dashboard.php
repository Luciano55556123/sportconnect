<?php $pendingCount = (int) ($pendingOrganizerRequestsCount ?? count($organizerRequests ?? [])); ?>
<section class="page-band"><div class="container"><h1>Painel administrativo</h1><p>Controle geral da plataforma.</p></div></section>
<section class="container py-4">
    <div class="metric-grid">
        <div><strong><?= count($users) ?></strong><span>Usuarios</span></div>
        <div><strong><?= count($championships) ?></strong><span>Campeonatos</span></div>
        <div><strong><?= count($sports) ?></strong><span>Esportes</span></div>
        <div><strong><?= $pendingCount ?></strong><span>Solicitacoes pendentes</span></div>
    </div>

    <div class="admin-alert mt-4 <?= $pendingCount > 0 ? 'has-pending' : '' ?>">
        <div>
            <h2><i class="fa-solid fa-bell me-2"></i>Solicitacoes de organizador</h2>
            <?php if ($pendingCount > 0): ?>
                <p><span class="badge text-bg-warning"><?= $pendingCount ?></span> <?= $pendingCount === 1 ? 'solicitacao aguardando analise' : 'solicitacoes aguardando analise' ?></p>
            <?php else: ?>
                <p>Nenhuma solicitacao pendente.</p>
            <?php endif; ?>
        </div>
        <a class="btn <?= $pendingCount > 0 ? 'btn-primary' : 'btn-outline-secondary' ?>" href="<?= url('/admin/solicitacoes-organizador') ?>">Analisar solicitacoes</a>
    </div>

    <div class="row g-4 mt-1">
        <div class="col-lg-8">
            <div class="panel">
                <h2>Recursos</h2>
                <div class="admin-links">
                    <a href="<?= url('/admin/solicitacoes-organizador') ?>">Solicitacoes de Organizador<?php if ($pendingCount > 0): ?> <span class="badge text-bg-warning"><?= $pendingCount ?></span><?php endif; ?></a>
                    <?php foreach (['usuarios','organizadores','esportes','categorias','campeonatos','inscricoes','avaliacoes','comentarios','notificacoes','relatorios'] as $r): ?><a href="<?= url('/admin/' . $r) ?>"><?= e(ucfirst($r)) ?></a><?php endforeach; ?>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
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
