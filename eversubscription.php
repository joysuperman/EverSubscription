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
 * Register REST API routes
 *
 * @since    1.0.0
 */
function eversubscription_register_api_routes() {
	Eversubscription_API::register_routes();
}

/**
 * Create subscription from order
 *
 * @param int $order_id Order ID
 * @since    1.0.0
 */
function eversubscription_create_subscription_from_order( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return;
	}

	// Check if subscription already created for this order
	$existing_subscription = get_post_meta( $order_id, '_ever_subscription_created', true );
	if ( $existing_subscription ) {
		return;
	}

	foreach ( $order->get_items() as $item ) {
		$product = $item->get_product();
		if ( $product && $product->get_type() === 'ever_subscription' ) {
			$user_id = $order->get_user_id();
			if ( ! $user_id ) {
				$user_id = $order->get_customer_id();
			}

			if ( $user_id ) {
				$subscription_id = Eversubscription_Subscription::create_subscription(
					$order_id,
					$product->get_id(),
					$user_id
				);

				if ( $subscription_id ) {
					update_post_meta( $order_id, '_ever_subscription_created', true );
					update_post_meta( $order_id, '_ever_subscription_id', $subscription_id );
				}
			}
		}
	}
}

/**
 * Process recurring payments
 *
 * @since    1.0.0
 */
function eversubscription_process_recurring_payments() {
	global $wpdb;
	$table_name = $wpdb->prefix . 'ever_subscriptions';

	// Get all active subscriptions with due payments
	$now = current_time( 'mysql' );
	$subscriptions = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT id FROM $table_name WHERE status = 'active' AND next_payment_date <= %s",
			$now
		)
	);

	foreach ( $subscriptions as $subscription ) {
		Eversubscription_Subscription::process_recurring_payment( $subscription->id );
	}
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
function run_eversubscription() {

	$plugin = new Eversubscription();
	$plugin->run();

}
run_eversubscription();
