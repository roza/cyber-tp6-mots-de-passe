<?php
// Crée (ou recrée) la base : composer init-db
require __DIR__ . "/../vendor/autoload.php";
Kineo\Schema::initialiser(Kineo\Db::connexion());
echo "Base initialisée.\n";
