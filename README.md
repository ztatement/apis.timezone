# ⏱️ Timezone API

Eine einfache und robuste PHP-API zur Abfrage von Zeitzoneninformationen, Zeitumrechnungen und Zeitumstellungen (Daylight Saving Time - DST).

## ✨ Features

- **Gängige Zeitzonen:** Abruf einer Liste häufig verwendeter Zeitzonen.
- **Alle Zeitzonen:** Abruf aller IANA-Zeitzonen-IDs.
- **Zeitumrechnung:** Umrechnung einer Zeit von einer Zeitzone in eine andere.
- **Zeitumstellungen (DST):** Anzeige der nächsten Zeitumstellungen für eine spezifische Zeitzone.
- **API-Key Schutz:** Optionale Authentifizierung über einen API-Key.
- **Clean URLs:** Saubere und lesbare API-Endpunkte ohne `.php`-Erweiterung.
- **Interaktive Demo:** Eine Live-Konsole zur direkten Interaktion mit der API.
- **Fehlerbehandlung:** Konsistente JSON-Fehlerantworten und Logging.
- **Performance-Messung:** Client-seitige Latenzmessung mit Diagramm in der Demo.

## 🚀 Installation & Setup

Diese API ist für die Ausführung unter Apache mit PHP (mind. 7.4) optimiert.

1.  **Projekt klonen:**
    ```bash
    git clone <URL_DEINES_REPOS> apis/timezone
    cd apis/timezone
    ```

2.  **Composer Abhängigkeiten installieren:**
    ```bash
    composer install
    ```

3.  **Apache `.htaccess` Konfiguration:**
    Stelle sicher, dass das Apache-Modul `mod_rewrite` aktiviert ist. Die vorhandenen `.htaccess`-Dateien im Hauptverzeichnis und im `public/`-Ordner sorgen automatisch für die Weiterleitung an die `tz.php`.

4.  **`config.php` anpassen:**
    Falls noch nicht geschehen, erstelle die Datei `config.php` im Hauptverzeichnis:
    ```php
    // c:\...\www\apis\timezone\config.php
    <?php
    return [
      'api_key_enabled' => false,                  // Auf true setzen für API-Key Schutz
      'api_key' => 'DEIN-GEHEIMER-SCHLUESSEL-123', // Dein geheimer API-Key
    ];
    ```

5.  **Webserver-Konfiguration (Beispiel WAMP/Apache):**
    Platziere das Projekt in deinem Webserver-Root unter `c:\...\www\apis\timezone`.
    Die Benutzeroberfläche und Dokumentation sind dann im Browser unter `http://localhost/apis/timezone/` erreichbar.

## 💡 Nutzung

Die API-Endpunkte sind über die Basis-URL deines Setups erreichbar (z.B. `http://localhost/apis/timezone/`).

### Endpunkte

-   **`GET /timezones`**
    Gibt eine Liste gängiger Zeitzonen zurück.
    Beispiel: `GET /timezones`
    ```json
    { "status": "success", "data": { "Europe/Berlin": "Europe/Berlin", ... } }
    ```
    
-   **`GET /timezones/all`**
    Gibt eine Liste aller verfügbaren IANA-Zeitzonen-IDs zurück.
    Beispiel: `GET /timezones/all`

-   **`GET /transitions?tz=<IANA_ZONE>&limit=<COUNT>`**
    Gibt die nächsten Zeitumstellungen (DST-Wechsel) für eine Zeitzone zurück.
    Parameter: `tz` (erforderlich, z.B. `Europe/Berlin`), `limit` (optional, Standard: 5).
    Beispiel: `GET /transitions?tz=Europe/Berlin&limit=2`
    ```json
    { "status": "success", "data": { "timezone": "Europe/Berlin", "transitions": [...] } }
    ```

-   **`GET /convert?from=<FROM_ZONE>&to=<TO_ZONE>&time=<ISO_TIME>`**
    Rechnet eine Zeit von einer Zeitzone in eine andere um.
    Parameter: `from` (erforderlich, Ausgangs-Zeitzone), `to` (erforderlich, Ziel-Zeitzone), `time` (erforderlich, ISO 8601 Format, z.B. `2024-01-01T12:00`).
    Beispiel: `GET /convert?from=Europe/Berlin&to=America/New_York&time=2024-01-01T12:00`
    ```json
    { "status": "success", "data": { "from": "Europe/Berlin", "to": "America/New_York", ... } }
    ```

### Authentifizierung

Wenn `api_key_enabled` in der `config.php` auf `true` gesetzt ist, muss jeder API-Request einen gültigen API-Key enthalten.

Empfohlen wird die Übertragung via HTTP-Header:
`X-API-KEY: DEIN-GEHEIMER-SCHLUESSEL-123`

Alternativ kann der Key auch als URL-Parameter übergeben werden (nicht empfohlen für Produktion):
`?key=DEIN-GEHEIMER-SCHLUESSEL-123`

## 🌐 Live-Demo & Dokumentation

Besuche die `index.php` im Root-Verzeichnis deines Projekts (z.B. `http://localhost/apis/timezone/`) für eine interaktive Demo und detaillierte Dokumentation der API-Endpunkte.

## 📂 Projektstruktur

Eine detaillierte Übersicht über die Projektstruktur findest du in der `projektstruktur.md`.

## 📝 OpenAPI-Spezifikation

Die vollständige OpenAPI-Spezifikation der API ist unter `/openapi.json` verfügbar.
