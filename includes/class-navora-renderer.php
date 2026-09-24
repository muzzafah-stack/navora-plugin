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

		// Preconnect resource hints for Google Fonts.
		add_filter( 'wp_resource_hints', array( $this, 'add_resource_hints' ), 10, 2 );

		// Script loader tag compatibility for Cloudflare Rocket Loader & Cache plugins.
		add_filter( 'script_loader_tag', array( $this, 'filter_script_loader_tag' ), 10, 3 );
	}

	/**
	 * Add preconnect resource hints for Google Fonts CDN.
	 */
	public function add_resource_hints( $urls, $relation_type ) {
		if ( wp_style_is( 'navora-google-fonts', 'queue' ) && 'preconnect' === $relation_type ) {
			$urls[] = array(
				'href'        => 'https://fonts.googleapis.com',
				'crossorigin' => 'anonymous',
			);
			$urls[] = array(
				'href'        => 'https://fonts.gstatic.com',
				'crossorigin' => 'anonymous',
			);
		}
		return $urls;
	}

	/**
	 * Ensure script compatibility with Cloudflare Rocket Loader and cache plugins.
	 */
	public function filter_script_loader_tag( $tag, $handle, $src ) {
		if ( 'navora-public-script' === $handle ) {
			if ( false === strpos( $tag, 'data-cfasync' ) ) {
				$tag = str_replace( '<script ', '<script data-cfasync="false" ', $tag );
			}
		}
		return $tag;
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
		wp_add_inline_style( 'navora-public-style', $this->get_dynamic_css() );

		// JS (Loaded in footer with defer for 0 render blocking).
		wp_enqueue_script(
			'navora-public-script',
			NAVORA_URL . 'assets/js/public.js',
			array(),
			NAVORA_VERSION,
			array(
				'in_footer' => true,
				'strategy'  => 'defer',
			)
		);
	}

	/**
	 * Generate inline CSS custom properties and dynamic breakpoint queries.
	 */
	public function get_dynamic_css() {
		$options    = Navora_Settings::get_options();
		$breakpoint = (int) $options['breakpoint'];

		// Map typography with robust fallback font stacks to avoid FOUT/CLS.
		$font_stack = 'inherit';
		if ( 'Outfit' === $options['font_family'] ) {
			$font_stack = '"Outfit", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
		} elseif ( 'Inter' === $options['font_family'] ) {
			$font_stack = '"Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
		} elseif ( 'Roboto' === $options['font_family'] ) {
			$font_stack = '"Roboto", -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif';
		} elseif ( 'sans-serif' === $options['font_family'] ) {
			$font_stack = 'system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif';
		}

		$primary_color = esc_attr( $options['primary_color'] );
		$text_color    = esc_attr( $options['text_color'] );
		$active_color  = esc_attr( $options['active_color'] );
		$bg_color      = esc_attr( $options['bg_color'] );
		$topbar_bg     = esc_attr( $options['topbar_bg'] );
		$topbar_text   = esc_attr( $options['topbar_text_color'] );
		$topbar_link   = esc_attr( $options['topbar_link_color'] );
		$bp_max        = $breakpoint;
		$bp_min        = $breakpoint + 1;

		return ":root {
			--navora-primary: {$primary_color};
			--navora-text: {$text_color};
			--navora-active: {$active_color};
			--navora-bg: {$bg_color};
			--navora-font: {$font_stack};
			--navora-breakpoint-val: {$breakpoint}px;
			--navora-topbar-bg: {$topbar_bg};
			--navora-topbar-text: {$topbar_text};
			--navora-topbar-link: {$topbar_link};
		}
		@media (min-width: {$bp_min}px) {
			.navora-desktop-menu { display: flex !important; }
			.navora-burger-btn { display: none !important; }
		}
		@media (max-width: {$bp_max}px) {
			.navora-desktop-menu { display: none !important; }
			.navora-burger-btn { display: flex !important; }
			.navora-header-nav .navora-cta-container .navora-cta-btn { display: none !important; }
			.navora-topbar.hide-on-mobile { display: none !important; }
		}";
	}

	/**
	 * Output inline CSS Custom Properties (fallback in head).
	 */
	public function output_dynamic_styles() {
		echo '<style id="navora-custom-vars">' . $this->get_dynamic_css() . '</style>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render Auxiliary / Secondary Bar (Top / Bottom Bar).
	 */
	public function render_topbar( $options ) {
		if ( '1' !== $options['enable_topbar'] ) {
			return;
		}

		$hide_mobile_class = ( '1' === $options['topbar_hide_mobile'] ) ? 'hide-on-mobile' : '';
		$position_class    = 'pos-' . esc_attr( $options['topbar_position'] );
		?>
		<div class="navora-topbar <?php echo esc_attr( $position_class ); ?> <?php echo esc_attr( $hide_mobile_class ); ?>">
			<div class="navora-topbar-inner">
				
				<!-- Left Content -->
				<div class="navora-topbar-left">
					<?php if ( ! empty( $options['topbar_left_menu_id'] ) ) : ?>
						<?php
						wp_nav_menu(
							array(
								'menu'        => $options['topbar_left_menu_id'],
								'container'   => false,
								'menu_class'  => 'navora-topbar-menu',
								'depth'       => 1,
								'fallback_cb' => false,
							)
						);
						?>
					<?php else : ?>
						<?php if ( ! empty( $options['topbar_left_badge'] ) ) : ?>
							<span class="navora-topbar-badge">
								<span class="badge-dot"></span>
								<?php echo esc_html( $options['topbar_left_badge'] ); ?>
							</span>
						<?php endif; ?>

						<?php if ( ! empty( $options['topbar_left_text'] ) ) : ?>
							<?php if ( ! empty( $options['topbar_left_url'] ) ) : ?>
								<a href="<?php echo esc_url( $options['topbar_left_url'] ); ?>" class="navora-topbar-link">
									<?php echo wp_kses_post( $options['topbar_left_text'] ); ?>
								</a>
							<?php else : ?>
								<span class="navora-topbar-text">
									<?php echo wp_kses_post( $options['topbar_left_text'] ); ?>
								</span>
							<?php endif; ?>
						<?php endif; ?>
					<?php endif; ?>
				</div>

				<!-- Right Content -->
				<div class="navora-topbar-right">
					<?php if ( ! empty( $options['topbar_right_menu_id'] ) ) : ?>
						<?php
						wp_nav_menu(
							array(
								'menu'        => $options['topbar_right_menu_id'],
								'container'   => false,
								'menu_class'  => 'navora-topbar-menu',
								'depth'       => 1,
								'fallback_cb' => false,
							)
						);
						?>
					<?php elseif ( ! empty( $options['topbar_right_text'] ) ) : ?>
						<a href="<?php echo esc_url( ! empty( $options['topbar_right_url'] ) ? $options['topbar_right_url'] : '#' ); ?>" class="navora-topbar-action-link">
							<span><?php echo esc_html( $options['topbar_right_text'] ); ?></span>
						</a>
					<?php endif; ?>
				</div>

			</div>
		</div>
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
			$logo_url    = $options['logo_url'];
			$logo_width  = ! empty( $options['logo_width'] ) ? absint( $options['logo_width'] ) : 0;
			$logo_height = ! empty( $options['logo_height'] ) ? absint( $options['logo_height'] ) : 0;
			$logo_id     = ! empty( $options['logo_id'] ) ? absint( $options['logo_id'] ) : 0;

			// If dimensions are missing from stored options, dynamically retrieve them.
			if ( empty( $logo_width ) || empty( $logo_height ) ) {
				$dims = Navora_Settings::get_image_dimensions( $logo_url, $logo_id );
				if ( ! empty( $dims['width'] ) && ! empty( $dims['height'] ) ) {
					$logo_width  = $dims['width'];
					$logo_height = $dims['height'];
				}
			}

			$dim_attrs = '';
			if ( $logo_width > 0 && $logo_height > 0 ) {
				$dim_attrs = ' width="' . esc_attr( $logo_width ) . '" height="' . esc_attr( $logo_height ) . '"';
			}

			$logo_html = '<img src="' . esc_url( $logo_url ) . '"' . $dim_attrs . ' alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" class="navora-logo-img" decoding="async" loading="eager">';
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
			
			<!-- Topbar rendered Above if position is 'above' -->
			<?php
			if ( 'above' === $options['topbar_position'] ) {
				$this->render_topbar( $options );
			}
			?>

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

			<!-- Topbar rendered Below if position is 'below' -->
			<?php
			if ( 'below' === $options['topbar_position'] ) {
				$this->render_topbar( $options );
			}
			?>

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

				<!-- Mobile Auxiliary info banner (if enabled) -->
				<?php if ( '1' === $options['enable_topbar'] && ! empty( $options['topbar_left_text'] ) ) : ?>
					<div class="navora-drawer-topbar-info">
						<?php if ( ! empty( $options['topbar_left_badge'] ) ) : ?>
							<span class="navora-topbar-badge"><?php echo esc_html( $options['topbar_left_badge'] ); ?></span>
						<?php endif; ?>
						<?php if ( ! empty( $options['topbar_left_url'] ) ) : ?>
							<a href="<?php echo esc_url( $options['topbar_left_url'] ); ?>"><?php echo wp_kses_post( $options['topbar_left_text'] ); ?></a>
						<?php else : ?>
							<span><?php echo wp_kses_post( $options['topbar_left_text'] ); ?></span>
						<?php endif; ?>
					</div>
				<?php endif; ?>

				<nav class="navora-mobile-menu" aria-label="<?php esc_attr_e( 'Mobile Navigation', 'navora' ); ?>">
					<?php
					$mobile_menu_args = $menu_args;
					$mobile_menu_args['walker'] = new Navora_Mobile_Nav_Walker();
					wp_nav_menu( $mobile_menu_args );
					?>
				</nav>

				<div class="navora-drawer-footer">
					<?php if ( '1' === $options['enable_topbar'] && ! empty( $options['topbar_right_text'] ) ) : ?>
						<a href="<?php echo esc_url( ! empty( $options['topbar_right_url'] ) ? $options['topbar_right_url'] : '#' ); ?>" class="navora-topbar-action-drawer">
							<?php echo esc_html( $options['topbar_right_text'] ); ?>
						</a>
					<?php endif; ?>

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
 * Custom Mobile Nav Walker for server-side dropdown toggles.
 * Eliminates DOM injection via JS and prevents CLS (Cumulative Layout Shift) when scripts are delayed by cache plugins.
 */
class Navora_Mobile_Nav_Walker extends Walker_Nav_Menu {
	public function start_el( &$output, $data_object, $depth = 0, $args = null, $current_object_id = 0 ) {
		$item_output = '';
		parent::start_el( $item_output, $data_object, $depth, $args, $current_object_id );

		if ( ! empty( $data_object->classes ) && in_array( 'menu-item-has-children', (array) $data_object->classes, true ) ) {
			$toggle_btn = '<button class="navora-submenu-toggle" type="button" aria-label="' . esc_attr__( 'Toggle submenu', 'navora' ) . '" aria-expanded="false"></button>';
			$item_output = preg_replace( '/(<\/a>)/i', '$1' . $toggle_btn, $item_output, 1 );
		}

		$output .= $item_output;
	}
}

/**
 * Public helper function for templates.
 */
function navora_render_menu() {
	Navora_Renderer::get_instance()->render();
}

