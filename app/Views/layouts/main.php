<?php
$contentView = BASE_PATH . '/app/Views/' . $view . '.php';
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? $config['name']) ?> | <?= e($config['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand fw-bold" href="<?= url('/') ?>"><i class="fa-solid fa-trophy me-2"></i>SportConnect</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav ms-auto gap-lg-2">
                <li class="nav-item"><a class="nav-link" href="<?= url('/campeonatos') ?>">Campeonatos</a></li>
                <li class="nav-item"><a class="nav-link" href="<?= url('/calendario') ?>">Calendario</a></li>
                <?php if ($currentUser): ?>
                    <?php $panelPath = in_array($currentUser['role'], ['organizer', 'organizador'], true) ? 'organizador' : ($currentUser['role'] === 'admin' ? 'admin' : 'atleta'); ?>
                    <li class="nav-item"><a class="nav-link" href="<?= url('/' . $panelPath) ?>">Painel</a></li>
                    <?php if (($currentUser['role'] ?? '') === 'athlete'): ?>
                        <li class="nav-item"><a class="nav-link" href="<?= url('/atleta/historico') ?>">Minhas inscricoes</a></li>
                    <?php endif; ?>
                    <?php if ($currentUser['role'] === 'admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?= url('/admin/solicitacoes-organizador') ?>">
                                Solicitacoes de organizador
                                <?php if (($adminPendingOrganizerRequests ?? 0) > 0): ?><span class="badge text-bg-warning ms-1"><?= (int) $adminPendingOrganizerRequests ?></span><?php endif; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="btn btn-sm btn-light" href="<?= url('/logout') ?>">Sair</a></li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link" href="<?= url('/login') ?>">Entrar</a></li>
                    <li class="nav-item"><a class="btn btn-sm btn-warning" href="<?= url('/cadastro') ?>">Criar conta</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main>
    <?php foreach (['success' => 'success', 'error' => 'danger'] as $key => $class): ?>
        <?php if ($message = flash($key)): ?>
            <div class="container pt-3"><div class="alert alert-<?= $class ?>"><?= e($message) ?></div></div>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php require $contentView; ?>
</main>

<footer class="footer mt-5 py-4">
    <div class="container d-flex flex-column flex-md-row justify-content-between gap-3">
        <div><strong>SportConnect</strong><br><span>Plataforma regional para atletas, equipes e organizadores.</span></div>
        <div class="d-flex gap-3"><a href="<?= url('/campeonatos') ?>">Pesquisar</a><a href="<?= url('/calendario') ?>">Calendario</a><a href="<?= url('/cadastro') ?>">Participar</a></div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
