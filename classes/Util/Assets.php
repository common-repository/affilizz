<?php
namespace Affilizz\Util;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

use Affilizz\Core;

class Assets {
	// Declare the traits used by the class
	use \Affilizz\Util\Generic;

	public function get_diverted_asset_file_path( $extension = 'rendering' ) {
		// Divert the loading
		$affilizz_proxy_uuid = get_option( 'affilizz_proxy_uuid' ) ?? Core::regenerate_uuid();
		$cache_folder_path   = trailingslashit( \Affilizz\Core::get_cdn_cache_root_path() ) . $affilizz_proxy_uuid;
		$cache_file_name     = sha1( $affilizz_proxy_uuid . '-' . $extension ) . '.js';

		// Bump the version to the installed version
		return trailingslashit( $cache_folder_path ) . $cache_file_name;
	}

	public function get_diverted_asset_file_url( $extension = 'rendering', $with_default = true ) {
		// Try to copy
		if ( ! ( file_exists( $this->get_diverted_asset_file_path() ) ) ) {
			$this->store_local_rendering_script_copy();
		}

		// If for some reason we could not create the file, return the CDN version
		if ( ! ( file_exists( $this->get_diverted_asset_file_path() ) ) ) {
			return \Affilizz\Core::get_rendering_script_location();
		}

		// Divert the loading
		$affilizz_proxy_uuid = get_option( 'affilizz_proxy_uuid' ) ?? Core::regenerate_uuid();
		$cache_folder_path = trailingslashit( \Affilizz\Core::get_cdn_cache_root_url() ) . $affilizz_proxy_uuid;
		$cache_file_name = sha1( $affilizz_proxy_uuid . '-' . $extension ) . '.js' ;

		// Bump the version to the installed version
		return trailingslashit( $cache_folder_path ) . $cache_file_name;
	}

	public function store_local_rendering_script_copy() {
		$remote_file_url = add_query_arg( array( 'cache_var' => time() ), \Affilizz\Core::get_rendering_script_location() );
		$cache_oldest_time = time() - ( \Affilizz\Core::get_cdn_cache_time() ?? 0 );

		$local_file = $this->get_diverted_asset_file_path();
		if ( ! file_exists( $local_file ) || filemtime( $local_file ) < ( $cache_oldest_time ?? 0 ) ) {
			// Create the directory if it doesn't exist
			( Core::get_instance() )->refresh_cache_directory();
			
			// Load the remote file
			$remote_script_body = wp_remote_retrieve_body( wp_remote_get( $remote_file_url ) );
			file_put_contents( $local_file, '/* Loaded from local cache - ' . time() . ' */' . $remote_script_body );
			chmod( $local_file, 0644 );

			// For clarification
			$return_value = $remote_script_body;
		}
    }
}
