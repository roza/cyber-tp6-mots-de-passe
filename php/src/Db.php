<?php

namespace Kineo;

use PDO;

final class Db
{
    /** SQLite, fichier var/kinegest.sqlite (ou DB_DSN). */
    public static function connexion(): PDO
    {
        $dsn = getenv('DB_DSN') ?: 'sqlite:' . __DIR__ . '/../var/kinegest.sqlite';
        return new PDO($dsn, null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
    }
}
