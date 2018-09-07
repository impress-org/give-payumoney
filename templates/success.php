<?php
// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html lang="en">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
		<meta http-equiv="X-UA-Compatible" content="ie=edge">
		<title><?php echo esc_html__( 'Process PayUmoney API Response', 'give-payumoney' ); ?></title>
	</head>
	<body>
		<?php
		/**
		 * Response array
		 *
		 * Array
		 *  (
		 *      [mihpayid] => 403993715515126963
		 *      [mode] => CC
		 *      [status] => success
		 *      [unmappedstatus] => captured
		 *      [key] => gtKFFx
		 *      [txnid] => 2433_16102534
		 *      [amount] => 14.00
		 *      [cardCategory] => international
		 *      [discount] => 0.00
		 *      [net_amount_debit] => 14
		 *      [addedon] => 2016-10-25 13:22:35
		 *      [productinfo] => This payment is donation against  2433
		 *      [firstname] => Ravinder
		 *      [lastname] => Kumar
		 *      [address1] => street 1  Ujina
		 *      [address2] => street 2
		 *      [city] => gurgaon
		 *      [state] => HR
		 *      [country] => IN
		 *      [zipcode] => 122001
		 *      [email] => ravinder25@gmail.com
		 *      [phone] =>
		 *      [udf1] => 2433
		 *      [udf2] =>
		 *      [udf3] =>
		 *      [udf4] =>
		 *      [udf5] =>
		 *      [udf6] =>
		 *      [udf7] =>
		 *      [udf8] =>
		 *      [udf9] =>
		 *      [udf10] => givewp
		 *      [hash] => 964fbafecf32c696a3465828a21b8985b53a55f9677c77f82bb53967d8bb9bb2b41c7baec5b38ca1c9a125d085c8aaad26919c5bcce75d668b342d1633ce402b
		 *      [field1] => 629917698996
		 *      [field2] => 999999
		 *      [field3] => 7415811221362990
		 *      [field4] => 7415811221362990
		 *      [field5] =>
		 *      [field6] =>
		 *      [field7] =>
		 *      [field8] =>
		 *      [field9] => SUCCESS
		 *      [payment_source] => payu
		 *      [PG_TYPE] => HDFCPG
		 *      [bank_ref_num] => 7415811221362990
		 *      [bankcode] => CC
		 *      [error] => E000
		 *      [error_Message] => No Error
		 *      [name_on_card] => Demo
		 *      [cardnum] => 400002XXXXXX2445
		 *      [cardhash] => This field is no longer supported in postback params.
		 *      [issuing_bank] => UNKNOWN
		 *      [card_type] => VISA
		 * )
		 */
		$_post = give_clean( $_POST );

		if (
			isset( $_post['hash'] )
			&& $_post['hash'] === Give_Payumoney_API::get_hash( $_post, 'after_transaction' )
		) {
			$donation_id = $_REQUEST['udf1'];

			if ( ! empty( $donation_id ) ) {
				try {
					$donation = new Give_Payment( $donation_id );
					$hash     = $_REQUEST['hash'];

					if ( $donation->status !== 'completed' ) {
						// Process each payment status.
						switch ( esc_attr( $_REQUEST['status'] ) ) {
							case 'success':
								Give_Payumoney_API::process_success( $donation_id );
								break;

							case 'failure':
								Give_Payumoney_API::process_failure( $donation_id );
								break;

							default:
								Give_Payumoney_API::process_pending( $donation_id );
						}
					}
				} catch ( Exception $e ) {
					error_log( print_r( $e->getMessage(), true ) . "\n", 3, WP_CONTENT_DIR . '/debug.log' );
				}// End try().
			}// End if().
		}// End if().

		// Default redirect to home page.
		wp_redirect( home_url( '/' ) );
		exit();
		?>
	</body>
</html>
