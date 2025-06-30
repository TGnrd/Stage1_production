<?php

require('./vendor/autoload.php');
require('./instructionReponse.php');
require('./formatReponse.php');
require('./config.php');

use Smalot\PdfParser\Parser;
use OpenAI\Client as OpenAIClient;


// Extrait le texte d'un fichier PDF local.
// Input :
// Le chemin d'un fichier PDF.
// Output :
// Le texte extrait du fichier.
function pdfToText(string $fichierPdf): string
{
    if (!file_exists($fichierPdf)) {
        throw new Exception("Le fichier n'existe pas : " . $fichierPdf);
    }

    $parser = new \Smalot\PdfParser\Parser();
    $pdf = $parser->parseFile($fichierPdf);
    $text = $pdf->getText();

    return $text;
}

// Fonction permettant le télécharge temporaire un PDF depuis une URL et extrait le texte du fichier.
// Input :
// L'URL du fichier PDF
// Output :
// Le texte extrait du fichier.
function lirePdf(string $url): string
{
    $data = @file_get_contents($url);
    if ($data === false) {
        throw new Exception("Échec du téléchargement du PDF depuis l’URL : $url");
    }

    $tempPath = tempnam(sys_get_temp_dir(), 'pdf_');
    if ($tempPath === false) {
        throw new Exception("Impossible de créer un fichier temporaire.");
    }

    file_put_contents($tempPath, $data);

    try {
        $text = pdfToText($tempPath);
        return $text;
    } finally {
        if (file_exists($tempPath)) {
            unlink($tempPath);
        }
    }
}

// Fonstion permettant d'extraire les informations d'une image.
// Input :
// Le chemin d'un fichier image.
// Output :
// Le texte extrait du fichier donné.
function imageToText(string $fichierImage): string
{
    if (!file_exists($fichierImage)) {
        throw new Exception("Le fichier n'existe pas : " . $fichierImage);
    }

    $command = "tesseract " . escapeshellarg($fichierImage) . " stdout 2>&1";
    $texte = shell_exec($command);

    if ($texte === null) {
        throw new Exception("Erreur lors de l'exécution de Tesseract.");
    }

    return $texte;
}

// Fonction modifiée pour recevoir l'URL dynamique.
// Input :
// Une clé API d'Open AI.
// L'URL de l'API d'Open AI.
// Le model, c'est-à-dire la version d'Open AI qui va être utilisé pour répondre à l'attente.
// Le prompt qui est les instructions de base pour l'IA.
// response_format qui est le format de réponse imposé à l'IA.
// Le document URL, c'est-à-dire le fichier à analyser.
// Output :
// Le résultat du document envoyer.
function connectToCurl ($api_key, $model, $instruction, $response_format, $document_url, $texte_cv, $date)  {
    $endpoint = 'https://api.openai.com/v1/chat/completions';

    $headers = [
        'Authorization: Bearer ' . $api_key,
        'Content-Type: application/json'
    ];

    // Construire le contenu des messages
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

// Fonction permettant de communiquer avec mistral.
// Input :
// L'url du CV à analyser par l'ia.
// Output :
// Les informations du CV donné sous format JSON.
function analyseAvecOpenAI(string $document_url): array
{
    // Ces variables doivent être définies au préalable (via require './config.php', './instructionReponse.php', './formatReponse.php')
    global $api_key, $model, $instruction, $response_format;

    // Extrait le texte du document.
    $texte_cv = lirePdf($document_url);

    // Récupérer la date du jour.
    date_default_timezone_set('Europe/Paris');
    $date_du_jour = date('d/m/Y');

    // Appel l'API d'Open AI avec une requête cURL.
    $reponse = connectToCurl($api_key, $model, $instruction, $response_format, $document_url, $texte_cv, $date_du_jour);
    // Extrait le contenu “message.content” du JSON renvoyé
    $decoded_raw = json_decode($reponse, true);
    if (json_last_error() !== JSON_ERROR_NONE || !isset($decoded_raw['choices'][0]['message']['content'])) {
        throw new Exception("Réponse inattendue de l'API OpenAI ou JSON invalide : "
            . json_last_error_msg());
    }
    $json_text = trim($decoded_raw['choices'][0]['message']['content']);

    // Retirer les balises Markdown.
     $json_text = preg_replace('/^```json\s*|^```\s*|```$/mi', '', $json_text);
    // Converti en UTF-8.
    $json_text = mb_convert_encoding($json_text, 'UTF-8', 'UTF-8');
    // Supprime les caractères de contrôle indésirables.
    $json_text = preg_replace(
        '/[^\x{0009}\x{000A}\x{000D}\x{0020}-\x{10FFFF}]/u',
        '',
        $json_text
    );

    // Décode le JSON nettoyé en tableau PHP.
    $decoded = json_decode($json_text, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Impossible de décoder le JSON renvoyé : "
            . json_last_error_msg()
            . "\nContenu nettoyé : " . $json_text
        );
    }

    // Supprime les clés de schéma si l’IA les a incluses.
    if (isset($decoded['type'])) {
        unset($decoded['type']);
    }
    if (isset($decoded['json_schema'])) {
        unset($decoded['json_schema']);
    }

    return $decoded;
}

if ($argc <= 1) {
    echo "Usage : php " . basename(__FILE__) . " <URL_DU_CV_PDF>\n";
    exit(1);
}

$document_url = $argv[1];
if (!filter_var($document_url, FILTER_VALIDATE_URL)) {
    echo "Erreur : URL invalide.\n";
    exit(1);
}

try {
    $resultat = analyseAvecOpenAI($document_url);

    $dossier = __DIR__ . '/CV_archive';
    if (!is_dir($dossier)) {
        mkdir($dossier, 0755, true);
    }
    $nomOriginal      = basename(parse_url($document_url, PHP_URL_PATH));
    $nomSansExtension = pathinfo($nomOriginal, PATHINFO_FILENAME);
    $timestamp        = date('Ymd_His');
    $nomFichier       = $dossier . '/' . $nomSansExtension . '_' . $timestamp . '.json';

    file_put_contents(
        $nomFichier,
        json_encode($resultat, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    echo "✅ Résultat écrit dans le fichier : $nomFichier\n";
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    exit(1);
}