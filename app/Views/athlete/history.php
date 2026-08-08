<section class="page-band"><div class="container"><h1>Historico</h1></div></section>
<section class="container py-4">
    <div class="table-responsive panel">
        <table class="table table-hover align-middle">
            <thead><tr><th>Campeonato</th><th>Data</th><th>Status</th><th>Pagamento</th><th>Acoes</th></tr></thead>
            <tbody>
            <?php
            $labels = [
                'aguardando_pagamento' => 'Aguardando pagamento',
                'pagamento_em_analise' => 'Pagamento em analise',
                'awaiting_receipt' => 'Aguardando pagamento',
                'under_review' => 'Pagamento em analise',
                'paid' => 'Pagamento aprovado',
                'rejected' => 'Pagamento rejeitado',
                'aprovado' => 'Pagamento aprovado',
                'rejeitado' => 'Pagamento rejeitado',
                'pendente' => 'Pendente',
                'confirmada' => 'Confirmada',
                'pagamento_rejeitado' => 'Pagamento rejeitado',
                'cancelado' => 'Cancelada',
            ];
            ?>
            <?php foreach ($registrations as $r): ?>
                <?php $paid = !empty($r['requires_payment']) || (float) ($r['registration_fee'] ?? 0) > 0; ?>
                <tr>
                    <td><?= e($r['championship_name']) ?><br><small><?= e($r['sport_name']) ?></small></td>
                    <td><?= e(date('d/m/Y', strtotime($r['event_date']))) ?></td>
                    <td><span class="badge text-bg-secondary"><?= e($labels[$r['status']] ?? $r['status']) ?></span></td>
                    <td>
                        <?php if ($paid): ?>
                            <?php $paymentStatus = $r['payment_status'] ?? 'awaiting_receipt'; ?>
                            <span class="badge text-bg-info"><?= e($labels[$paymentStatus] ?? $paymentStatus) ?></span><br>
                            <strong>R$ <?= number_format((float) ($r['payment_amount'] ?? $r['registration_fee']), 2, ',', '.') ?></strong>
                        <?php else: ?>
                            <span class="badge text-bg-success">Gratuito</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($paid): ?>
                            <button class="btn btn-sm btn-outline-primary mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#pix-<?= (int) $r['id'] ?>">Pagar via PIX</button>
                            <?php if (!empty($r['receipt_path'])): ?>
                                <a class="btn btn-sm btn-outline-secondary mb-2" target="_blank" href="<?= url('/atleta/inscricoes/' . $r['id'] . '/comprovante') ?>">Visualizar comprovante</a>
                            <?php endif; ?>
                            <div class="collapse" id="pix-<?= (int) $r['id'] ?>">
                                <div class="border rounded p-3 mt-2">
                                    <p class="mb-1"><strong><?= e($r['championship_name']) ?></strong></p>
                                    <p class="mb-1">Status: <?= e($labels[$paymentStatus] ?? $paymentStatus) ?></p>
                                    <p class="mb-1">Valor: R$ <?= number_format((float) ($r['payment_amount'] ?? $r['registration_fee']), 2, ',', '.') ?></p>
                                    <p class="mb-1">Chave PIX: <code id="pix-key-<?= (int) $r['id'] ?>"><?= e($r['pix_key'] ?? '') ?></code></p>
                                    <p class="mb-1">Tipo: <?= e($r['pix_key_type'] ?? '') ?></p>
                                    <p class="mb-1">Titular: <?= e($r['pix_holder_name'] ?? '') ?></p>
                                    <?php if (!empty($r['pix_instructions'])): ?><p class="mb-2"><?= e($r['pix_instructions']) ?></p><?php endif; ?>
                                    <button class="btn btn-sm btn-outline-primary mb-2" type="button" onclick="navigator.clipboard.writeText(document.getElementById('pix-key-<?= (int) $r['id'] ?>').innerText)">Copiar chave PIX</button>
                                    <form method="post" enctype="multipart/form-data" action="<?= url('/atleta/inscricoes/' . $r['id'] . '/comprovante') ?>" class="d-flex flex-column gap-2">
                                        <?= csrf_field() ?>
                                        <input class="form-control form-control-sm" type="file" name="receipt_file" accept=".jpg,.jpeg,.png,.pdf" required>
                                        <button class="btn btn-sm btn-primary">Enviar comprovante</button>
                                    </form>
                                </div>
                            </div>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</section>
