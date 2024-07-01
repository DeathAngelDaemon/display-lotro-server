<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.lionate.info/wordpress/plugins/display-lotro-server
 * @since             2.0.0
 * @package           DisplayLotroServer
 *
 * @wordpress-plugin
 * Plugin Name:       Display Lotro Server
 * Plugin URI:        https://www.lionate.info/wordpress/plugins/display-lotro-server
 * Description:       Shows a server list of the choosen servers (see the settings). Can be placed as a widget or a shortcode in every article or page.
 * Version:           2.0.0
 * Author:            Anna @ Lionate
 * Author URI:        https://www.lionate.info
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       display-lotro-server
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
define( 'DLS_VERSION', '2.0.0' );

/**
 * Define the general path to the plugin dir.
 */
if ( !defined( 'DLS_PATH' ) )
	define( 'DLS_PATH', plugin_dir_path( __FILE__ ) );

/**
 * Define the base name of the plugin.
 */
if ( !defined( 'DLS_BASENAME' ) )
	define( 'DLS_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Define the path to the images folder.
 */
if ( !defined( 'DLS_IMAGES_URL' ) )
	define( 'DLS_IMAGES_URL', trailingslashit( plugin_dir_url( __FILE__ ) . 'img' ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-dls-activator.php
 */
function activate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-dls-activator.php';
	DisplayLotroServer_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-dls-deactivator.php
 */
function deactivate_plugin_name() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-dls-deactivator.php';
	DisplayLotroServer_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_plugin_name' );
register_deactivation_hook( __FILE__, 'deactivate_plugin_name' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-dls.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    2.0.0
 */
function run_plugin_name() {

	$plugin = new DisplayLotroServer();
	$plugin->run();

}
run_plugin_name();
