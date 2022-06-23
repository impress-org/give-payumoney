<?php

use Give\Views\Form\Templates\Sequoia\Sequoia;

class Give_Payumoney_API {
	/**
	 * Instance.
	 *
	 * @since  1.0
	 * @access static
	 * @var
	 */
	static private $instance;

	/**
	 * @var
	 */
	static private $api_url;

	/**
	 * @var
	 */
	static private $merchant_key;

	/**
	 * @var
	 */
	static private $salt_key;

	/**
	 * Singleton pattern.
	 *
	 * @since  1.0
	 * @access private
	 * Give_Payumoney_API constructor.
	 */
	private function __construct() {
	}


	/**
	 * Get instance.
	 *
	 * @since  1.0
	 * @access static
	 * @return static
	 */
	static function get_instance() {
		if ( null === static::$instance ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Setup params.
	 *
	 * @since  1.0
	 * @access public
	 * @return mixed
	 */
	public function setup_params() {
		$merchant = give_payu_get_merchant_credentials();

		self::$merchant_key = $merchant['merchant_key'];
		self::$salt_key     = $merchant['salt_key'];
		self::$api_url      = give_payu_get_api_url();

		return self::$instance;
	}

	/**
	 * Setup hooks.
	 *
     * @since 1.0.8 Handle payUmoney redirect on template_redirect action hook.
	 * @since  1.0
	 * @access public
	 * @return mixed
	 */
	public function setup_hooks() {
		add_filter( 'template_include', array( $this, 'show_payu_form_template' ) );
		add_action( 'template_redirect', array( $this, 'show_payu_payment_success_template' ) );

		return self::$instance;
	}

	/**
	 * Show payumoney form template.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param $template
	 *
	 * @return string
	 */
	public function show_payu_form_template( $template ) {
		if ( isset( $_GET['process_payu_payment'] ) && 'processing' === $_GET['process_payu_payment'] ) {
			$template = GIVE_PAYU_DIR . 'templates/form.php';
		}

		return $template;
	}

	/**
	 * Show success template
	 *
     * @since 1.0.8 Load file to handle payUmoney redirect.
	 * @since  1.0
	 * @access public
	 */
	public function show_payu_payment_success_template() {
        if (isset( $_REQUEST['process_payu_payment'] ) && in_array( $_REQUEST['process_payu_payment'], array( 'success', 'failure' ) )) {
            require_once GIVE_PAYU_DIR . 'templates/success.php';
		}
	}

	/**
	 * @param        $payupaisa_args
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array  $payupaisa_args
	 * @param string  $which Hash logic code
	 *
	 * @return string
	 */
	public static function get_hash( $payupaisa_args, $which ) {
		$hashSequence = '';

		if( ! in_array( $which, array( 'before_transaction', 'after_transaction' ) ) ) {
			return '';
		}

		switch ( $which ) {
			case 'before_transaction':
				$hashSequence = 'key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5||||||SALT';
				break;

			case 'after_transaction':
				$hashSequence = 'SALT|status||||||udf5|udf4|udf3|udf2|udf1|email|firstname|productinfo|amount|txnid|key';
				break;
		}

		$hashVarsSeq = explode( '|', $hashSequence );
		$hash_string = array();

		// Add salt key.
		if( ! array_key_exists( 'SALT', $payupaisa_args ) ) {
			$payupaisa_args['SALT'] = self::$salt_key;
		}

		foreach ( $hashVarsSeq as $hash_var ) {
			$hash_string[] = isset( $payupaisa_args[ $hash_var ] ) ? $payupaisa_args[ $hash_var ] : '';
		}

		$hash_string = implode( '|', $hash_string );

		return strtolower( hash( 'sha512', $hash_string ) );
	}

	/**
	 * Get form
	 *
	 * @since  1.0
	 * @since 1.0.7 add logic to submit donation form to parent when donation form is in iframe.
	 *
	 * @access public
	 * @return string
	 */
	public static function get_form() {
		$donation_data = Give()->session->get( 'give_purchase' );
		$donation_id   = absint( $_GET['donation'] );
		$form_id       = absint( $_GET['form-id'] );

		$form_url = trailingslashit( current( explode( '?', $donation_data['post_data']['give-current-url'] ) ) );

		$payupaisa_args = array(
			'key'              => self::$merchant_key,
			'txnid'            => "{$donation_id}_" . date( 'ymds' ),
			'amount'           => give_sanitize_amount( give_donation_amount( $donation_id ) ),
			'firstname'        => $donation_data['post_data']['give_first'],
			'email'            => $donation_data['post_data']['give_email'],
			'phone'            => ( isset( $donation_data['post_data']['give_payumoney_phone'] ) ? $donation_data['post_data']['give_payumoney_phone'] : '' ),
			'productinfo'      => sprintf( __( 'This is a donation payment for %s', 'give-payumoney' ), $donation_id ),
			'surl'             => $form_url . '?process_payu_payment=success',
			'furl'             => $form_url . '?process_payu_payment=failure',
			'lastname'         => $donation_data['post_data']['give_last'],
			'udf1'             => $donation_id,
			'udf2'             => $form_id,
			'udf3'             => $form_url,
			'udf5'             => 'givewp',
		);

		// Pass address info if present.
		if ( give_is_setting_enabled( give_get_option( 'payumoney_billing_details' ) ) ) {
			$payupaisa_args['address1'] = $donation_data['post_data']['card_address'];
			$payupaisa_args['address2'] = $donation_data['post_data']['card_address_2'];
			$payupaisa_args['city']     = $donation_data['post_data']['card_city'];
			$payupaisa_args['state']    = $donation_data['post_data']['card_state'];
			$payupaisa_args['country']  = $donation_data['post_data']['billing_country'];
			$payupaisa_args['zipcode']  = $donation_data['post_data']['card_zip'];
		}

		// Add Service Provider only when the selected account is PayUMoney and not PayUBiz.
		if ( 'payumoney' === give_payu_get_selected_account() ) {
			$payupaisa_args['service_provider'] = 'payu_paisa';
        }

		// Add hash to payment params.
		$payupaisa_args['hash'] = self::get_hash( $payupaisa_args, 'before_transaction' );

		/**
		 * Filter the payumoney form arguments
		 *
		 * @since 1.0
		 *
		 * @param array $payupaisa_args
		 */
		$payupaisa_args = apply_filters( 'give_payumoney_form_args', $payupaisa_args );

		// Create input hidden fields.
		$payupaisa_args_array = array();
		foreach ( $payupaisa_args as $key => $value ) {
			$payupaisa_args_array[] = '<input type="hidden" name="' . $key . '" value="' . $value . '"/>';
		}

		ob_start();

		/* @var Sequoia $sequoiaTemplateClass */
		$sequoiaTemplateClass = give( Sequoia::class );
		?>
		<form
			action="<?php echo self::$api_url; ?>"
			method="post"
			name="payuForm" style="display: none"
			<?php if( $sequoiaTemplateClass->getID() === Give\Helpers\Form\Template::getActiveID( $form_id ) ) { echo 'target="_parent"'; } ?>
		>
			<?php echo implode( '', $payupaisa_args_array ); ?>
            <input type="submit" value="Submit"/>
        </form>
		<?php
		$form_html = ob_get_contents();
		ob_get_clean();

		return $form_html;
	}


	/**
	 * Process payumoney success payment.
	 *
	 * @since  1.0
	 *
	 * @access public
	 *
	 * @param int $donation_id
	 */
	public static function process_success( $donation_id ) {
		$donation = new Give_Payment( absint( $_POST['udf1'] ) );
		$donation->update_status( 'completed' );
		$donation->add_note( sprintf( __( 'PayUmoney payment completed (Transaction id: %s)', 'give-payumoney' ), $_REQUEST['txnid'] ) );

		wp_clear_scheduled_hook( 'give_payumoney_set_donation_abandoned', array( absint( $donation_id ) ) );

		give_set_payment_transaction_id( $donation_id, $_REQUEST['txnid'] );
		update_post_meta( $donation_id, 'payumoney_donation_response', $_REQUEST );

		give_send_to_success_page();
	}

	/**
	 * Process payumoney failure payment.
	 *
	 * @since  1.0
	 *
	 * @access public
	 *
	 * @param int $donation_id
	 */
	public static function process_failure( $donation_id ) {
		$donation = new Give_Payment( $donation_id );
		$donation->update_status( 'failed' );
		$donation->add_note( sprintf( __( 'PayUmoney payment failed (Transaction id: %s)', 'give-payumoney' ), $_REQUEST['txnid'] ) );

		wp_clear_scheduled_hook( 'give_payumoney_set_donation_abandoned', array( absint( $donation_id ) ) );

		give_set_payment_transaction_id( $donation_id, $_REQUEST['txnid'] );
		update_post_meta( $donation_id, 'payumoney_donation_response', $_REQUEST );

		give_record_gateway_error(
			esc_html__( 'PayUmoney Error', 'give-payumoney' ),
			esc_html__( 'The PayUmoney Gateway returned an error while charging a donation.', 'give-payumoney' ) . '<br><br>' . sprintf( esc_attr__( 'Details: %s', 'give-payumoney' ), '<br>' . print_r( $_REQUEST, true ) ),
			$donation_id
		);

		wp_redirect( give_get_failed_transaction_uri() );
		exit();
	}

	/**
	 * Process payumoney pending payment.
	 *
	 * @since  1.0
	 *
	 * @access public
	 *
	 * @param int $donation_id
	 */
	public static function process_pending( $donation_id ) {
		$donation = new Give_Payment( $donation_id );
		$donation->add_note( sprintf( __( 'PayUmoney payment has "%s" status. Check the <a href="%s" target="_blank">PayUmoney merchant dashboard</a> for more information or check the <a href="%s" target="_blank">payment gateway error logs</a> for additional details', 'give-payumoney' ), $_REQUEST['status'], "https://www.payumoney.com/merchant/dashboard/#/paymentCompleteDetails/{$_REQUEST['payuMoneyId']}", admin_url( 'edit.php?post_type=give_forms&page=give-tools&tab=logs&section=gateway_errors' ) ) );

		wp_clear_scheduled_hook( 'give_payumoney_set_donation_abandoned', array( absint( $donation_id ) ) );

		give_set_payment_transaction_id( $donation_id, $_REQUEST['txnid'] );
		update_post_meta( $donation_id, 'payumoney_donation_response', $_REQUEST );

		give_record_gateway_error(
			esc_html__( 'PayUmoney Error', 'give-payumoney' ),
			esc_html__( 'The PayUmoney Gateway returned an error while charging a donation.', 'give-payumoney' ) . '<br><br>' . sprintf( esc_attr__( 'Details: %s', 'give-payumoney' ), '<br>' . print_r( $_REQUEST, true ) ),
			$donation_id
		);

		give_send_to_success_page();
	}
}

Give_Payumoney_API::get_instance()->setup_params()->setup_hooks();
