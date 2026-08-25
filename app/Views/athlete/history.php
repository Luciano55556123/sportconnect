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
            $normalizeBrazilianWhatsapp = static function (?string $phone): string {
                $digits = preg_replace('/\D+/', '', (string) $phone);
                if ($digits === '') {
                    return '';
                }

                if (str_starts_with($digits, '00')) {
                    $digits = substr($digits, 2);
                }

                if (!str_starts_with($digits, '55') && strlen($digits) >= 10 && strlen($digits) <= 11) {
                    $digits = '55' . $digits;
                }

                return $digits;
            };
            ?>
            <?php foreach ($registrations as $r): ?>
                <?php
                    $requiresPayment = !empty($r['championship_requires_payment']);
                    $registrationFee = (float) ($r['championship_registration_fee'] ?? 0);
                    $paid = $requiresPayment && $registrationFee > 0;
                    $paymentStatus = $r['payment_status'] ?? null;
                    $paymentLabel = $paymentStatus ? ($labels[$paymentStatus] ?? $paymentStatus) : 'Aguardando pagamento';
                    if ($paid && $paymentStatus !== 'paid') {
                        $paymentLabel = $labels[$paymentStatus ?? 'awaiting_receipt'] ?? 'Aguardando pagamento';
                    }
                ?>
                <tr>
                    <td><?= e($r['championship_name']) ?><br><small><?= e($r['sport_name']) ?></small></td>
                    <td><?= e(date('d/m/Y', strtotime($r['event_date']))) ?></td>
                    <td><span class="badge text-bg-secondary"><?= e($labels[$r['status']] ?? $r['status']) ?></span></td>
                    <td>
                        <?php if ($paid): ?>
                            <strong>R$ <?= number_format($registrationFee, 2, ',', '.') ?></strong>
                            <br><span class="badge text-bg-info"><?= e($paymentLabel) ?></span>
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
                                <div class="pix-payment mt-2">
                                    <h2>Finalize sua inscricao</h2>
                                    <p class="mb-1"><strong><?= e($r['championship_name']) ?></strong></p>
                                    <p class="mb-1">Status: <?= e($paymentLabel) ?></p>
                                    <p class="pix-amount">Valor: R$ <?= number_format($registrationFee, 2, ',', '.') ?></p>
                                    <?php if (!empty($r['pix_qr']) && !empty($r['pix_payload'])): ?>
                                        <img class="pix-qr" src="<?= e($r['pix_qr']) ?>" alt="QR Code PIX">
                                        <p class="text-muted mb-2">Escaneie o QR Code com o aplicativo do seu banco.</p>
                                        <button class="btn btn-sm btn-outline-primary js-copy-pix mb-2" type="button" data-pix-code="<?= e($r['pix_payload']) ?>">Copiar codigo PIX</button>
                                        <label class="form-label mt-2">PIX Copia e Cola</label>
                                        <textarea class="form-control pix-code-field" rows="4" readonly><?= e($r['pix_payload']) ?></textarea>
                                    <?php else: ?>
                                        <div class="alert alert-warning mb-2">Nao foi possivel gerar o QR Code PIX. Fale com o organizador.</div>
                                    <?php endif; ?>
                                    <p class="mb-1">Ou utilize a chave PIX: <code><?= e($r['pix_key'] ?? '') ?></code></p>
                                    <p class="mb-1">Tipo: <?= e($r['pix_key_type'] ?? '') ?></p>
                                    <p class="mb-1">Recebedor: <?= e($r['pix_holder_name'] ?? '') ?></p>
                                    <?php if (!empty($r['pix_instructions'])): ?><p class="mb-2"><?= e($r['pix_instructions']) ?></p><?php endif; ?>
                                    <h3>Ja realizou o pagamento?</h3>
                                    <?php
                                        $whatsappNumber = $normalizeBrazilianWhatsapp($r['championship_whatsapp_contato'] ?? '');
                                        $message = 'Ola! Realizei o pagamento da inscricao no campeonato ' . ($r['championship_name'] ?? '') . ' e estou enviando o comprovante para validacao.';
                                        $whatsappUrl = $whatsappNumber !== '' ? 'https://wa.me/' . $whatsappNumber . '?text=' . rawurlencode($message) : '';
                                    ?>
                                    <?php if ($whatsappUrl !== ''): ?>
                                        <a class="btn btn-sm btn-primary" target="_blank" rel="noopener" href="<?= e($whatsappUrl) ?>">Enviar comprovante pelo WhatsApp</a>
                                    <?php else: ?>
                                        <div class="alert alert-warning mb-2">O organizador não informou um WhatsApp para envio do comprovante.</div>
                                    <?php endif; ?>
                                    <?php if (!empty($r['receipt_path'])): ?><p class="text-muted mt-2 mb-0">Comprovante enviado. Aguardando analise do organizador.</p><?php endif; ?>
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
