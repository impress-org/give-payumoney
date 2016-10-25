<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Check if iATS dependency enable or not.
 *
 * @since 1.0
 */
function give_iats_check_dependancies() {
	// Bailout
	if ( ! give_is_iats_active() ) {
		return;
	}

	// Get core settings.
	$give_settings  = give_get_settings();
	$reset_settings = false;


	// Check dependencies.
	if ( ! in_array( $give_settings['currency'], array( 'USD', 'CAD', 'GBA', 'EUR' ) ) ) {
		$reset_settings = true;

		// Show notice.
		add_filter( 'give-settings_update_notices', 'give_iats_disable_by_currency' );

	} elseif ( ! give_iats_is_sandbox_mode_enabled() && ( empty( $give_settings['iats_live_agent_code'] ) || empty( $give_settings['iats_live_agent_password'] ) ) ) {
		$reset_settings = true;

		// Show notice.
		add_filter( 'give-settings_update_notices', 'give_iats_disable_by_agent_credentials' );
	} elseif ( give_iats_is_sandbox_mode_enabled() && ( empty( $give_settings['iats_sandbox_agent_code'] ) || empty( $give_settings['iats_sandbox_agent_password'] ) ) ) {
		$reset_settings = true;

		// Show notice.
		add_filter( 'give-settings_update_notices', 'give_iats_disable_by_agent_credentials' );
	}

	// Bailout.
	if ( ! $reset_settings ) {
		return;
	}

	// Deactivate iats payment gateways: It has some currency dependency.
	unset( $give_settings['gateways']['iatspayments'] );

	// Update settings.
	update_option( 'give_settings', $give_settings );
}

add_action( 'give-settings_saved', 'give_iats_check_dependancies' );


/**
 * Add message when iATS disable by currency.
 *
 * @param array $messages
 *
 * @return mixed
 */
function give_iats_disable_by_currency( $messages ) {
	$messages['iats-disable'] = esc_html__( 'iATS payment gateway disabled automatically because you do not have required currency ( USD, CAD, GBA, EUR ).', 'give-iatspayments' );

	return $messages;
}

/**
 * Add message when iATS disable by agent credentials.
 *
 * @param array $messages
 *
 * @return mixed
 */
function give_iats_disable_by_agent_credentials( $messages ) {
	$messages['iats-disable'] = sprintf( __( 'iATS payment gateway disabled automatically because <a href="%s">agent credentials</a> is not correct.', 'give-iatspayments' ), admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=iatspayments' ) );

	return $messages;
}