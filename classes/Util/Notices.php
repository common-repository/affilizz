<?php
namespace Affilizz\Util;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Notices {
	// Declare the traits used by the class
	use \Affilizz\Util\Generic;

	/**
	 * This class is in charge of displaying the administration notices.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function __construct() {
        // Clean up a bit the top of the admin pages when notices exist
        add_action( 'admin_enqueue_scripts', [ $this, 'clean_administration_notices' ] );

        // Deal with the missing media states notices
		add_action( 'all_admin_notices', [ $this, 'display_missing_channel_notice' ] );
        add_action( 'all_admin_notices', [ $this, 'display_missing_media_notice' ] );
	}

    /**
     * Cleans up the administration notices by adding custom CSS to the page.
     * @author Affilizz <wordpress@affilizz.com>
     * @since 1.14.0
     * @return void
     */
    public function clean_administration_notices() {
        wp_add_inline_style( 'affilizz-admin', ':not(.notice) + .notice { margin-top: 20px; }' );
    }

    /**
     * Wraps the given content in a notice container.
     * @author Romain Carlier <romain@reaklab.com>
     * @since 1.14.0
     * @param string $content The content to wrap.
     * @return string The wrapped content.
     */
    public function wrap_single_line_notice( $notice_class = '', $content = '' ) {
        return wp_kses( '<div class="notice affilizz__notice affilizz__notice--' . $notice_class . ' is-dismissible"><p>' . $content . '</p></div>' . PHP_EOL, \Affilizz\Core::get_extended_allowed_tags() );
    }

	/**
	 * Displays an error if there is a missing information.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function display_missing_channel_notice() {
		$missing_channel = get_transient( 'affilizz_missing_channel' );
		if ( false !== $missing_channel ) {
            echo $this->wrap_single_line_notice( 'warning', wp_sprintf(
                __( '%1$sWarning%2$s : Your Affilizz %1$schannel%2$s seems to be inexistant or wrongly defined. Please %3$sre-execute our wizard%4$s to redefine these values.', 'affilizz' ),
                '<strong>', '</strong>', '<a href="' . esc_url( admin_url( 'admin.php?page=affilizz-wizard' ) ) . '">', '</a>'
            ) );
        }
	}

    /**
	 * Displays an error if there is a missing information.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function display_missing_media_notice() {
		$missing_media = get_transient( 'affilizz_missing_media' );
		if ( false !== $missing_media ) {
            echo $this->wrap_single_line_notice( 'warning', wp_sprintf(
                __( '%1$sWarning%2$s : Your Affilizz %1$smedia%2$s seems to be inexistant or wrongly defined. Please %3$sre-execute our wizard%4$s to redefine these values.', 'affilizz' ),
                '<strong>', '</strong>', '<a href="' . esc_url( admin_url( 'admin.php?page=affilizz-wizard' ) ) . '">', '</a>'
            ) );
        }
	}
}