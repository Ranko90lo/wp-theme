<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

?>

<footer>
    <nav class="navbar navbar-expand-sm navbar-dark p-0">
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <?php
            wp_nav_menu(
                array(
                    'theme_location'  => 'footer-navigation',
                    'depth'           => 2,
                    'container_class' => '',
                    'container_id'    => '',
                    'menu_class'      => 'navbar-nav',
                    'fallback_cb'     => 'WP_Bootstrap_Navwalker::fallback',
                    'walker'          => new \theme\includes\WP_Bootstrap_Navwalker(),
                )
            );
            ?>
        </div>
    </nav>
</footer>

<?php wp_footer(); ?>

</body>
</html>

