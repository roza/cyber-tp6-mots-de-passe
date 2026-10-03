-- Base KinéGest (version simplifiée) — SQLite
DROP TABLE IF EXISTS JetonReinit;
DROP TABLE IF EXISTS Utilisateur;

CREATE TABLE Utilisateur (
    id          INTEGER PRIMARY KEY AUTOINCREMENT,
    login       VARCHAR(50)  NOT NULL UNIQUE,
    nom         VARCHAR(80)  NOT NULL,
    motDePasse  VARCHAR(255) NOT NULL   -- empreinte
);

-- Demandes de réinitialisation du mot de passe
CREATE TABLE JetonReinit (
    id              INTEGER PRIMARY KEY AUTOINCREMENT,
    idUtilisateur   INTEGER      NOT NULL REFERENCES Utilisateur(id),
    empreinteJeton  VARCHAR(255) NOT NULL,
    expireLe        INTEGER      NOT NULL,   -- horodatage Unix
    utilise         INTEGER      NOT NULL DEFAULT 0
);
