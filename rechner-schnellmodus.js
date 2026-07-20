/* ============================================================
   SCHNELLMODUS für den eingebetteten Rechner
   ------------------------------------------------------------
   Diese Datei verändert rechner.html NICHT. Sie wird von der
   Landingpage in das eingebettete iframe hineingereicht.

   Dadurch kannst du rechner.html jederzeit gefahrlos durch eine
   neue Fassung aus dem Chat ersetzen – der Schnellmodus bleibt
   funktionsfähig, weil er hier lebt.

   Die Elemente werden über ihre ÜBERSCHRIFTEN gefunden, nicht
   über IDs. Findet eine Regel ihr Ziel nicht (z. B. weil du eine
   Überschrift umbenannt hast), wird an dieser Stelle einfach
   nichts ausgeblendet – es geht nie etwas kaputt.
   ============================================================ */
(function (global) {
  'use strict';

  var CSS = [
    /* erklärende Fließtexte auf der Annahmen-Seite */
    '[data-mki="page2"] .sec-sub,[data-mki="page2"] .panel-sub{display:none!important}',
    /* Unterlagen-Import (Drag & Drop) */
    '#dropZone,#dzStatus{display:none!important}',
    /* Ehegattenschaukel: Parameter + Auswertungsbox */
    '[data-mki="schaukel"]{display:none!important}',
    /* "So rechnet der Vergleich" */
    '[data-mki="vergleich-notes"]{display:none!important}',
    /* 5. Kachel (EK-Rendite Volltilgung) – steht unten in der Karte */
    '#kpiRow .stat-card:nth-child(5){display:none!important}',
    /* Diagramme und lange Hinweise; Cashflow-Tabelle bleibt */
    'details.chart-panel,[data-mki="hinweise"]{display:none!important}',
    /* Aufklapper, die der Schnellmodus erzeugt */
    '.mki-more>summary{cursor:pointer;font-weight:600;font-size:.92rem;padding:10px 2px;list-style:none}',
    '.mki-more>summary::-webkit-details-marker{display:none}',
    '.mki-more>summary::before{content:"\\25B8 "}',
    '.mki-more[open]>summary::before{content:"\\25BE "}',
    '.mki-more{border-top:1px dashed var(--border,#dbe6e6);margin-top:12px}',
    '#mkiDisclaimer{margin-top:16px;font-size:.78rem;line-height:1.5;opacity:.75}'
  ].join('\n');

  function txt(el) { return (el && el.textContent || '').replace(/\s+/g, ' ').trim(); }

  function markiere(doc) {
    /* Seite 2 anhand der Überschrift "Annahmen zur Entwicklung" finden */
    var h2s = doc.querySelectorAll('h2');
    for (var i = 0; i < h2s.length; i++) {
      if (/Annahmen zur Entwicklung/i.test(txt(h2s[i]))) {
        var page = h2s[i].closest('.page') || h2s[i].parentNode;
        if (page) page.setAttribute('data-mki', 'page2');
        break;
      }
    }
    /* details-Blöcke anhand ihrer summary-Beschriftung markieren */
    var det = doc.querySelectorAll('details');
    for (var j = 0; j < det.length; j++) {
      var s = txt(det[j].querySelector('summary'));
      if (/Ehegattenschaukel/i.test(s))            det[j].setAttribute('data-mki', 'schaukel');
      else if (/So rechnet der Vergleich/i.test(s)) det[j].setAttribute('data-mki', 'vergleich-notes');
      else if (/Annahmen\s*&?\s*(amp;)?\s*Hinweise/i.test(s)) det[j].setAttribute('data-mki', 'hinweise');
    }
    /* Schaukel-Box in der Auswertung (kein details, eigene Klasse) */
    var box = doc.querySelector('.schaukel');
    if (box) box.setAttribute('data-mki', 'schaukel');
  }

  function el(doc, tag, cls, html) {
    var e = doc.createElement(tag);
    if (cls) e.className = cls;
    if (html != null) e.innerHTML = html;
    return e;
  }

  function umbauen(doc) {
    /* 1) Ehegattenschaukel zwingend AUS – der Schalter ist unsichtbar und
          darf das Ergebnis unter keinen Umständen beeinflussen. */
    try {
      var tg = doc.getElementById('schaukelToggle');
      if (tg && tg.checked) {
        tg.checked = false;
        tg.dispatchEvent(new (doc.defaultView.Event)('input'));
      }
    } catch (e) {}

    /* 2) Vergleichsanlage: nur Bruttorendite + laufende Kosten sichtbar. */
    var rend = doc.getElementById('etfRendite');
    var grid = rend && rend.closest ? rend.closest('.input-grid') : null;
    if (grid && !doc.querySelector('[data-mki="etf-more"]')) {
      var more = el(doc, 'details', 'mki-more');
      more.setAttribute('data-mki', 'etf-more');
      more.appendChild(el(doc, 'summary', null, 'Weitere Annahmen zur Vergleichsanlage anzeigen'));
      var box = el(doc, 'div', 'input-grid');
      ['basiszins', 'teilfrei', 'spbModus'].forEach(function (id) {
        var f = doc.getElementById(id);
        var field = f && f.closest ? f.closest('.field') : null;
        if (field) box.appendChild(field);
      });
      more.appendChild(box);
      grid.parentNode.insertBefore(more, grid.nextSibling);
    }

    /* 3) Verkauf nach Volltilgung einklappen – zuerst nur die 10-Jahres-Karte. */
    var tilg = doc.getElementById('szTilgCard');
    if (tilg && tilg.parentNode && !doc.querySelector('[data-mki="tilg-more"]')) {
      var col = tilg.parentNode;
      var d1 = el(doc, 'details', 'mki-more');
      d1.setAttribute('data-mki', 'tilg-more');
      d1.appendChild(el(doc, 'summary', null, 'Verkauf nach Volltilgung (Jahr 30) anzeigen'));
      col.parentNode.insertBefore(d1, col.nextSibling);
      d1.appendChild(tilg);
      col.style.display = 'block';
    }

    /* 4) Erklärung zur sinkenden EK-Rendite als Aufklapper mit Frage. */
    var callout = doc.querySelector('.callout.plain');
    if (callout && /EK-Rendite/.test(txt(callout))) {
      var d2 = el(doc, 'details', 'mki-more');
      d2.appendChild(el(doc, 'summary', null, 'Warum die EK-Rendite über die Laufzeit sinkt?'));
      var body = el(doc, 'div', 'notes-body');
      body.innerHTML = callout.innerHTML.replace(/^\s*<b>[^<]*<\/b>\s*/i, '');
      d2.appendChild(body);
      callout.parentNode.insertBefore(d2, callout);
      callout.remove();
    }

    /* 5) Kurz-Disclaimer (ersetzt den langen Hinweisblock). */
    if (!doc.getElementById('mkiDisclaimer')) {
      var tbl = doc.querySelector('details.tbl');
      if (tbl && tbl.parentNode) {
        var p = el(doc, 'p', 'notes-body',
          '<b>Wichtig:</b> Modellrechnung auf Basis von Annahmen &ndash; keine Steuer-, Rechts- oder ' +
          'Anlageberatung und keine Garantie f&uuml;r k&uuml;nftige Entwicklungen. Ergebnisse h&auml;ngen ' +
          'von Objekt, Miete und pers&ouml;nlichem Steuersatz ab.');
        p.id = 'mkiDisclaimer';
        tbl.parentNode.insertBefore(p, tbl.nextSibling);
      }
    }

    /* 6) Der Landingpage melden, sobald eine Auswertung vorliegt. */
    try {
      var kpi = doc.getElementById('kpiRow');
      var win = doc.defaultView;
      if (kpi && win && win.parent !== win && !kpi.getAttribute('data-mki-watch')) {
        kpi.setAttribute('data-mki-watch', '1');
        var melden = function () {
          if (kpi.children.length) {
            win.parent.postMessage({ type: 'mki:calc-done' }, '*');
          }
        };
        new win.MutationObserver(melden).observe(kpi, { childList: true });
        melden();
      }
    } catch (e) {}
  }

  /* Öffentliche Funktion: auf ein (gleiches Origin) Dokument anwenden. */
  global.mkiSchnellmodus = function (doc) {
    if (!doc || !doc.body) return false;
    if (doc.getElementById('mkiSchnellCSS')) { umbauen(doc); return true; }
    var st = doc.createElement('style');
    st.id = 'mkiSchnellCSS';
    st.textContent = CSS;
    (doc.head || doc.documentElement).appendChild(st);
    markiere(doc);
    umbauen(doc);
    return true;
  };
})(window);
