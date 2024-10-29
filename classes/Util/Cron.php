<?php
namespace Affilizz\Util;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Cron {
	// Declare the traits used by the class
	use \Affilizz\Util\Generic;

	// Class variables
	public $api = false;

	/**
	 * Declare a series of CRON tasks.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function __construct() {
		add_action( 'affilizz_hourly_cron', [ $this, 'check_media_existence' ] );
		add_action( 'admin_notices', [ $this, 'display_missing_media_notice' ] );

		// Connect to the API
		$this->api = new \Affilizz\API();

		// Schedule events
		if ( ! wp_next_scheduled( 'affilizz_hourly_cron' ) ) {
			wp_schedule_event( time(), 'hourly', 'affilizz_hourly_cron', array( false ) );
		}
	}

	/**
	 * Checks if a media / channel exists, adds a warning if deleted on Affilizz side.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function check_media_existence() {
		if ( ! $this->api ) return;

		// Temporary error variables
		$missing_media   = false;
		$missing_channel = false;

		$organization = get_option( 'affilizz_organization', false );
		$media        = get_option( 'affilizz_media', false );
		$channel      = get_option( 'affilizz_channel', false );

		// Check if the media exists
		$affilizz_medias = $this->api->get_media( get_option( 'affilizz_api_key' ), $organization );
		if ( ! in_array( $media, $affilizz_medias ) ) $missing_media = true;

		if ( ! $missing_media ) {
			$affilizz_channels = $this->api->get_channels( $organization, $media );
			if ( ! in_array( $channel, $affilizz_channels ) ) $missing_channel = true;
		}

		// Store the error status
		if ( $missing_channel ) set_transient( 'affilizz_missing_channel', 1, 8 * HOUR_IN_SECONDS );
		if ( $missing_media ) set_transient( 'affilizz_missing_media', 1, 8 * HOUR_IN_SECONDS );
	}
}