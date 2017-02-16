<?php

class Give_Payumoney_API {
	/**
	 * Instance.
	 *
	 * @since  1.0
	 * @access static
	 * @var
	 */
	static private $instance;

	static private $api_url;
	static private $merchant_key;
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
	 * @since  1.0
	 * @access public
	 * @return mixed
	 */
	public function setup_hooks() {
		add_filter( 'template_include', array( $this, 'show_payu_form_template' ) );
		add_filter( 'template_include', array( $this, 'show_payu_payment_success_template' ) );

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
	 * @since  1.0
	 * @access public
	 *
	 * @param $template
	 *
	 * @return string
	 */
	public function show_payu_payment_success_template( $template ) {
		if ( isset( $_GET['process_payu_payment'] ) && 'success' === $_GET['process_payu_payment'] ) {
			$template = GIVE_PAYU_DIR . 'templates/success.php';
		}

		return $template;
	}

	/**
	 * @param $payupaisa_args
	 *
	 * @return string
	 */
	static function get_hash( $payupaisa_args ) {
		$hashSequence = 'key|txnid|amount|productinfo|firstname|email|udf1|udf2|udf3|udf4|udf5|udf6|udf7|udf8|udf9|udf10';

		$hashVarsSeq = explode( '|', $hashSequence );
		$hash_string = '';

		foreach ( $hashVarsSeq as $hash_var ) {
			$hash_string .= isset( $payupaisa_args[ $hash_var ] ) ? $payupaisa_args[ $hash_var ] : '';
			$hash_string .= '|';
		}

		$hash_string .= self::$salt_key;

		return strtolower( hash( 'sha512', $hash_string ) );
	}

	/**
	 * Get form
	 *
	 * @since  1.0
	 * @access public
	 * @return string
	 */
	public static function get_form() {
		$donation_data = Give()->session->get( 'give_purchase' );
		$donation_id   = absint( $_GET['donation'] );

		$form_url = trailingslashit( current( explode( '?', $donation_data['post_data']['give-current-url'] ) ) );

		$payupaisa_args = array(
			'key'              => self::$merchant_key,
			'txnid'            => "{$donation_id}_" . date( 'ymds' ),
			'amount'           => $donation_data['post_data']['give-amount'],
			'firstname'        => $donation_data['post_data']['give_first'],
			'email'            => $donation_data['post_data']['give_email'],
			'productinfo'      => "This payment is donation against #{$donation_id}",
			'surl'             => $form_url . '?process_payu_payment=success',
			'furl'             => $form_url . "?form-id={$donation_data['post_data']['give-form-id']}&payment_mode=payumoney&process_payu_payment=failed",
			'lastname'         => $donation_data['post_data']['give_last'],
			'address1'         => $donation_data['post_data']['card_address'],
			'address2'         => $donation_data['post_data']['card_address_2'],
			'city'             => $donation_data['post_data']['card_city'],
			'state'            => $donation_data['post_data']['card_state'],
			'country'          => $donation_data['post_data']['billing_country'],
			'zipcode'          => $donation_data['post_data']['card_zip'],
			'curl'             => $form_url . '?success_payu_payment=1',
			'udf1'             => $donation_id,
			'comment'          => 'givewp',
			'service_provider' => 'payu_paisa',
		);

		// Add hash to payment params.
		$payupaisa_args['HASH'] = self::get_hash( $payupaisa_args );

		/**
		 * Filter the payumoney form arguments
		 *
		 * @since 1.0
		 * @param array $payupaisa_args
		 */
		$payupaisa_args = apply_filters( 'give_payumoney_form_args', $payupaisa_args );

		// Create input hidden fields.
		$payupaisa_args_array = array();
		foreach ( $payupaisa_args as $key => $value ) {
			$payupaisa_args_array[] = '<input type="hidden" name="' . $key . '" value="' . $value . '"/>';
		}

		ob_start();
		?>
		<form action="<?php echo self::$api_url; ?>" method="post" name="payuForm" style="display: none">
			<?php echo implode( '', $payupaisa_args_array ); ?>
			<input type="submit" value="Submit"/>
		</form>
		<?php
		$form_html = ob_get_contents();
		ob_get_clean();

		return $form_html;
	}
}

Give_Payumoney_API::get_instance()->setup_params()->setup_hooks();
