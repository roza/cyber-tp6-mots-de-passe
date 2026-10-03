<?php
// Choix d'un nouveau mot de passe, depuis le lien reçu par courriel.
require __DIR__ . '/../vendor/autoload.php';

use Kineo\Db;
use Kineo\ReinitialisationService;

$jeton = $_GET['jeton'] ?? $_POST['jeton'] ?? '';
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $ok = (new ReinitialisationService(Db::connexion()))->reinitialiser($jeton, $_POST['mdp'] ?? '');
    $message = $ok ? 'Mot de passe changé. Vous pouvez vous connecter.' : 'Lien invalide ou expiré.';
}
?>
<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><title>KinéGest — nouveau mot de passe</title></head>
<body>
  <h1>Choisir un nouveau mot de passe</h1>
  <form method="post">
    <input type="hidden" name="jeton" value="<?= htmlspecialchars($jeton) ?>">
    <p><label>Nouveau mot de passe <input name="mdp" type="password" minlength="12" required></label></p>
    <p><button>Valider</button></p>
  </form>
  <p><?= htmlspecialchars($message) ?></p>
  <p><a href="index.php">Retour à la connexion</a></p>
</body>
</html>
