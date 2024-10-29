<?php
namespace Affilizz\Util;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Analytics {
	/**
	 * Gets an array of data for the analytics on Affilizz's side.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public static function all() {
		global $wp_version, $tinymce_version, $manifest_version, $required_php_version, $required_mysql_version, $wpdb;

		// Retrieve objects needed to build the analytics array
		$theme = wp_get_theme();
		$locale = explode( '_', get_locale() );
		$rendering_modes = array(
			'webcomponent' => __( 'Web component (default)', 'affilizz' ),
			'ssr' => __( 'Server-side rendering (SSR)', 'affilizz' )
		);
		$current_rendering_mode = get_option( 'affilizz_rendering_mode' );
		$current_rendering_mode = isset( $rendering_modes[ $$current_rendering_mode ] ) ? $rendering_modes[ $$current_rendering_mode ] : __( 'N/A', 'affilizz' );

		$javascript_status = get_option( 'affilizz_disable_javascript', __( 'N/A', 'affilizz' ) );
		if ( $javascript_status == 0 ) $javascript_status = __( 'Enabled', 'affilizz' );
		if ( $javascript_status == 1 ) $javascript_status = __( 'Disabled', 'affilizz' );

		$selective_enqueing = get_option( 'affilizz_selective_enqueue', __( 'N/A', 'affilizz' ) );
		if ( $selective_enqueing == 1 ) $selective_enqueing = __( 'Enabled', 'affilizz' );
		if ( $selective_enqueing == 0 ) $selective_enqueing = __( 'Disabled', 'affilizz' );

		// Analytics data array building
		$data = [
			'manifest_version' => [
				'key' => __( 'WordPress manifest version', 'affilizz' ),
				'value' => self::clean_version( $manifest_version )
			],
			'php_version' => [
				'key' => __( 'PHP version', 'affilizz' ),
				'value' => self::clean_version( phpversion() )
			],
			'required_php_version' => [
				'key' => __( 'Required PHP version', 'affilizz' ),
				'value' => self::clean_version( $required_php_version )
			],
			'mysql_version' => [
				'key' => __( 'MySQL version', 'affilizz' ),
				'value' => $wpdb->db_version()
			],
			'required_mysql_version' => [
				'key' => __( 'Required MySQL version', 'affilizz' ),
				'value' => self::clean_version( $required_mysql_version )
			],
			'operating_system' => [
				'key' => __( 'Operating system', 'affilizz' ),
				'value' => explode( ' ', trim( php_uname() ) )[ 0 ]
			],
			'server_flavor' => [
				'key' => __( 'PHP web server', 'affilizz' ),
				'value' => self::get_server_flavor()
			],
			'wordpress_version' => [
				'key' => __( 'WordPress version', 'affilizz' ),
				'value' => self::clean_version( $wp_version )
			],
			'tinymce_version' => [
				'key' => __( 'TinyMCE version', 'affilizz' ),
				'value' => self::clean_version( $tinymce_version )
			],
			'theme_name' => [
				'key' => __( 'Theme name', 'affilizz' ),
				'value' => $theme->get( 'Name' )
			],
			'active_plugins' => [
				'key' => __( 'Active plugins', 'affilizz' ),
				'value' => self::get_active_plugins_readable_list()
			],
			'locale' => [
				'key' => __( 'Current locale', 'affilizz' ),
				'value' => ( empty( $locale[ 0 ] ) ? __( 'Unknown locale', 'affilizz' ) : $locale[ 0 ] )
			],
			'multisite' => [
				'key' => __( 'Multisite installation', 'affilizz' ),
				'value' => is_multisite() ? __( 'Yes', 'affilizz' ) : __( 'No', 'affilizz' )
			],
			'document_root' => [
				'key' => __( 'Document root', 'affilizz' ),
				'value' => ( ! empty( $_SERVER['DOCUMENT_ROOT'] ) ? sanitize_file_name( $_SERVER['DOCUMENT_ROOT'] ) : __( 'N/A', 'affilizz' ) )
			],
			'affilizz_version' => [
				'key' => __( 'Affilizz version', 'affilizz' ),
				'value' => ( ! empty( AFFILIZZ_VERSION ) ? AFFILIZZ_VERSION : __( 'N/A', 'affilizz' ) )
			],
			'affilizz_assets_build_version' => [
				'key' => __( 'Affilizz version', 'affilizz' ),
				'value' => ( ! empty( AFFILIZZ_ASSETS_BUILD_VERSION ) ? AFFILIZZ_ASSETS_BUILD_VERSION : __( 'N/A', 'affilizz' ) )
			],
			'affilizz_channel' => [
				'key' => __( 'Option value : Channel', 'affilizz' ),
				'value' => get_option( 'affilizz_channel', __( 'N/A', 'affilizz' ) )
			],
			'affilizz_organization' => [
				'key' => __( 'Option value : Organization', 'affilizz' ),
				'value' => get_option( 'affilizz_organization', __( 'N/A', 'affilizz' ) )
			],
			'affilizz_media' => [
				'key' => __( 'Option value : Media', 'affilizz' ),
				'value' => get_option( 'affilizz_media', __( 'N/A', 'affilizz' ) )
			],
			'affilizz_api_key' => [
				'key' => __( 'Option value : API Key', 'affilizz' ),
				'value' => get_option( 'affilizz_api_key', __( 'N/A', 'affilizz' ) )
			],
			'affilizz_proxy_uuid' => [
				'key' => __( 'Current proxy UUID', 'affilizz' ),
				'value' => get_option( 'affilizz_proxy_uuid', __( 'N/A', 'affilizz' ) )
			],
			'affilizz_plugin_version' => [
				'key' => __( 'Affilizz plugin version', 'affilizz' ),
				'value' => get_option( 'affilizz_plugin_version', __( 'N/A', 'affilizz' ) )
			],
			'affilizz_rendering_mode' => [
				'key' => __( 'Affilizz rendering mode', 'affilizz' ),
				'value' => $current_rendering_mode
			],
			'affilizz_disable_javascript' => [
				'key' => __( 'Affilizz javascript file injection', 'affilizz' ),
				'value' => $javascript_status
			],
			'affilizz_selective_enqueue' => [
				'key' => __( 'Option value : Selective enqueue', 'affilizz' ),
				'value' => get_option( 'affilizz_selective_enqueue', __( 'N/A', 'affilizz' ) )
			],
			'affilizz_organization_label' => [
				'key' => __( 'Option value : Organization label', 'affilizz' ),
				'value' => get_option( 'affilizz_organization_label', __( 'N/A', 'affilizz' ) )
			],
			'affilizz_media_label' => [
				'key' => __( 'Option value : Media label', 'affilizz' ),
				'value' => get_option( 'affilizz_media_label', __( 'N/A', 'affilizz' ) )
			],
			'affilizz_channel_label' => [
				'key' => __( 'Option value : Channel label', 'affilizz' ),
				'value' => get_option( 'affilizz_channel_label', __( 'N/A', 'affilizz' ) )
			]
		];

		return $data;
	}

	private static function clean_version( $version_string = '0' ) {
		return preg_replace( '@^(\d\.\d+).*@', '\1', $version_string );
	}

	private static function get_server_flavor() {
		global $is_nginx, $is_apache, $is_iis7, $is_IIS;
		if ( $is_nginx ) return 'NGINX';
		if ( $is_apache ) return 'Apache';
		if ( $is_iis7 ) return 'IIS 7';
		if ( $is_IIS ) return 'IIS';

		return __( 'Unknown server flavor', 'affilizz' );
	}

	private static function get_active_plugins_readable_list() {
		// Get the plugin objects
		$return_array = [];
		$active_plugins = get_option( 'active_plugins', array() );

		foreach ( get_plugins() as $plugin => $plugin_metadata ) {
			if ( ! is_plugin_active( $plugin ) ) continue;
            $return_array[ $plugin ] = implode( ' ', [
				$plugin_metadata['Name'],
				$plugin_metadata['Version'],
				'-', sprintf( __( 'Network : %s', 'affilizz' ), ( $plugin_metadata['Network'] ) ? __( 'Yes', 'affilizz' ) : __( 'No', 'affilizz' ) ) ]
			);
        }

        return implode( "\r\n", $return_array );
    }
}