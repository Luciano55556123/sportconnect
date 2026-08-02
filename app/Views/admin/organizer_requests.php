<section class="page-band"><div class="container"><h1>Solicitacoes de Organizador</h1><p>Analise documentos e aprove apenas responsaveis reais.</p></div></section>
<section class="container py-4">
    <form class="filter-box row g-2 mb-3" method="get">
        <div class="col-md-4"><select class="form-select" name="status"><?php foreach (['pending','approved','rejected'] as $status): ?><option value="<?= e($status) ?>" <?= ($_GET['status'] ?? 'pending') === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Filtrar</button></div>
    </form>
    <div class="panel table-responsive">
        <?php if (!$requests): ?><p class="text-muted mb-0">Nenhuma solicitacao encontrada.</p><?php else: ?>
            <table class="table align-middle">
                <thead><tr><th>Nome</th><th>Email</th><th>Cidade</th><th>Telefone</th><th>Data da solicitacao</th><th>Status</th><th>Acoes</th></tr></thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?= e($request['user_name']) ?></td>
                            <td><?= e($request['user_email']) ?></td>
                            <td><?= e($request['city'] ?: $request['user_city']) ?><?= !empty($request['state']) ? '/' . e($request['state']) : '' ?></td>
                            <td><?= e($request['phone'] ?: $request['user_phone']) ?></td>
                            <td><?= e($request['created_at']) ?></td>
                            <td><span class="badge text-bg-secondary"><?= e($request['status']) ?></span></td>
                            <td class="d-flex flex-wrap gap-2">
                                <a class="btn btn-sm btn-outline-secondary" href="<?= url('/admin/solicitacoes-organizadores/' . $request['id']) ?>">Visualizar</a>
                                <?php if ($request['status'] === 'pending'): ?>
                                    <form method="post" action="<?= url('/admin/solicitacoes-organizadores/' . $request['id'] . '/revisar') ?>"><?= csrf_field() ?><input type="hidden" name="status" value="approved"><button class="btn btn-sm btn-success">Aprovar</button></form>
                                    <a class="btn btn-sm btn-outline-danger" href="<?= url('/admin/solicitacoes-organizadores/' . $request['id']) ?>">Rejeitar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>
