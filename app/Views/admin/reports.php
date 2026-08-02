<section class="page-band">
    <div class="container d-flex justify-content-between align-items-center gap-3 flex-wrap">
        <div>
            <h1>Denuncias</h1>
            <p class="mb-0 text-muted">Analise relatos enviados por usuarios logados.</p>
        </div>
        <form class="d-flex gap-2" method="GET">
            <select class="form-select" name="status">
                <option value="">Todos</option>
                <?php foreach (['open' => 'Abertas', 'under_review' => 'Em analise', 'resolved' => 'Resolvidas', 'rejected' => 'Rejeitadas', 'archived' => 'Arquivadas'] as $value => $label): ?>
                    <option value="<?= e($value) ?>" <?= ($status ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary">Filtrar</button>
        </form>
    </div>
</section>

<section class="container py-4">
    <?php if (empty($reports)): ?>
        <div class="panel text-center">
            <p class="mb-0 text-muted">Nenhuma denuncia encontrada.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive panel">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Denuncia</th>
                        <th>Campeonato</th>
                        <th>Autor</th>
                        <th>Status</th>
                        <th>Analise</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $report): ?>
                        <tr>
                            <td>
                                <strong><?= e($report['title']) ?></strong>
                                <div class="small text-muted"><?= e($report['report_type']) ?> | <?= e($report['created_at']) ?></div>
                                <div><?= e($report['description']) ?></div>
                                <?php if (!empty($report['admin_notes'])): ?>
                                    <div class="small mt-2"><strong>Decisao:</strong> <?= e($report['admin_notes']) ?></div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($report['championship_id'])): ?>
                                    <a href="<?= url('/campeonatos/' . $report['championship_id']) ?>"><?= e($report['championship_name'] ?? 'Campeonato') ?></a>
                                <?php else: ?>
                                    <span class="text-muted">Nao vinculado</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?= e($report['reporter_name']) ?>
                                <div class="small text-muted"><?= e($report['reporter_email']) ?></div>
                            </td>
                            <td><span class="badge bg-secondary"><?= e($report['status']) ?></span></td>
                            <td style="min-width: 260px;">
                                <form method="POST" action="<?= url('/admin/denuncias/' . $report['id'] . '/revisar') ?>" class="vstack gap-2">
                                    <?= csrf_field() ?>
                                    <select class="form-select form-select-sm" name="status">
                                        <?php foreach (['under_review' => 'Em analise', 'resolved' => 'Resolvida', 'rejected' => 'Rejeitada', 'archived' => 'Arquivada'] as $value => $label): ?>
                                            <option value="<?= e($value) ?>" <?= $report['status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <textarea class="form-control form-control-sm" name="admin_notes" rows="2" placeholder="Decisao administrativa"><?= e($report['admin_notes'] ?? '') ?></textarea>
                                    <button class="btn btn-sm btn-primary">Salvar analise</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</section>
