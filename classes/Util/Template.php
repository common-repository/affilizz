<?php
namespace Affilizz\Util;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

trait Template {
	/**
	 * File existence check, used to load overridden templates.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @param mixed The templates to load, in sequential order
	 * @return string The template path
	 */
	public function include_template( /* ... */ ) {
		return self::include_template_static( func_get_args() );
	}

	/**
	 * Gets the partial template from a whole path, used to get the localized template from a full-path template.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @param $template the full-path template or local template
	 * @return $subtemplate The partial template
	 */
	public static function get_subtemplate( $template = '' ) {
		$subtemplate = $template;

		if ( stripos( $template, WP_CONTENT_DIR ) !== false ) {
			$subtemplate = str_replace( trailingslashit( WP_CONTENT_DIR ), '', $template );
			$has_slash   = stripos( $template, '/' );

			if ( false !== $has_slash ) {
				$subtemplate = trim( substr( $subtemplate, $has_slash ), '/' );
			}
		}

		return $subtemplate;
	}

	/**
	 * File existence boolean check, used to load overridden templates from the theme.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @param mixed The templates to load, in sequential order
	 * @return string The template path
	 */
	public static function include_template_static( $templates = null /* ... */ ) {
		if ( ! is_array( $templates ) ) {
			$templates = func_get_args();
		}

		foreach ( $templates as $template ) {
			if ( file_exists( $template ) ) {
				return $template;
			}
		}

		return false;
	}

	/**
	 * Displays a custom affilizz template in the WordPress fashion
	 * @author Affilizz <wordpress@affilizz.com>
	 * @param $template The affilizz template to load
	 * @return String The template output.
	 */
	public function get_template_part( $template = '', $echo = true ) {
		return self::get_template_part_static( $template, $echo );
	}

	/**
	 * Displays a custom affilizz template in the WordPress fashion, static version
	 * @param $template The affilizz template to load.
	 */
	public static function get_template_part_static( $template = '', $echo = true ) {
		// Start the output buffer
		ob_start();

		// Get the template either from the current theme's directory or the plugin
		$template_to_include = self::include_template_static(
			untrailingslashit( get_stylesheet_directory() ) . '/affilizz/' . self::get_subtemplate( $template ) . '.php',
			untrailingslashit( get_template_directory() ) . '/affilizz/' . self::get_subtemplate( $template ) . '.php',
			$template . '.php',
			AFFILIZZ_DIRECTORY . 'templates/' . self::get_subtemplate( $template ) . '.php'
		);

		if ( $template_to_include ) {
			include $template_to_include;
		}

		$output = ob_get_clean();
		$output = wp_kses( $output, \Affilizz\Core::get_extended_allowed_tags() );

		if ( $echo ) {
			echo wp_kses( $output, \Affilizz\Core::get_extended_allowed_tags() );
		}

		// Return the processed content
		return $output;
	}

	/**
	 * Displays a custom affilizz template with added variables.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @param $template The affilizz template to load.
	 * @param $data The local values to pass to the template.
	 * @return String The rendered template part.
	 */
	public function render_template_part( $template = '', $data = array() ) {
		return self::render_template_part_static( $template, $data );
	}

	/**
	 * Displays a custom affilizz template with added variables, static version.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @param $template The affilizz template to load
	 * @param $data The local values to pass to the template
	 * @return String The rendered template part.
	 */
	public static function render_template_part_static( $template = '', $data = array() ) {
		// Start the output buffer
		ob_start();

		// Get added variables
		extract( $data, EXTR_OVERWRITE );

		// Get the template either from the current theme's directory or the plugin
		// phpcs:ignore
		$template_to_include = self::include_template_static(
			get_stylesheet_directory() . '/affilizz/' . self::get_subtemplate( $template ) . '.php',
			$template . '.php',
			AFFILIZZ_DIRECTORY . 'templates/' . self::get_subtemplate( $template ) . '.php'
		);

		if ( ! empty( $template_to_include ) ) {
			include $template_to_include;
		}

		// Return the processed content
		return ob_get_clean();
	}
}