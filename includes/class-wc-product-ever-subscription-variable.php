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
            if ( 'ever_subscription_variable' === $type ) {
                return true;
            }
            if ( 'variable' === $type ) {
                return true;
            }
            return parent::is_type( $type );
        }
    }
}

if ( ! class_exists( 'WC_Product_Ever_Subscription_Variation' ) ) {
    class WC_Product_Ever_Subscription_Variation extends WC_Product_Variation {
        public function get_type() {
            return 'ever_subscription_variation';
        }

        public function is_type( $type ) {
            if ( 'ever_subscription_variation' === $type ) {
                return true;
            }
            if ( 'variation' === $type ) {
                return true;
            }
            return parent::is_type( $type );
        }
    }
}