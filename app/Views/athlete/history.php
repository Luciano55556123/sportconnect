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
                <?php
                    $paid = !empty($r['requires_payment']) && (float) ($r['registration_fee'] ?? 0) > 0;
                    $amount = (float) ($r['payment_amount'] ?? $r['registration_fee']);
                    $whatsappMessage = "Ola! Fiz minha inscricao pelo Ponto Competitivo.\n\n"
                        . 'Campeonato: ' . ($r['championship_name'] ?? '') . "\n"
                        . 'Participante: ' . ($r['name'] ?? '') . "\n"
                        . 'Valor da inscricao: ' . money_br($amount) . "\n\n"
                        . "Estou entrando em contato para enviar meu comprovante de pagamento.\n\n"
                        . 'Aguardo a confirmacao da inscricao. Obrigado!';
                    $whatsappNumber = whatsapp_number($r['whatsapp_contato'] ?? '');
                    $whatsappLink = $whatsappNumber !== '' ? whatsapp_url($whatsappNumber, $whatsappMessage) : null;
                ?>
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
                            <div class="collapse" id="pix-<?= (int) $r['id'] ?>">
                                <div class="pix-payment mt-2">
                                    <h2>Pagamento via PIX</h2>
                                    <p class="mb-1"><strong><?= e($r['championship_name']) ?></strong></p>
                                    <p class="mb-1">Status: <?= e($labels[$paymentStatus] ?? $paymentStatus) ?></p>
                                    <p class="pix-amount">Valor: <?= e(money_br($amount)) ?></p>
                                    <?php if (!empty($r['pix_qr']) && !empty($r['pix_payload'])): ?>
                                        <img class="pix-qr" src="<?= e($r['pix_qr']) ?>" alt="QR Code PIX">
                                        <p class="text-muted mb-2">Escaneie o QR Code com o aplicativo do seu banco.</p>
                                        <label class="form-label mt-2">Codigo PIX:</label>
                                        <textarea class="form-control pix-code-field" rows="4" readonly><?= e($r['pix_payload']) ?></textarea>
                                        <button class="btn btn-sm btn-outline-primary js-copy-pix mt-2 mb-2" type="button" data-pix-code="<?= e($r['pix_payload']) ?>">Copiar PIX</button>
                                    <?php else: ?>
                                        <div class="alert alert-warning mb-2">Nao foi possivel gerar o QR Code PIX. Fale com o organizador.</div>
                                    <?php endif; ?>
                                    <p class="mb-1">Ou utilize a chave PIX: <code><?= e($r['pix_key'] ?? '') ?></code></p>
                                    <p class="mb-1">Tipo: <?= e($r['pix_key_type'] ?? '') ?></p>
                                    <p class="mb-1">Recebedor: <?= e($r['pix_holder_name'] ?? '') ?></p>
                                    <?php if (!empty($r['pix_instructions'])): ?><p class="mb-2"><?= e($r['pix_instructions']) ?></p><?php endif; ?>
                                    <h3>Comprovante de pagamento</h3>
                                    <p class="mb-2">Apos realizar o PIX, envie o comprovante diretamente para o organizador.</p>
                                    <?php if ($whatsappNumber !== '' && $whatsappLink !== null): ?>
                                        <a class="btn btn-sm btn-success d-inline-flex align-items-center gap-2" target="_blank" rel="noopener" href="<?= e($whatsappLink) ?>"><i class="fa-brands fa-whatsapp"></i> Enviar comprovante pelo WhatsApp</a>
                                    <?php else: ?>
                                        <div class="alert alert-warning mb-0">WhatsApp do organizador nao informado neste campeonato.</div>
                                    <?php endif; ?>
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
