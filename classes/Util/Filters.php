<?php
namespace Affilizz\Util;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Filters {
	// Declare the traits used by the class
	use \Affilizz\Util\Generic;

	/**
	 * Hooks onto default WordPress hooks to add feature.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function __construct() {
		// Add custom page templates from the plugin
		add_filter( 'body_class', array( $this, 'body_classes' ), 999 );

		// Add custom scripts to the plugin, the WordPress way
		add_action( 'wp_enqueue_scripts', array( $this, 'public_register_assets' ) );
		add_filter( 'affilizz_has_affilizz_content', array( $this, 'identify_content_from_regexp' ), 10, 2 );

		// Add needed meta tags in the header
		add_action( 'wp_head', array( $this, 'add_media_meta_tag' ) );

		// Add styles to the classic editor styles
		add_action( 'admin_init', array( $this, 'add_editor_style' ) );

		// Wrap the renders in a custom container
		add_filter( 'affilizz_publication_content_render', array( $this, 'wrap_affilizz_webcomponents' ) );

		// Allow custom HTML tags in the content
		add_filter( 'wp_kses_allowed_html', array( $this, 'allow_affilizz_custom_tag_in_content' ), 10, 2 );
		add_filter( 'tiny_mce_before_init', array( $this, 'allow_affilizz_custom_tag_in_editor' ), 10, 1 );
	}

	/**
	 * Updates the body classes for custom templates.
	 * @param $template The current body classes.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return String The updated body classes.
	 */
	public function body_classes( $body_classes ) {
		global $post;
		if ( $post && isset( $post->post_content ) && stripos( $post->post_content, '[affilizz-publication' ) ) {
			$body_classes[] = 'affilizz-content';
		}
		return $body_classes;
	}

	/**
	 * Register scripts and stylesheets for the public part.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function public_register_assets() {
		// Do not load the script if disabled by the user
		$disabled_javascript = get_option( 'affilizz_disable_javascript' );
		if ( ! empty( $disabled_javascript ) && $disabled_javascript === '1' ) {
			return;
		}

		// Do not load the script if we have selective enqueuing enabled and no Affilizz tag
		global $post;

		$selective_enqueing = get_option( 'affilizz_selective_enqueue', __( 'N/A', 'affilizz' ) );
		if ( $selective_enqueing == 1 ) {
			$post_content = apply_filters( 'the_content', ( ! empty( $post ) && ! empty( $post->post_content ) ? $post->post_content : '' ) );
			$has_affilizz_content = apply_filters( 'affilizz_has_affilizz_content', $post_content, false );
			if ( ! $has_affilizz_content ) {
				return;
			}
		}

		// Add and enqueue custom scripts, the WordPress way
		$affilizz_rendering_url = ( Assets::get_instance() )->get_diverted_asset_file_url();
		wp_enqueue_script( 'affilizz-rendering', $affilizz_rendering_url, array(), AFFILIZZ_VERSION, array( 'strategy' => 'defer' ) );
	}

	/**
	 * Adds custom styles to the tinymce editor styles.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function add_editor_style() {
		add_editor_style( AFFILIZZ_URL . 'assets/dist/css/admin.css' );
	}

	/**
	 * Wraps the components in a wrapper div to allow for easier identification in the webpage source.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return String The wrapper content.
	 */
	public function wrap_affilizz_webcomponents( $content ) {
		return '<div class="affilizz-rendering-container">' . $content . '</div>';
	}

	/**
	 * Allows affilizz-rendering-component tags in the WordPress content areas.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @since 1.12.0
	 * @param $tags The currently allowed tags ;
	 * @param $context The context in which we update the tags.
	 * @return Array An updated list of allowed tags.
	 */
	public function allow_affilizz_custom_tag_in_content( $tags, $context ) {
		if ( $context != 'post' ) {
			return $tags;
		}

		$tags['affilizz-rendering-component'] = array(
			'publication-content-id' => true,
			'loading' => true,
			'async'	=> true
		);

		$tags['affilizz-magic-match'] = array(
			'loading' => true,
			'async'	=> true
		);

		return $tags;
	}

	/**
	 * Allows affilizz-rendering-component tags in the WordPress editors.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @since 1.12.0
	 * @param $init The TinyMCE init array in its current state.
	 * @return Array An updated init array.
	 */
	public function allow_affilizz_custom_tag_in_editor( $init ) {
		// Command separated string of extended elements
		$added_tags = 'affilizz-rendering-component';

		// Add to extended_valid_elements if it already exists
		if ( isset( $init['extended_valid_elements'] ) ) {
			$init['extended_valid_elements'] .= ',affilizz-rendering-component,affilizz-rendering-component[*],affilizz-magic-match,affilizz-magic-match[*],';
		} else {
			$init['extended_valid_elements'] = 'affilizz-rendering-component,affilizz-rendering-component[*],affilizz-magic-match,affilizz-magic-match[*]';
		}

		if ( ! isset( $init['valid_children'] ) ) {
			$init['valid_children'] = '+body[affilizz-rendering-component],+body[affilizz-magic-match]';
		} else {
			$init['valid_children'] .= ',+body[affilizz-rendering-component],+body[affilizz-magic-match]';
		}

		if ( ! isset( $init['custom_elements'] ) ) {
			$init['custom_elements'] = 'affilizz-rendering-component,affilizz-rendering-component[*],affilizz-magic-match,affilizz-magic-match[*]';
		} else {
			$init['custom_elements'] .= ',affilizz-rendering-component,affilizz-rendering-component[*],affilizz-magic-match,affilizz-magic-match[*]';
		}

		return $init;
	}

	/**
	 * Remove the enqueues on pages that do not have Affilizz content.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return String The CDN caching root URL, (potentially) filtered.
	 */
	public function identify_content_from_regexp( $content = '', $bypass_option = false ) {
		$affilizz_selective_enqueue = get_option( 'affilizz_selective_enqueue' );
		if ( ( empty( $affilizz_selective_enqueue ) || $affilizz_selective_enqueue !== '1' ) && ! $bypass_option ) false;
		$has_affilizz_content = preg_match( \Affilizz\Core::get_selective_enqueuing_regex(), $content );
		return $has_affilizz_content;
	}

	/**
	 * Adds a meta tag if we specified the media.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function add_media_meta_tag() {
		$affilizz_media = esc_attr( trim( get_option( 'affilizz_media', '' ) ) );
		if ( empty( $affilizz_media ) ) return;

		printf( '<meta name="affilizz-media" content="%1$s" />' . "\r\n", esc_attr( $affilizz_media ) );
	}
}
