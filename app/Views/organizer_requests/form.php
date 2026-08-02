<section class="page-band"><div class="container"><h1>Solicitar perfil de organizador</h1><p>Envie seus dados para que a administracao avalie sua permissao para criar campeonatos reais.</p></div></section>
<section class="container py-4">
    <?php if ($latestRequest): ?>
        <div class="panel mb-3">
            <h2>Situacao da solicitacao</h2>
            <p><span class="badge text-bg-secondary"><?= e($latestRequest['status']) ?></span></p>
            <?php if (!empty($latestRequest['rejection_reason'])): ?><p><?= e($latestRequest['rejection_reason']) ?></p><?php endif; ?>
        </div>
    <?php endif; ?>
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
</section>
