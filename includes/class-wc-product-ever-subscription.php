<?php
/**
 * Custom product class for Ever Subscription products.
 *
 * Placed in `includes/` so it's loaded with plugin PHP on every request.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WC_Product_Ever_Subscription' ) ) {
    class WC_Product_Ever_Subscription extends WC_Product_Simple {
        /**
         * Return product type identifier.
         *
         * @return string
         */
        public function get_type() {
            return 'ever_subscription';
        }

        /**
         * Ensure WooCommerce treats this product like a simple product for
         * template decisions (so add-to-cart displays correctly).
         *
         * @param string $type
         * @return bool
         */
        public function is_type( $type ) {
            if ( 'ever_subscription' === $type ) {
                return true;
            }

            if ( 'simple' === $type ) {
                return true;
            }

            return parent::is_type( $type );
        }
    }
}
