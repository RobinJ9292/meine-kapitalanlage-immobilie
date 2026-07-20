<?php
// =====================================================================
//  Double-Opt-In Bestaetigung fuer meine-kapitalanlage-immobilie.de
//  Aufruf per Link aus der Bestaetigungsmail: bestaetigen.php?t=TOKEN
//  Nach Klick: Lead geht an Robin + an HubSpot (qualifiziert).
// =====================================================================
require __DIR__ . "/_lead-lib.php";

function seite($titel, $htmlInhalt, $status = 200) {
    http_response_code($status);
    header("Content-Type: text/html; charset=utf-8");
    echo '<!doctype html><html lang="de"><head><meta charset="utf-8">'
       . '<meta name="viewport" content="width=device-width, initial-scale=1.0">'
       . '<meta name="robots" content="noindex">'
       . '<title>' . $titel . ' · Robin Jaenicke</title>'
       . '<style>body{margin:0;font-family:"Open Sans",-apple-system,Segoe UI,sans-serif;background:#F6FAFA;color:#22354C;'
       . 'display:flex;min-height:100vh;align-items:center;justify-content:center;padding:24px}'
       . '.box{background:#fff;border:1px solid #E4EDED;border-radius:20px;max-width:520px;padding:40px 34px;text-align:center;'
       . 'box-shadow:0 20px 50px rgba(13,27,46,.08)}.ic{font-size:52px;line-height:1;margin-bottom:10px}'
       . 'h1{font-size:23px;color:#2C517F;margin:0 0 10px}p{font-size:15.5px;line-height:1.6;color:#5E718A;margin:0 0 8px}'
       . 'a.btn{display:inline-block;margin-top:18px;background:linear-gradient(100deg,#B4D6D4,#9FE8E2);color:#0D1B2E;'
       . 'font-weight:800;text-decoration:none;padding:14px 26px;border-radius:999px}a.tel{color:#2C517F;font-weight:800;text-decoration:none}</style>'
       . '</head><body><div class="box">' . $htmlInhalt
       . '<a class="btn" href="' . LEAD_BASISURL . '/">Zurueck zur Startseite</a></div></body></html>';
    exit;
}

$token = isset($_GET["t"]) ? preg_replace('/[^a-f0-9]/', '', $_GET["t"]) : "";
$file  = LEAD_STORE . "/" . $token . ".json";

if ($token === "" || !is_file($file)) {
    seite("Link ungueltig",
        '<div class="ic">⏳</div><h1>Link ungueltig oder bereits genutzt</h1>'
      . '<p>Dieser Bestaetigungslink ist nicht mehr gueltig. Vielleicht haben Sie ihn schon bestaetigt.</p>'
      . '<p>Falls nicht, rufen Sie mich gern direkt an: <a class="tel" href="tel:+4916092085192">0160 92085192</a>.</p>', 410);
}

$rec  = json_decode(@file_get_contents($file), true);
$data = is_array($rec) && isset($rec["data"]) ? $rec["data"] : [];

// Optional abgelaufen? (7 Tage)
if (isset($rec["ts"]) && (time() - (int)$rec["ts"]) > 7 * 24 * 3600) {
    @unlink($file);
    seite("Link abgelaufen",
        '<div class="ic">⏳</div><h1>Link abgelaufen</h1>'
      . '<p>Aus Sicherheitsgruenden war dieser Link nur 7 Tage gueltig. Bitte stellen Sie Ihre Anfrage kurz erneut.</p>', 410);
}

// Jetzt qualifiziert: an Robin + HubSpot
$data["doubleoptin"] = "bestaetigt am " . date("d.m.Y H:i");
lead_notify_robin($data, " (BESTAETIGT)");
lead_to_hubspot($data);
@unlink($file);

// Bestaetigung samt Geschenken an den Interessenten
lead_send_danke($data["email"] ?? "", $data["name"] ?? "");

if (!empty($data["leadmagnet"])) {
    $pdf = LEAD_BASISURL . "/einsteiger-guide-kapitalanlage-2026.pdf";
    seite("Bestaetigt – Ihr Einsteiger-Guide",
        '<div class="ic" style="color:#1a9e5f">✓</div><h1>Vielen Dank – hier ist Ihr Guide!</h1>'
      . '<p>Der Guide oeffnet sich in einem neuen Tab. Und wenn beim Lesen Fragen aufkommen: Ich bin nur einen Anruf entfernt.</p>'
      . '<a class="btn" href="' . $pdf . '" target="_blank" rel="noopener">Einsteiger-Guide oeffnen (PDF)</a>'
      . '<p style="margin-top:14px"><a class="tel" href="tel:+4916092085192">0160 92085192</a></p>');
}

seite("Bestaetigt",
    '<div class="ic" style="color:#1a9e5f">✓</div><h1>Vielen Dank – bestaetigt!</h1>'
  . '<p>Ihre Rueckrufbitte ist jetzt bei mir. Ich melde mich schnellstmoeglich persoenlich bei Ihnen.</p>'
  . '<p>Sie moechten nicht warten? <a class="tel" href="tel:+4916092085192">0160 92085192</a></p>');
