<?php
/**
 * Navora GitHub Updater Class.
 */

// Prevent direct access.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Navora_Updater {

	/**
	 * File path.
	 */
	private $file;

	/**
	 * GitHub Username.
	 */
	private $username;

	/**
	 * GitHub Repository name.
	 */
	private $repository;

	/**
	 * Plugin slug.
	 */
	private $slug;

	/**
	 * Constructor.
	 */
	public function __construct( $file, $username, $repository ) {
		$this->file       = $file;
		$this->username   = $username;
		$this->repository = $repository;
		$this->slug       = plugin_basename( $file );

		// Hook into update checks.
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'check_update' ) );
		add_filter( 'plugins_api', array( $this, 'plugin_popup' ), 10, 3 );
		add_filter( 'upgrader_post_install', array( $this, 'rename_github_folder' ), 10, 3 );
	}

	/**
	 * Get GitHub repository latest release information.
	 */
	private function get_repo_release_info() {
		$transient_key = 'navora_github_update_info';
		$info          = get_transient( $transient_key );

		if ( false !== $info ) {
			return $info;
		}

		$url  = sprintf( 'https://api.github.com/repos/%s/%s/releases/latest', $this->username, $this->repository );
		$args = array(
			'user-agent' => 'WordPress/Navora-Updater',
		);

		$response = wp_remote_get( $url, $args );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$data = json_decode( wp_remote_retrieve_body( $response ) );
		if ( empty( $data ) || ! isset( $data->tag_name ) ) {
			return false;
		}

		$info = array(
			'version'      => ltrim( $data->tag_name, 'v' ),
			'download'     => $data->zipball_url,
			'changelog'    => isset( $data->body ) ? $data->body : '',
			'publish_date' => $data->published_at,
		);

		set_transient( $transient_key, $info, 12 * HOUR_IN_SECONDS );

		return $info;
	}

	/**
	 * Inject update information into update transient.
	 */
	public function check_update( $transient ) {
		if ( empty( $transient->checked ) ) {
			return $transient;
		}

		$release_info = $this->get_repo_release_info();
		if ( ! $release_info ) {
			return $transient;
		}

		$current_version = NAVORA_VERSION;
		$github_version  = $release_info['version'];

		if ( version_compare( $github_version, $current_version, '>' ) ) {
			$obj              = new stdClass();
			$obj->slug        = 'navora';
			$obj->plugin      = $this->slug;
			$obj->new_version = $github_version;
			$obj->url         = sprintf( 'https://github.com/%s/%s', $this->username, $this->repository );
			$obj->package     = $release_info['download'];

			$transient->response[ $this->slug ] = $obj;
		}

		return $transient;
	}

	/**
	 * Display plugin info popup modal.
	 */
	public function plugin_popup( $result, $action, $args ) {
		if ( 'plugin_information' !== $action ) {
			return $result;
		}

		if ( ! isset( $args->slug ) || 'navora' !== $args->slug ) {
			return $result;
		}

		$release_info = $this->get_repo_release_info();
		if ( ! $release_info ) {
			return $result;
		}

		$obj                = new stdClass();
		$obj->name          = 'Navora';
		$obj->slug          = 'navora';
		$obj->version       = $release_info['version'];
		$obj->author        = '<a href="https://www.hipnolink.com">Hipnolink Digital Team</a>';
		$obj->homepage      = 'https://www.hipnolink.com';
		$obj->download_link = $release_info['download'];
		$obj->sections      = array(
			'description' => 'Modern Navigation, Made Simple. Built for Health/Healthcare websites, works with Elementor Free.',
			'changelog'   => wpautop( esc_html( $release_info['changelog'] ) ),
		);

		return $obj;
	}

	/**
	 * Rename GitHub directory to standard navora folder after install.
	 */
	public function rename_github_folder( $response, $hook_extra, $result ) {
		global $wp_filesystem;

		if ( ! isset( $hook_extra['plugin'] ) || $hook_extra['plugin'] !== $this->slug ) {
			return $response;
		}

		$plugin_dir  = WP_PLUGIN_DIR . '/navora';
		$destination = isset( $result['destination'] ) ? $result['destination'] : '';

		if ( ! empty( $destination ) && $destination !== $plugin_dir ) {
			if ( $wp_filesystem->exists( $plugin_dir ) ) {
				$wp_filesystem->delete( $plugin_dir, true );
			}
			$wp_filesystem->move( $destination, $plugin_dir );
			$result['destination'] = $plugin_dir;
		}

		return $response;
	}
}
