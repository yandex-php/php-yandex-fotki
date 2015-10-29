<?php
/**
 * Author: Kulikov Roman
 * Email: flinnraider@yandex.ru
 */
require_once __DIR__ . '/../../vendor/autoload.php';

$config = require __DIR__ . '/config.php';

define( 'FOTKI_API_LOGIN', $config['login'], true );
define( 'FOTKI_API_OAUTH_TOKEN', $config['oauthToken'], true );
define( 'FOTKI_API_ASSETS', __DIR__ . '/../assets' );