<?php

/**
  * Zeitzonen Service Klasse
  *
  * @author Thomas Boettcher @ztatement (github[at]ztatement[dot]com)
  * @copyright (c) 2026 ztatement
  *
  * @version 1.0.0.2026.06.15
  * @file $Id: src/Services/TimezoneService.php $
  * @created $Id: 1 Montag, 15. Juni 2026, 06:25:18 GMT+0200Z ztatement $
  *
  * @repository https://github.com/ztatement/timezone
  * @license MIT (https://opensource.org/license/MIT)
  * @see LICENSE
  */

  declare(strict_types=1);

  namespace ZET\Services;

  use DateTimeZone;

/**
  * Service-Klasse für Logik-Operationen rund um Zeitzonen.
  */
  class TimezoneService
  {
/**
  * Liefert eine gefilterte Liste gängiger Zeitzonen (Europa, Amerika, Asien).
  *
  * @return array Assoziatives Array der Zeitzonen.
  */
  public static function getCommonTimezones(): array
  {
    $regions = [
      DateTimeZone::EUROPE,
      DateTimeZone::AMERICA,
      DateTimeZone::ASIA,
    ];

    $timezones = [];
    foreach ($regions as $region) {

      $timezones = array_merge(
        $timezones,
        DateTimeZone::listIdentifiers($region),
      );
    }

    $filtered = [];
    foreach ($timezones as $tz) {

      if (!str_contains($tz, "/")) {

        continue;
      }
      if (str_starts_with($tz, "Etc/")) {

        continue;
      }
      if (str_starts_with($tz, "GMT")) {

        continue;
      }

      $filtered[$tz] = $tz;
    }

    ksort($filtered);
    return $filtered;
  }

/**
  * Liefert alle verfügbaren IANA-Zeitzonen-IDs.
  *
  * @return array Liste aller Zeitzonen.
  */
  public static function getAllTimezones(): array
  {
    return DateTimeZone::listIdentifiers();
  }

}
