<section class="page-band"><div class="container"><h1>Campeonatos pendentes</h1><p>Revise dados, regulamento, local e inscrições antes da publicação.</p></div></section>
<section class="container py-4">
    <div class="panel table-responsive">
        <?php if (!$championships): ?><p class="text-muted mb-0">Nenhum campeonato aguardando aprovação.</p><?php else: ?>
            <table class="table align-middle">
                <thead><tr><th>Campeonato</th><th>Organizador</th><th>Data</th><th>Cidade</th><th>Decisão</th></tr></thead>
                <tbody>
                    <?php foreach ($championships as $championship): ?>
                        <tr>
                            <td><strong><?= e($championship['name']) ?></strong><br><small><?= e($championship['sport_name']) ?> · <?= e($championship['category'] ?? '') ?></small></td>
                            <td><?= e($championship['organizer_name']) ?><br><small><?= e($championship['organizer_email'] ?? '') ?></small></td>
                            <td><?= e($championship['event_date'] ?? '') ?></td>
                            <td><?= e($championship['city']) ?></td>
                            <td>
                                <form class="stack-form" method="post" action="<?= url('/admin/campeonatos-pendentes/' . $championship['id'] . '/revisar') ?>">
                                    <?= csrf_field() ?>
                                    <select class="form-select form-select-sm" name="editorial_status"><option value="published">Aprovar publicação</option><option value="rejected">Rejeitar</option></select>
                                    <input class="form-control form-control-sm" name="rejection_reason" placeholder="Motivo quando necessário">
                                    <div class="d-flex gap-2"><a class="btn btn-sm btn-outline-secondary" href="<?= url('/campeonatos/' . $championship['id']) ?>">Visualizar</a><button class="btn btn-sm btn-primary">Salvar</button></div>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>
