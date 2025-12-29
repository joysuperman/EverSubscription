<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.joymojumder.com
 * @since      1.0.0
 *
 * @package    Eversubscription
 * @subpackage Eversubscription/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Eversubscription
 * @subpackage Eversubscription/admin
 * @author     JOYSUPERMAN <joymojumder529@gmail.com>
 */
class Eversubscription_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/eversubscription-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/eversubscription-admin.js', ['jquery'], $this->version, true );

		/**
		 * Block-based admin script
		 * 
		 */
		wp_enqueue_script( $this->plugin_name.'_react', plugin_dir_url( __FILE__ ) . 'build/eversubscription-admin.js', ['wp-element','wp-api-fetch', 'jquery'], $this->version, false );

	}

	/**
	 * Add menu admin page
	 *
	 * @since    1.0.0
	 */

	public function add_plugin_admin_menu() {
		add_menu_page(
			'EverSubscription Admin',
			'EverSubscription',
			'manage_options',
			'eversubscription-admin',
			[ $this, 'display_plugin_admin_page' ],
			'dashicons-admin-generic',
			6
		);
	}

	/**
	 * Display the admin page
	 *
	 * @since    1.0.0
	 */

	public function display_plugin_admin_page() {
		require_once plugin_dir_path( __FILE__ ) . 'partials/eversubscription-admin-display.php';
	}

	/**
	 * Add custom product type
	 *
	 * @since    1.0.0
	 */

	public function add_woo_product_type($types) {
        $types['ever_subscription'] = __('Ever Subscription', $this->plugin_name);
        return $types;
    }

    // public function register_class($classname, $product_type, $post_type, $product_id) {
    //     if ($product_type === 'ever_subscription') {
    //         $classname = 'WC_Product_Ever_Subscription';
    //     }
    //     return $classname;
    // }

	public function ever_subscription_product_tab($tabs) {
    $tabs['ever_subscription_tab'] = array(
        'label'    => __('Subscription', $this->plugin_name),
        'target'   => 'ever_subscription_data',
        // Add "show_if_ever_subscription" to the class array
        'class'    => array('show_if_ever_subscription'),
        'priority' => 5, 
    );
    return $tabs;
}


	public function ever_subscription_product_tab_content() {
		global $post;
		$product = wc_get_product( $post->ID );
		// Get the active currency symbol (e.g., $, €, £)
		$currency_symbol = get_woocommerce_currency_symbol();

		echo '<div id="ever_subscription_data" class="panel woocommerce_options_panel show_if_ever_subscription">';
			
			// Subscription Pricing Group
			echo '<div class="options_group">';
				// Subscription Price
				woocommerce_wp_text_input([
					'id'          => '_ever_subscription_price',
					'label'       => sprintf( __( 'Subscription Price (%s)', $this->plugin_name ), $currency_symbol ),
					'type'        => 'number',
					'custom_attributes' => ['step' => '0.01', 'min' => '0'],
					'value'       => $product->get_meta('_ever_subscription_price'), 
					'desc_tip'    => true,
					'description' => __('Enter the subscription price.', $this->plugin_name),
				]);

				// Billing Interval
				woocommerce_wp_text_input([
					'id'          => '_ever_billing_interval',
					'label'       => __('Billing Interval', $this->plugin_name),
					'type'        => 'number',
					'custom_attributes' => ['step' => '1', 'min' => '1'],
					'value'       => $product->get_meta('_ever_billing_interval') ?: 1,
				]);

				// Billing Period
				woocommerce_wp_select([
					'id'          => '_ever_billing_period',
					'label'       => __('Billing Period', $this->plugin_name),
					'options'     => ['day'=>'Day','week'=>'Week','month'=>'Month','year'=>'Year'],
					'value'       => $product->get_meta('_ever_billing_period') ?: 'month',
				]);

				// Subscription Length
				woocommerce_wp_select([
					'id'      => '_ever_subscription_length',
					'label'   => __('Expire After', $this->plugin_name),
					'options' => [
						'0' => __('Never expire', $this->plugin_name),
						'1' => '1 year', '2' => '2 years', '3' => '3 years', '4' => '4 years', '5' => '5 years',
					],
					'value'   => $product->get_meta('_ever_subscription_length') ?: '0',
				]);
			echo '</div>';

			// Fees & Trials Group
			echo '<div class="options_group">';
				// Sign-up Fee
				woocommerce_wp_text_input([
					'id'          => '_ever_subscription_sign_up_fee',
					'label'       => sprintf( __( 'Sign-up Fee (%s)', $this->plugin_name ), $currency_symbol ),
					'type'        => 'number',
					'custom_attributes' => ['step' => '0.01', 'min' => '0'],
					'value'       => $product->get_meta('_ever_subscription_sign_up_fee'),
				]);

				// Free Trial Length
				woocommerce_wp_text_input([
					'id'          => '_ever_subscription_trial_length',
					'label'       => __('Free Trial', $this->plugin_name),
					'type'        => 'number',
					'value'       => $product->get_meta('_ever_subscription_trial_length') ?: '0',
				]);

				// Free Trial Period
				woocommerce_wp_select([
					'id'      => '_ever_subscription_trial_period',
					'label'   => __('Trial Period', $this->plugin_name),
					'options' => ['day'=>'Days','week'=>'Weeks','month'=>'Months','year'=>'Years'],
					'value'   => $product->get_meta('_ever_subscription_trial_period') ?: 'day',
				]);
			echo '</div>';

			// Sale Price Group
			echo '<div class="options_group pricing show_if_ever_subscription">';
    
				// Sale Price Input
				woocommerce_wp_text_input([
					'id'          => '_ever_sale_price',
					'label'       => sprintf( __( 'Sale price (%s)', $this->plugin_name ), $currency_symbol ),
					'data_type'   => 'price',
					'type'        => 'number',
					'custom_attributes' => ['step' => '0.01', 'min' => '0'],
					'value'       => $product->get_meta('_ever_sale_price'),
					'desc_tip'    => false,
					'description' => '<span class="description"><span id="sale-price-period">every year</span> <a href="#" class="sale_schedule">' . __( 'Schedule', 'woocommerce' ) . '</a></span>',
				]);

				// Date Fields Wrapper
				$show_dates = ( $product->get_meta('_sale_price_dates_from') || $product->get_meta('_sale_price_dates_to') ) ? '' : 'display:none;';
				
				echo '<p class="form-field sale_price_dates_fields" style="' . $show_dates . '">';
					echo '<label for="_sale_price_dates_from">' . __( 'Sale price dates', 'woocommerce' ) . '</label>';
					
					// From Date
					echo '<input type="text" class="short date-picker" name="_sale_price_dates_from" id="_sale_price_dates_from" value="' . esc_attr( $product->get_meta('_sale_price_dates_from') ) . '" placeholder="YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />';
					
					// To Date
					echo '<input type="text" class="short date-picker" name="_sale_price_dates_to" id="_sale_price_dates_to" value="' . esc_attr( $product->get_meta('_sale_price_dates_to') ) . '" placeholder="YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />';
					
					echo '<a href="#" class="description cancel_sale_schedule">' . __( 'Cancel', 'woocommerce' ) . '</a>';
					echo wc_help_tip( __( 'The sale will start at 00:00:00 of "From" date and end at 23:59:59 of "To" date.', 'woocommerce' ) );
				echo '</p>';

			echo '</div>';

		echo '</div>';
	}

	public function ever_subscription_save_product_data( $product ) {
		$post_id = $product->get_id();

		// 1. FORCE the product type persistence
		if ( isset( $_POST['product-type'] ) && 'ever_subscription' === $_POST['product-type'] ) {
			wp_set_object_terms( $post_id, 'ever_subscription', 'product_type' );
		}

		// 2. Map all fields to their sanitization types
		$fields = [
			'_ever_subscription_price'         => 'wc_format_decimal',
			'_ever_billing_interval'           => 'absint',
			'_ever_billing_period'             => 'sanitize_text_field',
			'_ever_subscription_length'        => 'sanitize_text_field',
			'_ever_subscription_sign_up_fee'   => 'wc_format_decimal',
			'_ever_subscription_trial_length'  => 'absint',
			'_ever_subscription_trial_period'  => 'sanitize_text_field',
			'_ever_sale_price'                 => 'wc_format_decimal',
		];

		foreach ( $fields as $key => $sanitize_callback ) {
			if ( isset( $_POST[ $key ] ) ) {
				$value = call_user_func( $sanitize_callback, $_POST[ $key ] );
				
				// Save to Product Meta
				$product->update_meta_data( $key, $value );

				// 3. Sync with Core WC Price Fields (Essential for Shop Display)
				if ( '_ever_subscription_price' === $key ) {
					$product->set_regular_price( $value );
					// If there's no sale price currently being set, set main price to regular price
					if ( empty( $_POST['_ever_sale_price'] ) ) {
						$product->set_price( $value );
					}
				}
				
				if ( '_ever_sale_price' === $key) {
					$product->set_sale_price( $value );
					$product->set_price( $value );
				}
			}
		}

		$product->save();
	}
}