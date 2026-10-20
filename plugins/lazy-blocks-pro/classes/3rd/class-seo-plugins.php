<?php
/**
 * Support for Rank Math SEO and Yoast SEO plugins.
 *
 * @package Lazy Blocks Pro
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Lazy_Blocks_Pro_3rd_SEO_Plugins class.
 */
class Lazy_Blocks_Pro_3rd_SEO_Plugins {
	/**
	 * Constructor
	 */
	public function __construct() {
		// Actions.
		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_assets' ) );

		// Extend the preload cache with frontend HTML using flat keys:
		// '<blockId>' (editor) and '<blockId>-frontend' (frontend)
		// Run after the basic preload and request 5 args.
		add_filter( 'lzb/block_render/output', array( $this, 'extend_preloaded_cache' ), 201, 6 );
	}

	/**
	 * Enqueue assets.
	 */
	public function enqueue_assets() {
		if ( ! defined( 'WPSEO_VERSION' ) && ! class_exists( 'RankMath' ) ) {
			return;
		}

		Lazy_Blocks_Pro_Assets::enqueue_script( 'lzb-pro-3rd-seo-plugins', 'build/3rd-seo-plugins' );
	}

	/**
	 * Upgrade the global preload cache to flat keys and add frontend HTML.
	 *
	 * @param string     $output          Block output for this render.
	 * @param array      $attributes      Block attributes.
	 * @param string     $render_location 'editor' or 'frontend'.
	 * @param array|null $block           Lazy Blocks block spec if available.
	 * @param mixed      $context         Additional render context.
	 * @param string     $content         The block content.
	 *
	 * @return string
	 */
	public function extend_preloaded_cache( $output, $attributes, $render_location = 'editor', $block = null, $context = null, $content = null ) {
		unset( $block, $context, $content );

		if ( ! defined( 'WPSEO_VERSION' ) && ! class_exists( 'RankMath' ) ) {
			return $output;
		}

		global $lzb_preloaded_blocks;

		if ( empty( $attributes['blockId'] ) ) {
			return $output;
		}

		if ( ! is_array( $lzb_preloaded_blocks ) ) {
			$lzb_preloaded_blocks = array();
		}

		$block_id     = $attributes['blockId'];
		$frontend_key = $block_id . '-frontend';

		if ( 'editor' === $render_location ) {
			// Ensure editor HTML stored at "<blockId>".
			if ( ! isset( $lzb_preloaded_blocks[ $block_id ] ) ) {
				$lzb_preloaded_blocks[ $block_id ] = $output;
			}
		} else {
			// Frontend render path: store frontend HTML at "<blockId>-frontend" if empty.
			if ( ! isset( $lzb_preloaded_blocks[ $frontend_key ] ) ) {
				$lzb_preloaded_blocks[ $frontend_key ] = $output;
			}
		}

		return $output;
	}
}

// Init.
new Lazy_Blocks_Pro_3rd_SEO_Plugins();
