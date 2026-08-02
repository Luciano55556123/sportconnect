<section class="page-band">
    <div class="container"><span class="eyebrow">Busca regional</span><h1>Pesquisar campeonatos</h1><p>Filtre por cidade, esporte, categoria, data, valor e modalidade.</p></div>
</section>
<section class="container py-4">
    <button class="btn btn-outline-primary d-lg-none mb-3" data-bs-toggle="collapse" data-bs-target="#filtersPanel"><i class="fa-solid fa-sliders"></i> Filtros</button>
    <div class="row g-4">
        <aside class="col-lg-3">
            <form id="filtersPanel" class="filter-box collapse d-lg-block" method="get">
                <h2>Filtros</h2>
                <label class="form-label">Busca<input class="form-control" name="q" value="<?= e($filters['q'] ?? '') ?>" placeholder="Nome ou organizador"></label>
                <label class="form-label">Cidade<input class="form-control" name="city" value="<?= e($filters['city'] ?? '') ?>" placeholder="Cidade"></label>
                <label class="form-label">Estado<input class="form-control" name="state" maxlength="2" value="<?= e($filters['state'] ?? '') ?>" placeholder="UF"></label>
                <label class="form-label">Esporte<select class="form-select" name="sport_id"><option value="">Todos</option><?php foreach ($sports as $sport): ?><option value="<?= $sport['id'] ?>" <?= ($filters['sport_id'] ?? '') == $sport['id'] ? 'selected' : '' ?>><?= e($sport['name']) ?></option><?php endforeach; ?></select></label>
                <label class="form-label">Categoria<input class="form-control" name="category" value="<?= e($filters['category'] ?? '') ?>" placeholder="Sub-17, adulto..."></label>
                <label class="form-label">Modalidade<select class="form-select" name="modality"><option value="">Todas</option><option value="masculino" <?= ($filters['modality'] ?? '') === 'masculino' ? 'selected' : '' ?>>Masculino</option><option value="feminino" <?= ($filters['modality'] ?? '') === 'feminino' ? 'selected' : '' ?>>Feminino</option><option value="misto" <?= ($filters['modality'] ?? '') === 'misto' ? 'selected' : '' ?>>Misto</option></select></label>
                <label class="form-label">Tipo<select class="form-select" name="registration_type"><option value="">Todos</option><option value="individual" <?= ($filters['registration_type'] ?? '') === 'individual' ? 'selected' : '' ?>>Individual</option><option value="team" <?= ($filters['registration_type'] ?? '') === 'team' ? 'selected' : '' ?>>Equipe</option></select></label>
                <label class="form-label">Data inicial<input class="form-control" type="date" name="date_from" value="<?= e($filters['date_from'] ?? '') ?>"></label>
                <label class="form-label">Data final<input class="form-control" type="date" name="date_to" value="<?= e($filters['date_to'] ?? '') ?>"></label>
                <label class="form-label">Valor maximo<input class="form-control" type="number" step="0.01" name="max_price" value="<?= e($filters['max_price'] ?? '') ?>"></label>
                <label class="form-label">Ordenar<select class="form-select" name="sort"><option value="">Mais proximos</option><option value="recentes" <?= ($filters['sort'] ?? '') === 'recentes' ? 'selected' : '' ?>>Mais recentes</option><option value="menor_preco" <?= ($filters['sort'] ?? '') === 'menor_preco' ? 'selected' : '' ?>>Menor preco</option><option value="maior_preco" <?= ($filters['sort'] ?? '') === 'maior_preco' ? 'selected' : '' ?>>Maior preco</option><option value="mais_inscritos" <?= ($filters['sort'] ?? '') === 'mais_inscritos' ? 'selected' : '' ?>>Mais inscritos</option></select></label>
                <label class="form-check"><input class="form-check-input" type="checkbox" name="registrations_open" value="1" <?= !empty($filters['registrations_open']) ? 'checked' : '' ?>> Inscricoes abertas</label>
                <label class="form-check"><input class="form-check-input" type="checkbox" name="free" value="1" <?= !empty($filters['free']) ? 'checked' : '' ?>> Gratuito</label>
                <div class="d-grid gap-2 mt-3"><button class="btn btn-primary"><i class="fa-solid fa-filter"></i> Aplicar filtros</button><a class="btn btn-outline-secondary" href="<?= url('/campeonatos') ?>">Limpar filtros</a></div>
            </form>
        </aside>
        <div class="col-lg-9">
            <div class="d-flex justify-content-between align-items-center mb-3 gap-2 flex-wrap"><strong><?= count($championships) ?> resultado(s)</strong><span class="text-muted small">Somente campeonatos publicados aparecem aqui.</span></div>
            <div class="row g-4">
                <?php foreach ($championships as $event): require BASE_PATH . '/app/Views/championships/_card.php'; endforeach; ?>
                <?php if (!$championships): ?><div class="col-12"><div class="empty-state"><h2>Nenhum campeonato encontrado</h2><p>Revise filtros de cidade, esporte, data ou valor.</p><a class="btn btn-outline-primary" href="<?= url('/campeonatos') ?>">Limpar busca</a></div></div><?php endif; ?>
            </div>
        </div>
    </div>
</section>
