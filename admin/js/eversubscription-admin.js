(function ($) {
    'use strict';

    $(document).ready(function () {

        const initClasses = () => {
            // Add Classes to Product Data Tabs for your custom type
            $('#general_product_data .show_if_variable').addClass('show_if_ever_subscription_variable');
            $('#general_product_data .hide_if_variable').addClass('hide_if_ever_subscription_variable');

            $('#inventory_product_data .show_if_variable').addClass('show_if_ever_subscription_variable');
            $('#inventory_product_data .hide_if_variable').addClass('hide_if_ever_subscription_variable');
        }

        const initProductAttributes = () => {
            // Add classes to the product attributes for your custom type
            // This enables the "Used for variations" checkbox
            $('#product_attributes .enable_variation').addClass('show_if_ever_subscription_variable');
            $('#product_attributes .enable_if_variable').addClass('enable_if_ever_subscription_variable');
        }

        const maybeShow = () => {
            const product_type = $('select#product-type').val();
            // Show the product attributes based on the current type
            $(`.show_if_${product_type}`).each(function () {
                $(this).show();
            });

            $('input#_manage_stock').trigger('change');
        }

        const maybeEnable = () => {
            const product_type = $('select#product-type').val();
            // Enable the product attributes based on the current type
            $(`.enable_if_${product_type}`).each(function () {
                $(this).removeClass('disabled');
                if ($(this).is('input')) {
                    $(this).prop('disabled', false);
                }
            });
        }

        initClasses();
        initProductAttributes();
        maybeShow();
        maybeEnable();

        // Fires when attributes are added or UI is refreshed
        $('body').on('wc-enhanced-select-init', () => {
            initProductAttributes();
            maybeShow();
        });

        $('body').on('woocommerce-product-type-change', () => {
            maybeShow();
            maybeEnable();
        });

        $('body').on('woocommerce_variations_loaded', () => {
            maybeEnable();
        });

		// Tell WooCommerce this is a variable product
		$(document.body).on('wc_product_type_changed', function (e, type) {
			if (type === 'ever_subscription_variable') {
				$('select#product-type').val('variable');
			}
		});

		// Force WC to recognize it on load
		$(document).ready(function () {
			const type = $('select#product-type').val();
			if (type === 'ever_subscription_variable') {
				window.wc_product_type = 'variable';
			}
		});

    });

})(jQuery);