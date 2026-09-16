<?php
/**
 * Plugin Name: Navora
 * Plugin URI:  https://www.hipnolink.com
 * Description: Modern Navigation, Made Simple. Modern and clean healthcare navigation menus designed for WordPress and Elementor Free.
 * Version:     1.1.3
 * Author:      Hipnolink Digital Team
 * Author URI:  https://www.hipnolink.com
 * License:     GPL2
 * Text Domain: navora
 * Requires PHP: 8.2
 * Requires at least: 7.0
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// System Requirements Check.
function navora_check_system_requirements() {
	global $wp_version;

	$errors = array();

	// Check PHP Version.
	if ( version_compare( PHP_VERSION, '8.2', '<' ) ) {
		$errors[] = sprintf(
			/* translators: %s: current PHP version */
			__( 'PHP version 8.2 or greater is required. Your site is running PHP version %s.', 'navora' ),
			PHP_VERSION
		);
	}

	// Check WordPress Version.
	if ( version_compare( $wp_version, '7.0', '<' ) ) {
		$errors[] = sprintf(
			/* translators: %s: current WordPress version */
			__( 'WordPress version 7.0 or greater is required. Your site is running WordPress version %s.', 'navora' ),
			$wp_version
		);
	}

	if ( ! empty( $errors ) ) {
		// Output Admin Notice.
		add_action( 'admin_notices', function() use ( $errors ) {
			?>
			<div class="error notice is-dismissible">
				<p><strong><?php esc_html_e( 'Navora Navigation error:', 'navora' ); ?></strong></p>
				<ul>
					<?php foreach ( $errors as $error ) : ?>
						<li><?php echo esc_html( $error ); ?></li>
					<?php endforeach; ?>
				</ul>
				<p><?php esc_html_e( 'The plugin has been deactivated.', 'navora' ); ?></p>
			</div>
			<?php
		} );

		// Deactivate plugin.
		require_once ABSPATH . 'wp-admin/includes/plugin.php';
		deactivate_plugins( plugin_basename( __FILE__ ) );
		return false;
	}

	return true;
}

// Run checks before loading.
if ( ! navora_check_system_requirements() ) {
	return;
}

// Define Constants.
define( 'NAVORA_VERSION', '1.1.3' );
define( 'NAVORA_PATH', plugin_dir_path( __FILE__ ) );
define( 'NAVORA_URL', plugin_dir_url( __FILE__ ) );

/**
 * Main Navora Class.
 */
class Navora {

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
		// Load dependencies.
		$this->includes();

		// Hook initialization.
		add_action( 'plugins_loaded', array( $this, 'init' ) );
	}

	/**
	 * Include files.
	 */
	private function includes() {
		require_once NAVORA_PATH . 'includes/class-navora-settings.php';
		require_once NAVORA_PATH . 'includes/class-navora-renderer.php';
		require_once NAVORA_PATH . 'includes/class-navora-elementor.php';
		require_once NAVORA_PATH . 'includes/class-navora-updater.php';
	}

	/**
	 * Initialize plugin modules.
	 */
	public function init() {
		// Initialize settings admin.
		if ( is_admin() ) {
			Navora_Settings::get_instance();
		}

		// Initialize renderer (hooks shortcodes, public assets).
		Navora_Renderer::get_instance();

		// Initialize Elementor integration.
		Navora_Elementor::get_instance();

		// Initialize GitHub Updater (Admin and Cron only to keep frontend lightweight).
		if ( is_admin() || ( defined( 'DOING_CRON' ) && DOING_CRON ) ) {
			new Navora_Updater( NAVORA_PATH . 'navora.php', 'muzzafah-stack', 'navora-plugin' );
		}
	}
}

// Instantiate.
Navora::get_instance();

