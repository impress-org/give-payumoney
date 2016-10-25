jQuery(document).ready(function ($) {
	// Show/Hide fields.
	var sandbox_radio_btns = $('input[name="payumoney_sandbox_testing"]:radio');

	sandbox_radio_btns.on('change', function () {
		var field_value = $('input[name="payumoney_sandbox_testing"]:radio:checked').val();

		if ('enabled' == field_value) {
			$('#payumoney_live_salt_key').closest('tr').hide();
			$('#payumoney_live_merchant_key').closest('tr').hide();
			$('#payumoney_sandbox_salt_key').closest('tr').show();
			$('#payumoney_sandbox_merchant_key').closest('tr').show();
		} else {
			$('#payumoney_live_salt_key').closest('tr').show();
			$('#payumoney_live_merchant_key').closest('tr').show();
			$('#payumoney_sandbox_salt_key').closest('tr').hide();
			$('#payumoney_sandbox_merchant_key').closest('tr').hide();
		}
	}).change();
});
