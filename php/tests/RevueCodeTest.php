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
}
