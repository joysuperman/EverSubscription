<?php

/**
 * Fired during plugin activation
 *
 * @link       https://www.joymojumder.com
 * @since      1.0.0
 *
 * @package    Eversubscription
 * @subpackage Eversubscription/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Eversubscription
 * @subpackage Eversubscription/includes
 * @author     JOYSUPERMAN <joymojumder529@gmail.com>
 */
class Eversubscription_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'ever_subscriptions';

		$sql = "CREATE TABLE IF NOT EXISTS $table_name (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			subscription_key varchar(200) NOT NULL,
			user_id bigint(20) UNSIGNED NOT NULL,
			order_id bigint(20) UNSIGNED NOT NULL,
			product_id bigint(20) UNSIGNED NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'pending',
			subscription_price decimal(19,4) NOT NULL,
			billing_interval int(11) NOT NULL DEFAULT 1,
			billing_period varchar(20) NOT NULL DEFAULT 'month',
			subscription_length varchar(20) DEFAULT '0',
			sign_up_fee decimal(19,4) DEFAULT 0,
			trial_length int(11) DEFAULT 0,
			trial_period varchar(20) DEFAULT 'day',
			start_date datetime DEFAULT NULL,
			next_payment_date datetime DEFAULT NULL,
			end_date datetime DEFAULT NULL,
			last_payment_date datetime DEFAULT NULL,
			trial_end_date datetime DEFAULT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY subscription_key (subscription_key),
			KEY user_id (user_id),
			KEY order_id (order_id),
			KEY product_id (product_id),
			KEY status (status),
			KEY next_payment_date (next_payment_date)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );

		// Create subscription orders table
		$orders_table = $wpdb->prefix . 'ever_subscription_orders';
		$sql_orders = "CREATE TABLE IF NOT EXISTS $orders_table (
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			subscription_id bigint(20) UNSIGNED NOT NULL,
			order_id bigint(20) UNSIGNED NOT NULL,
			payment_date datetime DEFAULT NULL,
			amount decimal(19,4) NOT NULL,
			status varchar(20) NOT NULL DEFAULT 'pending',
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY subscription_id (subscription_id),
			KEY order_id (order_id),
			KEY status (status)
		) $charset_collate;";

		dbDelta( $sql_orders );

		// Schedule cron event for recurring payments
		if ( ! wp_next_scheduled( 'ever_subscription_process_recurring_payments' ) ) {
			wp_schedule_event( time(), 'hourly', 'ever_subscription_process_recurring_payments' );
		}

		// Ensure rewrite rules include the plugin endpoints
		flush_rewrite_rules();
	}

}
