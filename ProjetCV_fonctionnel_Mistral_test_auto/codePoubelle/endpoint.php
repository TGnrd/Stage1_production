<?php

    set_time_limit(300);

// Vérifier si la méthode de la requête est POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Récupérer l'URL du CV depuis le corps de la requête
    $cv_url = $_POST['cv_url'] ?? null;

    if ($cv_url) {
        // Appeler votre script principal avec l'URL du CV
        $command = "php C:/Users/romua/OneDrive/Documents/testTitouan/projetCV/ProjetCV_fonctionnel_Mistral/phpUpload.php " . escapeshellarg($cv_url);
        $output = shell_exec($command);

        header('Content-Type: application/json');
        echo $output;
    } else {
        // Répondre avec une erreur si l'URL n'est pas fournie
        http_response_code(400);
        echo "L'URL du CV est requise.";
    }
} else {
    // Répondre avec une erreur si la méthode n'est pas POST
    http_response_code(405);
    echo "Méthode non autorisée.";
}
?>
