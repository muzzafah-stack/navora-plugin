<?php
/**
 * Navora Elementor Free Widget Integration.
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Navora_Elementor {

	/**
	 * Instance of the class.
	 */
	private static $instance = null;

	/**
	 * Get Class Instance.
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor.
	 */
	private function __construct() {
		add_action( 'init', array( $this, 'check_elementor_active' ) );
	}

	/**
	 * Check if Elementor is active, then hook widget registration.
	 */
	public function check_elementor_active() {
		if ( did_action( 'elementor/loaded' ) ) {
			add_action( 'elementor/widgets/register', array( $this, 'register_navora_widget' ) );
		}
	}

	/**
	 * Register the widget with Elementor.
	 */
	public function register_navora_widget( $widgets_manager ) {
		// Include Widget Class code dynamically.
		require_once NAVORA_PATH . 'includes/class-navora-elementor-widget.php';
		$widgets_manager->register( new Navora_Elementor_Widget() );
	}
}
