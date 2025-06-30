<?php

// Ajuster le chemin vers l'autoloader de Composer
require '../vendor/autoload.php';
require './instructionReponse.php';
require './formatReponse.php';
require './config.php';

use HelgeSverre\Mistral\Mistral;
use HelgeSverre\Mistral\Enums\Model;
use Smalot\PdfParser\Parser;

function pdfToText($filePath) {
    // Vérifiez que le fichier existe
    if (!file_exists($filePath)) {
        throw new Exception("Le fichier n'existe pas : " . $filePath);
    }

    // Utilisez PdfParser pour extraire le texte
    $parser = new \Smalot\PdfParser\Parser();
    $pdf    = $parser->parseFile($filePath);
    $text = $pdf->getText();

    if (empty($text)) {
        throw new Exception("Aucun texte n'a pu être extrait du PDF.");
    }

    return $text;
}

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


function connectToCurl($api_key, $url, $model, $prompt, $response_format, $document_url, $texte_pdf, $date) {
    $headers = [
        'Authorization: Bearer ' . $api_key,
        "Content-Type: application/json"
    ];

    // Ajoute le format de réponse au prompt
    $promptComplet = "\n Voici la date d'aujourd'hui:\n". $date . $prompt . "\n\nVoici le format de réponse attendu :\n" . json_encode($response_format, JSON_PRETTY_PRINT);

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


//Vérification du nombre d'arguments
if($argc <= 1) {
    echo "Usage script + URL"."\n";
    exit(-1);
}


// Demander l'URL dans la console CLI
//echo "Veuillez entrer l'URL du fichier à analyser : ";
//$document_url = trim(fgets(STDIN));
$document_url = $argv[1];

// Vérifier que l'URL est valide
if (!filter_var($document_url, FILTER_VALIDATE_URL)) {
    die("Erreur : URL invalide.\n");
}

// Exécution
try {
    $texteStructure = analyseDocumentWithMistral($document_url);
    $resultatTexte = $texteStructure->choices[0]->message->content;

// Supprime les balises de type ```json ou ```
    $resultatTexte = preg_replace('/^```json\s*|^```\s*|```$/i', '', trim($resultatTexte));

// Dossier de destination
$dossier = __DIR__ . '/CV_archive';

//
date_default_timezone_set('Europe/Paris');
$nomOriginal = basename(parse_url($document_url, PHP_URL_PATH)); // extrait "moncv.pdf"
$nomSansExtension = pathinfo($nomOriginal, PATHINFO_FILENAME);   // extrait "moncv"
$nomFichier = $dossier . '/' . $nomSansExtension . '_' . date('Ymd_His') . '.json';

// Écrire le contenu
file_put_contents($nomFichier, $resultatTexte);

echo "Résultat écrit dans le fichier : $nomFichier\n";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}

?>
