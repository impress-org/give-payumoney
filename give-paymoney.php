<?php
/**
 * Plugin Name: Give - PayUmoney
 * Plugin URI: https://github.com/impress-org/give-payumoney
 * Description: Process online donations via the PayUmoney payment gateway.
 * Author: GiveWP
 * Author URI: https://givewp.com
 * Version: 1.0.6
 * Text Domain: give-payumoney
 * Domain Path: /languages
 * GitHub Plugin URI: https://github.com/impress-org/give-payumoney
 */


if ( ! class_exists( 'Give_Payumoney_Gateway' ) ) {
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
		 * Notices (array)
		 *
		 * @since 1.0
		 *
		 * @var array
		 */
		public $notices = array();

		/**
		 * Get instance
		 *
		 * @since  1.0
		 * @access static
		 * @return Give_Payumoney_Gateway|static
		 */
		static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
				self::$instance->setup();
			}

			return self::$instance;
		}

		/**
		 * Setup Give PayUmoney.
		 *
		 * @since  1.0.0
		 * @access private
		 */
		private function setup() {

			// Setup constants.
			$this->setup_constants();

			// Give init hook.
			add_action( 'give_init', array( $this, 'init' ), 10 );
			add_action( 'admin_init', array( $this, 'check_environment' ), 999 );
			add_action( 'admin_notices', array( $this, 'admin_notices' ), 15 );
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
			define( 'GIVE_PAYU_VERSION', '1.0.6' );
			define( 'GIVE_PAYU_MIN_GIVE_VER', '2.7.0' );
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
		public function init() {

			if ( ! $this->get_environment_warning() ) {
				return;
			}

			$this->load_textdomain();
			$this->licensing();
			$this->activation_banner();

			require_once GIVE_PAYU_DIR . 'includes/admin/plugin-activation.php';

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
		 * Load scripts.
		 *
		 * @since  1.0
		 * @access public
		 */
		function enqueue_scripts( $hook ) {
			if (
				'gateways' === give_get_current_setting_tab()
				&& 'payumoney' === give_get_current_setting_section()
			) {
				wp_register_script( 'payumoney-admin-settings', GIVE_PAYU_URL . 'assets/js/admin/admin-settings.js', array( 'jquery' ) );
				wp_enqueue_script( 'payumoney-admin-settings' );
			}
		}

		/**
		 * Load the text domain.
		 *
		 * @access private
		 * @since  1.0
		 *
		 * @return void
		 */
		public function load_textdomain() {

			// Set filter for plugin's languages directory.
			$give_payumoney_lang_dir = dirname( plugin_basename( __FILE__ ) ) . '/languages/';
			$give_payumoney_lang_dir = apply_filters( 'give_payumoney_languages_directory', $give_payumoney_lang_dir );

			// Traditional WordPress plugin locale filter
			$locale = apply_filters( 'plugin_locale', get_locale(), 'give-payumoney' );
			$mofile = sprintf( '%1$s-%2$s.mo', 'give-payumoney', $locale );

			// Setup paths to current locale file
			$mofile_local  = $give_payumoney_lang_dir . $mofile;
			$mofile_global = WP_LANG_DIR . '/give-payumoney/' . $mofile;

			if ( file_exists( $mofile_global ) ) {
				// Look in global /wp-content/languages/give-payumoney folder
				load_textdomain( 'give-payumoney', $mofile_global );
			} elseif ( file_exists( $mofile_local ) ) {
				// Look in local /wp-content/plugins/give-payumoney/languages/ folder
				load_textdomain( 'give-payumoney', $mofile_local );
			} else {
				// Load the default language files
				load_plugin_textdomain( 'give-payumoney', false, $give_payumoney_lang_dir );
			}

		}

		/**
		 * Implement Give Licensing for Give PayUmoney Add On.
		 *
		 * @since  1.0.2
		 * @access private
		 */
		private function licensing() {
			if ( class_exists( 'Give_License' ) ) {
				new Give_License( __FILE__, 'PayUmoney Gateway', GIVE_PAYU_VERSION, 'WordImpress' );
			}
		}

		/**
		 * Check plugin environment.
		 *
		 * @since  1.0.0
		 * @access public
		 *
		 * @return bool
		 */
		public function check_environment() {
			// Flag to check whether plugin file is loaded or not.
			$is_working = true;

			// Load plugin helper functions.
			if ( ! function_exists( 'is_plugin_active' ) ) {
				require_once ABSPATH . '/wp-admin/includes/plugin.php';
			}

			/*
			 Check to see if Give is activated, if it isn't deactivate and show a banner. */
			// Check for if give plugin activate or not.
			$is_give_active = defined( 'GIVE_PLUGIN_BASENAME' ) ? is_plugin_active( GIVE_PLUGIN_BASENAME ) : false;

			if ( empty( $is_give_active ) ) {
				// Show admin notice.
				$this->add_admin_notice( 'prompt_give_activate', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%s" target="_blank">Give</a> plugin installed and activated for Give - PayUmoney to activate.', 'give-payumoney' ), 'https://givewp.com' ) );
				$is_working = false;
			}

			return $is_working;
		}

		/**
		 * Check plugin for Give environment.
		 *
		 * @since  1.1.2
		 * @access public
		 *
		 * @return bool
		 */
		public function get_environment_warning() {
			// Flag to check whether plugin file is loaded or not.
			$is_working = true;

			// Verify dependency cases.
			if (
				defined( 'GIVE_VERSION' )
				&& version_compare( GIVE_VERSION, GIVE_PAYU_MIN_GIVE_VER, '<' )
			) {

				/*
				 Min. Give. plugin version. */
				// Show admin notice.
				$this->add_admin_notice( 'prompt_give_incompatible', 'error', sprintf( __( '<strong>Activation Error:</strong> You must have the <a href="%1$s" target="_blank">Give</a> core version %2$s for the Give - PayUmoney add-on to activate.', 'give-payumoney' ), 'https://givewp.com', GIVE_PAYU_MIN_GIVE_VER ) );

				$is_working = false;
			}

			return $is_working;
		}

		/**
		 * Allow this class and other classes to add notices.
		 *
		 * @since 1.0
		 *
		 * @param $slug
		 * @param $class
		 * @param $message
		 */
		public function add_admin_notice( $slug, $class, $message ) {
			$this->notices[ $slug ] = array(
				'class'   => $class,
				'message' => $message,
			);
		}

		/**
		 * Display admin notices.
		 *
		 * @since 1.0
		 */
		public function admin_notices() {

			$allowed_tags = array(
				'a'      => array(
					'href'  => array(),
					'title' => array(),
					'class' => array(),
					'id'    => array(),
				),
				'br'     => array(),
				'em'     => array(),
				'span'   => array(
					'class' => array(),
				),
				'strong' => array(),
			);

			foreach ( (array) $this->notices as $notice_key => $notice ) {
				echo "<div class='" . esc_attr( $notice['class'] ) . "'><p>";
				echo wp_kses( $notice['message'], $allowed_tags );
				echo '</p></div>';
			}

		}

		/**
		 * Show activation banner for this add-on.
		 *
		 * @since 1.0
		 */
		public function activation_banner() {

			// Check for activation banner inclusion.
			if (
				! class_exists( 'Give_Addon_Activation_Banner' )
				&& file_exists( GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php' )
			) {
				include GIVE_PLUGIN_DIR . 'includes/admin/class-addon-activation-banner.php';
			}

			// Initialize activation welcome banner.
			if ( class_exists( 'Give_Addon_Activation_Banner' ) ) {

				// Only runs on admin.
				$args = array(
					'file'              => __FILE__,
					'name'              => esc_html__( 'PayUmoney Gateway', 'give-payumoney' ),
					'version'           => GIVE_PAYU_VERSION,
					'settings_url'      => admin_url( 'edit.php?post_type=give_forms&page=give-settings&tab=gateways&section=payumoney' ),
					'documentation_url' => 'http://docs.givewp.com/addon-payumoney',
					'support_url'       => 'https://givewp.com/support/',
					'testing'           => false, // Never leave true.
				);
				new Give_Addon_Activation_Banner( $args );
			}
		}
	}

	function Give_Payumoney_Gateway() {
		return Give_Payumoney_Gateway::get_instance();
	}

	/**
	 * Returns class object instance.
	 *
	 * @since 1.3
	 *
	 * @return Give_Payumoney_Gateway bool|object
	 */
	Give_Payumoney_Gateway();
}
