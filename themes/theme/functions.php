<?php

namespace theme;

use theme\includes\Block_Types;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once __DIR__ . '/vendor/autoload.php';

$config = require_once __DIR__ . '/config/main.php';

( new Theme( $config ) )->init();

Block_Types::register();
