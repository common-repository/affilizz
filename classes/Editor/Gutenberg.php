<?php
namespace Affilizz\Editor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Gutenberg {
	/**
	 * Hooks to the default WordPress hooks to enable custom features.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function __construct() {
		// Invite WordPress to register the custom Gutenberg blocks
		add_action( 'init', array( $this, 'register_blocks' ) );
	}

	/**
	 * Registers the Affilizz publication gutenberg block
	 */
	public function register_blocks() {
		register_block_type( AFFILIZZ_DIRECTORY . 'assets/dist/blocks/publication' );
	}
}