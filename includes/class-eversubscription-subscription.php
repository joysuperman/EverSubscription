<?php

/**
 * Subscription Management Class
 *
 * Handles all subscription-related operations
 *
 * @package    Eversubscription
 * @subpackage Eversubscription/includes
 * @author     JOYSUPERMAN <joymojumder529@gmail.com>
 */

class Eversubscription_Subscription {

	/**
	 * Create a new subscription
	 *
	 * @param int $order_id Order ID
	 * @param int $product_id Product ID
	 * @param int $user_id User ID
	 * @return int|false Subscription ID or false on failure
	 */
	public static function create_subscription( $order_id, $product_id, $user_id ) {
		global $wpdb;

		$product = wc_get_product( $product_id );
		if ( ! $product || $product->get_type() !== 'ever_subscription' ) {
			return false;
		}

		$subscription_price = $product->get_meta( '_ever_subscription_price' );
		$billing_interval = $product->get_meta( '_ever_billing_interval' ) ?: 1;
		$billing_period = $product->get_meta( '_ever_billing_period' ) ?: 'month';
		$subscription_length = $product->get_meta( '_ever_subscription_length' ) ?: '0';
		$sign_up_fee = $product->get_meta( '_ever_subscription_sign_up_fee' ) ?: 0;
		$trial_length = $product->get_meta( '_ever_subscription_trial_length' ) ?: 0;
		$trial_period = $product->get_meta( '_ever_subscription_trial_period' ) ?: 'day';

		$subscription_key = 'ever_sub_' . time() . '_' . wp_generate_password( 12, false );

		$start_date = current_time( 'mysql' );
		$next_payment_date = null;
		$trial_end_date = null;

		// Calculate trial end date if trial exists
		if ( $trial_length > 0 ) {
			$trial_end_date = date( 'Y-m-d H:i:s', strtotime( "+{$trial_length} {$trial_period}", strtotime( $start_date ) ) );
			$next_payment_date = $trial_end_date;
		} else {
			// Calculate next payment date
			$next_payment_date = date( 'Y-m-d H:i:s', strtotime( "+{$billing_interval} {$billing_period}", strtotime( $start_date ) ) );
		}

		// Calculate end date if subscription has length
		$end_date = null;
		if ( $subscription_length !== '0' && is_numeric( $subscription_length ) ) {
			$end_date = date( 'Y-m-d H:i:s', strtotime( "+{$subscription_length} years", strtotime( $start_date ) ) );
		}

		$table_name = $wpdb->prefix . 'ever_subscriptions';

		$result = $wpdb->insert(
			$table_name,
			array(
				'subscription_key' => $subscription_key,
				'user_id' => $user_id,
				'order_id' => $order_id,
				'product_id' => $product_id,
				'status' => 'active',
				'subscription_price' => $subscription_price,
				'billing_interval' => $billing_interval,
				'billing_period' => $billing_period,
				'subscription_length' => $subscription_length,
				'sign_up_fee' => $sign_up_fee,
				'trial_length' => $trial_length,
				'trial_period' => $trial_period,
				'start_date' => $start_date,
				'next_payment_date' => $next_payment_date,
				'end_date' => $end_date,
				'trial_end_date' => $trial_end_date,
			),
			array( '%s', '%d', '%d', '%d', '%s', '%f', '%d', '%s', '%s', '%f', '%d', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( $result ) {
			return $wpdb->insert_id;
		}

		return false;
	}

	/**
	 * Get subscription by ID
	 *
	 * @param int $subscription_id Subscription ID
	 * @return object|false Subscription object or false
	 */
	public static function get_subscription( $subscription_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ever_subscriptions';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $subscription_id ) );
	}

	/**
	 * Get subscription by key
	 *
	 * @param string $subscription_key Subscription key
	 * @return object|false Subscription object or false
	 */
	public static function get_subscription_by_key( $subscription_key ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ever_subscriptions';
		return $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE subscription_key = %s", $subscription_key ) );
	}

	/**
	 * Get subscriptions by user ID
	 *
	 * @param int $user_id User ID
	 * @return array Array of subscription objects
	 */
	public static function get_user_subscriptions( $user_id ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ever_subscriptions';
		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $table_name WHERE user_id = %d ORDER BY created_at DESC", $user_id ) );
	}

	/**
	 * Get all subscriptions with pagination
	 *
	 * @param array $args Query arguments
	 * @return array Array of subscription objects
	 */
	public static function get_subscriptions( $args = array() ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ever_subscriptions';

		$defaults = array(
			'status' => '',
			'per_page' => 20,
			'page' => 1,
			'orderby' => 'created_at',
			'order' => 'DESC',
		);

		$args = wp_parse_args( $args, $defaults );

		$where = '1=1';
		if ( ! empty( $args['status'] ) ) {
			$where .= $wpdb->prepare( " AND status = %s", $args['status'] );
		}

		$offset = ( $args['page'] - 1 ) * $args['per_page'];
		$orderby = sanitize_sql_orderby( $args['orderby'] . ' ' . $args['order'] );

		$query = "SELECT * FROM $table_name WHERE $where ORDER BY $orderby LIMIT %d OFFSET %d";
		return $wpdb->get_results( $wpdb->prepare( $query, $args['per_page'], $offset ) );
	}

	/**
	 * Update subscription status
	 *
	 * @param int $subscription_id Subscription ID
	 * @param string $status New status
	 * @return bool Success
	 */
	public static function update_status( $subscription_id, $status ) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ever_subscriptions';

		$valid_statuses = array( 'pending', 'active', 'on-hold', 'cancelled', 'expired', 'trial' );
		if ( ! in_array( $status, $valid_statuses, true ) ) {
			return false;
		}

		return $wpdb->update(
			$table_name,
			array( 'status' => $status ),
			array( 'id' => $subscription_id ),
			array( '%s' ),
			array( '%d' )
		) !== false;
	}

	/**
	 * Cancel subscription
	 *
	 * @param int $subscription_id Subscription ID
	 * @return bool Success
	 */
	public static function cancel_subscription( $subscription_id ) {
		return self::update_status( $subscription_id, 'cancelled' );
	}

	/**
	 * Pause subscription
	 *
	 * @param int $subscription_id Subscription ID
	 * @return bool Success
	 */
	public static function pause_subscription( $subscription_id ) {
		return self::update_status( $subscription_id, 'on-hold' );
	}

	/**
	 * Resume subscription
	 *
	 * @param int $subscription_id Subscription ID
	 * @return bool Success
	 */
	public static function resume_subscription( $subscription_id ) {
		return self::update_status( $subscription_id, 'active' );
	}

	/**
	 * Process recurring payment
	 *
	 * @param int $subscription_id Subscription ID
	 * @return int|false New order ID or false on failure
	 */
	public static function process_recurring_payment( $subscription_id ) {
		$subscription = self::get_subscription( $subscription_id );
		if ( ! $subscription || $subscription->status !== 'active' ) {
			return false;
		}

		// Check if payment is due
		$now = current_time( 'mysql' );
		if ( strtotime( $subscription->next_payment_date ) > strtotime( $now ) ) {
			return false;
		}

		// Check if subscription has expired
		if ( $subscription->end_date && strtotime( $subscription->end_date ) < strtotime( $now ) ) {
			self::update_status( $subscription_id, 'expired' );
			return false;
		}

		// Create renewal order
		$order = wc_create_order();
		if ( is_wp_error( $order ) ) {
			return false;
		}

		$product = wc_get_product( $subscription->product_id );
		if ( ! $product || $product->get_type() !== 'ever_subscription' ) {
			return false;
		}

		$order->add_product( $product, 1 );
		$order->set_customer_id( $subscription->user_id );
		$order->set_payment_method( 'bacs' ); // Default payment method
		$order->calculate_totals();
		$order->save();

		// Update subscription
		global $wpdb;
		$table_name = $wpdb->prefix . 'ever_subscriptions';

		$billing_interval = $subscription->billing_interval;
		$billing_period = $subscription->billing_period;
		$next_payment_date = date( 'Y-m-d H:i:s', strtotime( "+{$billing_interval} {$billing_period}", strtotime( $subscription->next_payment_date ) ) );

		$wpdb->update(
			$table_name,
			array(
				'next_payment_date' => $next_payment_date,
				'last_payment_date' => current_time( 'mysql' ),
			),
			array( 'id' => $subscription_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		// Record subscription order
		$orders_table = $wpdb->prefix . 'ever_subscription_orders';
		$wpdb->insert(
			$orders_table,
			array(
				'subscription_id' => $subscription_id,
				'order_id' => $order->get_id(),
				'amount' => $subscription->subscription_price,
				'status' => 'pending',
				'payment_date' => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%f', '%s', '%s' )
		);

		return $order->get_id();
	}

	/**
	 * Get subscription count by status
	 *
	 * @return array Status counts
	 */
	public static function get_status_counts() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'ever_subscriptions';

		$results = $wpdb->get_results( "SELECT status, COUNT(*) as count FROM $table_name GROUP BY status" );
		$counts = array(
			'all' => 0,
			'active' => 0,
			'pending' => 0,
			'on-hold' => 0,
			'cancelled' => 0,
			'expired' => 0,
		);

		foreach ( $results as $result ) {
			$counts[ $result->status ] = (int) $result->count;
			$counts['all'] += (int) $result->count;
		}

		return $counts;
	}
}

