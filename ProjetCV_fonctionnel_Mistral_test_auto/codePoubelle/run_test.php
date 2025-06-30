<?php
// définit le type de contenu de la réponse HTTP en tant que texte brut
header('Content-Type: text/plain');
// Récupère les données JSON envoyées en POST et les convertit en tableau associatif PHP
$data = json_decode(file_get_contents('php://input'), true);
// vérification des champ requis
if (!isset($data['file1']) || !isset($data['file2'])) {
    http_response_code(400);
    echo "Champs manquants : 'file1' et 'file2' sont requis.";
    exit;
}

$file1 = escapeshellarg($data['file1']);
$file2 = escapeshellarg($data['file2']);

$output = [];

// Exécution des tests
exec("php testFichier.php $file1 $file2 2>&1", $output, $return_var);

if ($return_var !== 0) {
    http_response_code(500);
    echo "Erreur lors de l'exécution du test JSON.\n";
    echo implode("\n", $output);
    exit;
}

echo implode("\n", $output);

