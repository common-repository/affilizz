<?php
namespace Affilizz;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Install {
	public static $force_affilizz_update_key = '62d7ebpik54ghu1usrxsrl1w1mg2lifj';

	/**
	 * When installing the plugin, redirects to the wizard.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function __construct() {
		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'redirect_to_wizard' ) );
			add_action( 'upgrader_process_complete', array( $this, 'upgrade' ), 10, 2 );


			if ( isset( $_GET['affilizz_force_upgrade'] ) ) {
				$force_upgrade_argument = sanitize_key( $_GET['affilizz_force_upgrade'] );
				if ( $force_upgrade_argument == self::$force_affilizz_update_key ) {
					$this->upgrade();
					die();
				}
			}
		}
	}

	/**
	 * Checks the transient to display the wizard page.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function redirect_to_wizard() {
		if ( get_transient( 'affilizz_should_display_install_wizard' ) ) {
			// Prevent Ajax request catching
			if ( wp_doing_ajax() ) {
				return;
			}

			// If we're not on the wizard page, redirect to the wizard
			if ( isset( $_GET['page'] ) ) {
				if ( wp_unslash( sanitize_text_field( $_GET['page'] ) ?? '' ) !== '/affilizz-wizard' ) {
					delete_transient( 'affilizz_should_display_install_wizard' );
					wp_safe_redirect( admin_url( 'admin.php?page=affilizz-wizard' ) );
					exit;
				}
			}
		}
	}

	/**
	 * Sets up the plugin.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public static function install() {
		if ( empty( get_option( 'affilizz_api_key' ) ) ) {
			set_transient( 'affilizz_should_display_install_wizard', 1, 0 );
		} else {
			delete_transient( 'affilizz_should_display_install_wizard' );
		}

		// If we install the plugin and it's already been installed previously, upgrade it
		self::upgrade();
	}

	/**
	 * Creates the table if it does not exist.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @since 1.12.0
	 * @version 1.13.1
	 * @return void
	 */
	public static function create_table() {
		global $wpdb;

		$table_name = $wpdb->prefix . ( \Affilizz\Core::get_publications_database_table() );
		$creation_query = $wpdb->prepare( 'CREATE TABLE IF NOT EXISTS %i (
            `affilizz_publication_id` int(11) NOT NULL AUTO_INCREMENT,
            `id` varchar(13) NOT NULL,
            `publication_id` varchar(32) NOT NULL,
            `publication_name` varchar(255) DEFAULT NULL,
            `publication_contents` json NOT NULL,
            `publication_channel_id` varchar(32) NOT NULL,
            `user_id` bigint(20) NOT NULL,
            `post_id` bigint(20) NOT NULL,
            `date_created` timestamp NOT NULL DEFAULT current_timestamp(),
            `date_modified` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
            `data` text DEFAULT NULL,
            `render` mediumblob DEFAULT NULL,
            PRIMARY KEY (`affilizz_publication_id`),
            UNIQUE KEY `id` (`id`),
            KEY `publication_id` (`publication_id`),
            KEY `user_id` (`user_id`),
            KEY `post_id` (`post_id`)
		) ENGINE=InnoDB DEFAULT CHARSET=utf8;', $table_name );

		$creation_query = str_replace( [ '`\'', '\'`' ], '`', $creation_query );
		$wpdb->query( $creation_query );
	}

	/**
	 * Drops the table if it does not exist.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @since 1.12.0
	 * @return void
	 */
	public static function drop_table() {
		global $wpdb;
		$table_name = $wpdb->prefix . ( \Affilizz\Core::get_publications_database_table() );
		return $wpdb->query( $wpdb->prepare( 'DROP TABLE IF EXISTS %i', $table_name ) );
	}

	/**
	 * Upgrades the plugin.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @version 1.13.1
	 * @return void
	 */
	public static function upgrade() {
		global $wpdb;

		self::create_table();

		// Get the current API version, default to the first released version if inexistant (since we added this feature in 1.10.0)
		$current_plugin_version = get_option( 'affilizz_plugin_version' );
		if ( empty( $current_plugin_version ) ) {
			$current_plugin_version = '1.0.0';
		}
		
		if ( version_compare( $current_plugin_version, '1.10.0', '<' ) ) {
			// Upgrades the publication name to accomodate longer names on old publications
			$table_name = $wpdb->prefix . ( \Affilizz\Core::get_publications_database_table() );
			$wpdb->query( 'ALTER TABLE `' . $table_name . '` CHANGE `publication_name` `publication_name` varchar(255) CHARACTER SET ' . DB_CHARSET );
		}

		// Adds the custom UUID for the proxy URL
		$affilizz_proxy_uuid = get_option( 'affilizz_proxy_uuid' );
		if ( version_compare( $current_plugin_version, '1.11.0', '<' ) || empty( $affilizz_proxy_uuid ) ) {
			update_option( 'affilizz_proxy_uuid', wp_generate_uuid4() );
		}

		if ( version_compare( $current_plugin_version, '1.13.1', '<' ) ) {
			// Upgrades the publication name to accomodate longer names on old publications
			$table_name = $wpdb->prefix . ( \Affilizz\Core::get_publications_database_table() );
			$wpdb->query( 'ALTER TABLE `' . $table_name . '` CHANGE `data` `data` mediumblob' );
		}

		// Bump the version to the installed version
		Core::regenerate_uuid();

		// Flush rules
		flush_rewrite_rules();

		// Create the cache directories
		( Core::get_instance() )->init_cache_directories();
		( Core::get_instance() )->refresh_cache_directory( $affilizz_proxy_uuid );

		// Post WordPress 6.4, update the autolading of options
		if ( version_compare( $current_plugin_version, '1.14.0', '>=' ) && function_exists( 'wp_set_option_autoload_values' ) ) {
			\wp_set_option_autoload_values( array( 'affilizz_channel_label', 'affilizz_media_label', 'affilizz_organization_label' ), 'no' );
		}
	}
}
