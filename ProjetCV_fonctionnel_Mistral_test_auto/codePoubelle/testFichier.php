<?php

// Fonction pour charger le tableau de mappage
function loadCustomMapping($mappingFile) {
    $json = file_get_contents($mappingFile);
    return json_decode($json, true);
}

// Fonction comparant deux tableaux JSON
// Input:
// resultatAttendu qui est le résultat attendu
// resultatObtenu qui est le résultat obtenu par le code
// total initialement à 0, est le total de comparaison
// matches initialement à 0, montre les correspondances entre les 2 CV
// differencesCount initialement à 0, est le nombre de différence présente entre les 2 CV
// Output:
// pourcentage de correspondance
// le nombre de différence
function deepEqual($resultatAttendu, $resultatObtenu, $path = '', &$total = 0, &$matches = 0, &$differencesCount = 0) {
    if (is_object($resultatAttendu)) $resultatAttendu = get_object_vars($resultatAttendu);
    if (is_object($resultatObtenu)) $resultatObtenu = get_object_vars($resultatObtenu);

    if (is_array($resultatAttendu) && is_array($resultatObtenu)) {
        $keys = array_unique(array_merge(array_keys($resultatAttendu), array_keys($resultatObtenu)));

        foreach ($keys as $key) {
            $newPath = $path ? "$path.$key" : $key;

            if (!array_key_exists($key, $resultatAttendu) || !array_key_exists($key, $resultatObtenu)) {
                $total++;
                $differencesCount++;
                continue;
            }

            deepEqual($resultatAttendu[$key], $resultatObtenu[$key], $newPath, $total, $matches, $differencesCount);
        }
        return;
    }

    $total++;
    if ((is_string($resultatAttendu) && is_string($resultatObtenu) && strtolower($resultatAttendu) === strtolower($resultatObtenu)) || $resultatAttendu === $resultatObtenu) {
        $matches++;
    }
    $differencesCount++;

}

// Fonction pour comparer deux fichiers
function compareFiles($referenceFile, $extractedFile) {
    $content1 = file_get_contents($referenceFile);
    $content2 = file_get_contents($extractedFile);
    $data1 = json_decode($content1);
    $data2 = json_decode($content2);

    $totalComparisons = 0;
    $matchingComparisons = 0;
    $differencesCount = 0;

    deepEqual($data1, $data2, '', $totalComparisons, $matchingComparisons, $differencesCount);
    $pourcentage = $totalComparisons > 0 ? round(($matchingComparisons / $totalComparisons) * 100, 2) : 100;

    echo "Similarité : $pourcentage%\n";
    echo "Différences totales : $differencesCount\n";
    if ($pourcentage === 100.0) {
        echo "Les fichiers JSON sont identiques.\n";
    }
    echo "Les fichiers JSON sont différents.\n";

}

// Fonction principale pour effectuer les comparaisons
function performComparisons($mappingFile, $referenceDir, $extractedDir) {
    $mappings = loadCustomMapping($mappingFile);

    foreach ($mappings as $mapping) {
        $referenceFile = $referenceDir . '/' . $mapping['nomReference'];
        $extractedFile = $extractedDir . '/' . $mapping['nomBasique'];

        if (file_exists($referenceFile) && file_exists($extractedFile)) {
            echo "Comparaison entre {$mapping['nomReference']} et {$mapping['nomBasique']}:\n";
            compareFiles($referenceFile, $extractedFile);
            echo "\n";
        }
        echo "Un des fichiers pour la comparaison n'existe pas: {$referenceFile} ou {$extractedFile}\n\n";

    }
}

// Configuration des chemins
$mappingFile = 'C:\Users\romua\OneDrive\Documents\testTitouan\projetCV\ProjetCV_fonctionnel_Mistral\listComparaison.json';
$referenceDir = 'C:\Users\romua\OneDrive\Documents\testTitouan\projetCV\ProjetCV_fonctionnel_Mistral\attendus';
$extractedDir = 'C:\Users\romua\OneDrive\Documents\testTitouan\projetCV\ProjetCV_fonctionnel_Mistral\CV_archive';

// Exécuter les comparaisons
performComparisons($mappingFile, $referenceDir, $extractedDir);
?>
