<?php

require '../vendor/autoload.php';
require './instructionReponse.php';
require './formatReponse.php';
require './config.php';

use HelgeSverre\Mistral\Mistral;
use HelgeSverre\Mistral\Enums\Model;
use Smalot\PdfParser\Parser;

// Fonction permettant d'extraire le contenu d'un fichier PDF.
// Input :
// Fichier PDF.
// Output :
// Fichier texte contenant le texte du fichier PDF.
function pdfToText($fichierPdf) {
    if (!file_exists($fichierPdf)) {
        throw new Exception("Le fichier n'existe pas : " . $fichierPdf); // Vérifie si le fichier existe et envoie un message d'erreur dans le cas où il n'existe pas.
    }

    $parser = new \Smalot\PdfParser\Parser(); // créer un évenement parser.
    $pdf    = $parser->parseFile($fichierPdf); // Extrait le texte du fichier.
    $text = $pdf->getText(); // Enregistre le texte extrait.

    if (empty($text)) {
        throw new Exception("Aucun texte n'a pu être extrait du PDF."); // Envoie un message d'erreur si le texte est vide.
    }

    return $text;
}

// Fonction permettant d'extraire le contenu d'un fichier PDF à partir de son URL.
// Input :
// URL d'un fichier PDF.
// Output :
// Fichier texte contenant le texte du PDF.
function lirePdf(string $url): ?string {
    $data = @file_get_contents($url);
    if ($data === false) {
        throw new Exception("Échec du téléchargement du PDF depuis l'URL : $url");
    }

    // Création d’un fichier temporaire sur disque
    $tempPath = tempnam(sys_get_temp_dir(), 'pdf_');
    file_put_contents($tempPath, $data);

    try {
        $parser = new \Smalot\PdfParser\Parser(); // créer un évenement parser.
        $pdf = $parser->parseFile($tempPath); // Extrait le texte du fichier.
        $text = $pdf->getText(); // Enregistre le texte extrait.

        return $text;
    } catch (Exception $e) {
        return null;
    } finally {
        // Supprimer le fichier temporaire
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }
}




// Fonction permettant d'extraire le contenu d'un fichier image (png ou jpg).
// Input :
// Image ou photo.
// Output :
// Fichier texte contenant le texte de l'image ou de la photo.
function imageToText($fichierImage) {
    if (!file_exists($fichierImage)) {
        throw new Exception("Le fichier n'existe pas : " . $fichierImage); // Vérifie si le fichier existe et envoie un message d'erreur dans le cas où il n'existe pas.
    }

    $command = "tesseract " . escapeshellarg($fichierImage) . " stdout"; //prépare la commande de l'extraction du texte de l'image.
    $text = shell_exec($command); // Execute la commande de l'extraction du texte.

    if ($text === null) {
        throw new Exception("Erreur lors de l'exécution de Tesseract."); // Envoie un message d'erreur si le texte est vide.
    }

    return $text;
}


// Fonction modifiée pour recevoir l'URL dynamique.
// Input :
// Une clé API de mistral ai.
// L'URL de l'API de mistral ai.
// Le model, c'est-à-dire la version de mistral qui va être utilisé pour répondre à l'attente.
// Le prompt qui est les instructions de base pour l'IA.
// response_format qui est le format de réponse imposé à l'IA.
// Le document URL, c'est-à-dire le fichier à analyser.
// Output :
// Le résultat du document envoyer.
function connectToCurl($api_key, $url, $model, $prompt, $response_format, $document_url, $texte_pdf, $date) {
    $headers = [
        'Authorization: Bearer ' . $api_key,
        "Content-Type: application/json"
    ];

    // Ajoute le format de réponse au prompt
    $promptComplet = "\n Voici la date d'aujourd'hui:\n". $date . $prompt; //"\n\nVoici le format de réponse attendu :\n" . json_encode($response_format, JSON_PRETTY_PRINT);

    $ch = curl_init($url);
    $postFields = json_encode([
        'model' => $model,
        'messages' => [
            [
                "role" => "user",
                'content' => [
                    [
                        'type' => 'text',
                        'text' => $promptComplet
                    ],
                    [
                        'type' => 'text',
                        'text' => $texte_pdf
                    ],
                    [
                        "type" => "document_url",
                        "document_url" => $document_url
                    ]
                ]
            ]
        ],
        "response_format" => $response_format,
        "max_tokens" => 4096,
        "temperature" => 0,
    ]);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        echo 'Erreur cURL: ' . curl_error($ch);
        return null;
    }

    curl_close($ch);
    return $response;
}


// Fonction permettant de communiquer avec mistral.
// Input :
// L'url du CV à analyser par l'ia.
// Output :
// Les informations du CV donné sous format JSON.
function analyseDocumentWithMistral($document_url) {

    global $api_key, $url, $model, $instruction, $response_format; // Récupérer les variables présentes dans les fichiers externes.


    $ocr_text = lirePdf($document_url) ?? ''; // Remplace la valeur null si rien n'est trouvé dans le fichier donné

    $date_du_jour = date('d/m/Y'); // permet d'avoir le jour exact

    $response = connectToCurl($api_key, $url, $model, $instruction, $response_format, $document_url, $ocr_text, $date_du_jour);

    if ($response === null) {
        throw new Exception("Erreur lors de l'appel à l'API Mistral.");
    }

    $decoded = json_decode($response);
    return $decoded;
}

// Fonction pour trouver le fichier le plus récent dans un répertoire
// Input :
// Chemin d'accès du dossier comportant les fichiers d'extraction des informations enregistrées.
// Output :
// Le fichier le plus récent.
function fichierLePlusRecent($emplacementFichier) {
    $fichier = scandir($emplacementFichier);
    $fichierPlusRecent = null;
    $mostRecentTimestamp = null;

    foreach ($fichier as $file) {
        if ($file !== '.' && $file !== '..') {
            if (preg_match('/^(\d{8}_\d{6})/', $file, $matches)) {
                $currentDateTime = $matches[1];
                $currentTimestamp = strtotime(str_replace('_', ' ', $currentDateTime));

                if ($mostRecentTimestamp === null || $currentTimestamp > $mostRecentTimestamp) {
                    $mostRecentTimestamp = $currentTimestamp;
                    $fichierPlusRecent = $file;
                }
            }
        }
    }

    return $fichierPlusRecent;
}

// Fonction permettant de récupérer le nom du CV initial
// Input :
// Le fichier le plus récent.
// Chemin d'accès du dossier comportant les fichiers de référence.
// Output :
// Le chemin du fichier de référence ayant le même nom que le fichier recherché.
function rechercheNomFichierRef($fichierPlusRecent, $referenceDir) {
    if ($fichierPlusRecent === null) return null;

    $separe = explode('_', $fichierPlusRecent);
    $nomFichier = end($separe); // exemple : cv-12.json

    preg_match('/cv-(\d+)\.json/', $nomFichier, $matches);
    if (!isset($matches[1])) return null;

    $id = $matches[1];
    $refPattern = "cv-$id.json";
    $referencePath = rtrim($referenceDir, '/') . '/' . $refPattern;

    return file_exists($referencePath) ? $refPattern : null;
}

// Fonction comparant deux tableaux JSON.
// Input:
// ResultatAttendu qui est le résultat attendu.
// ResultatObtenu qui est le résultat obtenu par le code.
// Total initialement à 0, est le total de comparaison.
// Matches initialement à 0, montre les correspondances entre les 2 CV.
// DifferencesCount initialement à 0, est le nombre de différence présente entre les 2 CV.
// Mode initialement avec 'attendu', qui permet de déterminer s'il s'agit de la vérification des différences des informations ou s'il n'y a rien d'inventer dans le résultat.
// Output:
// Pourcentage de correspondance.
// Le nombre de différence.
function deepEqual($resultatAttendu, $resultatObtenu, $path = '', &$total = 0, &$matches = 0, &$differencesCount = 0, &$differencesAttendus = 0, &$differencesObtenus = 0, $mode = 'attendu') {
    if (is_object($resultatAttendu)) $resultatAttendu = get_object_vars($resultatAttendu);
    if (is_object($resultatObtenu)) $resultatObtenu = get_object_vars($resultatObtenu);

    if (is_array($resultatAttendu) && is_array($resultatObtenu)) {
        $keys = array_keys($resultatAttendu); // On ne parcourt QUE les clés du 1er argument (resultatAttendu)

        foreach ($keys as $key) {
            $newPath = $path ? "$path.$key" : $key;

            if (!array_key_exists($key, $resultatObtenu)) {
                $total++;
                $differencesCount++;
                if ($mode === 'attendu') {
                    $differencesAttendus++;
                } else {
                    $differencesObtenus++;
                }
                continue;
            }

            // Appel récursif
            deepEqual($resultatAttendu[$key], $resultatObtenu[$key], $newPath, $total, $matches, $differencesCount, $differencesAttendus, $differencesObtenus, $mode);
        }
    } else {
        $total++;
        if ((is_string($resultatAttendu) && is_string($resultatObtenu) && strtolower($resultatAttendu) === strtolower($resultatObtenu)) || $resultatAttendu == $resultatObtenu) {
            $matches++;
        } else {
            $differencesCount++;
        }
    }
}


// Fonction permettant de comparer le fichier de référence et le résultat obtenu de l'extraction.
// Input :
// Le fichier de référence.
// Le fichier de l'extraction la plus récente.
// Output :
// Le pourcentage de de similarité.
// Le nombre d'élément différent entre les deux fichiers.
// Une phrase de conclusion.
function compareFiles($referenceFile, $extractedFile) {
    $data1 = json_decode(file_get_contents($referenceFile));
    $data2 = json_decode(file_get_contents($extractedFile));

    if ($data1 === null || $data2 === null) {
        throw new Exception("Erreur : l'un des fichiers JSON est invalide.");
    }

    // Comparaison Référence → Extraction : ce qui est manquant
    $total1 = 0;
    $match1 = 0;
    $diff1 = 0;
    $diffAttendus1 = 0;
    $diffObtenus1 = 0;
    deepEqual($data1, $data2, '', $total1, $match1, $diff1, $diffAttendus1, $diffObtenus1, 'attendu');

    // Comparaison Extraction → Référence : ce qui est en trop
    $total2 = 0;
    $match2 = 0;
    $diff2 = 0;
    $diffAttendus2 = 0;
    $diffObtenus2 = 0;
    deepEqual($data2, $data1, '', $total2, $match2, $diff2, $diffAttendus2, $diffObtenus2, 'obtenu');

    $pourcentage1 = $total1 > 0 ? round(($match1 / $total1) * 100, 2) : 100;
    $pourcentage2 = $total2 > 0 ? round(($match2 / $total2) * 100, 2) : 100;


    // Affichage console
    echo "Résultats globaux :\n";
    echo "- Similarité (référence → extraction) : {$pourcentage1}%\n";
    echo "Différences totales : {$diff1} / {$total1}\n";
    echo "  - Différences dans l'attendu : {$diffAttendus1}\n";
    echo "- Similarité (extraction → référence) : {$pourcentage2}%\n";
    echo "Différences totales : {$diff2} / {$total2}\n";
    echo "  - Différences dans le résultat : {$diffObtenus2}\n";

    if ($diffObtenus2 > 0) {
        echo "\n⚠️ Présence probable d'informations inventées.\n";
    }
    if ($diff1 === 0 && $diff2 === 0) {
        echo "\n✅ Les fichiers JSON sont parfaitement identiques.\n";
    }

    return [
        'pct_ref_extr' => $pourcentage1,
        'diff_ref_extr' => $diff1,
        'pct_extr_ref' => $pourcentage2,
        'diff_extr_ref' => $diff2,
        'details' => [
            'ref_vs_extr' => [
                'total' => $total1,
                'match' => $match1,
                'diff' => $diff1,
                'diff_attendus' => $diffAttendus1,
                'diff_obtenus' => $diffObtenus1,
            ],
            'extr_vs_ref' => [
                'total' => $total2,
                'match' => $match2,
                'diff' => $diff2,
                'diff_attendus' => $diffAttendus2,
                'diff_obtenus' => $diffObtenus2,
            ]
        ]
    ];
}


// Fonction pemettant d'écrire dans un fichier le résultat des comparaisons en JSON.
// Input :
// Le fichier dans lequel les résultats seront notés.
// horodate qui est l'heure de l'exécution du code.
// Le nom du CV testé.
// Le pourcentage de similarité entre le fichier de référence et le fichier d'extraction.
// Le pourcentage de similarité entre le fichier d'extraction et le fichier de référence.
// Le temps d'exécution du code.
// Output :
// Toute les informations du tests avec une mise à jour des moyennes total
function recordScores($fichier, $horodate, $nomFichier, $pctRefExtr, $pctExtrRef, $executionTime) {
    // Chargement des données existantes
    if (file_exists($fichier)) {
        $data = json_decode(file_get_contents($fichier), true);
        if (!is_array($data)) {
            $data = ["moyennes" => ["pct_ref_extr" => 0, "pct_extr_ref" => 0, "execution_time" => 0, "count" => 0], "tests" => []];
        }
    } else {
        $data = ["moyennes" => ["pct_ref_extr" => 0, "pct_extr_ref" => 0, "execution_time" => 0, "count" => 0], "tests" => []];
    }

    // Ajout du nouveau test
    $data["tests"][] = [
        "horodate"        => $horodate,
        "fichier_testé"   => $nomFichier,
        "pct_ref_extr"    => $pctRefExtr,
        "pct_extr_ref"    => $pctExtrRef,
        "temps_execution" => $executionTime
    ];

    // Mise à jour des moyennes
    $count = $data["moyennes"]["count"] + 1;
    $data["moyennes"]["pct_ref_extr"] = round((($data["moyennes"]["pct_ref_extr"] * $data["moyennes"]["count"]) + $pctRefExtr) / $count, 2);
    $data["moyennes"]["pct_extr_ref"] = round((($data["moyennes"]["pct_extr_ref"] * $data["moyennes"]["count"]) + $pctExtrRef) / $count, 2);
    $data["moyennes"]["execution_time"] = round((($data["moyennes"]["execution_time"] * $data["moyennes"]["count"]) + $executionTime) / $count, 3);
    $data["moyennes"]["count"] = $count;

    return file_put_contents($fichier, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
}

// Fonction pemettant d'écrire dans un fichier le résultat des comparaisons en CSV.
// Input :
// Le fichier dans lequel les résultats seront notés.
// horodate qui est l'heure de l'exécution du code.
// Le nom du CV testé.
// Le pourcentage de similarité entre le fichier de référence et le fichier d'extraction.
// Le pourcentage de similarité entre le fichier d'extraction et le fichier de référence.
// Le temps d'exécution du code.
// Output :
// Toute les informations du tests avec une mise à jour des moyennes total
function recordScoresCsv($fichier, $horodate, $nomFichier, $pctRefExtr, $pctExtrRef, $executionTime) {
    // Ajout de la nouvelle ligne de test en mode append
    $nouveauTest = [$horodate, $nomFichier, $pctRefExtr, $pctExtrRef, $executionTime];
    $lines = [];

    // On lit tout le CSV s’il existe
    if (file_exists($fichier)) {
        $lines = file($fichier, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        // Enlève toute ancienne ligne de moyennes
        $lines = array_filter($lines, fn($l) => !str_starts_with($l, 'Moyennes'));
        // Enlève toute ligne d’en-tête potentielle
        $lines = array_filter($lines, fn($l) => !str_starts_with($l, 'horodate'));
    }

    // Ajoute notre nouveau test
    $lines[] = implode(',', $nouveauTest);

    // Ouvre en écriture complète pour réécrire file.csv
    $fp = fopen($fichier, 'w');
    if (!$fp) {
        error_log("Impossible d'ouvrir $fichier en écriture");
        return false;
    }

    // Réécrit toujours l’en-tête fixe
    fputcsv($fp, ['horodate','fichier_testé','pct_ref_extr','pct_extr_ref','temps_execution']);

    // Réécrit toutes les lignes de test sans l’en-tête ni les anciennes moyennes
    foreach ($lines as $l) {
        $cols = str_getcsv($l);
        fputcsv($fp, $cols);
    }

    // Calcul et écriture de la nouvelle ligne de moyennes
    $total1 = $total2 = $total3 = 0;
    $count = 0;
    foreach ($lines as $l) {
        $cols = str_getcsv($l);
        if (count($cols) === 5) {
            $total1 += (float)$cols[2];
            $total2 += (float)$cols[3];
            $total3 += (float)$cols[4];
            $count++;
        }
    }
    if ($count > 0) {
        $moy1 = round($total1 / $count, 2);
        $moy2 = round($total2 / $count, 2);
        $moy3 = round($total3 / $count, 3);
        fwrite($fp, "\nMoyennes,,{$moy1},{$moy2},{$moy3},{$count}\n");
    }

    fclose($fp);
    return true;
}
