<section class="page-band"><div class="container"><h1>Inscricoes recebidas</h1></div></section>
<section class="container py-4">
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
            <?php foreach ($registrations as $r): ?>
                <?php $paid = !empty($r['requires_payment']) || (float) ($r['registration_fee'] ?? 0) > 0; ?>
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
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge text-bg-success">Gratuito</span>
                        <?php endif; ?>
                    </td>
                    <td><span class="badge text-bg-secondary"><?= e($labels[$r['status']] ?? $r['status']) ?></span></td>
                    <td>
                        <?php if ($paid): ?>
                            <form method="post" action="<?= url('/organizador/inscricoes/' . $r['id'] . '/pagamento') ?>" class="mb-2">
                                <?= csrf_field() ?>
                                <input class="form-control form-control-sm mb-2" name="review_notes" placeholder="Observacao opcional">
                                <button class="btn btn-sm btn-success" name="action" value="approve">Aprovar pagamento</button>
                                <button class="btn btn-sm btn-outline-danger" name="action" value="reject">Rejeitar pagamento</button>
                            </form>
                        <?php endif; ?>
                        <form method="post" action="<?= url('/organizador/inscricoes/' . $r['id'] . '/status') ?>">
                            <?= csrf_field() ?>
                            <select class="form-select form-select-sm mb-2" name="status">
                                <option value="confirmada">Confirmar inscricao</option>
                                <option value="aprovado">Aprovar</option>
                                <option value="rejeitado">Rejeitar</option>
                                <option value="cancelado">Cancelar inscricao</option>
                            </select>
                            <button class="btn btn-sm btn-primary">Atualizar</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
