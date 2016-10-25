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