<?php

namespace Kineo\Tests;

use Kineo\Schema;
use PDO;
use PHPUnit\Framework\TestCase;

/** Chaque test repart d'une base SQLite neuve, en mémoire. */
abstract class BaseTestCase extends TestCase
{
    protected PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:', null, null, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        Schema::initialiser($this->pdo);
    }

    protected function empreinte(string $login): string
    {
        $req = $this->pdo->prepare('SELECT motDePasse FROM Utilisateur WHERE login = ?');
        $req->execute([$login]);
        return $req->fetchColumn();
    }

    /** Vrai si l'empreinte a été produite par password_hash(). */
    protected function estUnHachageAdapte(string $empreinte): bool
    {
        return password_get_info($empreinte)['algoName'] !== 'unknown';
    }
}
