<?php
/**
 * Auto set pending payment to abandoned.
 *
 * @since 1.0
 *
 * @param int $payment_id
 */
function give_payumoney_set_donation_abandoned_callback( $payment_id ) {
	/**
	 * @var Give_Payment $payment Payment object.
	 */
	$payment = new Give_Payment( $payment_id );

	if ( 'pending' === $payment->status ) {
		$payment->update_status( 'abandoned' );
	}
}

add_action( 'give_payumoney_set_donation_abandoned', 'give_payumoney_set_donation_abandoned_callback' );


/**
 * Validate payumoney settings.
 * 
 * @since 1.0
 * @param $options
 */
function give_payu_validate_settings( $options ) {
	if ( isset( $options['gateways']['payumoney'] ) && ( 'INR' !== $options['currency'] ) ) {
		// Unset payumoney.
		unset( $options['gateways']['payumoney'] );

		// Show payment gateway disable notice to admin.
		Give_Admin_Settings::add_error(
			'give_payu_no_inr_currency',
			esc_html__( 'PayUmoney payment gateway disabled because INR is not set as donation currency.', 'give-payumoney' )
		);

		// Update options.
		update_option( 'give_settings', $options );
	}
}

add_action( 'give_save_settings_give_settings', 'give_payu_validate_settings' );

