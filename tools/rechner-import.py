#!/usr/bin/env python3
"""
Rechner aus dem Chat übernehmen
===============================

Wenn du den Rechner im Chat geändert und als HTML exportiert hast:

    python3 tools/rechner-import.py ~/Downloads/immobilien-kapitalanlage-rechner_5.html

Das Skript ersetzt rechner.html durch deine neue Fassung und setzt den
Absender-Block (Name, Website, Telefon, Disclaimer) wieder ein – der ist
das Einzige, was in der Datei selbst stehen muss, weil ein Download
nachträglich nicht mehr verändert werden kann.

Der Schnellmodus muss NICHT wiederhergestellt werden: Er liegt in
rechner-schnellmodus.js und wird von der Landingpage eingespielt.
"""

import shutil
import sys
from pathlib import Path

WURZEL = Path(__file__).resolve().parent.parent
ZIEL = WURZEL / "rechner.html"
MARKER = 'id="mkiBrandFooter"'
MARKER_LADER = "rechner-schnellmodus.js"

LADER = """
<!-- ====== SCHNELLMODUS-LADER (automatisch eingesetzt) ======
     Laedt die schlanke Ansicht bei Aufruf mit ?modus=schnell.
     Die Regeln stehen in rechner-schnellmodus.js, nicht hier. -->
<script>
(function(){
  try{
    if(new URLSearchParams(location.search).get('modus') !== 'schnell') return;
    var s = document.createElement('script');
    s.src = 'rechner-schnellmodus.js';
    s.onload = function(){
      try{ if(window.mkiSchnellmodus) window.mkiSchnellmodus(document); }catch(e){}
    };
    (document.head || document.documentElement).appendChild(s);
  }catch(e){}
})();
</script>
"""

ABSENDER = """
<!-- ====== ABSENDER-BLOCK (automatisch eingesetzt) ======
     Nur sichtbar beim Direktaufruf/Download - im iframe unsichtbar. -->
<div id="mkiBrandFooter" style="display:none">
  <div style="max-width:960px;margin:44px auto 30px;padding:22px 24px 4px;border-top:1px solid #dbe6e6;font-family:'Open Sans',-apple-system,'Segoe UI',sans-serif;text-align:center;color:#5E718A;font-size:13.5px;line-height:1.65">
    <p style="margin:0 0 6px;font-size:15px"><b style="color:#22354C">Ihr pers&ouml;nlicher Kapitalanlage-Rechner</b></p>
    <p style="margin:0 0 14px">Diese Datei geh&ouml;rt Ihnen. Speichern Sie sie ab &ndash; sie funktioniert jederzeit, auch offline, ohne Anmeldung und ohne Tracking.</p>
    <p style="margin:0 0 14px">Fragen zu Ihrer konkreten Rechnung? Melden Sie sich einfach &ndash; ich rechne Ihr Objekt gern gemeinsam mit Ihnen durch.</p>
    <p style="margin:0 0 18px">
      <a href="https://meine-kapitalanlage-immobilie.de/beratung" style="color:#2C517F;font-weight:700;text-decoration:none">meine-kapitalanlage-immobilie.de</a>
      &nbsp;&middot;&nbsp;
      <a href="tel:+4916092085192" style="color:#2C517F;font-weight:700;text-decoration:none">0160&nbsp;92085192</a>
      &nbsp;&middot;&nbsp; Robin J&auml;nicke
    </p>
    <p style="margin:0 0 18px;font-size:11px;color:#8092a6">Modellrechnung &ndash; kein Versprechen und keine individuelle Steuer- oder Anlageberatung.</p>
  </div>
</div>
<script>(function(){try{if(window.self===window.top){var f=document.getElementById('mkiBrandFooter');if(f)f.style.display='';}}catch(e){}})();</script>
"""


def main() -> int:
    if len(sys.argv) != 2:
        print(__doc__)
        return 1

    quelle = Path(sys.argv[1]).expanduser()
    if not quelle.is_file():
        print(f"FEHLER: Datei nicht gefunden: {quelle}")
        return 1

    inhalt = quelle.read_text(encoding="utf-8")

    if "</body>" not in inhalt:
        print("FEHLER: Das sieht nicht nach einer vollstaendigen HTML-Datei aus.")
        return 1

    zusatz = ""
    if MARKER_LADER in inhalt:
        print("Hinweis: Schnellmodus-Lader war bereits enthalten.")
    else:
        zusatz += LADER
        print("Schnellmodus-Lader eingesetzt.")

    if MARKER in inhalt:
        print("Hinweis: Absender-Block war bereits enthalten.")
    else:
        zusatz += ABSENDER
        print("Absender-Block eingesetzt.")

    if zusatz:
        i = inhalt.rfind("</body>")
        neu = inhalt[:i] + zusatz + inhalt[i:]
    else:
        neu = inhalt

    if ZIEL.exists():
        sicherung = ZIEL.with_suffix(".html.backup")
        shutil.copy2(ZIEL, sicherung)
        print(f"Sicherung angelegt: {sicherung.name}")

    ZIEL.write_text(neu, encoding="utf-8")
    print(f"Fertig: {ZIEL.name} aktualisiert ({len(neu):,} Zeichen).")
    print("Naechster Schritt: in GitHub Desktop committen und pushen.")
    return 0


if __name__ == "__main__":
    raise SystemExit(main())
