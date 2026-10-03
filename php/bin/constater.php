<?php
// Étape 0 : constater les deux faiblesses de l'ancien stockage, sans rien casser.
//   php bin/constater.php
require __DIR__ . "/../vendor/autoload.php";

$pdo = Kineo\Db::connexion();
echo "1. Les empreintes stockées aujourd'hui\n\n";
foreach ($pdo->query("SELECT login, motDePasse FROM Utilisateur ORDER BY id") as $u) {
    printf("   %-12s %s\n", $u["login"], $u["motDePasse"]);
}
$doublons = $pdo->query("SELECT motDePasse, COUNT(*) n FROM Utilisateur GROUP BY motDePasse HAVING n > 1")->fetchAll();
echo "\n   Empreintes identiques : " . count($doublons) . " (donc des mots de passe identiques)\n\n";

echo "2. Combien d'empreintes cette machine calcule-t-elle en une seconde ?\n\n";
$mesurer = function (callable $f): int {
    $n = 0;
    $fin = microtime(true) + 1.0;
    while (microtime(true) < $fin) { $f($n); $n++; }
    return $n;
};
$sha1 = $mesurer(fn($i) => sha1("candidat$i"));
$lent = $mesurer(fn($i) => password_hash("candidat$i", PASSWORD_DEFAULT));
printf("   sha1()           : %12s par seconde\n", number_format($sha1, 0, ",", " "));
printf("   password_hash()  : %12s par seconde\n", number_format($lent, 0, ",", " "));
printf("\n   Rapport : environ %s fois plus lent.\n", number_format(intdiv($sha1, max(1, $lent)), 0, ",", " "));
echo "   C'est ce que paie un attaquant pour CHAQUE mot de passe qu'il essaie.\n";
