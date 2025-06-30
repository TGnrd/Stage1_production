<?php

$api_key = 'sk-svcacct-...............................hikA';
$api_url = 'https://api.openai.com/v1/chat/completions';

$prompt = 'Qui a gagné la coupe du monde de football en 98 ?';

$data = array(
'model' => 'gpt-3.5-turbo',
'messages' => array(
    array('role' => 'system', 'content' => 'Vous êtes un assistant'),
    array('role' => 'user', 'content' => $prompt)
));

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $api_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
    'Content-Type: application/json',
    'Authorization: Bearer ' . $api_key
));

$response = curl_exec($ch);
curl_close($ch);
var_dump($response);

if ($response) {
    $response_data = json_decode($response, true);
    $response_prod = $response_data['choices'][0]['message']['content'];
    var_dump($response_prod) ;
} else {
    echo "Une erreur s'est produite lors de l'appel à l'API.";
}

?>