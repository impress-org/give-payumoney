<?php
/**
 * Plugin Name: Give - PayUmoney
 * Plugin URI: https://github.com/WordImpress/payumoney
 * Description: Process online donations via the PayUmoney payment gateway.
 * Author: WordImpress
 * Author URI: https://wordimpress.com
 * Version: 1.0
 * Text Domain: give-payumoney
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/WordImpress/payumoney
 */


/**
 * Class Give_Payumoney_Gateway
 *
 * @since 1.0
 */
final class Give_Payumoney_Gateway {

	/**
	 * @since  1.0
	 * @access static
	 * @var Give_Payumoney_Gateway $instance
	 */
	static private $instance;

	/**
	 * Singleton pattern.
	 *
	 * Give_Payumoney_Gateway constructor.
	 */
	private function __construct() {
	}


	/**
	 * Get instance
	 *
	 * @since  1.0
	 * @access static
	 * @return Give_Payumoney_Gateway|static
	 */
	static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new static();
		}

		return self::$instance;
	}


	/**
	 * Setup constants.
	 *
	 * @since  1.0
	 * @access public
	 * @return Give_Payumoney_Gateway
	 */
	public function setup_constants() {
		// Global Params.
		define( 'GIVE_PAYU_VERSION', '1.0' );
		define( 'GIVE_PAYU_MIN_GIVE_VER', '1.8.3' );
		define( 'GIVE_PAYU_BASENAME', plugin_basename( __FILE__ ) );
		define( 'GIVE_PAYU_URL', plugins_url( '/', __FILE__ ) );
		define( 'GIVE_PAYU_DIR', plugin_dir_path( __FILE__ ) );

		return self::$instance;
	}

	/**
	 * Load files.
	 *
	 * @since  1.0
	 * @access public
	 * @return Give_Payumoney_Gateway
	 */
	public function load_files() {
		// Load helper functions.
		require_once GIVE_PAYU_DIR . 'includes/functions.php';

		// Load plugin settings.
		require_once GIVE_PAYU_DIR . 'includes/admin/admin-settings.php';

		// Process payments.
		require_once GIVE_PAYU_DIR . 'includes/payment-processing.php';

		require_once GIVE_PAYU_DIR . 'includes/lib/class-give-payumoney-api.php';

		require_once GIVE_PAYU_DIR . 'includes/filters.php';

		require_once GIVE_PAYU_DIR . 'includes/actions.php';

		if ( is_admin() ) {
			// Add actions.
			require_once GIVE_PAYU_DIR . 'includes/admin/actions.php';
		}

		return self::$instance;
	}

	/**
	 * Setup hooks.
	 *
	 * @since  1.0
	 * @access public
	 * @return Give_Payumoney_Gateway
	 */
	public function setup_hooks() {
		// Load scripts and style.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		return self::$instance;
	}


	/**
	 * Load scripts.
	 *
	 * @since  1.0
	 * @access public
	 */
	function enqueue_scripts( $hook ) {
		if ( 'gateways' === give_get_current_setting_tab() && 'payumoney' === give_get_current_setting_section() ) {
			wp_enqueue_script( 'payumoney-admin-settings', GIVE_PAYU_URL . 'assets/js/admin/admin-settings.js', array( 'jquery' ) );
		}
	}

	/**
	 * Check if plugin dependencies satisfied or not
	 *
	 * @since 1.0
	 * @access public
	 * @return bool
	 */
	public function is_plugin_dependency_satisfied() {
		return ( -1 !== version_compare( GIVE_VERSION, GIVE_PAYU_MIN_GIVE_VER ) );
	}
}

// Initiate plugin.
function give_payu_plugin_init() {
	// Get instance.
	$give_payu = Give_Payumoney_Gateway::get_instance();

	// Load constants.
	$give_payu->setup_constants();

	// Process plugin activation.
	require_once GIVE_PAYU_DIR . 'includes/admin/plugin-activation.php';

	if (
		class_exists( 'Give' )
		&& $give_payu->is_plugin_dependency_satisfied()
	) {
		$give_payu->load_files()->setup_hooks();
	}
}

add_action( 'plugins_loaded', 'give_payu_plugin_init' );
