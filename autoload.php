<?php

/**
  * Autoloader 
  *
  * @author Thomas Boettcher @ztatement (github[at]ztatement[dot]com)
  * @copyright (c) 2026 ztatement
  *
  * @version 1.0.0.2026.06.15
  * @file $Id: autoload.php $
  * @created $Id: 1 Montag, 15. Juni 2026, 06:26:51 GMT+0200Z ztatement $
  *
  * @repository https://github.com/ztatement/timezone
  * @license MIT (https://opensource.org/license/MIT)
  * @see LICENSE
  */

  // Fehler-Konfiguration für die API
  error_reporting(E_ALL);
  ini_set('display_errors', '0'); // Fehler nicht im Browser/Response ausgeben
  ini_set('log_errors', '1');     // Fehler in eine Datei schreiben

  // Log-Verzeichnis erstellen, falls nicht vorhanden
  $logDir = __DIR__ . '/.logs';
  if (!is_dir($logDir)) {
    // Versuche, das Verzeichnis zu erstellen
    if (!mkdir($logDir, 0777, true)) {
      // Wenn die Erstellung fehlschlägt, logge den Fehler in die System-Log
      error_log("Failed to create log directory: " . $logDir);
    }
  }
  // Pfad zur Log-Datei setzen
  ini_set('error_log', $logDir . '/php_error.log');

  // --- Custom Error Handling für API ---

/**
  * Sendet eine standardisierte JSON-Fehlerantwort und beendet das Skript.
  *
  * @param string $message Die Fehlermeldung.
  * @param int $statusCode Der HTTP-Statuscode (Standard: 500).
  * @param array $details Optionale zusätzliche Fehlerdetails.
  * @return void
  */
  function sendJsonError(string $message, int $statusCode = 500, array $details = []): void
  {
    // Sicherstellen, dass Header nicht mehrfach gesendet werden
    if (!headers_sent()) {
      http_response_code($statusCode);
      header("Content-Type: application/json");
    }

    $response = ["error" => $message];
    if (!empty($details)) {
      $response["details"] = $details;
    }
    echo json_encode($response, JSON_PRETTY_PRINT);
    exit(); // Skript nach dem Senden des Fehlers beenden
  }

/**
  * Globaler Exception Handler für alle unbehandelten Ausnahmen.
  */
  set_exception_handler(function (\Throwable $exception) {
    // Fehler ins Log schreiben
    error_log("Uncaught Exception: " . $exception->getMessage() . " in " . $exception->getFile() . " on line " . $exception->getLine());

    // JSON-Fehlerantwort senden
    sendJsonError("Internal Server Error", 500, [
      "message" => $exception->getMessage(),
      "file" => $exception->getFile(),
      "line" => $exception->getLine()
    ]);
  });

/**
  * Globaler Error Handler für PHP-Fehler (Warnungen, Hinweise etc.).
  */
  // Dieser Handler loggt Fehler und verhindert, dass PHP's Standard-Fehlerhandler ausgeführt wird.
  // Er sendet KEINE JSON-Antwort direkt, da das Skript möglicherweise noch fortgesetzt werden kann.
  // Fatale Fehler werden von der Shutdown-Funktion abgefangen.
  set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) {
    // Fehler ins Log schreiben
    error_log("PHP Error: [$errno] $errstr in $errfile on line $errline");

    // True zurückgeben, um zu verhindern, dass PHP's interner Fehlerhandler ausgeführt wird
    return true;
  }, E_ALL); // Alle Fehlertypen abfangen

/**
  * Shutdown-Funktion, um fatale Fehler abzufangen, die den Error-Handler umgehen.
  */
  // Diese Funktion wird immer am Ende der Skriptausführung aufgerufen.
  register_shutdown_function(function () {
    $lastError = error_get_last();
    // Prüfen, ob es sich um einen fatalen Fehler handelt, der nicht von set_error_handler abgefangen wurde
    if ($lastError && in_array($lastError['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR, E_RECOVERABLE_ERROR])) {
      // Fehler ins Log schreiben
      error_log("Fatal Error: " . $lastError['message'] . " in " . $lastError['file'] . " on line " . $lastError['line']);

      // JSON-Fehlerantwort senden
      sendJsonError("Fatal Server Error", 500, [
        "message" => $lastError['message'],
        "file" => $lastError['file'],
        "line" => $lastError['line']
      ]);
    }
  });

/**
  * --- Ende Custom Error Handling ---
  */

/**
  * Autoloader für Klassen
  */
  spl_autoload_register(function ($class) {
    $prefix = "ZET\\";
    $baseDir = __DIR__ . "/src/";

    if (str_starts_with($class, $prefix)) {

      $relative = str_replace("\\", "/", substr($class, strlen($prefix)));
      $file = $baseDir . $relative . ".php";

      if (file_exists($file)) {

        require $file;
      }
    }
  });
