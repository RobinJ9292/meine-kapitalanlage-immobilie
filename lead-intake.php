<?php
// =====================================================================
//  Lead-Intake mit Double-Opt-In fuer meine-kapitalanlage-immobilie.de
//  - Terminwunsch (Buchung) -> gilt als qualifiziert, Robin bekommt Lead sofort
//  - sonst (Rueckruf/Quiz)   -> Bestaetigungsmail; erst nach Klick geht Lead an Robin + HubSpot
// =====================================================================
require __DIR__ . "/_lead-lib.php";
header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405); echo json_encode(["ok" => false, "error" => "method"]); exit;
}
// Honeypot
if (!empty($_POST["bot-field"])) { echo json_encode(["ok" => true, "confirm" => false]); exit; }

// Felder einsammeln
$data = [];
foreach ($_POST as $k => $v) {
    if ($k === "bot-field") continue;
    if (is_array($v)) $v = implode(", ", $v);
    $data[$k] = trim(strip_tags((string)$v));
}

$name  = $data["name"]  ?? "";
$email = $data["email"] ?? "";
$phone = $data["phone"] ?? "";
$terminwunsch = strtolower($data["terminwunsch"] ?? "nein");

// Basis-Validierung
if ($name === "" || $phone === "") {
    http_response_code(400); echo json_encode(["ok" => false, "error" => "input"]); exit;
}

// --- Fall 1: Terminbuchung => bereits qualifiziert, sofort an Robin (kein Double-Opt-In) ---
if ($terminwunsch === "ja") {
    lead_notify_robin($data, " (Terminwunsch)");
    lead_to_hubspot($data);
    echo json_encode(["ok" => true, "confirm" => false]); exit;
}

// --- Fall 2: Rueckruf/Quiz => Double-Opt-In. E-Mail ist hier Pflicht ---
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400); echo json_encode(["ok" => false, "error" => "email"]); exit;
}

$token = bin2hex(random_bytes(16));
if (!is_dir(LEAD_STORE)) @mkdir(LEAD_STORE, 0700, true);
$saved = @file_put_contents(
    LEAD_STORE . "/" . $token . ".json",
    json_encode(["data" => $data, "ts" => time()], JSON_UNESCAPED_UNICODE)
);

// Fallback: kann nicht gespeichert werden -> Lead nicht verlieren, direkt an Robin
if ($saved === false) {
    lead_notify_robin($data, " (unbestaetigt – Speicher-Fallback)");
    echo json_encode(["ok" => true, "confirm" => false]); exit;
}

$link = LEAD_BASISURL . "/bestaetigen.php?t=" . $token;
lead_send_confirm($email, $name, $link);

echo json_encode(["ok" => true, "confirm" => true]);
