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
	 * Output variation subscription fields in the Variations pricing tab.
	 *
	 * @param int $loop Variation loop index
	 * @param array $variation_data Data for the variation
	 * @param WC_Product_Variation $variation Variation product object
	 */
	public function ever_subscription_variation_options( $loop, $variation_data, $variation ) {
        $variation_id = 0;
		if ( is_object( $variation ) ) {
			if ( method_exists( $variation, 'get_id' ) ) {
				$variation_id = $variation->get_id();
			} elseif ( isset( $variation->ID ) ) {
				$variation_id = $variation->ID;
			}
		} else {
			$variation_id = $variation;
		}
		$currency_symbol = get_woocommerce_currency_symbol();

        echo '<div class="ever_subscription_variation_settings">';
        echo '<strong>' . __( 'Ever Subscription Settings', $this->plugin_name ) . '</strong>';

        // 1. Subscription Price & Billing Interval (Row 1)
        echo '<div class="options_group form-row-group">';
            woocommerce_wp_text_input( array(
                'id'            => "_ever_subscription_price[{$loop}]",
                'label'         => sprintf( __( 'Subscription Price (%s)', $this->plugin_name ), $currency_symbol ),
                'value'         => get_post_meta( $variation_id, '_ever_subscription_price', true ),
                'wrapper_class' => 'form-row form-row-first',
                'data_type'     => 'price',
            ) );

            woocommerce_wp_text_input( array(
                'id'            => "_ever_billing_interval[{$loop}]",
                'label'         => __( 'Billing Interval', $this->plugin_name ),
                'value'         => get_post_meta( $variation_id, '_ever_billing_interval', true ) ?: 1,
                'type'          => 'number',
                'wrapper_class' => 'form-row form-row-last',
            ) );
        echo '</div>';

        // 2. Billing Period & Subscription Length (Row 2)
        echo '<div class="options_group form-row-group">';
            woocommerce_wp_select( array(
                'id'            => "_ever_billing_period[{$loop}]",
                'label'         => __( 'Billing Period', $this->plugin_name ),
                'options'       => array( 'day' => 'Day', 'week' => 'Week', 'month' => 'Month', 'year' => 'Year' ),
                'value'         => get_post_meta( $variation_id, '_ever_billing_period', true ) ?: 'month',
                'wrapper_class' => 'form-row form-row-first',
            ) );

            woocommerce_wp_select( array(
                'id'            => "_ever_subscription_length[{$loop}]",
                'label'         => __( 'Expire After', $this->plugin_name ),
                'options'       => array(
                    '0' => 'Never expire', '1' => '1 year', '2' => '2 years', '3' => '3 years', '4' => '4 years', '5' => '5 years',
                ),
                'value'         => get_post_meta( $variation_id, '_ever_subscription_length', true ) ?: '0',
                'wrapper_class' => 'form-row form-row-last',
            ) );
        echo '</div>';

        // 3. Sign-up Fee & Trial Length (Row 3)
        echo '<div class="options_group form-row-group">';
            woocommerce_wp_text_input( array(
                'id'            => "_ever_subscription_sign_up_fee[{$loop}]",
                'label'         => sprintf( __( 'Sign-up Fee (%s)', $this->plugin_name ), $currency_symbol ),
                'value'         => get_post_meta( $variation_id, '_ever_subscription_sign_up_fee', true ),
                'wrapper_class' => 'form-row form-row-first',
                'data_type'     => 'price',
            ) );

            woocommerce_wp_text_input( array(
                'id'            => "_ever_subscription_trial_length[{$loop}]",
                'label'         => __( 'Trial Length', $this->plugin_name ),
                'value'         => get_post_meta( $variation_id, '_ever_subscription_trial_length', true ) ?: 0,
                'type'          => 'number',
                'wrapper_class' => 'form-row form-row-last',
            ) );
        echo '</div>';

        // 4. Sale Price & Dates
        echo '<div class="options_group form-row-group">';
            woocommerce_wp_text_input( array(
                'id'            => "_ever_sale_price[{$loop}]",
                'label'         => __( 'Sale Price', $this->plugin_name ),
                'value'         => get_post_meta( $variation_id, '_ever_sale_price', true ),
                'wrapper_class' => 'form-row form-row-first',
                'data_type'     => 'price',
            ) );

            echo '<div class="form-row form-row-last">';
                echo '<label>' . esc_html__( 'Sale Dates (From/To)', $this->plugin_name ) . '</label>';
                echo '<input type="text" class="short date-picker" name="_ever_sale_price_dates_from[' . $loop . ']" value="' . esc_attr( get_post_meta( $variation_id, '_ever_sale_price_dates_from', true ) ) . '" placeholder="YYYY-MM-DD" style="width:45%; display:inline;" /> ';
                echo '<input type="text" class="short date-picker" name="_ever_sale_price_dates_to[' . $loop . ']" value="' . esc_attr( get_post_meta( $variation_id, '_ever_sale_price_dates_to', true ) ) . '" placeholder="YYYY-MM-DD" style="width:45%; display:inline;" />';
            echo '</div>';
        echo '</div>';

        // 5. Additional Notes
        woocommerce_wp_textarea_input( array(
            'id'            => "_ever_aditional_note[{$loop}]",
            'label'         => __( 'Additional Note', $this->plugin_name ),
            'value'         => get_post_meta( $variation_id, '_ever_aditional_note', true ),
            'wrapper_class' => 'form-row form-row-full',
        ) );

        echo '</div>';
    }


	/**
	 * Save per-variation subscription fields.
	 *
	 * @param int $variation_id Variation ID being saved
	 * @param int $i Loop index
	 */
	public function ever_subscription_save_variation( $variation_id, $i ) {
        $fields = array(
            '_ever_subscription_price'        => 'wc_format_decimal',
            '_ever_billing_interval'          => 'absint',
            '_ever_billing_period'            => 'sanitize_text_field',
            '_ever_subscription_length'       => 'sanitize_text_field',
            '_ever_subscription_sign_up_fee'  => 'wc_format_decimal',
            '_ever_subscription_trial_length' => 'absint',
            '_ever_sale_price'                => 'wc_format_decimal',
            '_ever_sale_price_dates_from'     => 'sanitize_text_field',
            '_ever_sale_price_dates_to'       => 'sanitize_text_field',
            '_ever_aditional_note'            => 'sanitize_textarea_field',
        );

        foreach ( $fields as $key => $sanitize_cb ) {
            if ( isset( $_POST[ $key ][ $i ] ) ) {
                $value = wp_unslash( $_POST[ $key ][ $i ] );
                
                if ( '' === $value ) {
                    delete_post_meta( $variation_id, $key );
                    continue;
                }

                $sanitized = call_user_func( $sanitize_cb, $value );
                update_post_meta( $variation_id, $key, $sanitized );

                // Sync Core WC Fields for variations
                if ( '_ever_subscription_price' === $key ) {
                    update_post_meta( $variation_id, '_regular_price', $sanitized );
                }
                if ( '_ever_sale_price' === $key ) {
                    update_post_meta( $variation_id, '_sale_price', $sanitized );
                }
                if ( '_ever_sale_price_dates_from' === $key ) {
                    update_post_meta( $variation_id, '_sale_price_dates_from', strtotime( $sanitized ) );
                }
                if ( '_ever_sale_price_dates_to' === $key ) {
                    update_post_meta( $variation_id, '_sale_price_dates_to', strtotime( $sanitized . ' 23:59:59' ) );
                }
            }
        }
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
		$screen = get_current_screen();

		// 1. Load for WooCommerce Product Page
		if ( $screen && ( $screen->id === 'product' || $screen->post_type === 'product' ) ) {
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/eversubscription-admin.js', ['jquery'], $this->version, true );
		}

		// 2. Load for EverSubscription Admin Page
		if ( $screen && $screen->id === 'toplevel_page_eversubscription-admin' ) {
			
			// Use WordPress Core provided scripts for stability
			wp_enqueue_script( 'wp-api-fetch' );
			wp_enqueue_script( 'wp-element' ); // This includes React & ReactDOM
			
			$handle = $this->plugin_name . '_react';
			wp_enqueue_script( 
				$handle, 
				plugin_dir_url( __FILE__ ) . 'build/eversubscription-admin.js', 
				['wp-element', 'wp-api-fetch', 'jquery'], 
				$this->version, 
				true 
			);
			
			// Crucial: Provide the correct REST root
			$roles = function_exists( 'wp_roles' ) ? wp_roles()->get_names() : array();
			wp_localize_script( $handle, 'eversubscriptionApi', array(
				'root'  => esc_url_raw( rest_url() ), // Base WP-JSON URL
				'nonce' => wp_create_nonce( 'wp_rest' ),
				'roles' => $roles,
			) );
		}

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
	 * Map the `ever_subscription` product type to our frontend product class.
	 *
	 * @param string $classname Determined classname
	 * @param string $product_type Product type identifier
	 * @param string $post_type Post type
	 * @param int    $product_id Product ID
	 * @return string
	 */
	public function register_product_class( $classname, $product_type, $post_type, $product_id ) {
		// Map simple subscription product type
		if ( 'ever_subscription' === $product_type ) {
			return 'WC_Product_Ever_Subscription';
		}

		// Map variable subscription product type
		if ( 'ever_subscription_variable' === $product_type ) {
			return 'WC_Product_Ever_Subscription_Variable';
		}

		// If WooCommerce asks for 'variable', check parent product type
		if ( 'variable' === $product_type ) {
			$terms = wp_get_object_terms( $product_id, 'product_type', array( 'fields' => 'slugs' ) );
			if ( ! is_wp_error( $terms ) && is_array( $terms ) && in_array( 'ever_subscription_variable', $terms, true ) ) {
				return 'WC_Product_Ever_Subscription_Variable';
			}
		}

		// Variation instances: check parent product's type
		if ( 'variation' === $product_type ) {
			$parent_id = wp_get_post_parent_id( $product_id );
			if ( $parent_id ) {
				$terms = wp_get_object_terms( $parent_id, 'product_type', array( 'fields' => 'slugs' ) );
				if ( ! is_wp_error( $terms ) && is_array( $terms ) && in_array( 'ever_subscription_variable', $terms, true ) ) {
					return 'WC_Product_Ever_Subscription_Variation';
				}
			}
		}

		return $classname;
	}

	public function eversubscription_save_post_product( $post_id ) {
		 $product = wc_get_product($post_id);
		if ( $product && $product->is_type('ever_subscription_variable') ) {
			$prices = [];
			foreach ( $product->get_children() as $child_id ) {
				$child = wc_get_product($child_id);
				if ( $child ) {
					$prices[] = $child->get_price();
				}
			}
			if ( $prices ) {
				$product->set_price(min($prices));
				$product->save();
			}
		}
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
	 * Registers the "Ever Subscription" custom product type in WooCommerce
	 *
	 * @since    1.0.0
	 * @param array $types Array of WooCommerce product types
	 * @return array Updated product types array
	 */
	public function add_woo_product_type($types) {
		$types['ever_subscription'] = __('Ever Subscription', $this->plugin_name);
		$types['ever_subscription_variable'] = __('Ever Subscription (Variable)', $this->plugin_name);
        return $types;
    }


	/**
	 * Register the data store for the custom variable product type.
	 * This prevents the array_keys() NULL error during AJAX calls.
	 */
	public function register_data_stores( $stores ) {
		$stores['product-ever_subscription_variable'] = 'WC_Product_Variable_Data_Store_CPT';
		return $stores;
	}
	/**
	 * Add custom product tab
	 *
	 * Registers the "Subscription" tab in the WooCommerce product data metabox
	 *
	 * @since    1.0.0
	 * @param array $tabs Array of WooCommerce product data tabs
	 * @return array Updated product tabs array
	 */
	public function ever_subscription_product_tab($tabs) {
		$tabs['ever_subscription_tab'] = array(
			'label'    => __('Subscription', $this->plugin_name),
			'target'   => 'ever_subscription_data',
			'class'    => array('show_if_ever_subscription'),
			'priority' => 5, 
		);
		return $tabs;
	}

	/**
	 * Ensures the native 'Variations' tab is visible for our custom variable type.
	 *
	 * @param array $tabs Existing product data tabs.
	 * @return array Updated tabs.
	 */
	public function add_variation_support_to_tabs( $tabs ) {
		// We add our custom product type class to the Variations tab's class list.
		// WooCommerce JS looks for 'show_if_{product_type}' to toggle visibility.
		if ( isset( $tabs['variations'] ) ) {
			$tabs['variations']['class'][] = 'show_if_ever_subscription_variable';
		}
		return $tabs;
	}

	public function ever_subscription_variations_allowed_product_types( $types ) {
		$types[] = 'ever_subscription_variable';
		return $types;
	}

	/**
	 * Output custom product tab content
	 *
	 * Renders the subscription fields panel with all custom product fields
	 *
	 * @since    1.0.0
	 */
	public function ever_subscription_product_tab_content() {
		global $post;
		
		// Check if post exists
		if ( ! $post || ! isset( $post->ID ) ) {
			return;
		}

		$product = wc_get_product( $post->ID );
		
		// Check if product exists
		if ( ! $product ) {
			return;
		}

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

				// Date Fields Wrapper: check both our custom meta and core WC meta
				$ever_from = $product->get_meta('_ever_sale_price_dates_from');
				$core_from = $product->get_meta('_sale_price_dates_from');
				$ever_to   = $product->get_meta('_ever_sale_price_dates_to');
				$core_to   = $product->get_meta('_sale_price_dates_to');
				$show_dates = ( $ever_from || $core_from || $ever_to || $core_to ) ? '' : 'display:none;';
				
				echo '<p class="form-field sale_price_dates_fields" style="' . $show_dates . '">';
					echo '<label for="_ever_sale_price_dates_from">' . __( 'Sale price dates', 'woocommerce' ) . '</label>';
					echo wc_help_tip( __( 'The sale will start at 00:00:00 of "From" date and end at 23:59:59 of "To" date.', 'woocommerce' ) );
					
					// From Date
					echo '<input type="text" class="short date-picker" name="_ever_sale_price_dates_from" id="_ever_sale_price_dates_from" value="' . esc_attr( $product->get_meta('_ever_sale_price_dates_from') ) . '" placeholder="YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />';
					
					// To Date
					echo '<input type="text" class="short date-picker" name="_ever_sale_price_dates_to" id="_ever_sale_price_dates_to" value="' . esc_attr( $product->get_meta('_ever_sale_price_dates_to') ) . '" placeholder="YYYY-MM-DD" maxlength="10" pattern="[0-9]{4}-(0[1-9]|1[012])-(0[1-9]|1[0-9]|2[0-9]|3[01])" />';
					
					echo '<a href="#" class="description cancel_sale_schedule">' . __( 'Cancel', 'woocommerce' ) . '</a>';
				echo '</p>';

			echo '</div>';

			echo '<div class="options_group pricing show_if_ever_subscription">';
				// Display notice about price synchronization
				echo '<p class="form-field">';
					woocommerce_wp_textarea_input([
						'id'            => '_ever_aditional_note',
						'label'         => __('Additional Note', $this->plugin_name),
						'desc_tip'    => true,
						'description'   => __( 'Add additional note for customer', $this->plugin_name ),
					] );
				echo '</p>';
			echo '</div>';

		echo '</div>';
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
			if ( $product && ( $product->is_type( 'ever_subscription' ) || $product->is_type( 'ever_subscription_variable' ) ) ) {
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
	 * Save custom subscription product data
	 *
	 * Handles sanitization and storage of subscription fields
	 * Also syncs custom prices with WooCommerce core price fields
	 *
	 * @since    1.0.0
	 * @param WC_Product $product The product object being saved
	 */
	public function ever_subscription_save_product_data( $product ) {
		if ( ! $product || ! $product->get_id() ) {
			return;
		}

		$post_id = $product->get_id();
		$posted_type = isset( $_POST['product-type'] ) ? sanitize_text_field( wp_unslash( $_POST['product-type'] ) ) : '';

		// Allow either the simple or variable subscription product types to be saved here
		if ( 'ever_subscription' === $posted_type ) {
			wp_set_object_terms( $post_id, 'ever_subscription', 'product_type' );
			$product_type = 'ever_subscription';
		} elseif ( 'ever_subscription_variable' === $posted_type ) {
			wp_set_object_terms( $post_id, 'ever_subscription_variable', 'product_type' );
			$product_type = 'ever_subscription_variable';
		} else {
			$product_type = $product->get_type();
		}

		if ( 'ever_subscription' !== $product_type && 'ever_subscription_variable' !== $product_type ) {
			return;
		}

		$fields = [
			'_ever_subscription_price'        => 'wc_format_decimal',
			'_ever_billing_interval'          => 'absint',
			'_ever_billing_period'            => 'sanitize_text_field',
			'_ever_subscription_length'       => 'sanitize_text_field',
			'_ever_subscription_sign_up_fee'  => 'wc_format_decimal',
			'_ever_subscription_trial_length' => 'absint',
			'_ever_subscription_trial_period' => 'sanitize_text_field',
			'_ever_sale_price'                => 'wc_format_decimal',
			'_ever_sale_price_dates_from'     => 'sanitize_text_field',
			'_ever_sale_price_dates_to'       => 'sanitize_text_field',
			'_ever_aditional_note'       	  => 'sanitize_textarea_field',
		];

		foreach ( $fields as $key => $sanitize_callback ) {
			if ( isset( $_POST[ $key ] ) && '' !== $_POST[ $key ] ) {
				$value = wp_unslash( $_POST[ $key ] );
				$sanitized_value = call_user_func( $sanitize_callback, $value );
				
				// 1. Update our custom meta
				$product->update_meta_data( $key, $sanitized_value );

				// 2. Sync Regular Price
				if ( '_ever_subscription_price' === $key ) {
					$product->set_regular_price( $sanitized_value );
				}

				// 3. Sync Sale Price
				if ( '_ever_sale_price' === $key ) {
					$product->set_sale_price( $sanitized_value );
				}

				// 4. Sync Sale Dates (Crucial Part)
				if ( '_ever_sale_price_dates_from' === $key ) {
					$timestamp = ! empty( $sanitized_value ) ? strtotime( $sanitized_value ) : '';
					$product->set_date_on_sale_from( $timestamp );
					// Sync core meta for legacy support
					$product->update_meta_data( '_sale_price_dates_from', $timestamp );
				}

				if ( '_ever_sale_price_dates_to' === $key ) {
					$timestamp = ! empty( $sanitized_value ) ? strtotime( $sanitized_value ) : '';
					// For "To" dates, WC usually assumes the end of the day (23:59:59)
					if ( ! empty( $timestamp ) ) {
						$timestamp = strtotime( $sanitized_value . ' 23:59:59' );
					}
					$product->set_date_on_sale_to( $timestamp );
					$product->update_meta_data( '_sale_price_dates_to', $timestamp );
				}
				if ( '_ever_aditional_note' === $key ) {
					$product->update_meta_data( '_ever_aditional_note', $sanitized_value );
				}

			} else {
				// Clear data if input is empty
				$product->delete_meta_data( $key );
				
				if ( '_ever_sale_price' === $key ) $product->set_sale_price( '' );
				if ( '_ever_sale_price_dates_from' === $key ) $product->set_date_on_sale_from( '' );
				if ( '_ever_sale_price_dates_to' === $key ) $product->set_date_on_sale_to( '' );
			}
		}

		// FINAL SYNC: Update the active 'price' based on sale logic
		// WooCommerce handles this automatically inside save() if set_regular_price/set_sale_price were used correctly
		$product->save();
	}

	/**
	 * Cron handler for processing recurring payments.
	 *
	 * Uses existing procedural function if available, otherwise falls back
	 * to calling the subscription class directly.
	 *
	 * @since 1.0.0
	 */
	public function eversubscription_process_recurring_payments() {
		if ( function_exists( 'eversubscription_process_recurring_payments' ) ) {
			eversubscription_process_recurring_payments();
			return;
		}

		global $wpdb;
		$table_name = $wpdb->prefix . 'ever_subscriptions';

		$now = current_time( 'mysql' );
		$subscriptions = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id FROM $table_name WHERE status = 'active' AND next_payment_date <= %s",
				$now
			)
		);

		if ( $subscriptions && class_exists( 'Eversubscription_Subscription' ) ) {
			foreach ( $subscriptions as $subscription ) {
				Eversubscription_Subscription::process_recurring_payment( $subscription->id );
			}
		}
	}
}