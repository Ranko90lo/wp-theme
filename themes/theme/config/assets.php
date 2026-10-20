<?php

return array(
    'css' => array(
        array(
            'handle' => 'main',
            'src'    => '@web/dist/css/main.css',
            'deps'   => array(),
        ),
    ),

    'js' => array(
        array(
            'handle' => 'main',
            'src'    => '@web/dist/js/main.js',
            'deps'   => array( 'jquery' ),
        ),
    ),
);
