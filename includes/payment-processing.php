<?php
/**
 * Progress donation by iATS payment gateway
 *
 * @since 1.0
 *
 * @param $donation_data
 */
function give_process_payumoney_payment( $donation_data ) {
	if ( ! wp_verify_nonce( $donation_data['gateway_nonce'], 'give-gateway' ) ) {
		wp_die( esc_html__( 'Nonce verification has failed.', 'give-payumoney' ), esc_html__( 'Error', 'give' ), array( 'response' => 403 ) );
	}

	$form_id  = intval( $donation_data['post_data']['give-form-id'] );
	$price_id = isset( $donation_data['post_data']['give-price-id'] ) ? $donation_data['post_data']['give-price-id'] : '';

	// Collect payment data.
	$donation_payment_data = array(
		'price'           => $donation_data['price'],
		'give_form_title' => $donation_data['post_data']['give-form-title'],
		'give_form_id'    => $form_id,
		'give_price_id'   => $price_id,
		'date'            => $donation_data['date'],
		'user_email'      => $donation_data['user_email'],
		'purchase_key'    => $donation_data['purchase_key'],
		'currency'        => give_get_currency(),
		'user_info'       => $donation_data['user_info'],
		'status'          => 'pending',
		'gateway'         => 'payumoneypayments',
	);

	// Record the pending payment.
	$payment = give_insert_payment( $donation_payment_data );

	// Auto set payment to abandoned in one hour if donor is not able to donate in that time.
	wp_schedule_single_event( current_time( 'timestamp', 1 ) + HOUR_IN_SECONDS, 'give_payumoney_set_donation_abandoned', array( $payment ) );

	// Verify donation payment.
	if ( ! $payment ) {
		// Record the error.
		give_record_gateway_error(
			esc_html__( 'Payment Error', 'give' ),
			/* translators: %s: payment data */
			sprintf(
				esc_html__( 'Payment creation failed before process Payumoney gateway. Payment data: %s', 'give' ),
				json_encode( $donation_payment_data )
			),
			$payment
		);

		// Problems? Send back.
		give_send_back_to_checkout( '?payment-mode=' . $donation_data['post_data']['give-gateway'] );
	}

	// Send to success page.
	wp_redirect( home_url( '/?process_payu_payment=processing&donation=' . $payment ) );
	exit();
}

add_action( 'give_gateway_payumoney', 'give_process_payumoney_payment' );


/**
 * Process refund.
 *
 * @since 1.0
 *
 * @param bool   $do_change
 * @param int    $donation_id
 * @param string $new_status
 * @param string $old_status
 *
 * @return bool
 */
function give_payumoney_donation_refund( $do_change, $donation_id, $new_status, $old_status ) {
	$donation = new Give_Payment( $donation_id );

	// Bailout.
	if ( 'refunded' !== $new_status || 'payumoneypayments' !== $donation->gateway || empty( $_POST['give_refund_in_payumoney'] ) ) {
		return $do_change;
	}

	// Get agent credentials.
	$agent_credential = give_payumoney_get_agent_credentials();
	$agentCode        = $agent_credential['code'];            // Assigned by iATS
	$password         = $agent_credential['password'];        // Assigned by iATS

	// Process link.
	$iATS_PL = new iATS\ProcessLink( $agentCode, $password, give_payumoney_get_server_name() );

	$request = array(
		'transactionId' => give_get_payment_transaction_id( $donation->ID ),
		'total'         => - $donation->total,
		'comment'       => sprintf( __( "Refund for donation %d", 'give-payumoney' ), $donation->ID ),
	);

	// Make the API call using the ProcessLink service.
	$response = $iATS_PL->processCreditCardRefundWithTransactionId( $request );

	// Verify successful call
	if ( 'OK' != substr( trim( $response['AUTHORIZATIONRESULT'] ), 0, 2 ) ) {
		$url_data = parse_url( $_SERVER['REQUEST_URI'] );

		// Build query
		$url_query = array_merge(
			wp_parse_args( $url_data['query'] ),
			array( 'give-payumoney-message' => $response['code'] )
		);

		$url = home_url( "/{$url_data['path']}?" . http_build_query( $url_query ) );

		// Redirect.
		wp_safe_redirect( $url );
		exit();
	}

	// Add payumoney payment response meta.
	update_post_meta( $donation->ID, 'payumoney_refund_response', $response );

	// Add refund transaction id.
	give_update_payment_meta( $donation->ID, '_give_payment_refund_id', $response['TRANSACTIONID'] );

	return true;
}

add_filter( 'give_should_update_payment_status', 'give_payumoney_donation_refund', 10, 4 );


/**
 * Show refund id.
 *
 * @since 1.0
 *
 * @param $donation_id
 */
function give_payumoney_show_refund_transaction_id( $donation_id ) {
	/* @var Give_Payment $donation Give_Payment object. */
	$donation = new Give_Payment( $donation_id );

	// Bailout.
	if ( 'refunded' !== $donation->status || 'payumoneypayments' !== $donation->gateway ) {
		return;
	}

	if ( $refund_id = give_get_payment_meta( $donation_id, '_give_payment_refund_id', true ) ):
		?>
		<div class="give-admin-box-inside">
			<p>
				<strong><?php esc_html_e( 'Refund ID:', 'give' ); ?></strong>&nbsp;
				<?php echo $refund_id; ?>
			</p>
		</div>
		<?php
	endif;
}

add_action( 'give_view_order_details_payment_meta_after', 'give_payumoney_show_refund_transaction_id' );