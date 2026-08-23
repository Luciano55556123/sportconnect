<section class="page-band"><div class="container"><h1>Inscricoes recebidas</h1></div></section>
<section class="container py-4">
    <?php $activeFilter = $filter ?? 'all'; ?>
    <div class="d-flex flex-wrap gap-2 mb-3">
        <a class="btn btn-sm <?= $activeFilter === 'pending' ? 'btn-primary' : 'btn-outline-primary' ?>" href="<?= url('/organizador/inscricoes?status=pending') ?>">Pendentes</a>
        <a class="btn btn-sm <?= $activeFilter === 'approved' ? 'btn-primary' : 'btn-outline-primary' ?>" href="<?= url('/organizador/inscricoes?status=approved') ?>">Aprovadas</a>
        <a class="btn btn-sm <?= $activeFilter === 'rejected' ? 'btn-primary' : 'btn-outline-primary' ?>" href="<?= url('/organizador/inscricoes?status=rejected') ?>">Rejeitadas</a>
        <a class="btn btn-sm <?= $activeFilter === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>" href="<?= url('/organizador/inscricoes') ?>">Todas</a>
    </div>
    <div class="table-responsive panel">
        <table class="table align-middle">
            <thead><tr><th>Campeonato</th><th>Participante</th><th>Data</th><th>Pagamento</th><th>Status</th><th>Acoes</th></tr></thead>
            <tbody>
            <?php
            $labels = [
                'aguardando_pagamento' => 'Aguardando pagamento',
                'pagamento_em_analise' => 'Pagamento em analise',
                'awaiting_receipt' => 'Aguardando pagamento',
                'under_review' => 'Pagamento em analise',
                'paid' => 'Aprovado',
                'rejected' => 'Rejeitado',
                'aprovado' => 'Aprovado',
                'rejeitado' => 'Rejeitado',
                'pendente' => 'Pendente',
                'confirmada' => 'Confirmada',
                'pagamento_rejeitado' => 'Pagamento rejeitado',
                'cancelado' => 'Cancelada',
            ];
            ?>
            <?php if (!$registrations): ?>
                <tr><td colspan="6" class="text-center text-muted py-4">Nenhuma inscricao recebida.</td></tr>
            <?php endif; ?>
            <?php foreach ($registrations as $r): ?>
                <?php
                    $paid = !empty($r['requires_payment']) && (float) ($r['registration_fee'] ?? 0) > 0;
                    $registrationStatus = (string) ($r['status'] ?? '');
                    $pending = $registrationStatus === 'pendente';
                ?>
                <tr>
                    <td><?= e($r['championship_name']) ?><br><small>R$ <?= number_format((float) ($r['payment_amount'] ?? $r['registration_fee']), 2, ',', '.') ?></small></td>
                    <td><?= e($r['name']) ?><br><small><?= e($r['email']) ?> | <?= e($r['phone']) ?></small><br><small><?= e($r['team']) ?></small></td>
                    <td><?= e(date('d/m/Y H:i', strtotime($r['created_at']))) ?></td>
                    <td>
                        <?php if ($paid): ?>
                            <?php $paymentStatus = $r['payment_status'] ?? 'awaiting_receipt'; ?>
                            <span class="badge text-bg-info"><?= e($labels[$paymentStatus] ?? $paymentStatus) ?></span>
                            <?php if (!empty($r['receipt_path'])): ?>
                                <br><a class="btn btn-sm btn-outline-secondary mt-2" target="_blank" href="<?= url('/organizador/inscricoes/' . $r['id'] . '/comprovante') ?>">Visualizar comprovante</a>
                            <?php else: ?>
                                <br><small class="text-muted">Comprovante enviado pelo WhatsApp.</small>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge text-bg-success">Gratuito</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge text-bg-secondary"><?= e($labels[$r['status']] ?? $r['status']) ?></span>
                        <?php if ($paid && (($r['payment_status'] ?? '') !== 'paid')): ?>
                            <br><small class="text-muted">Inscricao pendente - pagamento aguardando confirmacao</small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($pending): ?>
                            <form method="post" action="<?= url('/organizador/inscricoes/' . $r['id'] . '/status') ?>" class="d-flex flex-wrap gap-2">
                                <?= csrf_field() ?>
                                <button class="btn btn-sm btn-success" name="status" value="aprovado">Aprovar</button>
                                <button class="btn btn-sm btn-outline-danger" name="status" value="rejeitado">Rejeitar</button>
                            </form>
                        <?php else: ?>
                            <form method="post" action="<?= url('/organizador/inscricoes/' . $r['id'] . '/status') ?>">
                                <?= csrf_field() ?>
                                <select class="form-select form-select-sm mb-2" name="status">
                                    <option value="pendente">Marcar como pendente</option>
                                    <option value="aprovado">Aprovar</option>
                                    <option value="rejeitado">Rejeitar</option>
                                    <option value="cancelado">Cancelar inscricao</option>
                                </select>
                                <button class="btn btn-sm btn-primary">Atualizar</button>
                            </form>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
