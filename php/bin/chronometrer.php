<?php
// Pour aller plus loin (étape 5) : le temps de réponse révèle-t-il quels comptes existent ?
//   php bin/chronometrer.php
// À lancer après l'étape 1 (password_hash). Travaille sur une base en mémoire :
// votre base var/kinegest.sqlite n'est pas touchée.
require __DIR__ . "/../vendor/autoload.php";

use Kineo\CompteService;
use Kineo\Schema;

$pdo = new PDO('sqlite::memory:', null, null, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);
Schema::initialiser($pdo);
$comptes = new CompteService($pdo);
$comptes->inscrire('chrono.existant', 'Compte de mesure', 'Le-bon-mot-de-passe-42');

$empreinte = $pdo->query("SELECT motDePasse FROM Utilisateur WHERE login = 'chrono.existant'")->fetchColumn();
if (password_get_info($empreinte)['algoName'] === 'unknown') {
    exit("inscrire() n'utilise pas encore password_hash() : faites d'abord l'étape 1.\n");
}

/** Durée médiane, en millisecondes, d'une tentative de connexion ratée. */
$mesurer = function (string $login) use ($comptes): float {
    $durees = [];
    for ($i = 0; $i < 15; $i++) {
        $debut = hrtime(true);
        $comptes->authentifier($login, 'mauvais-mot-de-passe');
        $durees[] = (hrtime(true) - $debut) / 1e6;
    }
    sort($durees);
    return $durees[intdiv(count($durees), 2)];
};

$existant = $mesurer('chrono.existant');
$inconnu  = $mesurer('personne.inconnue');

printf("PHP %s, empreinte %s…\n\n", PHP_VERSION, substr($empreinte, 0, 7));
printf("   login existant, mauvais mot de passe : %8.1f ms\n", $existant);
printf("   login inconnu                        : %8.1f ms\n\n", $inconnu);

$ecart = abs($existant - $inconnu);
if ($ecart > 0.5 * max($existant, $inconnu)) {
    printf("   Écart de %.0f ms : le chronomètre dit quels logins existent.\n", $ecart);
} else {
    echo "   Pas d'écart notable : le temps de réponse ne trahit plus les comptes.\n";
}
