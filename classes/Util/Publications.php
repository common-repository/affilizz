<?php
namespace Affilizz\Util;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Publications {
	/**
	 * Retrieves a publication from the database.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public static function get( $context_id = false, $column = 'id' ) {
		global $wpdb;
		if ( ! $context_id ) {
			return null;
		}

		// Only allow two columns
		if ( ! in_array( $column, [ 'id', 'affilizz_publication_id' ] ) ) {
			$column = 'id';
		}

		if ( ! self::table_exists() ) {
			return null;
		}

		$table_name = $wpdb->prefix . ( \Affilizz\Core::get_publications_database_table() );

		// Avoid the backtick issue in wpdb query preparation
		$publication_contents_query = str_replace( [ '`\'', '\'`' ], '`', $wpdb->prepare( 'SELECT * FROM %i WHERE %i = %s', $table_name, $column, $context_id ) );
		return $wpdb->get_row( $publication_contents_query ) ?? null;
	}


	/**
	 * Returns a list of publications recently used by the user.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return array
	 */
	public static function recent( $user_id = null, $count = 2 ) {
		if ( empty( $user_id ) ) {
			$user_id = ( get_current_user_id() ?? 0 );
		}

		if ( empty( $user_id ) ) {
			return array();
		}

		if ( ! self::table_exists() ) {
			return array();
		}

		global $wpdb;
		$table_name                 = $wpdb->prefix . ( \Affilizz\Core::get_publications_database_table() );
		$publication_contents_query = $wpdb->prepare( 'SELECT * FROM %i WHERE user_id = %s LIMIT 0, %d', $table_name, $user_id, intval( $count ) );
		$results                    = $wpdb->get_results( $publication_contents_query ) ?? null;
		return wp_list_pluck( $results, 'publication_id' );
	}

	/**
	 * Checks if the publication table exists.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @since 1.12.0
	 * @return Boolean Does the table exist ?
	 */
	public static function table_exists() {
		global $wpdb;
		$table_name = $wpdb->base_prefix . ( \Affilizz\Core::get_publications_database_table() );
		return $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) ) ) == $table_name;
	}
}