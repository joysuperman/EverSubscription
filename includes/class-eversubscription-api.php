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
	public static function eversubscription_register_api_routes() {
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

		// Settings: public GET (safe) and admin POST (save)
		register_rest_route( 'eversubscription/v1', '/settings', array(
			'methods' => 'GET',
			'callback' => array( __CLASS__, 'get_settings' ),
			'permission_callback' => '__return_true',
		) );

		register_rest_route( 'eversubscription/v1', '/settings', array(
			'methods' => 'POST',
			'callback' => array( __CLASS__, 'save_settings' ),
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
	 * Get plugin settings (public safe read)
	 */
	public static function get_settings( $request ) {
		$defaults = array(
			'enabled' => true,
			'subscription_message' => __( 'Subscribe to get updates and recurring deliveries.', 'eversubscription' ),
			'default_interval' => 1,
			'default_period' => 'month',
			'admin_email' => get_option( 'admin_email' ),
			'add_to_cart_button_text' => __( 'Add To Cart', 'eversubscription' ),
			'order_button_text' => __( 'Get Deal', 'eversubscription' ),
			'subscriber_role' => 'subscriber',
			'cancelled_role' => 'customer',
			'zero_initial_payment_allows_without_payment_method' => false,
			'drip_downloadable_on_renewal' => false,
			'max_customer_suspensions' => 0,
			'multiple_purchase' => false,
			'enable_retry' => true,
			'accept_manual_renewals' => true,
			'turn_off_automatic_payments' => false,
			'enable_auto_renewal_toggle' => true,
			'enable_early_renewal' => true,
			'enable_early_renewal_via_modal' => true,
			'sync_payments' => true,
			'prorate_synced_payments' => 'no',
			'days_no_fee' => 0,
			'allow_switching_variable' => false,
			'allow_switching_grouped' => false,
			'switch_button_text' => __( 'Upgrade or Downgrade', 'eversubscription' ),
		);

		$settings = array();
		$settings['enabled'] = (bool) get_option( 'eversubscription_enabled', $defaults['enabled'] );
		$settings['subscription_message'] = get_option( 'eversubscription_subscription_message', $defaults['subscription_message'] );
		$settings['default_interval'] = (int) get_option( 'eversubscription_default_interval', $defaults['default_interval'] );
		$settings['default_period'] = get_option( 'eversubscription_default_period', $defaults['default_period'] );
		$settings['admin_email'] = get_option( 'eversubscription_admin_email', $defaults['admin_email'] );
		$settings['add_to_cart_button_text'] = get_option( 'eversubscription_add_to_cart_button_text', $defaults['add_to_cart_button_text'] );
		$settings['order_button_text'] = get_option( 'eversubscription_order_button_text', $defaults['order_button_text'] );
		$settings['subscriber_role'] = get_option( 'eversubscription_subscriber_role', $defaults['subscriber_role'] );
		$settings['cancelled_role'] = get_option( 'eversubscription_cancelled_role', $defaults['cancelled_role'] );
		$settings['zero_initial_payment_allows_without_payment_method'] = (bool) get_option( 'eversubscription_zero_initial_payment_allows_without_payment_method', $defaults['zero_initial_payment_allows_without_payment_method'] );
		$settings['drip_downloadable_on_renewal'] = (bool) get_option( 'eversubscription_drip_downloadable_on_renewal', $defaults['drip_downloadable_on_renewal'] );
		$settings['max_customer_suspensions'] = get_option( 'eversubscription_max_customer_suspensions', $defaults['max_customer_suspensions'] );
		$settings['multiple_purchase'] = (bool) get_option( 'eversubscription_multiple_purchase', $defaults['multiple_purchase'] );
		$settings['enable_retry'] = (bool) get_option( 'eversubscription_enable_retry', $defaults['enable_retry'] );
		$settings['accept_manual_renewals'] = (bool) get_option( 'eversubscription_accept_manual_renewals', $defaults['accept_manual_renewals'] );
		$settings['turn_off_automatic_payments'] = (bool) get_option( 'eversubscription_turn_off_automatic_payments', $defaults['turn_off_automatic_payments'] );
		$settings['enable_auto_renewal_toggle'] = (bool) get_option( 'eversubscription_enable_auto_renewal_toggle', $defaults['enable_auto_renewal_toggle'] );
		$settings['enable_early_renewal'] = (bool) get_option( 'eversubscription_enable_early_renewal', $defaults['enable_early_renewal'] );
		$settings['enable_early_renewal_via_modal'] = (bool) get_option( 'eversubscription_enable_early_renewal_via_modal', $defaults['enable_early_renewal_via_modal'] );
		$settings['sync_payments'] = (bool) get_option( 'eversubscription_sync_payments', $defaults['sync_payments'] );
		$settings['prorate_synced_payments'] = get_option( 'eversubscription_prorate_synced_payments', $defaults['prorate_synced_payments'] );
		$settings['days_no_fee'] = (int) get_option( 'eversubscription_days_no_fee', $defaults['days_no_fee'] );
		$settings['allow_switching_variable'] = (bool) get_option( 'eversubscription_allow_switching_variable', $defaults['allow_switching_variable'] );
		$settings['allow_switching_grouped'] = (bool) get_option( 'eversubscription_allow_switching_grouped', $defaults['allow_switching_grouped'] );
		$settings['switch_button_text'] = get_option( 'eversubscription_switch_button_text', $defaults['switch_button_text'] );

		return new WP_REST_Response( $settings, 200 );
	}

	/**
	 * Save plugin settings (admin only)
	 */
	public static function save_settings( $request ) {
		$params = $request->get_json_params();

		// Simple sanitization and save for many settings
		if ( isset( $params['enabled'] ) ) {
			update_option( 'eversubscription_enabled', (bool) $params['enabled'] );
		}
		if ( isset( $params['subscription_message'] ) ) {
			update_option( 'eversubscription_subscription_message', sanitize_text_field( $params['subscription_message'] ) );
		}
		if ( isset( $params['default_interval'] ) ) {
			update_option( 'eversubscription_default_interval', absint( $params['default_interval'] ) );
		}
		if ( isset( $params['default_period'] ) ) {
			update_option( 'eversubscription_default_period', sanitize_text_field( $params['default_period'] ) );
		}
		if ( isset( $params['admin_email'] ) ) {
			update_option( 'eversubscription_admin_email', sanitize_email( $params['admin_email'] ) );
		}

		if ( isset( $params['add_to_cart_button_text'] ) ) {
			update_option( 'eversubscription_add_to_cart_button_text', sanitize_text_field( $params['add_to_cart_button_text'] ) );
		}
		if ( isset( $params['order_button_text'] ) ) {
			update_option( 'eversubscription_order_button_text', sanitize_text_field( $params['order_button_text'] ) );
		}
		if ( isset( $params['subscriber_role'] ) ) {
			update_option( 'eversubscription_subscriber_role', sanitize_text_field( $params['subscriber_role'] ) );
		}
		if ( isset( $params['cancelled_role'] ) ) {
			update_option( 'eversubscription_cancelled_role', sanitize_text_field( $params['cancelled_role'] ) );
		}

		$checkboxes = [
			'zero_initial_payment_allows_without_payment_method',
			'drip_downloadable_on_renewal',
			'multiple_purchase',
			'enable_retry',
			'accept_manual_renewals',
			'turn_off_automatic_payments',
			'enable_auto_renewal_toggle',
			'enable_early_renewal',
			'enable_early_renewal_via_modal',
			'sync_payments',
			'allow_switching_variable',
			'allow_switching_grouped',
		];
		foreach ( $checkboxes as $c ) {
			if ( isset( $params[ $c ] ) ) {
				update_option( 'eversubscription_' . $c, (bool) $params[ $c ] );
			}
		}

		if ( isset( $params['max_customer_suspensions'] ) ) {
			update_option( 'eversubscription_max_customer_suspensions', sanitize_text_field( $params['max_customer_suspensions'] ) );
		}
		if ( isset( $params['prorate_synced_payments'] ) ) {
			update_option( 'eversubscription_prorate_synced_payments', sanitize_text_field( $params['prorate_synced_payments'] ) );
		}
		if ( isset( $params['days_no_fee'] ) ) {
			update_option( 'eversubscription_days_no_fee', absint( $params['days_no_fee'] ) );
		}
		if ( isset( $params['switch_button_text'] ) ) {
			update_option( 'eversubscription_switch_button_text', sanitize_text_field( $params['switch_button_text'] ) );
		}

		return new WP_REST_Response( array( 'success' => true ), 200 );
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

