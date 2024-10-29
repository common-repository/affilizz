<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    $errors = $page->errors ?? array();

    if ( ! empty( $errors ) ) {
        foreach ( $errors as $error ) {
?>
<div class="notification notification-error">
    <p><?php echo wp_kses_post( $error ); ?></p>
</div>
<?php
        }
    }
?>