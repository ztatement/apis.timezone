# Projektstruktur - Timezone API

/timezone
│   # Hauptverzeichnis des Projekts
├── public/
│   ├── index.php        # Startseite (Live-Demo + Erklärung)
│   ├── tz.php           # Zentraler Einstiegspunkt für alle API-Endpunkte
│   ├── openapi.json     # OpenAPI-Spezifikation der API
│   ├── swagger/         # Swagger UI (falls für interaktive Doku aktiviert)
│   ├── assets/          # Statische Assets für das Frontend (Demo)
│   │   ├── css/         # CSS-Dateien
│   │   │   └── style.css
│   │   └── js/          # JavaScript-Dateien
│   │       └── app.js
│   └── .htaccess        # Apache Rewrite-Regeln für Clean URLs im public-Ordner
│
├── src/
│   ├── Services/
│   │   └── TimezoneService.php
│   └── Controllers/
│       └── TimezoneController.php
│
├── composer.json
├── config.php           # Konfigurationsdatei (z.B. für API-Key)
├── autoload.php         # Autoloader und globale Fehlerbehandlung
├── .htaccess            # Apache Rewrite-Regeln für Clean URLs (Root-Verzeichnis)
└── .logs/               # Verzeichnis für PHP-Fehlerlogs (wird automatisch erstellt)
