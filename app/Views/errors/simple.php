<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Erro') ?> | Ponto Competitivo</title>
    <link href="<?= asset('css/app.css') ?>" rel="stylesheet">
</head>
<body class="error-shell">
    <main class="error-card" role="main">
        <span><?= (int) $status ?></span>
        <h1><?= e($title ?? 'Erro') ?></h1>
        <p><?= e($safeMessage ?? 'Nao foi possivel concluir a solicitacao.') ?></p>
        <a href="<?= url('/') ?>">Voltar ao inicio</a>
    </main>
</body>
</html>
