<?php
/**
 * Plugin Name:       Geoapify Autocomplete
 * Plugin URI:        https://github.com/guisfus/wp-geoapify-autocomplete
 * Description:       Adds Geoapify-powered address autocomplete to WordPress forms and fills city, state and postal code fields automatically.
 * Version:           1.0.0
 * Requires at least: 5.8
 * Requires PHP:      7.4
 * Author:            guisfus
 * Author URI:        https://github.com/guisfus
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       geoapify-autocomplete
 * Domain Path:       /languages
 *
 * @package GeoapifyAutocomplete
 */

defined( 'ABSPATH' ) || exit;

define( 'GEOAPIFY_AUTOCOMPLETE_VERSION', '1.0.0' );
define( 'GEOAPIFY_AUTOCOMPLETE_FILE', __FILE__ );
define( 'GEOAPIFY_AUTOCOMPLETE_PATH', plugin_dir_path( __FILE__ ) );
define( 'GEOAPIFY_AUTOCOMPLETE_URL', plugin_dir_url( __FILE__ ) );

require_once GEOAPIFY_AUTOCOMPLETE_PATH . 'includes/class-gaa-plugin.php';

/**
 * Boot the plugin.
 */
function geoapify_autocomplete_run() {
	$plugin = new GAA_Plugin();
	$plugin->run();
}
geoapify_autocomplete_run();

/**
 * Load plugin translations.
 */
function geoapify_autocomplete_load_textdomain() {
	load_plugin_textdomain( 'geoapify-autocomplete', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'geoapify_autocomplete_load_textdomain' );

/**
 * Add quick links to the plugin row.
 *
 * @param string[] $links Existing plugin action links.
 * @return string[]
 */
add_filter(
	'plugin_action_links_' . plugin_basename( __FILE__ ),
	function ( $links ) {
		$settings_link = '<a href="' . esc_url( admin_url( 'options-general.php?page=geoapify-autocomplete' ) ) . '">' . esc_html__( 'Settings', 'geoapify-autocomplete' ) . '</a>';
		$docs_link     = '<a href="' . esc_url( admin_url( 'options-general.php?page=geoapify-autocomplete&tab=docs' ) ) . '">' . esc_html__( 'Documentation', 'geoapify-autocomplete' ) . '</a>';

		array_unshift( $links, $settings_link, $docs_link );
		return $links;
	}
);
