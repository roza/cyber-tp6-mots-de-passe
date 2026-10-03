<?php

namespace Kineo\Tests;

use Kineo\CompteService;
use Kineo\ReinitialisationService;

final class ReinitialisationServiceTest extends BaseTestCase
{
    private int $maintenant = 1_800_000_000;

    private function service(): ReinitialisationService
    {
        return new ReinitialisationService($this->pdo, fn(): int => $this->maintenant);
    }

    /** Le jeton est récupéré dans le lien du courriel. */
    private function jetonDuCourriel(string $courriel): string
    {
        $this->assertMatchesRegularExpression(
            '/jeton=([0-9a-f]{64})\b/',
            $courriel,
            'Le courriel doit contenir un lien ...?jeton=<64 caractères hexadécimaux>'
        );
        preg_match('/jeton=([0-9a-f]{64})\b/', $courriel, $m);
        return $m[1];
    }

    public function testUnCompteInconnuNeDonneLieuAAucunCourriel(): void
    {
        $this->assertNull($this->service()->demander('inconnu'));
        $this->assertSame(0, (int) $this->pdo->query('SELECT COUNT(*) FROM JetonReinit')->fetchColumn());
    }

    /** Étape 3 — un lien avec un jeton, jamais un mot de passe. */
    public function testLeCourrielContientUnLienEtAucunMotDePasse(): void
    {
        $courriel = $this->service()->demander('a.martin');
        $this->jetonDuCourriel($courriel);
        $this->assertStringNotContainsStringIgnoringCase('mot de passe :', $courriel);
    }

    /** Étape 3 — la demande ne change pas le mot de passe actuel. */
    public function testLaDemandeNeChangePasLeMotDePasse(): void
    {
        $this->service()->demander('a.martin');
        $this->assertNotNull((new CompteService($this->pdo))->authentifier('a.martin', 'Genou-42'));
    }

    /** Étape 3 — la base ne contient pas le jeton lui-même. */
    public function testLaBaseNeStockeQueLEmpreinteDuJeton(): void
    {
        $jeton = $this->jetonDuCourriel($this->service()->demander('a.martin'));
        $stocke = $this->pdo->query('SELECT empreinteJeton FROM JetonReinit')->fetchColumn();
        $this->assertNotFalse($stocke, 'La demande doit être enregistrée dans JetonReinit');
        $this->assertStringNotContainsString($jeton, $stocke);
    }

    /** Étape 3 — un jeton valide permet de choisir un nouveau mot de passe. */
    public function testUnJetonValideChangeLeMotDePasse(): void
    {
        $jeton = $this->jetonDuCourriel($this->service()->demander('a.martin'));
        $this->maintenant += 5 * 60;
        $this->assertTrue($this->service()->reinitialiser($jeton, 'Nouveau-Genou-2026'));

        $comptes = new CompteService($this->pdo);
        $this->assertNotNull($comptes->authentifier('a.martin', 'Nouveau-Genou-2026'));
        $this->assertNull($comptes->authentifier('a.martin', 'Genou-42'), 'L\'ancien mot de passe ne doit plus fonctionner');
        $this->assertTrue($this->estUnHachageAdapte($this->empreinte('a.martin')));
    }

    /** Étape 3 — un jeton ne sert qu'une fois. */
    public function testUnJetonNeSertQuUneFois(): void
    {
        $jeton = $this->jetonDuCourriel($this->service()->demander('a.martin'));
        $this->assertTrue($this->service()->reinitialiser($jeton, 'Premier-2026'));
        $this->assertFalse($this->service()->reinitialiser($jeton, 'Second-2026'));
    }

    /** Étape 3 — un jeton expire au bout de 15 minutes. */
    public function testUnJetonExpireAuBoutDeQuinzeMinutes(): void
    {
        $jeton = $this->jetonDuCourriel($this->service()->demander('a.martin'));
        $this->maintenant += 16 * 60;
        $this->assertFalse($this->service()->reinitialiser($jeton, 'Trop-tard-2026'));
        $this->assertNotNull((new CompteService($this->pdo))->authentifier('a.martin', 'Genou-42'));
    }

    /** Étape 3 — un jeton inventé est refusé. */
    public function testUnJetonInconnuEstRefuse(): void
    {
        $this->service()->demander('a.martin');
        $this->assertFalse($this->service()->reinitialiser(str_repeat('0', 64), 'Pirate-2026'));
    }
}
