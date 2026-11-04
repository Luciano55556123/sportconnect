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
                'paid' => 'Confirmado',
                'waived' => 'Dispensado',
                'refunded' => 'Reembolsado',
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
                                <br><small class="text-muted">Comprovante via WhatsApp, quando enviado.</small>
                            <?php endif; ?>
                        <?php else: ?>
                            <span class="badge text-bg-success">Gratuito</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge text-bg-secondary"><?= e($labels[$r['status']] ?? $r['status']) ?></span>
                        <?php if ($paid && !in_array(($r['payment_status'] ?? ''), ['paid', 'waived'], true) && !in_array($registrationStatus, ['rejeitado', 'cancelado'], true)): ?>
                            <br><small class="text-muted">
                                <?php if (($r['payment_status'] ?? '') === 'rejected'): ?>
                                    Pagamento rejeitado - entre em contato com o participante.
                                <?php elseif (in_array($registrationStatus, ['aprovado', 'confirmada'], true)): ?>
                                    Inscricao aprovada - pagamento ainda nao confirmado.
                                <?php else: ?>
                                    Inscricao pendente - pagamento ainda nao confirmado.
                                <?php endif; ?>
                            </small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($paid && ($r['payment_status'] ?? '') === 'paid'): ?>
                            <small class="text-success">Pagamento confirmado. Para corrigir um cancelamento ou reembolso, procure o administrador.</small>
                        <?php elseif ($pending): ?>
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
                        <?php if ($paid && !in_array(($r['payment_status'] ?? ''), ['paid', 'waived', 'refunded'], true)
                            && !in_array($registrationStatus, ['cancelado', 'rejeitado'], true)): ?>
                            <form method="post" action="<?= url('/organizador/inscricoes/' . (int) $r['id'] . '/pagamento') ?>" class="mt-2">
                                <?= csrf_field() ?>
                                <p class="small text-muted mb-1">Confirme somente após verificar o comprovante e o recebimento do PIX.</p>
                                <input class="form-control form-control-sm mb-2" name="review_notes" maxlength="250" placeholder="Observação da conferência (opcional)">
                                <div class="d-flex flex-wrap gap-1">
                                    <button class="btn btn-sm btn-outline-success" name="action" value="approve" onclick="return confirm('Confirma que verificou o recebimento deste PIX?')">Confirmar PIX</button>
                                    <button class="btn btn-sm btn-outline-danger" name="action" value="reject" onclick="return confirm('Rejeitar este pagamento?')">Rejeitar PIX</button>
                                </div>
                            </form>
                        <?php elseif ($paid && ($r['payment_status'] ?? '') === 'paid'): ?>
                            <small class="text-success d-block mt-2">Pagamento confirmado.</small>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (!empty($managedAthletes)): ?>
        <div class="mt-4">
            <h2>Atletas cadastrados na gestao (<?= count($managedAthletes) ?>)</h2>
            <p class="text-muted">
                Participantes cadastrados diretamente na gestao dos campeonatos.
                Eles nao sao inscricoes online e nao possuem pagamento associado a esse cadastro.
            </p>
            <div class="table-responsive panel">
                <table class="table align-middle">
                    <thead><tr><th>Campeonato</th><th>Atleta</th><th>Equipe</th><th>Status</th><th>Acoes</th></tr></thead>
                    <tbody>
                    <?php
                    $managedStatusLabels = [
                        'aprovado' => 'Aprovado',
                        'pendente' => 'Pendente',
                        'rejeitado' => 'Rejeitado',
                        'cancelado' => 'Cancelado',
                    ];
                    ?>
                    <?php foreach ($managedAthletes as $athlete): ?>
                        <?php
                        $managedStatus = (string) ($athlete['status'] ?? '');
                        $managedStatusLabel = $managedStatusLabels[$managedStatus] ?? ($managedStatus ?: 'Nao informado');
                        ?>
                        <tr>
                            <td><?= e($athlete['championship_name']) ?></td>
                            <td><?= e($athlete['name']) ?></td>
                            <td><?= e($athlete['team_name'] ?? 'Sem equipe') ?></td>
                            <td><span class="badge text-bg-secondary"><?= e($managedStatusLabel) ?></span></td>
                            <td><a class="btn btn-sm btn-outline-primary" href="<?= url('/organizador/campeonatos/' . (int) $athlete['championship_id'] . '/gerenciar#atletas') ?>">Ver na gestao</a></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</section>
