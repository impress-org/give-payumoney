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
 *
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
	if ( 'payumoney' !== give_get_chosen_gateway( $form_id ) || ! give_is_setting_enabled( give_get_option('payumoney_phone_field') ) ) {
		return false;
	}
	?>
	<p id="give-phone-wrap" class="form-row form-row-wide">
		<label class="give-label" for="give-phone">
			<?php esc_html_e( 'Phone', 'give-payumoney' ); ?>
			<span class="give-required-indicator">*</span>
			<span class="give-tooltip give-icon give-icon-question" data-tooltip="<?php esc_attr_e( 'Enter only phone number.', 'give-payumoney' ); ?>"></span>

		</label>

		<input
				class="give-input required"
				type="tel"
				name="give_payumoney_phone"
				placeholder="<?php esc_attr_e( '99999999999', 'give' ); ?>"
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

