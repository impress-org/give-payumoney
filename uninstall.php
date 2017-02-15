<?php
// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Get Give core settigns.
$give_settings = give_get_settings();

// List of plugin settings.
$plugin_settings = array(
	'payumoney_sandbox_testing',
	'payumoney_payment_method_label',
	'payumoney_sandbox_merchant_key',
	'payumoney_sandbox_salt_key',
	'payumoney_live_merchant_key',
	'payumoney_live_salt_key',
);

// Unset all plugin settings.
foreach ( $plugin_settings as $setting ) {
	if( isset( $give_settings[ $setting ] ) ) {
		unset( $give_settings[ $setting ] );
	}
}

// Remove payumoney from active gateways list.
if( isset( $give_settings['gateways']['payumoney'] ) ) {
	unset( $give_settings['gateways']['payumoney'] );
}


// Update settings.
update_option( 'give_settings', $give_settings );