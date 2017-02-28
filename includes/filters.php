<?php
/**
 * @param $messages
 *
 * @return mixed
 */
function give_pauy_form_validation_message( $messages ) {
	$messages['give_payumoney_phone'] = __( 'Please enter valid phone number without zero', 'give-payumoney' );

	return $messages;
}

add_filter( 'give_form_translation_js', 'give_pauy_form_validation_message' );
