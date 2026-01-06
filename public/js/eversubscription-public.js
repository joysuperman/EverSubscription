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

	 / Update subscription details when a variation is found
    $('.variations_form').on('found_variation', function(event, variation) {

        if (!variation.ever_subscription) return;

        var s = variation.ever_subscription;

        // Format price using WooCommerce formatted price (requires WooCommerce JS settings)
        var formattedPrice = typeof wc_price === 'function' ? wc_price(s.price) : s.price;
        var formattedFee = typeof wc_price === 'function' ? wc_price(s.signup_fee) : s.signup_fee;

        var html = `
            <p><strong>Price:</strong> ${formattedPrice}</p>
            <p><strong>Billing:</strong> Every ${s.interval} ${s.period}${s.interval > 1 ? 's' : ''}</p>
            ${s.trial_l > 0 ? `<p><strong>Trial:</strong> ${s.trial_l} ${s.trial_p}${s.trial_l > 1 ? 's' : ''}</p>` : ''}
            ${s.signup_fee > 0 ? `<p><strong>Sign-up Fee:</strong> ${formattedFee}</p>` : ''}
            ${s.note ? `<p>${s.note}</p>` : ''}
        `;

        // Inject HTML into subscription-details container
        $(this).find('.subscription-details').html(html);
    });
})( jQuery );
