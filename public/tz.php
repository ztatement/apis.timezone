<?php

/**
  * 
  * 
  * @author Thomas Boettcher @ztatement (github[at]ztatement[dot]com)
  * @copyright (c) 2026 ztatement
  *
  * @version 1.0.0.2026.06.15
  * @file $Id: public/tz.php $
  * @created $Id: 1 Montag, 15. Juni 2026, 07:02:11 GMT+0200Z ztatement $
  *
  * @repository https://github.com/ztatement/timezone
  * @license MIT (https://opensource.org/license/MIT)
  * @see LICENSE
  */

/**
  * Aufrufe:
  * https://zeitzone.apis.demo-seite.com/timezones
  * https://zeitzone.apis.demo-seite.com/timezones/all
  * https://zeitzone.apis.demo-seite.com/transitions?tz=Europe/Berlin&limit=5
  * https://zeitzone.apis.demo-seite.com/convert?from=Europe/Berlin&to=Asia/Tokyo&time=2024-01-01T12:00
  */

  require __DIR__ . "/../autoload.php";
  $config = require __DIR__ . "/../config.php";

  // --- CORS-Header hinzufügen ---
  if ($config['cors_enabled'] ?? true) {

    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    $whitelist = $config['cors_whitelist'] ?? ['*'];

    // Dynamische Prüfung gegen die Whitelist
    if (in_array('*', $whitelist)) {

        header("Access-Control-Allow-Origin: *");
    } elseif (!empty($origin) && in_array($origin, $whitelist)) {

        header("Access-Control-Allow-Origin: " . $origin);
        header("Vary: Origin"); // Wichtig, wenn der Origin-Header dynamisch gesetzt wird
    }

    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, X-API-KEY");
    header("Access-Control-Max-Age: 86400"); // Cache preflight requests for 24 hours

    // Handle preflight OPTIONS requests
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {

        http_response_code(200);
        exit();
    }
  }
  // --- Ende CORS-Header ---

  use ZET\Controllers\TimezoneController;

/**
  * Prüft den API-Key, falls dieser in der Konfiguration aktiviert ist.
  *
  * @param array $config Die Anwendungskonfiguration.
  */
  function checkApiKey(array $config): void
  {
    if (!$config["api_key_enabled"]) {

      return;
    }

    // Wir prüfen zuerst den Header 'X-API-KEY' und erst dann den URL-Parameter
    $key = $_SERVER["HTTP_X_API_KEY"] ?? ($_GET["key"] ?? null);

    if ($key !== $config["api_key"]) {

      http_response_code(401);
      header("Content-Type: application/json");
      echo json_encode(["error" => "Invalid or missing API key"]);
      exit();
    }
  }

  checkApiKey($config);

  $controller = new TimezoneController();

  // Robuste Pfad-Erkennung
  // Dank der .htaccess-Änderung (tz.php/$1) ist PATH_INFO nun gefüllt
  $path = $_SERVER['PATH_INFO'] ?? '/';

  // Slashes normalisieren (immer mit / beginnen, kein / am Ende)
  $path = '/' . ltrim(rtrim($path, '/'), '/');

  switch ($path) {

    case "/timezones":
      $controller->listCommon();
      break;

    case "/timezones/all":
      $controller->listAll();
      break;

    case "/transitions":
      $controller->transitions();
      break;

    case "/convert":
      $controller->convert();
      break;

    case "/openapi.json":
      checkApiKey($config);
      header("Content-Type: application/json");
      readfile(__DIR__ . "/openapi.json");
      break;

    default:
      http_response_code(404);
      header("Content-Type: application/json");
      echo json_encode(["error" => "Not found"]);
  }
