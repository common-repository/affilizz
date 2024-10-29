<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>
<div class="wrap affilizz-admin-page-wrap">
    <?php
        $page->get_errors();
        $available_submenus = array( 'configuration', 'advanced' );
        $tab_argument       = isset( $_GET['tab'] ) ? esc_attr( sanitize_key( $_GET['tab'] ) ) : '';
        $current_submenu    = ( ! empty( $tab_argument ) && in_array( $tab_argument, $available_submenus ) ? $tab_argument : ( $available_submenus[ 0 ] ?? 'default' ) );

        // Manipulate the output of the standard submit button
        $submit_button = str_replace( '"button ', '"', get_submit_button( __( 'Save changes', 'affilizz' ), 'affilizz-button affilizz-button--primary affilizz-plugin__pane__button affilizz-plugin__pane__button--submit', 'affilizz-save-settings', true ) );
    ?>

    <div class="affilizz-plugin">
        <div class="affilizz-plugin__sidebar">
            <?php include( trailingslashit( AFFILIZZ_DIRECTORY ) . 'templates/administration/page/partial/sidebar.php' ); ?>
        </div>
        <div class="affilizz-plugin__body">
            <form method="post" action="#">
                <?php
                    wp_nonce_field( 'affilizz-save-page-settings' );
                    include( trailingslashit( AFFILIZZ_DIRECTORY ) . 'templates/administration/page/settings/' . $current_submenu . '.php' );
                ?>
            </form>
        </div>
    </div>
</div>