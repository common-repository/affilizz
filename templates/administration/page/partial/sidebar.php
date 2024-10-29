<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
	$current_submenu = esc_attr( $current_submenu );
?>
<div id="affilizz-plugin-menu">
	<div class="affilizz-plugin-menu__header">
		<div class="affilizz-plugin-menu__header__icon">
			<img src="<?php echo esc_url( AFFILIZZ_URL ); ?>/assets/dist/images/logo/logo-type-dark-blue.svg" alt="<?php _e( 'Affilizz logo', 'affilizz' ); ?>" />
		</div>
		<div class="affilizz-plugin-menu__header__title">
			<?php _e( 'Affilizz', 'affilizz' ); ?>
		</div>
		<div class="affilizz-plugin-menu__header__description">
			<?php _e( 'Plugin settings', 'affilizz' ); ?>
		</div>
	</div>

	<a href="<?php echo esc_url( add_query_arg( 'tab', 'configuration' ) ); ?>" id="affilizz-menu-configuration" class="affilizz-plugin-menu__item <?php echo ( $current_submenu == 'configuration' ? 'affilizz-plugin-menu__item--active' : '' ); ?>">
		<div class="affilizz-plugin-menu__item__title"><?php _e( 'Settings', 'affilizz' ); ?></div>
		<div class="affilizz-plugin-menu__item__description"><?php _e( 'General plugin setup', 'affilizz' ); ?></div>
		<div class="affilizz-plugin-menu__item__icon"><img src="<?php echo esc_url( AFFILIZZ_URL ); ?>/assets/dist/images/icon/configuration-knobs.svg" alt="<?php _e( 'General plugin setup', 'affilizz' ); ?>" /></div>
	</a>
	<a href="<?php echo esc_url( add_query_arg( 'tab', 'advanced' ) ); ?>" id="affilizz-menu-advanced" class="affilizz-plugin-menu__item <?php echo ( $current_submenu == 'advanced' ? 'affilizz-plugin-menu__item--active' : '' ); ?>">
		<div class="affilizz-plugin-menu__item__title"><?php _e( 'Advanced settings', 'affilizz' ); ?></div>
		<div class="affilizz-plugin-menu__item__description"><?php _ex( 'Rendering, maintenance', 'Settings page', 'affilizz' ); ?>&hellip;</div>
		<div class="affilizz-plugin-menu__item__icon"><img src="<?php echo esc_url( AFFILIZZ_URL ); ?>/assets/dist/images/icon/settings-cog.svg" alt="<?php _e( 'Advanced settings', 'affilizz' ); ?>" /></div>
	</a>
</div>