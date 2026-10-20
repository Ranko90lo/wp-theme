<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

while ( have_posts() ) :

    the_post();

    get_template_part( 'content', get_post_type() );

endwhile;

the_posts_pagination( array(
    'prev_text'          => 'prev',
    'next_text'          => 'next',
    'screen_reader_text' => '',
) );
