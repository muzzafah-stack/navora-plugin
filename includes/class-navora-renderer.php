<?php
/**
 * Navora Frontend Renderer Class.
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Navora_Renderer {

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
		// Register Shortcode.
		add_shortcode( 'navora_menu', array( $this, 'render_shortcode' ) );

		// Enqueue scripts/styles.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_public_assets' ) );

		// Output dynamic inline styles.
		add_action( 'wp_head', array( $this, 'output_dynamic_styles' ) );
	}

	/**
	 * Enqueue assets.
	 */
	public function enqueue_public_assets() {
		$options = Navora_Settings::get_options();

		// Enqueue Google Fonts if Outfit, Inter, or Roboto are selected.
		if ( in_array( $options['font_family'], array( 'Outfit', 'Inter', 'Roboto' ), true ) ) {
			$font_query = array();
			if ( 'Outfit' === $options['font_family'] ) {
				$font_query[] = 'family=Outfit:wght@400;500;600;700';
			} elseif ( 'Inter' === $options['font_family'] ) {
				$font_query[] = 'family=Inter:wght@400;500;600;700';
			} elseif ( 'Roboto' === $options['font_family'] ) {
				$font_query[] = 'family=Roboto:wght@400;500;700';
			}
			$font_url = 'https://fonts.googleapis.com/css2?' . implode( '&', $font_query ) . '&display=swap';
			wp_enqueue_style( 'navora-google-fonts', $font_url, array(), null );
		}

		// CSS.
		wp_enqueue_style( 'navora-public-style', NAVORA_URL . 'assets/css/public.css', array(), NAVORA_VERSION );

		// JS.
		wp_enqueue_script( 'navora-public-script', NAVORA_URL . 'assets/js/public.js', array(), NAVORA_VERSION, true );
	}

	/**
	 * Output inline CSS Custom Properties (caching-safe design).
	 */
	public function output_dynamic_styles() {
		$options    = Navora_Settings::get_options();
		$breakpoint = (int) $options['breakpoint'];

		// Map typography.
		$font_stack = 'inherit';
		if ( 'Outfit' === $options['font_family'] ) {
			$font_stack = '"Outfit", sans-serif';
		} elseif ( 'Inter' === $options['font_family'] ) {
			$font_stack = '"Inter", sans-serif';
		} elseif ( 'Roboto' === $options['font_family'] ) {
			$font_stack = '"Roboto", sans-serif';
		} elseif ( 'sans-serif' === $options['font_family'] ) {
			$font_stack = 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
		}
		?>
		<style id="navora-custom-vars">
			:root {
				--navora-primary: <?php echo esc_html( $options['primary_color'] ); ?>;
				--navora-text: <?php echo esc_html( $options['text_color'] ); ?>;
				--navora-active: <?php echo esc_html( $options['active_color'] ); ?>;
				--navora-bg: <?php echo esc_html( $options['bg_color'] ); ?>;
				--navora-font: <?php echo $font_stack; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
				--navora-breakpoint-val: <?php echo esc_html( $breakpoint ); ?>px;
			}

			/* CSS variables cannot be used in media query declarations, so we render the breakpoint dynamically */
			@media (min-width: <?php echo esc_html( $breakpoint + 1 ); ?>px) {
				.navora-desktop-menu {
					display: flex !important;
				}
				.navora-burger-btn {
					display: none !important;
				}
			}
			@media (max-width: <?php echo esc_html( $breakpoint ); ?>px) {
				.navora-desktop-menu {
					display: none !important;
				}
				.navora-burger-btn {
					display: flex !important;
				}
				.navora-header-nav .navora-cta-container .navora-cta-btn {
					display: none !important;
				}
			}
		</style>
		<?php
	}

	/**
	 * Shortcode callback.
	 */
	public function render_shortcode( $atts ) {
		ob_start();
		$this->render();
		return ob_get_clean();
	}

	/**
	 * Main Render Function.
	 */
	public function render() {
		$options = Navora_Settings::get_options();

		// Logo source.
		$logo_html = '';
		if ( ! empty( $options['logo_url'] ) ) {
			$logo_html = '<img src="' . esc_url( $options['logo_url'] ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" class="navora-logo-img">';
		} else {
			$logo_html = '<span class="navora-site-title">' . esc_html( get_bloginfo( 'name' ) ) . '</span>';
		}

		// Sticky class.
		$sticky_class = ( '1' === $options['sticky'] ) ? 'navora-sticky' : '';

		// Nav menu parameters.
		$menu_args = array(
			'container'      => false,
			'menu_class'     => 'navora-menu-list',
			'fallback_cb'    => array( $this, 'fallback_menu' ),
			'items_wrap'     => '<ul id="%1$s" class="%2$s">%3$s</ul>',
			'depth'          => 3,
		);

		if ( ! empty( $options['menu_id'] ) ) {
			$menu_args['menu'] = $options['menu_id'];
		}

		?>
		<header class="navora-header-nav layout-<?php echo esc_attr( $options['layout'] ); ?> <?php echo esc_attr( $sticky_class ); ?>">
			<div class="navora-nav-container">
				
				<!-- Logo -->
				<div class="navora-logo">
					<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
						<?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</a>
				</div>

				<!-- Desktop Menu -->
				<nav class="navora-desktop-menu" aria-label="<?php esc_attr_e( 'Desktop Navigation', 'navora' ); ?>">
					<?php wp_nav_menu( $menu_args ); ?>
				</nav>

				<!-- CTA and Burger Menu Button -->
				<div class="navora-cta-container">
					<?php if ( ! empty( $options['cta_text'] ) ) : ?>
						<a href="<?php echo esc_url( $options['cta_url'] ); ?>" class="navora-cta-btn">
							<?php echo esc_html( $options['cta_text'] ); ?>
						</a>
					<?php endif; ?>

					<button class="navora-burger-btn" aria-label="<?php esc_attr_e( 'Toggle Menu', 'navora' ); ?>" aria-expanded="false" aria-controls="navora-mobile-drawer">
						<span class="burger-line"></span>
						<span class="burger-line"></span>
						<span class="burger-line"></span>
					</button>
				</div>

			</div>

			<!-- Mobile Drawer -->
			<div id="navora-mobile-drawer" class="navora-mobile-drawer behavior-<?php echo esc_attr( $options['mobile_behavior'] ); ?>">
				<div class="navora-drawer-header">
					<div class="navora-drawer-logo">
						<a href="<?php echo esc_url( home_url( '/' ) ); ?>">
							<?php echo $logo_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</a>
					</div>
					<button class="navora-close-btn" aria-label="<?php esc_attr_e( 'Close Menu', 'navora' ); ?>">&times;</button>
				</div>

				<nav class="navora-mobile-menu" aria-label="<?php esc_attr_e( 'Mobile Navigation', 'navora' ); ?>">
					<?php wp_nav_menu( $menu_args ); ?>
				</nav>

				<div class="navora-drawer-footer">
					<?php if ( ! empty( $options['cta_text'] ) ) : ?>
						<a href="<?php echo esc_url( $options['cta_url'] ); ?>" class="navora-cta-btn navora-cta-drawer">
							<?php echo esc_html( $options['cta_text'] ); ?>
						</a>
					<?php endif; ?>
				</div>
			</div>
			
			<div class="navora-drawer-overlay"></div>
		</header>
		<?php
	}

	/**
	 * Fallback menu when no WordPress menu is assigned.
	 */
	public function fallback_menu() {
		echo '<ul class="navora-menu-list fallback-menu">';
		echo '<li class="menu-item"><a href="' . esc_url( admin_url( 'nav-menus.php' ) ) . '">' . esc_html__( 'Create / Select a Menu in WordPress', 'navora' ) . '</a></li>';
		echo '</ul>';
	}
}

/**
 * Public helper function for templates.
 */
function navora_render_menu() {
	Navora_Renderer::get_instance()->render();
}
