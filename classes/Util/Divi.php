<?php
namespace Affilizz\Util;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Divi extends \DiviExtension {
	/**
	 * The constructor.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @param string $name The name.
	 * @param array  $args The arguments to create the extension.
	 */
	public function __construct( $name = 'affilizz', $args = array() ) {
		$this->plugin_dir     = plugin_dir_path( __FILE__ );
		$this->plugin_dir_url = plugin_dir_url( $this->plugin_dir );
		parent::__construct( $name, $args );
	}

	/**
	 * Executes when the ET builder module is loaded.
	 * @author Affilizz <wordpress@affilizz.com>
	 */
	public function hook_et_builder_modules_loaded() {
		$this->hook_et_builder_ready();
	}

	/**
	 * Loads custom modules when the builder is ready.
	 * @author Affilizz <wordpress@affilizz.com>
	 */
	public function hook_et_builder_ready() {
		require_once plugin_dir_path( __FILE__ ) . 'class-videopress-divi-module.php';
		$this->divi_module = new \Affilizz\Editor\Divi();
	}

	/**
	 * Performs initialization tasks.
	 * @author Affilizz <wordpress@affilizz.com>
	 */
	protected function _initialize() { // phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
		DiviExtensions::add( $this );

		$this->_set_debug_mode();
		$this->_set_bundle_dependencies();

		// Register callbacks.
		register_activation_hook( 'affilizz/init.php', array( $this, 'wp_hook_activate' ) );

		add_action( 'et_builder_ready', array( $this, 'hook_et_builder_ready' ), 9 );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_hook_enqueue_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_hook_enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_js_assets' ) );
	}

	/**
	 * Register the extension's js assets.
	 */
	public function register_js_assets() {
		Assets::register_script(
			self::JETPACK_VIDEOPRESS_DIVI_PKG_NAMESPACE,
			'../../build/divi-editor/index.js',
			__FILE__,
			array(
				'in_footer'    => true,
				'css_path'     => '../../build/divi-editor/index.css',
				'textdomain'   => 'jetpack-videopress-pkg',
				'dependencies' => array( 'jquery' ),
			)
		);

		Assets::enqueue_script( self::JETPACK_VIDEOPRESS_DIVI_PKG_NAMESPACE );
	}

	/**
	 * Enqueue frontend stuff.
	 *
	 * @override
	 */
	public function wp_hook_enqueue_scripts() {
	}
}