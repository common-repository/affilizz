<?php

namespace Affilizz\Editor;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Divi extends \ET_Builder_Module {
    public $slug       = 'affilizz';
    public $vb_support = 'on';

    public function __construct() {
        add_action( 'et_builder_ready', array( $this, 'add_custom_divi_module' ) );
    }

    protected $module_credits = array(
        'module_uri' => '',
        'author'     => '',
        'author_uri' => '',
    );

    public function init() {
        $this->name = esc_html__( 'Affilizz', 'affilizz' );
        $this->icon_path = AFFILIZZ_URL . 'assets/dist/images/logo/logo-type-dark-blue.svg';
    }

    public function get_fields() {
        return array(
            'button_text' => array(
                'label'           => esc_html__( 'Affilizz', 'affilizz' ),
                'type'            => 'text',
                'option_category' => 'basic_option',
                'description'     => esc_html__( 'Embed an Affilizz publication.', 'affilizz' ),
                'toggle_slug'     => 'affilizz',
            ),
        );
    }

    public function shortcode_callback( $atts, $content = null, $function_name ) {
        $button_text = $this->shortcode_atts[ 'affilizz' ];

        return sprintf(
            '<div class="affilizz-button">%1$s</div>',
            esc_html( $button_text )
        );
    }

    public function add_custom_divi_module() {
        if ( ! class_exists( 'ET_Builder_Module' ) ) {
            return;
        }
    }
}