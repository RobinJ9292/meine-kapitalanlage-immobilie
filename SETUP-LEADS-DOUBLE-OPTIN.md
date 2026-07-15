# Einrichtung: Double-Opt-In + HubSpot für die Lead-Formulare

Kurzanleitung, um den neuen Bestätigungs-Flow live zu schalten. Betrifft alle Lead-Wege der Seite.

## Was neu ist

- **`lead-intake.php`** — nimmt alle Formulare an (Verkäufer-Quiz + Rückruf-Kontaktformular).
- **`bestaetigen.php`** — die Seite, die per Bestätigungslink aufgerufen wird.
- **`_lead-lib.php`** — gemeinsame Logik + Einstellungen (E-Mail-Texte, HubSpot).
- **`_leads/`** — Zwischenspeicher für noch nicht bestätigte Anfragen (per `.htaccess` gegen Web-Zugriff gesperrt, per `.gitignore` vom Repo ausgeschlossen).
- Die alte **`formular.php`** bleibt unverändert liegen (Fallback – aktuell nicht mehr eingebunden).

## So läuft der Flow

1. Besucher füllt Quiz oder Rückruf-Formular aus → Daten werden zwischengespeichert, er bekommt eine **Bestätigungsmail**.
2. Er klickt den Link → `bestaetigen.php` → **jetzt** bekommst du die Lead-Mail **und** der Kontakt geht **an HubSpot** (als bestätigt/qualifiziert).
3. **Ausnahme Terminbuchung:** Wer im Kontaktformular „direkt Termin buchen" wählt, gilt als bereits qualifiziert → du bekommst den Lead sofort, ohne Bestätigungsschritt (die Calendly-Buchung ist der Nachweis).

## 1 · Deploy

Alle neuen Dateien über GitHub Desktop pushen. Hostinger deployt automatisch. Prüfe im Datei-Manager, dass der Ordner **`_leads/`** existiert und beschreibbar ist (Rechte 755/775). Er wird sonst beim ersten Lead automatisch angelegt.

## 2 · HubSpot-Formular — ✅ bereits erledigt

Das Formular ist angelegt und **veröffentlicht** (EU-Portal `148893059`), mit den Feldern **Vorname, Nachname, E-Mail, Telefonnummer, Nachricht**. Die GUID ist schon in `_lead-lib.php` eingetragen:

```php
const HUBSPOT_FORM_GUID = "4e5bb7a8-b68e-40a3-8311-f9c3c0bad78a";
```

Du musst hier nichts mehr tun. (Falls du das Formular je löschst/neu anlegst, hier die neue GUID eintragen.)

Tipp: Damit bestätigte Leads sofort als qualifiziert erscheinen, kannst du im HubSpot-Formular unter „Automatisierung" die Lifecycle-Stage auf „Lead" setzen. Die Detail-Antworten aus dem Quiz landen im Feld **Nachricht**.

## 3 · E-Mail-Zustellbarkeit (SPF + DKIM einrichten)

Die Bestätigungsmail geht von `info@meine-kapitalanlage-immobilie.de` über den Hostinger-Mailserver. Damit sie zuverlässig im Postfach landet (nicht Spam), muss die Domain **SPF + DKIM** haben:

1. hPanel → **Domains** → deine Domain → **DNS-Zone** (bzw. **E-Mails** → Domain → E-Mail-Konfiguration).
2. Hostinger bietet oft eine **automatische Einrichtung** an, die MX + SPF + DKIM in einem Rutsch setzt („Für Hostinger-E-Mail einrichten" / „automatisch verbinden"). Das ist der einfachste Weg.
3. Prüfen:
   - **SPF**: nur **ein** TXT-Record `v=spf1 …`. Falls schon einer existiert, nicht doppeln, sondern zusammenführen.
   - **DKIM**: wird als **CNAME** angelegt (nicht TXT). Wenn es als TXT oder mit Tippfehler drin steht → Signatur schlägt fehl.
   - **DMARC** (empfohlen, manuell): TXT-Record auf `_dmarc` mit `v=DMARC1; p=none; rua=mailto:info@meine-kapitalanlage-immobilie.de` — startet im „nur beobachten"-Modus.
4. Nach dem Setzen bis zu 24 h Propagation. Danach **testen**: eine Bestätigungsmail an dich selbst schicken und die Adresse bei **mail-tester.com** prüfen (Ziel: 9–10/10).

Absender/Empfänger stehen oben in `_lead-lib.php` und lassen sich dort ändern. (Von einer fremden Adresse wie `…@bonnfinanz.de` über Hostinger zu senden würde SPF/DKIM brechen → mehr Spam; dafür bräuchte es echten Versand über den Bonnfinanz-SMTP-Server.)

## 4 · Testen

1. Quiz oder Rückruf-Formular mit **deiner eigenen** E-Mail ausfüllen und absenden.
2. Erfolgsmeldung „Fast geschafft – bitte E-Mail bestätigen" sollte erscheinen.
3. Bestätigungsmail öffnen → Link klicken → grüne „Vielen Dank – bestätigt!"-Seite.
4. Prüfen: Lead-Mail bei `info@…` angekommen? Kontakt in HubSpot angelegt (wenn GUID gesetzt)?
5. Termin-Weg separat testen: „direkt Termin buchen" → Lead sollte **sofort** ankommen.

## Gut zu wissen

- Unbestätigte Anfragen liegen 7 Tage in `_leads/` und verfallen dann.
- Wenn der Speicher mal nicht schreibbar ist, wird der Lead **direkt** an dich gemailt (mit Hinweis „unbestätigt") – es geht also nie einer verloren.
- E-Mail ist im Rückruf-Kontaktformular jetzt **Pflichtfeld** (wird für die Bestätigung gebraucht).
