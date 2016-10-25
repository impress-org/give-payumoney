<?php
/**
 * Plugin Name: Give Payumoney
 * Plugin URI: http://givewp.com
 * Description: The most robust, flexible, and intuitive way to accept donations on WordPress with Give plugin by payumoney payment gateway.
 * Author: WordImpress
 * Author URI: https://wordimpress.com
 * Version: 1.0
 * Text Domain: give-payumoney
 * Domain Path: /languages
 * GitHub Plugin URI:
 *
 * Give payumoney is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * any later version.
 *
 * Give payumoney is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Give payumoney. If not, see <https://www.gnu.org/licenses/>.
 *
 * A Tribute to Open Source:
 *
 * "Open source software is software that can be freely used, changed, and shared (in modified or unmodified form) by anyone. Open
 * source software is made by many people, and distributed under licenses that comply with the Open Source Definition."
 *
 * -- The Open Source Initiative
 *
 * Give payumoney is a tribute to the spirit and philosophy of Open Source. We at WordImpress gladly embrace the Open Source philosophy both
 * in how Give payumoney itself was developed, and how we hope to see others build more from our code base.
 *
 * Give payumoney would not have been possible without the tireless efforts of WordPress and the surrounding Open Source projects and their talented developers. Thank you all for your contribution to WordPress.
 *
 * - The WordImpress Team
 *
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
		define( 'GIVE_PAYU_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
		define( 'GIVE_PAYU_URL', trailingslashit( plugins_url( '', __FILE__ ) ) );

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
}

// Initiate plugin.
function give_payu_plugin_init() {
	if( class_exists( 'Give' ) ) {

		Give_Payumoney_Gateway::get_instance()
		                      ->setup_constants()
		                      ->load_files()
		                      ->setup_hooks();

	}
}
add_action( 'plugins_loaded', 'give_payu_plugin_init' );