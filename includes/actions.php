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
 * Add phone field.
 *
 * @since 1.0
 *
 * @param $form_id
 *
 * @return bool
 */
function give_payu_add_phone_field( $form_id ) {
	// Bailout.
	if (
		'payumoney' !== give_get_chosen_gateway( $form_id )
		|| ! give_is_setting_enabled( give_get_option( 'payumoney_phone_field' ) )
	) {
		return false;
	}
	?>
	<p id="give-phone-wrap" class="form-row form-row-wide">
		<label class="give-label" for="give-phone">
			<?php esc_html_e( 'Phone', 'give-payumoney' ); ?>
			<span class="give-required-indicator">*</span>
			<span class="give-tooltip give-icon give-icon-question"
				  data-tooltip="<?php esc_attr_e( 'Enter only phone number.', 'give-payumoney' ); ?>"></span>

		</label>

		<input
				class="give-input required"
				type="tel"
				name="give_payumoney_phone"
				id="give-phone"
				value="<?php echo isset( $give_user_info['give_phone'] ) ? $give_user_info['give_phone'] : ''; ?>"
				required
				aria-required="true"
				maxlength="10"
				pattern="\d{10}"
		/>
	</p>
	<?php
}

add_action( 'give_donation_form_after_email', 'give_payu_add_phone_field' );

/**
 * Do not print cc field in donation form.
 *
 * Note: We do not need credit card field in donation form but we need billing detail fields.
 *
 * @since 1.0
 *
 * @param $form_id
 *
 * @return bool
 */
function give_payumoney_cc_form_callback( $form_id ) {

	if ( give_is_setting_enabled( give_get_option( 'payumoney_billing_details' ) ) ) {
		give_default_cc_address_fields( $form_id );

		return true;
	}

	return false;
}

add_action( 'give_payumoney_cc_form', 'give_payumoney_cc_form_callback' );


/**
 * Register Gateway Admin Notices for PayUMoney add-on.
 *
 * @since 1.0.5
 *
 * @return void
 */
function give_payumoney_show_admin_notice() {

	// Bailout, if not admin.
	if ( ! is_admin() ) {
		return;
	}

	// Show currency notice, if currency is not set as "Indian Rupee".
	if (
		current_user_can( 'manage_give_settings' ) &&
		'INR' !== give_get_currency() &&
		! class_exists( 'Give_Currency_Switcher' ) // Disable Notice, if Currency Switcher add-on is enabled.
	) {
		Give()->notices->register_notice( array(
			'id'          => 'give-payumoney-currency-notice',
			'type'        => 'error',
			'dismissible' => false,
			'description' => sprintf(
				__( 'The currency must be set as "Indian Rupee (₹)" within Give\'s <a href="%s">Currency Settings</a> in order to collect donations through the PayUMoney Payment Gateway.', 'give-payumoney' ),
				admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=general&section=currency-settings' )
			),
			'show'        => true,
		) );
	}

}

add_action( 'admin_notices', 'give_payumoney_show_admin_notice' );
