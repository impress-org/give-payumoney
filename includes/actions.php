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
 * @param $form_id
 */
function give_payu_show_frontend_notices( $form_id ){
	if( ! isset( $_GET['form-id'] ) || $form_id !== absint( $_GET['form-id'] ) ) {
		return;
	}
	
	if ( isset( $_GET['payu-error-message'] ) ) {
		if ( $error_message = sanitize_text_field( $_GET['payu-error-message'] )  ) {

			// Show error.
			give_output_error( $error_message , true, 'error' );
		}
	}

	if( isset( $_GET['process_payu_payment'] ) && 'failed' === $_GET['process_payu_payment'] ) {
		// Show error.
		give_output_error( __( 'Transaction failed. Please try after some time.', 'give-payumoney' ) , true, 'error' );
	}
}
add_action( 'give_pre_form_output', 'give_payu_show_frontend_notices' );

