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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/eversubscription-admin.js', ['wp-element','wp-api-fetch', 'jquery'], $this->version, false );

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

    public function register_class($classname, $product_type, $post_type, $product_id) {
        if ($product_type === 'ever_subscription') {
            $classname = 'WC_Product_Ever_Subscription';
        }
        return $classname;
    }

	public function ever_subscription_product_tab($tabs) {
		$tabs['ever_subscription_tab'] = array(
			'label'    => __('Subscription', $this->plugin_name),
			'target'   => 'ever_subscription_data',
			'class'    => ['show_if_ever_subscription'],
			'priority' => 21,
		);
		return $tabs;
	}

	public function ever_subscription_product_tab_content() {
		global $post;

		echo '<div id="ever_subscription_data" class="panel woocommerce_options_panel show_if_ever_subscription">';

			// Subscription Price
			echo '<div class="options_group subscription_pricing">';

			woocommerce_wp_text_input([
				'id'                => '_ever_subscription_price',
				'label'             => __('Subscription Price (£)', $this->plugin_name),
				'type'              => 'number',
				'custom_attributes' => ['step' => '0.01', 'min' => '0'],
				'value'             => get_post_meta($post->ID, '_ever_subscription_price', true) ?: '',
				'description'       => __('Enter the subscription price.', $this->plugin_name),
				'desc_tip'          => true,
			]);

			// Billing Interval
			woocommerce_wp_text_input([
				'id'                => '_ever_billing_interval',
				'label'             => __('Billing Interval', $this->plugin_name),
				'type'              => 'number',
				'custom_attributes' => ['step' => '1', 'min' => '1'],
				'value'             => get_post_meta($post->ID, '_ever_billing_interval', true) ?: 1,
				'description'       => __('How often to charge the subscription.', $this->plugin_name),
				'desc_tip'          => true,
			]);
			// Billing Period (radio buttons)
			woocommerce_wp_select([
				'id'                => '_ever_billing_period',
				'label'             => __('Billing Period', $this->plugin_name),
				'type'              => 'select',
				'options'           => ['day'=>'Day','week'=>'Week','month'=>'Month','year'=>'Year'],
				'value'             => get_post_meta($post->ID, '_ever_billing_period', true) ?: 'month',
			]);

			// Subscription Length (Expire After)
			woocommerce_wp_select([
				'id'      => '_ever_subscription_length',
				'label'   => __('Expire After', $this->plugin_name),
				'options' => [
					'0' => __('Never expire', $this->plugin_name),
					'1' => '1 year',
					'2' => '2 years',
					'3' => '3 years',
					'4' => '4 years',
					'5' => '5 years',
				],
				'value'   => get_post_meta($post->ID, '_ever_subscription_length', true) ?: '0',
				'description' => __('Automatically expire the subscription after this length of time.', $this->plugin_name),
				'desc_tip' => true,
			]);

			// Sign-up Fee
			woocommerce_wp_text_input([
				'id'                => '_ever_subscription_sign_up_fee',
				'label'             => __('Sign-up Fee (£)', $this->plugin_name),
				'type'              => 'number',
				'custom_attributes' => ['step' => '0.01', 'min' => '0'],
				'value'             => get_post_meta($post->ID, '_ever_subscription_sign_up_fee', true) ?: '',
				'description'       => __('Optional amount charged at the start of the subscription.', $this->plugin_name),
				'desc_tip'          => true,
			]);

			// Free Trial
			echo '<p class="form-field">';
			woocommerce_wp_text_input([
				'id'                => '_ever_subscription_trial_length',
				'label'             => __('Free Trial', $this->plugin_name),
				'type'              => 'number',
				'custom_attributes' => ['step' => '1', 'min' => '0'],
				'value'             => get_post_meta($post->ID, '_ever_subscription_trial_length', true) ?: '0',
				'description'       => __('Number of days/weeks/months for a free trial.', $this->plugin_name),
				'desc_tip'          => true,
			]);

			woocommerce_wp_select([
				'id'      => '_ever_subscription_trial_period',
				'label'   => __('', $this->plugin_name),
				'options' => ['day'=>'Days','week'=>'Weeks','month'=>'Months','year'=>'Years'],
				'value'   => get_post_meta($post->ID, '_ever_subscription_trial_period', true) ?: 'day',
			]);
			echo '</p>';

			echo '</div>'; // end options_group

			// Subscription Pricing Group
			echo '<div class="options_group pricing show_if_ever_subscription">';

			// Sale Price
			woocommerce_wp_text_input([
				'id'                => '_ever_sale_price',
				'label'             => __('Sale price (£)', $this->plugin_name),
				'type'              => 'number',
				'custom_attributes' => ['step' => '0.01', 'min' => '0'],
				'value'             => get_post_meta($post->ID, '_ever_sale_price', true) ?: '',
				'description'       => '',
				'desc_tip'          => true,
			]);
			echo '<span id="sale-price-period">' . __('every year', $this->plugin_name) . '</span> <a href="#" class="sale_schedule">' . __('Schedule', $this->plugin_name) . '</a>';
			// Sale Price Dates (hidden by default)
			$sale_from = get_post_meta($post->ID, '_ever_sale_price_dates_from', true);
			$sale_to   = get_post_meta($post->ID, '_ever_sale_price_dates_to', true);

			echo '<p class="form-field sale_price_dates_fields" style="display:none;">';
			echo '<label for="_ever_sale_price_dates_from">' . __('Sale price dates', $this->plugin_name) . '</label>';
			echo '<input type="date" class="short hasDatepicker" name="_ever_sale_price_dates_from" id="_ever_sale_price_dates_from" value="' . esc_attr($sale_from) . '" placeholder="From… YYYY-MM-DD"></br>';
			echo '<input type="date" class="short hasDatepicker" name="_ever_sale_price_dates_to" id="_ever_sale_price_dates_to" value="' . esc_attr($sale_to) . '" placeholder="To… YYYY-MM-DD">';
			echo '<a href="#" class="description cancel_sale_schedule">' . __('Cancel', $this->plugin_name) . '</a>';
			echo '<span class="woocommerce-help-tip" tabindex="0" aria-label="' . esc_attr__('The sale will start at 00:00:00 of "From" date and end at 23:59:59 of "To" date.', $this->plugin_name) . '"></span>';
			echo '</p>';

			echo '</div>'; // end options_group
		
		echo '</div>'; // end panel
	}


	public function ever_subscription_save_product_data( $product ) {

		// if ( ! $product instanceof WC_Product ) {
		// 	return;
		// }

		// if ( $product->get_type() !== 'ever_subscription' ) {
		// 	return;
		// }

		// if ( isset( $_POST['_ever_subscription_price'] ) ) {
		// 	$price = wc_format_decimal( wp_unslash( $_POST['_ever_subscription_price'] ) );
		// 	$product->set_regular_price( $price );
		// 	$product->set_price( $price );
		// 	$product->update_meta_data( '_ever_subscription_price', $price );
		// }

		// if ( isset( $_POST['_ever_billing_interval'] ) ) {
		// 	$product->update_meta_data(
		// 		'_ever_billing_interval',
		// 		absint( $_POST['_ever_billing_interval'] )
		// 	);
		// }

		// if ( isset( $_POST['_ever_billing_period'] ) ) {
		// 	$product->update_meta_data(
		// 		'_ever_billing_period',
		// 		sanitize_text_field( $_POST['_ever_billing_period'] )
		// 	);
		// }

		// $product->save();


		 if ( $product->get_type() !== 'ever_subscription' ) {
        	return;
		}

		// Save fields here
		$price = wc_format_decimal( $_POST['_ever_subscription_price'] ?? '' );

		if ( $price !== '' ) {
			$product->set_regular_price( $price );
			$product->set_price( $price );
			$product->update_meta_data( '_ever_subscription_price', $price );
		}

		$product->save(die($price));
    }

   




}