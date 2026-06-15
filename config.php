<?php

/**
  * Zentrale Konfiguration für die Timezone API.
  * 
  * @author Thomas Boettcher @ztatement (github[at]ztatement[dot]com)
  * @copyright (c) 2026 ztatement
  *
  * @version 1.0.0.2026.06.15
  * @file $Id: config.php $
  * @created $Id: 1 Montag, 15. Juni 2026, 06:41:41 GMT+0200Z ztatement $
  *
  * @repository https://github.com/ztatement/timezone
  * @license MIT (https://opensource.org/license/MIT)
  * @see LICENSE
  */

  return [
    'api_key_enabled' => false,
    'api_key' => 'MEIN-GEHEIMER-SCHLUESSEL-123',
    'cors_enabled' => true,                       // CORS-Unterstützung aktivieren/deaktivieren
    'cors_whitelist' => ['*'],                   // Erlaubte Domains (z.B. ['https://meine-app.de']) oder ['*'] für alle
  ];
