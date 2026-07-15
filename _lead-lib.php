<?php
// =====================================================================
//  Lead-Bibliothek (Double-Opt-In) fuer meine-kapitalanlage-immobilie.de
//  Wird von lead-intake.php und bestaetigen.php eingebunden.
// =====================================================================

// ------------------- EINSTELLUNGEN -------------------
const LEAD_EMPFAENGER = "info@meine-kapitalanlage-immobilie.de"; // Robin bekommt die Leads
const LEAD_ABSENDER   = "info@meine-kapitalanlage-immobilie.de"; // Postfach dieser Domain
const LEAD_BASISURL   = "https://meine-kapitalanlage-immobilie.de";
const LEAD_STORE      = __DIR__ . "/_leads";

// HubSpot (EU-Portal). Formular-GUID nach dem Anlegen des HubSpot-Formulars eintragen:
const HUBSPOT_PORTAL_ID = "148893059";
const HUBSPOT_FORM_GUID = "4e5bb7a8-b68e-40a3-8311-f9c3c0bad78a"; // HubSpot-Formular "Neues leeres Formular" (EU-Portal), Felder: firstname, lastname, email, phone, message

// Lesbare Feld-Bezeichnungen fuer die Lead-Mail
function lead_labels() {
    return [
        "name" => "Name", "phone" => "Telefon", "email" => "E-Mail",
        "time" => "Beste Erreichbarkeit", "thema" => "Thema", "status" => "Aktuelle Situation",
        "einkommen" => "Nettoeinkommen", "eigenkapital" => "Eigenkapital", "nachricht" => "Nachricht",
        "quelle" => "Quelle", "form-name" => "Formular", "terminwunsch" => "Terminwunsch",
        "rechner_zusammenfassung" => "Rechner-Eingaben des Kunden",
        "immobilientyp" => "Immobilientyp", "plz" => "PLZ", "ort" => "Ort",
        "nutzung" => "Nutzung", "kaltmiete" => "Aktuelle Kaltmiete (€/Monat)",
        "wunschpreis" => "Wunschpreis (€)", "kaufzeitpunkt" => "Gekauft",
        "verkaufszeitpunkt" => "Verkaufswunsch",
    ];
}

function lead_headers($replyTo = "") {
    $h  = "From: " . LEAD_ABSENDER . "\r\n";
    if ($replyTo && filter_var($replyTo, FILTER_VALIDATE_EMAIL)) $h .= "Reply-To: " . $replyTo . "\r\n";
    $h .= "MIME-Version: 1.0\r\nContent-Type: text/plain; charset=utf-8\r\n";
    return $h;
}

function lead_subject_encode($s) { return "=?UTF-8?B?" . base64_encode($s) . "?="; }

// Sendet die Lead-Benachrichtigung an Robin
function lead_notify_robin($data, $zusatz = "") {
    $labels = lead_labels();
    $zeilen = [];
    foreach ($data as $key => $wert) {
        if ($key === "bot-field") continue;
        $wert = trim((string)$wert);
        if ($wert === "") continue;
        $label = $labels[$key] ?? ucfirst($key);
        $zeilen[] = $label . ": " . $wert;
    }
    $name = isset($data["name"]) ? $data["name"] : "";
    $form = isset($data["form-name"]) ? $data["form-name"] : "anfrage";
    $betreff = "Neuer Kapitalanlage-Lead (" . $form . ")" . $zusatz . " - " . $name;
    $text = "Neue Anfrage ueber meine-kapitalanlage-immobilie.de\n"
          . "==================================================\n\n"
          . implode("\n", $zeilen)
          . "\n\nGesendet am " . date("d.m.Y") . " um " . date("H:i") . " Uhr";
    $reply = isset($data["email"]) ? $data["email"] : "";
    return @mail(LEAD_EMPFAENGER, lead_subject_encode($betreff), $text, lead_headers($reply));
}

// Sendet die Bestaetigungsmail (Double-Opt-In) an den Interessenten
function lead_send_confirm($email, $name, $link) {
    $anrede = $name !== "" ? "Hallo " . $name . "," : "Hallo,";
    $text = $anrede . "\n\n"
          . "vielen Dank fuer Ihre Anfrage ueber meine-kapitalanlage-immobilie.de.\n\n"
          . "Bitte bestaetigen Sie kurz, dass Sie einen Rueckruf wuenschen – mit einem Klick auf diesen Link:\n\n"
          . $link . "\n\n"
          . "Erst nach dieser Bestaetigung nehme ich mit Ihnen Kontakt auf. So stelle ich sicher, dass die Angaben\n"
          . "wirklich von Ihnen stammen. Wenn Sie diese Anfrage nicht gestellt haben, ignorieren Sie diese Mail einfach.\n\n"
          . "Herzliche Gruesse\nRobin Jaenicke\nSpezialist fuer Kapitalanlage-Immobilien\nTel. 0160 92085192";
    return @mail($email, lead_subject_encode("Bitte bestaetigen Sie Ihre Rueckrufbitte"), $text, lead_headers());
}

// Uebergabe an HubSpot (Forms-API, EU). Ohne GUID passiert nichts.
function lead_to_hubspot($data) {
    if (HUBSPOT_FORM_GUID === "" || !function_exists("curl_init")) return false;
    $fields = [];
    $nm = trim((string)($data["name"] ?? ""));
    if ($nm !== "") {
        $parts = explode(" ", $nm, 2);
        $fields[] = ["name" => "firstname", "value" => $parts[0]];
        if (!empty($parts[1])) $fields[] = ["name" => "lastname", "value" => $parts[1]];
    }
    if (!empty($data["email"])) $fields[] = ["name" => "email", "value" => $data["email"]];
    if (!empty($data["phone"])) $fields[] = ["name" => "phone", "value" => $data["phone"]];

    $labels = lead_labels();
    $skip = ["name","email","phone","bot-field","form-name","quelle"];
    $msg = [];
    foreach ($data as $k => $v) {
        if (in_array($k, $skip, true)) continue;
        $v = trim((string)$v); if ($v === "") continue;
        $msg[] = ($labels[$k] ?? ucfirst($k)) . ": " . $v;
    }
    $msg[] = "Double-Opt-In: bestaetigt";
    if ($msg) $fields[] = ["name" => "message", "value" => implode("\n", $msg)];

    $payload = json_encode([
        "fields"  => $fields,
        "context" => ["pageName" => "Kapitalanlage-Landingpage", "pageUri" => LEAD_BASISURL],
    ]);
    $url = "https://api-eu1.hsforms.com/submissions/v3/integration/submit/" . HUBSPOT_PORTAL_ID . "/" . HUBSPOT_FORM_GUID;
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10,
    ]);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $code >= 200 && $code < 300;
}
