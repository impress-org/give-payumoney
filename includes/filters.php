<?php
/**
 * Do not print cc field in donation form.
 *
 * Note: We do not need credit card field in donation form but we need billing detail fields.
 *
 * @since 1.0
 *
 * @param $form_id
 */
function give_payumoney_cc_form_callback( $form_id ) {
	give_default_cc_address_fields( $form_id );
}

add_action( 'give_payumoney_cc_form', 'give_payumoney_cc_form_callback' );


function give_pauy_form_validation_message( $messages ) {
	$messages['give_payumoney_phone'] = __( 'Please enter valid phone number without zero', 'give-payumoney' );

	return $messages;
}

add_filter( 'give_form_translation_js', 'give_pauy_form_validation_message' );
