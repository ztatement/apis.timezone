<?php

/**
  * Haupt-Index-Datei für die Timezone API Demo.
  * 
  * @author Thomas Boettcher @ztatement (github[at]ztatement[dot]com)
  * @copyright (c) 2026 ztatement
  *
  * @version 1.0.0.2026.06.15
  * @file $Id: index.php $
  * @created $Id: 1 Montag, 15. Juni 2026, 06:26:51 GMT+0200Z ztatement $
  *
  * @repository https://github.com/ztatement/timezone
  * @license MIT (https://opensource.org/license/MIT)
  * @see LICENSE
  */

  $config = require __DIR__ . "/config.php";
?>
<!DOCTYPE html>
<html lang="de">

<head>
  <meta charset="UTF-8">
  <title>Timezone API – Demo & Dokumentation</title>
  <link rel="stylesheet" href="assets/css/style.css">
  <!-- Chart.js CDN einbinden -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <!-- App Logik einbinden -->
  <script src="assets/js/app.js" defer></script>
</head>

<body>
  <h1>⏱️ Timezone API</h1>
  <p>Willkommen zur API-Dokumentation und Live‑Demo.</p>

  <!-- Sektion: Information über Endpunkte -->
  <div class="box">
    <h2>🔗 Endpunkte & Authentifizierung</h2>
    <p>Basis-URL: <code>./</code></p>
    <ul>
      <li><code>/timezones</code> – Gängige Zeitzonen</li>
      <li><code>/timezones/all</code> – Alle verfügbaren Zeitzonen</li>
      <li><code>/transitions?tz=...&limit=5</code> – Nächste DST-Wechsel</li>
      <li><code>/convert?from=...&to=...&time=...</code> – Zeitumrechnung</li>
    </ul>
    <p>Authentifizierung empfohlen via Header: <code>X-API-KEY: DEIN_KEY</code></p>
  </div>

  <!-- Sektion: Interaktive Konsole (Live-Demo) -->
  <div class="box" id="demo-area">
    <h2>🧪 Interaktive API-Konsole 
      <span style="font-size: 12px; font-weight: normal; padding: 2px 8px; border-radius: 12px; margin-left: 10px; 
        background: <?= $config['api_key_enabled'] ? '#ff6b6b' : '#28a745' ?>; color: white;">
        API-Schutz: <?= $config['api_key_enabled'] ? 'AKTIV' : 'INAKTIV' ?>
      </span>
    </h2>

    <div class="controls">
      <div>
        <label>API Key (optional)</label>
        <input type="text" id="apiKey" placeholder="<?= $config['api_key_enabled'] ? 'Key erforderlich!' : 'z.B. MEIN-SCHLUESSEL' ?>">
      </div>
      <div>
        <label>Aktion</label>
        <select id="apiAction" onchange="toggleInputs()">
          <option value="timezones">Gängige Zeitzonen laden</option>
          <option value="timezones/all">Alle Zeitzonen laden</option>
          <option value="transitions">Zeitumstellungen (DST)</option>
          <option value="convert">Zeitumrechnung</option>
        </select>
      </div>
      <button id="submitBtn" onclick="runDemo()">Anfrage senden</button>
    </div>

    <div id="convertInputs" class="controls" style="display:none;">
      <div>
        <label>Von</label>
        <input id="fromTz" value="Europe/Berlin" list="tzOptions">
        <div id="fromTzError" class="field-error"></div>
      </div>
      <button onclick="swapTimezones()" title="Zeitzonen vertauschen" style="background: #6c757d; margin-bottom: 2px; padding: 5px 10px;">⇄</button>
      <div>
        <label>Nach</label>
        <input id="toTz" value="America/New_York" list="tzOptions">
        <div id="toTzError" class="field-error"></div>
      </div>
      <div>
        <label>Zeit</label>
        <div style="display: flex; align-items: center; gap: 10px;">
          <input id="timeInput" value="<?= date('Y-m-d\TH:i') ?>">
          <button type="button" onclick="setDateTimeInputToCurrentMinute()" style="background: #6c757d; padding: 5px 10px; font-size: 11px;">Aktualisieren</button>
          <div style="display: flex; align-items: center; gap: 5px;">
            <input type="checkbox" id="allowPast" onchange="togglePastDates()" style="width: auto;">
            <label for="allowPast" style="font-weight: normal; margin-bottom: 0; font-size: 11px; cursor: pointer; white-space: nowrap;">Vergangenheit erlauben</label>
          </div>
        </div>
        <div id="timeInputError" class="field-error"></div>
      </div>
    </div>

    <datalist id="tzOptions"></datalist>
    <div id="statusInfo"></div>
    <pre id="demoOutput" class="jsonbox">Bereit für Anfrage...</pre>

    <button id="toggleChartBtn" onclick="toggleChart()" style="margin-top: 15px; background: #6c757d;">Performance-Statistiken anzeigen</button>

    <div id="latencyChartContainer" class="chart-container">
      <h3>Antwortzeiten (letzte 10 Anfragen) <span id="avgLatency" style="font-weight: normal; font-size: 12px; color: #888; margin-left: 10px;"></span></h3>
      <canvas id="latencyChart" width="400" height="150"></canvas>
    </div>
  </div>

  <!-- Sektion: Statische Dokumentation und Beispiele -->
  <div class="box">
    <h3>📖 Dokumentation</h3>

    <p><strong>Beispiel-Abfrage:</strong> Liste gängiger Zeitzonen (GET)</p>
    <div class="copy-group">
      <code id="url1">https://timezone.apis.demo-seite.com/timezones</code>
      <button class="btn-copy" onclick="copyToClipboard(document.getElementById('url1').textContent, this)">URL kopieren</button>
      <button class="btn-load" onclick="loadExample('timezones')">In Konsole laden</button>
    </div>

    <p>Antwort (JSON):</p>
    <pre style="background: #eee; padding: 10px; border-radius: 4px; font-size: 13px;">{
    "status": "success",
    "data": {
      "Africa/Abidjan": "Africa/Abidjan",
      "Africa/Algiers": "Africa/Algiers",
      "Europe/Berlin": "Europe/Berlin",
      "...": "..."
    }
}</pre>

    <hr style="border: 0; border-top: 1px solid #ddd; margin: 20px 0;">

    <p><strong>Beispiel-Abfrage:</strong> Zeitumrechnung Berlin → New York (GET)</p>
    <div class="copy-group">
      <code id="url2">https://timezone.apis.demo-seite.com/convert?from=Europe/Berlin&to=America/New_York&time=2024-01-01T12:00</code>
      <button class="btn-copy" onclick="copyToClipboard(document.getElementById('url2').textContent, this)">URL kopieren</button>
      <button class="btn-load" onclick="loadExample('convert', 'Europe/Berlin', 'America/New_York', '2024-01-01T12:00')">In Konsole laden</button>
    </div>

    <p>Antwort (JSON):</p>
    <pre style="background: #eee; padding: 10px; border-radius: 4px; font-size: 13px;">{
    "status": "success",
    "data": {
      "from": "Europe/Berlin",
      "to": "America/New_York",
      "offset_from_hours": 1,
      "offset_from_formatted": "+01:00",
      "offset_to_hours": -5,
      "offset_to_formatted": "-05:00",
      "offset_diff_hours": -6,
      "input": "2024-01-01T12:00",
      "output": "2024-01-01 06:00:00",
      "output_timezone_abbr": "EST",
      "is_dst": false,
      "dst_label": "Winterzeit",
      "output_day_of_week": "Monday",
      "day_jump": false
    }
}</pre>
    <hr style="border: 0; border-top: 1px solid #ddd; margin: 20px 0;">

    <p><strong>Beispiel-Abfrage:</strong> Nächste Zeitumstellungen (GET)</p>
    <div class="copy-group">
      <code id="url3">https://timezone.apis.demo-seite.com/transitions?tz=Europe/Berlin&limit=2</code>
      <button class="btn-copy" onclick="copyToClipboard(document.getElementById('url3').textContent, this)">URL kopieren</button>
      <button class="btn-load" onclick="loadExample('transitions', 'Europe/Berlin')">In Konsole laden</button>
    </div>
    <p>Antwort (JSON):</p>
    <pre style="background: #eee; padding: 10px; border-radius: 4px; font-size: 13px;">{
    "status": "success",
    "data": {
      "timezone": "Europe/Berlin",
      "transitions": [
        {
          "ts": 1743300000,
          "time": "2025-03-30T01:00:00+0000",
          "offset": 7200,
          "type": "Spring Forward",
          "offset_formatted": "+02:00",
          "isdst": true,
          "abbr": "CEST"
        }
      ]
    }
}</pre>
    <hr style="border: 0; border-top: 1px solid #ddd; margin: 20px 0;">
    <p>Die vollständige OpenAPI-Spezifikation findest du hier:</p>
    <a href="openapi.json" target="_blank">openapi.json öffnen</a>
  </div>
</body>
</html>