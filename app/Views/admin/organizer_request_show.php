<section class="page-band"><div class="container"><h1>Solicitacao de Organizador</h1><p><?= e($request['user_name']) ?> - <?= e($request['status']) ?></p></div></section>
<section class="container py-4">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="panel">
                <h2>Dados da solicitacao</h2>
                <div class="table-responsive mt-3">
                    <table class="table align-middle mb-0">
                        <tbody>
                            <tr><th>Nome</th><td><?= e($request['user_name']) ?></td></tr>
                            <tr><th>Email</th><td><?= e($request['user_email']) ?></td></tr>
                            <tr><th>Cidade</th><td><?= e($request['city'] ?: $request['user_city']) ?><?= !empty($request['state']) ? '/' . e($request['state']) : '' ?></td></tr>
                            <tr><th>Telefone</th><td><?= e($request['phone'] ?: $request['user_phone']) ?></td></tr>
                            <tr><th>Responsavel</th><td><?= e($request['responsible_name']) ?></td></tr>
                            <tr><th>Entidade</th><td><?= e($request['organization_name']) ?></td></tr>
                            <tr><th>Tipo</th><td><?= e($request['organization_type']) ?></td></tr>
                            <tr><th>Documento</th><td><?= e($request['document_number']) ?></td></tr>
                            <tr><th>Data da solicitacao</th><td><?= e($request['created_at']) ?></td></tr>
                            <tr><th>Status</th><td><span class="badge text-bg-secondary"><?= e($request['status']) ?></span></td></tr>
                            <?php if (!empty($request['approved_at'])): ?><tr><th>Data da aprovacao</th><td><?= e($request['approved_at']) ?></td></tr><?php endif; ?>
                            <?php if (!empty($request['approved_by_name'])): ?><tr><th>Aprovado por</th><td><?= e($request['approved_by_name']) ?></td></tr><?php endif; ?>
                            <?php if (!empty($request['rejection_reason'])): ?><tr><th>Motivo da rejeicao</th><td><?= e($request['rejection_reason']) ?></td></tr><?php endif; ?>
                            <tr><th>Descricao</th><td><?= nl2br(e($request['description'])) ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="panel">
                <h2>Acoes</h2>
                <?php if ($request['status'] === 'pending'): ?>
                    <form class="mb-3" method="post" action="<?= url('/admin/solicitacoes-organizadores/' . $request['id'] . '/revisar') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="approved">
                        <button class="btn btn-success w-100">Aprovar</button>
                    </form>
                    <form method="post" action="<?= url('/admin/solicitacoes-organizadores/' . $request['id'] . '/revisar') ?>">
                        <?= csrf_field() ?>
                        <input type="hidden" name="status" value="rejected">
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
