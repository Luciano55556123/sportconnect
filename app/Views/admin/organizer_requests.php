<section class="page-band"><div class="container"><h1>Solicitacoes de Organizador</h1><p>Analise os pedidos pendentes para liberar novos organizadores.</p></div></section>
<section class="container py-4">
    <div class="panel">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Nome</th><th>Email</th><th>Cidade</th><th>Telefone</th><th>Data da solicitacao</th><th>Status</th><th>Acoes</th></tr></thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td><?= e($request['name']) ?></td>
                            <td><?= e($request['email']) ?></td>
                            <td><?= e($request['city']) ?></td>
                            <td><?= e($request['phone']) ?></td>
                            <td><?= e($request['created_at']) ?></td>
                            <td><?= e($request['status']) ?></td>
                            <td class="d-flex flex-wrap gap-2">
                                <a class="btn btn-sm btn-outline-secondary" href="<?= url('/admin/solicitacoes-organizador/' . $request['id']) ?>">Visualizar</a>
                                <form method="post" action="<?= url('/admin/solicitacoes-organizador/' . $request['id'] . '/aprovar') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-success">Aprovar</button></form>
                                <a class="btn btn-sm btn-outline-danger" href="<?= url('/admin/solicitacoes-organizador/' . $request['id']) ?>">Rejeitar</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$requests): ?><tr><td colspan="7" class="text-muted">Nenhuma solicitacao pendente.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
