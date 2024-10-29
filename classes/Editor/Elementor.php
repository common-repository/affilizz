<?php
namespace Affilizz\Editor;
use \Elementor\Widget_Base;

class Elementor extends \Elementor\Widget_Base {
    // Declare the traits used by this class
	use \Affilizz\Util\Template, \Affilizz\Util\Generic;

	public function get_name() {
		return 'affilizz';
	}

	public function get_title() {
		return esc_html__( 'Affilizz', 'affilizz' );
	}

	public function get_icon() {
		return 'affilizz-icon';
	}

	public function get_categories() {
		return [ 'basic' ];
	}

	public function get_keywords() {
		return [ 'affilizz', 'affiliattion', 'ads', 'block', 'widget', 'publication' ];
	}

	protected function register_controls() {
		// Content Tab Start
		$this->start_controls_section( 'section_title', [
			'label' => esc_html__( 'Configuration', 'elementor-addon' ),
			'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
		] );

		$controls = self::render_template_part_static( trailingslashit( esc_url( AFFILIZZ_DIRECTORY ) ) . 'templates/administration/editor/elementor/controls' );

		$this->add_control(
			'affilizz_controls', [
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw'  => $controls
			]
		);


		$this->end_controls_section();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		echo '———';
	}
}