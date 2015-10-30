<?php
/**
 * Author: Kulikov Roman
 * Email: flinnraider@yandex.ru
 */
require_once __DIR__ . '/../../vendor/autoload.php';

$config = require __DIR__ . '/config.php';

//@formatter:off
define( 'FOTKI_API_LOGIN',                    (string)$config['login'],                 true );
define( 'FOTKI_API_OAUTH_TOKEN',              (string)$config['oauthToken'],            true );
define( 'FOTKI_API_DELETE_STUFF_AFTER_TESTS', (string)$config['deleteStuffAfterTests'], true );
define( 'FOTKI_API_ASSETS',                           __DIR__ . '/../assets',           true );
//@formatter:on