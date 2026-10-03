<?php
// Connexion — lancer : php -S localhost:8000 -t public
require __DIR__ . '/../vendor/autoload.php';

use Kineo\CompteService;
use Kineo\Db;

session_start();
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $utilisateur = (new CompteService(Db::connexion()))
        ->authentifier($_POST['login'] ?? '', $_POST['mdp'] ?? '');
    if ($utilisateur !== null) {
        $_SESSION['utilisateur'] = $utilisateur;
        $message = 'Bienvenue, ' . $utilisateur['nom'] . '.';
    } else {
        $message = 'Identifiant ou mot de passe incorrect.';
    }
}
?>
<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><title>KinéGest — connexion</title></head>
<body>
  <h1>KinéGest</h1>
  <form method="post">
    <p><label>Identifiant <input name="login" required></label></p>
    <p><label>Mot de passe <input name="mdp" type="password" required></label></p>
    <p><button>Se connecter</button> · <a href="oubli.php">Mot de passe oublié ?</a></p>
  </form>
  <p><?= htmlspecialchars($message) ?></p>
</body>
</html>
