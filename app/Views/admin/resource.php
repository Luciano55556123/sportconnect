<section class="page-band"><div class="container"><h1><?= e(ucfirst($resource)) ?></h1></div></section>
<section class="container py-4">
    <div class="panel">
        <p>Listagem administrativa de <?= e($resource) ?> com os registros mais recentes.</p>
        <div class="table-responsive">
            <table class="table align-middle">
                <tbody>
                    <?php foreach (array_slice($resource === 'campeonatos' ? $championships : $users, 0, 20) as $row): ?>
                        <tr>
                            <?php if ($resource === 'campeonatos'): ?>
                                <td><img class="table-thumb" src="<?= e(championship_image_url($row)) ?>" alt="<?= e($row['name'] ?? 'Campeonato') ?>"></td>
                            <?php endif; ?>
                            <td><?= e($row['name'] ?? $row['email'] ?? 'Registro') ?></td>
                            <td><?= e($row['status'] ?? $row['role'] ?? '') ?></td>
                            <td>
                                <?php if ($resource === 'campeonatos'): ?>
                                    <a class="btn btn-sm btn-outline-secondary" href="<?= url('/campeonatos/' . $row['id']) ?>">Visualizar</a>
                                <?php else: ?>
                                    <span class="text-muted small">Registro administrativo</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</section>
