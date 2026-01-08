(function( $ ) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	// Update subscription details when a variation is found
    $(function() {
        /**
         * 1. Force WooCommerce to check variations when our custom selects change
         */
        $(document).on('change', '.attribute-selection-row select', function() {
            $(this).closest('form.variations_form').trigger('check_variations');
        });

        /**
         * 2. Real-time Variation Update
         */
        $(document).on('found_variation', 'form.variations_form', function(event, variation) {
            const $form = $(this);
            const $detailsContainer = $('.ever-variation-realtime-details');
            const $contentArea = $detailsContainer.find('.subscription-details-container');

            if (variation.ever_sub_html) {
                $contentArea.html(variation.ever_sub_html);
                $detailsContainer.slideDown(200);
            } else {
                $detailsContainer.hide();
            }

            // Sync ID and enable button
            $form.find('input.variation_id').val(variation.variation_id).trigger('change');
            $form.find('.single_add_to_cart_button').removeClass('disabled').prop('disabled', false);
        });

        /**
         * 3. Clear details on reset
         */
        $(document).on('reset_data', 'form.variations_form', function() {
            $('.ever-variation-realtime-details').slideUp(200);
            $(this).find('.single_add_to_cart_button').addClass('disabled').prop('disabled', true);
        });
    });
})( jQuery );
