<?php
$titles = [
    'usuarios' => 'Usuarios cadastrados',
    'organizadores' => 'Organizadores cadastrados',
    'campeonatos' => 'Campeonatos cadastrados',
];
$title = $titles[$resource] ?? 'Consulta administrativa';
$isChampionship = $resource === 'campeonatos';
$rows = $isChampionship
    ? $championships
    : ($resource === 'organizadores'
        ? array_values(array_filter($users, static fn (array $user): bool => in_array($user['role'] ?? '', ['organizer', 'organizador'], true)))
        : $users);
?>
<section class="page-band"><div class="container"><h1><?= e($title) ?></h1></div></section>
<section class="container py-4">
    <div class="panel">
        <p class="text-muted">Consulta de registros do sistema. A edicao de contas e campeonatos nao esta disponivel nesta tela.</p>
        <div class="table-responsive">
            <table class="table table-hover align-middle">
                <thead>
                    <?php if ($isChampionship): ?>
                        <tr><th>ID</th><th>Campeonato</th><th>Cidade</th><th>Status</th><th>Acesso</th></tr>
                    <?php else: ?>
                        <tr><th>ID</th><th>Nome</th><th>E-mail</th><th>Perfil</th></tr>
                    <?php endif; ?>
                </thead>
                <tbody>
                    <?php if (!$rows): ?>
                        <tr><td colspan="<?= $isChampionship ? 5 : 4 ?>" class="text-center text-muted py-4">Nenhum registro encontrado.</td></tr>
                    <?php endif; ?>
                    <?php foreach (array_slice($rows, 0, 100) as $row): ?>
                        <?php if ($isChampionship): ?>
                            <tr>
                                <td><?= (int) ($row['id'] ?? 0) ?></td>
                                <td><?= e($row['name'] ?? '') ?></td>
                                <td><?= e($row['city'] ?? '') ?></td>
                                <td><?= e($row['status'] ?? '') ?></td>
                                <td><a class="btn btn-sm btn-outline-primary" href="<?= url('/campeonatos/' . (int) $row['id']) ?>">Ver campeonato</a></td>
                            </tr>
                        <?php else: ?>
                            <tr>
                                <td><?= (int) ($row['id'] ?? 0) ?></td>
                                <td><?= e($row['name'] ?? '') ?></td>
                                <td><?= e($row['email'] ?? '') ?></td>
                                <td><?= e($row['role'] ?? '') ?></td>
                            </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php if (count($rows) > 100): ?>
            <p class="text-muted mb-0">Mostrando os primeiros 100 registros.</p>
        <?php endif; ?>
        <a class="btn btn-outline-secondary mt-3" href="<?= url('/admin') ?>">Voltar ao painel</a>
    </div>
</section>
