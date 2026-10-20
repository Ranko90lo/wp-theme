<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

return array(
    'title'    => __( 'Image with Text', 'theme' ),
    'icon'     => 'dashicons-align-pull-left',
    'keywords' => array( 'image', 'text', 'media' ),
    'controls' => array(
        'control_image' => array(
            'type'  => 'image',
            'label' => __( 'Image', 'theme' ),
        ),
        'control_heading' => array(
            'label'   => __( 'Heading', 'theme' ),
            'default' => __( 'Your heading here', 'theme' ),
        ),
        'control_content' => array(
            'type'      => 'rich_text',
            'label'     => __( 'Content', 'theme' ),
        ),
        'control_link' => array(
            'type'  => 'url',
            'label' => __( 'Link', 'theme' ),
        ),
        'control_link_text' => array(
            'label'   => __( 'Link text', 'theme' ),
            'default' => __( 'Learn more', 'theme' ),
        ),
    ),
);
