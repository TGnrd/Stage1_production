<?php
if ($argc < 2) {
    echo "Usage: php testMistral.php URL_DU_PDF\n";
    exit(1);
}

$api_key = trim('sk-live-YmFEEQSYzdVC1zjA6qxFpCf2Ywq7m9vF'); // Ta clé API ici
$url_pdf = $argv[1];

// 1. Télécharger le PDF
$pdf_data = file_get_contents($url_pdf);
if ($pdf_data === false) {
    echo "Erreur : Impossible de télécharger le PDF.\n";
    exit(1);
}
$base64_pdf = base64_encode($pdf_data);

// 2. Préparer payload OCR selon la doc
$payload = [
    'input' => [
        'mime_type' => 'application/pdf',
        'data' => $base64_pdf
    ]
];

// 3. Initialiser CURL
$ch = curl_init('https://api.mistral.ai/v1/ocr');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $api_key,
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_VERBOSE, true);

$response = curl_exec($ch);
var_dump($response);
$err = curl_error($ch);
curl_close($ch);

if ($err) {
    echo "Erreur cURL : $err\n";
    exit(1);
}

$data = json_decode($response, true);

// 4. Extraire texte OCR
$text = $data['text'] ?? null;

if (!$text) {
    echo "Aucun texte OCR détecté.\n";
    exit(1);
}

echo "Texte OCR extrait :\n$text\n";

// 5. (optionnel) Appel modèle Mistral pour analyse prénom/nom

// Prépare requête chat
$chat_payload = [
    'model' => 'mistral-7b', // modèle le plus puissant
    'messages' => [
        [
            'role' => 'user',
            'content' => "Voici un texte extrait d'un CV :\n\n$text\n\nQuel est le prénom et le nom de la personne dans ce CV ? Donne uniquement sous forme : Prénom Nom."
        ]
    ]
];

$ch = curl_init('https://api.mistral.ai/v1/chat/completions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $api_key,
    'Content-Type: application/json',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($chat_payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

$chat_response = curl_exec($ch);
$chat_err = curl_error($ch);
curl_close($ch);

if ($chat_err) {
    echo "Erreur cURL Chat : $chat_err\n";
    exit(1);
}

$chat_data = json_decode($chat_response, true);
$answer = $chat_data['choices'][0]['message']['content'] ?? null;

echo $answer ? "Nom détecté : $answer\n" : "L'IA n’a pas pu identifier le nom.\n";
?>
