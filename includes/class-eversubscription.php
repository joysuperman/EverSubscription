<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.joymojumder.com
 * @since      1.0.0
 *
 * @package    Eversubscription
 * @subpackage Eversubscription/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Eversubscription
 * @subpackage Eversubscription/includes
 * @author     JOYSUPERMAN <joymojumder529@gmail.com>
 */
class Eversubscription {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Eversubscription_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'EVERSUBSCRIPTION_VERSION' ) ) {
			$this->version = EVERSUBSCRIPTION_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'eversubscription';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_api_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Eversubscription_Loader. Orchestrates the hooks of the plugin.
	 * - Eversubscription_i18n. Defines internationalization functionality.
	 * - Eversubscription_Admin. Defines all hooks for the admin area.
	 * - Eversubscription_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-eversubscription-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-eversubscription-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-eversubscription-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-eversubscription-public.php';

		/**
		 * Subscription management class
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-eversubscription-subscription.php';

		/**
		 * REST API class
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-eversubscription-api.php';

		$this->loader = new Eversubscription_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Eversubscription_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Eversubscription_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}


	private function define_api_hooks() {
		$plugin_api = new Eversubscription_API( $this->get_plugin_name(), $this->get_version() );

		// Register REST API routes
		$this->loader->add_action('rest_api_init', $plugin_api, 'eversubscription_register_api_routes');

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Eversubscription_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
    
		// CRITICAL: Register Data Stores
		$this->loader->add_filter( 'woocommerce_data_stores', $plugin_admin, 'register_data_stores' );

		$this->loader->add_action( 'plugins_loaded', $plugin_admin , 'include_product_class' );
		$this->loader->add_filter( 'product_type_selector', $plugin_admin, 'add_woo_product_type' );
		$this->loader->add_filter( 'woocommerce_product_class', $plugin_admin, 'register_product_class', 10, 4 );
		
		// Tabs & UI
		$this->loader->add_filter( 'woocommerce_product_data_tabs', $plugin_admin, 'ever_subscription_product_tab' );
		$this->loader->add_filter( 'woocommerce_product_data_tabs', $plugin_admin, 'add_variation_support_to_tabs' );
		$this->loader->add_action( 'woocommerce_product_data_panels', $plugin_admin, 'ever_subscription_product_tab_content' );

		// Saving
		$this->loader->add_action( 'woocommerce_admin_process_product_object', $plugin_admin, 'ever_subscription_save_product_data' );
		$this->loader->add_action( 'woocommerce_variation_options_pricing', $plugin_admin, 'ever_subscription_variation_options', 10, 3 );
		$this->loader->add_action( 'woocommerce_save_product_variation', $plugin_admin, 'ever_subscription_save_variation', 10, 2 );
		// Variation UI and save handlers
		$this->loader->add_action('woocommerce_variation_options_pricing', $plugin_admin, 'ever_subscription_variation_options', 10, 3);

		$this->loader->add_filter('woocommerce_bulk_edit_variations_allowed_product_types', $plugin_admin, 'ever_subscription_variations_allowed_product_types', 10, 3);
		$this->loader->add_action( 'woocommerce_checkout_order_processed', $plugin_admin, 'eversubscription_create_subscription_from_order' );
		// Handle subscription creation on order completion
		$this->loader->add_action('woocommerce_order_status_completed', $plugin_admin, 'eversubscription_create_subscription_from_order');
		$this->loader->add_action('woocommerce_order_status_processing', $plugin_admin, 'eversubscription_create_subscription_from_order');
		$this->loader->add_action('save_post_product', $plugin_admin, 'eversubscription_save_post_product', 20, 1);
		// Cron job for recurring payments
		$this->loader->add_action('ever_subscription_process_recurring_payments', $plugin_admin, 'eversubscription_process_recurring_payments');

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		$plugin_public = new Eversubscription_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		// Keep internal product prices numeric; modify displayed price HTML instead
		$this->loader->add_filter( 'woocommerce_get_price_html', $plugin_public, 'eversubscription_subscription_price_html', 10, 2 );
		$this->loader->add_action( 'woocommerce_cart_calculate_fees', $plugin_public, 'eversubscription_apply_trial_discount_fee' );
		// Display subscription info on product page
		$this->loader->add_action( 'woocommerce_single_product_summary', $plugin_public, 'eversubscription_display_subscription_info', 25 );
		// Show billing period on cart, checkout and order price display and item meta
		$this->loader->add_filter( 'woocommerce_get_item_data', $plugin_public, 'eversubscription_subscription_get_item_data', 10, 2 );
		
		// Use saved settings to change button text and checkout button when appropriate
		$this->loader->add_filter( 'woocommerce_product_single_add_to_cart_text', $plugin_public, 'eversubscription_product_add_to_cart_text', 10, 2 );
		$this->loader->add_filter( 'woocommerce_product_add_to_cart_text', $plugin_public, 'eversubscription_product_add_to_cart_text', 10, 2 );
		$this->loader->add_filter( 'woocommerce_order_button_text', $plugin_public, 'eversubscription_order_button_text', 15 );

		// Cart and checkout totals with subscription details - using correct hooks
		$this->loader->add_action( 'woocommerce_review_order_before_payment', $plugin_public, 'eversubscription_display_checkout_subscription_details' );

		// Thank you / Order received page - display after order details
		$this->loader->add_action( 'woocommerce_order_details_after_order_table', $plugin_public, 'eversubscription_display_thankyou_subscription_details' );
		$this->loader->add_action( 'woocommerce_available_variation', $plugin_public, 'eversubscription_add_variation_subscription_data', 10, 3 );
		
		// Handle subscription preference form submission
		$this->loader->add_action( 'init', $plugin_public, 'eversubscription_handle_subscription_preferences' );
		

		// My Account subscriptions
		$this->loader->add_action( 'init', $plugin_public, 'eversubscription_register_my_account_endpoint' );
		$this->loader->add_filter( 'woocommerce_account_menu_items', $plugin_public, 'eversubscription_add_subscriptions_menu_item' );
		// Display content for the subscriptions endpoint
		$this->loader->add_action( 'woocommerce_account_subscriptions_endpoint', $plugin_public, 'eversubscription_display_subscriptions_content' );
		$this->loader->add_action( 'template_redirect', $plugin_public, 'eversubscription_handle_subscription_actions' );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Eversubscription_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
