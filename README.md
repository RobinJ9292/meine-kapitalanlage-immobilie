# meine-kapitalanlage-immobilie.de

Landingpage für Kapitalanlageimmobilien (Robin Jänicke · Bonnfinanz).

## Struktur
- `index.html` – komplette Landingpage (Objekte & Annahmen im KONFIGURATION-Block im Skript pflegen)
- `formular.php` – Mail-Versand der Formulare (Empfänger/Absender oben in der Datei)
- `impressum.html`, `datenschutz.html`, `vermittlerprofil.pdf` – Rechtliches
- `og-image.jpg`, `favicon.svg`, `robots.txt`, `sitemap.xml`

## Deployment (Hostinger)
hPanel → Websites → Verwalten → Erweitert → **GIT**: Repository verbinden,
Branch `main`, Verzeichnis `public_html`. Danach bei jedem Push „Deploy" oder Auto-Deployment aktivieren.

## Vor Livegang
- Calendly-Event `kapitalanlage-erstgespraech` (60 Min.) anlegen
- Meta-Pixel / GA4 + Consent-Banner einbauen (Platzhalter im <head>)
- Beispielobjekte durch echte Objekte ersetzen
