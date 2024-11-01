<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'UFAE_Review_Form' ) ) {

	/**
	 * UFAE_Review_Form class
	 */
	class UFAE_Review_Form {

		/**
		 * The single instance of the class.
		 *
		 * @var UFAE_Review_Form
		 */
		private static $instance;

		/**
		 * Returns the single instance of the class.
		 *
		 * @return UFAE_Review_Form
		 */
		public static function instance() {
			if ( ! isset( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * UFAE_Review_Form constructor.
		 */
		public function __construct() {
			add_action( 'admin_notices', array( $this, 'print_admin_notice' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
			add_action( 'wp_ajax_ufae_review_dismiss', array( $this, 'ufae_review_dismiss' ) );
		}

		/**
		 * Prints the admin notice.
		 */
		public function print_admin_notice() {
			printf(
				'<div class="notice notice-info is-dismissible ufae-review-notice" style="padding: 1rem;">
			<h2 style="margin: 0px">%s</h2>
			<p>%s</p>
			<div class="ufae-review-notice-buttons">
				<a href="' . esc_url( 'https://wordpress.org/support/plugin/ultimate-flipbox-addon-for-elementor/reviews/?filter=5#new-post' ) . '" class="ufae-review-notice-button button" target="_blank" >%s</a>
				<button type="button" class="ufae-review-notice-button button">%s</button>
			</div>
		</div>',
				__( 'Thank you for using Ultimate Flipbox Addon for Elementor.', 'ultimate-flipbox-addon-for-elementor' ),
				__( 'Enjoying the Ultimate Flipbox Addon for Elementor? Your feedback is invaluable in shaping the plugin\'s future.<br>Please consider leaving a review on the WordPress Plugin Directory to help others and support our growth.', 'ultimate-flipbox-addon-for-elementor' ),
				__( 'Leave a Review', 'ultimate-flipbox-addon-for-elementor' ),
				__( 'Already Review.', 'ultimate-flipbox-addon-for-elementor' )
			);
		}

		/**
		 * Enqueues admin scripts and styles.
		 */
		public function enqueue_admin_scripts() {
			wp_enqueue_script( 'ufae-review-script', esc_url( UFAE_URL . 'assets/js/ufae-admin-script.min.js' ), array( 'jquery' ), esc_attr( UFAE_VERSION ), true );
			wp_enqueue_style( 'ufae-review-style', esc_url( UFAE_URL . 'assets/css/ufae-admin-style.min.css' ), array(), esc_attr( UFAE_VERSION ) );

			wp_localize_script(
				'ufae-review-script',
				'ufae_review_obj',
				array(
					'ajax_url' => esc_url( admin_url( 'admin-ajax.php' ) ),
					'nonce'    => esc_attr( wp_create_nonce( 'ufae-review-nonce' ) ),
				)
			);
		}

		/**
		 * Dismisses the review notice.
		 */
		public function ufae_review_dismiss() {
			check_ajax_referer( 'ufae-review-nonce', 'nonce' );

			if ( isset( $_POST['ufae_review_dismiss'] ) ) {
					update_option( 'ufae-already-reviewd', true );
			}

			exit;
		}
	}
}
