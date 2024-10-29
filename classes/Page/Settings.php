<?php
namespace Affilizz\Page;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Settings extends \Affilizz\Page {
	public $affilizz_rendering_mode;
	public $affilizz_proxy_uuid;
	public $affilizz_disable_javascript;
	public $affilizz_selective_enqueue;
	/**
	 * Adds the settings page for the Affilizz plugin.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function __construct() {
		parent::__construct(
			__( 'Affilizz', 'affilizz' ), 'affilizz', trailingslashit( AFFILIZZ_URL ) . 'assets/dist/images/logo/logo-type-white.svg', AFFILIZZ_DIRECTORY . 'templates/administration/page/settings',
			false, null, 'manage_options', __( 'Affilizz plugin configuration', 'affilizz' )
		);

		// Format correction - see https://developer.wordpress.org/apis/security/data-validation/
		$bust_cache = (int) ( isset( $_REQUEST['bust_cache'] ) && $_REQUEST['bust_cache'] );

		if ( $bust_cache ) {
			$local_file = trailingslashit( AFFILIZZ_DIRECTORY ) . 'assets/js/affilizz.local.js';
			unlink( $local_file );
			wp_redirect( remove_query_arg( 'bust_cache' ) );
		}

		$this->set_description( __( 'Configure the general settings and define the important variables for the Affilizz plugin.', 'affilizz' ) );
	}

	/**
	 * Registers the fields used by this plugin.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function register_fields() {
		// Usable fields
		$this->available_fields = array(
			'affilizz-rendering-mode'     => 'affilizz_rendering_mode',
			'affilizz-proxy-uuid'         => 'affilizz_proxy_uuid',
			'affilizz-disable-javascript' => 'affilizz_disable_javascript',
			'affilizz-selective-enqueue'  => 'affilizz_selective_enqueue'
		);

		parent::register_fields();
	}

	/**
	 * Saves the settings.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function save() {
		parent::save();
		if ( isset( $_POST['refresh-uuid'] ) ) {
			\Affilizz\Core::regenerate_uuid();
		}

		if ( ! empty( $_POST['affilizz-delete-codes-table'] ) && ! empty( $_POST['affilizz-confirm-table-deletion'] ) && $_POST['affilizz-confirm-table-deletion'] == AFFILIZZ_DELETE_TABLE_CONFIRMATION_TEXT ) {
			\Affilizz\Install::drop_table();
		}
	}
}