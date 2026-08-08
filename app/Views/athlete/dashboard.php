<section class="page-band"><div class="container"><h1>Painel do atleta</h1><p>Perfil, notificacoes e recomendacoes inteligentes.</p></div></section>
<section class="container py-4">
    <div class="row g-4">
        <div class="col-lg-4">
            <form class="panel" method="post" action="<?= url('/atleta/perfil') ?>">
                <?= csrf_field() ?><h2>Perfil</h2>
                <input class="form-control" name="name" value="<?= e($user['name']) ?>" required>
                <input class="form-control" name="phone" value="<?= e($user['phone']) ?>" placeholder="Telefone">
                <input class="form-control" name="city" value="<?= e($user['city']) ?>" placeholder="Cidade">
                <input class="form-control" type="date" name="birth_date" value="<?= e($user['birth_date']) ?>">
                <input class="form-control" type="number" step="0.01" name="preferred_price_max" value="<?= e($user['preferred_price_max']) ?>" placeholder="Valor preferido">
                <div class="checks mt-2"><?php foreach ($sports as $sport): ?><label><input type="checkbox" name="sports[]" value="<?= $sport['id'] ?>" <?= in_array((int) $sport['id'], $favoriteSports, true) ? 'checked' : '' ?>> <?= e($sport['name']) ?></label><?php endforeach; ?></div>
                <button class="btn btn-primary w-100 mt-3">Salvar</button>
            </form>
        </div>
        <div class="col-lg-8">
            <?php if (($user['role'] ?? '') !== 'organizer'): ?>
                <div class="panel mb-4">
                    <div class="section-heading mb-2"><h2>Minha Solicitacao de Organizador</h2><?php if (!$organizerRequest || $organizerRequest['status'] !== 'pending'): ?><form method="post" action="<?= url('/organizador/solicitar') ?>"><?= csrf_field() ?><button class="btn btn-primary">Solicitar perfil de Organizador</button></form><?php endif; ?></div>
                    <?php if ($organizerRequest): ?>
                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <tbody>
                                    <tr><th>Status atual</th><td><?= e($organizerRequest['status']) ?></td></tr>
                                    <tr><th>Data da solicitacao</th><td><?= e($organizerRequest['created_at']) ?></td></tr>
                                    <?php if (!empty($organizerRequest['approved_at'])): ?><tr><th>Data da aprovacao</th><td><?= e($organizerRequest['approved_at']) ?></td></tr><?php endif; ?>
                                    <?php if (!empty($organizerRequest['rejection_reason'])): ?><tr><th>Motivo da rejeicao</th><td><?= e($organizerRequest['rejection_reason']) ?></td></tr><?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted mb-0">Voce ainda nao enviou uma solicitacao.</p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
            <div class="panel mb-4"><h2>Notificacoes</h2><?php foreach ($notifications as $note): ?><p class="note"><?= e($note['message']) ?></p><?php endforeach; ?><?php if (!$notifications): ?><p class="text-muted">Nenhuma notificacao ainda.</p><?php endif; ?></div>
            <div class="section-heading"><h2>Melhores recomendacoes</h2><a href="<?= url('/atleta/recomendacoes') ?>">Ver lista completa</a></div>
            <div class="row g-3"><?php foreach (array_slice($recommendations, 0, 4) as $event): require BASE_PATH . '/app/Views/championships/_card.php'; endforeach; ?></div>
        </div>
    </div>
</section>
