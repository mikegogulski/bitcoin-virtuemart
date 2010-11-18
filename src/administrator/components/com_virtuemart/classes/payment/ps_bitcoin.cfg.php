<?php
if (!defined('_VALID_MOS') && !defined('_JEXEC')) die('Shoo!');

define('BITCOIN_SCHEME', 'https');
define('BITCOIN_CERTIFICATE', '/www/server.cert');
define('BITCOIN_USERNAME', 'wordpress');
define('BITCOIN_PASSWORD', 'shoppy');
define('BITCOIN_HOST', 'localhost');
define('BITCOIN_PORT', '8332');
define('BITCOIN_TIMEOUT', '1');
define('BITCOIN_CONFIRMS', '1');
define('BITCOIN_CRON_SECRET', 'db1eca763ed1dc0ce887ff3355bdf0f8');
define('BITCOIN_VERIFIED_STATUS', 'C');
define('BITCOIN_PENDING_STATUS', 'P');
define('BITCOIN_INVALID_STATUS', 'X');
?>