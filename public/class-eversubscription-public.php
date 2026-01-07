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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/eversubscription-public.js', array( 'jquery', 'wc-add-to-cart-variation' ), $this->version, true );

	}

	/**
	 * Apply trial discount fee to the cart.
	 */
	public function eversubscription_apply_trial_discount_fee( $cart ) {
		// 1. Early exits for Admin or non-checkout pages
		if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
			return;
		}

		// Ensure we are on a page where checkout/session exists
		if ( ! did_action( 'woocommerce_before_calculate_totals' ) && ! is_checkout() ) {
			// return; // Uncomment if you want to restrict purely to checkout
		}

		// 2. Determine if the user is a returning customer
		$billing_email = WC()->checkout ? WC()->checkout->get_value('billing_email') : '';
		$user_exists   = is_user_logged_in() || ( ! empty( $billing_email ) && email_exists( $billing_email ) );

		foreach ( $cart->get_cart() as $cart_item ) {
			$product = $cart_item['data'];

			if ( is_a( $product, 'WC_Product' ) && method_exists( $product, 'is_type' ) && $product->is_type( array( 'ever_subscription', 'ever_subscription_variable' ) ) ) {
				
				// Fetch Metadata
				$price        = floatval( $product->get_meta( '_ever_subscription_price' ) );
				$trial_length = intval( $product->get_meta( '_ever_subscription_trial_length' ) );
				$trial_period = $product->get_meta( '_ever_subscription_trial_period' );
				$period       = $product->get_meta( '_ever_billing_period' );

				if ( $trial_length <= 0 ) {
					continue;
				}

				// 3. Normalized Calculation Logic
				// We convert everything to a "daily" rate to find the exact value of the trial
				$days_in_period = [
					'year'  => 365,
					'month' => 30,
					'week'  => 7,
					'day'   => 1
				];

				$billing_days = $days_in_period[$period] ?? 1;
				$trial_days   = $days_in_period[$trial_period] ?? 1;

				// Calculate daily rate based on the subscription price
				$daily_rate     = $price / $billing_days;
				$discount_total = $daily_rate * $trial_days * $trial_length;

				// 4. Set the Fee (Use a negative value for a discount)
				if ( $discount_total > 0 ) {
					$label = __( 'Trial Discount', 'eversubscription' );
					$cart->add_fee( $label, -$discount_total );
				}
			}
		}
	}

	/**
	 * Display subscription information on product page
	 *
	 * @since    1.0.0
	 */
	public function eversubscription_display_subscription_info() {
		global $product;

		if ( ! $product || ! $product->is_type( array( 'ever_subscription', 'ever_subscription_variable' ) ) ) {
			return;
		}

		/* ================================
		* VARIABLE SUBSCRIPTION PRODUCT
		* ================================ */
		if ( $product->is_type( 'ever_subscription_variable' ) ) {
            $variations = $product->get_available_variations();
            $attributes = $product->get_variation_attributes();

            ?>
            <div class="ever-subscription-info variable-subscription-selection">
                <h4><?php esc_html_e( 'Choose Your Subscription Plan', $this->plugin_name ); ?></h4>

                <form class="variations_form cart"
                    method="post"
                    data-product_id="<?php echo esc_attr( $product->get_id() ); ?>"
                    data-product_variations="<?php echo esc_attr( wp_json_encode( $variations ) ); ?>">

                    <div class="variations">
                        <?php foreach ( $attributes as $attribute_name => $options ) : ?>
                            <div class="attribute-selection-row">
                                <label><?php echo wc_attribute_label( $attribute_name ); ?></label>
                                <?php 
                                    wc_dropdown_variation_attribute_options( array(
                                        'options'   => $options,
                                        'attribute' => $attribute_name,
                                        'product'   => $product,
                                    ) );
                                ?>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="ever-variation-realtime-details">
                        <div class="subscription-details-container"></div>
                    </div>

                    <div class="single_variation_wrap">
                        <div class="woocommerce-variation single_variation"></div>
                        <div class="woocommerce-variation-add-to-cart variations_button">
                            <button type="submit" class="single_add_to_cart_button button alt">
                                <?php echo esc_html( get_option( 'eversubscription_add_to_cart_button_text', __( 'Subscribe', $this->plugin_name ) ) ); ?>
                            </button>
                            <input type="hidden" name="add-to-cart" value="<?php echo absint( $product->get_id() ); ?>" />
                            <input type="hidden" name="product_id" value="<?php echo absint( $product->get_id() ); ?>" />
                            <input type="hidden" name="variation_id" class="variation_id" value="0" />
                        </div>
                    </div>
                </form>
            </div>
            <?php
            return;
        }

		/* ================================
		* SIMPLE SUBSCRIPTION PRODUCT
		* ================================ */
		$meta = [
			'price'    => $product->get_meta( '_ever_subscription_price' ),
			'interval' => $product->get_meta( '_ever_billing_interval' ) ?: 1,
			'period'   => $product->get_meta( '_ever_billing_period' ) ?: 'month',
			'trial_l'  => (int) $product->get_meta( '_ever_subscription_trial_length' ),
			'trial_p'  => $product->get_meta( '_ever_subscription_trial_period' ) ?: 'day',
			'fee'      => (float) $product->get_meta( '_ever_subscription_sign_up_fee' ),
			'note'     => $product->get_meta( '_ever_aditional_note' ),
		];

		?>
		<div class="ever-subscription-info">
			<h4><?php esc_html_e( 'Subscription Details', $this->plugin_name ); ?></h4>
			<table>
				<tr>
					<td><strong><?php esc_html_e( 'Price:', $this->plugin_name ); ?></strong></td>
					<td><?php echo wc_price( $meta['price'] ); ?> / <?php echo esc_html( $meta['interval'] > 1 ? $meta['interval'] . ' ' . $meta['period'] . 's' : $meta['period'] ); ?></td>
				</tr>
				<?php if ( $meta['fee'] > 0 ) : ?>
					<tr><td><strong><?php esc_html_e( 'Sign-up Fee:', $this->plugin_name ); ?></strong></td><td> <?php echo wc_price( $meta['fee'] ); ?></td></tr>
				<?php endif; ?>
				<?php if ( $meta['trial_l'] > 0 ) : ?>
					<tr><td><strong><?php esc_html_e( 'Free Trial:', $this->plugin_name ); ?></strong></td><td> <?php echo esc_html( $meta['trial_l'] . ' ' . $meta['trial_p'] . ( $meta['trial_l'] > 1 ? '(s)' : '' ) ); ?></td></tr>
				<?php endif; ?>
			</table>
			
			<?php if ( ! empty( $meta['note'] ) ) : ?>
				<blockquote class="eversubscription-additional-note" style="margin: 0px; padding: 10px; background-color: #f9f9f9; border-left: 4px solid #0073aa;">
					<strong>Note: </strong>
					<?php echo esc_html( $meta['note'] ); ?>
				</blockquote>
			<?php endif; ?>
			<?php if ( $product->is_purchasable() && $product->is_in_stock() ) : ?>
				<form class="cart" action="<?php echo esc_url( wc_get_cart_url() ); ?>" method="post">
					<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" />
					<button type="submit" class="single_add_to_cart_button button alt">
						<?php echo esc_html( get_option( 'eversubscription_add_to_cart_button_text', __( 'Subscribe', $this->plugin_name ) ) ); ?>
					</button>
				</form>
			<?php endif; ?>
		</div>
		<?php
	}


	/**
	 * Add subscription meta to variation JSON
	 */
	public function eversubscription_add_variation_subscription_data( $data, $product, $variation ) {
        if ( ! $product->is_type( 'ever_subscription_variable' ) ) {
            return $data;
        }

        // Get metadata
        $price      = floatval($variation->get_meta('_ever_subscription_price'));
        $sale_price = floatval($variation->get_meta('_ever_sale_price'));
        $interval   = $variation->get_meta('_ever_billing_interval') ?: 1;
        $period     = $variation->get_meta('_ever_billing_period') ?: 'month';
        
        // Format the display string (Real-time logic)
        $billing_text = ($interval > 1) ? $interval . ' ' . $period . 's' : $period;

        $data['ever_sub_html'] = '
            <table class="ever-sub-details-table">
                <tr>
                    <td><strong>' . __( 'Subscription:', 'eversubscription' ) . '</strong></td>
                    <td>' . wc_price($price) . ' / ' . $billing_text . '</td>
                </tr>';

        if ($sale_price > 0) {
            $data['ever_sub_html'] .= '
                <tr>
                    <td><strong>' . __( 'Sale Price:', 'eversubscription' ) . '</strong></td>
                    <td>' . wc_price($sale_price) . '</td>
                </tr>';
        }

        $signup_fee = floatval($variation->get_meta('_ever_subscription_sign_up_fee'));
        if ($signup_fee > 0) {
            $data['ever_sub_html'] .= '<tr><td><strong>' . __( 'Sign-up Fee:', 'eversubscription' ) . '</strong></td><td>' . wc_price($signup_fee) . '</td></tr>';
        }

        $trial = intval($variation->get_meta('_ever_subscription_trial_length'));
        if ($trial > 0) {
            $trial_p = $variation->get_meta('_ever_subscription_trial_period') ?: 'day';
            $data['ever_sub_html'] .= '<tr><td><strong>' . __( 'Free Trial:', 'eversubscription' ) . '</strong></td><td>' . $trial . ' ' . $trial_p . ($trial > 1 ? 's' : '') . '</td></tr>';
        }

        $data['ever_sub_html'] .= '</table>';

        return $data;
    }


	/**
	 * Filter product add to cart text.
	 */
	public function eversubscription_product_add_to_cart_text( $text, $product = null ) {
		if ( $product && is_object( $product ) && method_exists( $product, 'is_type' ) && $product->is_type( array( 'ever_subscription', 'ever_subscription_variable' ) ) ) {
			$custom = get_option( 'eversubscription_add_to_cart_button_text', '' );
			if ( $custom ) {
				return $custom;
			}
		}
		return $text;
	}

	/**
	 * Filter checkout order button text.
	 */
	public function eversubscription_order_button_text( $text ) {
		$custom = get_option( 'eversubscription_order_button_text', '' );
		if ( $custom && $this->cart_has_subscription() ) {
			return $custom;
		}
		return $text;
	}

	/**
	 * Helper: check if cart contains any ever_subscription products
	 */
	protected function cart_has_subscription() {
		if ( ! WC()->cart ) {
			return false;
		}
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
			if ( $product && is_object( $product ) && method_exists( $product, 'is_type' ) && $product->is_type( array( 'ever_subscription', 'ever_subscription_variable' ) ) ) {
				return true;
			}
		}
		return false;
	}

	/**
	 * Append billing period to the displayed price HTML for subscription products.
	 *
	 * @param string $price_html Price HTML
	 * @param WC_Product $product Product object
	 * @return string Modified price HTML
	 */
	public function eversubscription_subscription_price_html( $price_html, $product ) {

        if ( ! $product || ! is_object( $product ) || ! method_exists( $product, 'is_type' ) ) {
            return $price_html;
        }

        // Simple subscription
        if ( $product->is_type( 'ever_subscription' ) ) {
            $billing_period = $product->get_meta( '_ever_billing_period' ) ?: 'month';
            return $price_html . ' /' . esc_html( $billing_period );
        }

        // Variable subscription (SAFE)
        if ( $product->is_type( 'ever_subscription_variable' ) ) {
			$variation_ids = $product->get_children();
			
			if ( empty( $variation_ids ) ) {
				return $price_html;
			}

			$prices = array();
			$display_prices = array();

			foreach ( $variation_ids as $variation_id ) {
				$variation = wc_get_product( $variation_id );
				if ( ! $variation || ! $variation->is_visible() ) continue;

				// Get our custom meta values
				$reg_price  = floatval( $variation->get_meta( '_ever_subscription_price' ) );
				$sale_price = $variation->get_meta( '_ever_sale_price' ); // String/Float
				$period     = $variation->get_meta( '_ever_billing_period' ) ?: 'month';

				// Check if Variation is on sale
				$active_price = $reg_price;
				if ( $sale_price !== '' && floatval( $sale_price ) > 0 ) {
					// Optional: You could check date limits here too
					$active_price = floatval( $sale_price );
				}

				if ( $active_price > 0 ) {
					$prices[] = $active_price;
					// Store formatted string: "$10.00 / Day"
					$display_prices[$active_price] = wc_price( $active_price ) . ' / ' . esc_html( ucfirst($period) );
				}
			}

			if ( empty( $prices ) ) {
				return $price_html;
			}

			sort( $prices );
			$min_price = current( $prices );
			$max_price = end( $prices );

			if ( $min_price !== $max_price ) {
				// Range format: "$10.00 / Day – $20.00 / Month"
				$range_html = $display_prices[$min_price] . ' &ndash; ' . $display_prices[$max_price];
			} else {
				// Single variation or all same: "$10.00 / Month"
				$range_html = $display_prices[$min_price];
			}

			return $range_html;
		}

        return $price_html;
    }


	/**
	 * Fix the price HTML for individual variations when selected.
	 * This ensures the sale price actually shows as a discount and includes the period.
	 */
	public function eversubscription_variation_price_html( $price_html, $variation, $product ) {
		// Only target our custom variable product type
		if ( ! $product->is_type( 'ever_subscription_variable' ) ) {
			return $price_html;
		}

		$reg_price  = floatval( $variation->get_meta( '_ever_subscription_price' ) );
		$sale_price = $variation->get_meta( '_ever_sale_price' );
		$period     = $variation->get_meta( '_ever_billing_period' ) ?: 'month';
		$interval   = $variation->get_meta( '_ever_billing_interval' ) ?: 1;

		$billing_text = ( $interval > 1 ) ? $interval . ' ' . $period . 's' : $period;

		// Check if there is a valid sale price
		if ( $sale_price !== '' && floatval( $sale_price ) > 0 && floatval( $sale_price ) < $reg_price ) {
			$price_html = '<del aria-hidden="true">' . wc_price( $reg_price ) . '</del> <ins aria-hidden="true">' . wc_price( $sale_price ) . '</ins>';
		} else {
			$price_html = wc_price( $reg_price );
		}

		return $price_html . ' / ' . esc_html( ucfirst( $billing_text ) );
	}

	/**
	 * Add subscription details to cart/checkout item meta display.
	 *
	 * @param array $item_data
	 * @param array $cart_item
	 * @return array
	 */
	public function eversubscription_subscription_get_item_data( $item_data, $cart_item ) {
        $product = $cart_item['data'] ?? null;

        // 1. Validate Product Object and Type
        if ( ! is_a( $product, 'WC_Product' ) || $product->get_type() !== 'ever_subscription' ) {
            return $item_data;
        }

        // 2. Fetch Metadata with Defaults
        $billing_interval = max( 1, intval( $product->get_meta( '_ever_billing_interval' ) ) );
        $billing_period   = $product->get_meta( '_ever_billing_period' ) ?: 'month';
        $trial_length     = intval( $product->get_meta( '_ever_subscription_trial_length' ) );
        $trial_period     = $product->get_meta( '_ever_subscription_trial_period' ) ?: 'day';
        
        // Fallback price logic
        $price = floatval( $product->get_meta( '_ever_subscription_price' ) ) ?: floatval( $product->get_price() );

        // 3. Helper for Pluralization
        $get_label = function( $value, $period ) {
            return $value . ' ' . $period . ( $value > 1 ? 's' : '' );
        };

        // 4. Build Data List
        $display_data = [
            'Subscription' => sprintf( __( 'Every %s', $this->plugin_name ), $get_label( $billing_interval, $billing_period ) ),
        ];

        if ( $price > 0 ) {
            $display_data['Price'] = wc_price( $price );
        }

        if ( $trial_length > 0 ) {
            $display_data['Free Trial'] = $get_label( $trial_length, $trial_period );
        }

        $next_date = $this->calculate_next_payment_date( $trial_length, $trial_period, $billing_interval, $billing_period );
        if ( $next_date ) {
            $display_data['Next Payment'] = $next_date;
        }

        // 5. Merge into $item_data in WooCommerce format
        foreach ( $display_data as $key => $value ) {
            $item_data[] = [
                'key'   => __( $key, $this->plugin_name ),
                'value' => $value,
            ];
        }

        return $item_data;
    }

	/**
	 * Handle subscription preference form submission from thank you page.
	 *
	 * @since    1.0.0
	 */
	public function eversubscription_handle_subscription_preferences() {
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
			
			if ( $product && is_object( $product ) && method_exists( $product, 'is_type' ) && $product->is_type( array( 'ever_subscription', 'ever_subscription_variable' ) ) ) {
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
	 * Display subscription details before checkout payment section.
	 *
	 * @since    1.0.0
	 */
	public function eversubscription_display_checkout_subscription_details() {
		// Check if there are any subscription items
		$has_subscriptions = false;
		foreach ( WC()->cart->get_cart() as $cart_item ) {
			$product = isset( $cart_item['data'] ) ? $cart_item['data'] : null;
			if ( $product && is_object( $product ) && method_exists( $product, 'is_type' ) && $product->is_type( array( 'ever_subscription', 'ever_subscription_variable' ) ) ) {
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
			if ( $product && is_object( $product ) && method_exists( $product, 'is_type' ) && $product->is_type( array( 'ever_subscription', 'ever_subscription_variable' ) ) ) {
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
	public function eversubscription_display_thankyou_subscription_details( $order_id ) {
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
		$product_name = $product->get_name();

		// Calculate next payment date
		$next_payment_date = $this->calculate_next_payment_date( $trial_length, $trial_period, $billing_interval, $billing_period );
		// Determine whether signup fee applies (only for guests)
		$apply_signup_fee = ( ! is_user_logged_in() && $sign_up_fee > 0 );
		$first_payment_amount = $apply_signup_fee ? ( $sign_up_fee ) : ( $product->get_meta( '_ever_subscription_price' ) ? floatval( $product->get_meta( '_ever_subscription_price' ) ) : floatval( $product->get_price() ) );

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
		$html .= '<strong>' . esc_html__( 'Next Payment:', $this->plugin_name ) . '</strong> ' . esc_html( $next_payment_date ) . '';
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




	/**
	 * Register the "subscriptions" My Account endpoint.
	 */
	public function eversubscription_register_my_account_endpoint() {
		// Change 'ever-subscriptions' to 'subscriptions'
		add_rewrite_endpoint( 'subscriptions', EP_ROOT | EP_PAGES );
	}
	/**
	 * Insert the Subscriptions item into the My Account menu.
	 *
	 * @param array $items Current menu items
	 * @return array Modified items
	 */
	public function eversubscription_add_subscriptions_menu_item( $items ) {
		$new_items = array();
		foreach ( $items as $key => $label ) {
			$new_items[ $key ] = $label;
			if ( 'orders' === $key ) {
				$new_items['subscriptions'] = __( 'Subscriptions', $this->plugin_name );
			}
		}
		return $new_items;
	}

	/**
	 * Display subscriptions content in My Account
	 *
	 * @since    1.0.0
	 */
	public function eversubscription_display_subscriptions_content() {
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
     * Enhanced Action Handler
     * Added extra security check for ownership and state.
     */
    public function eversubscription_handle_subscription_actions() {
        if ( ! isset( $_GET['ever_subscription_action'], $_GET['subscription_id'] ) ) return;
        if ( ! wp_verify_nonce( $_GET['_wpnonce'], 'ever_subscription_action' ) ) return;
        if ( ! is_user_logged_in() ) return;

        $sub_id  = absint( $_GET['subscription_id'] );
        $action  = sanitize_text_field( $_GET['ever_subscription_action'] );
        $user_id = get_current_user_id();
        $sub     = Eversubscription_Subscription::get_subscription( $sub_id );

        // Security: Ensure the subscription belongs to the logged-in user
        if ( ! $sub || (int) $sub->user_id !== $user_id ) {
            wc_add_notice( __( 'Access denied.', $this->plugin_name ), 'error' );
            return;
        }

        switch ( $action ) {
            case 'pause':
                if ( $sub->status === 'active' ) {
                    Eversubscription_Subscription::pause_subscription( $sub_id );
                    wc_add_notice( __( 'Subscription paused.', $this->plugin_name ), 'success' );
                }
                break;
            case 'resume':
                if ( $sub->status === 'on-hold' ) {
                    Eversubscription_Subscription::resume_subscription( $sub_id );
                    wc_add_notice( __( 'Subscription resumed.', $this->plugin_name ), 'success' );
                }
                break;
            case 'cancel':
                Eversubscription_Subscription::cancel_subscription( $sub_id );
                wc_add_notice( __( 'Subscription cancelled.', $this->plugin_name ), 'success' );
                break;
        }

        wp_safe_redirect( wc_get_endpoint_url( 'subscriptions', '', wc_get_page_permalink( 'myaccount' ) ) );
        exit;
    }
}