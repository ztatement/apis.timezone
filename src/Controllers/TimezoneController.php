<?php

  declare(strict_types=1);

/**
  * Verarbeitung von zeitzonenbezogenen API-Anfragen
  *
  * @author Thomas Boettcher @ztatement (github[at]ztatement[dot]com)
  * @copyright (c) 2026 ztatement
  *
  * @version 1.0.0.2026.06.15
  * @file $Id: src/Controllers/TimezoneController.php $
  * @created $Id: 1 Montag, 15. Juni 2026, 06:26:08 GMT+0200Z ztatement $
  *
  * @repository https://github.com/ztatement/timezone
  * @license MIT (https://opensource.org/license/MIT)
  * @see LICENSE
  */

  namespace ZET\Controllers;

  use ZET\Services\TimezoneService;

/**
  * Controller für die Verarbeitung von zeitzonenbezogenen API-Anfragen.
  */
class TimezoneController
{
/**
  * Gibt eine Liste gängiger Zeitzonen zurück.
  */
  public function listCommon(): void
  {
    $this->json(TimezoneService::getCommonTimezones());
  }

/**
  * Gibt eine Liste aller verfügbaren IANA-Zeitzonen zurück.
  */
  public function listAll(): void
  {
    $this->json(TimezoneService::getAllTimezones());
  }

/**
  * Berechnet die nächsten Zeitumstellungen (DST) für eine gegebene Zeitzone.
  */
  public function transitions(): void
  {
    $tzName = $_GET["tz"] ?? null;
    $limit = (int)($_GET["limit"] ?? 5);

    if (!$tzName) {
      $this->json(["error" => "Missing 'tz' parameter"], 400);
      return;
    }

    try {
      $tz = new \DateTimeZone($tzName);

      // Wir rufen die Umstellungen ab "jetzt" für die nächsten 10 Jahre ab
      $allTransitions = $tz->getTransitions(time(), time() + (10 * 365 * 24 * 3600)); // 10 Jahre in die Zukunft

      // Prüfen, ob es tatsächlich DST-Wechsel gibt (mehr als nur der initiale Zustand)
      // Ein Array mit nur einem Element (dem aktuellen Zustand) bedeutet keine zukünftigen Wechsel
      if (count($allTransitions) <= 1) {
        $this->json([
          "timezone" => $tzName,
          "transitions" => [],
          "message" => "Diese Zeitzone hat keine zukünftigen Zeitumstellungen (DST)."
        ]);
        return;
      }

      $formattedTransitions = [];
      $previousOffset = null;

      // Die erste Transition in $allTransitions ist der aktuelle Zustand.
      // Wir benötigen ihren Offset, um den Typ der *ersten tatsächlichen* Umstellung zu bestimmen.
      if (!empty($allTransitions)) {
        $firstState = array_shift($allTransitions); // Entfernt das erste Element (aktueller Zustand)
        $previousOffset = $firstState['offset'];
        // Der initiale Zustand selbst hat keine "Art des Wechsels"
      }

      foreach ($allTransitions as $transition) {
        $currentOffset = $transition['offset'];
        $transitionType = "Unbekannt"; // Standardwert, sollte überschrieben werden

        if ($previousOffset !== null) {
          if ($currentOffset > $previousOffset) {
            $transitionType = "Spring Forward"; // Uhr wird vorgestellt (z.B. +1 Stunde)
          } elseif ($currentOffset < $previousOffset) {
            $transitionType = "Fall Back"; // Uhr wird zurückgestellt (z.B. -1 Stunde)
          }
        }

        $transition['type'] = $transitionType;

        // Formatiere den Offset für die aktuelle Transition
        $offsetSeconds = $transition['offset'];
        $transition['offset_formatted'] = sprintf('%+03d:%02d', $offsetSeconds / 3600, abs($offsetSeconds % 3600) / 60);

        $formattedTransitions[] = $transition;
        $previousOffset = $currentOffset; // Für den nächsten Vergleich aktualisieren
      }

      $this->json([ // Hier wurde $transitions zu $formattedTransitions geändert
        "timezone" => $tzName,
        "transitions" => array_slice($formattedTransitions, 0, $limit)
      ]);
    } catch (\Exception $e) {
      $this->json(["error" => "Unknown timezone identifier"], 404);
    }
  }

/**
  * Rechnet eine Zeitangabe von einer Zeitzone in eine andere um.
  */
  public function convert(): void
  {
    $from = $_GET["from"] ?? null;
    $to = $_GET["to"] ?? null;
    $time = $_GET["time"] ?? null;

    if (!$from || !$to || !$time) {

      $this->json(["error" => "Missing parameters"], 400);
      return;
    }

    try {
      // Prüfen ob Zeitzonen existieren, bevor DateTime sie nutzt
      try {
        $tzFrom = new \DateTimeZone($from);
        $tzTo = new \DateTimeZone($to);
      } catch (\Exception $e) {
        $this->json(["error" => "Unknown timezone identifier provided"], 404);
        return;
      }

      $dt = new \DateTime($time, $tzFrom);
      $offsetSecondsFrom = $dt->getOffset();
      $offsetFromHours = $offsetSecondsFrom / 3600;
      $offsetFromFormatted = sprintf('%+03d:%02d', $offsetSecondsFrom / 3600, abs($offsetSecondsFrom % 3600) / 60);

      $inputDate = $dt->format("Y-m-d");

      $dt->setTimezone($tzTo);
      $offsetSecondsTo = $dt->getOffset();
      $offsetToHours = $offsetSecondsTo / 3600;
      $offsetToFormatted = sprintf('%+03d:%02d', $offsetSecondsTo / 3600, abs($offsetSecondsTo % 3600) / 60);

      $outputDate = $dt->format("Y-m-d");

      $this->json([
        "from" => $from,
        "to" => $to,
        "offset_from_hours" => $offsetFromHours,
        "offset_from_formatted" => $offsetFromFormatted,
        "offset_to_hours" => $offsetToHours,
        "offset_to_formatted" => $offsetToFormatted,
        "offset_diff_hours" => $offsetToHours - $offsetFromHours,
        "input" => $time,
        "output" => $dt->format("Y-m-d H:i:s"),
        "output_timezone_abbr" => $dt->format("T"),
        "is_dst" => (bool)$dt->format("I"),
        "dst_label" => $dt->format("I") === "1" ? "Sommerzeit" : "Winterzeit",
        "output_day_of_week" => $dt->format("l"),
        "day_jump" => ($inputDate !== $outputDate)
      ]);
    } catch (\Exception $e) {

      $this->json(["error" => $e->getMessage()], 400);
    }
  }

/**
  * Hilfsmethode zur Ausgabe von Daten im JSON-Format.
  *
  * @param mixed $data Die auszugebenden Daten.
  * @param int $status Der HTTP-Statuscode.
  */
  private function json($data, int $status = 200): void
  {
    http_response_code($status);
    header("Content-Type: application/json");

    // Einheitliche JSON-Struktur für Erfolgsantworten
    $response = ["status" => "success", "data" => $data];

    echo json_encode($response, JSON_PRETTY_PRINT);
  }

}
