<?php
/**
 * Control Token Field
 *
 * @package Lazy Blocks Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lazy_Blocks_Pro_Control_Token_Field class.
 *
 * LazyBlocks_Control - https://github.com/nk-o/lazy-blocks/blob/master/src/controls/_base/index.php
 */
class Lazy_Blocks_Pro_Control_Token_Field extends LazyBlocks_Control {
	/**
	 * Constructor
	 */
	public function __construct() {
		// Control unique name.
		$this->name = 'token_field';

		// Control icon SVG.
		$this->icon = '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 7.5C4 6.11929 5.11929 5 6.5 5H17.5C18.8807 5 20 6.11929 20 7.5V16.5C20 17.8807 18.8807 19 17.5 19H6.5C5.11929 19 4 17.8807 4 16.5V7.5Z" stroke="currentColor" stroke-width="1.5"/><rect x="6" y="9" width="4" height="2.5" rx="1" fill="currentColor"/><rect x="11" y="9" width="4" height="2.5" rx="1" fill="currentColor"/><rect x="6" y="13" width="5" height="2.5" rx="1" fill="currentColor"/></svg>';

		// Control value type [string, number, boolean, array].
		$this->type = 'string';

		// Control label.
		$this->label = __( 'Token Field', 'lazy-blocks' );

		// Category name [basic, content, choice, advanced, layout].
		$this->category = 'choice';

		// Add/remove some options from control settings.
		$this->restrictions = array(
			'default_settings' => false,
		);

		// Optional additional attributes, that will be saved in control data.
		$this->attributes = array(
			'choices'       => array(),
			'multiple'      => 'true',
			'allow_custom'  => 'false',
			'output_format' => '',
		);

		// Filters.
		add_filter( 'lzb/prepare_block_attribute', array( $this, 'filter_lzb_prepare_block_attribute' ), 10, 2 );

		parent::__construct();
	}

	/**
	 * Register control assets.
	 */
	public function register_assets() {
		Lazy_Blocks_Pro_Assets::register_script( 'lzb-pro-control-token-field', 'build/control-token-field' );
	}

	/**
	 * Enqueue control scripts.
	 *
	 * @return array script dependencies.
	 */
	public function get_script_depends() {
		return array( 'lzb-pro-control-token-field' );
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
			$this->name !== $control['type'] ||
			! isset( $control['multiple'] )
		) {
			return $attribute_data;
		}

		if ( 'true' === $control['multiple'] ) {
			$attribute_data['type']    = 'array';
			$attribute_data['items']   = array( 'type' => 'string' );
			$attribute_data['default'] = array();
		}

		return $attribute_data;
	}

	/**
	 * Get choice data by control value.
	 *
	 * @param string $value - attribute value.
	 * @param array  $control - control data.
	 *
	 * @return array
	 */
	public function get_choice_data_by_value( $value, $control ) {
		$choice_data = array(
			'value' => $value,
			'label' => $value,
		);

		if ( isset( $control['choices'] ) && is_array( $control['choices'] ) ) {
			foreach ( $control['choices'] as $choice ) {
				if ( $value === $choice['value'] ) {
					$choice_data = $choice;
				}
			}
		}

		return $choice_data;
	}

	/**
	 * Get new attribute value.
	 *
	 * @param string $value - attribute value.
	 * @param array  $control - control data.
	 *
	 * @return array
	 */
	public function get_new_attribute_value( $value, $control ) {
		if ( isset( $control['multiple'] ) && 'true' === $control['multiple'] ) {
			if ( is_array( $value ) ) {
				foreach ( $value as $k => $item ) {
					$choice_data = $this->get_choice_data_by_value( $value[ $k ], $control );

					if ( 'label' === $control['output_format'] ) {
						$value[ $k ] = $choice_data['label'];
					} elseif ( 'array' === $control['output_format'] ) {
						$value[ $k ] = $choice_data;
					}
				}
			}
		} else {
			$choice_data = $this->get_choice_data_by_value( $value, $control );

			if ( 'label' === $control['output_format'] ) {
				$value = $choice_data['label'];
			} elseif ( 'array' === $control['output_format'] ) {
				$value = $choice_data;
			}
		}

		return $value;
	}

	/**
	 * Change control output to array.
	 *
	 * @param mixed  $value - control value.
	 * @param array  $control_data - control data.
	 * @param array  $block_data - block data.
	 * @param string $context - block render context.
	 *
	 * @return string|array filtered control value.
	 */
	// phpcs:ignore
	public function filter_control_value( $value, $control_data, $block_data, $context ) {
		if ( ! isset( $control_data['output_format'] ) || ! $control_data['output_format'] ) {
			return $value;
		}

		return $this->get_new_attribute_value( $value, $control_data );
	}
}

// Init.
new Lazy_Blocks_Pro_Control_Token_Field();
