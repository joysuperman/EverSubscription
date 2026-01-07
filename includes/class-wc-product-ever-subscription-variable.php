<?php
/**
 * Variable and Variation product classes for Ever Subscription.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WC_Product_Ever_Subscription_Variable' ) ) {
    class WC_Product_Ever_Subscription_Variable extends WC_Product_Variable {
        public function get_type() {
            return 'ever_subscription_variable';
        }

        public function is_type( $type ) {
            return in_array( $type, array( 'variable', 'ever_subscription_variable' ), true ) || parent::is_type( $type );
        }
    }
}

if ( ! class_exists( 'WC_Product_Ever_Subscription_Variation' ) ) {
    class WC_Product_Ever_Subscription_Variation extends WC_Product_Variation {
        public function get_type() {
            return 'variation';
        }

        public function is_type( $type ) {
            return in_array( $type, array( 'variation', 'ever_subscription_variation' ), true ) || parent::is_type( $type );
        }

        // Sync price with subscription price meta
        public function get_price( $context = 'view' ) {
            $price = $this->get_meta('_ever_subscription_price');
            return ( '' === $price || null === $price ) ? parent::get_price($context) : (float) $price;
        }

        public function get_regular_price( $context = 'view' ) {
            $price = $this->get_meta('_ever_subscription_price');
            return ( '' === $price || null === $price ) ? parent::get_regular_price($context) : (float) $price;
        }
    }
}