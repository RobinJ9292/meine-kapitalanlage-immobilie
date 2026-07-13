<?php
// =====================================================================
//  Formular-Versand für meine-kapitalanlage-immobilie.de (Hostinger)
//  Nimmt die Anfragen aller Formulare entgegen und sendet sie per E-Mail.
// =====================================================================

// ------------------- EINSTELLUNGEN (bitte prüfen) -------------------
$EMPFAENGER = "info@meine-kapitalanlage-immobilie.de";

// Absender MUSS eine Adresse dieser Domain sein (Postfach existiert):
$ABSENDER = "info@meine-kapitalanlage-immobilie.de";
// ---------------------------------------------------------------------

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["ok" => false, "error" => "method"]);
    exit;
}

// Spamschutz: Honeypot-Feld darf nicht ausgefüllt sein
if (!empty($_POST["bot-field"])) {
    echo json_encode(["ok" => true]);
    exit;
}

$form = isset($_POST["form-name"]) ? trim($_POST["form-name"]) : "anfrage";
$name = isset($_POST["name"]) ? trim($_POST["name"]) : "";
$email = isset($_POST["email"]) ? trim($_POST["email"]) : "";

if ($name === "") {
    http_response_code(400);
    echo json_encode(["ok" => false, "error" => "name"]);
    exit;
}

$labels = [
    "name" => "Name",
    "phone" => "Telefon",
    "email" => "E-Mail",
    "time" => "Beste Erreichbarkeit",
    "thema" => "Thema",
    "status" => "Aktuelle Situation",
    "einkommen" => "Nettoeinkommen",
    "eigenkapital" => "Eigenkapital",
    "nachricht" => "Nachricht",
    "quelle" => "Quelle",
    "form-name" => "Formular",
];

$zeilen = [];
foreach ($_POST as $key => $wert) {
    if ($key === "bot-field") continue;
    if (is_array($wert)) $wert = implode(", ", $wert);
    $wert = trim(strip_tags($wert));
    if ($wert === "") continue;
    $label = isset($labels[$key]) ? $labels[$key] : ucfirst($key);
    $zeilen[] = $label . ": " . $wert;
}

$betreff = "Neuer Kapitalanlage-Lead (" . $form . ") - " . $name;
$text = "Neue Anfrage über meine-kapitalanlage-immobilie.de\n"
      . "==================================================\n\n"
      . implode("\n", $zeilen)
      . "\n\nGesendet am " . date("d.m.Y") . " um " . date("H:i") . " Uhr";

$headers = "From: " . $ABSENDER . "\r\n";
if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $headers .= "Reply-To: " . $email . "\r\n";
}
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/plain; charset=utf-8\r\n";

$ok = mail($EMPFAENGER, "=?UTF-8?B?" . base64_encode($betreff) . "?=", $text, $headers);

echo json_encode(["ok" => (bool)$ok]);
