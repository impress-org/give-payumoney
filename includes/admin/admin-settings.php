<?php

/**
 * Class Give_Payumoney_Gateway_Settings
 *
 * @since 1.0
 */
class Give_Payumoney_Gateway_Settings {
	/**
	 * @since  1.0
	 * @access static
	 * @var Give_Payumoney_Gateway_Settings $instance
	 */
	static private $instance;

	/**
	 * @since  1.0
	 * @access private
	 * @var string $section_id
	 */
	private $section_id;

	/**
	 * @since  1.0
	 * @access private
	 * @var string $section_label
	 */
	private $section_label;

	/**
	 * Give_Payumoney_Gateway_Settings constructor.
	 */
	private function __construct() {
	}

	/**
	 * get class object.
	 *
	 * @since 1.0
	 * @return Give_Payumoney_Gateway_Settings
	 */
	static function get_instance() {
		if ( null === static::$instance ) {
			static::$instance = new static();
		}

		return static::$instance;
	}

	/**
	 * Setup hooks.
	 *
	 * @since 1.0
	 */
	public function setup_hooks() {
		$this->section_id    = 'payumoney';
		$this->section_label = __( 'PayUmoney', 'give-payumoney' );

		// Add payment gateway to payment gateways list.
		add_filter( 'give_payment_gateways', array( $this, 'add_gateways' ) );

		if ( is_admin() ) {

			// Add section to payment gateways tab.
			add_filter( 'give_get_sections_gateways', array( $this, 'add_section' ) );

			// Add section settings.
			add_filter( 'give_get_settings_gateways', array( $this, 'add_settings' ) );
		}
	}

	/**
	 * Add payment gateways to gateways list.
	 *
	 * @since 1.0
	 *
	 * @param array $gateways array of payment gateways.
	 *
	 * @return array
	 */
	public function add_gateways( $gateways ) {
		$gateways[ $this->section_id ] = array(
			'admin_label'    => $this->section_label,
			'checkout_label' => give_payu_get_payment_method_label(),
		);

		return $gateways;
	}

	/**
	 * Add setting section.
	 *
	 * @since 1.0
	 *
	 * @param array $sections Array of section.
	 *
	 * @return array
	 */
	public function add_section( $sections ) {
		$sections[ $this->section_id ] = $this->section_label;

		return $sections;
	}

	/**
	 * Add plugin settings.
	 *
	 * @since 1.0
	 *
	 * @param array $settings Array of setting fields.
	 *
	 * @return array
	 */
	public function add_settings( $settings ) {
		$current_section = give_get_current_setting_section();

		if ( $this->section_id == $current_section ) {
			$settings = array(
				array(
					'id'   => 'give_payumoney_payments_setting',
					'type' => 'title',
				),
				array(
					'title'   => __( 'Payment Method Label', 'give-payumoney' ),
					'id'      => 'payumoney_payment_method_label',
					'type'    => 'text',
					'default' => give_payu_get_payment_method_label(),
					'desc'    => __( 'Payment method label will be appear on frontend.', 'give-payumoney' ),
				),
				array(
					'title' => __( 'Live Merchant Key', 'give-payumoney' ),
					'id'    => 'payumoney_live_merchant_key',
					'type'  => 'text',
					'desc'  => __( 'Required LIVE Merchant ID provided by payumoney.', 'give-payumoney' ),
				),
				array(
					'title' => __( 'Live Merchant Salt', 'give-payumoney' ),
					'id'    => 'payumoney_live_salt_key',
					'type'  => 'api_key',
					'desc'  => __( 'Required LIVE Merchant Salt provided by payumoney.', 'give-payumoney' ),
				),
				array(
					'title' => __( 'Sandbox Merchant Key', 'give-payumoney' ),
					'id'    => 'payumoney_sandbox_merchant_key',
					'type'  => 'text',
					'desc'  => __( 'Required TEST Merchant Key provided by payumoney.', 'give-payumoney' ),
				),
				array(
					'title' => __( 'Sandbox Merchant Salt', 'give-payumoney' ),
					'id'    => 'payumoney_sandbox_salt_key',
					'type'  => 'api_key',
					'desc'  => __( 'Required TEST Merchant Salt provided by payumoney.', 'give-payumoney' ),
				),
				array(
					'title' => __( 'Show Phone Field', 'give-payumoney' ),
					'id'    => 'payumoney_phone_field',
					'type'  => 'radio_inline',
					'desc'  => __( 'Enable this setting if you want to show phone field on donation form (this field is necessary for PayUmoney).', 'give-payumoney' ),
					'default' => 'enabled',
					'options' => array(
						'enabled' => __( 'Enabled', 'give-payumoney' ),
						'disabled' => __( 'Disabled', 'give-payumoney' ),
					),
				),
				array(
					'id'   => 'give_payumoney_payments_setting',
					'type' => 'sectionend',
				),
			);
		}// End if().

		return $settings;
	}
}

Give_Payumoney_Gateway_Settings::get_instance()->setup_hooks();
