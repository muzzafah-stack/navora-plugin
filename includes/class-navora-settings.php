<?php
/**
 * Navora Admin Settings Class.
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Navora_Settings {

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
		// Register menu.
		add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
		// Register settings.
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		// Enqueue scripts on settings page.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Add Admin Menu.
	 */
	public function add_admin_menu() {
		add_options_page(
			__( 'Navora Navigation Settings', 'navora' ),
			'Navora Navigation',
			'manage_options',
			'navora',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register Options.
	 */
	public function register_settings() {
		register_setting(
			'navora_settings_group',
			'navora_options',
			array(
				'sanitize_callback' => array( $this, 'sanitize_options' ),
				'default'           => $this->get_default_options(),
			)
		);
	}

	/**
	 * Get Default Settings.
	 */
	public function get_default_options() {
		return array(
			'menu_id'         => '',
			'layout'          => 'clean-clinical',
			'logo_url'        => '',
			'primary_color'   => '#0d9488', // Teal
			'text_color'      => '#1f2937', // Dark Slate
			'active_color'    => '#0f766e', // Hover Teal
			'bg_color'        => '#ffffff', // Background White
			'font_family'     => 'Outfit',  // Standard 2026 Modern Health Font
			'breakpoint'      => '768',
			'sticky'          => '1',
			'cta_text'        => 'Book Appointment',
			'cta_url'         => '#',
			'mobile_behavior' => 'slide-right',
		);
	}

	/**
	 * Retrieve saved options merged with defaults.
	 */
	public static function get_options() {
		$saved    = get_option( 'navora_options', array() );
		$defaults = ( new self() )->get_default_options();
		return wp_parse_args( $saved, $defaults );
	}

	/**
	 * Sanitize Settings Input.
	 */
	public function sanitize_options( $input ) {
		$output = array();

		$output['menu_id']         = isset( $input['menu_id'] ) ? sanitize_text_field( $input['menu_id'] ) : '';
		$output['layout']          = isset( $input['layout'] ) && in_array( $input['layout'], array( 'clean-clinical', 'modern-wellness' ), true ) ? $input['layout'] : 'clean-clinical';
		$output['logo_url']        = isset( $input['logo_url'] ) ? esc_url_raw( $input['logo_url'] ) : '';
		$output['primary_color']   = isset( $input['primary_color'] ) ? sanitize_hex_color( $input['primary_color'] ) : '#0d9488';
		$output['text_color']      = isset( $input['text_color'] ) ? sanitize_hex_color( $input['text_color'] ) : '#1f2937';
		$output['active_color']    = isset( $input['active_color'] ) ? sanitize_hex_color( $input['active_color'] ) : '#0f766e';
		$output['bg_color']        = isset( $input['bg_color'] ) ? sanitize_hex_color( $input['bg_color'] ) : '#ffffff';
		$output['font_family']     = isset( $input['font_family'] ) ? sanitize_text_field( $input['font_family'] ) : 'Outfit';
		$output['breakpoint']      = isset( $input['breakpoint'] ) ? absint( $input['breakpoint'] ) : 768;
		$output['sticky']          = isset( $input['sticky'] ) ? '1' : '0';
		$output['cta_text']        = isset( $input['cta_text'] ) ? sanitize_text_field( $input['cta_text'] ) : '';
		$output['cta_url']         = isset( $input['cta_url'] ) ? esc_url_raw( $input['cta_url'] ) : '';
		$output['mobile_behavior'] = isset( $input['mobile_behavior'] ) && in_array( $input['mobile_behavior'], array( 'slide-right', 'slide-left', 'fade' ), true ) ? $input['mobile_behavior'] : 'slide-right';

		return $output;
	}

	/**
	 * Enqueue Admin Scripts/Styles.
	 */
	public function enqueue_admin_assets( $hook_suffix ) {
		if ( 'settings_page_navora' !== $hook_suffix ) {
			return;
		}

		// WordPress Core Assets for media library and color picker.
		wp_enqueue_media();
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );

		// Custom Admin Assets.
		wp_enqueue_style( 'navora-admin-style', NAVORA_URL . 'assets/css/admin.css', array(), NAVORA_VERSION );
		wp_enqueue_script( 'navora-admin-script', NAVORA_URL . 'assets/js/admin.js', array( 'jquery', 'wp-color-picker' ), NAVORA_VERSION, true );
	}

	/**
	 * Render settings page.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$options = self::get_options();
		$menus   = wp_get_nav_menus();
		?>
		<div class="wrap navora-settings-wrap">
			<div class="navora-header">
				<div class="navora-title-area">
					<h1>Navora</h1>
					<p class="tagline">Modern Navigation, Made Simple. v<?php echo esc_html( NAVORA_VERSION ); ?></p>
				</div>
				<div class="navora-author-badge">
					By <a href="https://www.hipnolink.com" target="_blank" rel="noopener">Hipnolink Digital Team</a>
				</div>
			</div>

			<form method="post" action="options.php" class="navora-admin-form">
				<?php
				settings_fields( 'navora_settings_group' );
				?>

				<div class="navora-grid">
					<!-- Main Settings Panel -->
					<div class="navora-card main-settings">
						<h2><span class="dashicons dashicons-admin-settings"></span> <?php esc_html_e( 'General Settings', 'navora' ); ?></h2>

						<!-- Navigation Menu Selection -->
						<div class="navora-field">
							<label for="navora_menu_id"><?php esc_html_e( 'Select Menu Source', 'navora' ); ?></label>
							<select id="navora_menu_id" name="navora_options[menu_id]">
								<option value=""><?php esc_html_e( '-- Choose a WordPress Menu --', 'navora' ); ?></option>
								<?php foreach ( $menus as $menu ) : ?>
									<option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( $options['menu_id'], $menu->term_id ); ?>>
										<?php echo esc_html( $menu->name ); ?>
									</option>
								<?php endforeach; ?>
							</select>
							<p class="description"><?php esc_html_e( 'Create or manage menus under Appearance > Menus.', 'navora' ); ?></p>
						</div>

						<!-- Layout Selection -->
						<div class="navora-field">
							<label><?php esc_html_e( 'Visual Layout', 'navora' ); ?></label>
							<div class="navora-layout-options">
								<label class="navora-radio-card">
									<input type="radio" name="navora_options[layout]" value="clean-clinical" <?php checked( $options['layout'], 'clean-clinical' ); ?>>
									<span class="radio-label">
										<strong><?php esc_html_e( 'Clean Clinical', 'navora' ); ?></strong>
										<span class="desc"><?php esc_html_e( 'Centered logo, clean borders, professional grid.', 'navora' ); ?></span>
									</span>
								</label>
								<label class="navora-radio-card">
									<input type="radio" name="navora_options[layout]" value="modern-wellness" <?php checked( $options['layout'], 'modern-wellness' ); ?>>
									<span class="radio-label">
										<strong><?php esc_html_e( 'Modern Wellness', 'navora' ); ?></strong>
										<span class="desc"><?php esc_html_e( 'Floating layout, soft borders, modern typography focus.', 'navora' ); ?></span>
									</span>
								</label>
							</div>
						</div>

						<!-- Logo Customization -->
						<div class="navora-field">
							<label for="navora_logo_url"><?php esc_html_e( 'Navigation Logo', 'navora' ); ?></label>
							<div class="logo-upload-group">
								<input type="text" id="navora_logo_url" name="navora_options[logo_url]" value="<?php echo esc_url( $options['logo_url'] ); ?>" class="regular-text">
								<button type="button" class="button navora-upload-button" id="navora_upload_logo_btn"><?php esc_html_e( 'Upload/Select', 'navora' ); ?></button>
							</div>
							<div id="navora-logo-preview" class="logo-preview-wrapper" style="<?php echo empty( $options['logo_url'] ) ? 'display:none;' : ''; ?>">
								<img src="<?php echo esc_url( $options['logo_url'] ); ?>" alt="Logo preview">
								<button type="button" class="navora-remove-logo" id="navora_remove_logo_btn">&times;</button>
							</div>
						</div>

						<!-- Sticky Menu -->
						<div class="navora-field checkbox-field">
							<label for="navora_sticky">
								<input type="checkbox" id="navora_sticky" name="navora_options[sticky]" value="1" <?php checked( $options['sticky'], '1' ); ?>>
								<span><?php esc_html_e( 'Enable Sticky Header', 'navora' ); ?></span>
							</label>
							<p class="description"><?php esc_html_e( 'Keep navigation menu fixed at the top during scrolling.', 'navora' ); ?></p>
						</div>

						<!-- Mobile Menu Options -->
						<div class="navora-field">
							<label for="navora_mobile_behavior"><?php esc_html_e( 'Mobile Menu Style', 'navora' ); ?></label>
							<select id="navora_mobile_behavior" name="navora_options[mobile_behavior]">
								<option value="slide-right" <?php selected( $options['mobile_behavior'], 'slide-right' ); ?>><?php esc_html_e( 'Slide out from Right', 'navora' ); ?></option>
								<option value="slide-left" <?php selected( $options['mobile_behavior'], 'slide-left' ); ?>><?php esc_html_e( 'Slide out from Left', 'navora' ); ?></option>
								<option value="fade" <?php selected( $options['mobile_behavior'], 'fade' ); ?>><?php esc_html_e( 'Fade Overlay', 'navora' ); ?></option>
							</select>
						</div>
					</div>

					<!-- Styling Panel -->
					<div class="navora-card style-settings">
						<h2><span class="dashicons dashicons-art"></span> <?php esc_html_e( 'Design & Branding', 'navora' ); ?></h2>

						<!-- Color Customizer -->
						<div class="navora-field">
							<label for="navora_primary_color"><?php esc_html_e( 'Primary Color (Brand/CTA)', 'navora' ); ?></label>
							<input type="text" id="navora_primary_color" name="navora_options[primary_color]" value="<?php echo esc_attr( $options['primary_color'] ); ?>" class="navora-color-picker">
						</div>

						<div class="navora-field">
							<label for="navora_text_color"><?php esc_html_e( 'Menu Link Color', 'navora' ); ?></label>
							<input type="text" id="navora_text_color" name="navora_options[text_color]" value="<?php echo esc_attr( $options['text_color'] ); ?>" class="navora-color-picker">
						</div>

						<div class="navora-field">
							<label for="navora_active_color"><?php esc_html_e( 'Hover / Active Color', 'navora' ); ?></label>
							<input type="text" id="navora_active_color" name="navora_options[active_color]" value="<?php echo esc_attr( $options['active_color'] ); ?>" class="navora-color-picker">
						</div>

						<div class="navora-field">
							<label for="navora_bg_color"><?php esc_html_e( 'Header Background', 'navora' ); ?></label>
							<input type="text" id="navora_bg_color" name="navora_options[bg_color]" value="<?php echo esc_attr( $options['bg_color'] ); ?>" class="navora-color-picker">
						</div>

						<!-- Typography / Font family -->
						<div class="navora-field">
							<label for="navora_font_family"><?php esc_html_e( 'Font Family', 'navora' ); ?></label>
							<select id="navora_font_family" name="navora_options[font_family]">
								<option value="Outfit" <?php selected( $options['font_family'], 'Outfit' ); ?>>Outfit (Modern/Clean)</option>
								<option value="Inter" <?php selected( $options['font_family'], 'Inter' ); ?>>Inter (Professional)</option>
								<option value="Roboto" <?php selected( $options['font_family'], 'Roboto' ); ?>>Roboto (Standard)</option>
								<option value="sans-serif" <?php selected( $options['font_family'], 'sans-serif' ); ?>>System Sans-Serif</option>
								<option value="inherit" <?php selected( $options['font_family'], 'inherit' ); ?>><?php esc_html_e( 'Inherit from Theme', 'navora' ); ?></option>
							</select>
						</div>

						<!-- Mobile Breakpoint -->
						<div class="navora-field">
							<label for="navora_breakpoint"><?php esc_html_e( 'Mobile Breakpoint (px)', 'navora' ); ?></label>
							<input type="number" id="navora_breakpoint" name="navora_options[breakpoint]" value="<?php echo esc_attr( $options['breakpoint'] ); ?>" class="small-text"> px
						</div>

						<hr>

						<!-- CTA Settings -->
						<h3><?php esc_html_e( 'Header CTA Action Button', 'navora' ); ?></h3>

						<div class="navora-field">
							<label for="navora_cta_text"><?php esc_html_e( 'Button Text', 'navora' ); ?></label>
							<input type="text" id="navora_cta_text" name="navora_options[cta_text]" value="<?php echo esc_attr( $options['cta_text'] ); ?>" placeholder="e.g. Book Appointment">
						</div>

						<div class="navora-field">
							<label for="navora_cta_url"><?php esc_html_e( 'Button URL / Link', 'navora' ); ?></label>
							<input type="text" id="navora_cta_url" name="navora_options[cta_url]" value="<?php echo esc_attr( $options['cta_url'] ); ?>" placeholder="e.g. /appointment">
						</div>
					</div>
				</div>

				<div class="navora-submit-bar">
					<?php submit_button( __( 'Save Settings', 'navora' ), 'primary', 'submit', false ); ?>
				</div>
			</form>

			<div class="navora-info-footer">
				<h3><?php esc_html_e( 'How to Display this Menu', 'navora' ); ?></h3>
				<p>
					1. **Shortcode**: Paste `[navora_menu]` anywhere in your post, page, or widget content.<br>
					2. **Elementor Free**: Open Elementor, search for the **Navora Menu** widget under the general category, and drag it into your template.<br>
					3. **PHP Theme Integration**: Place <code>&lt;?php if ( function_exists( 'navora_render_menu' ) ) { navora_render_menu(); } ?&gt;</code> in your theme's <code>header.php</code>.
				</p>
			</div>
		</div>
		<?php
	}
}
