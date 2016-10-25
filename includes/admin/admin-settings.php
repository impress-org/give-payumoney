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
		$this->section_label = __( 'PayUmoney Payments', 'give-payumoney' );

		// Add payment gateway to payment gateways list.
		add_filter( 'give_donation_gateways', array( $this, 'add_gateways' ) );

		if ( is_admin() ) {

			// Add section to payment gateways tab.
			add_filter( 'give_get_sections_gateways', array( $this, 'add_section' ) );

			// Add section settings.
			add_filter( 'give_get_settings_gateways', array( $this, 'add_settings' ) );

			// Add setting to donation edit screen.
			// add_action( 'give_view_order_details_before', array( $this, 'give_payumoney_admin_payment_js' ), 100 );
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
			'checkout_label' => give_pum_get_payment_method_label(),
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
					'title'   => esc_html__( 'Sandbox Testing', 'give-payumoney' ),
					'id'      => 'payumoney_sandbox_testing',
					'type'    => 'radio_inline',
					'desc'    => '',
					'default' => 'enabled',
					'options' => array(
						'enabled'  => esc_html__( 'Enabled', 'give-payumoney' ),
						'disabled' => esc_html__( 'Disabled', 'give-payumoney' ),
					),
				),
				array(
					'title'   => esc_html__( 'Payment method label', 'give-payumoney' ),
					'id'      => 'payumoney_payment_method_label',
					'type'    => 'text',
					'default' => esc_html__( 'Credit Card', 'give-payumoney' ),
					'desc'    => __( 'Payment method label will be appear on frontend.', 'give-payumoney' ),
				),
				array(
					'title' => esc_html__( 'Sandbox merchant key', 'give-payumoney' ),
					'id'    => 'payumoney_sandbox_merchant_key',
					'type'  => 'text',
					'desc'  => __( 'Required merchant id provided by payumoney.', 'give-payumoney' ),
				),
				array(
					'title' => __( 'Sandbox API key', 'give-payumoney' ),
					'id'    => 'payumoney_sandbox_salt_key',
					'type'  => 'password',
					'desc'  => esc_html__( 'Required api key provided by payumoney.', 'give-payumoney' ),
				),
				array(
					'title' => esc_html__( 'Live merchant key', 'give-payumoney' ),
					'id'    => 'payumoney_live_merchant_key',
					'type'  => 'text',
					'desc'  => __( 'Required merchant id provided by payumoney.', 'give-payumoney' ),
				),
				array(
					'title' => __( 'Live API key', 'give-payumoney' ),
					'id'    => 'payumoney_live_salt_key',
					'type'  => 'password',
					'desc'  => esc_html__( 'Required api key provided by payumoney.', 'give-payumoney' ),
				),
				array(
					'id'   => 'give_payumoney_payments_setting',
					'type' => 'sectionend',
				),
			);
		}

		return $settings;
	}

	/**
	 * Load Transaction-specific admin javascript
	 *
	 * @since 1.0
	 *
	 * @param int $payment_id
	 */
	function give_payumoney_admin_payment_js( $payment_id = 0 ) {
		// Bailout.
		if ( 'payumoney' !== give_get_payment_gateway( $payment_id ) ) {
			return;
		}
		?>
		<script type="text/javascript">
			jQuery(document).ready(function ($) {
				$('select[name=give-payment-status]').change(function () {

					if ('refunded' == $(this).val()) {
						$(this)
							.closest('div')
							.append('<p id="give-payumoney-refund"><input type="checkbox" id="give_refund_in_payumoney" name="give_refund_in_payumoney" value="1"/><label for="give_refund_in_payumoney"><?php _e( 'Refund Charge in PayUmoney?', 'give-payumoney' ); ?></label></p>');
					} else {
						$('#give-payumoney-refund').remove();
					}

				});
			});
		</script>
		<?php

	}
}

Give_Payumoney_Gateway_Settings::get_instance()->setup_hooks();