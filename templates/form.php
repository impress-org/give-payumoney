<?php
// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html lang="en">
	<head>
		<title><?php _e( 'Process Donation with CCAvenue payment gateways', 'give-payumoney' ); ?></title>
	</head>
	<body>
		<!-- Request -->
		<?php echo Give_Payumoney_API::get_form(); ?>

		<script language='javascript'>document.payuForm.submit();</script>
	</body>
</html>