<?php
/*
* Plugin Name: Ultimate Flipbox Addon for Elementor
* Plugin URI: https://github.com/Aniketji07/ultimate-flipbox-addon-for-elementor
* Description: Ultimate Flipbox Addon for Elementor: Create interactive flipboxes with 5 animation styles, 4 preset styles, and customizable vertical grid and horizontal carousel layouts.
* Version: 1.0.3
* Author: aniketji007
* Author URI: https://github.com/aniketji007/
* Text Domain: ultimate-flipbox-addon-for-elementor
* License: GPL2
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// Define constants only if they aren't already defined.
defined( 'UFAE_FILE' ) || define( 'UFAE_FILE', __FILE__ );
defined( 'UFAE_VERSION' ) || define( 'UFAE_VERSION', '1.0.3' );
defined( 'UFAE_DIR' ) || define( 'UFAE_DIR', plugin_dir_path( UFAE_FILE ) );
defined( 'UFAE_URL' ) || define( 'UFAE_URL', plugin_dir_url( UFAE_FILE ) );

// Ensure the class doesn't already exist.
if ( ! class_exists( 'Ultimate_Flipbox_Addon_For_Elementor' ) ) {
	/**
	 * Ultimate_Flipbox_Addon_For_Elementor class handles the initialization and functionality of the Ultimate Flipbox Addon for Elementor plugin.
	 * It utilizes a singleton pattern for instantiation and registers hooks for plugin activation, deactivation, and file inclusion.
	 */
	class Ultimate_Flipbox_Addon_For_Elementor {

		/**
		 * The single instance of the Ultimate_Flipbox_Addon_For_Elementor class.
		 *
		 * @var Ultimate_Flipbox_Addon_For_Elementor|null The single instance of the class.
		 */
		private static $instance = null;

		/**
		 * Singleton pattern for class instantiation.
		 * Ensures only one instance of the class is created.
		 *
		 * @return Ultimate_Flipbox_Addon_For_Elementor The single instance of the class.
		 */
		public static function init() {
			if ( self::$instance === null ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Constructor registers activation and deactivation hooks for the plugin.
		 * It also hooks into the 'plugins_loaded' action to include necessary files.
		 */
		public function __construct() {
			$this->ufae_review();
			register_activation_hook( UFAE_FILE, array( $this, 'plugin_activated' ) );
			register_deactivation_hook( UFAE_FILE, array( $this, 'plugin_deactivated' ) );
			add_action( 'plugins_loaded', array( $this, 'includes_files' ) );
			add_action( 'admin_init', array( $this, 'ufae_required_plugins' ) );
			add_action( 'init', array( $this, 'ufafe_load_textdomain' ) );
		}

		/**
		 * Initializes the review form functionality.
		 */
		public function ufae_review(){
			if ( is_admin() ) {
				$already_rated = get_option( 'ufae-already-reviewd', false );
				if ( ! $already_rated ) {
					require_once UFAE_DIR . '/admin/review/class-ufae-review-form.php';
					UFAE_Review_Form::instance();
				}
			}
		}

		/**
		 * Checks if required plugins are active and displays an admin notice if not.
		 *
		 * This method verifies if the Elementor plugin is active. If it is not,
		 * an admin notice is displayed to inform the user that the Ultimate Flipbox
		 * Addon for Elementor requires Elementor to function properly.
		 *
		 * @return void
		 */
		public function ufae_required_plugins() {
			if ( ! is_plugin_active( 'elementor/elementor.php' ) ) {
				add_action(
					'admin_notices',
					function () {
						echo '<div class="notice notice-error is-dismissible"><p>Ultimate Flipbox Addon for Elementor requires Elementor to be installed and active. Please <a href="' . esc_url( 'https://wordpress.org/plugins/elementor/' ) . '" target="_blank">install Elementor</a> to use this plugin.</p></div>';
					}
				);
			}
		}

		/**
		 * Loads the translation files for the plugin.
		 * This function is hooked into the 'init' action to ensure the text domain is loaded early.
		 */
		public function ufafe_load_textdomain() {
			load_plugin_textdomain( 'ultimate-flipbox-addon-for-elementor', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		}

		/**
		 * Includes necessary files for the plugin based on the current context.
		 */
		public function includes_files() {
			// Include the class for registering plugin functionality
			include_once UFAE_DIR . 'includes/class-ufae-register.php';
			if(is_admin()){
				// Include the class for handling AJAX requests in the admin area
				include_once UFAE_DIR . 'includes/class-ufae-ajax-handler.php';
				Ufae_Ajax_Handler::init();

				// Include the class for handling feedback form data in the admin area
				include_once UFAE_DIR . 'admin/feedback/class-ufae-feedback-form.php';
				Ufae_Feedback_Form::get_instance();
			}
			// Initialize the plugin registration
			Ufae_Register::init();
		}

		/**
		 * Placeholder for activation logic.
		 * This method is called when the plugin is activated.
		 * It updates options for installation date and plugin version.
		 */
		public function plugin_activated() {
			// Installation data
			update_option( 'ufafe_installation_date', gmdate( 'Y-m-d H:i:s' ) );
			// Plugin version
			update_option( 'ufafe_version', UFAE_VERSION );
		}

		/**
		 * Placeholder for deactivation logic.
		 * This method is called when the plugin is deactivated.
		 * It can be used to perform cleanup or other deactivation tasks.
		 */
		public function plugin_deactivated() {
		}
	}

	// Initialize the plugin.
	$UFAE = Ultimate_Flipbox_Addon_For_Elementor::init();
}
