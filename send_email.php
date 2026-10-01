<?php

function sendCivicVoiceEmail($toEmail, $toName, $subject, $message)
{
    $apiKey =$apiKey = "";

    $data = [
        "sender" => [
            "name" => "CivicVoice",
            "email" => "civicvoice.notifications@gmail.com"
        ],
        "to" => [
            [
                "email" => $toEmail,
                "name" => $toName
            ]
        ],
        "subject" => $subject,
        "htmlContent" => $message
    ];

    $ch = curl_init("https://api.brevo.com/v3/smtp/email");

    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "accept: application/json",
        "api-key: " . $apiKey,
        "content-type: application/json"
    ]);

    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

if ($response === false) {
    die("Brevo connection error: " . curl_error($ch));
}

$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

if ($http_code < 200 || $http_code >= 300) {
    die("Brevo error ($http_code): " . $response);
}

return $response;
}
?>