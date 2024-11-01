<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Ufae_Ajax_Handler' ) ) {
	/**
	 * Class Ufae_Ajax_Handler
	 *
	 * Handles AJAX requests for the UFAE plugin.
	 */
	class Ufae_Ajax_Handler {

		/**
		 * The single instance of the class.
		 *
		 * @var Ufae_Ajax_Handler
		 */
		private static $instance;

		/**
		 * Initializes the class and returns an instance.
		 *
		 * @return Ufae_Ajax_Handler
		 */
		public static function init() {
			if ( null === self::$instance ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Ufae_Ajax_Handler constructor.
		 */
		public function __construct() {
			add_action( 'wp_ajax_ufae_preset_styles', array( $this, 'ufae_preset_styles' ) );
		}

		/**
		 * Handles the AJAX request for preset styles.
		 *
		 * Validates the nonce and retrieves the JSON data.
		 * Sends a JSON response back to the client.
		 */
		public function ufae_preset_styles() {
			if ( ! check_ajax_referer( 'ufae_preset_nonce', 'nonce', false ) ) {
				wp_send_json_error( 'Invalid nonce provided.' );
				wp_die();
			}
			$json_file_path = UFAE_URL . 'admin/controls/assets/js/ufae-controls-style.json';

			$json_data = wp_remote_retrieve_body( wp_remote_get( $json_file_path ) );
			$data      = json_decode( $json_data, true );

			if ( json_last_error() !== JSON_ERROR_NONE ) {
				wp_send_json_error( 'Error decoding JSON data: ' . json_last_error_msg() );
				wp_die();
			}

			wp_send_json_success( $data );
		}
	}
}
