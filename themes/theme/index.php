<?php get_header(); ?>

<main id="main" class="site-main">
    <?php if ( have_posts() ) :
        while ( have_posts() ) :
            the_post();

            get_template_part( 'content', get_post_format() );

        endwhile;

        the_posts_pagination(
            array(
                'prev_text'          => __( 'Previous page' ),
                'next_text'          => __( 'Next page' ),
            )
        );
    else :
        get_template_part( 'content', 'none' );
    endif;
    ?>
</main>

<?php get_footer(); ?>
