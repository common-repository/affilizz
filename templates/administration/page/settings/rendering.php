<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    $rendering_modes = array(
        'webcomponent' => array(
            'title'       => __( 'Web component (default)', 'affilizz' ),
            'description' => sprintf( __( '%1$sRecommended%2$s - Your affiliate content is loaded after the page.', 'affilizz' ), '<em>', '</em>' ) . ' ' . __( 'There is no conflict possible with your CSS&hellip;', 'affilizz' )
        ), 'ssr' => array(
            'title'       => __( 'Server-side rendering (SSR)', 'affilizz' ),
            'description' => __( 'Your affiliate content is loaded alongside the page. There could be conflicts with your CSS.', 'affilizz' )
        )
    );
    $current_rendering_mode = get_option( 'affilizz_rendering_mode' );
    if ( ! $current_rendering_mode || ! in_array( $current_rendering_mode, array_keys( $rendering_modes ) ) ) {
        $current_rendering_mode = AFFILIZZ_DEFAULT_RENDERING_MODE ?? ( array_keys( $rendering_modes ) )[ 0 ];
    }
?>
<h4 class="affilizz-plugin__pane__section__title">
    <span><?php _e( 'Rendering mode', 'affilizz' ); ?>
</h4>
<div class="affilizz-field radio">
    <?php foreach ( $rendering_modes as $key => $values ) { ?>
    <div class="affilizz-field-main">
        <label for="affilizz-rendering-mode-<?php echo esc_attr( $key ); ?>">
            <input type="radio" id="affilizz-rendering-mode-<?php echo esc_attr( $key ); ?>" name="affilizz-rendering-mode" value="<?php echo esc_attr( $key ); ?>"  <?php checked( $key, $current_rendering_mode ); ?> />
            <span class="affilizz-field__label">
                <?php if ( ! empty( $values['title'] ) ) { echo wp_kses( $values['title'], \Affilizz\Core::get_extended_allowed_tags() ); } ?>
                <?php if ( ! empty( $values['description'] ) ) { ?><small><?php echo wp_kses( $values['description'], \Affilizz\Core::get_extended_allowed_tags() ); ?></small><?php } ?>
            </span>
        </label>
    </div>
    <?php } ?>
</div>