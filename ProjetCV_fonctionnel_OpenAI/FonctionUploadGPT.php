<?php


require './vendor/autoload.php';
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
        $parser = new \Smalot\PdfParser\Parser(); // Créer un évenement parser.
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

    $command = "tesseract " . escapeshellarg($fichierImage) . " stdout"; //Prépare la commande de l'extraction du texte de l'image.
    $text = shell_exec($command); // Execute la commande de l'extraction du texte.

    if ($text === null) {
        throw new Exception("Erreur lors de l'exécution de Tesseract."); // Envoie un message d'erreur si le texte est vide.
    }

    return $text;
}




// Fonction permettant l'appelle de l'API OpenAI Chat Completion pour analyser le texte d'un CV.
// Input :
// La clé API OpenAI.
// Le modèle à utiliser de l'API (ex. "gpt-4.1").
// Les instructions données à l'IA.
// Format de réponse en JSON schema attendu.
// Le texte de l'extraction OCR du fichier.
// La date du jour (format "JJ/MM/AAAA").
// Output :
// L'extraction le résultat de l'extraction OCR.
function connectToCurl ($api_key, $model, $instruction, $response_format, $document_url, $texte_cv, $date)  {
    $endpoint = 'https://api.openai.com/v1/chat/completions';

    $headers = [
        'Authorization: Bearer ' . $api_key,
        'Content-Type: application/json'
    ];

    // Construire le contenu des messages.
    $messages = [
        [
            'role'    => 'system',
            'content' => $instruction
                         . "\n\nFormat de sortie attendu (schéma) :\n"
                         . json_encode($response_format, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                         . "\n\n**NE RENVOIE QUE L'OBJET JSON DES DONNÉES, SANS INCLURE CE SCHÉMA NI LES CLÉS type OU json_schema.**"
        ],
        [
            'role'    => 'user',
            'content' => "URL du document : " . $document_url . "\n" . "Date du jour : " . $date . "\n\n" . $texte_cv
        ]
    ];

    $payload = [
        'model'       => $model,
        'messages'    => $messages,
        'temperature' => 0,
        'max_tokens'  => 4096
    ];

    $ch = curl_init($endpoint);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $response = curl_exec($ch);
    if ($response === false) {
        $err = curl_error($ch);
        curl_close($ch);
        throw new Exception("Erreur cURL : " . $err);
    }
    curl_close($ch);

    return $response;
}


// Fonction permettant l'analyse un document (PDF ou image) en appelant l’API OpenAI.
// Input :
// L'URL du PDF ou de l’image à analyser.
// Output :
// Le résultat de l'extraction du code au bon format.
function analyseAvecOpenAI(string $document_url): array
{
    // Ces variables doivent être définies au préalable (via require './config.php', './instructionReponse.php', './formatReponse.php').
    global $api_key, $model, $instruction, $response_format;

    // Extraire le texte du document.
    $texte_cv = lirePdf($document_url);

    // Récupérer la date du jour.
    date_default_timezone_set('Europe/Paris');
    $date_du_jour = date('d/m/Y');

    // Appeler l’API via connectToCurl.
    $reponse = connectToCurl($api_key, $model, $instruction, $response_format, $document_url, $texte_cv, $date_du_jour);
    // Extraire le contenu “message.content” du JSON renvoyé.
    $decoded_raw = json_decode($reponse, true);
    if (json_last_error() !== JSON_ERROR_NONE || !isset($decoded_raw['choices'][0]['message']['content'])) {
        throw new Exception("Réponse inattendue de l'API OpenAI ou JSON invalide : "
            . json_last_error_msg());
    }
    $json_text = trim($decoded_raw['choices'][0]['message']['content']);

    // Retirer les balises Markdown
     $json_text = preg_replace('/^```json\s*|^```\s*|```$/mi', '', $json_text);
    // Forcer en UTF-8
    $json_text = mb_convert_encoding($json_text, 'UTF-8', 'UTF-8');
    // Supprimer les caractères de contrôle indésirables
    $json_text = preg_replace(
        '/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{10FFFF}]/u',
        '',
        $json_text
    );

    // Décoder le JSON nettoyé en tableau PHP
    $decoded = json_decode($json_text, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Impossible de décoder le JSON renvoyé : "
            . json_last_error_msg()
            . "\nContenu nettoyé : " . $json_text
        );
    }

    // Supprimer les clés de schéma si l’IA les a incluses
    if (isset($decoded['type'])) {
        unset($decoded['type']);
    }
    if (isset($decoded['json_schema'])) {
        unset($decoded['json_schema']);
    }

    return $decoded;
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


// Fonction pemettant d'écrire dans un fichier
// Input :
// Le fichier qui est le fichier scores.json où les résultats des comparaisons sont enregistrées.
// Horodate qui permet de savoir la date du test.
// Le nom du fichier est la fCV qui à été extrait et qui à été comparer.
// pctRefExtr est le pourcentage de la première comparaison entre le fichier de référence et celui de l'extraction.
// pctExtrRef est le pourcentage de similarité de la seconde comparaison entre le fichier de l'extraction et celui de référence.
// executionTime contient le temps d'exécution du code complet.
// Output :
// Écrit dans le fichier scores.json les résultat des comparaisons ainsi que le temps d'exécution du code pour chaque CV testé.
// Mets à jour les moyennes des pourcentages de comparaison et la moyenne du temps d'exécution du code.
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

