<?php
require './Fonction.php';
// Vérification du nombre d'arguments.
if ($argc <= 1) {
    echo "Usage : php analyseConsole.php <URL>\n";
    exit(-1);
}

// Récupération de l'URL donnée dans la requête.
$document_url = $argv[1];
if (!filter_var($document_url, FILTER_VALIDATE_URL)) {
    die("Erreur : URL invalide.\n");
}
try {
    // Extraction des informations du CV avec Mistral.
    $texteStructure  = analyseDocumentWithMistral($document_url);
    $resultatTexte   = $texteStructure->choices[0]->message->content;
    // Suppression des caractères indésirables.
    $resultatTexte = preg_replace(
        '/^```(?:json)?\s*[\r\n]?|[\r\n]?```$/i',
        '',
        trim($resultatTexte)
    );

    // Création des dossiers pour les résultats.
    date_default_timezone_set('Europe/Paris');
    $horodate = date('Ymd_His');  // ex. "20250618_154512"

    // Dossier pour les résultats de l'extraction
    $archiveDir = __DIR__ . '/resultat/CV_archive_horodate/' . $horodate;
    if (!is_dir($archiveDir)) {
        mkdir($archiveDir, 0755, true);
    }

    // Dossier pour les résultats de comparaison.
    $comparaisonDir = $archiveDir . '/Comparaison';
    if (!is_dir($comparaisonDir)) {
        mkdir($comparaisonDir, 0755, true);
    }

    // Écriture du JSON extrait dans le dossier CV_archive_horodate.
    $nomOriginal = basename(parse_url($document_url, PHP_URL_PATH)); // ex: "moncv.pdf"
    $nomSansExt = pathinfo($nomOriginal, PATHINFO_FILENAME); // ex: "moncv"
    $cheminJson = $archiveDir . '/' . $nomSansExt . '.json';
    file_put_contents($cheminJson, $resultatTexte);

    // Comparaison automatique.
    $fichierExtrait = basename($cheminJson); // "moncv.json"
    $nomFichierRef = rechercheNomFichierRef($fichierExtrait, __DIR__ . '/attendus');

    if ($nomFichierRef) {
        $fichierReference = __DIR__ . '/attendus/' . $nomFichierRef;

        // Capture la sortie de compareFiles().
        ob_start();
        compareFiles($fichierReference, $cheminJson);
        $cmpOutput = ob_get_clean();

        // Sauvegarde du résultat de comparaison dans le dossier Comparaison.
        $fichierResult = $comparaisonDir . '/comparaison_' . $nomSansExt . '.txt';
        file_put_contents($fichierResult, $cmpOutput);
        echo "Comparaison enregistrée dans : $fichierResult\n";
    } else {
        echo "Aucune référence trouvée pour '$fichierExtrait'.\n";
    }

} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}
