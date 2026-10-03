<?php

namespace Kineo\Tests;

use PHPUnit\Framework\TestCase;

/**
 * Revue de code automatique : un jeton de réinitialisation doit être
 * imprévisible. rand(), mt_rand(), uniqid() et md5() n'y suffisent pas :
 * seul random_bytes() (ou random_int()) convient.
 */
final class RevueCodeTest extends TestCase
{
    public function testLeJetonVientDUnGenerateurCryptographique(): void
    {
        $code = file_get_contents(__DIR__ . '/../src/ReinitialisationService.php');
        $this->assertDoesNotMatchRegularExpression(
            '/\b(rand|mt_rand|uniqid|md5|lcg_value)\s*\(/',
            $code,
            'ReinitialisationService : utiliser random_bytes(), pas rand(), mt_rand(), uniqid() ni md5()'
        );
        $this->assertMatchesRegularExpression('/\brandom_(bytes|int)\s*\(/', $code);
    }

    /** Étape 4 — la page de connexion consulte et alimente le limiteur. */
    public function testLaPageDeConnexionUtiliseLeLimiteur(): void
    {
        $code = file_get_contents(__DIR__ . '/../public/index.php');
        $this->assertMatchesRegularExpression('/->estBloque\s*\(/', $code, 'index.php doit appeler estBloque()');
        $this->assertMatchesRegularExpression('/->enregistrer\s*\(/', $code, 'index.php doit enregistrer chaque tentative');
        $this->assertLessThan(
            strpos($code, '->authentifier('),
            strpos($code, '->estBloque('),
            'Le blocage se vérifie AVANT de tester le mot de passe'
        );
    }
}
