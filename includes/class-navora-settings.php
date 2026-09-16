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
		// Purge cache plugins when settings are saved.
		add_action( 'update_option_navora_options', array( $this, 'purge_all_caches' ), 10, 0 );
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
			'menu_id'              => '',
			'layout'               => 'clean-clinical',
			'logo_url'             => '',
			'primary_color'        => '#0d9488', // Teal
			'text_color'           => '#1f2937', // Dark Slate
			'active_color'         => '#0f766e', // Hover Teal
			'bg_color'             => '#ffffff', // Background White
			'font_family'          => 'Outfit',  // Standard 2026 Modern Health Font
			'breakpoint'           => '768',
			'sticky'               => '1',
			'cta_text'             => 'Book Appointment',
			'cta_url'              => '#',
			'mobile_behavior'      => 'slide-right',

			// Secondary / Auxiliary Bar (Top/Bottom Bar) Options
			'enable_topbar'        => '0',
			'topbar_position'      => 'above', // 'above' or 'below'
			'topbar_bg'            => '#f0fdfa',
			'topbar_text_color'    => '#334155',
			'topbar_link_color'    => '#0d9488',
			'topbar_left_badge'    => '',
			'topbar_left_text'     => '📞 Info Pelatihan',
			'topbar_left_url'      => '',
			'topbar_left_menu_id'  => '',
			'topbar_right_text'    => 'Join with Us ›››',
			'topbar_right_url'     => '#',
			'topbar_right_menu_id' => '',
			'topbar_hide_mobile'   => '0',
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

		// Topbar Sanitization
		$output['enable_topbar']        = isset( $input['enable_topbar'] ) ? '1' : '0';
		$output['topbar_position']      = isset( $input['topbar_position'] ) && in_array( $input['topbar_position'], array( 'above', 'below' ), true ) ? $input['topbar_position'] : 'above';
		$output['topbar_bg']            = isset( $input['topbar_bg'] ) ? sanitize_hex_color( $input['topbar_bg'] ) : '#f0fdfa';
		$output['topbar_text_color']    = isset( $input['topbar_text_color'] ) ? sanitize_hex_color( $input['topbar_text_color'] ) : '#334155';
		$output['topbar_link_color']    = isset( $input['topbar_link_color'] ) ? sanitize_hex_color( $input['topbar_link_color'] ) : '#0d9488';
		$output['topbar_left_badge']    = isset( $input['topbar_left_badge'] ) ? sanitize_text_field( $input['topbar_left_badge'] ) : '';
		$output['topbar_left_text']     = isset( $input['topbar_left_text'] ) ? wp_kses_post( $input['topbar_left_text'] ) : '';
		$output['topbar_left_url']      = isset( $input['topbar_left_url'] ) ? esc_url_raw( $input['topbar_left_url'] ) : '';
		$output['topbar_left_menu_id']  = isset( $input['topbar_left_menu_id'] ) ? sanitize_text_field( $input['topbar_left_menu_id'] ) : '';
		$output['topbar_right_text']    = isset( $input['topbar_right_text'] ) ? sanitize_text_field( $input['topbar_right_text'] ) : '';
		$output['topbar_right_url']     = isset( $input['topbar_right_url'] ) ? esc_url_raw( $input['topbar_right_url'] ) : '';
		$output['topbar_right_menu_id'] = isset( $input['topbar_right_menu_id'] ) ? sanitize_text_field( $input['topbar_right_menu_id'] ) : '';
		$output['topbar_hide_mobile']   = isset( $input['topbar_hide_mobile'] ) ? '1' : '0';

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
							<label for="navora_menu_id"><?php esc_html_e( 'Select Main Menu Source', 'navora' ); ?></label>
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
										<span class="desc"><?php esc_html_e( 'Classic layout, seamless hero blend, professional grid.', 'navora' ); ?></span>
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

				<!-- Auxiliary / Secondary Bar Panel (Top Bar / Bottom Bar) -->
				<div class="navora-card secondary-bar-settings" style="margin-bottom: 25px;">
					<h2><span class="dashicons dashicons-menu-alt2"></span> <?php esc_html_e( 'Auxiliary / Secondary Menu Bar (Top Bar / Sub Bar)', 'navora' ); ?></h2>
					<p class="description" style="margin-bottom: 20px;">
						<?php esc_html_e( 'Add an extra information/navigation bar above or below your main header for announcements, contact info, training details, badges, or secondary links.', 'navora' ); ?>
					</p>

					<div class="navora-field checkbox-field" style="background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
						<label for="navora_enable_topbar">
							<input type="checkbox" id="navora_enable_topbar" name="navora_options[enable_topbar]" value="1" <?php checked( $options['enable_topbar'], '1' ); ?>>
							<span><strong><?php esc_html_e( 'Enable Auxiliary / Secondary Bar', 'navora' ); ?></strong></span>
						</label>
					</div>

					<div class="navora-topbar-controls" style="<?php echo ( '1' !== $options['enable_topbar'] ) ? 'display:none;' : ''; ?>">
						<div class="navora-grid" style="margin-top: 20px; margin-bottom: 0;">
							<!-- Position & Appearance -->
							<div class="navora-subcard" style="background:#fff; padding:15px; border:1px solid #e2e8f0; border-radius:8px;">
								<h3 style="margin-top:0; font-size:14px; color:#0f172a;"><?php esc_html_e( 'Bar Position & Colors', 'navora' ); ?></h3>

								<div class="navora-field">
									<label for="navora_topbar_position"><?php esc_html_e( 'Bar Position', 'navora' ); ?></label>
									<select id="navora_topbar_position" name="navora_options[topbar_position]">
										<option value="above" <?php selected( $options['topbar_position'], 'above' ); ?>><?php esc_html_e( 'Top (Above Main Header)', 'navora' ); ?></option>
										<option value="below" <?php selected( $options['topbar_position'], 'below' ); ?>><?php esc_html_e( 'Bottom (Below Main Header / Sub-Bar)', 'navora' ); ?></option>
									</select>
								</div>

								<div class="navora-field">
									<label for="navora_topbar_bg"><?php esc_html_e( 'Bar Background Color', 'navora' ); ?></label>
									<input type="text" id="navora_topbar_bg" name="navora_options[topbar_bg]" value="<?php echo esc_attr( $options['topbar_bg'] ); ?>" class="navora-color-picker">
								</div>

								<div class="navora-field">
									<label for="navora_topbar_text_color"><?php esc_html_e( 'Text Color', 'navora' ); ?></label>
									<input type="text" id="navora_topbar_text_color" name="navora_options[topbar_text_color]" value="<?php echo esc_attr( $options['topbar_text_color'] ); ?>" class="navora-color-picker">
								</div>

								<div class="navora-field">
									<label for="navora_topbar_link_color"><?php esc_html_e( 'Link / Accent Color', 'navora' ); ?></label>
									<input type="text" id="navora_topbar_link_color" name="navora_options[topbar_link_color]" value="<?php echo esc_attr( $options['topbar_link_color'] ); ?>" class="navora-color-picker">
								</div>

								<div class="navora-field checkbox-field">
									<label for="navora_topbar_hide_mobile">
										<input type="checkbox" id="navora_topbar_hide_mobile" name="navora_options[topbar_hide_mobile]" value="1" <?php checked( $options['topbar_hide_mobile'], '1' ); ?>>
										<span><?php esc_html_e( 'Hide bar on mobile screens', 'navora' ); ?></span>
									</label>
								</div>
							</div>

							<!-- Left & Right Content -->
							<div class="navora-subcard" style="background:#fff; padding:15px; border:1px solid #e2e8f0; border-radius:8px;">
								<h3 style="margin-top:0; font-size:14px; color:#0f172a;"><?php esc_html_e( 'Left Content (Info / Text / Badge / Menu)', 'navora' ); ?></h3>

								<div class="navora-field">
									<label for="navora_topbar_left_badge"><?php esc_html_e( 'Optional Pill Badge', 'navora' ); ?></label>
									<input type="text" id="navora_topbar_left_badge" name="navora_options[topbar_left_badge]" value="<?php echo esc_attr( $options['topbar_left_badge'] ); ?>" placeholder="e.g. INFO or NEW">
								</div>

								<div class="navora-field">
									<label for="navora_topbar_left_text"><?php esc_html_e( 'Left Text / Title (Supports Emoji/Icon)', 'navora' ); ?></label>
									<input type="text" id="navora_topbar_left_text" name="navora_options[topbar_left_text]" value="<?php echo esc_attr( $options['topbar_left_text'] ); ?>" placeholder="e.g. 📞 Info Pelatihan: +62 812-3456-7890">
								</div>

								<div class="navora-field">
									<label for="navora_topbar_left_url"><?php esc_html_e( 'Left Link URL (Optional)', 'navora' ); ?></label>
									<input type="text" id="navora_topbar_left_url" name="navora_options[topbar_left_url]" value="<?php echo esc_attr( $options['topbar_left_url'] ); ?>" placeholder="e.g. /training-info">
								</div>

								<div class="navora-field">
									<label for="navora_topbar_left_menu_id"><?php esc_html_e( 'Or Display Secondary Menu (Left)', 'navora' ); ?></label>
									<select id="navora_topbar_left_menu_id" name="navora_options[topbar_left_menu_id]">
										<option value=""><?php esc_html_e( '-- None (Use Text / Badge above) --', 'navora' ); ?></option>
										<?php foreach ( $menus as $menu ) : ?>
											<option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( $options['topbar_left_menu_id'], $menu->term_id ); ?>>
												<?php echo esc_html( $menu->name ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>

								<hr style="margin:18px 0; border:0; border-top:1px solid #f1f5f9;">

								<h3 style="font-size:14px; color:#0f172a;"><?php esc_html_e( 'Right Content (Action Link / CTA / Menu)', 'navora' ); ?></h3>

								<div class="navora-field">
									<label for="navora_topbar_right_text"><?php esc_html_e( 'Right Action Text', 'navora' ); ?></label>
									<input type="text" id="navora_topbar_right_text" name="navora_options[topbar_right_text]" value="<?php echo esc_attr( $options['topbar_right_text'] ); ?>" placeholder="e.g. Join with Us ›››">
								</div>

								<div class="navora-field">
									<label for="navora_topbar_right_url"><?php esc_html_e( 'Right Link URL', 'navora' ); ?></label>
									<input type="text" id="navora_topbar_right_url" name="navora_options[topbar_right_url]" value="<?php echo esc_attr( $options['topbar_right_url'] ); ?>" placeholder="e.g. /join">
								</div>

								<div class="navora-field">
									<label for="navora_topbar_right_menu_id"><?php esc_html_e( 'Or Display Secondary Menu (Right)', 'navora' ); ?></label>
									<select id="navora_topbar_right_menu_id" name="navora_options[topbar_right_menu_id]">
										<option value=""><?php esc_html_e( '-- None (Use Action Text above) --', 'navora' ); ?></option>
										<?php foreach ( $menus as $menu ) : ?>
											<option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( $options['topbar_right_menu_id'], $menu->term_id ); ?>>
												<?php echo esc_html( $menu->name ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
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

	/**
	 * Purge all known cache plugins when Navora settings are updated.
	 */
	public function purge_all_caches() {
		// 1. Perfmatters
		if ( class_exists( '\Perfmatters\CSS' ) && method_exists( '\Perfmatters\CSS', 'clear_used_css' ) ) {
			\Perfmatters\CSS::clear_used_css();
		}
		if ( has_action( 'perfmatters_clear_cache' ) ) {
			do_action( 'perfmatters_clear_cache' );
		}

		// 2. FlyingPress
		if ( class_exists( '\FlyingPress\Purge' ) && method_exists( '\FlyingPress\Purge', 'purge_everything' ) ) {
			\FlyingPress\Purge::purge_everything();
		}

		// 3. Cloudflare Plugin
		if ( has_action( 'cloudflare_purge_everything' ) ) {
			do_action( 'cloudflare_purge_everything' );
		}

		// 4. WP Rocket
		if ( function_exists( 'rocket_clean_domain' ) ) {
			rocket_clean_domain();
		}
		if ( function_exists( 'rocket_clean_minify' ) ) {
			rocket_clean_minify();
		}

		// 5. LiteSpeed Cache
		if ( class_exists( '\LiteSpeed_Cache_API' ) && method_exists( '\LiteSpeed_Cache_API', 'purge_all' ) ) {
			\LiteSpeed_Cache_API::purge_all();
		} elseif ( has_action( 'litespeed_purge_all' ) ) {
			do_action( 'litespeed_purge_all' );
		}

		// 6. Autoptimize
		if ( class_exists( '\autoptimizeCache' ) && method_exists( '\autoptimizeCache', 'clearall' ) ) {
			\autoptimizeCache::clearall();
		}

		// 7. WP Super Cache
		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			wp_cache_clear_cache();
		}

		// 8. W3 Total Cache
		if ( function_exists( 'w3tc_flush_all' ) ) {
			w3tc_flush_all();
		}

		// 9. SiteGround Optimizer
		if ( function_exists( 'sg_cachepress_purge_cache' ) ) {
			sg_cachepress_purge_cache();
		}

		// 10. WP Engine
		if ( class_exists( '\WpeCommon' ) ) {
			if ( method_exists( '\WpeCommon', 'purge_memcached' ) ) {
				\WpeCommon::purge_memcached();
			}
			if ( method_exists( '\WpeCommon', 'clear_maxcdn_cache' ) ) {
				\WpeCommon::clear_maxcdn_cache();
			}
			if ( method_exists( '\WpeCommon', 'purge_varnish_cache' ) ) {
				\WpeCommon::purge_varnish_cache();
			}
		}

		// 11. Kinsta Cache
		if ( class_exists( '\Kinsta\Cache' ) ) {
			global $kinsta_cache;
			if ( isset( $kinsta_cache->kinsta_cache_purge ) && method_exists( $kinsta_cache->kinsta_cache_purge, 'purge_complete_caches' ) ) {
				$kinsta_cache->kinsta_cache_purge->purge_complete_caches();
			}
		}
	}
}

