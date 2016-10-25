<?php
/**
 * Check if iATS payment gateway active or not.
 *
 * @since 1.0
 * @return bool
 */
function give_is_payumoney_active() {
	$give_settings = give_get_settings();
	$is_active     = false;

	if (
		array_key_exists( 'payuoney', $give_settings['gateways'] )
		&& ( 1 == $give_settings['gateways']['payuoney'] )
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
	$give_settings = give_get_settings();

	return ( empty( $give_settings['payumoney_payment_method_label'] ) ? __( 'Credit Card', 'give-payuoney' ) : $give_settings['payumoney_payment_method_label'] );
}


/**
 * Check if sandbox mode is enabled or disabled.
 *
 * @since 1.0
 * @return bool
 */
function give_payu_is_sandbox_mode_enabled() {
	$give_settings = give_get_settings();

	return give_is_setting_enabled( $give_settings['payumoney_sandbox_testing'] );
}


/**
 * Get payumoney merchant credentials.
 *
 * @since 1.0
 * @return array
 */
function give_payu_get_merchant_credentials() {
	$give_settings = give_get_settings();
	$credentials   = array(
		'merchant_key' => $give_settings['payumoney_sandbox_merchant_key'],
		'salt_key'     => $give_settings['payumoney_sandbox_salt_key'],
	);

	if ( ! give_payu_is_sandbox_mode_enabled() ) {
		$credentials = array(
			'merchant_key' => $give_settings['payumoney_sandbox_merchant_key'],
			'salt_key'     => $give_settings['payumoney_sandbox_salt_key'],
		);
	}

	return $credentials;

}


/**
 * Get api urls.
 *
 * @since 1.0
 * @return array
 */
function give_payu_get_api_url() {
	$api_url = 'https://test.payu.in/_payment';

	if ( ! give_payu_is_sandbox_mode_enabled() ) {
		$api_urls = 'https://secure.payu.in/_payment';
	}

	return $api_url;
}