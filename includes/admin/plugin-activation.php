<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
/**
 * Plugins row action links
 *
 * @since 1.0
 *
 * @param array $actions An array of plugin action links.
 *
 * @return array An array of updated action links.
 */
function give_payumoney_plugin_action_links( $actions ) {
	$new_actions = array(
		'settings' => sprintf(
			'<a href="%1$s">%2$s</a>',
			admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=payumoney' ),
			esc_html__( 'Settings', 'give-payumoney' )
		),
	);

	return array_merge( $new_actions, $actions );
}

add_filter( 'plugin_action_links_' . GIVE_PAYU_BASENAME, 'give_payumoney_plugin_action_links' );


/**
 * Plugin row meta links
 *
 * @since 1.0
 *
 * @param array $plugin_meta An array of the plugin's metadata.
 * @param string $plugin_file Path to the plugin file, relative to the plugins directory.
 *
 * @return array
 */
function give_payumoney_plugin_row_meta( $plugin_meta, $plugin_file ) {

	if ( $plugin_file != GIVE_PAYU_BASENAME ) {
		return $plugin_meta;
	}

	$new_meta_links = array(
		sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( add_query_arg( array(
					'utm_source'   => 'plugins-page',
					'utm_medium'   => 'plugin-row',
					'utm_campaign' => 'admin',
				), 'http://docs.givewp.com/addon-payumoney' )
			),
			esc_html__( 'Documentation', 'give-payumoney' )
		),
		sprintf(
			'<a href="%1$s" target="_blank">%2$s</a>',
			esc_url( add_query_arg( array(
					'utm_source'   => 'plugins-page',
					'utm_medium'   => 'plugin-row',
					'utm_campaign' => 'admin',
				), 'https://givewp.com/addons/' )
			),
			esc_html__( 'Add-ons', 'give-payumoney' )
		),
	);

	return array_merge( $plugin_meta, $new_meta_links );
}

add_filter( 'plugin_row_meta', 'give_payumoney_plugin_row_meta', 10, 2 );
