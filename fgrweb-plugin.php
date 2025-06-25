<?php
use YahnisElsts\PluginUpdateChecker\v5\PucFactory;

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://fgrweb.es
 * @since             1.0.0
 * @package           Fgrweb_Plugin
 *
 * @wordpress-plugin
 * Plugin Name:       F.GR Plugin Boilerplate
 * Plugin URI:        https://fgrweb.es
 * Description:       Custom plugin
 * Version:           1.0.0
 * Author:            Fernando Garcia Rebolledo
 * Author URI:        https://fgrweb.es/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       fgrweb-plugin
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'FGRWEB_PLUGIN_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-fgrweb-plugin-activator.php
 */
function activate_fgrweb_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-fgrweb-plugin-activator.php';
	Fgrweb_Plugin_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-fgrweb-plugin-deactivator.php
 */
function deactivate_fgrweb_plugin() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-fgrweb-plugin-deactivator.php';
	Fgrweb_Plugin_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_fgrweb_plugin' );
register_deactivation_hook( __FILE__, 'deactivate_fgrweb_plugin' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-fgrweb-plugin.php';

/**
 * Check for plugin updates.
 *
 * @return void
 */
function plugin_updater_checker() {
	require_once plugin_dir_path( dirname( __FILE__ ) ) . 'fgrweb-plugin/includes/updater-checker/plugin-update-checker.php';
	$update_checker = PucFactory::buildUpdateChecker(
		'https://github.com/fgrweb/fgrweb-plugin/',
		plugin_dir_path( __FILE__ ) . 'fgrweb-plugin.php',
		'fgrweb-plugin'
	);
	// Set the branch that contains the stable release.
	$update_checker->setBranch( 'main' );
	// Token.
	$token = get_option( 'fgrweb_plugin_config' );
	if ( ! empty( $token['github_token'] ) ) {
		$update_checker->setAuthentication( $token['github_token'] );
	}
	// Check for releases.
	$update_checker->getVcsApi()->enableReleaseAssets();
}

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_fgrweb_plugin() {
	plugin_updater_checker();
	$plugin = new Fgrweb_Plugin();
	$plugin->run();

}
run_fgrweb_plugin();
