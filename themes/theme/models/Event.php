<?php

namespace theme\models;

use wpmvc\models\Post_Model;
use theme\controllers\Event_Options_Controller;

class Event extends Post_Model {

    /**
     * Defines custom post type.
     *
     * @var string
     */
    public $post_type = 'event';

    /**
     * Custom attributes.
     */
    public $city;
    public $date;

    /**
     * If registry is defined, the custom post type will be registered.
     *
     * @return array
     */
    public function registry() : array {
        return array(
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'show_in_rest'       => true,
            'query_var'          => true,
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
        );
    }

    /**
     * Custom post type lables for the registry.
     *
     * @return array
     */
    public function registry_labels() : array {
        return array(
            'name' => __( 'Event' ),
        );
    }

    /**
     * Labels for the attributes.
     *
     * @return array
     */
    public function attribute_labels(): array {
        return array(
            'city' => __( 'Event City' ),
            'date' => __( 'Event Date' ),
        );
    }

    /**
     * Meta boxes are wired automatically by register().
     *
     * @return array
     */
    protected function meta_boxes() : array {
        return array(
            array( Event_Options_Controller::class, __( 'Options' ) ),
        );
    }

    /**
     * Helper function for the select boxes.
     *
     * @return array
     */
    public static function cities() : array {
        return array(
            'belgrade'   => __( 'Belgrade' ),
            'bratislava' => __( 'Bratislava' ),
        );
    }

}
