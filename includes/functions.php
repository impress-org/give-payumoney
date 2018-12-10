<?php
/**
 * Check if the PayUmoney payment gateway is active or not.
 *
 * @since 1.0
 * @return bool
 */
function give_is_payu_active() {
	$give_settings = give_get_settings();
	$is_active     = false;

	if (
		array_key_exists( 'payumoney', $give_settings['gateways'] )
		&& ( 1 == $give_settings['gateways']['payumoney'] )
	) {
		$is_active = true;
	}

	return $is_active;
}


/**
 * Get payment method label.
 *
 * @since 1.0
 * @return string
 */
function give_payu_get_payment_method_label() {
	return ( give_get_option( 'payumoney_payment_method_label', false ) ?  give_get_option( 'payumoney_payment_method_label', '' ) : __( 'PayUmoney', 'give-payumoney' ) );
}


/**
 * Check if sandbox mode is enabled or disabled.
 *
 * @since 1.0
 * @return bool
 */
function give_payu_is_sandbox_mode_enabled() {
	return give_is_test_mode();
}


/**
 * Get payumoney merchant credentials.
 *
 * @since 1.0
 * @return array
 */
function give_payu_get_merchant_credentials() {
	$credentials = array(
		'merchant_key' => give_get_option( 'payumoney_sandbox_merchant_key', '' ),
		'salt_key'     => give_get_option( 'payumoney_sandbox_salt_key', '' ),
	);

	if ( ! give_payu_is_sandbox_mode_enabled() ) {
		$credentials = array(
			'merchant_key' => give_get_option( 'payumoney_live_merchant_key', '' ),
			'salt_key'     => give_get_option( 'payumoney_live_salt_key', '' ),
		);
	}

	return $credentials;

}


/**
 * Get api urls.
 *
 * @since 1.0
 * @return string
 */
function give_payu_get_api_url() {
	$api_url = 'https://sandboxsecure.payu.in/_payment';

	if ( ! give_payu_is_sandbox_mode_enabled() ) {
		$api_url = 'https://secure.payu.in/_payment';
	}

	return $api_url;
}
