<?php

session_start();
require_once "config.php";
require_once "db.php";

/* =========================================
   CHECK CITIZEN LOGIN
========================================= */

if (!isset($_SESSION["citizen_id"])) {
    echo json_encode([
        "success" => false,
        "message" => "Please login first."
    ]);
    exit;
}

/* =========================================
   GET COMPLAINT DESCRIPTION
========================================= */

$issue = trim($_POST["issue_description"] ?? "");

if ($issue === "") {
    echo json_encode([
        "success" => false,
        "message" => "Please enter the complaint description."
    ]);
    exit;
}

/* =========================================
   GET DEPARTMENTS FROM DATABASE
========================================= */

$departments = [];

$sql = "SELECT department_id, department_name
        FROM departments
        ORDER BY department_name ASC";

$result = mysqli_query($conn, $sql);

if ($result) {

    while ($row = mysqli_fetch_assoc($result)) {
        $departments[] = [
            "id" => $row["department_id"],
            "name" => $row["department_name"]
        ];
    }
}

/* =========================================
   CONVERT DEPARTMENTS TO TEXT
========================================= */

$departmentText = "";

foreach ($departments as $department) {

    $departmentText .=
        $department["id"] . " - " .
        $department["name"] . "\n";
}

/* =========================================
   AI PROMPT
========================================= */

$prompt = <<<PROMPT

You are the department recommendation assistant for a civic complaint system called CivicVoice.

A citizen has entered this complaint:

"$issue"

Available departments in the CivicVoice database are:

$departmentText

Choose the MOST appropriate department ONLY from the departments listed above.

Also determine:

1. Category
2. Priority: Low, Medium, or High
3. Short reason for the recommendation

Return ONLY valid JSON in exactly this format:

{
    "department_id": 1,
    "department_name": "Department Name",
    "category": "Category",
    "priority": "Medium",
    "reason": "Short explanation"
}

Do not create a department that is not in the provided list.

PROMPT;

/* =========================================
   OPENAI API
========================================= */

$url = "https://api.openai.com/v1/responses";

$data = [
    "model" => "gpt-5",
    "input" => $prompt
];

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);

curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "Authorization: Bearer " . OPENAI_API_KEY
]);

curl_setopt(
    $ch,
    CURLOPT_POSTFIELDS,
    json_encode($data)
);

$response = curl_exec($ch);

if ($response === false) {

    echo json_encode([
        "success" => false,
        "message" => "cURL Error: " . curl_error($ch)
    ]);

    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

curl_close($ch);

/* =========================================
   CHECK API RESPONSE
========================================= */

if ($httpCode !== 200) {

    echo json_encode([
        "success" => false,
        "message" => "OpenAI API error.",
        "http_code" => $httpCode
    ]);

    exit;
}

$apiResult = json_decode($response, true);

/* =========================================
   GET AI TEXT
========================================= */

$aiText = "";

if (isset($apiResult["output"])) {

    foreach ($apiResult["output"] as $output) {

        if (
            isset($output["content"]) &&
            is_array($output["content"])
        ) {

            foreach ($output["content"] as $content) {

                if (
                    isset($content["type"]) &&
                    $content["type"] === "output_text"
                ) {

                    $aiText .= $content["text"];
                }
            }
        }
    }
}

/* =========================================
   CLEAN JSON
========================================= */

$aiText = trim($aiText);

$aiText = preg_replace('/```json\s*/', '', $aiText);
$aiText = preg_replace('/```\s*/', '', $aiText);

$aiData = json_decode($aiText, true);

/* =========================================
   VALIDATE AI RESULT
========================================= */

if (!is_array($aiData)) {

    echo json_encode([
        "success" => false,
        "message" => "AI returned an invalid response.",
        "raw_response" => $aiText
    ]);

    exit;
}

/* =========================================
   VERIFY DEPARTMENT
========================================= */

$validDepartment = false;

foreach ($departments as $department) {

    if (
        isset($aiData["department_id"]) &&
        (string)$aiData["department_id"] ===
        (string)$department["id"]
    ) {

        $validDepartment = true;
        break;
    }
}

if (!$validDepartment) {

    echo json_encode([
        "success" => false,
        "message" => "AI selected an invalid department."
    ]);

    exit;
}

/* =========================================
   RETURN RESULT
========================================= */

echo json_encode([
    "success" => true,
    "department_id" => $aiData["department_id"],
    "department_name" => $aiData["department_name"] ?? "",
    "category" => $aiData["category"] ?? "",
    "priority" => $aiData["priority"] ?? "",
    "reason" => $aiData["reason"] ?? ""
]);

?>