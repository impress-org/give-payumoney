<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Show transaction ID under donation meta.
 *
 * @since 1.0
 *
 * @param $transaction_id
 */
function give_payumoney_link_transaction_id( $transaction_id ) {

	$payment = new Give_Payment( $transaction_id );

	$payumoney_trans_url = 'https://www.payumoney.com/merchant/dashboard/#/paymentCompleteDetails/';

	if ( 'test' === $payment->mode ) {
		$payumoney_trans_url = 'https://test.payumoney.com/merchant/dashboard/#/paymentCompleteDetails/';
	}

	$payumoney_response = get_post_meta( absint( $_GET['id'] ), 'payumoney_donation_response', true );
	$payumoney_trans_url .= $payumoney_response['payuMoneyId'];

	echo sprintf( '<a href="%1$s" target="_blank">%2$s</a>', $payumoney_trans_url, $payumoney_response['txnid'] );
}

add_filter( 'give_payment_details_transaction_id-payumoney', 'give_payumoney_link_transaction_id', 10, 2 );


/**
 * Add payumoney donor detail to "Donor Detail" metabox
 *
 * @since 1.0
 *
 * @param $payment_id
 *
 * @return bool
 */
function give_payu_view_details( $payment_id ) {
	// Bailout.
	if ( 'payumoney' !== give_get_payment_gateway( $payment_id ) ) {
		return false;
	}

	$payumoney_response = get_post_meta( absint( $_GET['id'] ), 'payumoney_donation_response', true );

	// Check if phone exit in payumoney response.
	if ( empty( $payumoney_response['phone'] ) ) {
		return false;
	}
	?>
    <div class="column">
        <p>
            <strong><?php _e( 'Phone:', 'give-payumoney' ); ?></strong><br>
			<?php echo $payumoney_response['phone']; ?>
        </p>
    </div>
	<?php
}

add_action( 'give_payment_view_details', 'give_payu_view_details' );
