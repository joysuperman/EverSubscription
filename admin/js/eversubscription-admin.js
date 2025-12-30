jQuery(document).ready(function($) {
	// Auto-select 'ever_subscription' product type when subscription fields are filled
	var subscriptionFields = [
		'_ever_subscription_price',
		'_ever_billing_interval',
		'_ever_billing_period',
		'_ever_subscription_length',
		'_ever_subscription_sign_up_fee',
		'_ever_subscription_trial_length',
		'_ever_subscription_trial_period',
		'_ever_sale_price'
	];

	function checkAndSetProductType() {
		var hasSubscriptionData = false;
		
		subscriptionFields.forEach(function(fieldName) {
			var field = $('input[name="' + fieldName + '"], select[name="' + fieldName + '"]');
			if (field.length && field.val() && field.val() !== '' && field.val() !== '0') {
				hasSubscriptionData = true;
			}
		});

		if (hasSubscriptionData) {
			var productTypeSelect = $('#product-type');
			if (productTypeSelect.length && productTypeSelect.val() !== 'ever_subscription') {
				productTypeSelect.val('ever_subscription').trigger('change');
			}
		}
	}

	// Check on field changes
	subscriptionFields.forEach(function(fieldName) {
		$(document).on('input change', 'input[name="' + fieldName + '"], select[name="' + fieldName + '"]', function() {
			checkAndSetProductType();
		});
	});

	// Also check on page load in case fields are pre-filled
	setTimeout(checkAndSetProductType, 500);
});

