<?php
namespace Affilizz\Util;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

trait Generic {
	// Class variables
	public static $instance;

	/**
	 * Avoids the class instance from being cloned.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return A standard WordPress error.
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, esc_html( __( 'Cheating, huh ?', 'affilizz' ) ), '6.0' );
	}

	/**
	 * Creates a class singleton.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return Object An instance of the current class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new static();
		}
		return self::$instance;
	}

	/**
	 * Retrieves a distant URL after passing the necessary headers.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @param $url The URL to retrieve.
	 * @param $headers The custom headers (for APIs for example).
	 * @return $data The return from the distant URL.
	 */
	public static function get_distant_url( $url = '', $headers = null ) {
		$timeout = 5;

		$args = array(
			'timeout' => $timeout
		);

		if ( ! empty( $headers ) ) {
			$args['headers'] = $headers;
		}

		return wp_remote_retrieve_body( wp_remote_get( $url, $args ) );
	}

	/**
	 * Posts to a distant URL after passing the necessary headers.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @param $url The URL to retrieve.
	 * @param $headers The custom headers (for APIs for example).
	 * @return $data The return from the distant URL.
	 */
	public static function post_to_distant_url( $url = '', $headers = null, $body = array() ) {
		$timeout = 5;

		$args = array(
			'body'        => $body,
			'timeout'     => $timeout,
			'redirection' => '5',
			'httpversion' => '1.0'
		);

		if ( ! empty( $headers ) ) {
			$args['headers'] = $headers;
		}

		return wp_remote_retrieve_body( wp_remote_post( $url, $args ) );
	}
}