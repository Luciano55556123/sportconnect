<?php
$contentView = BASE_PATH . '/app/Views/' . $view . '.php';
$unreadNotifications = $currentUser ? (new \App\Models\Notification())->unreadCount((int) $currentUser['id']) : 0;
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$isActive = static function (string $target) use ($path): string {
    $targetPath = parse_url(url($target), PHP_URL_PATH) ?: $target;
    return rtrim($path, '/') === rtrim($targetPath, '/') || ($target !== '/' && str_starts_with($path, $targetPath)) ? 'active' : '';
};
$initials = '';
if ($currentUser) {
    foreach (array_slice(preg_split('/\s+/', trim($currentUser['name'] ?? 'U')) ?: ['U'], 0, 2) as $part) {
        $initials .= mb_strtoupper(mb_substr($part, 0, 1));
    }
}
?>
<!doctype html>
<html lang="pt-br">
<head>
    <link rel="icon" type="image/png" href="<?= asset('images/favicon.png') ?>">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? $config['name']) ?> | <?= e($config['name']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body>
<a class="skip-link" href="#conteudo">Pular para o conteudo</a>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
    <div class="container">
        <a class="navbar-brand brand-mark" href="<?= url('/') ?>"><span><i class="fa-solid fa-bolt"></i></span>SportConnect</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-label="Abrir menu"><span class="navbar-toggler-icon"></span></button>
        <div class="collapse navbar-collapse" id="nav">
            <ul class="navbar-nav ms-auto gap-lg-2">
                <?php if ($currentUser): ?>
                    <?php if ($currentUser['role'] === 'admin'): ?>
                        <li class="nav-item"><a class="nav-link <?= $isActive('/admin') ?>" href="<?= url('/admin') ?>"><i class="fa-solid fa-gauge-high"></i> Painel administrativo</a></li>
                        <li class="nav-item"><a class="nav-link <?= $isActive('/admin/campeonatos-pendentes') ?>" href="<?= url('/admin/campeonatos-pendentes') ?>"><i class="fa-solid fa-list-check"></i> Pendencias</a></li>
                        <li class="nav-item"><a class="nav-link <?= $isActive('/admin/usuarios') ?>" href="<?= url('/admin/usuarios') ?>"><i class="fa-solid fa-users"></i> Usuarios</a></li>
                        <li class="nav-item"><a class="nav-link <?= $isActive('/admin/denuncias') ?>" href="<?= url('/admin/denuncias') ?>"><i class="fa-solid fa-flag"></i> Denuncias</a></li>
                    <?php elseif ($currentUser['role'] === 'organizer'): ?>
                        <li class="nav-item"><a class="nav-link <?= $isActive('/organizador') ?>" href="<?= url('/organizador') ?>"><i class="fa-solid fa-gauge"></i> Painel</a></li>
                        <li class="nav-item"><a class="nav-link <?= $isActive('/organizador/campeonatos') ?>" href="<?= url('/organizador') ?>"><i class="fa-solid fa-trophy"></i> Meus campeonatos</a></li>
                        <li class="nav-item"><a class="nav-link <?= $isActive('/organizador/inscricoes') ?>" href="<?= url('/organizador/inscricoes') ?>"><i class="fa-solid fa-clipboard-list"></i> Inscricoes</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link <?= $isActive('/campeonatos') ?>" href="<?= url('/campeonatos') ?>"><i class="fa-solid fa-magnifying-glass"></i> Campeonatos</a></li>
                        <li class="nav-item"><a class="nav-link <?= $isActive('/atleta/historico') ?>" href="<?= url('/atleta/historico') ?>"><i class="fa-solid fa-medal"></i> Minhas inscricoes</a></li>
                        <li class="nav-item"><a class="nav-link <?= $isActive('/atleta/favoritos') ?>" href="<?= url('/atleta/favoritos') ?>"><i class="fa-solid fa-heart"></i> Favoritos</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link position-relative <?= $isActive('/notificacoes') ?>" href="<?= url('/notificacoes') ?>"><i class="fa-solid fa-bell"></i> Notificacoes<?php if ($unreadNotifications > 0): ?><span class="notification-pill"><?= (int) $unreadNotifications ?></span><?php endif; ?></a></li>
                    <li class="nav-item dropdown">
                        <button class="btn user-menu dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"><span class="avatar"><?= e($initials ?: 'U') ?></span><?= e($currentUser['name']) ?></button>
                        <ul class="dropdown-menu dropdown-menu-end shadow">
                            <li><a class="dropdown-item" href="<?= url('/' . ($currentUser['role'] === 'organizer' ? 'organizador' : ($currentUser['role'] === 'admin' ? 'admin' : 'atleta'))) ?>">Meu painel</a></li>
                            <?php if ($currentUser['role'] === 'athlete'): ?><li><a class="dropdown-item" href="<?= url('/solicitar-organizador') ?>">Solicitar organizador</a></li><?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?= url('/logout') ?>">Sair</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item"><a class="nav-link <?= $isActive('/') ?>" href="<?= url('/') ?>">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link <?= $isActive('/campeonatos') ?>" href="<?= url('/campeonatos') ?>">Campeonatos</a></li>
                    <li class="nav-item"><a class="nav-link <?= $isActive('/calendario') ?>" href="<?= url('/calendario') ?>">Calendario</a></li>
                    <li class="nav-item"><a class="nav-link <?= $isActive('/login') ?>" href="<?= url('/login') ?>">Entrar</a></li>
                    <li class="nav-item"><a class="btn btn-warning nav-cta" href="<?= url('/cadastro') ?>">Criar conta</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<main id="conteudo" tabindex="-1">
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
        <div class="d-flex gap-3 flex-wrap"><a href="<?= url('/campeonatos') ?>">Pesquisar</a><a href="<?= url('/calendario') ?>">Calendario</a><a href="<?= url('/cadastro') ?>">Participar</a><a href="<?= url('/privacidade') ?>">Privacidade</a><a href="<?= url('/termos') ?>">Termos</a></div>
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="<?= asset('js/app.js') ?>"></script>
</body>
</html>
