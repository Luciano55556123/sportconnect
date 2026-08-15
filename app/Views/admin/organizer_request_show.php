<?php
$statusLabels = ['pending' => 'Pendente', 'approved' => 'Aprovada', 'rejected' => 'Rejeitada'];
$name = $request['responsible_name'] ?? $request['user_name'] ?? '';
$email = $request['contact_email'] ?? $request['email'] ?? $request['user_email'] ?? '';
$city = $request['city'] ?? $request['user_city'] ?? '';
$phone = $request['phone'] ?? $request['user_phone'] ?? '';
$document = $request['cpf_cnpj'] ?? $request['document_number'] ?? $request['document'] ?? '';
$state = $request['state'] ?? $request['uf'] ?? '';
$experience = $request['experience'] ?? $request['event_experience'] ?? '';
$reason = $request['request_reason'] ?? $request['description'] ?? $request['reason'] ?? '';
$reviewDate = $request['reviewed_at'] ?? $request['approved_at'] ?? '';
?>
<section class="page-band"><div class="container"><h1>Solicitacao de Organizador</h1><p><?= e($name) ?> - <?= e($statusLabels[$request['status']] ?? $request['status']) ?></p></div></section>
<section class="container py-4">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="panel">
                <h2>Dados da solicitacao</h2>
                <div class="table-responsive mt-3">
                    <table class="table align-middle mb-0">
                        <tbody>
                            <tr><th>Usuario da conta</th><td><?= e($request['user_name'] ?? '') ?></td></tr>
                            <tr><th>E-mail da conta</th><td><?= e($request['user_email'] ?? '') ?></td></tr>
                            <tr><th>Nome do responsavel</th><td><?= e($name) ?></td></tr>
                            <tr><th>CPF/CNPJ</th><td><?= e($document) ?></td></tr>
                            <tr><th>Organizacao</th><td><?= e($request['organization_name'] ?? '') ?></td></tr>
                            <tr><th>Tipo de organizacao</th><td><?= e($request['organization_type'] ?? '') ?></td></tr>
                            <tr><th>Email de contato</th><td><?= e($email) ?></td></tr>
                            <tr><th>Cidade</th><td><?= e($city) ?></td></tr>
                            <tr><th>Estado</th><td><?= e($state) ?></td></tr>
                            <tr><th>Telefone</th><td><?= e($phone) ?></td></tr>
                            <tr><th>WhatsApp</th><td><?= e($request['whatsapp'] ?? '') ?></td></tr>
                            <tr><th>Experiencia</th><td><?= nl2br(e($experience)) ?></td></tr>
                            <tr><th>Motivo</th><td><?= nl2br(e($reason)) ?></td></tr>
                            <tr><th>Data da solicitacao</th><td><?= e($request['created_at']) ?></td></tr>
                            <tr><th>Status</th><td><?= e($statusLabels[$request['status']] ?? $request['status']) ?></td></tr>
                            <?php if (!empty($reviewDate)): ?><tr><th>Data da analise</th><td><?= e($reviewDate) ?></td></tr><?php endif; ?>
                            <?php if (!empty($request['approved_by_name'])): ?><tr><th>Analisado por</th><td><?= e($request['approved_by_name']) ?></td></tr><?php endif; ?>
                            <?php if (!empty($request['rejection_reason'])): ?><tr><th>Motivo da rejeicao</th><td><?= e($request['rejection_reason']) ?></td></tr><?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="panel">
                <h2>Acoes</h2>
                <?php if ($request['status'] === 'pending'): ?>
                    <form class="mb-3" method="post" action="<?= url('/admin/solicitacoes-organizador/' . $request['id'] . '/aprovar') ?>"><?= csrf_field() ?><button class="btn btn-success w-100">Aprovar</button></form>
                    <form method="post" action="<?= url('/admin/solicitacoes-organizador/' . $request['id'] . '/rejeitar') ?>">
                        <?= csrf_field() ?>
                        <textarea class="form-control mb-3" name="rejection_reason" rows="4" placeholder="Motivo da rejeicao" required></textarea>
                        <button class="btn btn-outline-danger w-100">Rejeitar</button>
                    </form>
                <?php else: ?>
                    <p class="text-muted mb-0">Esta solicitacao ja foi analisada.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>
