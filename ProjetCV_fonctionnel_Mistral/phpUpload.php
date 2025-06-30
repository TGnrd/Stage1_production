<?php


//permet d'ajouter les fonctions et de les utiliser
include './FonctionUpload.php';

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
$nomFichier = $dossier . '/' . date('Ymd_His') . '_' . $nomSansExtension . '.json'; // /CV_archive/date_monCV.json

// Écrire le contenu
file_put_contents($nomFichier, $resultatTexte);

echo "Résultat écrit dans le fichier : $nomFichier\n";
} catch (Exception $e) {
    echo "Erreur : " . $e->getMessage() . "\n";
}


/*
====== Ancien exécutable pour les fichiers téléchargés en format PDF, JPG ET PNG ======
try {
    $fichierPdf = './cvtest/CV_Titouan_Goinard.pdf'; // Assurez-vous que ce chemin est correct
    $text = pdfToText($fichierPdf); // Passe le fichier dans la fonction qui réupere le texte du fichier pdf
    $textOrganize = organizeTextWithMistral($text); // Organise la réponse avec mistral ai
    $decoded = json_decode($textOrganize, true); // Permet d'obtenir l'affichage souhaité sans les caractères non voulu
$content = $decoded['choices'][0]['message']['content'] ?? null; // extrait le contenu du texte de la réponse json

// Écrit le résultat si le content n'est pas vide
if ($content) {
    echo "Texte structuré : \n\n";
    echo $content; // Pas de re-encodage !
    echo "\n\n";
}
// Sinon il affiche un message d'erreur
 else {
    echo "Erreur : contenu non trouvé dans la réponse.\n";
}

} catch (Exception $e) { // Capture si il y a une erreur de type Exception
    echo "Erreur : " . $e->getMessage(); // Envoie un message d'erreur en recupérant le message d'erreur associé
}

try {
    $fichierImage = ''; // Assurez-vous que ce chemin est correct
    $text = imageToText($fichierImage); // Passe le fichier dans la fonction qui réupere le texte du fichier pdf
    $textOrganize = organizeTextWithMistral($text); // Organise la réponse avec mistral ai
    $decoded = json_decode($textOrganize, true); // Permet d'obtenir l'affichage souhaité sans les caractères non voulu
$content = $decoded['choices'][0]['message']['content'] ?? null; // extrait le contenu du texte de la réponse json

// Écrit le résultat si le content n'est pas vide
if ($content) {
    echo "Texte structuré : \n\n";
    echo $content; // Pas de re-encodage !
    echo "\n\n";
}
// Sinon il affiche un message d'erreur
 else {
    echo "Erreur : contenu non trouvé dans la réponse.\n";
}

} catch (Exception $e) { // Capture si il y a une erreur de type Exception
    echo "Erreur : " . $e->getMessage(); // Envoie un message d'erreur en recupérant le message d'erreur associé
}
*/

?>