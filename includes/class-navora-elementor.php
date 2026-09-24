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
		if ( did_action( 'elementor/loaded' ) ) {
			$this->init();
		} else {
			add_action( 'elementor/loaded', array( $this, 'init' ) );
		}
	}

	/**
	 * Hook widget registration.
	 */
	public function init() {
		add_action( 'elementor/widgets/register', array( $this, 'register_navora_widget' ) );
	}

	/**
	 * Register the widget with Elementor.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager instance.
	 */
	public function register_navora_widget( $widgets_manager ) {
		// Include Widget Class code dynamically.
		require_once NAVORA_PATH . 'includes/class-navora-elementor-widget.php';
		if ( is_object( $widgets_manager ) && method_exists( $widgets_manager, 'register' ) ) {
			$widgets_manager->register( new Navora_Elementor_Widget() );
		} elseif ( is_object( $widgets_manager ) && method_exists( $widgets_manager, 'register_widget_type' ) ) {
			$widgets_manager->register_widget_type( new Navora_Elementor_Widget() );
		}
	}
}
