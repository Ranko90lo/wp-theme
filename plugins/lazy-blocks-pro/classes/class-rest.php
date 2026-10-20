<?php
/**
 * Rest API functions
 *
 * @package Lazy Blocks
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class LazyBlocksPro_Rest
 */
class LazyBlocksPro_Rest extends WP_REST_Controller {
	/**
	 * Namespace.
	 *
	 * @var string
	 */
	protected $namespace = 'lazy-blocks-pro/v';

	/**
	 * Version.
	 *
	 * @var string
	 */
	protected $version = '1';

	/**
	 * LazyBlocksPro_Rest constructor.
	 */
	public function __construct() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	/**
	 * Register rest routes.
	 */
	public function register_routes() {
		$namespace = $this->namespace . $this->version;

		// Update Lazy Block Data.
		register_rest_route(
			$namespace,
			'/update-collections/',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_collections' ),
				'permission_callback' => array( $this, 'update_collections_permission' ),
			)
		);

		register_rest_route(
			$namespace,
			'/batch-block-render/',
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'batch_block_render' ),
				'permission_callback' => array( $this, 'batch_block_render_permission' ),
			)
		);
	}

	/**
	 * Update collections permissions.
	 *
	 * @return WP_REST_Response|true
	 */
	public function update_collections_permission() {
		if ( current_user_can( 'edit_posts' ) ) {
			return true;
		}

		return $this->error( 'not_allowed', esc_html__( 'Sorry, you are not allowed to update block collections.', 'lazy-blocks' ), true );
	}

	/**
	 * Batch block render permissions.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|true
	 */
	public function batch_block_render_permission( $request ) {
		global $post;

		$post_id = isset( $request['post_id'] ) ? intval( $request['post_id'] ) : 0;

		if ( 0 < $post_id ) {
			// phpcs:ignore
			$post = get_post( $post_id );

			if ( ! $post || ! current_user_can( 'edit_post', $post->ID ) ) {
				return $this->error( 'not_allowed', esc_html__( 'Sorry, you are not allowed to render blocks.', 'lazy-blocks' ), true );
			}
		} elseif ( ! current_user_can( 'edit_posts' ) ) {
			return $this->error( 'not_allowed', esc_html__( 'Sorry, you are not allowed to render blocks.', 'lazy-blocks' ), true );
		}

		return true;
	}

	/**
	 * Update collections.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response
	 */
	public function update_collections( $request ) {
		$collections = isset( $request['collections'] ) ? $request['collections'] : false;

		if ( is_array( $collections ) ) {
			update_option( 'lazy_blocks_pro_block_collections', $collections );
		}

		return $this->success( esc_html__( 'Block data updated successfully.', 'lazy-blocks' ) );
	}

	/**
	 * Render multiple blocks in a single request.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response
	 */
	public function batch_block_render( $request ) {
		$post_id = isset( $request['post_id'] ) ? intval( $request['post_id'] ) : 0;
		$blocks  = isset( $request['blocks'] ) && is_array( $request['blocks'] ) ? $request['blocks'] : array();

		if ( empty( $blocks ) ) {
			return $this->success( array() );
		}

		if ( 0 < $post_id ) {
			// phpcs:ignore
			$post = get_post( $post_id );

			if ( $post ) {
				setup_postdata( $post );
			}
		}

		$results = array();

		foreach ( $blocks as $item ) {
			$block_name       = isset( $item['name'] ) ? $item['name'] : '';
			$block_attributes = isset( $item['attributes'] ) && is_array( $item['attributes'] ) ? $item['attributes'] : array();
			$context          = isset( $item['context'] ) ? $item['context'] : null;
			$block_id         = isset( $block_attributes['blockId'] ) ? $block_attributes['blockId'] : '';

			if ( ! $block_name || ! $block_id ) {
				continue;
			}

			$block = lazyblocks()->blocks()->get_block( $block_name );

			if ( ! $block ) {
				continue;
			}

			global $lzb_preview_block_data;
			$lzb_preview_block_data = array(
				'post_id'          => $post_id,
				'render_location'  => 'frontend',
				'block_name'       => $block_name,
				'block_attributes' => $block_attributes,
				'context'          => $context,
			);

			$results[ $block_id ] = lazyblocks()->blocks()->render_callback( $block_attributes, $context, null, 'frontend', $block );
		}

		if ( 0 < $post_id ) {
			wp_reset_postdata();
		}

		return $this->success( $results );
	}

	/**
	 * Success rest.
	 *
	 * @param mixed $response response data.
	 *
	 * @return WP_REST_Response
	 */
	public function success( $response ) {
		return new WP_REST_Response(
			array(
				'success'  => true,
				'response' => $response,
			),
			200
		);
	}

	/**
	 * Error rest.
	 *
	 * @param mixed   $code       error code.
	 * @param mixed   $response   response data.
	 * @param boolean $true_error use true error response to stop the code processing.
	 * @return mixed
	 */
	public function error( $code, $response, $true_error = false ) {
		if ( $true_error ) {
			return new WP_Error( $code, $response, array( 'status' => 401 ) );
		}

		return new WP_REST_Response(
			array(
				'error'      => true,
				'success'    => false,
				'error_code' => $code,
				'message'    => $response,
			),
			401
		);
	}
}
new LazyBlocksPro_Rest();
