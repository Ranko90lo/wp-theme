<?php

/**
 * @var array $attributes
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

?>

<div class="block block-image-text">
    <div class="container">
        <div class="row align-items-center">
            <?php if ( ! empty( $attributes['image']['id'] ) ) : ?>
            <div class="col-lg-6">
                <?php echo wp_get_attachment_image( $attributes['image']['id'], 'full', false, array(
                    'class' => 'img-fluid',
                ) ) ?>
            </div>
            <?php endif; ?>

            <div class="col-lg-6">
                <?php if ( ! empty( $attributes['heading'] ) ) : ?>
                <h2 class="mb-3">
                    <?php echo esc_html( $attributes['heading'] ); ?>
                </h2>
                <?php endif; ?>

                <?php if ( ! empty( $attributes['content'] ) ) : ?>
                <div class="d-block mb-4">
                    <?php echo wp_kses_post( $attributes['content'] ); ?>
                </div>
                <?php endif; ?>

                <?php if ( ! empty( $attributes['link'] ) && ! empty( $attributes['link-text'] ) ) : ?>
                <a href="<?php echo esc_url( $attributes['link'] ); ?>" class="btn btn-primary">
                    <?php echo esc_html( $attributes['link-text'] ); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
