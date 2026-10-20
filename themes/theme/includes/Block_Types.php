<?php

namespace theme\includes;

use theme\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Block_Types {

    public static function register() {
        if ( ! function_exists( 'lazyblocks' ) ) {
            return;
        }

        add_action( 'init',                 array( static::class, 'add_blocks' ) );
        add_action( 'enqueue_block_assets', array( static::class, 'enqueue_blocks_assets' ) );
        add_filter( 'block_categories_all', array( static::class, 'register_blocks_categories' ) );
    }

    public static function add_blocks() {
        $blocks = array_filter( glob( Theme::alias( '@root/blocks/*' ) ), 'is_dir' );

        if ( empty( $blocks ) ) {
            return;
        }

        foreach ( $blocks as $block ) {
            $lazy_block = explode( '/', $block );
            $lazy_block = end( $lazy_block );

            static::register_block( $lazy_block );
        }
    }

    public static function enqueue_blocks_assets() {
        if ( ! is_admin() ) {
            return;
        }

        $version = wp_get_theme()->get( 'Version' );

        wp_enqueue_style( 'editor', Theme::alias( '@web/assets/css/editor.css' ), array(), $version );
        wp_enqueue_style( 'main', Theme::alias( '@web/assets/css/main.css' ), array(), $version );
    }

    public static function register_blocks_categories( $categories = array() ) {
        array_unshift( $categories, array(
            'slug'  => 'theme',
            'title' => __( 'Theme', 'theme' ),
            'icon'  => null,
        ) );

        return $categories;
    }

    public static function register_block( $name ) {
        $block_config = require Theme::alias( sprintf( '@root/blocks/%s/config.php', $name ) );

        lazyblocks()->add_block( static::normalize_block( $name, $block_config ) );
    }

    protected static function normalize_block( $name, $config ) {
        $config = static::merge( static::block_defaults( $name ), $config );

        foreach ( $config['controls'] as $key => $control ) {
            $config['controls'][ $key ] = static::merge( static::control_defaults( $key ), $control );
        }

        return $config;
    }

    protected static function block_defaults( $name ) {
        return array(
            'id'          => 0,
            'title'       => '',
            'icon'        => '',
            'keywords'    => array(),
            'slug'        => str_replace( 'lazyblock-', 'lazyblock/', $name ),
            'description' => '',
            'category'    => 'theme',
            'supports' => array(
                'customClassName' => true,
                'anchor'    => true,
                'align'     => array( 'wide', 'full' ),
                'html'      => false,
                'multiple'  => true,
                'inserter'  => true,
            ),
            'ghostkit' => array(
                'supports' => array(
                    'spacings'      => false,
                    'display'       => false,
                    'scrollReveal'  => false,
                    'frame'         => false,
                    'customCSS'     => false,
                ),
            ),
            'controls' => array(),
            'code' => array(
                'output_method'     => 'template',
                'editor_html'       => '',
                'editor_callback'   => '',
                'editor_css'        => '',
                'frontend_html'     => '',
                'frontend_callback' => '',
                'frontend_css'      => '',
                'show_preview'      => true,
                'single_output'     => false,
            ),
            'condition' => array(),
            'block_style_in_editor' => 'native',
        );
    }

    protected static function control_defaults( $key ) {
        return array(
            'type'                  => 'text',
            'name'                  => str_replace( 'control_', '', $key ),
            'default'               => '',
            'label'                 => '',
            'help'                  => '',
            'child_of'              => '',
            'placement'             => 'inspector',
            'width'                 => '100',
            'hide_if_not_selected'  => 'false',
            'save_in_meta'          => 'false',
            'save_in_meta_name'     => '',
            'required'              => 'false',
            'placeholder'           => '',
            'characters_limit'      => '',
        );
    }

    protected static function merge( $defaults, $config ) {
        foreach ( $config as $key => $value ) {
            $is_assoc_pair = is_array( $value )
                && isset( $defaults[ $key ] )
                && is_array( $defaults[ $key ] )
                && array_keys( $defaults[ $key ] ) !== range( 0, count( $defaults[ $key ] ) - 1 );

            if ( $is_assoc_pair ) {
                $defaults[ $key ] = static::merge( $defaults[ $key ], $value );
            } else {
                $defaults[ $key ] = $value;
            }
        }

        return $defaults;
    }
}
