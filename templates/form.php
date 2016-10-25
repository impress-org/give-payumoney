<?php
// Exit if access directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html lang="en">
	<head>
		<title>Process Donation with CCAvenue payment gateways</title>
	</head>
	<body>
		<!-- Request -->
		<?php echo Give_Payumoney_API::get_form(); ?>

		<script language='javascript'>document.payuForm.submit();</script>
	</body>
</html>