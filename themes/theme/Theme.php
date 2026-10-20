<?php

namespace theme;

use wpmvc\App;

class Theme extends App {

    public static $app;

    public function init() {
        parent::init();

        add_action( 'after_setup_theme',  array( static::class, 'after_setup' ) );
    }

    public static function after_setup() {
        add_theme_support( 'menus' );
        add_theme_support( 'post-thumbnails' );

        register_nav_menus(
            array(
                'main-navigation'   => __( 'Main navigation', 'theme' ),
                'footer-navigation' => __( 'Footer navigation', 'theme' ),
            )
        );
    }
}
