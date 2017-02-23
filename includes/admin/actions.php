<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if PayUmoney dependency enable or not.
 *
 * @since 1.0
 */
function give_payumoney_check_dependancies() {
	// Bailout
	if ( ! give_is_payu_active() ) {
		return;
	}

	// Get core settings.
	$give_settings  = give_get_settings();
	$reset_settings = false;

	// Check dependencies.
	if ( ! give_payu_is_sandbox_mode_enabled() && ( empty( $give_settings['payumoney_live_merchant_key'] ) || empty( $give_settings['payumoney_live_salt_key'] ) ) ) {
		$reset_settings = true;

		// Show notice.
		add_filter( 'give-settings_update_notices', 'give_payu_disable_by_agent_credentials' );
	} elseif ( give_payu_is_sandbox_mode_enabled() && ( empty( $give_settings['payumoney_sandbox_merchant_key'] ) || empty( $give_settings['payumoney_sandbox_salt_key'] ) ) ) {
		$reset_settings = true;

		// Show notice.
		add_filter( 'give-settings_update_notices', 'give_payu_disable_by_agent_credentials' );
	}

	// Bailout.
	if ( ! $reset_settings ) {
		return;
	}

	// Deactivate iats payment gateways: It has some currency dependency.
	unset( $give_settings['gateways']['payumoney'] );

	// Update settings.
	update_option( 'give_settings', $give_settings );
}

add_action( 'give-settings_saved', 'give_payumoney_check_dependancies' );


/**
 * Add message when PayUmoney disable by agent credentials.
 *
 * @param array $messages
 *
 * @return mixed
 */
function give_payu_disable_by_agent_credentials( $messages ) {
	$messages['give-payumoney-disable'] = sprintf( __( 'PayUmoney payment gateway disabled automatically because <a href="%s">merchant credentials</a> is not correct.', 'give-payumoney' ), admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=payumoney' ) );

	return $messages;
}


/**
 * Show transaction ID under donation meta.
 *
 * @since 1.0
 */
function give_payumoney_link_transaction_id( $transaction_id ) {
	$payumoney_response = get_post_meta( absint( $_GET['id'] ), 'payumoney_donation_response', true );
	echo sprintf( '<a href="https://www.payumoney.com/merchant/dashboard/#/paymentCompleteDetails/%s" target="_blank">%s</a>', $payumoney_response['payuMoneyId'], $payumoney_response['txnid'] );
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
