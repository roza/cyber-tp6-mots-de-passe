<?php

namespace Kineo;

use Closure;
use PDO;

/**
 * « Mot de passe oublié ». L'horloge est injectable pour que les tests
 * puissent simuler le passage du temps.
 */
final class ReinitialisationService
{
    private Closure $maintenant;

    public function __construct(private PDO $pdo, ?Closure $maintenant = null)
    {
        $this->maintenant = $maintenant ?? fn(): int => time();
    }

    /**
     * Traite une demande de réinitialisation pour $login.
     * Renvoie le texte du courriel à envoyer à l'utilisateur, ou null si le
     * compte n'existe pas (la page affiche le même message dans les deux cas).
     */
    public function demander(string $login): ?string
    {
        $req = $this->pdo->prepare('SELECT id FROM Utilisateur WHERE login = :login');
        $req->execute([':login' => $login]);
        $id = $req->fetchColumn();
        if ($id === false) {
            return null;
        }

        $nouveau = substr(md5((string) rand()), 0, 8);
        (new CompteService($this->pdo))->changerMotDePasse((int) $id, $nouveau);

        return "Bonjour,\nVotre identifiant : $login\nVotre nouveau mot de passe : $nouveau\n"
             . "Vous pourrez le changer sur le site si vous le souhaitez.";
    }

    /**
     * Remplace le mot de passe si $jeton est valide : connu, non utilisé, non
     * expiré. Renvoie true si le mot de passe a été changé.
     */
    public function reinitialiser(string $jeton, string $nouveauMdp): bool
    {
        throw new \LogicException('À écrire (étape 3)');
    }
}
