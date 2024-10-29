<?php
namespace Affilizz;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

spl_autoload_register( 'Affilizz\Core::autoload' );

class Core {
	// Instance variables
	public static $instance;

	/**
	 * Creates a class singleton for this plugin Core class.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return Affilizz\Core An instance of the current class.
	 */
	public static function get_instance() {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Creates an instance of the current class.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function __construct() {
		// Register the global variables
		define( 'AFFILIZZ_DIRECTORY', trailingslashit( WP_CONTENT_DIR . '/plugins/affilizz/' ) );
		define( 'AFFILIZZ_URL', trailingslashit( esc_url( WP_CONTENT_URL ) . '/plugins/affilizz/' ) );

		// Register the environment specifics
		defined( 'AFFILIZZ_SSR_ENDPOINT_ROOT' ) || define( 'AFFILIZZ_SSR_ENDPOINT_ROOT', 'https://rendering.ssr.affilizz.com/' );
		defined( 'AFFILIZZ_SSR_DEFAULT_WIDTH' ) || define( 'AFFILIZZ_SSR_DEFAULT_WIDTH', '740' );
		defined( 'AFFILIZZ_USER_API_BASE_URL' ) || define( 'AFFILIZZ_USER_API_BASE_URL', 'https://user.api.affilizz.com/api/v1/' );
		defined( 'AFFILIZZ_CMS_API_BASE_URL' ) || define( 'AFFILIZZ_CMS_API_BASE_URL', 'https://publication-cms.api.affilizz.com/api/v1/' );
		defined( 'AFFILIZZ_API_KEY_HELP_URL' ) || define( 'AFFILIZZ_API_KEY_HELP_URL', 'https://intercom-help.eu/affilizz/fr/articles/26097-ou-trouver-ma-cle-d-api-pour-wordpress' );
		defined( 'AFFILIZZ_PUBLICATIONS_TABLE' ) || define( 'AFFILIZZ_PUBLICATIONS_TABLE', 'affilizz_publications' );
		defined( 'AFFILIZZ_PUBLICATIONS_TRANSIENT_KEY' ) || define( 'AFFILIZZ_PUBLICATIONS_TRANSIENT_KEY', 'affilizz_publications' );

		// URLS on the Affilizz Dashboard
		defined( 'AFFILIZZ_CREATE_PUBLICATION_URL' ) || define( 'AFFILIZZ_CREATE_PUBLICATION_URL', 'https://app.affilizz.com/publications/new' );
		defined( 'AFFILIZZ_EDIT_PUBLICATION_URL' ) || define( 'AFFILIZZ_EDIT_PUBLICATION_URL', 'https://app.affilizz.com/publications/##publication##?mediaId=##media##' );

		// Deal with issues loading from a CDN
		defined( 'AFFILIZZ_CDN_RENDERING_SCRIPT_LOCATION' ) || define( 'AFFILIZZ_CDN_RENDERING_SCRIPT_LOCATION', 'https://sc.affilizz.com/affilizz.js' );
		defined( 'AFFILIZZ_CDN_CACHE_TIME' ) || define( 'AFFILIZZ_CDN_CACHE_TIME', HOUR_IN_SECONDS );
		defined( 'AFFILIZZ_CDN_CACHE_ROOT_PATH' ) || define( 'AFFILIZZ_CDN_CACHE_ROOT_PATH', WP_CONTENT_DIR . '/cache/' );
		defined( 'AFFILIZZ_CDN_CACHE_ROOT_URL' ) || define( 'AFFILIZZ_CDN_CACHE_ROOT_URL', WP_CONTENT_URL . '/cache/' );

		// Affilizz defaults
		defined( 'AFFILIZZ_DEFAULT_RENDERING_MODE' ) || define( 'AFFILIZZ_DEFAULT_RENDERING_MODE', 'webcomponent' );
		defined( 'AFFILIZZ_SELECTIVE_ENQUEUING_REGEX' ) || define( 'AFFILIZZ_SELECTIVE_ENQUEUING_REGEX', '|\<affilizz-rendering(.*?)\>(.*?)\<\/affilizz-rendering|' );
		defined( 'AFFILIZZ_DELETE_TABLE_CONFIRMATION_TEXT' ) || define( 'AFFILIZZ_DELETE_TABLE_CONFIRMATION_TEXT', 'CONFIRM-AFFILIZZ-TABLE-DELETION' );

		// Register and enqueue admin specific styles and scripts
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_register_assets' ) );

		// Load the plugin's translations
		add_action( 'plugins_loaded', array( $this, 'load_translations' ), 10 );
		add_action( 'plugins_loaded', array( $this, 'init' ), 999 );

		// Change the administration footer output
		add_action( 'admin_footer', array( $this, 'admin_footer' ), 99 );

		// Register the elementor block
		add_action( 'elementor/widgets/register', array( $this, 'register_elementor_widget_classes' ) );
		add_action( 'elementor/editor/after_enqueue_styles', array( $this, 'elementor_register_styles' ) );
		add_action( 'elementor/editor/before_enqueue_scripts', array( $this, 'elementor_register_scripts' ) );
		add_action( 'elementor/editor/footer', array( $this, 'admin_footer' ), 99 );

	}

	/**
	 * Hooks after the plugin load process to allow for translation in later hooks.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function init() {
		new Install();

		// Add the custom admin pages
		Page\Settings::get_instance();
		// Page\Wizard::get_instance();
		new Page\Wizard();

		// Add the shortcodes
		Shortcode\Publication::get_instance();

		// Add the utilitary classes
		Util\Filters::get_instance();
		Util\Assets::get_instance();
		Util\Notices::get_instance();
		Ajax::get_instance();

		// Add the custom editor blocks
		Editor\Classic::get_instance();
		new Editor\Gutenberg();

		// Add the divi module
		if ( class_exists( 'DiviExtension' ) ) {
			new Util\Divi();
		}
	}

	/**
	 * Load plugin translations.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function load_translations() {
		load_plugin_textdomain( 'affilizz', false, 'affilizz/languages' );
	}

	/**
	 * Allows the plugin to autoload the classes it holds.
	 * @param $class The name of the class to load, namespaced.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public static function autoload( $class = '' ) {
		// Deal with the filename structure
		$class    = str_replace( __NAMESPACE__ . '\\', '', $class );
		$segments = explode( '\\', $class );
		$path     = __DIR__ . '/' . implode( '/', $segments ) . '.php';

		// If the correct path exists, use it
		if ( file_exists( $path ) ) {
			include $path;
			return;
		}

		// If the lowercase version exists, use it
		if ( file_exists( strtolower( $path ) ) ) {
			include $path;
		}
		return false;
	}

	/**
	 * Register scripts and stylesheets for the administration area.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function admin_register_assets( $hook_suffix = null ) {
		global $post;

		wp_enqueue_editor();

		// Add custom scripts
		wp_register_script( 'affilizz-admin', esc_url( AFFILIZZ_URL ) . 'assets/dist/js/admin.js', array(), esc_attr( AFFILIZZ_ASSETS_BUILD_VERSION ) );
		wp_register_script( 'tom-select', esc_url( AFFILIZZ_URL ) . 'assets/vendor/tom-select/tom-select.complete.min.js', array(), esc_attr( AFFILIZZ_ASSETS_BUILD_VERSION ) );
		wp_register_script( 'affilizz-admin-editor-classic', esc_url( AFFILIZZ_URL ) . 'assets/dist/js/editor.js', array(), esc_attr( AFFILIZZ_ASSETS_BUILD_VERSION ) );

		// Add PHP variables to the JS scripts, for translation or display purposes
		$posted_id = sanitize_text_field( $_GET['post'] ?? '' );
		wp_localize_script( 'affilizz-admin', 'affilizz_admin_l10n', $this->get_javascript_l10n() );

		// Enqueue the scripts, the WordPress way
		wp_enqueue_script( 'affilizz-admin' );
		wp_enqueue_script( 'tom-select' );

		// Do not load the editor in the wizard
		if ( in_array( $hook_suffix, [ 'post.php', 'post-new.php' ] ) ) {
		    wp_enqueue_script( 'jquery-ui-core' );
		    wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'affilizz-admin-editor-classic' );
		}

		// Add custom styles
		wp_register_style( 'affilizz-admin', esc_url( AFFILIZZ_URL ) . 'assets/dist/css/admin.css', '', AFFILIZZ_ASSETS_BUILD_VERSION );
		wp_register_style( 'tom-select', esc_url( AFFILIZZ_URL ) . 'assets/vendor/tom-select/tom-select.default.css', '', AFFILIZZ_ASSETS_BUILD_VERSION );

		// Enqueue the styles, the WordPress way
		wp_enqueue_style( 'affilizz-admin' );
		wp_enqueue_style( 'tom-select' );
	}

	/**
	 * Outputs the HTML for the footer section of the admin panel.
	 * @author Romain Carlier <romain@reaklab.com>
	 * @return void
	 */
	public function admin_footer() {
		// Enable the modal only if we are in Classic Editor context
		include_once trailingslashit( AFFILIZZ_DIRECTORY ) . 'templates/administration/partial/footer.php';
	}

	/**
	 * Filtreable getter for the Affilizz API publications transient.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @since 1.14.0
	 * @return String The API publications transient, (potentially) filtered.
	 */
	public static function get_publications_transient_key() {
		return apply_filters( 'affilizz_publications_transient_key', AFFILIZZ_PUBLICATIONS_TRANSIENT_KEY );
	}

	/**
	 * Registers the Affilizz Elementor widget class with the Elementor widgets manager.
	 * @author Romain Carlier <romain@reaklab.com>
	 * @param \Elementor\Widgets_Manager $widgets_manager The Elementor widgets manager instance.
	 * @return void
	 */
	public function register_elementor_widget_classes( $widgets_manager ) {
		$widgets_manager->register( new \Affilizz\Editor\Elementor() );
	}

	/**
	 * Register scripts and stylesheets for the administration area.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public static function regenerate_uuid() {
		$previous_affilizz_proxy_uuid = get_option( 'affilizz_proxy_uuid' );
		$updated_affilizz_proxy_uuid = wp_generate_uuid4();
		update_option( 'affilizz_proxy_uuid', $updated_affilizz_proxy_uuid );

		( self::get_instance() )->refresh_cache_directory( $previous_affilizz_proxy_uuid );

		return $updated_affilizz_proxy_uuid;
	}

	/**
	 * The plugin is removed, remove its traces.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public static function uninstall() {
		// Transients
		delete_transient( 'affilizz_should_display_install_wizard' );
	}

	/**
	 * Creates the cache folders for our local files.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @since 1.12.0
	 * @return void
	 */
	public function init_cache_directories() {
		// Create cache folder if it does not exist
		if ( ! is_dir( self::get_cdn_cache_root_path() ) ) {
			mkdir( self::get_cdn_cache_root_path(), 0755 );
		}
	}

	/**
	 * Deletes recursively the content of a folder, replaces previous call to $wp_filesystem->delete.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @since 1.12.0
	 * @return void
	 */
	private function delete_directory( $directory ) {
		// Avoid deleting files in other directories than this one
		$current_path = realpath( __FILE__ );
		if ( stripos( $directory, $current_path ) === false ) return false;

		// Assign files inside the directory
		$directory = new \DirectoryIterator( dirname( $directory ) );

		// Remove all the files in the list
		foreach ( $directory as $file_information ) {
			if ( $file_information->isDot() || ! $file_information->isFile() ) continue;
			unlink( $file_information->getPathname() );
		}

		// Finally, remove the (now empty) directory
		rmdir( $directory );
	}

	/**
	 * Create or update the local cache directory.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @since 1.12.0
	 * @return void
	 */
	public function refresh_cache_directory( $previous_uuid = '' ) {
		$affilizz_proxy_uuid = get_option( 'affilizz_proxy_uuid' );
		$new_cache_folder_path = trailingslashit( self::get_cdn_cache_root_path() ) . $affilizz_proxy_uuid;

		// Delete the previous directory if it exists
		if ( ! empty( $previous_uuid ) && is_dir( trailingslashit( self::get_cdn_cache_root_path() ) . $previous_uuid ) ) {
			$this->delete_directory( trailingslashit( self::get_cdn_cache_root_path() ) . $previous_uuid );
		}

		// Create new cache folder if it does not exist
		if ( ! is_dir( $new_cache_folder_path ) ) {
			$this->init_cache_directories();
			mkdir( $new_cache_folder_path, 0755 );
		}
	}

	/**
	 * Filtreable getter for the Affilizz SSR Endpoint.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @since 1.13.0
	 * @return String The SSR Endpoint, (potentially) filtered.
	 */
	public static function get_ssr_endpoint_root() {
		return apply_filters( 'affilizz_ssr_endpoint_root_url', AFFILIZZ_SSR_ENDPOINT_ROOT );
	}

	/**
	 * Filtreable getter for the Affilizz rendering mode, either generic or for a specific publication.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @since 1.13.0
	 * @param $affilizz_publication_id The Affilizz publication ID to pass to the filter.
	 * @return String The rendering mode, either generic or for a specific publication, (potentially) filtered.
	 */
	public static function get_rendering_mode( $affilizz_publication_id = 0 ) {
		return apply_filters( 'affilizz_rendering_mode', AFFILIZZ_DEFAULT_RENDERING_MODE, $affilizz_publication_id ) ?? 'webcomponent';
	}

	/**
	 * Filtreable getter for the Affilizz rendering mode, either generic or for a specific publication.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @since 1.13.0
	 * @param $affilizz_publication_id The Affilizz publication ID to pass to the filter.
	 * @return String The rendering mode, either generic or for a specific publication, (potentially) filtered.
	 */
	public static function get_publication_default_width( $affilizz_publication_id = 0 ) {
		return apply_filters( 'affilizz_publication_default_width', AFFILIZZ_SSR_DEFAULT_WIDTH, $affilizz_publication_id ) ?? 'webcomponent';
	}


	/**
	 * Filtreable getter for the Affilizz rendering script location URL.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @since 1.13.0
	 * @return String The rendering script location URL, (potentially) filtered.
	 */
	public static function get_rendering_script_location() {
		return apply_filters( 'affilizz_cdn_rendering_script_location', AFFILIZZ_CDN_RENDERING_SCRIPT_LOCATION );
	}

	/**
	 * Filtreable getter for the Affilizz CDN caching time.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @since 1.13.0
	 * @return String The CDN caching time, (potentially) filtered.
	 */
	public static function get_cdn_cache_time() {
		return apply_filters( 'affilizz_cdn_cache_time', AFFILIZZ_CDN_CACHE_TIME );
	}

	/**
	 * Filtreable getter for the Affilizz CDN caching root path.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @since 1.13.0
	 * @return String The CDN caching root path, (potentially) filtered.
	 */
	public static function get_cdn_cache_root_path() {
		return apply_filters( 'affilizz_cdn_cache_root_path', AFFILIZZ_CDN_CACHE_ROOT_PATH );
	}

	/**
	 * Filtreable getter for the Affilizz CDN caching root URL.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @since 1.13.0
	 * @return String The CDN caching root URL, (potentially) filtered.
	 */
	public static function get_cdn_cache_root_url() {
		return apply_filters( 'affilizz_cdn_cache_root_url', AFFILIZZ_CDN_CACHE_ROOT_URL );
	}

	/**
	 * Filtreable getter for the Affilizz CDN caching root URL.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @since 1.13.0
	 * @return String The CDN caching root URL, (potentially) filtered.
	 */
	public static function get_publications_database_table() {
		return apply_filters( 'affilizz_publications_database_table', AFFILIZZ_PUBLICATIONS_TABLE );
	}

	/**
	 * Filtreable getter for the Affilizz selective enqueuing regular expression.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @since 1.13.0
	 * @return String The selective enqueuing regular expression, (potentially) filtered.
	 */
	public static function get_selective_enqueuing_regex() {
		return apply_filters( 'affilizz_selective_enqueuing_regex', AFFILIZZ_SELECTIVE_ENQUEUING_REGEX, false );
	}

	/**
	 * Returns a list of allowed / needed HTML tags for the configuration pages.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return Array A list of allowed / needed HTML tags for the configuration pages.
	 */
	public static function get_extended_allowed_tags() {
		global $allowedtags;
		return array_merge( $allowedtags, [
			'form' => [ 'action' => true, 'method' => true, 'class' => true, 'id' => true ],
			'select' => [ 'name' => true, 'multiple' => true, 'placeholder' => true, 'autocomplete' => true, 'disabled' => true, 'class' => true, 'id' => true ],
			'input' => [ 'name' => true, 'placeholder' => true, 'autocomplete' => true, 'disabled' => true, 'class' => true, 'id' => true, 'type' => true, 'value' => true, 'selected' => true, 'checked' => true ],
			'textarea' => [ 'name' => true, 'placeholder' => true, 'autocomplete' => true, 'disabled' => true, 'class' => true, 'id' => true ],
			'label' => [ 'for' => true, 'class' => true, 'id' => true ],
			'button' => [ 'type' => true, 'class' => true, 'id' => true, 'name' => true, 'style' => true ],

			'div' => [ 'class' => true, 'id' => true, 'style' => true ],
			'section' => [ 'class' => true, 'id' => true ],
			'nav' => [ 'class' => true, 'id' => true ],
			'footer' => [ 'class' => true, 'id' => true ],
			'header' => [ 'class' => true, 'id' => true ],

			'ol'       => [],
			'ul'       => [],
			'li'       => [ 'class' => true, 'id' => true ],
			'fieldset' => [],
			'legend'   => [],

			'a' => [ 'title' => true, 'href' => true, 'class' => true, 'id' => true ],
			'span' => [ 'class' => true, 'id' => true ],
			'small' => [ 'class' => true, 'id' => true ],
			'strong' => [ 'class' => true, 'id' => true ],

			'p' => [ 'class' => true, 'id' => true ],
			'h2' => [ 'class' => true, 'id' => true ],
			'h3' => [ 'class' => true, 'id' => true ],
			'h4' => [ 'class' => true, 'id' => true ],
			'img' => [ 'src' => true, 'width' => true, 'alt' => true, 'class' => true, 'id' => true ],
			'ol' => [], 'ul' => [ 'class' => true ], 'fieldset' => [], 'legend' => [], 'br' => []
		] );
	}

		/**
	 * Register scripts for the administration area (elementor only).
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function elementor_register_scripts() {
		global $post;

		wp_enqueue_editor();

		// Add custom scripts
		wp_register_script( 'affilizz-elementor-admin', esc_url( AFFILIZZ_URL ) . 'assets/dist/js/admin.js', array(), esc_attr( AFFILIZZ_ASSETS_BUILD_VERSION ) );
		wp_register_script( 'affilizz-elementor-tom-select', esc_url( AFFILIZZ_URL ) . 'assets/vendor/tom-select/tom-select.complete.min.js', array(), esc_attr( AFFILIZZ_ASSETS_BUILD_VERSION ) );
		wp_register_script( 'affilizz-elementor-admin-editor-classic', esc_url( AFFILIZZ_URL ) . 'assets/dist/js/editor.js', array(), esc_attr( AFFILIZZ_ASSETS_BUILD_VERSION ) );

		// Add PHP variables to the JS scripts, for translation or display purposes
		$posted_id = sanitize_text_field( $_GET['post'] ?? ''	 );
		wp_localize_script(
			'affilizz-elementor-',
			'affilizz_admin_l10n',
			$this->get_javascript_l10n()
		);

		// Enqueue the scripts, the WordPress way
		wp_enqueue_script( 'affilizz-elementor-admin' );
		wp_enqueue_script( 'affilizz-elementor-tom-select' );

		wp_enqueue_script( 'jquery-ui-core' );
		wp_enqueue_script( 'jquery-ui-sortable' );
		wp_enqueue_script( 'affilizz-elementor-admin-editor-classic' );
	}

	/**
	 * Register scripts for the administration area (elementor only).
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function elementor_register_styles() {
		// Add custom styles
		wp_register_style( 'affilizz-admin', esc_url( AFFILIZZ_URL ) . 'assets/dist/css/admin.css', '', AFFILIZZ_ASSETS_BUILD_VERSION );
		wp_register_style( 'tom-select', esc_url( AFFILIZZ_URL ) . 'assets/vendor/tom-select/tom-select.default.css', '', AFFILIZZ_ASSETS_BUILD_VERSION );

		// Enqueue the styles, the WordPress way
		wp_enqueue_style( 'affilizz-admin' );
		wp_enqueue_style( 'tom-select' );
	}

	/**
	 * Returns an array of localized strings that can be used in javascript files.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return Array An array of localized strings that can be used in javascript files.
	 */
	public function get_javascript_l10n() {
		return array(
			'plugin' => array(
				'url' => esc_url( AFFILIZZ_URL ),
			),
			'ajax_url'  => admin_url( 'admin-ajax.php' ),
			'modal'     => array(
				'button' => array(
					'insert' => __( 'Insert publication', 'affilizz' ),
					'update' => __( 'Update publication', 'affilizz' )
				),
				'title' => array(
					'insert' => __( 'Insert affiliate publication', 'affilizz' ),
					'update' => __( 'Update / edit affiliate publication', 'affilizz' )
				),
				'overtitle' => array(
					'insert' => __( 'Affilizz affiliate content', 'affilizz' ),
					'update' => __( 'Affilizz affiliate content', 'affilizz' )
				),
				'recent' => __( 'Recent', 'affilizz' ),
				'list' => array(
					'remove' => __( 'Remove this item', 'affilizz' ),
					'emptyPublicationContentName' => __( 'Undefined publication content', 'affilizz' ),
				),
				'messages' => array(
					'type' => array(
						'error' => __( 'Error', 'affilizz' ),
						'information' => __( 'Information', 'affilizz' ),
						'warning' => __( 'Error', 'affilizz' ),
						'success' => __( 'Success', 'affilizz' )
					),
					'missingBookmark' => array(
						'title' => __( 'You cannot insert content in text mode', 'affilizz' ),
						'overtitle' => __( 'Cannot insert content', 'affilizz' ),
						'content' => __( 'Please switch to the visual editor.', 'affilizz' )
					),
				),
				'configuration' => array(
					'media' => ( ! empty( get_option( 'affilizz_media' ) ) ? get_option( 'affilizz_media' ) : false )
				)
			),
			'float' => array(
				'button' => array(
					'insert' => __( 'Insert publication', 'affilizz' )
				),
			),
			'constants' => array(
				'plugin_url' => esc_url( AFFILIZZ_URL ),
				'loading' => __( 'Loading…', 'affilizz' ),
				'fetching' => __( 'Fetching content…', 'affilizz' ),
				'next' => __( 'Next', 'affilizz' ),
				'entities' => __( 'Entities', 'affilizz' ),
				'media' => __( 'Media', 'affilizz' ),
				'channel' => __( 'Channel', 'affilizz' ),
				'types' => array(
					'default' => _x( 'Publication', 'Affilizz content type', 'affilizz' ),
					'link' => _x( 'Link', 'Affilizz content type', 'affilizz' ),
					'box' => _x( 'Comparison table', 'Affilizz content type', 'affilizz' ),
					'card' => _x( 'Card', 'Affilizz content type', 'affilizz' ),
					'cta' => _x( 'Button', 'Affilizz content type', 'affilizz' ),
					'carousel' => _x( 'Carousel', 'Affilizz content type', 'affilizz' )
				),
				'urls'      => array(
					'create' => esc_url( AFFILIZZ_CREATE_PUBLICATION_URL ),
					'edit'   => esc_url( AFFILIZZ_EDIT_PUBLICATION_URL )
				)
			),
			'variables' => array(
				'current_post' => ( ! empty( $post ) && ! empty( $post->ID )
					? $post->ID
					: ( ! empty( $posted_id ) ? $posted_id : 0 )
				),
				'current_user' => get_current_user_id() ?: 0,
			)
		);
	}
}

Core::get_instance();