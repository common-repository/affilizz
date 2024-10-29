<?php
namespace Affilizz\Editor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Classic {
	// Declare the traits used by this class
	use \Affilizz\Util\Template, \Affilizz\Util\Generic;

	/**
	 * Hooks to the default WordPress hooks to enable custom features.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function __construct() {
		// Add a custom button next to the default "Add a media" button in the classic editor
		add_action( 'media_buttons', array( $this, 'media_buttons' ) );
		add_action( 'print_media_templates', array( $this, 'print_media_templates' ) );
		add_action( 'mce_external_plugins', array( $this, 'mce_external_plugins' ) );
	}

	/**
	 * Adds a custom button next to the default "Add a media" button in the classic editor
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function media_buttons() {
		echo self::render_template_part_static( trailingslashit( esc_url( AFFILIZZ_DIRECTORY ) ) . 'templates/administration/editor/classic/button' );
	}

	/**
	 * Hooked on print_media_templates. Rendering the inline shortcode block inside the MCE edition frame.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function print_media_templates() {
		// Exclude Divi
		if ( ! empty( $_GET[ 'et_fb' ] ) ) {
			return;
		}

		// Deal with other plugins that may not have the same load cycle
		if ( ! function_exists( 'get_current_screen' ) ) {
			require_once ABSPATH . '/wp-admin/includes/screen.php';
		}

		// Exclude other non-classic-editor situations
		if ( ! isset( \get_current_screen()->id ) || \get_current_screen()->base !== 'post' ) {
			return;
		}

		$shortcode_instance = $this;
		echo self::render_template_part_static( trailingslashit( esc_url( AFFILIZZ_DIRECTORY ) ) . 'templates/administration/editor/classic/block' );
	}

	/**
	 * Registers a custom TinyMCE plugin to display a floating panel.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @param array $external_plugins The current list of external plugins.
	 * @return array The updated list of external plugins.
	 */
	public function mce_external_plugins( $external_plugins = array() ) {
		if ( ! is_array( $external_plugins ) ) {
			$external_plugins = array();
		}

		$external_plugins[ 'affilizz-float' ] = trailingslashit( esc_url( AFFILIZZ_URL ) ) . 'assets/dist/js/float.js?ver=' . AFFILIZZ_ASSETS_BUILD_VERSION;
		return $external_plugins;
	}
}