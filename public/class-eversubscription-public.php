<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.joymojumder.com
 * @since      1.0.0
 *
 * @package    Eversubscription
 * @subpackage Eversubscription/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Eversubscription
 * @subpackage Eversubscription/public
 * @author     JOYSUPERMAN <joymojumder529@gmail.com>
 */
class Eversubscription_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}

	/**
	 * Apply sign-up fee as cart item price for guest users.
	 *
	 * @param WC_Cart $cart
	 */
	public function apply_signup_fee_to_cart_items( $cart ) {
		if ( ! $cart || ( is_admin() && ! defined( 'DOING_AJAX' ) ) ) {
			return;
		}

		foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
			$product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
			if ( ! $product || ! is_object( $product ) || ! method_exists( $product, 'get_type' ) || $product->get_type() !== 'ever_subscription' ) {
				continue;
			}

			$sign_up_fee = floatval( $product->get_meta( '_ever_subscription_sign_up_fee' ) ) ?: 0;
			if ( ! is_user_logged_in() && $sign_up_fee > 0 ) {
				// Set unit price to sign-up fee and adjust line totals
				$cart->cart_contents[ $cart_item_key ]['data']->set_price( $sign_up_fee );
				$qty = isset( $cart_item['quantity'] ) ? intval( $cart_item['quantity'] ) : ( isset( $cart_item['qty'] ) ? intval( $cart_item['qty'] ) : 1 );
				$cart->cart_contents[ $cart_item_key ]['line_total'] = floatval( $sign_up_fee ) * $qty;
				$cart->cart_contents[ $cart_item_key ]['line_subtotal'] = floatval( $sign_up_fee ) * $qty;
			}
		}
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Eversubscription_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Eversubscription_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/eversubscription-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Eversubscription_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Eversubscription_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/eversubscription-public.js', array( 'jquery' ), $this->version, true );

	}

	/**
	 * Display subscription information on product page
	 *
	 * @since    1.0.0
	 */
	public function display_subscription_info() {
		global $product;

		if ( ! $product || $product->get_type() !== 'ever_subscription' ) {
			return;
		}

		$subscription_price = $product->get_meta( '_ever_subscription_price' );
		$billing_interval = $product->get_meta( '_ever_billing_interval' ) ?: 1;
		$billing_period = $product->get_meta( '_ever_billing_period' ) ?: 'month';
		$trial_length = intval( $product->get_meta( '_ever_subscription_trial_length' ) ) ?: 0;
		$trial_period = $product->get_meta( '_ever_subscription_trial_period' ) ?: 'day';
		$sign_up_fee = floatval( $product->get_meta( '_ever_subscription_sign_up_fee' ) ) ?: 0;

		$currency_symbol = get_woocommerce_currency_symbol();
		$billing_text = sprintf(
			_n( 'Every %d %s', 'Every %d %ss', $billing_interval, $this->plugin_name ),
			$billing_interval,
			$billing_period
		);

		echo '<div class="ever-subscription-info" style="margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 5px;">';
		echo '<h4 style="margin-top: 0;">' . esc_html__( 'Subscription Details', $this->plugin_name ) . '</h4>';
		echo '<p><strong>' . esc_html__( 'Price:', $this->plugin_name ) . '</strong> ' . esc_html( $currency_symbol . number_format( $subscription_price, 2 ) ) . ' ' . esc_html( $billing_text ) . '</p>';

		if ( $sign_up_fee > 0 ) {
			echo '<p><strong>' . esc_html__( 'Sign-up Fee:', $this->plugin_name ) . '</strong> ' . esc_html( $currency_symbol . number_format( $sign_up_fee, 2 ) ) . '</p>';
		}

		if ( $trial_length > 0 ) {
			echo '<p><strong>' . esc_html__( 'Free Trial:', $this->plugin_name ) . '</strong> ' . esc_html( $trial_length . ' ' . $trial_period . '(s)' ) . '</p>';
		}

		// Add a simple Subscribe (add to cart) button on the product page for this subscription product.
		if ( method_exists( $product, 'is_purchasable' ) && $product->is_purchasable() && $product->is_in_stock() ) {
			echo '<div class="ever-subscribe-action" style="margin-top:12px;">';
			echo '<form class="cart" action="' . esc_url( wc_get_cart_url() ) . '" method="post">';
			echo '<input type="hidden" name="add-to-cart" value="' . esc_attr( $product->get_id() ) . '" />';
			echo '<button type="submit" class="single_add_to_cart_button button alt">' . esc_html__( 'Subscribe', $this->plugin_name ) . '</button>';
			echo '</form>';
			echo '</div>';
		}

		echo '</div>';
	}

	public function subscription_modification_price( $price, $product = null ) {
		if ( ! $product || ! is_object( $product ) || ! method_exists( $product, 'get_type' ) || $product->get_type() !== 'ever_subscription' ) {
			return $price;
		}

		// If guest and sign-up fee is set, use sign-up fee as the internal price
		$sign_up_fee = floatval( $product->get_meta( '_ever_subscription_sign_up_fee' ) ) ?: 0;
		if ( ! is_user_logged_in() && $sign_up_fee > 0 ) {
			$price = $sign_up_fee;
		}

		// Ensure internal price remains numeric for WooCommerce internals
		$numeric = preg_replace( '/[^0-9\.-]/', '', (string) $price );
		if ( $numeric === '' ) {
			return $price;
		}

		return $numeric;
	}

	/**
	 * Append billing period to the displayed price HTML for subscription products.
	 *
	 * @param string $price_html Price HTML
	 * @param WC_Product $product Product object
	 * @return string Modified price HTML
	 */
	public function subscription_price_html( $price_html, $product ) {
		if ( ! $product || ! is_object( $product ) || ! method_exists( $product, 'get_type' ) || $product->get_type() !== 'ever_subscription' ) {
			return $price_html;
		}

		$billing_period = $product->get_meta( '_ever_billing_period' ) ?: 'month';
		// If guest and sign-up fee exists, display sign-up fee instead of regular price
		$sign_up_fee = floatval( $product->get_meta( '_ever_subscription_sign_up_fee' ) ) ?: 0;
		if ( ! is_user_logged_in() && $sign_up_fee > 0 ) {
			return wc_price( $sign_up_fee ) . ' /' . esc_html( $billing_period );
		}

		return $price_html . ' /' . esc_html( $billing_period );
	}

	/**
	 * Append billing period to cart item price display.
	 *
	 * @param string $price_html
	 * @param array $cart_item
	 * @param string $cart_item_key
	 * @return string
	 */
	public function subscription_cart_item_price( $price_html, $cart_item, $cart_item_key ) {
		$product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
		if ( ! $product || ! is_object( $product ) || ! method_exists( $product, 'get_type' ) || $product->get_type() !== 'ever_subscription' ) {
			return $price_html;
		}

		$billing_period = $product->get_meta( '_ever_billing_period' ) ?: 'month';

		// If guest and sign-up fee exists, show sign-up fee as the displayed price
		$sign_up_fee = floatval( $product->get_meta( '_ever_subscription_sign_up_fee' ) ) ?: 0;
		if ( ! is_user_logged_in() && $sign_up_fee > 0 ) {
			return wc_price( $sign_up_fee ) . ' /' . esc_html( $billing_period );
		}

		return $price_html . ' /' . esc_html( $billing_period );
	}

	/**
	 * Add subscription details to cart/checkout item meta display.
	 *
	 * @param array $item_data
	 * @param array $cart_item
	 * @return array
	 */
	public function subscription_get_item_data( $item_data, $cart_item ) {
		$product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
		if ( ! $product || ! is_object( $product ) || ! method_exists( $product, 'get_type' ) || $product->get_type() !== 'ever_subscription' ) {
			return $item_data;
		}

		$billing_interval = intval( $product->get_meta( '_ever_billing_interval' ) ) ?: 1;
		$billing_period = $product->get_meta( '_ever_billing_period' ) ?: 'month';
		$sign_up_fee = floatval( $product->get_meta( '_ever_subscription_sign_up_fee' ) ) ?: 0;
		$trial_length = intval( $product->get_meta( '_ever_subscription_trial_length' ) ) ?: 0;
		$trial_period = $product->get_meta( '_ever_subscription_trial_period' ) ?: 'day';
		// For subscription item meta we do not expose quantity — always treat as single subscription
		$price = floatval( $product->get_meta( '_ever_subscription_price' ) );
		if ( ! $price ) {
			$price = floatval( $product->get_price() );
		}

		// Human readable billing text
		$billing_text = sprintf( __( 'Every %d %s', $this->plugin_name ), $billing_interval, $billing_period . ( $billing_interval > 1 ? 's' : '' ) );

		// Next / first payment date (formatted)
		$next_date = $this->calculate_next_payment_date( $trial_length, $trial_period, $billing_interval, $billing_period );

		// Primary subscription line
		$item_data[] = array(
			'key'   => __( 'Subscription', $this->plugin_name ),
			'value' => esc_html( $billing_text ),
		);

		if ( $price > 0 ) {
			$item_data[] = array(
				'key'   => __( 'Price', $this->plugin_name ),
				'value' => wc_price( $price ),
			);
		}

		// Free trial
		if ( $trial_length > 0 ) {
			$trial_label = $trial_length . ' ' . $trial_period . ( $trial_length > 1 ? 's' : '' );
			$item_data[] = array(
				'key'   => __( 'Free Trial', $this->plugin_name ),
				'value' => esc_html( $trial_label ),
			);
		}

		// First/Next payment date
		if ( $next_date ) {
			$item_data[] = array(
				'key'   => __( 'First Payment Date', $this->plugin_name ),
				'value' => esc_html( $next_date ),
			);
		}

		// No separate 'First Payment' meta — price already reflects sign-up fee for guests

		return $item_data;
	}

	/**
	 * Add subscription endpoint to My Account
	 *
	 * @since    1.0.0
	 */
	public function add_subscriptions_endpoint() {
		add_rewrite_endpoint( 'subscriptions', EP_ROOT | EP_PAGES );
	}

	/**
	 * Add subscriptions menu item to My Account
	 *
	 * @param array $items Menu items
	 * @return array Modified menu items
	 * @since    1.0.0
	 */
	public function add_subscriptions_menu_item( $items ) {
		$items['subscriptions'] = __( 'Subscriptions', $this->plugin_name );
		return $items;
	}

	/**
	 * Display subscriptions content in My Account
	 *
	 * @since    1.0.0
	 */
	public function display_subscriptions_content() {
		if ( ! is_user_logged_in() ) {
			return;
		}

		$user_id = get_current_user_id();
		$subscriptions = Eversubscription_Subscription::get_user_subscriptions( $user_id );

		echo '<div class="woocommerce-MyAccount-subscriptions">';
		echo '<h2>' . esc_html__( 'My Subscriptions', $this->plugin_name ) . '</h2>';

		if ( empty( $subscriptions ) ) {
			echo '<p>' . esc_html__( 'You have no active subscriptions.', $this->plugin_name ) . '</p>';
		} else {
			echo '<table class="shop_table shop_table_responsive my_account_subscriptions">';
			echo '<thead>';
			echo '<tr>';
			echo '<th class="subscription-id">' . esc_html__( 'ID', $this->plugin_name ) . '</th>';
			echo '<th class="subscription-product">' . esc_html__( 'Product', $this->plugin_name ) . '</th>';
			echo '<th class="subscription-status">' . esc_html__( 'Status', $this->plugin_name ) . '</th>';
			echo '<th class="subscription-next-payment">' . esc_html__( 'Next Payment', $this->plugin_name ) . '</th>';
			echo '<th class="subscription-actions">' . esc_html__( 'Actions', $this->plugin_name ) . '</th>';
			echo '</tr>';
			echo '</thead>';
			echo '<tbody>';

			foreach ( $subscriptions as $subscription ) {
				$product = wc_get_product( $subscription->product_id );
				$product_name = $product ? $product->get_name() : __( 'Product not found', $this->plugin_name );

				echo '<tr class="subscription">';
				echo '<td class="subscription-id" data-title="' . esc_attr__( 'ID', $this->plugin_name ) . '">#' . esc_html( $subscription->id ) . '</td>';
				echo '<td class="subscription-product" data-title="' . esc_attr__( 'Product', $this->plugin_name ) . '">' . esc_html( $product_name ) . '</td>';
				echo '<td class="subscription-status" data-title="' . esc_attr__( 'Status', $this->plugin_name ) . '">';
				echo '<span class="status-' . esc_attr( $subscription->status ) . '">' . esc_html( ucfirst( $subscription->status ) ) . '</span>';
				echo '</td>';
				echo '<td class="subscription-next-payment" data-title="' . esc_attr__( 'Next Payment', $this->plugin_name ) . '">';
				if ( $subscription->next_payment_date ) {
					echo esc_html( date_i18n( get_option( 'date_format' ), strtotime( $subscription->next_payment_date ) ) );
				} else {
					echo '—';
				}
				echo '</td>';
				echo '<td class="subscription-actions" data-title="' . esc_attr__( 'Actions', $this->plugin_name ) . '">';

				if ( $subscription->status === 'active' ) {
					echo '<a href="' . esc_url( wp_nonce_url( add_query_arg( array( 'ever_subscription_action' => 'pause', 'subscription_id' => $subscription->id ) ), 'ever_subscription_action' ) ) . '" class="button pause">' . esc_html__( 'Pause', $this->plugin_name ) . '</a> ';
					echo '<a href="' . esc_url( wp_nonce_url( add_query_arg( array( 'ever_subscription_action' => 'cancel', 'subscription_id' => $subscription->id ) ), 'ever_subscription_action' ) ) . '" class="button cancel">' . esc_html__( 'Cancel', $this->plugin_name ) . '</a>';
				} elseif ( $subscription->status === 'on-hold' ) {
					echo '<a href="' . esc_url( wp_nonce_url( add_query_arg( array( 'ever_subscription_action' => 'resume', 'subscription_id' => $subscription->id ) ), 'ever_subscription_action' ) ) . '" class="button resume">' . esc_html__( 'Resume', $this->plugin_name ) . '</a>';
				}

				echo '</td>';
				echo '</tr>';
			}

			echo '</tbody>';
			echo '</table>';
		}

		echo '</div>';
	}

	/**
	 * Handle subscription preference form submission from thank you page.
	 *
	 * @since    1.0.0
	 */
	public function handle_subscription_preferences() {
		if ( ! isset( $_POST['save_subscription_prefs'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['subscription_prefs_nonce'], 'save_subscription_preferences' ) ) {
			wp_die( 'Security check failed' );
		}

		$order_id = absint( $_POST['order_id'] );
		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			return;
		}

		// Get per-item preferences from form
		$auto_renew_items = isset( $_POST['auto_renew'] ) && is_array( $_POST['auto_renew'] ) ? array_map( 'absint', $_POST['auto_renew'] ) : array();
		$auto_payment_items = isset( $_POST['auto_payment'] ) && is_array( $_POST['auto_payment'] ) ? array_map( 'absint', $_POST['auto_payment'] ) : array();

		// Save preferences for each subscription item in the order
		foreach ( $order->get_items() as $item ) {
			$item_id = $item->get_id();
			$product = $item->get_product();
			
			if ( $product && is_object( $product ) && method_exists( $product, 'get_type' ) && $product->get_type() === 'ever_subscription' ) {
				// Get or create subscription from order
				$subscription = Eversubscription_Subscription::get_subscription_by_order_and_product( $order_id, $product->get_id() );
				
				if ( $subscription ) {
					// Save auto-renewal preference for this item
					$auto_renew = in_array( $item_id, $auto_renew_items, true ) ? 1 : 0;
					update_post_meta( $subscription->id, '_subscription_auto_renew', $auto_renew );

					// Save auto-payment preference for this item
					$auto_payment = in_array( $item_id, $auto_payment_items, true ) ? 1 : 0;
					update_post_meta( $subscription->id, '_subscription_auto_payment', $auto_payment );
				}
			}
		}

		// Add notice
		wc_add_notice( __( 'Subscription preferences saved successfully.', $this->plugin_name ), 'success' );

		// Redirect to avoid form resubmission
		wp_safe_redirect( wc_get_page_permalink( 'checkout' ) . '?order=' . $order_id );
		exit;
	}

	/**
	 * Handle subscription actions from My Account
	 *
	 * @since    1.0.0
	 */
	public function handle_subscription_actions() {
		if ( ! isset( $_GET['ever_subscription_action'] ) || ! isset( $_GET['subscription_id'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'ever_subscription_action' ) ) {
			return;
		}

		if ( ! is_user_logged_in() ) {
			return;
		}

		$subscription_id = absint( $_GET['subscription_id'] );
		$action = sanitize_text_field( $_GET['ever_subscription_action'] );
		$user_id = get_current_user_id();

		$subscription = Eversubscription_Subscription::get_subscription( $subscription_id );

		if ( ! $subscription || $subscription->user_id != $user_id ) {
			wc_add_notice( __( 'Invalid subscription.', $this->plugin_name ), 'error' );
			return;
		}

		switch ( $action ) {
			case 'pause':
				Eversubscription_Subscription::pause_subscription( $subscription_id );
				wc_add_notice( __( 'Subscription paused.', $this->plugin_name ), 'success' );
				break;
			case 'resume':
				Eversubscription_Subscription::resume_subscription( $subscription_id );
				wc_add_notice( __( 'Subscription resumed.', $this->plugin_name ), 'success' );
				break;
			case 'cancel':
				Eversubscription_Subscription::cancel_subscription( $subscription_id );
				wc_add_notice( __( 'Subscription cancelled.', $this->plugin_name ), 'success' );
				break;
		}

		wp_safe_redirect( wc_get_endpoint_url( 'subscriptions', '', wc_get_page_permalink( 'myaccount' ) ) );
		exit;
	}

	/**
	 * Display subscription details before checkout payment section.
	 *
	 * @since    1.0.0
	 */
	public function display_checkout_subscription_details() {
		// Check if there are any subscription items
		$has_subscriptions = false;
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
			if ( $product && is_object( $product ) && method_exists( $product, 'get_type' ) && $product->get_type() === 'ever_subscription' ) {
				$has_subscriptions = true;
				break;
			}
		}

		// Only render if subscriptions exist
		if ( ! $has_subscriptions ) {
			return;
		}

		$subscription_items = array();
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
			if ( $product && is_object( $product ) && method_exists( $product, 'get_type' ) && $product->get_type() === 'ever_subscription' ) {
				$subscription_items[] = $this->get_subscription_details( $product, $cart_item );
			}
		}

		if ( ! empty( $subscription_items ) ) {
			echo '<div class="subscription-details-checkout-section" style="margin-top: 30px; padding: 20px; background: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 5px;">';
			echo '<h3 style="margin-top: 0; color: #333;">' . esc_html__( 'Subscription Details', $this->plugin_name ) . '</h3>';
			foreach ( $subscription_items as $details ) {
				echo wp_kses_post( $details );
			}
			echo '</div>';
		}
	}

	/**
	 * Display subscription details on thank you / order received page.
	 *
	 * @param int $order_id Order ID
	 * @since    1.0.0
	 */
	public function display_thankyou_subscription_details( $order_id ) {
		static $displayed_orders = array();

		// Prevent duplicate output for the same order
		if ( in_array( $order_id, $displayed_orders, true ) ) {
			return;
		}
		$displayed_orders[] = $order_id;

		$order = wc_get_order( $order_id );
		if ( ! $order ) {
			return;
		}

		$has_subscriptions = false;
		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			if ( $product && is_object( $product ) && method_exists( $product, 'get_type' ) && $product->get_type() === 'ever_subscription' ) {
				$has_subscriptions = true;
				break;
			}
		}

		// Only render if subscriptions exist
		if ( ! $has_subscriptions ) {
			return;
		}

		echo '<div class="subscription-details-thankyou" style="margin: 30px 0; padding: 20px; background: #f9f9f9; border: 1px solid #e0e0e0; border-radius: 5px;">';
		echo '<h3 style="margin-top: 0; color: #333;">' . esc_html__( 'Your Subscription Details', $this->plugin_name ) . '</h3>';
		
		echo '<form method="post" class="subscription-preferences-form">';
		wp_nonce_field( 'save_subscription_preferences', 'subscription_prefs_nonce' );
		echo '<input type="hidden" name="order_id" value="' . esc_attr( $order_id ) . '" />';

		$item_count = 0;
		foreach ( $order->get_items() as $item ) {
			$product = $item->get_product();
			if ( $product && is_object( $product ) && method_exists( $product, 'get_type' ) && $product->get_type() === 'ever_subscription' ) {
				$item_id = $item->get_id();
				echo wp_kses_post( $this->get_subscription_details( $product, array( 'data' => $product, 'quantity' => $item->get_quantity(), 'order_id' => $order_id, 'item_id' => $item_id ) ) );
				
				// Per-item preferences
				echo '<div style="margin-top: 15px; padding: 12px; background: #fff; border-left: 3px solid #0073aa; border-radius: 3px;">';
				echo '<p style="margin: 0 0 10px 0;"><strong>' . esc_html__( 'Preferences for this item:', $this->plugin_name ) . '</strong></p>';
				echo '<label style="display: block; margin: 8px 0;">';
				echo '<input type="checkbox" name="auto_renew[' . esc_attr( $item_id ) . ']" value="1" checked /> ';
				echo esc_html__( 'Enable Auto-Renewal', $this->plugin_name );
				echo '</label>';
				echo '<label style="display: block; margin: 8px 0;">';
				echo '<input type="checkbox" name="auto_payment[' . esc_attr( $item_id ) . ']" value="1" checked /> ';
				echo esc_html__( 'Enable Auto-Payment', $this->plugin_name );
				echo '</label>';
				echo '</div>';
				$item_count++;
			}
		}

		echo '<div style="margin-top: 20px; text-align: right;">';
		echo '<button type="submit" class="button button-primary" name="save_subscription_prefs">' . esc_html__( 'Save All Preferences', $this->plugin_name ) . '</button>';
		echo '</div>';
		
		echo '</form>';
		echo '</div>';
	}


	/**
	 * Get formatted subscription details including next payment date.
	 *
	 * @param WC_Product $product Product object
	 * @param array $cart_item Cart item data
	 * @return string HTML formatted subscription details
	 */
	private function get_subscription_details( $product, $cart_item ) {
		if ( ! $product || ! is_object( $product ) || ! method_exists( $product, 'get_type' ) || $product->get_type() !== 'ever_subscription' ) {
			return '';
		}

		$billing_interval = $product->get_meta( '_ever_billing_interval' ) ?: 1;
		$billing_period = $product->get_meta( '_ever_billing_period' ) ?: 'month';
		$sign_up_fee = floatval( $product->get_meta( '_ever_subscription_sign_up_fee' ) ) ?: 0;
		$trial_length = intval( $product->get_meta( '_ever_subscription_trial_length' ) ) ?: 0;
		$trial_period = $product->get_meta( '_ever_subscription_trial_period' ) ?: 'day';
		// Subscriptions are single-quantity items
		$quantity = 1;
		$product_name = $product->get_name();

		// Calculate next payment date
		$next_payment_date = $this->calculate_next_payment_date( $trial_length, $trial_period, $billing_interval, $billing_period );
		// Determine whether signup fee applies (only for guests)
		$apply_signup_fee = ( ! is_user_logged_in() && $sign_up_fee > 0 );
		$first_payment_amount = $apply_signup_fee ? ( $sign_up_fee * $quantity ) : ( $product->get_meta( '_ever_subscription_price' ) ? floatval( $product->get_meta( '_ever_subscription_price' ) ) * $quantity : floatval( $product->get_price() ) * $quantity );

		$billing_text = sprintf( __( 'Every %d %s', $this->plugin_name ), $billing_interval, $billing_period . ( $billing_interval > 1 ? 's' : '' ) );

		$html = '<div style="margin: 10px 0; padding: 10px; background: #fff; border-left: 4px solid #0073aa; border-radius: 3px;">';
		$html .= '<p style="margin: 5px 0;"><strong>' . esc_html( $product_name ) . '</strong></p>';
		$html .= '<p style="margin: 5px 0; color: #666; font-size: 0.9em;">';
		$html .= esc_html__( 'Billing:', $this->plugin_name ) . ' <strong>' . esc_html( $billing_text ) . '</strong>';
		$html .= '</p>';

		if ( $apply_signup_fee ) {
			$html .= '<p style="margin: 5px 0; color: #666; font-size: 0.9em;">';
			$html .= esc_html__( 'Sign-up Fee:', $this->plugin_name ) . ' <strong>' . wc_price( $sign_up_fee ) . '</strong>';
			$html .= '</p>';
			// Show first payment amount (sign-up fee applies to first period for guests)
			$html .= '<p style="margin: 5px 0; color: #28a745; font-size: 0.95em;">';
			$html .= '<strong>' . esc_html__( 'First Payment:', $this->plugin_name ) . '</strong> ' . wc_price( $first_payment_amount );
			$html .= '</p>';
		}

		if ( $trial_length > 0 ) {
			$html .= '<p style="margin: 5px 0; color: #666; font-size: 0.9em;">';
			$html .= esc_html__( 'Free Trial:', $this->plugin_name ) . ' <strong>' . esc_html( $trial_length . ' ' . $trial_period . ( $trial_length > 1 ? 's' : '' ) ) . '</strong>';
			$html .= '</p>';
		}

		$html .= '<p style="margin: 5px 0; color: #28a745; font-size: 0.9em;">';
		$html .= '<strong>' . esc_html__( 'Next Payment Date:', $this->plugin_name ) . '</strong> ' . esc_html( $next_payment_date ) . '';
		$html .= '</p>';
		$html .= '</div>';

		return $html;
	}

	/**
	 * Calculate the next payment date based on trial and billing periods.
	 *
	 * @param int $trial_length Trial length
	 * @param string $trial_period Trial period (day, week, month, year)
	 * @param int $billing_interval Billing interval
	 * @param string $billing_period Billing period
	 * @return string Formatted next payment date
	 */
	private function calculate_next_payment_date( $trial_length, $trial_period, $billing_interval, $billing_period ) {
		$start_date = new DateTime( 'now' );

		// Add trial period if exists
		if ( $trial_length > 0 ) {
			$interval_spec = 'P' . $trial_length;
			switch ( $trial_period ) {
				case 'day':
					$interval_spec .= 'D';
					break;
				case 'week':
					$interval_spec .= 'W';
					break;
				case 'month':
					$interval_spec .= 'M';
					break;
				case 'year':
					$interval_spec .= 'Y';
					break;
			}
			$start_date->add( new DateInterval( $interval_spec ) );
		}

		// Add first billing period
		$interval_spec = 'P' . $billing_interval;
		switch ( $billing_period ) {
			case 'day':
				$interval_spec .= 'D';
				break;
			case 'week':
				$interval_spec .= 'W';
				break;
			case 'month':
				$interval_spec .= 'M';
				break;
			case 'year':
				$interval_spec .= 'Y';
				break;
		}
		$start_date->add( new DateInterval( $interval_spec ) );

		return $start_date->format( get_option( 'date_format' ) );
	}

}