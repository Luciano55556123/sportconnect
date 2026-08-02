<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Security;

class HealthController extends Controller
{
    public function show(): void
    {
        if (Security::isProduction($this->config)) {
            $expected = (string) ($this->config['health_token'] ?? '');
            $provided = (string) ($_GET['token'] ?? $_SERVER['HTTP_X_HEALTH_TOKEN'] ?? '');
            if ($expected === '' || !hash_equals($expected, $provided)) {
                Security::abort(404, 'Pagina nao encontrada.');
            }
        }

        $database = 'ok';
        try {
            Database::connection()->query('SELECT 1');
        } catch (\Throwable $exception) {
            error_log('health_database_failed ' . $exception->getMessage());
            $database = 'unavailable';
            http_response_code(503);
        }

        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'application' => 'ok',
            'database' => $database,
            'timestamp' => gmdate('c'),
            'version' => 'sportconnect',
        ], JSON_UNESCAPED_SLASHES);
    }
}
