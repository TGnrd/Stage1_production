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
// Le document URL, c'est-à-dire le fichier à analyser.
// Output :
// Le résultat du document envoyer.

// Fonction modifiée pour recevoir l'URL dynamique
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