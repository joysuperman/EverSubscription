(function( $ ) {
	'use strict';

	/**
	 * Handle WooCommerce product type visibility for Ever Subscription
	 * Shows/hides metabox panels based on selected product type
	 */

	$(document).ready(function() {
		
		// Function to show/hide options based on product type
		function toggleProductTypeOptions() {
			var $productType = $('select#product-type');
			var selectedType = $productType.val();

			// Hide all product-type specific options
			$('.show_if_simple').closest('.options_group, .panel').hide();
			$('.show_if_grouped').closest('.options_group, .panel').hide();
			$('.show_if_external').closest('.options_group, .panel').hide();
			$('.show_if_variable').closest('.options_group, .panel').hide();
			$('.show_if_ever_subscription').closest('.options_group, .panel').hide();
			$('.hide_if_simple').closest('.options_group, .panel').show();
			$('.hide_if_grouped').closest('.options_group, .panel').show();
			$('.hide_if_external').closest('.options_group, .panel').show();
			$('.hide_if_variable').closest('.options_group, .panel').show();
			$('.hide_if_ever_subscription').closest('.options_group, .panel').show();

			// Show options for selected product type
			$('.show_if_' + selectedType).closest('.options_group, .panel').show();
			$('.show_if_' + selectedType).show();

			// Hide options that should be hidden for this type
			$('.hide_if_' + selectedType).closest('.options_group, .panel').hide();
			$('.hide_if_' + selectedType).hide();

			// Show subscription panel only for simple Ever Subscription product type
			if ( 'ever_subscription' === selectedType ) {
				$('#ever_subscription_data').show();
				$('.show_if_ever_subscription').show().closest('.options_group, .panel').show();
			} else {
				$('#ever_subscription_data').hide();
				$('.show_if_ever_subscription').hide().closest('.options_group, .panel').hide();
			}

			// If the variable subscription product type is selected, show variable panels only
			if ( 'ever_subscription_variable' === selectedType ) {
				$('.show_if_variable').show().closest('.options_group, .panel').show();
				$('.hide_if_variable').hide().closest('.options_group, .panel').hide();
			}

			// Trigger any dependent scripts
			$(document.body).trigger('woocommerce-product-type-change', selectedType);
		}

		// Initial load - set the correct options
		toggleProductTypeOptions();

		// Change event handler
		$('select#product-type').on('change', function() {
			toggleProductTypeOptions();
		});

		// Re-run toggle after WooCommerce attribute/variation AJAX operations
		// Expose for external calls
		window.toggleEversubscriptionProductTypeOptions = toggleProductTypeOptions;

		// WooCommerce triggers this when variations are reloaded
		$(document).on('woocommerce_variations_loaded woocommerce_variations_added', function() {
			toggleProductTypeOptions();
		});

		// When attributes are saved via AJAX in the product edit screen, the request
		// includes action=woocommerce_save_attributes. Re-run our toggle after such requests.
		$(document).ajaxComplete(function(event, xhr, settings) {
			if ( settings && settings.data && settings.data.indexOf && settings.data.indexOf('woocommerce_save_attributes') !== -1 ) {
				toggleProductTypeOptions();
			}
		});

		// Handle sale price schedule links
		$(document).on('click', '.sale_schedule', function(e) {
			e.preventDefault();
			$('.sale_price_dates_fields').slideDown();
		});

		// Handle cancel schedule
		$(document).on('click', '.cancel_sale_schedule', function(e) {
			e.preventDefault();
			$('.sale_price_dates_fields').slideUp();
			// Clear both core and plugin-specific date inputs
			$('#_sale_price_dates_from').val('');
			$('#_sale_price_dates_to').val('');
			$('#_ever_sale_price_dates_from').val('');
			$('#_ever_sale_price_dates_to').val('');
		});

		// Initialize date pickers if jQuery UI is available
		if ( $.datepicker ) {
			$('.date-picker').datepicker({
				dateFormat: 'yy-mm-dd'
			});
		}

	});

})( jQuery );
