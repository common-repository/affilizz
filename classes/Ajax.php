<?php
namespace Affilizz;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Ajax {
	// Traits used by the class
	use \Affilizz\Util\Generic, \Affilizz\Util\Template;

	// Class variables
	public $api_instance;

	/**
	 * Hooks to the initialization to declare ajax routes.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	private function __construct() {
		$this->api_instance = new \Affilizz\API();

		add_action( 'wp_ajax_affilizz_get_publications', array( $this, 'get_publications' ) );
		add_action( 'wp_ajax_affilizz_get_publication_contents', array( $this, 'get_publication_contents' ) );

		// Handle ajax editing
		add_action( 'wp_ajax_edit_affilizz_publication_shortcode', array( $this, 'edit_shortcode_ajax' ) );

		// Handle ajax rendering
		add_action( 'wp_ajax_get_affilizz_publication', array( $this, 'get_publication_html' ) );
	}

	/**
	 * Retrieves the publications from the API.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function get_publications() {
		$current_id = sanitize_text_field( $_REQUEST['current_id'] ?? '' );

		$force_refresh = sanitize_text_field( $_REQUEST['force'] ?? false );
		$force_refresh = ! empty( $force_refresh );

		$search_term = sanitize_text_field( $_REQUEST['search'] ?? false );

		$currently_selected = \Affilizz\Util\Publications::get( $current_id );
		if ( empty( $currently_selected ) ) $currently_selected = new \stdClass();

		// Add the currently selected value
		$valid_publications = [];
		if ( ! empty( $currently_selected->publication_id ) && ! empty( $currently_selected->publication_name ) ) {
			$valid_publications[$currently_selected->publication_id] = [
				'name' => $currently_selected->publication_name,
				'selected' => true,
				'recent' => in_array( $currently_selected->publication_id, \Affilizz\Util\Publications::recent() ),
				'label' => $currently_selected->publication_name,
				'value' => $currently_selected->publication_id
			];
		}

		// Add the found publications
		foreach ( ( new \Affilizz\API() )->get_publications( $force_refresh, $search_term ) as $key => $publication ) {
			if ( empty( $publication['name'] ) ) $publication['name'] = __( 'Untitled publication', 'affilizz' );
			$valid_publications[$key] = $publication;
			$valid_publications[$key]['value'] = $key;
			$valid_publications[$key]['text'] = $publication['name'];
		}

		// Reduce the object to an array
		$valid_publications = array_values( $valid_publications );

		echo wp_json_encode( [
			'request' => $_REQUEST,
			'currently_selected' => $currently_selected->publication_id ?? '',
			'publications' => $valid_publications,
			'force_refresh'	=> $force_refresh
		] );
		wp_die();
	}

	/**
	 * Gets a structured representation of the contents in a publication (and their metadata from the database if applicable)
	 * @author Affilizz <wordpress@affilizz.com>
	 * @param $context_id The ID of the tuple in the database from which to pull both the publication and its contents.
	 * @return void
	 */
	public function get_publication_contents( $context_id = false ) {
		// Allow to pass the context through a regular call or an ajax variable
		if ( ! $context_id ) {
			$context_id = sanitize_text_field( $_REQUEST['context'] ?? '' );
		}

		$publication = sanitize_text_field( $_REQUEST['publication_id'] );
		$context = sanitize_text_field( $_REQUEST['context_id'] );
		$channel = get_option( 'affilizz_channel' );

		$publication_object = \Affilizz\Util\Publications::get( $context );
		$selected_publication_contents = array();

		// If we do not have a publication or if the publication is not the one in database (e.g. if we change publication in the modal), do not return selected content ids
		if ( is_object( $publication_object ) && ! empty( $publication_object->publication_id ) && $publication === $publication_object->publication_id ) {
			if ( ! empty( $publication_object->channel_id ) ) {
				$channel = $publication_object->channel_id;
			}

			if ( json_decode( stripslashes( $publication_object->publication_contents ) ) ) {
				$selected_publication_contents = json_decode( stripslashes( $publication_object->publication_contents ), true );
				$selected_publication_contents = wp_list_pluck( $selected_publication_contents, 'id' );
			}
		}

		$publication_api_object = $this->api_instance->get_publication( $publication, $channel );
		$publication_contents = array();

		// Build the array to return
		foreach ( $publication_api_object->contents as $content ) {
			$metadata = $content->items[0]->metadata;
			$name = trim( $metadata->name ) ?? __( 'Untitled publication content', 'affilizz' );

			$publication_contents[$content->id] = array(
				'label' => esc_attr( trim( substr( $name, 0, 30 ) ) . ( strlen( $name ) > 28 ? '…' : '' ) ),
				'value' => $content->id,
				'name' => esc_attr( trim( substr( $name, 0, 30 ) ) . ( strlen( $name ) > 28 ? '…' : '' ) ),
				'selected' => in_array( $content->id, $selected_publication_contents ),
				'type' => strtolower( $content->type ),
			);
		}

		// Return the JSON encoded version of the array
		echo wp_json_encode( $publication_contents );
		wp_die();
	}

	/**
	 * Renders the shortcode attributes edition modal.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function edit_shortcode_ajax() {
		$id = ( sanitize_text_field( $_REQUEST['id'] ) ?? '' );
		echo wp_json_encode(
			array(
				'context_id' => $id,
				'publication_object' => esc_js( \Affilizz\Util\Publications::get( sanitize_text_field( $id ) ) ),
				'render' => htmlspecialchars_decode( esc_html( self::render_template_part_static(
					trailingslashit( AFFILIZZ_DIRECTORY ) . 'templates/administration/editor/classic/modal',
					array(
						'id' => sanitize_text_field( $id )
					) )
				) ),
			)
		);
		wp_die();
	}

	/**
	 * Renders the SSR content for a publication attributes edition modal.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function get_publication_html() {
		$id = ( sanitize_text_field( $_REQUEST['id'] ) ?? '' );
		if ( ! empty( $id ) ) {
			$shortcode = new \Affilizz\Shortcode\Publication();
			$result = $shortcode->get_publication_render( $id, true );

			// Format correction - see https://developer.wordpress.org/apis/security/data-validation/
			$with_indicator = (int) $_REQUEST['with_indicator'];

			if ( $with_indicator ) {
				$result = '<div class="affilizz-inline__indicator">' . PHP_EOL
					. '<span class="affilizz-button-icon">' . PHP_EOL
						. '<img src="' . trailingslashit( AFFILIZZ_URL ) . 'assets/dist/images/logo/logo-type-white.svg" alt="' . __( 'Affilizz logo', 'affilizz' ) . '" width="12" />' . PHP_EOL
					. '</span>' . PHP_EOL
					. __( 'Affilizz affiliate content', 'affilizz' ) . PHP_EOL
				. '</div>' . PHP_EOL . $result;
			}

			wp_die( htmlspecialchars_decode( esc_html( $result ) ) );
		}

		wp_die();
	}
}