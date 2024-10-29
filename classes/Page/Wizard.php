<?php
namespace Affilizz\Page;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Affilizz\Page;

class Wizard extends Page {

	public $api;

	/**
	 * Creates the wizard configuration page.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function __construct() {
		// Create the page object
		parent::__construct( __( 'Configuration', 'affilizz' ), 'affilizz-wizard', 'admin-generic', AFFILIZZ_DIRECTORY . 'templates/administration/page/wizard', true );

		// Hook to AJAX actions
		add_action( 'wp_ajax_affilizz_check_api_key', array( $this, 'check_api_key' ) );
		add_action( 'wp_ajax_affilizz_get_entities', array( $this, 'get_entities' ) );
		add_action( 'wp_ajax_affilizz_get_media', array( $this, 'get_media' ) );
		add_action( 'wp_ajax_affilizz_get_channels', array( $this, 'get_channels' ) );
		add_action( 'wp_ajax_affilizz_save_params', array( $this, 'save_params' ) );

		// Common object
		$this->api = new \Affilizz\API();
	}

	/**
	 * Enqueues styles and scripts needed for the wizard page.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function display() {
		wp_register_style( 'affilizz-wizard', AFFILIZZ_URL . 'assets/dist/css/admin.css', array(), AFFILIZZ_VERSION, true );
		wp_register_script( 'affilizz-wizard', AFFILIZZ_URL . 'assets/dist/js/wizard.js', array( 'jquery' ), AFFILIZZ_VERSION, true );

		wp_enqueue_style( 'affilizz-wizard' );
		wp_enqueue_script( 'affilizz-wizard' );

		parent::display();
	}

	/**
	 * Checks if an API key provided through AJAX is valid.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function check_api_key() {
		if ( check_ajax_referer( 'affilizz-api-key', '_wpnonce_key' ) === false ) return;
		$api_key = sanitize_text_field( $_POST['key'] ?? '' );

		if ( ! empty( $api_key ) ) {
			$valid = $this->api->verify_key( $api_key );

			if ( $valid ) {
				update_option( 'affilizz_api_key', $api_key );
				wp_send_json( array( 'status' => 'success' ) );
			} else {
				wp_send_json(
					array(
						'status'  => 'error',
						'message' => __( 'This API key is invalid', 'affilizz' ),
					)
				);
			}
		} else {
			wp_send_json(
				array(
					'status'  => 'error',
					'message' => __( 'Please provide an API key, the field seems empty.', 'affilizz' ),
				)
			);
		}

		exit;
	}

	/**
	 * Retrieve entities of this account.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function get_entities() {
		$entities = $this->api->get_entities( get_option( 'affilizz_api_key' ) );

		if ( $entities ) {
			wp_send_json( $entities );
		}

		wp_send_json(
			array(
				'status'  => 'error',
				'message' => __( 'You don\'t have any organization on your account. Please create your first organization before installing this plugin.', 'affilizz' ),
			)
		);
	}

	/**
	 * Retrieves media for a specific entity.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function get_media() {
		$organization = sanitize_text_field( $_GET['organizationId'] );
		$organization = isset( $organization ) ? trim( $organization ) : null;

		if ( ! empty( $organization ) ) {
			$media = $this->api->get_media( get_option( 'affilizz_api_key' ), $organization );

			if ( $media ) {
				wp_send_json( $media );
			}
		}

		wp_send_json(
			array(
				'status'  => 'error',
				'message' => __( 'No media found for this organization. Please choose another organization or create your first media for this organization before installing this plugin.', 'affilizz' ),
			)
		);
	}

	/**
	 * Gets the channels of given organization and media.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function get_channels() {
		$media = sanitize_text_field( $_GET['mediaId'] );
		$media = isset( $media ) ? trim( $media ) : null;

		if ( ! empty( $media ) ) {
			$channels = $this->api->get_channels( '', $media );

			if ( $channels ) {
				wp_send_json( $channels );
			}
		}

		wp_send_json(
			array(
				'status'  => 'error',
				'message' => __( 'No channels found for this media. Please choose another media or create your first channel for this media before installing this plugin.', 'affilizz' ),
			)
		);
	}

	/**
	 * Saves the information at the end of the wizard process.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function save_params() {
		// Check the nonce before we save data
		if ( check_ajax_referer( 'affilizz-save-wizard-data' ) === false ) return;

		$organization = trim( sanitize_text_field( $_POST['organization'] ?? null ) );
		$media        = trim( sanitize_text_field( $_POST['media'] ?? null ) );
		$channel      = trim( sanitize_text_field( $_POST['channel'] ?? null ) );

		$organization_label = trim( sanitize_text_field( $_POST['organization-label'] ?? '' ) );
		$media_label        = trim( sanitize_text_field( $_POST['media-label'] ?? '' ) );
		$channel_label      = trim( sanitize_text_field( $_POST['channel-label'] ?? '' ) );

		if ( ! empty( $organization ) && ! empty( $media ) && ! empty( $channel ) ) {
			update_option( 'affilizz_organization', $organization );
			update_option( 'affilizz_media', $media );
			update_option( 'affilizz_channel', $channel );

			update_option( 'affilizz_organization_label', $organization_label ,false );
			update_option( 'affilizz_media_label', $media_label, false );
			update_option( 'affilizz_channel_label', $channel_label, false );

			wp_send_json( array( 'status' => 'success' ) );
		}

		wp_send_json(
			array(
				'status'  => 'error',
				'message' => __( 'You must pick an organization, a media and a channel to continue', 'affilizz' ),
			),
			400
		);
	}
}