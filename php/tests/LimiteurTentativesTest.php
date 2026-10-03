<?php

namespace Kineo\Tests;

use Kineo\LimiteurTentatives;

/** Étape 4 — limiter les tentatives de connexion. */
final class LimiteurTentativesTest extends BaseTestCase
{
    private int $maintenant = 1_800_000_000;

    private function limiteur(): LimiteurTentatives
    {
        return new LimiteurTentatives($this->pdo, fn(): int => $this->maintenant);
    }

    private function echouer(string $login, int $fois, string $ip = '192.0.2.10'): void
    {
        for ($i = 0; $i < $fois; $i++) {
            $this->limiteur()->enregistrer($login, false, $ip);
            $this->maintenant += 10;
        }
    }

    public function testChaqueTentativeEstJournalisee(): void
    {
        $this->limiteur()->enregistrer('a.martin', false, '192.0.2.10');
        $this->limiteur()->enregistrer('a.martin', true, '192.0.2.10');

        $lignes = $this->pdo->query('SELECT login, horodatage, reussie, adresseIP FROM JournalConnexion ORDER BY id')
            ->fetchAll();
        $this->assertCount(2, $lignes, 'Chaque tentative doit ajouter une ligne dans JournalConnexion');
        $this->assertSame('a.martin', $lignes[0]['login']);
        $this->assertSame(1_800_000_000, (int) $lignes[0]['horodatage']);
        $this->assertSame(0, (int) $lignes[0]['reussie']);
        $this->assertSame('192.0.2.10', $lignes[0]['adresseIP']);
        $this->assertSame(1, (int) $lignes[1]['reussie']);
    }

    public function testQuatreEchecsNeBloquentPas(): void
    {
        $this->echouer('a.martin', 4);
        $this->assertFalse($this->limiteur()->estBloque('a.martin'));
    }

    public function testCinqEchecsBloquentLeCompte(): void
    {
        $this->echouer('a.martin', 5);
        $this->assertTrue($this->limiteur()->estBloque('a.martin'), 'Cinq échecs en quelques minutes doivent bloquer le compte');
    }

    public function testUnLoginInexistantSeBloqueAussi(): void
    {
        $this->echouer('inconnu', 5);
        $this->assertTrue(
            $this->limiteur()->estBloque('inconnu'),
            'Un login inexistant doit se comporter comme un autre : sinon, on apprend quels comptes existent'
        );
    }

    public function testLesEchecsDUnAutreCompteNeComptentPas(): void
    {
        $this->echouer('s.leroy', 5);
        $this->assertFalse($this->limiteur()->estBloque('a.martin'));
    }

    public function testLeBlocageCesseApresLaFenetre(): void
    {
        $this->echouer('a.martin', 5);
        $this->maintenant += LimiteurTentatives::FENETRE;
        $this->assertFalse($this->limiteur()->estBloque('a.martin'), 'Le blocage est temporaire : 15 minutes');
    }

    public function testUneConnexionReussieRemetLeCompteurAZero(): void
    {
        $this->echouer('a.martin', 4);
        $this->limiteur()->enregistrer('a.martin', true, '192.0.2.10');
        $this->maintenant += 10;
        $this->echouer('a.martin', 4);
        $this->assertFalse($this->limiteur()->estBloque('a.martin'), 'Les échecs comptent depuis la dernière connexion réussie');
    }
}
