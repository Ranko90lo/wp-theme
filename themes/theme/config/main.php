<?php

return array(
    'name'   => 'Theme',
    'domain' => 'theme',

    'aliases' => array(
        '@root' => get_template_directory(),
        '@web'  => get_template_directory_uri(),
        '@home' => home_url(),
    ),

    'bootstrap' => array(
        'view',
    ),

    'components' => array(
        'request' => array(
            'class' => \wpmvc\web\Request::class,
        ),

        'view' => array(
            'class'  => \wpmvc\web\View::class,
            'assets' => require_once __DIR__ . '/assets.php',
        ),
    ),

    'params' => require_once __DIR__ . '/params.php',
);
