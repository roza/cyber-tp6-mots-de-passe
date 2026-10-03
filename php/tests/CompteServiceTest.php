<?php

namespace Kineo\Tests;

use Kineo\CompteService;

final class CompteServiceTest extends BaseTestCase
{
    private function comptes(): CompteService
    {
        return new CompteService($this->pdo);
    }

    public function testUnNouveauCompteSeConnecte(): void
    {
        $this->comptes()->inscrire('j.dupont', 'Jean Dupont', 'Coude-2026');
        $this->assertSame('Jean Dupont', $this->comptes()->authentifier('j.dupont', 'Coude-2026')['nom']);
        $this->assertNull($this->comptes()->authentifier('j.dupont', 'Coude-2025'));
    }

    /** Étape 1 — l'inscription utilise password_hash(). */
    public function testUnNouveauCompteEstHacheAvecPasswordHash(): void
    {
        $this->comptes()->inscrire('j.dupont', 'Jean Dupont', 'Coude-2026');
        $this->assertTrue(
            $this->estUnHachageAdapte($this->empreinte('j.dupont')),
            'L\'empreinte doit venir de password_hash()'
        );
    }

    /** Étape 1 — le sel : même mot de passe, empreintes différentes. */
    public function testDeuxComptesAuMemeMotDePasseOntDesEmpreintesDifferentes(): void
    {
        $this->comptes()->inscrire('j.dupont', 'Jean Dupont', 'Coude-2026');
        $this->comptes()->inscrire('l.moreau', 'Lea Moreau', 'Coude-2026');
        $this->assertNotSame($this->empreinte('j.dupont'), $this->empreinte('l.moreau'));
    }

    public function testUnAncienCompteSeConnecteToujours(): void
    {
        $this->assertSame('Anne Martin', $this->comptes()->authentifier('a.martin', 'Genou-42')['nom']);
        $this->assertNull($this->comptes()->authentifier('a.martin', 'Genou-43'));
    }

    /** Étape 2 — à la connexion, l'ancienne empreinte SHA-1 est remplacée. */
    public function testLaConnexionMigreUneAncienneEmpreinte(): void
    {
        $this->comptes()->authentifier('a.martin', 'Genou-42');
        $this->assertTrue(
            $this->estUnHachageAdapte($this->empreinte('a.martin')),
            'Après une connexion réussie, l\'empreinte doit avoir été refaite avec password_hash()'
        );
    }

    /** Étape 2 — après la migration, le même mot de passe fonctionne, un autre non. */
    public function testApresMigrationLeMotDePasseFonctionneToujours(): void
    {
        $this->comptes()->authentifier('a.martin', 'Genou-42');
        $this->assertNotNull($this->comptes()->authentifier('a.martin', 'Genou-42'));
        $this->assertNull($this->comptes()->authentifier('a.martin', 'Genou-43'));
    }

    /** Étape 2 — un mauvais mot de passe ne déclenche aucune migration. */
    public function testUnEchecNeModifiePasLEmpreinte(): void
    {
        $avant = $this->empreinte('m.dalmeida');
        $this->comptes()->authentifier('m.dalmeida', 'mauvais');
        $this->assertSame($avant, $this->empreinte('m.dalmeida'));
    }
}
