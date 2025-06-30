<?php

// Lance un chronomètre.
$startTime = microtime(true);
set_time_limit(0);
ini_set('max_execution_time', '300');
require './FonctionUploadGPT.php';

header('Content-Type: application/json');
// Configuration des dossiers globaux.
date_default_timezone_set('Europe/Paris');

$dateFolder = date('Ymd_H'); // Un dossier par jour.
$cheminArchive = __DIR__ . '/CV_archive_horodate/' . $dateFolder . '_CV_archive'; //archive
$cheminResultat  = __DIR__ . '/CV_archive_horodate/Comparaison/'; //résultat

// Création des dossiers globaux si nécessaire.
if (!is_dir($cheminArchive)) mkdir($cheminArchive, 0755, true); // archive

// Vérification de la requête HTTP (Postman).
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["error" => "Méthode non autorisée. Utilisez POST."], JSON_UNESCAPED_UNICODE);
    exit;
}

// Vérification de l'URL du CV.
if (empty($_POST['url']) || !filter_var($_POST['url'], FILTER_VALIDATE_URL)) {
    http_response_code(400);
    echo json_encode(["error" => "URL invalide ou manquante."], JSON_UNESCAPED_UNICODE);
    exit;
}

$outputFormat = $_POST['output_format'] ?? 'both';

$document_url = $_POST['url'];

// Exécution.
try {
    // Extraction des information avec l'API d'OpenAI.
    $resultat = analyseAvecOpenAI($document_url);


    // Création des dossiers pour les résultats.
    date_default_timezone_set('Europe/Paris'); // Initialise le fuseau horaire européen
    $horodate = date('Ymd_His');  // AAAAMMJJ_HHMMSS

    // Dossier pour les résultats de l'extraction.
    $archiveDir = __DIR__ . '/resultat/CV_archive_horodate/' . $horodate;
    if (!is_dir($archiveDir)) {
        mkdir($archiveDir, 0755, true);
    }

    // Dossier pour les résultats de comparaison
    $comparaisonDir = $archiveDir . '/Comparaison';
    if (!is_dir($comparaisonDir)) {
        mkdir($comparaisonDir, 0755, true);
    }

    // Écriture du JSON dans le dossier global d’archives.

    $nomOriginal = basename(parse_url($document_url, PHP_URL_PATH)); // ex: "moncv.pdf".
    $nomSansExt = pathinfo($nomOriginal, PATHINFO_FILENAME); // Récupère le nom original du CV. ex: "moncv".
    $cheminJson = $archiveDir . '/' . $nomSansExt . '.json'; // Création du fichier du résultat de l'extraction.
    file_put_contents($cheminJson, json_encode($resultat, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)); // Écrit les informations dans le fichier créé.

    // Comparaison automatique.
    $fichierExtrait = basename($cheminJson); // "moncv.json"
    $nomFichierRef = rechercheNomFichierRef("$nomSansExt.json", __DIR__.'/attendus');
    if ($nomFichierRef) {
        $fichierReference = __DIR__ . '/attendus/' . $nomFichierRef;

        try {
            // Récupère les pourcentages des comparaisons.
            $scores = compareFiles($fichierReference, $cheminJson);

            // Garde aussi le .txt de sortie.
            ob_start();
            compareFiles($fichierReference, $cheminJson);
            $txtOutput = ob_get_clean();
            $nomfichierComparaison = $comparaisonDir . '/comparaison_' . $nomSansExt . '.txt';
            file_put_contents($nomfichierComparaison, $txtOutput);

            // Calcule le temps d'exécution.
            $executionTime = round(microtime(true) - $startTime, 3); // secondes avec 3 décimales.

            // Enregistre les scores dans scores.json.
            $fichierScores = __DIR__ . '/scores.json';

            if ($outputFormat === 'json' || $outputFormat === 'both') {
                recordScores($fichierScores, $horodate, "$nomSansExt.json", $scores['pct_ref_extr'], $scores['pct_extr_ref'], $executionTime);
            }

            if ($outputFormat === 'csv' || $outputFormat === 'both') {
                // Chemin vers le CSV
                $csvChemin = __DIR__ . '/scores.csv';

                // Détecter si on doit écrire l'en-tête
                $entete = ! file_exists($csvChemin);

                // Ouvrir en mode « append »
                if ($fp = fopen($csvChemin, 'a')) {
                    if ($entete) {
                        // Écrire l'en-tête
                        fputcsv($fp, [
                            'horodate',
                            'fichier_testé',
                            'pct_ref_extr',
                            'pct_extr_ref',
                            'temps_execution'
                        ]);
                    }
                    // Écrire la ligne du test courant
                    fputcsv($fp, [
                        $horodate,
                        "$nomSansExt.json",
                        $scores['pct_ref_extr'],
                        $scores['pct_extr_ref'],
                        $executionTime
                    ]);
                    fclose($fp);
                } else {
                    error_log("Impossible d'ouvrir $csvChemin en écriture");
                }
            }
            // Vérification de l'écriture.
            if (!file_exists($fichierScores)) {
                error_log("Erreur : impossible d'écrire dans scores.json ($fichierScores)");
            }

            // Affichage dans la console.
            echo json_encode([
                "message"           => "Extraction et comparaison terminées",
                "chemin_d'archive"  => $cheminArchive,
                "chemin_resultat"   => $cheminResultat
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                "error" => "Échec de la comparaison : " . $e->getMessage()
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

    } else {
        echo json_encode([
            "message"            => "Extraction réussie, mais référence introuvable.",
            "archive_directory"  => $cheminArchive,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Erreur inattendue : " . $e->getMessage()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
