<?php
/**
 * Navora Elementor Widget definition.
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Navora_Elementor_Widget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 */
	public function get_name() {
		return 'navora_menu';
	}

	/**
	 * Get widget title.
	 */
	public function get_title() {
		return esc_html__( 'Navora Menu', 'navora' );
	}

	/**
	 * Get widget icon.
	 */
	public function get_icon() {
		return 'eicon-nav-menu';
	}

	/**
	 * Get widget categories.
	 */
	public function get_categories() {
		return array( 'general' );
	}

	/**
	 * Get widget keywords.
	 */
	public function get_keywords() {
		return array( 'navora', 'menu', 'navigation', 'health', 'medical', 'header', 'navbar' );
	}

	/**
	 * Check if widget has inner wrapper (Elementor 3.x/4.x DOM optimization compatibility).
	 *
	 * @return bool
	 */
	public function has_widget_inner_wrapper(): bool {
		return false;
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {
		$this->start_controls_section(
			'section_content',
			array(
				'label' => esc_html__( 'Navora Info', 'navora' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'navora_settings_info',
			array(
				'type' => \Elementor\Controls_Manager::RAW_HTML,
				'raw'  => sprintf(
					/* translators: %s: url to settings page */
					__( 'Configure menu sources, layout options, colors, and branding details in the <a href="%s" target="_blank" style="color: #0d9488; font-weight: 600; text-decoration: underline;">Navora Settings Panel</a>.', 'navora' ),
					esc_url( admin_url( 'options-general.php?page=navora' ) )
				),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget frontend output.
	 */
	protected function render() {
		if ( function_exists( 'navora_render_menu' ) ) {
			navora_render_menu();
		}
	}
}
