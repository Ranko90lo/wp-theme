<?php
/**
 * Control Link
 *
 * @package Lazy Blocks Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lazy_Blocks_Pro_Control_Link class.
 *
 * LazyBlocks_Control - https://github.com/nk-o/lazy-blocks/blob/master/src/controls/_base/index.php
 */
class Lazy_Blocks_Pro_Control_Link extends LazyBlocks_Control {
	/**
	 * Constructor
	 */
	public function __construct() {
		// Control unique name.
		$this->name = 'link';

		// Control icon SVG.
		$this->icon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M14.9997 7.00021H15.9997C18.7611 7.00021 20.9995 9.23875 20.9995 12.0001C20.9995 14.7615 18.7611 17 15.9997 17H14.9997" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/><path d="M8.99988 16.9998H7.9999C5.23853 16.9998 3 14.7613 3 11.9999C2.99999 9.23853 5.23853 7 7.9999 7H8.99988" stroke="currentColor" stroke-width="1.5" stroke-linecap="square"/><path d="M8.99989 12.1565H14.9998" stroke="currentColor" stroke-width="1.5"/></svg>';

		// Control value type [string, number, boolean, array].
		$this->type = 'string';

		// Control label.
		$this->label = __( 'Link', 'lazy-blocks' );

		// Category name [basic, content, choice, advanced, layout].
		$this->category = 'advanced';

		// Add/remove some options from control settings.
		$this->restrictions = array(
			'default_settings'   => false,
			'placement_settings' => array( 'content', 'inspector' ),
		);

		// Optional additional attributes, that will be saved in control data.
		$this->attributes = array(
			'link_suggestions'              => '',
			'show_initial_link_suggestions' => 'false',
			'rich_preview'                  => 'false',
			'show_title_field'              => 'true',
			'show_target_field'             => 'true',
			'show_nofollow_field'           => 'true',
			'show_classes_field'            => 'true',
		);

		// Filters.
		add_filter( 'lzb/prepare_block_attribute', array( $this, 'filter_lzb_prepare_block_attribute' ), 10, 2 );

		parent::__construct();
	}

	/**
	 * Register control assets.
	 */
	public function register_assets() {
		Lazy_Blocks_Pro_Assets::register_script( 'lzb-pro-control-link', 'build/control-link' );
		Lazy_Blocks_Pro_Assets::register_style( 'lzb-pro-control-link', 'build/control-link' );
		wp_style_add_data( 'lzb-pro-control-link', 'rtl', 'replace' );
	}

	/**
	 * Enqueue control scripts.
	 *
	 * @return array script dependencies.
	 */
	public function get_script_depends() {
		return array( 'lzb-pro-control-link' );
	}

	/**
	 * Enqueue control style.
	 *
	 * @return array style dependencies.
	 */
	public function get_style_depends() {
		return array( 'lzb-pro-control-link' );
	}

	/**
	 * Filter block attribute.
	 *
	 * @param array $attribute_data - attribute data.
	 * @param array $control - control data.
	 *
	 * @return array filtered attribute data.
	 */
	public function filter_lzb_prepare_block_attribute( $attribute_data, $control ) {
		if (
			! $control ||
			! isset( $control['type'] ) ||
			$this->name !== $control['type']
		) {
			return $attribute_data;
		}

		// Link control stores data as an object/array.
		$attribute_data['type']    = 'object';
		$attribute_data['default'] = array(
			'url'           => '',
			'title'         => '',
			'opensInNewTab' => false,
			'nofollow'      => false,
			'cssClasses'    => '',
		);

		return $attribute_data;
	}

	/**
	 * Change control output to array.
	 *
	 * @param mixed  $value - control value.
	 * @param array  $control_data - control data.
	 * @param array  $block_data - block data.
	 * @param string $context - block render context.
	 *
	 * @return array filtered control value.
	 */
	// phpcs:ignore
	public function filter_control_value( $value, $control_data, $block_data, $context ) {
		if ( ! is_string( $value ) && ! is_array( $value ) ) {
			return $value;
		}

		// Maybe decode if it's a JSON string.
		if ( is_string( $value ) ) {
			$decoded = json_decode( rawurldecode( $value ), true );
			if ( is_array( $decoded ) ) {
				$value = $decoded;
			}
		}

		// Ensure all expected keys exist.
		$defaults = array(
			'url'           => '',
			'title'         => '',
			'opensInNewTab' => false,
			'nofollow'      => false,
			'cssClasses'    => '',
		);

		if ( is_array( $value ) ) {
			$value = wp_parse_args( $value, $defaults );
		} else {
			$value = $defaults;
		}

		return $value;
	}
}

// Init.
new Lazy_Blocks_Pro_Control_Link();
