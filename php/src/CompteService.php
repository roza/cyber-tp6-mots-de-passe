<?php

namespace Kineo;

use PDO;

final class CompteService
{
    public function __construct(private PDO $pdo)
    {
    }

    /** Crée un compte. */
    public function inscrire(string $login, string $nom, string $mdp): void
    {
        $req = $this->pdo->prepare(
            'INSERT INTO Utilisateur (login, nom, motDePasse) VALUES (:login, :nom, :mdp)'
        );
        $req->execute([':login' => $login, ':nom' => $nom, ':mdp' => sha1($mdp)]);
    }

    /**
     * Renvoie l'utilisateur ['id', 'login', 'nom'] si le login et le mot de
     * passe sont corrects, null sinon.
     */
    public function authentifier(string $login, string $mdp): ?array
    {
        $req = $this->pdo->prepare('SELECT id, login, nom, motDePasse FROM Utilisateur WHERE login = :login');
        $req->execute([':login' => $login]);
        $ligne = $req->fetch(PDO::FETCH_ASSOC);

        if ($ligne === false || sha1($mdp) !== $ligne['motDePasse']) {
            return null;
        }
        unset($ligne['motDePasse']);
        return $ligne;
    }

    /** Remplace le mot de passe d'un utilisateur. */
    public function changerMotDePasse(int $idUtilisateur, string $mdp): void
    {
        $req = $this->pdo->prepare('UPDATE Utilisateur SET motDePasse = :mdp WHERE id = :id');
        $req->execute([':mdp' => sha1($mdp), ':id' => $idUtilisateur]);
    }
}
