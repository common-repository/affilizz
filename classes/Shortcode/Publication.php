<?php
namespace Affilizz\Shortcode;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Publication {
	// Declare the traits used by this class
	use \Affilizz\Util\Template, \Affilizz\Util\Generic;

	/**
	 * Adds the 'publication' shortcode.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function __construct() {
		// Register the shortcode
		add_shortcode( 'affilizz-publication', array( $this, 'display' ) );

		// Ajax actions
		add_action( 'wp_ajax_affilizz_render_shortcode', array( $this, 'render_shortcode' ) );
		add_action( 'wp_ajax_affilizz_save_shortcode', array( $this, 'save_shortcode' ) );
	}

	/**
	 * Ajax action, save in WordPress options the attributes of a shortcode.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function save_shortcode() {
		global $wpdb;
		$id = ( sanitize_text_field( $_REQUEST['id'] ) ?? '' );
		if ( isset( $id ) && ! empty( $id ) ) {
			// And upate the database table
			$affilizz_channel = sanitize_text_field( $_REQUEST['publication_channel_id'] ?? '' );
			if ( empty( $affilizz_channel ) ) {
				$affilizz_channel = get_option( 'affilizz_channel' );
			}

			$publication_id = sanitize_text_field( $_REQUEST['publication_id'] );
			if ( empty( $publication_id ) ) {
				$publication_id = '';
			}

			$publication_content_id = sanitize_text_field( $_REQUEST['publication_content_id'] ?? '' );
			if ( empty( $publication_content_id ) ) {
				$publication_content_id = '';
			}

			$publication_name = sanitize_text_field( $_REQUEST['publication_name'] ?? '' );
			if ( empty( $publication_name ) ) {
				$publication_name = '';
			}

			$user_id = sanitize_text_field( $_REQUEST['user'] ?? '' );
			if ( empty( $user_id ) ) {
				$user_id = 0;
			}

			$post_id = sanitize_text_field( $_REQUEST['post'] ?? '' );
			if ( empty( $post_id ) ) {
				$post_id = 0;
			}

			$publication_contents = stripslashes_deep( sanitize_text_field( $_REQUEST['publication_contents'] ) ?? '{}' );
			if ( empty( $publication_contents ) ) {
				$publication_contents = '{}';
			}

			\Affilizz\Install::create_table();

			$insert_values = array(
				'id' => $id,
				'publication_id' => $publication_id,
				'publication_name' => $publication_name,
				'publication_contents' => $publication_contents,
				'publication_channel_id' => $affilizz_channel,
				'user_id' => $user_id,
				'post_id' => $post_id,
				'render' => $this->get_publication_render( $id, false )
			);
			// Upsert the data
			$table  = $wpdb->prefix . ( \Affilizz\Core::get_publications_database_table() );
			$result = $wpdb->update( $table, $insert_values, array( 'id' => $id ) );

			if ( false === $result || $result < 1 ) {
				global $wpdb;
				$result = $wpdb->insert( $table, $insert_values );
			}
		}

		echo wp_json_encode( array_merge( array( 'result' => $result ), $insert_values ), false );
		die();
	}

	/**
	 * Ajax action, save in WordPress options the attributes of a shortcode.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function render_shortcode() {
		$cache = ( sanitize_text_field( $_REQUEST['cache'] ) ?? 1 );
		echo wp_json_encode(
			array(
				'cache'  => $cache,
				'render' => do_shortcode( '[affilizz-publication cache="' . intval( $cache ) . '" id="' . ( esc_attr( sanitize_key( $_REQUEST['id'] ) ) ?? '' ) . '"]' )
			)
		);
		die();
	}

	/**
	 * Retrieves the server side rendering, without scripts, for every publication content in the publication.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @param $affilizz_publication_id The database ID for the publication.
	 * @param $cache Do we pull the information from the live API or do we pull it from the database?
	 * @param $with_scripts Do we leave or remove the script tags in the render?
	 * @return String A string containing the concatenated SSR from Affilizz.
	 */
	public function get_publication_render( $affilizz_publication_id, $cache = false, $with_scripts = false ) {
		global $wpdb;

		// If we need the cached version of the render and it exists, return it.
		$publication_object = \Affilizz\Util\Publications::get( $affilizz_publication_id );

		if ( empty( $publication_object ) ) {
			return '';
		}

		// Find the right sub-item
		$current_rendering_mode = get_option( 'affilizz_rendering_mode' );
		if ( ! $current_rendering_mode || ! in_array( $current_rendering_mode, array( 'ssr', 'webcomponent' ) ) ) {
			$current_rendering_mode = \Affilizz\Core::get_rendering_mode( $affilizz_publication_id );
		}

		if ( $cache && $current_rendering_mode == 'ssr' ) {
			if ( ! empty( $publication_object->render ) ) {
				return $publication_object->render;
			}
		}

		// Get the publication content to check if we have a link (thus not requiring an SSR)
		$publication_api_object = ( new \Affilizz\API() )->get_publication( $publication_object->publication_id, $publication_object->publication_channel_id );

		// If we do not have publication contents selected, return an empty string
		if ( empty( $publication_object->publication_contents ) ) {
			return '';
		}

		// If the JSON is misformed in database and we cannot process it, return an empty string
		$publication_contents = json_decode( $publication_object->publication_contents );
		if ( empty( $publication_contents ) ) {
			return '';
		}

		$concatenated_render = '';
		if ( is_array( $publication_contents ) ) {
			foreach ( $publication_contents as $publication_content ) {
				$server_site_endpoint = add_query_arg(
					array(
						'publicationContentId' => $publication_content->id,
						'chrome'               => 'false',
						'contextId'            => wp_generate_uuid4()
					),
					\Affilizz\Core::get_ssr_endpoint_root()
				);

				if ( is_admin() || $current_rendering_mode == 'ssr' ) {
					foreach ( $publication_api_object->contents as $content ) {
						if ( $publication_content->id != $content->id ) {
							continue;
						}
						switch ( strtolower( $content->type ) ) {
							case 'link':
								$href   = trim( $content->redirectUrl ?? '#' );
								$title  = trim( $content->items[0]->metadata->name ?? '' );
								$render = sprintf( '<a href="%1$s" title="%2$s">%3$s</a>', $href, esc_attr( $title ), $title );
								break;
							case 'box':
							case 'cta':
							case 'card':
							case 'carousel':
							default:
								// Remove scripts if they are not required
								$render = ( $with_scripts
									? self::get_distant_url( $server_site_endpoint )
									: preg_replace( '#<script src="(.*?)"(.*?)>(.*?)</script>#is', '<script src="' . \Affilizz\Core::get_ssr_endpoint_root() . '$1" $2>$3</script>', self::get_distant_url( $server_site_endpoint ) )
								);
								break;
						}
					}
				} else {
					foreach ( $publication_api_object->contents as $content ) {
						if ( $publication_content->id != $content->id ) {
							continue;
						}

						switch ( strtolower( $content->type ) ) {
							case 'link':
								$href   = trim( $content->redirectUrl ?? '#' );
								$title  = trim( $content->items[0]->metadata->name ?? '' );
								$render = sprintf( '<a href="%1$s" title="%2$s">%3$s</a>', $href, esc_attr( $title ), $title );
								break;
							case 'box':
							case 'cta':
							case 'card':
							default:
								// Remove scripts if they are not required
								$render = sprintf( '<affilizz-rendering-component publication-content-id="%1$s" loading="lazy" async></affilizz-rendering-component>', $publication_content->id );
								break;
						}
					}
				}

				$render = apply_filters( 'affilizz_publication_content_render', $render );
				$concatenated_render .= $render;
			}
		}

		// Update the data
		$render = htmlspecialchars_decode( esc_html( $concatenated_render ) );
		if ( $current_rendering_mode == 'ssr' ) {
			$wpdb->update(
				$wpdb->prefix . ( \Affilizz\Core::get_publications_database_table() ),
				array( 'render' => $render ),
				array( 'id' => esc_attr( $affilizz_publication_id ) )
			);
		}

		return $concatenated_render;
	}

	/**
	 * Displays the shortcode template inside the content of the page.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @param $attributes The shortcode parameters array.
	 * @param $content The content in which to replace the shortcode, if supplied.
	 * @return String The generated markup.
	 */
	public function display( $attributes = array(), $content = '' ) {
		do_action( 'before_affilizz_publication_shortcode_output' );
		$cache = ( isset( $attributes['cache'] ) && $attributes['cache'] == '0' ) ? false : true;
		$result = ( isset( $attributes['id'] ) ) ? $this->get_publication_render( $attributes['id'], $cache ) : '';
		if ( empty( $result ) && is_admin() ) {
			return '<div class="affilizz-missing-publication">' . __( 'This publication does not exist in the database. It was either deleted, or its ID does not exist.' ) . '</div>';
		}
		do_action( 'after_affilizz_publication_shortcode_output' );
		return $result;
	}
}