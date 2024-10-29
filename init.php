<?php
/**
 * Plugin Name: Affilizz
 * Description: Affilizz allows you to manage all your affiliated content in one place regardless of your activity.
 * Author URI: https://www.affilizz.com
 * Author: Affilizz, Dewizz SAS <wordpress@affilizz.com>
 * Version: 1.14.5
 * Text Domain: affilizz
 */
namespace Affilizz;
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

define( 'AFFILIZZ_VERSION', '1.14.5' );
define( 'AFFILIZZ_ASSETS_BUILD_VERSION', 'VbcqpGgPlOgs1Se8' );

foreach ( array(
	'helpers/functions.php',
	'classes/Core.php',
) as $droplet ) {
	if ( file_exists( dirname( realpath( __FILE__ ) ) . '/' . $droplet ) ) {
		include_once dirname( realpath( __FILE__ ) ) . '/' . $droplet;
	}
}

register_activation_hook( __FILE__, array( 'Affilizz\Install', 'install' ) );
register_uninstall_hook( __FILE__, array( 'Affilizz\Install', 'uninstall' ) );

// Allows translation of the plugin name
__( 'Affilizz', 'affilizz' );
__( 'Affilizz allows you to manage all your affiliated content in one place regardless of your activity.', 'affilizz' );
