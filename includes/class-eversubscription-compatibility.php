<?php

/**
 * The file that defines the compatibility class
 *
 * Handles compatibility declarations with WooCommerce and other plugins.
 *
 * @link       https://www.joymojumder.com
 * @since      1.0.0
 *
 * @package    Eversubscription
 * @subpackage Eversubscription/includes
 */

/**
 * The compatibility class.
 *
 * This class handles compatibility declarations with WooCommerce features
 * and other third-party plugins.
 *
 * @since      1.0.0
 * @package    Eversubscription
 * @subpackage Eversubscription/includes
 * @author     JOYSUPERMAN <joymojumder529@gmail.com>
 */
class Eversubscription_Compatibility {

	/**
	 * The plugin file path.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_file    The plugin file path.
	 */
	private $plugin_file;

	/**
	 * Initialize the compatibility class.
	 *
	 * @since    1.0.0
	 * @param    string    $plugin_file    The plugin file path.
	 */
	public function __construct( $plugin_file ) {
		$this->plugin_file = $plugin_file;
		$this->init();
	}

	/**
	 * Initialize compatibility hooks.
	 *
	 * @since    1.0.0
	 */
	private function init() {
		// WooCommerce compatibility must be declared before woocommerce_init
		add_action( 'before_woocommerce_init', array( $this, 'declare_woocommerce_compatibility' ) );
		
		// Other plugin compatibilities can be declared on plugins_loaded
		add_action( 'plugins_loaded', array( $this, 'declare_other_plugin_compatibilities' ), 20 );
	}

	/**
	 * Declare compatibility with WooCommerce features.
	 *
	 * @since    1.0.0
	 */
	public function declare_woocommerce_compatibility() {
		// Check if WooCommerce is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			return;
		}

		// Declare compatibility with WooCommerce features
		if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
			// High-Performance Order Storage (HPOS)
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $this->plugin_file, true );
			
			// Cart and Checkout Blocks
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'cart_checkout_blocks', $this->plugin_file, true );
			
			// Product Block Editor
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'product_block_editor', $this->plugin_file, true );
		}
	}

	/**
	 * Declare compatibility with other plugins.
	 *
	 * This method can be extended to add compatibility with other plugins
	 * such as WooCommerce Subscriptions, Elementor, etc.
	 *
	 * @since    1.0.0
	 */
	public function declare_other_plugin_compatibilities() {
		// WooCommerce Subscriptions compatibility
		if ( class_exists( 'WC_Subscriptions' ) ) {
			$this->handle_woocommerce_subscriptions_compatibility();
		}

		// Elementor compatibility
		if ( did_action( 'elementor/loaded' ) ) {
			$this->handle_elementor_compatibility();
		}

		// WooCommerce Blocks compatibility
		if ( class_exists( 'Automattic\WooCommerce\Blocks\Package' ) ) {
			$this->handle_woocommerce_blocks_compatibility();
		}

		// Allow other plugins to hook into compatibility declarations
		do_action( 'eversubscription_declare_compatibility', $this->plugin_file );
	}

	/**
	 * Handle WooCommerce Subscriptions compatibility.
	 *
	 * @since    1.0.0
	 */
	private function handle_woocommerce_subscriptions_compatibility() {
		// Add any specific compatibility code for WooCommerce Subscriptions here
		// For example, ensuring subscription products work together
	}

	/**
	 * Handle Elementor compatibility.
	 *
	 * @since    1.0.0
	 */
	private function handle_elementor_compatibility() {
		// Add any specific compatibility code for Elementor here
		// For example, registering widgets or custom controls
	}

	/**
	 * Handle WooCommerce Blocks compatibility.
	 *
	 * @since    1.0.0
	 */
	private function handle_woocommerce_blocks_compatibility() {
		// Add any specific compatibility code for WooCommerce Blocks here
		// For example, registering custom blocks or extending existing ones
	}
}

