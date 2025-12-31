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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/eversubscription-public.js', array( 'jquery' ), $this->version, false );

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
		$sign_up_fee = $product->get_meta( '_ever_subscription_sign_up_fee' ) ?: 0;
		$trial_length = $product->get_meta( '_ever_subscription_trial_length' ) ?: 0;
		$trial_period = $product->get_meta( '_ever_subscription_trial_period' ) ?: 'day';

		$currency_symbol = get_woocommerce_currency_symbol();
		$billing_text = sprintf(
			_n( 'Every %d %s', 'Every %d %ss', $billing_interval, $this->plugin_name ),
			$billing_interval,
			$billing_period
		);

		echo '<div class="ever-subscription-info" style="margin: 20px 0; padding: 15px; background: #f5f5f5; border-radius: 5px;">';
		echo '<h3 style="margin-top: 0;">' . esc_html__( 'Subscription Details', $this->plugin_name ) . '</h3>';
		echo '<p><strong>' . esc_html__( 'Price:', $this->plugin_name ) . '</strong> ' . esc_html( $currency_symbol . number_format( $subscription_price, 2 ) ) . ' ' . esc_html( $billing_text ) . '</p>';

		if ( $sign_up_fee > 0 ) {
			echo '<p><strong>' . esc_html__( 'Sign-up Fee:', $this->plugin_name ) . '</strong> ' . esc_html( $currency_symbol . number_format( $sign_up_fee, 2 ) ) . '</p>';
		}

		if ( $trial_length > 0 ) {
			echo '<p><strong>' . esc_html__( 'Free Trial:', $this->plugin_name ) . '</strong> ' . esc_html( $trial_length . ' ' . $trial_period . '(s)' ) . '</p>';
		}

		echo '</div>';
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

}
