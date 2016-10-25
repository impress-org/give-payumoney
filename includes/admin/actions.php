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
	if ( ! give_is_pum_active() ) {
		return;
	}

	// Get core settings.
	$give_settings  = give_get_settings();
	$reset_settings = false;


	// Check dependencies.
	if ( ! give_pum_is_sandbox_mode_enabled() && ( empty( $give_settings['payumoney_live_merchant_key'] ) || empty( $give_settings['payumoney_live_salt_key'] ) ) ) {
		$reset_settings = true;

		// Show notice.
		add_filter( 'give-settings_update_notices', 'give_pum_disable_by_agent_credentials' );
	} elseif ( give_pum_is_sandbox_mode_enabled() && ( empty( $give_settings['payumoney_sandbox_merchant_key'] ) || empty( $give_settings['payumoney_sandbox_salt_key'] ) ) ) {
		$reset_settings = true;

		// Show notice.
		add_filter( 'give-settings_update_notices', 'give_pum_disable_by_agent_credentials' );
	}

	// Bailout.
	if ( ! $reset_settings ) {
		return;
	}

	// Deactivate iats payment gateways: It has some currency dependency.
	unset( $give_settings['gateways']['payumoney'] );

	// Update settings.
	update_option( 'give_settings', $give_settings );

	error_log( print_r(  'demo', true ) . "\n", 3, WP_CONTENT_DIR . '/debug_new.log' );
}

add_action( 'give-settings_saved', 'give_payumoney_check_dependancies' );


/**
 * Add message when PayUmoney disable by agent credentials.
 *
 * @param array $messages
 *
 * @return mixed
 */
function give_pum_disable_by_agent_credentials( $messages ) {
	$messages['iats-disable'] = sprintf( __( 'PayUmoney payment gateway disabled automatically because <a href="%s">merchant credentials</a> is not correct.', 'give-payumoney' ), admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=payumoney' ) );

	return $messages;
}