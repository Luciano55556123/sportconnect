<?php

namespace App\Core;

use PDO;

class Database
{
    private static ?PDO $connection = null;

    public static function connection(): PDO
    {
        if (self::$connection === null) {
            $config = require BASE_PATH . '/config/app.php';
            $db = $config['database'];
            if (($db['driver'] ?? 'mysql') === 'pgsql') {
                $dsn = "pgsql:host={$db['host']};port={$db['port']};dbname={$db['name']}";
                if (getenv('DB_SSLMODE')) {
                    $dsn .= ';sslmode=' . getenv('DB_SSLMODE');
                }
            } else {
                $dsn = "mysql:host={$db['host']};port={$db['port']};dbname={$db['name']};charset={$db['charset']}";
            }
            self::$connection = new PDO($dsn, $db['user'], $db['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            ]);
        }

        return self::$connection;
    }
}
