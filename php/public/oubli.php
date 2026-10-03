<?php
// « Mot de passe oublié ». Sans serveur de messagerie, le courriel qui serait
// envoyé est affiché à l'écran (cadre « courriel simulé »).
require __DIR__ . '/../vendor/autoload.php';

use Kineo\Db;
use Kineo\ReinitialisationService;

$message = '';
$courriel = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $courriel = (new ReinitialisationService(Db::connexion()))->demander($_POST['login'] ?? '');
    $message = $courriel === null
        ? 'Aucun compte ne correspond à cet identifiant.'
        : 'Un courriel vient de vous être envoyé.';
}
?>
<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><title>KinéGest — mot de passe oublié</title></head>
<body>
  <h1>Mot de passe oublié</h1>
  <form method="post">
    <p><label>Identifiant <input name="login" required></label> <button>Envoyer</button></p>
  </form>
  <p><?= htmlspecialchars($message) ?></p>
  <?php if ($courriel !== null): ?>
    <fieldset><legend>Courriel simulé</legend><pre><?= htmlspecialchars($courriel) ?></pre></fieldset>
  <?php endif; ?>
</body>
</html>
