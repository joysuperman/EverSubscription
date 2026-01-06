<?php
/**
 * Variable and Variation product classes for Ever Subscription product type.
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

        public function get_available_variations( $return = 'array' ) {
            $variations = parent::get_available_variations( $return );
            return is_array( $variations ) ? $variations : []; // ALWAYS return array
        }

        public function get_children( $context = 'view' ) {
            $children = parent::get_children( $context );
            return is_array( $children ) ? $children : []; // prevent null
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

        // Ensure the variation price is always synced with subscription price
        public function get_price( $context = 'view' ) {
            $price = $this->get_meta('_ever_subscription_price');
            if ( '' === $price || $price === null ) {
                $price = 0; // fallback
            }
            return (float) $price;
        }

        public function get_regular_price( $context = 'view' ) {
            $price = $this->get_meta('_ever_subscription_price');
            if ( '' === $price || $price === null ) {
                $price = 0;
            }
            return (float) $price;
        }

        public function save() {
            // Make sure WooCommerce core _price meta is set
            $subscription_price = $this->get_meta('_ever_subscription_price');
            if ( '' === $subscription_price || $subscription_price === null ) {
                $subscription_price = 0;
            }
            $this->set_regular_price($subscription_price);
            $this->set_price($subscription_price); // sets _price
            parent::save();
        }
    }
}