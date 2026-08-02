<section class="page-band"><div class="container"><h1>Solicitacoes de organizadores</h1><p>Analise documentos e aprove apenas responsaveis reais.</p></div></section>
<section class="container py-4">
    <form class="filter-box row g-2 mb-3" method="get">
        <div class="col-md-4"><select class="form-select" name="status"><option value="">Todos os status</option><?php foreach (['pending','approved','rejected','suspended'] as $status): ?><option value="<?= e($status) ?>" <?= ($_GET['status'] ?? '') === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><button class="btn btn-primary w-100">Filtrar</button></div>
    </form>
    <div class="panel table-responsive">
        <?php if (!$requests): ?><p class="text-muted mb-0">Nenhuma solicitacao encontrada.</p><?php else: ?>
            <table class="table align-middle">
                <thead><tr><th>Usuario</th><th>Entidade</th><th>Documento</th><th>Cidade</th><th>Status</th><th>Analise</th></tr></thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?= e($request['user_name']) ?><br><small><?= e($request['user_email']) ?></small></td>
                            <td><?= e($request['organization_name']) ?><br><small><?= e($request['organization_type']) ?></small></td>
                            <td><?= e($request['document_number']) ?></td>
                            <td><?= e($request['city']) ?>/<?= e($request['state']) ?></td>
                            <td><span class="badge text-bg-secondary"><?= e($request['status']) ?></span></td>
                            <td>
                                <form class="stack-form" method="post" action="<?= url('/admin/solicitacoes-organizadores/' . $request['id'] . '/revisar') ?>">
                                    <?= csrf_field() ?>
                                    <select class="form-select form-select-sm" name="status"><option value="approved">Aprovar</option><option value="rejected">Rejeitar</option><option value="suspended">Suspender</option></select>
                                    <input class="form-control form-control-sm" name="rejection_reason" placeholder="Motivo quando necessario">
                                    <button class="btn btn-sm btn-primary">Salvar decisao</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>
