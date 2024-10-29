<?php
namespace Affilizz;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Page {
	use \Affilizz\Util\Template, \Affilizz\Util\Generic;

	// Instance variables
	public $available_fields;
	public $mandatory_fields;
	public $template;
	public $title;
	public $page_title;
	public $icon;
	public $slug;
	public $description;
	public $parent;
	public $is_child;
	public $capabilities;
	public $screen_name;
	public $in_menu;

	/**
	 * Adds the menu entry for the current Page class.
	 * @param $menu_title The title of the page or sub-page.
	 * @param $page_slug The variable in the URL to access this page.
	 * @param $icon The Dashicon used to display this page.
	 * @param $template The template to display.
	 * @param $child The boolean check that we are in a parent or a child situation.
	 * @param $parent_page_slug The page to add this child to, if applicable.
	 * @param $capabilities The WordPress capabilities required to load the page.
	 * @param $page_title The title of the page, if different from the menu title.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return An instance of the current class.
	 */
	public function __construct(
		$menu_title,
		$page_slug = 'affilizz',
		$icon = 'admin-settings',
		$template = null,
		$child = false,
		$parent_page_slug = 'affilizz',
		$capabilities = 'manage_options',
		$page_title = ''
	) {
		// If we have no page title, consider the operation void
		if ( is_null( $menu_title ) ) {
			return false;
		}

		// Set the page template
		$this->template     = ( empty( $template ) ? $page_slug : $template );
		$this->icon         = $icon;
		$this->is_child     = $child;
		$this->capabilities = $capabilities;
		$this->title        = $menu_title;
		$this->page_title   = $page_title ?? $this->title;
		$this->parent       = $parent_page_slug;
		$this->slug         = $page_slug;

		$this->in_menu = true;

		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
	}

	public function add_admin_menu() {
		if ( $this->in_menu ) {
			$this->icon = stripos( $this->icon, 'http' ) !== false ? $this->icon : 'dashicons-' . $this->icon;

			// If we register a child page, first add the parent page.
			if ( true === $this->is_child ) {
				if ( empty( $GLOBALS['admin_page_hooks'][$this->parent] ) ) {
					$parent_page = \add_menu_page( $this->title, $this->title, $this->capabilities, $this->parent, array( $this, 'display' ), $this->icon, 3 );
				}

				$page = \add_submenu_page( $this->parent, $this->title, $this->title, $this->capabilities, $this->slug, array( $this, 'display' ) );
			}

			// Else, register the page as a first menu page
			if ( empty( $GLOBALS['admin_page_hooks'][$this->parent] ) ) {
				$page = \add_menu_page( $this->title, $this->title, $this->capabilities, $this->slug, array( $this, 'display' ), $this->icon, 3 );
			}
		};

		$this->screen_name = isset( $page ) ? $page : '';
		$this->register_fields();
	}

	/**
	 * Removes a menu or submenu from the megamenu, directly from its parent class.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return Self The current instance of the class, for fluent chaining.
	 */
	public function remove_from_menu() {
		$this->in_menu = false;

		return $this;
	}

	/**
	 * Register (from the child classes) the data we automatically save.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function register_fields() {
		// Default to an empty array
		if ( ! isset( $this->available_fields ) ) {
			$this->available_fields = array();
		}
		if ( ! isset( $this->mandatory_fields ) ) {
			$this->mandatory_fields = array();
		}
	}

	/**
	 * Adds a description to the page header.
	 * @param $description The page's description.
	 * @return void
	 */
	public function set_description( $description = '' ) {
		if ( ! empty( $description ) ) {
			$this->description = $description;
		}
	}

	/**
	 * Gets the data from the class table.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return String|False The value of the field, false if it is not registered.
	 */
	public function get( $key = '' ) {
		$this->register_fields();

		// Only output data registered with this page class
		return ( in_array( $key, $this->available_fields ) ) ? get_option( $key ) : false;
	}

	/**
	 * Displays the page content.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function display() {
		$errors = array();

		// Save the posted data
		if ( ! empty( $_POST ) && check_admin_referer( 'affilizz-save-page-settings' ) ) {
			$this->save();
		}

		// Move the variables from the database to the view
		$this->register_fields();
		foreach ( $this->available_fields as  $option ) {
			$this->$option = $this->get( $option );
		}

		// Get the template either from the current theme's directory or the plugin
		echo wp_kses( $this->render_template_part( $this->template, array( 'page' => $this ) ), \Affilizz\Core::get_extended_allowed_tags() );
	}

	/**
	 * Saves the settings.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function save() {
		$this->register_fields();

		foreach ( $this->available_fields as $field => $option ) {
			if ( empty( $_POST[$field] ) && $field !== 'affilizz_proxy_uuid' ) {
				delete_option( $option );
			} else {
				update_option( $option, sanitize_option( $option, sanitize_text_field( $_POST[$field] ) ) );
			}
		}
	}

	/**
	 * Displays the header in the WordPress fashion.
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function get_header() {
		echo wp_kses( $this->render_template_part( 'administration/page/partial/header', array( 'page' => $this ) ), \Affilizz\Core::get_extended_allowed_tags() );
	}

	/**
	 * Displays the errors pane in the WordPress fashion
	 * @author Affilizz <wordpress@affilizz.com>
	 * @return void
	 */
	public function get_errors() {
		echo wp_kses( $this->render_template_part( 'administration/error/summary', array( 'page' => $this ) ), \Affilizz\Core::get_extended_allowed_tags() );
	}
}