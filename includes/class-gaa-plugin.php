<?php
/**
 * Main plugin bootstrap.
 *
 * @package GuisFusGeoapifyAutocomplete
 */

defined( 'ABSPATH' ) || exit;

require_once GUISFUS_GEOAPIFY_AUTOCOMPLETE_PATH . 'includes/class-gaa-settings.php';
require_once GUISFUS_GEOAPIFY_AUTOCOMPLETE_PATH . 'includes/admin/class-gaa-admin.php';
require_once GUISFUS_GEOAPIFY_AUTOCOMPLETE_PATH . 'includes/public/class-gaa-public.php';

/**
 * Main plugin class.
 */
final class GAA_Plugin {

	/**
	 * Settings service.
	 *
	 * @var GAA_Settings
	 */
	private $settings;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->settings = new GAA_Settings();
	}

	/**
	 * Register WordPress hooks.
	 */
	public function run() {
		$admin = new GAA_Admin( $this->settings );
		add_action( 'admin_menu', array( $admin, 'add_settings_page' ) );
		add_action( 'admin_init', array( $admin, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $admin, 'enqueue_admin_assets' ) );

		$public = new GAA_Public( $this->settings );
		add_action( 'wp_enqueue_scripts', array( $public, 'enqueue_assets' ) );
	}
}
