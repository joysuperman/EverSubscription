<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.joymojumder.com
 * @since             1.0.0
 * @package           Eversubscription
 *
 * @wordpress-plugin
 * Plugin Name:       EverSubscription
 * Plugin URI:        https://github.com/joysuperman/EverSubscription.git
 * Description:       Create and manage recurring subscription products easily in WooCommerce with flexible billing cycles and customer-friendly subscription controls.
 * Version:           1.0.0
 * Author:            JOYSUPERMAN
 * Author URI:        https://www.joymojumder.com/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       eversubscription
 * Domain Path:       /languages
 * Requires Plugins:   woocommerce
 * Requires at least: 5.0
 * Requires PHP:      7.4
 * WC requires at least: 6.0
 * WC tested up to: 6.5
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
define( 'EVERSUBSCRIPTION_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-eversubscription-activator.php
 */
function activate_eversubscription() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-eversubscription-activator.php';
	Eversubscription_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-eversubscription-deactivator.php
 */
function deactivate_eversubscription() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-eversubscription-deactivator.php';
	Eversubscription_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_eversubscription' );
register_deactivation_hook( __FILE__, 'deactivate_eversubscription' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-eversubscription.php';

/**
 * The compatibility class that handles compatibility with WooCommerce and other plugins.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-eversubscription-compatibility.php';

/**
 * Initialize plugin compatibility.
 *
 * This must be called early, before WooCommerce initializes.
 *
 * @since    1.0.0
 */
function eversubscription_init_compatibility() {
	new Eversubscription_Compatibility( __FILE__ );
}
eversubscription_init_compatibility();


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_eversubscription() {

	$plugin = new Eversubscription();
	$plugin->run();

}
run_eversubscription();
