# Landingpage-Farben für den Einsteiger-Guide

Ziel: den Guide farblich an `beratung.html` angleichen.
Palette ausgelesen aus dem `:root`-Block der Landingpage, Stand 20.07.2026.

---

## Die Palette der Landingpage

### Blau (Primär, Bonnfinanz-CI)

| Hex | Name | Verwendung auf der Seite |
|---|---|---|
| `#2C517F` | True Blue | Primärfarbe, Überschriften-Akzent, Buttons |
| `#0D1B2E` | Blue Deep | Dunkelster Ton, Hero-Hintergrund |
| `#122844` | Blue Night | Dunkle Kästen |
| `#1B3A61` | Blue Mid | Mittleres Blau, Verläufe |
| `#173252` | Navy Card | Kartenflächen auf dunklem Grund |

### Jade (Akzent)

| Hex | Name | Verwendung auf der Seite |
|---|---|---|
| `#9FE8E2` | Jade Bright | Stärkster Akzent, Zahlen, CTA |
| `#B4D6D4` | Jade | Akzentflächen, Rahmen |
| `#CCE7E6` | Jade 60 | Feine Rahmen |
| `#F0F7F7` | Jade 20 | Getönte Flächen |
| `#ECF5F4` | Light Jade | Sehr helle Flächen |
| `#8CC7CF` | — | Endpunkt des CTA-Verlaufs |
| `#7FB6C4` | — | Endpunkt des Text-Verlaufs |

### Text und Flächen

| Hex | Name | Verwendung auf der Seite |
|---|---|---|
| `#22354C` | Ink | Fließtext und Überschriften auf hell |
| `#5E718A` | Muted | Sekundärtext |
| `#EAF2F6` | Text auf dunkel | Überschriften auf dunklem Grund |
| `#A7BCCD` | Text auf dunkel, gedämpft | Fließtext auf dunklem Grund |
| `#F6FAFA` | Paper | Heller Seitenhintergrund |
| `#FFFFFF` | Weiß | Karten |
| `#E1EAEA` | Line | Trennlinien |
| `#6DA9B5` | Info | Hinweise |
| `#EB2747` | Error | Fehler, Warnungen |

### Verläufe

- **CTA-Button:** `linear-gradient(100deg, #B4D6D4 0%, #9FE8E2 55%, #8CC7CF 100%)`
- **Text-Verlauf:** `linear-gradient(95deg, #9FE8E2, #B4D6D4 55%, #7FB6C4)`

---

## Umsetzungstabelle: was im Guide womit ersetzen

Links der Wert, der aktuell in der PDF steckt — rechts der passende
Landingpage-Ton.

| Rolle im Guide | bisher | neu |
|---|---|---|
| Heller Seitengrund | `#FAF9F4` | `#F6FAFA` |
| Dunkle Seiten und Kästen | `#17201B` | `#0D1B2E` |
| Dunkle Fläche, etwas heller | — | `#122844` |
| Getönte Infokästen | `#F1ECE2` | `#F0F7F7` |
| Hellere Tönung | `#F7F2E6` | `#ECF5F4` |
| Karten auf getöntem Grund | `#FFFFFF` | `#FFFFFF` (bleibt) |
| Überschriften auf hell | `#1E241F` | `#22354C` |
| Fließtext auf hell | `#242A22` | `#22354C` |
| Sekundärtext auf hell | `#4A4E42` | `#5E718A` |
| Gedämpfter Text auf hell | `#77715F` | `#5E718A` |
| Titel auf dunkel | `#FBF9F4` | `#EAF2F6` |
| Fließtext auf dunkel | `#EFEAD9` | `#EAF2F6` |
| Gedämpfter Text auf dunkel | `#D9D3C2` | `#A7BCCD` |
| Zitate auf dunkel | `#B9B4A2` | `#A7BCCD` |
| Fußzeilen, Kolumnentitel | `#8B8471` | `#A7BCCD` |
| Kleinstschrift gesperrt | `#7C7460` | `#A7BCCD` |
| **Hauptakzent** (Kapitelziffern, Buttons) | `#C7B08A` | `#9FE8E2` |
| Akzent auf hellem Grund | `#A98B5F` | `#2C517F` |
| Standard-Trennlinie | `#E6E0D2` | `#E1EAEA` |
| Kräftigere Trennlinie | `#D8CFBB` | `#CCE7E6` |
| Akzentlinie | `#C7B08A` | `#B4D6D4` |

---

## Zwei Hinweise zur Umstellung

**Der Akzent wechselt von warm auf kalt.** Messing `#C7B08A` gegen Jade
`#9FE8E2` ist der größte Eingriff — er verändert den Charakter des Guides
deutlich. Auf dunklem Grund funktioniert Jade sehr gut (so wie im Hero der
Landingpage), auf hellem Grund ist es dagegen zu blass für Text; dort besser
`#2C517F` nehmen.

**Die Fotos sind auf warmes Licht abgestimmt.** Das Altbau-Motiv im
Abendlicht und die Schlüsselübergabe tragen viel Gelb- und Bernsteinton. Mit
einer blau-jadegrünen Typografie darüber kann das unruhig wirken. Falls es
beißt: die Bilder leicht entsättigen oder kühler abstimmen — oder das Messing
als Bildakzent behalten und nur Text, Flächen und Linien umstellen.
