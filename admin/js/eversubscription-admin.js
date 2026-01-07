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
            // This enables the "Used for variations" checkbox
            $('#product_attributes .enable_variation').addClass('show_if_ever_subscription_variable');
            $('#product_attributes .enable_if_variable').addClass('enable_if_ever_subscription_variable');
        }

        const maybeShow = () => {
            const product_type = $('select#product-type').val();
            
            // Core WC toggling
            $(`.show_if_${product_type}`).each(function () {
                $(this).show();
            });

            if (product_type === 'ever_subscription_variable') {
                // We target paragraphs (p) that contain the standard variable price names
                $('.variable_pricing').find('p.form-row').each(function() {
                    const $row = $(this);
                    // If the row contains standard WC names, hide it
                    if ($row.find('input[name^="variable_regular_price"], input[name^="variable_sale_price"]').length > 0) {
                        $row.hide();
                    }
                });

                // 2. Hide the default WC date fields container
                $('.variable_pricing').find('.sale_price_dates_fields').not('.ever_subscription_variation_settings *').hide();

                // 3. Show your custom settings container
                $('.ever_subscription_variation_settings').show();

            } else {
                // Restore standard fields for other product types
                $('.variable_pricing').find('p.form-row, .sale_price_dates_fields').show();
                $('.ever_subscription_variation_settings').hide();
            }

            $('input#_manage_stock').trigger('change');
        }

        const maybeEnable = () => {
            const product_type = $('select#product-type').val();
            $(`.enable_if_${product_type}`).each(function () {
                $(this).removeClass('disabled');
                if ($(this).is('input')) {
                    $(this).prop('disabled', false);
                }
            });
        }

        // --- NEW: Handle Schedule/Cancel Toggle Logic ---
        const initScheduleToggles = () => {
            $(document.body).on('click', '.ever_sale_schedule', function(e) {
                e.preventDefault();
                const $settingsWrapper = $(this).closest('.ever_subscription_variation_settings');
                
                // Show date fields container
                $settingsWrapper.find('.ever_sale_price_dates_fields').removeClass('ever_subscription_hidden').show();
                
                // Toggle buttons
                $(this).addClass('ever_subscription_hidden').hide();
                $settingsWrapper.find('.ever_cancel_sale_schedule').removeClass('ever_subscription_hidden').show();
                
                // Initialize datepicker if not already initialized
                if ($.isFunction($.fn.datepicker)) {
                    $settingsWrapper.find('.date-picker').datepicker({
                        dateFormat: 'yy-mm-dd',
                        numberOfMonths: 1,
                        showButtonPanel: true
                    });
                }
            });

            // 2. Handle "Cancel" click
            $(document.body).on('click', '.ever_cancel_sale_schedule', function(e) {
                e.preventDefault();
                const $settingsWrapper = $(this).closest('.ever_subscription_variation_settings');
                
                // Hide and Clear date fields
                const $dates = $settingsWrapper.find('.ever_sale_price_dates_fields');
                $dates.addClass('ever_subscription_hidden').hide();
                $dates.find('input').val(''); 
                
                // Toggle buttons
                $(this).addClass('ever_subscription_hidden').hide();
                $settingsWrapper.find('.ever_sale_schedule').removeClass('ever_subscription_hidden').show();
            });
        }

        initClasses();
        initProductAttributes();
        maybeShow();
        maybeEnable();
        initScheduleToggles();

        $('body').on('wc-enhanced-select-init', () => {
            initProductAttributes();
            maybeShow();
        });

        $('body').on('woocommerce-product-type-change', () => {
            maybeShow();
            maybeEnable();
        });

        $(document.body).on('woocommerce_variations_loaded woocommerce_variations_added', () => {
            maybeShow();
            maybeEnable();
            initScheduleToggles();
        });

        // Tell WooCommerce this is a variable product logic
        $(document.body).on('wc_product_type_changed', function (e, type) {
            if (type === 'ever_subscription_variable') {
                // Keep the internal logic as variable for WC script compatibility
                window.wc_product_type = 'variable';
            }
        });
    });

})(jQuery);