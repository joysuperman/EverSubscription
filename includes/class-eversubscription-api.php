<?php

/**
 * REST API for Subscription Management
 *
 * @package    Eversubscription
 * @subpackage Eversubscription/includes
 * @author     JOYSUPERMAN <joymojumder529@gmail.com>
 */

class Eversubscription_API {

	/**
	 * Register REST API routes
	 */
	public static function register_routes() {
		register_rest_route( 'eversubscription/v1', '/subscriptions', array(
			'methods' => 'GET',
			'callback' => array( __CLASS__, 'get_subscriptions' ),
			'permission_callback' => array( __CLASS__, 'check_admin_permission' ),
		) );

		register_rest_route( 'eversubscription/v1', '/subscriptions/(?P<id>\d+)', array(
			'methods' => 'GET',
			'callback' => array( __CLASS__, 'get_subscription' ),
			'permission_callback' => array( __CLASS__, 'check_admin_permission' ),
		) );

		register_rest_route( 'eversubscription/v1', '/subscriptions/(?P<id>\d+)/status', array(
			'methods' => 'POST',
			'callback' => array( __CLASS__, 'update_subscription_status' ),
			'permission_callback' => array( __CLASS__, 'check_admin_permission' ),
		) );

		register_rest_route( 'eversubscription/v1', '/subscriptions/(?P<id>\d+)/cancel', array(
			'methods' => 'POST',
			'callback' => array( __CLASS__, 'cancel_subscription' ),
			'permission_callback' => array( __CLASS__, 'check_admin_permission' ),
		) );

		register_rest_route( 'eversubscription/v1', '/subscriptions/(?P<id>\d+)/pause', array(
			'methods' => 'POST',
			'callback' => array( __CLASS__, 'pause_subscription' ),
			'permission_callback' => array( __CLASS__, 'check_admin_permission' ),
		) );

		register_rest_route( 'eversubscription/v1', '/subscriptions/(?P<id>\d+)/resume', array(
			'methods' => 'POST',
			'callback' => array( __CLASS__, 'resume_subscription' ),
			'permission_callback' => array( __CLASS__, 'check_admin_permission' ),
		) );

		register_rest_route( 'eversubscription/v1', '/subscriptions/stats', array(
			'methods' => 'GET',
			'callback' => array( __CLASS__, 'get_stats' ),
			'permission_callback' => array( __CLASS__, 'check_admin_permission' ),
		) );

		register_rest_route( 'eversubscription/v1', '/subscriptions/(?P<id>\d+)/delete', array(
			'methods' => 'DELETE',
			'callback' => array( __CLASS__, 'delete_subscription' ),
			'permission_callback' => array( __CLASS__, 'check_admin_permission' ),
		) );
	}

	/**
	 * Check admin permission
	 */
	public static function check_admin_permission() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Get subscriptions
	 */
	public static function get_subscriptions( $request ) {
		$params = $request->get_query_params();
		$args = array(
			'status' => isset( $params['status'] ) ? sanitize_text_field( $params['status'] ) : '',
			'per_page' => isset( $params['per_page'] ) ? absint( $params['per_page'] ) : 20,
			'page' => isset( $params['page'] ) ? absint( $params['page'] ) : 1,
		);

		$subscriptions = Eversubscription_Subscription::get_subscriptions( $args );
		$formatted = array();

		foreach ( $subscriptions as $subscription ) {
			$formatted[] = self::format_subscription( $subscription );
		}

		return new WP_REST_Response( $formatted, 200 );
	}

	/**
	 * Get single subscription
	 */
	public static function get_subscription( $request ) {
		$id = absint( $request['id'] );
		$subscription = Eversubscription_Subscription::get_subscription( $id );

		if ( ! $subscription ) {
			return new WP_Error( 'not_found', 'Subscription not found', array( 'status' => 404 ) );
		}

		return new WP_REST_Response( self::format_subscription( $subscription ), 200 );
	}

	/**
	 * Update subscription status
	 */
	public static function update_subscription_status( $request ) {
		$id = absint( $request['id'] );
		$params = $request->get_json_params();
		$status = isset( $params['status'] ) ? sanitize_text_field( $params['status'] ) : '';

		if ( empty( $status ) ) {
			return new WP_Error( 'invalid_status', 'Status is required', array( 'status' => 400 ) );
		}

		$result = Eversubscription_Subscription::update_status( $id, $status );

		if ( $result ) {
			$subscription = Eversubscription_Subscription::get_subscription( $id );
			return new WP_REST_Response( self::format_subscription( $subscription ), 200 );
		}

		return new WP_Error( 'update_failed', 'Failed to update subscription', array( 'status' => 500 ) );
	}

	/**
	 * Cancel subscription
	 */
	public static function cancel_subscription( $request ) {
		$id = absint( $request['id'] );
		$result = Eversubscription_Subscription::cancel_subscription( $id );

		if ( $result ) {
			$subscription = Eversubscription_Subscription::get_subscription( $id );
			return new WP_REST_Response( self::format_subscription( $subscription ), 200 );
		}

		return new WP_Error( 'cancel_failed', 'Failed to cancel subscription', array( 'status' => 500 ) );
	}

	/**
	 * Pause subscription
	 */
	public static function pause_subscription( $request ) {
		$id = absint( $request['id'] );
		$result = Eversubscription_Subscription::pause_subscription( $id );

		if ( $result ) {
			$subscription = Eversubscription_Subscription::get_subscription( $id );
			return new WP_REST_Response( self::format_subscription( $subscription ), 200 );
		}

		return new WP_Error( 'pause_failed', 'Failed to pause subscription', array( 'status' => 500 ) );
	}

	/**
	 * Resume subscription
	 */
	public static function resume_subscription( $request ) {
		$id = absint( $request['id'] );
		$result = Eversubscription_Subscription::resume_subscription( $id );

		if ( $result ) {
			$subscription = Eversubscription_Subscription::get_subscription( $id );
			return new WP_REST_Response( self::format_subscription( $subscription ), 200 );
		}

		return new WP_Error( 'resume_failed', 'Failed to resume subscription', array( 'status' => 500 ) );
	}

	/**
	 * Delete subscription
	 */
	public static function delete_subscription( $request ) {
		$id = absint( $request['id'] );
		global $wpdb;
		$table_name = $wpdb->prefix . 'ever_subscriptions';
		$result = $wpdb->delete( $table_name, array( 'id' => $id ), array( '%d' ) );

		if ( $result ) {
			return new WP_REST_Response( array( 'success' => true ), 200 );
		}

		return new WP_Error( 'delete_failed', 'Failed to delete subscription', array( 'status' => 500 ) );
	}

	/**
	 * Get stats
	 */
	public static function get_stats( $request ) {
		$counts = Eversubscription_Subscription::get_status_counts();
		return new WP_REST_Response( $counts, 200 );
	}

	/**
	 * Format subscription for API response
	 */
	private static function format_subscription( $subscription ) {
		$product = wc_get_product( $subscription->product_id );
		$user = get_userdata( $subscription->user_id );
		$order = wc_get_order( $subscription->order_id );

		return array(
			'id' => (int) $subscription->id,
			'subscription_key' => $subscription->subscription_key,
			'user_id' => (int) $subscription->user_id,
			'user_name' => $user ? $user->display_name : '',
			'user_email' => $user ? $user->user_email : '',
			'order_id' => (int) $subscription->order_id,
			'order_number' => $order ? $order->get_order_number() : '',
			'product_id' => (int) $subscription->product_id,
			'product_name' => $product ? $product->get_name() : '',
			'status' => $subscription->status,
			'subscription_price' => (float) $subscription->subscription_price,
			'billing_interval' => (int) $subscription->billing_interval,
			'billing_period' => $subscription->billing_period,
			'billing_cycle' => $subscription->billing_interval . ' ' . $subscription->billing_period . '(s)',
			'subscription_length' => $subscription->subscription_length,
			'sign_up_fee' => (float) $subscription->sign_up_fee,
			'trial_length' => (int) $subscription->trial_length,
			'trial_period' => $subscription->trial_period,
			'start_date' => $subscription->start_date,
			'next_payment_date' => $subscription->next_payment_date,
			'end_date' => $subscription->end_date,
			'last_payment_date' => $subscription->last_payment_date,
			'trial_end_date' => $subscription->trial_end_date,
			'created_at' => $subscription->created_at,
			'updated_at' => $subscription->updated_at,
		);
	}
}

