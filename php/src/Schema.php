<?php

namespace Kineo;

use PDO;

/** (Ré)initialise la base à partir des scripts de ../sql. Pas à corriger. */
final class Schema
{
    public static function initialiser(PDO $pdo): void
    {
        foreach (['schema.sql', 'donnees.sql'] as $f) {
            foreach (preg_split('/;\s*\n/', file_get_contents(__DIR__ . "/../../sql/$f")) as $sql) {
                if (trim(preg_replace('/^\s*--.*$/m', '', $sql)) !== '') {
                    $pdo->exec($sql);
                }
            }
        }
    }
}
