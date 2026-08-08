<section class="page-band"><div class="container"><h1>Solicitacao de Organizador</h1><p><?= e($request['name']) ?> - <?= e($request['status']) ?></p></div></section>
<section class="container py-4">
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="panel">
                <h2>Dados da solicitacao</h2>
                <div class="table-responsive mt-3">
                    <table class="table align-middle mb-0">
                        <tbody>
                            <tr><th>Nome</th><td><?= e($request['name']) ?></td></tr>
                            <tr><th>Email</th><td><?= e($request['email']) ?></td></tr>
                            <tr><th>Cidade</th><td><?= e($request['city']) ?></td></tr>
                            <tr><th>Telefone</th><td><?= e($request['phone']) ?></td></tr>
                            <tr><th>Data da solicitacao</th><td><?= e($request['created_at']) ?></td></tr>
                            <tr><th>Status</th><td><?= e($request['status']) ?></td></tr>
                            <?php if (!empty($request['approved_at'])): ?><tr><th>Data da aprovacao</th><td><?= e($request['approved_at']) ?></td></tr><?php endif; ?>
                            <?php if (!empty($request['approved_by_name'])): ?><tr><th>Aprovado por</th><td><?= e($request['approved_by_name']) ?></td></tr><?php endif; ?>
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
