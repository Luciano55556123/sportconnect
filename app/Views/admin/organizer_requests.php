<section class="page-band"><div class="container"><h1>Solicitacoes de Organizador</h1><p>Analise os pedidos pendentes para liberar novos organizadores.</p></div></section>
<section class="container py-4">
    <div class="panel">
        <div class="table-responsive">
            <table class="table align-middle">
                <thead><tr><th>Nome</th><th>Email</th><th>Cidade</th><th>Telefone</th><th>Data da solicitacao</th><th>Status</th><th>Acoes</th></tr></thead>
                <tbody>
                    <?php foreach ($requests as $request): ?>
                        <?php
                        $name = $request['responsible_name'] ?? $request['user_name'] ?? '';
                        $email = $request['contact_email'] ?? $request['email'] ?? $request['user_email'] ?? '';
                        $city = $request['city'] ?? $request['user_city'] ?? '';
                        $phone = $request['phone'] ?? $request['user_phone'] ?? '';
                        $statusLabels = ['pending' => 'Pendente', 'approved' => 'Aprovada', 'rejected' => 'Rejeitada'];
                        ?>
                        <tr>
                            <td><?= e($name) ?></td>
                            <td><?= e($email) ?></td>
                            <td><?= e($city) ?></td>
                            <td><?= e($phone) ?></td>
                            <td><?= e($request['created_at']) ?></td>
                            <td><?= e($statusLabels[$request['status']] ?? $request['status']) ?></td>
                            <td class="d-flex flex-wrap gap-2">
                                <a class="btn btn-sm btn-outline-secondary" href="<?= url('/admin/solicitacoes-organizador/' . $request['id']) ?>">Ver detalhes</a>
                                <?php if ($request['status'] === 'pending'): ?>
                                    <form method="post" action="<?= url('/admin/solicitacoes-organizador/' . $request['id'] . '/aprovar') ?>"><?= csrf_field() ?><button class="btn btn-sm btn-success">Aprovar</button></form>
                                    <a class="btn btn-sm btn-outline-danger" href="<?= url('/admin/solicitacoes-organizador/' . $request['id']) ?>">Rejeitar</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php if (!$requests): ?><tr><td colspan="7" class="text-muted">Nenhuma solicitacao registrada.</td></tr><?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
