<?php

namespace theme\includes;

use theme\Theme;

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class Html {

    public static function get_icon( $name, $width = 16, $height = 16 ) {
        $svg_file_url = Theme::alias( '@web/dist/icons/sprite.svg' );

        if ( $modified_time = @filemtime( Theme::alias( '@root/dist/icons/sprite.svg' ) ) ) {
            $svg_file_url = add_query_arg( 'ver', $modified_time, $svg_file_url );
        }

        ob_start(); ?>

        <svg class="icon icon--<?php echo esc_attr( $name ); ?>" width="<?php echo esc_attr( absint( $width) ) ?>"
             height="<?php echo esc_attr( absint( $height) ) ?>">

            <use href="<?php echo esc_url( $svg_file_url ); ?>#icon-<?php echo esc_attr( $name ); ?>"></use>
        </svg>

        <?php
        return ob_get_clean();
    }

    public static function the_content( $post = 0 ) {
        $content = get_the_content( null, false, $post );

        $content = apply_filters( 'the_content', $content );
        $content = str_replace( ']]>', ']]&gt;', $content );

        return $content;
    }

}
