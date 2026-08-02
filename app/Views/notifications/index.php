<section class="page-band"><div class="container"><h1>Notificacoes</h1><p>Acompanhe atualizacoes da plataforma, inscricoes, campeonatos e resultados.</p></div></section>
<section class="container py-4">
    <div class="notification-list-pro">
        <?php foreach ($notifications as $notification): ?>
            <div class="notification-row notification-item-pro <?= empty($notification['read_at']) && empty($notification['is_read']) ? 'unread' : '' ?>">
                <span class="notification-icon"><i class="fa-solid fa-bell"></i></span>
                <div>
                    <strong><?= e($notification['title'] ?? 'Notificacao') ?></strong>
                    <p><?= e($notification['message']) ?></p>
                    <?php if (!empty($notification['link'])): ?><a href="<?= e(url($notification['link'])) ?>">Abrir</a><?php endif; ?>
                    <small><?= e(date('d/m/Y H:i', strtotime($notification['created_at']))) ?></small>
                </div>
                <?php if (empty($notification['read_at']) && empty($notification['is_read'])): ?>
                    <form method="post" action="<?= url('/notificacoes/' . $notification['id'] . '/ler') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-outline-primary">Marcar como lida</button></form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <?php if (!$notifications): ?><p class="text-muted mb-0">Nenhuma notificacao.</p><?php endif; ?>
    </div>
</section>
