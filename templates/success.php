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
		<title><?php echo esc_html__( 'Process Payumoney API Response', 'give-payumoney' ); ?></title>
	</head>
	<body>
		<?php
		if ( ! isset( $_POST ) ) {
			// @TODO: redirect back to checkout page.
		}

		// Process each payment status.
		switch ( esc_attr( $_POST['status'] ) ) {
			case 'success':
				$donation = new Give_Payment( absint( $_POST['udf1'] ) );
				$donation->update_status( 'completed' );
				give_send_to_success_page();
				break;

			case 'failure': ?>
				<form action="<?php echo '?form-id=' . absint( $_POST['udf1'] ) . '&payment_mode = payumoney'; ?>" name="payuFailure" method="post">
					<input type="hidden" name="payu-error-message" value="<?php echo $_POST['error_Message'];  ?>">
				</form>
				<script>//document.payuFailure.submit();</script>
				<?php
				break;

			case 'pending':
				break;
		}
		?>
	</body>
</html>