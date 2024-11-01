<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Ufae_Register' ) ) {
	/**
	 * Class Ufae_Register
	 *
	 * This class is responsible for registering the UFAE widget with Elementor.
	 * It follows the singleton pattern to ensure only one instance of the class exists.
	 */
	class Ufae_Register {

		/**
		 * The single instance of the class.
		 *
		 * @var Ufae_Register|null
		 */
		private static $instance = null;

		/**
		 * Initializes the singleton instance of the class.
		 *
		 * @return Ufae_Register The single instance of the class.
		 */
		public static function init() {
			if ( self::$instance === null ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Ufae_Register constructor.
		 * Registers the widget and category with Elementor.
		 */
		public function __construct() {
			add_action( 'elementor/widgets/register', array( $this, 'ufae_register_widget' ) );
			add_action( 'elementor/init', array( $this, 'ufae_register_category' ) );
			add_action( 'elementor/controls/register', array( $this, 'ufae_register_custom_controller' ) );
		}

		/**
		 * Registers the custom category for the UFAE widget in Elementor.
		 *
		 * @return void
		 */
		public function ufae_register_category() {
			\Elementor\Plugin::$instance->elements_manager->add_category(
				'ufae',              // The name of the category.
				array(
					'title' => esc_html__( 'Ultimate Flipbox', 'ultimate-flipbox-addon-for-elementor' ),
					'icon'  => 'fa fa-header', // Default icon.
				),
				1 // Position.
			);
		}

		/**
		 * Registers a custom controller for the UFAE widget.
		 *
		 * @param \Elementor\Controls_Manager $controls_manager The controls manager instance.
		 * @return void
		 */
		public function ufae_register_custom_controller( $controls_manager ) {
			require_once UFAE_DIR . 'admin/controls/class-ufae-control-preset.php';
			$controls_manager->register( new Ufae_Control_Preset() );
		}

		/**
		 * Registers the UFAE widget with Elementor.
		 *
		 * @return void
		 */
		public function ufae_register_widget() {
			require_once UFAE_DIR . 'widget/class-ufae-widget.php';
			\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new Ufae_Widget() );
		}
	}
}
