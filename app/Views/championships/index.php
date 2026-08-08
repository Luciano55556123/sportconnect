<section class="page-band">
    <div class="container"><h1>Pesquisar campeonatos</h1><p>Filtre por cidade, esporte, categoria, data, valor e modalidade.</p></div>
</section>
<section class="container py-4">
    <form class="filter-box row g-3" method="get">
        <div class="col-md-3"><input class="form-control" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Nome ou organizador"></div>
        <div class="col-md-2"><input class="form-control" name="city" value="<?= e($filters['city'] ?? '') ?>" placeholder="Cidade"></div>
        <div class="col-md-2"><select class="form-select" name="sport_id"><option value="">Esporte</option><?php foreach ($sports as $sport): ?><option value="<?= $sport['id'] ?>" <?= ($filters['sport_id'] ?? '') == $sport['id'] ? 'selected' : '' ?>><?= e($sport['name']) ?></option><?php endforeach; ?></select></div>
        <div class="col-md-2"><input class="form-control" type="date" name="date" value="<?= e($filters['date'] ?? '') ?>"></div>
        <div class="col-md-2"><input class="form-control" type="number" step="0.01" name="max_price" value="<?= e($filters['max_price'] ?? '') ?>" placeholder="Valor max."></div>
        <div class="col-md-1"><button class="btn btn-primary w-100"><i class="fa-solid fa-filter"></i></button></div>
        <div class="col-md-3"><input class="form-control" name="category" value="<?= e($filters['category'] ?? '') ?>" placeholder="Categoria"></div>
        <div class="col-md-3"><select class="form-select" name="modality"><option value="">Modalidade</option><option value="masculino">Masculino</option><option value="feminino">Feminino</option><option value="misto">Misto</option></select></div>
        <div class="col-md-3"><select class="form-select" name="status"><option value="">Status</option><option value="ativo">Ativo</option><option value="encerrado">Encerrado</option><option value="cancelado">Cancelado</option></select></div>
    </form>
    <div class="row g-4 mt-2">
        <?php foreach ($championships as $event): require BASE_PATH . '/app/Views/championships/_card.php'; endforeach; ?>
        <?php if (!$championships): ?><p class="text-muted">Nenhum campeonato encontrado.</p><?php endif; ?>
    </div>
</section>
