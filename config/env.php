<?php

function carregarEnv(string $caminho): void
{
    if (!is_file($caminho)) {
        return;
    }

    $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    foreach ($linhas as $linha) {
        $linha = trim($linha);

        if ($linha === '' || str_starts_with($linha, '#')) {
            continue;
        }

        [$chave, $valor] = array_pad(explode('=', $linha, 2), 2, '');

        $chave = trim($chave);
        $valor = trim($valor);
        $valor = trim($valor, "\"'");

        if ($chave !== '') {
            $_ENV[$chave] = $valor;
            putenv($chave . '=' . $valor);
        }
    }
}

$basePath = dirname(__DIR__);
carregarEnv($basePath . '/.env');
carregarEnv($basePath . '/.env/.env');
