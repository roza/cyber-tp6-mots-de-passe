<?php

namespace Kineo;

use Closure;
use PDO;

/**
 * Limitation des tentatives de connexion : après MAX_ECHECS échecs sur un même
 * login en moins de FENETRE secondes, le compte est bloqué temporairement.
 */
final class LimiteurTentatives
{
    public const MAX_ECHECS = 5;
    public const FENETRE = 15 * 60;

    private Closure $horloge;

    /** $horloge renvoie l'heure courante (horodatage Unix) ; time() par défaut. */
    public function __construct(private PDO $pdo, ?Closure $horloge = null)
    {
        $this->horloge = $horloge ?? fn(): int => time();
    }

    /** Enregistre une tentative de connexion dans JournalConnexion. */
    public function enregistrer(string $login, bool $reussie, string $adresseIP): void
    {
        // À écrire (étape 4)
    }

    /**
     * Vrai si le login a cumulé au moins MAX_ECHECS échecs dans les FENETRE
     * dernières secondes, sans connexion réussie depuis.
     */
    public function estBloque(string $login): bool
    {
        // À écrire (étape 4)
        return false;
    }
}
