<?php
    if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

    if ( ! empty( $page->page_title ) ) {
?>
<h2 class="affilizz-page__title">
    <?php if ( ! empty( $page->icon ) && strpos( $page->icon, '/' ) === false ) { ?>
    <span class="dashicons <?php echo esc_attr( $page->icon ); ?>"></span>
    <?php } elseif ( ! empty( $page->icon ) ) { ?>
    <img src="<?php echo esc_url( add_query_arg( array( 'color' => '000' ), $page->icon ) ); ?>" alt="<?php echo esc_attr( $page->page_title ); ?>">
    <?php } ?>

    <?php echo esc_html( $page->page_title ); ?>
</h2>
<?php } ?>

<?php if ( ! empty( $page->description ) ) { ?>
<p class="affilizz-page__description"><?php echo esc_html( $page->description ); ?></p>
<?php } ?>