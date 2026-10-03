# TP6 — Stocker les mots de passe et les réinitialiser

**BTS SIO 2ᵉ année · option SLAM · Bloc 3 — cybersécurité**

L'application **KinéGest** stocke les mots de passe en **SHA-1, sans sel**, et
son « mot de passe oublié » envoie un nouveau mot de passe par courriel. Votre
travail : passer à `password_hash()`, **migrer** les comptes existants sans
connaître leurs mots de passe, et réécrire la réinitialisation avec un
**jeton** à usage unique et à durée limitée. Les tests vous guident ; l'énoncé
complet est dans le PDF du TP.

## 1. Récupérer le projet

1. Sur la page de ce dépôt : **Use this template** › **Create a new repository**.
2. Gardez le nom `cyber-tp6-mots-de-passe`, cochez **Private**.
3. **Settings** › **Collaborators** : ajoutez `roza`.
4. Clonez **votre** dépôt et ouvrez le **dossier** dans VS Code.

Sans PHP sur votre poste : **Code** › **Codespaces** › **Create codespace on
main** (pensez à l'arrêter en fin de séance).

## 2. Lancer

Prérequis : PHP 8.2 ou plus avec `pdo_sqlite` (vérifiez avec `php -m`), et
Composer.

```sh
cd php
composer install
composer init-db                    # crée la base SQLite var/kinegest.sqlite
php bin/constater.php               # étape 0 : constater les deux faiblesses
vendor/bin/phpunit                  # les tests
php -S localhost:8000 -t public     # l'application
```

Comptes existants (empreintes SHA-1) :

| Identifiant | Mot de passe |
|---|---|
| `a.martin` | `Genou-42` |
| `s.leroy` | `Kineo2026` |
| `c.petit` | `Kineo2026` |
| `m.dalmeida` | `Epaule-17` |

*Sans serveur de messagerie, le courriel de réinitialisation est affiché à
l'écran, dans un cadre « courriel simulé ».*

## 3. L'intégration continue

À chaque `git push`, GitHub lance les tests (onglet **Actions**). **Au départ,
plusieurs tests sont rouges : c'est normal.** Le TP est terminé quand tout est vert.

## Organisation

```
sql/                          schéma (Utilisateur, JetonReinit) et comptes existants
php/src/CompteService.php     inscription, connexion, changement de mot de passe
php/src/ReinitialisationService.php   « mot de passe oublié »
php/public/                   les pages : connexion, mot de passe oublié, nouveau mot de passe
php/bin/constater.php         étape 0
php/tests/                    tests PHPUnit
```
