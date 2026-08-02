<section class="page-band"><div class="container"><h1>Solicitar perfil de organizador</h1><p>Envie seus dados para que a administracao avalie sua permissao para criar campeonatos reais.</p></div></section>
<section class="container py-4">
    <?php if ($latestRequest): ?>
        <div class="panel mb-3">
            <h2>Minha Solicitacao de Organizador</h2>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <tbody>
                        <tr><th>Status atual</th><td><span class="badge text-bg-secondary"><?= e($latestRequest['status']) ?></span></td></tr>
                        <tr><th>Data da solicitacao</th><td><?= e($latestRequest['created_at']) ?></td></tr>
                        <?php if (!empty($latestRequest['approved_at'])): ?><tr><th>Data da aprovacao</th><td><?= e($latestRequest['approved_at']) ?></td></tr><?php endif; ?>
                        <?php if (!empty($latestRequest['rejection_reason'])): ?><tr><th>Motivo da rejeicao</th><td><?= e($latestRequest['rejection_reason']) ?></td></tr><?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
    <?php if (!$latestRequest || $latestRequest['status'] === 'rejected'): ?>
        <form class="panel row g-3" method="post" enctype="multipart/form-data" action="<?= url('/solicitar-organizador') ?>">
            <?= csrf_field() ?>
            <div class="col-md-6"><label class="form-label">Nome completo</label><input class="form-control" name="responsible_name" required></div>
            <div class="col-md-3"><label class="form-label">CPF ou CNPJ</label><input class="form-control" name="document_number" required></div>
            <div class="col-md-3"><label class="form-label">Telefone</label><input class="form-control" name="phone" required></div>
            <div class="col-md-6"><label class="form-label">Nome da entidade</label><input class="form-control" name="organization_name" required></div>
            <div class="col-md-3"><label class="form-label">Tipo da entidade</label><input class="form-control" name="organization_type" required placeholder="Clube, escola, associacao"></div>
            <div class="col-md-2"><label class="form-label">Cidade</label><input class="form-control" name="city" required></div>
            <div class="col-md-1"><label class="form-label">UF</label><input class="form-control" name="state" maxlength="2" required></div>
            <div class="col-12"><label class="form-label">Descricao</label><textarea class="form-control" name="description" rows="4" required></textarea></div>
            <div class="col-12"><label class="form-label">Comprovante opcional</label><input class="form-control" type="file" name="proof_file" accept=".pdf,image/jpeg,image/png"></div>
            <div class="col-12"><button class="btn btn-primary">Enviar solicitacao</button></div>
        </form>
    <?php endif; ?>
</section>
