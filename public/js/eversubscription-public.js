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
         * Real-time Variation Update
         */
        $(document).on('found_variation', 'form.variations_form', function(event, variation) {
            const $detailsContainer = $('.ever-variation-realtime-details');
            const $contentArea = $detailsContainer.find('.subscription-details-container');

            if (variation.ever_sub_html) {
                // Inject the HTML we prepared in PHP
                $contentArea.html(variation.ever_sub_html);
                $detailsContainer.slideDown(200);
            } else {
                $detailsContainer.hide();
            }
        });

        /**
         * Clear details if selection is reset
         */
        $(document).on('reset_data', 'form.variations_form', function() {
            $('.ever-variation-realtime-details').hide();
        });
    });
})( jQuery );
