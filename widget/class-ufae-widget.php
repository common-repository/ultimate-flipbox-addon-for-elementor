<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

use Elementor\Widget_Base;
use Ultimate_Flipbox_Addon_For_Elementor\Ufae_Frontend_Output;
use Ultimate_Flipbox_Addon_For_Elementor\Ufae_Editor_Output;

if ( ! class_exists( 'Ufae_Widget' ) ) {
	/**
	 * Class Ufae_Register_Widget
	 *
	 * This class is responsible for registering the UFAE widget with Elementor.
	 * It extends the Elementor\Widget_Base class and defines the widget's properties,
	 * controls, and rendering methods.
	 */
	class Ufae_Widget extends \Elementor\Widget_Base {


		private $common_selector = '.ufae-wrapper';
		/**
		 * Ufae_Widget constructor.
		 *
		 * This method initializes the widget, registers styles and scripts
		 * based on the current environment (minified or not) and user login status.
		 *
		 * @param array $data Widget data.
		 * @param null  $args Widget arguments.
		 */
		public function __construct( $data = array(), $args = null ) {
			parent::__construct( $data, $args );

			$ext = ( true ) ? '.min' : ''; // Use minified CSS if $min_v is true

			$js_common_dep = array( 'elementor-frontend' );

			// Check if the user is logged in and not in preview mode
			if ( ! \Elementor\Plugin::$instance->preview->is_preview_mode() && is_user_logged_in() ) {
				$js_common_dep = array( 'elementor-common', 'elementor-frontend' );
			}

			// Register styles
			wp_register_style( 'ufae-common-style', UFAE_URL . 'assets/css/ufae-common' . $ext . '.css', array(), UFAE_VERSION, 'all' );
			wp_register_style( 'ufae-vertical-style', UFAE_URL . 'assets/css/ufae-vertical' . $ext . '.css', array(), UFAE_VERSION, 'all' );
			wp_register_style( 'ufae-horizontal-style', UFAE_URL . 'assets/css/ufae-horizontal' . $ext . '.css', array(), UFAE_VERSION, 'all' );
			// Widget editor styles.
			wp_register_style( 'ufae-widget-editor', UFAE_URL . 'assets/css/ufae-widget-editor' . $ext . '.css', array(), UFAE_VERSION, 'all' );

			// Register scripts
			wp_register_script( 'ufae-common-script', UFAE_URL . 'assets/js/ufae-common' . $ext . '.js', $js_common_dep, UFAE_VERSION, true );
			wp_register_script( 'ufae-horizontal-script', UFAE_URL . 'assets/js/ufae-horizontal' . $ext . '.js', $js_common_dep, UFAE_VERSION, true );
		}

		/**
		 * Get the script dependencies for the widget.
		 *
		 * This method checks if the Elementor editor is in edit or preview mode
		 * and returns the appropriate scripts based on the layout setting.
		 *
		 * @return array List of script handles to be enqueued.
		 */
		public function get_script_depends() {
			$scripts = array( 'ufae-common-script' );

			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
				return array_merge( $scripts, array( 'ufae-horizontal-script' ) );
			}

			$settings = $this->get_settings_for_display();
			$layout   = $settings['ufae_layout_option'];

			if ( $layout === 'horizontal' ) {
				$scripts[] = 'ufae-horizontal-script';
			}

			return $scripts;
		}

		/**
		 * Get the style dependencies for the widget.
		 *
		 * This method checks if the Elementor editor is in edit or preview mode
		 * and returns the appropriate styles based on the layout setting.
		 *
		 * @return array List of style handles to be enqueued.
		 */
		public function get_style_depends() {
			$styles = array( 'ufae-common-style' );

			if ( \Elementor\Plugin::$instance->editor->is_edit_mode() || \Elementor\Plugin::$instance->preview->is_preview_mode() ) {
				return array_merge( $styles, array( 'ufae-horizontal-style', 'ufae-vertical-style', 'ufae-widget-editor' ) );
			}

			$settings = $this->get_settings_for_display();
			$layout   = $settings['ufae_layout_option'];

			if ( $layout === 'horizontal' ) {
				$styles[] = 'ufae-horizontal-style';
			} else {
				$styles[] = 'ufae-vertical-style';
			}
			return $styles;
		}

		/**
		 * Get widget name.
		 *
		 * @return string Widget name.
		 */
		public function get_name() {
			return 'ufae_flipbox_widget';
		}

		/**
		 * Get widget title.
		 *
		 * @return string Widget title.
		 */
		public function get_title() {
			return esc_html__( 'Ultimate Flipbox', 'ultimate-flipbox-addon-for-elementor' );
		}

		/**
		 * Get widget icon.
		 *
		 * @return string Widget icon.
		 */
		public function get_icon() {
			return 'eicon-flip-box'; // Elementor icon
		}

		/**
		 * Get categories for the widget.
		 *
		 * @return array Widget categories.
		 */
		public function get_categories() {
			return array( 'ufae' ); // Change to your desired category
		}

		/**
		 * Register widget controls.
		 *
		 * This method is used to define the controls for the widget in the Elementor editor.
		 */
		protected function _register_controls() {
			$this->ufae_content_controls();
			$this->ufae_style_controls();
		}

		/**
		 * Register content controls for the widget.
		 *
		 * This method is used to define the content-related controls for the widget in the Elementor editor.
		 */
		protected function ufae_content_controls() {

			$this->start_controls_section(
				'ufae_content_section',
				array(
					'label' => esc_html__( 'Flipbox Items', 'ultimate-flipbox-addon-for-elementor' ),
					'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
				)
			);

			// Flipbox Repeater
			$flipbox_repeater = new \Elementor\Repeater();

			// Flipbox Tabs - START
			$flipbox_repeater->start_controls_tabs(
				'ufae_flipbox_items'
			);

			// Fipbox Front Tab - START
			$flipbox_repeater->start_controls_tab(
				'ufae_front_content_tab',
				array(
					'label' => esc_html__( 'Front', 'ultimate-flipbox-addon-for-elementor' ),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_front_icon_type',
				array(
					'label'   => esc_html__( 'Icon Type', 'ultimate-flipbox-addon-for-elementor' ),
					'type'    => \Elementor\Controls_Manager::CHOOSE,
					'options' => array(
						'icon'  => array(
							'title' => esc_html__( 'Icon', 'ultimate-flipbox-addon-for-elementor' ),
							'icon'  => 'fab fa-font-awesome',
						),
						'image' => array(
							'title' => esc_html__( 'Icon Image', 'ultimate-flipbox-addon-for-elementor' ),
							'icon'  => 'fa fa-image',
						),
						'text'  => array(
							'title' => esc_html__( 'Icon Text', 'ultimate-flipbox-addon-for-elementor' ),
							'icon'  => 'fa fa-list-ol',
						),
					),
					'default' => 'icon',
				)
			);

			$flipbox_repeater->add_control(
				'ufae_front_icon',
				array(
					'label'     => esc_html__( 'Icon', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::ICONS,
					'default'   => array(
						'value'   => 'fas fa-music',
						'library' => 'fa-solid',
					),
					'condition' => array(
						'ufae_front_icon_type' => 'icon',
					),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_front_icon_image',
				array(
					'label'     => esc_html__( 'Icon Image', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::MEDIA,
					'default'   => array(
						'url' => '',
					),
					'condition' => array(
						'ufae_front_icon_type' => 'image',
					),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_front_icon_text',
				array(
					'label'       => esc_html__( 'Icon Text', 'ultimate-flipbox-addon-for-elementor' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'default'     => esc_html__( 'Default Text', 'ultimate-flipbox-addon-for-elementor' ),
					'label_block' => true,
					'condition'   => array(
						'ufae_front_icon_type' => 'text',
					),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_front_title',
				array(
					'label'       => esc_html__( 'Title', 'ultimate-flipbox-addon-for-elementor' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'default'     => esc_html__( 'Default Title', 'ultimate-flipbox-addon-for-elementor' ),
					'label_block' => true,
				)
			);

			$flipbox_repeater->add_control(
				'ufae_front_description',
				array(
					'label'       => esc_html__( 'Description', 'ultimate-flipbox-addon-for-elementor' ),
					'type'        => \Elementor\Controls_Manager::TEXTAREA,
					'default'     => esc_html__( 'Default description text.', 'ultimate-flipbox-addon-for-elementor' ),
					'label_block' => true,
				)
			);

			$flipbox_repeater->add_control(
				'ufae_front_button_enable',
				array(
					'label'        => esc_html__( 'Enable Button', 'ultimate-flipbox-addon-for-elementor' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'ultimate-flipbox-addon-for-elementor' ),
					'label_off'    => esc_html__( 'No', 'ultimate-flipbox-addon-for-elementor' ),
					'return_value' => 'yes',
					'default'      => 'no',
				)
			);

			$flipbox_repeater->add_control(
				'ufae_front_button_text',
				array(
					'label'       => esc_html__( 'Button Text', 'ultimate-flipbox-addon-for-elementor' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'default'     => esc_html__( 'Click Here', 'ultimate-flipbox-addon-for-elementor' ),
					'label_block' => true,
					'condition'   => array(
						'ufae_front_button_enable' => 'yes',
					),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_front_button_url',
				array(
					'label'       => esc_html__( 'Button URL', 'ultimate-flipbox-addon-for-elementor' ),
					'type'        => \Elementor\Controls_Manager::URL,
					'default'     => array(
						'url'         => '#',
						'is_external' => false,
						'nofollow'    => false,
					),
					'label_block' => true,
					'condition'   => array(
						'ufae_front_button_enable' => 'yes',
						'ufae_back_enable!'        => 'yes',
					),
				)
			);

			$flipbox_repeater->end_controls_tab();
			// Fipbox Front Tab - START

			// Fipbox Back Tab - START
			$flipbox_repeater->start_controls_tab(
				'ufae_back_content_tab',
				array(
					'label' => esc_html__( 'Back', 'ultimate-flipbox-addon-for-elementor' ),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_back_enable',
				array(
					'label'        => esc_html__( 'Enable Back', 'ultimate-flipbox-addon-for-elementor' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'ultimate-flipbox-addon-for-elementor' ),
					'label_off'    => esc_html__( 'No', 'ultimate-flipbox-addon-for-elementor' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$flipbox_repeater->add_control(
				'ufae_back_icon_type',
				array(
					'label'     => esc_html__( 'Icon Type', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::CHOOSE,
					'options'   => array(
						'icon'  => array(
							'title' => esc_html__( 'Icon', 'ultimate-flipbox-addon-for-elementor' ),
							'icon'  => 'fab fa-font-awesome',
						),
						'image' => array(
							'title' => esc_html__( 'Icon Image', 'ultimate-flipbox-addon-for-elementor' ),
							'icon'  => 'fa fa-image',
						),
						'text'  => array(
							'title' => esc_html__( 'Icon Text', 'ultimate-flipbox-addon-for-elementor' ),
							'icon'  => 'fa fa-list-ol',
						),
					),
					'default'   => 'icon',
					'condition' => array(
						'ufae_back_enable' => 'yes',
					),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_back_icon',
				array(
					'label'     => esc_html__( 'Icon', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::ICONS,
					'default'   => array(
						'value'   => 'fas fa-music',
						'library' => 'fa-solid',
					),
					'condition' => array(
						'ufae_back_icon_type' => 'icon',
						'ufae_back_enable'    => 'yes',
					),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_back_icon_image',
				array(
					'label'     => esc_html__( 'Icon Image', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::MEDIA,
					'default'   => array(
						'url' => '',
					),
					'condition' => array(
						'ufae_back_icon_type' => 'image',
						'ufae_back_enable'    => 'yes',
					),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_back_icon_text',
				array(
					'label'       => esc_html__( 'Icon Text', 'ultimate-flipbox-addon-for-elementor' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'default'     => esc_html__( 'Default Text', 'ultimate-flipbox-addon-for-elementor' ),
					'label_block' => true,
					'condition'   => array(
						'ufae_back_icon_type' => 'text',
						'ufae_back_enable'    => 'yes',
					),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_back_title',
				array(
					'label'       => esc_html__( 'Title', 'ultimate-flipbox-addon-for-elementor' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'default'     => esc_html__( 'Default Title', 'ultimate-flipbox-addon-for-elementor' ),
					'label_block' => true,
					'condition'   => array(
						'ufae_back_enable' => 'yes',
					),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_back_description',
				array(
					'label'       => esc_html__( 'Description', 'ultimate-flipbox-addon-for-elementor' ),
					'type'        => \Elementor\Controls_Manager::TEXTAREA,
					'default'     => esc_html__( 'Default description text.', 'ultimate-flipbox-addon-for-elementor' ),
					'label_block' => true,
					'condition'   => array(
						'ufae_back_enable' => 'yes',
					),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_back_button_enable',
				array(
					'label'        => esc_html__( 'Enable Button', 'ultimate-flipbox-addon-for-elementor' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'ultimate-flipbox-addon-for-elementor' ),
					'label_off'    => esc_html__( 'No', 'ultimate-flipbox-addon-for-elementor' ),
					'return_value' => 'yes',
					'default'      => 'yes',
					'condition'    => array(
						'ufae_back_enable' => 'yes',
					),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_back_button_text',
				array(
					'label'       => esc_html__( 'Button Text', 'ultimate-flipbox-addon-for-elementor' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'default'     => esc_html__( 'Click Here', 'ultimate-flipbox-addon-for-elementor' ),
					'label_block' => true,
					'condition'   => array(
						'ufae_back_button_enable' => 'yes',
						'ufae_back_enable'        => 'yes',
					),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_back_button_url',
				array(
					'label'       => esc_html__( 'Button URL', 'ultimate-flipbox-addon-for-elementor' ),
					'type'        => \Elementor\Controls_Manager::URL,
					'default'     => array(
						'url'         => '#',
						'is_external' => false,
						'nofollow'    => false,
					),
					'label_block' => true,
					'condition'   => array(
						'ufae_back_button_enable' => 'yes',
						'ufae_back_enable'        => 'yes',
					),
				)
			);

			$flipbox_repeater->end_controls_tab();
			// Fipbox Back Tab - END

			$flipbox_repeater->start_controls_tab(
				'ufae_style_tab',
				array(
					'label' => esc_html__( 'Style', 'ultimate-flipbox-addon-for-elementor' ),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_item_front_bg_color',
				array(
					'label'     => esc_html__( 'Front Background', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) . ' .ufae-flipbox-item{{CURRENT_ITEM}}' => '--ufae-box-front-bg-color: {{VALUE}}',
					),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_item_front_title_color',
				array(
					'label'     => esc_html__( 'Front Title Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) . ' .ufae-flipbox-item{{CURRENT_ITEM}}' => '--ufae-title-front-color: {{VALUE}}',
					),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_item_front_desc_color',
				array(
					'label'     => esc_html__( 'Front Desc Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) . ' .ufae-flipbox-item{{CURRENT_ITEM}}' => '--ufae-desc-front-color: {{VALUE}}',
					),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_item_front_icon_color',
				array(
					'label'     => esc_html__( 'Front Icon Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) . ' .ufae-flipbox-item{{CURRENT_ITEM}}' => '--ufae-icon-front-color: {{VALUE}}',
					),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_item_front_btn_color',
				array(
					'label'     => esc_html__( 'Front Button Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) . ' .ufae-flipbox-item{{CURRENT_ITEM}}' => '--ufae-btn-front-color: {{VALUE}}',
					),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_item_style_divider',
				array(
					'type'      => \Elementor\Controls_Manager::DIVIDER,
					'condition' => array(
						'ufae_back_enable' => 'yes',
					),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_item_back_bg_color',
				array(
					'label'     => esc_html__( 'Back Background', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) . ' .ufae-flipbox-item{{CURRENT_ITEM}}' => '--ufae-box-back-bg-color: {{VALUE}}',
					),
					'condition' => array(
						'ufae_back_enable' => 'yes',
					),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_item_back_title_color',
				array(
					'label'     => esc_html__( 'Back Title Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) . ' .ufae-flipbox-item{{CURRENT_ITEM}}' => '--ufae-title-back-color: {{VALUE}}',
					),
					'condition' => array(
						'ufae_back_enable' => 'yes',
					),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_item_back_desc_color',
				array(
					'label'     => esc_html__( 'Back Desc Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) . ' .ufae-flipbox-item{{CURRENT_ITEM}}' => '--ufae-desc-back-color: {{VALUE}}',
					),
					'condition' => array(
						'ufae_back_enable' => 'yes',
					),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_item_back_icon_color',
				array(
					'label'     => esc_html__( 'Back Icon Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) . ' .ufae-flipbox-item{{CURRENT_ITEM}}' => '--ufae-icon-back-color: {{VALUE}}',
					),
				)
			);

			$flipbox_repeater->add_control(
				'ufae_item_back_btn_color',
				array(
					'label'     => esc_html__( 'Back Button Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) . ' .ufae-flipbox-item{{CURRENT_ITEM}}' => '--ufae-btn-back-color: {{VALUE}}',
					),
					'condition' => array(
						'ufae_back_enable' => 'yes',
					),
				)
			);

			$flipbox_repeater->end_controls_tab();

			$flipbox_repeater->end_controls_tabs();

			// Story Dummy Content
			$this->add_control(
				'ufae_lists',
				array(
					'label'       => esc_html__( 'Flipbox Item', 'ultimate-flipbox-addon-for-elementor' ),
					'type'        => \Elementor\Controls_Manager::REPEATER,
					'fields'      => $flipbox_repeater->get_controls(),
					'title_field' => '{{{ ufae_front_title }}}',
					'default'     => array(
						array(
							'ufae_front_icon'          => array(
								'value'   => 'fab fa-wordpress',
								'library' => 'fa-brands',
							), // WordPress icon
							'ufae_front_title'         => 'WordPress Basics',
							'ufae_front_description'   => 'Learn the fundamentals of WordPress, from installation to customization.',
							'ufae_front_button_enable' => 'no',
							'ufae_item_front_bg_color' => '#F7CAC9',
							'ufae_back_icon_type'      => '', // Book icon for back
							'ufae_back_title'          => 'Documentation',
							'ufae_back_description'    => 'Access the official WordPress documentation for in-depth guides and tutorials.',
							'ufae_back_button_enable'  => 'yes',
							'ufae_back_button_text'    => 'Read Docs',
							'ufae_back_button_url'     => array( 'url' => '' ),
							'ufae_item_back_bg_color'  => '#ADD8E6',
						),
						array(
							'ufae_front_icon'             => array(
								'value'   => 'fas fa-paint-brush',
								'library' => 'fa-solid',
							), // Design icon
							'ufae_front_title'            => 'Theme Customization',
							'ufae_front_description'      => 'Discover how to customize your WordPress theme to match your brand.',
							'ufae_front_button_enable'    => 'no',
							'ufae_item_front_bg_color'    => '#2F4F4F',
							'ufae_item_front_title_color' => '#fff',
							'ufae_item_front_desc_color'  => '#fff',
							'ufae_item_front_icon_color'  => '#fff',
							'ufae_back_icon_type'         => '', // Settings icon for back
							'ufae_back_title'             => 'Theme Settings',
							'ufae_back_description'       => 'Explore the settings available in your theme for further customization.',
							'ufae_back_button_enable'     => 'yes',
							'ufae_back_button_text'       => 'View Settings',
							'ufae_back_button_url'        => array( 'url' => '' ),
							'ufae_item_back_bg_color'     => '#4E5338',
							'ufae_item_back_title_color'  => '#fff',
							'ufae_item_back_desc_color'   => '#fff',
						),
						array(
							'ufae_front_icon'          => array(
								'value'   => 'fas fa-user',
								'library' => 'fa-solid',
							), // User icon
							'ufae_front_title'         => 'User Management',
							'ufae_front_description'   => 'Learn how to manage users and roles in your WordPress site effectively.',
							'ufae_front_button_enable' => 'no',
							'ufae_back_icon_type'      => '', // Users icon for back
							'ufae_back_title'          => 'User Roles',
							'ufae_back_description'    => 'Understand different user roles and their permissions in WordPress.',
							'ufae_back_button_enable'  => 'yes',
							'ufae_back_button_text'    => 'View Roles',
							'ufae_back_button_url'     => array( 'url' => '' ),
						),
					),
				)
			);

			$this->end_controls_section();

			$this->ufae_content_layout_controls();
		}

		/**
		 * Register style controls for the widget.
		 *
		 * This method is responsible for initializing and registering all style-related controls
		 * for the widget in the Elementor editor. It calls individual methods to set up styles
		 * for various components of the widget, ensuring a modular and organized approach.
		 */
		protected function ufae_style_controls() {
			// Register styles for the container box of the widget
			$this->ufae_container_styles();

			// Register styles for the title of the widget
			$this->ufae_title_styles();

			// Register styles for the description of the widget
			$this->ufae_desc_styles();

			// Register styles for the icon used in the widget
			$this->ufae_icon_styles();

			// Register styles for the button within the widget
			$this->ufae_btn_styles();
		}

		/**
		 * Register container styles for the widget.
		 *
		 * This method is used to define the style-related controls for the container in the Elementor editor.
		 */
		protected function ufae_container_styles() {
			// container box style settings - START
			$this->start_controls_section(
				'ufae_container_style_section',
				array(
					'label' => esc_html__( 'Container Box Settings', 'ultimate-flipbox-addon-for-elementor' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				)
			);

			$this->start_controls_tabs(
				'ufae_flipbox_container_style_tabs'
			);

			// container box front style tab - START
			$this->start_controls_tab(
				'ufae_front_container_tab',
				array(
					'label' => esc_html__( 'Front', 'ultimate-flipbox-addon-for-elementor' ),
				)
			);

			$this->add_control(
				'ufae_container_front_bg_color',
				array(
					'label'     => esc_html__( 'Front Background Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-box-front-bg-color: {{VALUE}}',
					),
					'default'   => '#40D0FF',
				)
			);

			$this->add_control(
				'ufae_container_front_border_popover',
				array(
					'type'         => \Elementor\Controls_Manager::POPOVER_TOGGLE,
					'label'        => esc_html__( 'Front Border', 'ultimate-flipbox-addon-for-elementor' ),
					'label_off'    => esc_html__( 'Default', 'ultimate-flipbox-addon-for-elementor' ),
					'label_on'     => esc_html__( 'Custom', 'ultimate-flipbox-addon-for-elementor' ),
					'return_value' => 'yes',
				)
			);

			$this->start_popover();

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				array(
					'name'      => 'ufae_container_front_border',
					'selector'  => '{{WRAPPER}} ' . esc_html( $this->common_selector ) . ' .ufae-flipbox-front',
					'condition' => array(
						'ufae_container_front_border_popover' => 'yes',
					),
				)
			);

			$this->end_popover();

			$this->add_responsive_control(
				'ufae_front_items_justify',
				array(
					'label'     => esc_html__( 'Content Justify', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::CHOOSE,
					'options'   => array(
						'start'  => array(
							'title' => esc_html__( 'Align Left', 'ultimate-flipbox-addon-for-elementor' ),
							'icon'  => 'fas fa-align-left',
						),
						'center' => array(
							'title' => esc_html__( 'Align Center', 'ultimate-flipbox-addon-for-elementor' ),
							'icon'  => 'fas fa-align-center',
						),
						'end'    => array(
							'title' => esc_html__( 'Align Right', 'ultimate-flipbox-addon-for-elementor' ),
							'icon'  => 'fas fa-align-right',
						),
					),
					'default'   => 'center',
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-front-items-justify: {{VALUE}}',
					),
				)
			);

			$this->add_responsive_control(
				'ufae_front_items_alignment',
				array(
					'label'     => esc_html__( 'Item Alignment', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::CHOOSE,
					'options'   => array(
						'start'  => array(
							'title' => esc_html__( 'Align Top', 'ultimate-flipbox-addon-for-elementor' ),
							'icon'  => 'fas fa-align-left',
						),
						'center' => array(
							'title' => esc_html__( 'Align Middle', 'ultimate-flipbox-addon-for-elementor' ),
							'icon'  => 'fas fa-align-center',
						),
						'end'    => array(
							'title' => esc_html__( 'Align Bottom', 'ultimate-flipbox-addon-for-elementor' ),
							'icon'  => 'fas fa-align-right',
						),
					),
					'default'   => 'center',
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-front-items-alignment: {{VALUE}}',
					),
				)
			);

			$this->add_responsive_control(
				'ufae_container_front_padding',
				array(
					'label'      => esc_html__( 'Front Padding', 'ultimate-flipbox-addon-for-elementor' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'default'    => array(
						'top'    => '2',
						'right'  => '2',
						'bottom' => '2',
						'left'   => '2',
						'unit'   => 'em',
					),
					'selectors'  => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-box-front-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$this->end_controls_tab();
			// container box front style tab - END

			// container box back style tab - START
			$this->start_controls_tab(
				'ufae_back_container_tab',
				array(
					'label' => esc_html__( 'Back', 'ultimate-flipbox-addon-for-elementor' ),
				)
			);

			$this->add_control(
				'ufae_container_back_bg_color',
				array(
					'label'     => esc_html__( 'Back Background Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-box-back-bg-color: {{VALUE}}',
					),
					'default'   => '#408FFF',
				)
			);

			$this->add_control(
				'ufae_container_back_border_popover',
				array(
					'type'         => \Elementor\Controls_Manager::POPOVER_TOGGLE,
					'label'        => esc_html__( 'Back Border', 'ultimate-flipbox-addon-for-elementor' ),
					'label_off'    => esc_html__( 'Default', 'ultimate-flipbox-addon-for-elementor' ),
					'label_on'     => esc_html__( 'Custom', 'ultimate-flipbox-addon-for-elementor' ),
					'return_value' => 'yes',
				)
			);

			$this->start_popover();

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				array(
					'name'      => 'ufae_container_back_border',
					'selector'  => '{{WRAPPER}} ' . esc_html( $this->common_selector ) . ' .ufae-flipbox-back',
					'condition' => array(
						'ufae_container_back_border_popover' => 'yes',
					),
				)
			);

			$this->end_popover();

			$this->add_responsive_control(
				'ufae_back_items_justify',
				array(
					'label'     => esc_html__( 'Content Justify', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::CHOOSE,
					'options'   => array(
						'flex-start' => array(
							'title' => esc_html__( 'Align Left', 'ultimate-flipbox-addon-for-elementor' ),
							'icon'  => 'fas fa-align-left',
						),
						'center'     => array(
							'title' => esc_html__( 'Align Center', 'ultimate-flipbox-addon-for-elementor' ),
							'icon'  => 'fas fa-align-center',
						),
						'flex-end'   => array(
							'title' => esc_html__( 'Align Right', 'ultimate-flipbox-addon-for-elementor' ),
							'icon'  => 'fas fa-align-right',
						),
					),
					'default'   => 'center',
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-back-items-justify: {{VALUE}}',
					),
				)
			);

			$this->add_responsive_control(
				'ufae_back_items_alignment',
				array(
					'label'     => esc_html__( 'Item Alignment', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::CHOOSE,
					'options'   => array(
						'flex-start' => array(
							'title' => esc_html__( 'Align Top', 'ultimate-flipbox-addon-for-elementor' ),
							'icon'  => 'fas fa-align-left',
						),
						'center'     => array(
							'title' => esc_html__( 'Align Middle', 'ultimate-flipbox-addon-for-elementor' ),
							'icon'  => 'fas fa-align-center',
						),
						'flex-end'   => array(
							'title' => esc_html__( 'Align Bottom', 'ultimate-flipbox-addon-for-elementor' ),
							'icon'  => 'fas fa-align-right',
						),
					),
					'default'   => 'center',
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-back-items-alignment: {{VALUE}}',
					),
				)
			);

			$this->add_responsive_control(
				'ufae_container_back_padding',
				array(
					'label'      => esc_html__( 'Back Padding', 'ultimate-flipbox-addon-for-elementor' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'default'    => array(
						'top'    => '2',
						'right'  => '2',
						'bottom' => '2',
						'left'   => '2',
						'unit'   => 'em',
					),
					'selectors'  => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-box-back-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$this->end_controls_tab();
			// container box back style tab - END

			$this->end_controls_tabs();

			$this->add_control(
				'ufae_container_divider',
				array(
					'label' => esc_html__( 'Divider', 'ultimate-flipbox-addon-for-elementor' ),
					'type'  => \Elementor\Controls_Manager::DIVIDER,
				)
			);

			$this->add_responsive_control(
				'ufae_container_margin',
				array(
					'label'      => esc_html__( 'Margin', 'ultimate-flipbox-addon-for-elementor' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'default'    => array(
						'top'    => '1',
						'right'  => '1',
						'bottom' => '1',
						'left'   => '1',
						'unit'   => 'em',
					),
					'selectors'  => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-box-margin-top: {{TOP}}{{UNIT}};--ufae-box-margin-right: {{RIGHT}}{{UNIT}};--ufae-box-margin-left: {{LEFT}}{{UNIT}};--ufae-box-margin-bottom: {{BOTTOM}}{{UNIT}};',
					),
					'condition'  => array(
						'ufae_layout_option!' => 'horizontal',
					),
				)
			);

			$this->add_responsive_control(
				'ufae_container_border_radius',
				array(
					'label'      => esc_html__( 'Border Radius', 'ultimate-flipbox-addon-for-elementor' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'default'    => array(
						'top'    => '20',
						'right'  => '20',
						'bottom' => '20',
						'left'   => '20',
						'unit'   => 'px',
					),
					'selectors'  => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-box-bd-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
				)
			);

			$this->add_responsive_control(
				'ufae_container_width',
				array(
					'label'      => esc_html__( 'Width', 'ultimate-flipbox-addon-for-elementor' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'size_units' => array( 'px', '%', 'em', 'rem' ),
					'range'      => array(
						'px' => array(
							'min'  => 0,
							'max'  => 1200,
							'step' => 1,
						),
					),
					'default'    => array(
						'size' => 100,
						'unit' => '%',
					),
					'selectors'  => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-box-width: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$this->add_responsive_control(
				'ufae_container_height',
				array(
					'label'      => esc_html__( 'Height', 'ultimate-flipbox-addon-for-elementor' ),
					'size_units' => array( 'px', 'em', 'rem' ),
					'type'       => \Elementor\Controls_Manager::SLIDER,
					'range'      => array(
						'px' => array(
							'min'  => 0,
							'max'  => 1200,
							'step' => 1,
						),
					),
					'default'    => array(
						'size' => 350,
						'unit' => 'px',
					),
					'selectors'  => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-box-height: {{SIZE}}{{UNIT}};',
					),
				)
			);

			$this->add_control(
				'ufae_container_boxshadow_popover',
				array(
					'type'         => \Elementor\Controls_Manager::POPOVER_TOGGLE,
					'label'        => esc_html__( 'Box Shadow', 'ultimate-flipbox-addon-for-elementor' ),
					'label_off'    => esc_html__( 'Default', 'ultimate-flipbox-addon-for-elementor' ),
					'label_on'     => esc_html__( 'Custom', 'ultimate-flipbox-addon-for-elementor' ),
					'return_value' => 'yes',
				)
			);

			$this->start_popover();

			$this->add_control(
				'ufae_container_box_shadow',
				array(
					'label'     => esc_html__( 'Box Shadow', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::BOX_SHADOW,
					'default'   => array(
						'color'    => 'rgba(0, 0, 0, 0.1)',
						'blur'     => 10,
						'spread'   => 0,
						'position' => 'outset',
					),
					// 'selector' => '{{WRAPPER}} ' . esc_html($this->common_selector) . ' :where(.ufae-flipbox-front,.ufae-flipbox-back)',
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector )  => '--ufae-box-shadow-hr: {{HORIZONTAL}}px;--ufae-box-shadow-vr: {{VERTICAL}}px;--ufae-box-shadow-blur: {{BLUR}}px;--ufae-box-shadow-spread: {{SPREAD}}px;--ufae-box-shadow-color: {{COLOR}};',
					),
					'condition' => array(
						'ufae_container_boxshadow_popover' => 'yes',
					),
				)
			);

			$this->end_popover();

			$this->end_controls_section();
			// container box style settings - END
		}

		/**
		 * Register title styles for the widget.
		 *
		 * This method is used to define the style-related controls for the title in the Elementor editor.
		 */
		protected function ufae_title_styles() {
			// title style settings - START
			$this->start_controls_section(
				'ufae_title_style_section',
				array(
					'label' => esc_html__( 'Title Style', 'ultimate-flipbox-addon-for-elementor' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				)
			);

			$this->start_controls_tabs(
				'ufae_flipbox_title_style_tabs'
			);

			// title front style tab - START
			$this->start_controls_tab(
				'ufae_front_title_tab',
				array(
					'label' => esc_html__( 'Front', 'ultimate-flipbox-addon-for-elementor' ),
				)
			);

			$this->add_control(
				'ufae_front_title_enable',
				array(
					'label'        => esc_html__( 'Enable Front Title', 'ultimate-flipbox-addon-for-elementor' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'ultimate-flipbox-addon-for-elementor' ),
					'label_off'    => esc_html__( 'No', 'ultimate-flipbox-addon-for-elementor' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'      => 'ufae_title_front_typography',
					'selector'  => '{{WRAPPER}} ' . esc_html( $this->common_selector ) . ' .ufae-flipbox-front .ufae-title',
					'condition' => array(
						'ufae_front_title_enable' => 'yes',
					),
				)
			);

			$this->add_control(
				'ufae_title_front_bg_color',
				array(
					'label'     => esc_html__( 'Background Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-title-front-bg-color: {{VALUE}};',
					),
					'condition' => array(
						'ufae_front_title_enable' => 'yes',
					),
				)
			);

			$this->add_control(
				'ufae_title_front_text_color',
				array(
					'label'     => esc_html__( 'Text Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-title-front-color: {{VALUE}};',
					),
					'condition' => array(
						'ufae_front_title_enable' => 'yes',
					),
					'default'   => '#FFFFFF',
				)
			);

			$this->add_responsive_control(
				'ufae_title_front_padding',
				array(
					'label'      => esc_html__( 'Padding', 'ultimate-flipbox-addon-for-elementor' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'default'    => array(
						'top'    => '10',
						'right'  => '10',
						'bottom' => '10',
						'left'   => '10',
						'unit'   => 'px',
					),
					'selectors'  => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-title-front-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition'  => array(
						'ufae_front_title_enable' => 'yes',
					),
				)
			);

			$this->add_responsive_control(
				'ufae_title_front_margin',
				array(
					'label'      => esc_html__( 'Margin', 'ultimate-flipbox-addon-for-elementor' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'default'    => array(
						'top'    => '0',
						'right'  => '0',
						'bottom' => '0',
						'left'   => '0',
						'unit'   => 'px',
					),
					'selectors'  => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-title-front-margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition'  => array(
						'ufae_front_title_enable' => 'yes',
					),
				)
			);

			$this->end_controls_tab();
			// title front style tab - END

			// title back style tab - START
			$this->start_controls_tab(
				'ufae_back_title_tab',
				array(
					'label' => esc_html__( 'Back', 'ultimate-flipbox-addon-for-elementor' ),
				)
			);

			$this->add_control(
				'ufae_back_title_enable',
				array(
					'label'        => esc_html__( 'Enable Back Title', 'ultimate-flipbox-addon-for-elementor' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'ultimate-flipbox-addon-for-elementor' ),
					'label_off'    => esc_html__( 'No', 'ultimate-flipbox-addon-for-elementor' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'      => 'ufae_title_back_typography',
					'selector'  => '{{WRAPPER}} ' . esc_html( $this->common_selector ) . ' .ufae-flipbox-back .ufae-title',
					'condition' => array(
						'ufae_back_title_enable' => 'yes',
					),
				)
			);

			$this->add_control(
				'ufae_title_back_bg_color',
				array(
					'label'     => esc_html__( 'Background Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,

					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-title-back-bg-color: {{VALUE}};',
					),
					'condition' => array(
						'ufae_back_title_enable' => 'yes',
					),
				)
			);

			$this->add_control(
				'ufae_title_back_text_color',
				array(
					'label'     => esc_html__( 'Text Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-title-back-color: {{VALUE}};',
					),
					'condition' => array(
						'ufae_back_title_enable' => 'yes',
					),
					'default'   => '#FFFFFF',
				)
			);

			$this->add_responsive_control(
				'ufae_title_back_padding',
				array(
					'label'      => esc_html__( 'Padding', 'ultimate-flipbox-addon-for-elementor' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'default'    => array(
						'top'    => '10',
						'right'  => '10',
						'bottom' => '10',
						'left'   => '10',
						'unit'   => 'px',
					),
					'selectors'  => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-title-back-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition'  => array(
						'ufae_back_title_enable' => 'yes',
					),
				)
			);

			$this->add_responsive_control(
				'ufae_title_back_margin',
				array(
					'label'      => esc_html__( 'Margin', 'ultimate-flipbox-addon-for-elementor' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'default'    => array(
						'top'    => '0',
						'right'  => '0',
						'bottom' => '0',
						'left'   => '0',
						'unit'   => 'px',
					),
					'selectors'  => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-title-back-margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition'  => array(
						'ufae_back_title_enable' => 'yes',
					),
				)
			);

			$this->end_controls_tab();
			// title back style tab - END

			$this->end_controls_tabs();

			$this->end_controls_section();
			// title style settings - END
		}

		/**
		 * Register description styles for the widget.
		 *
		 * This method is used to define the style-related controls for the description in the Elementor editor.
		 */
		protected function ufae_desc_styles() {
			// desc style settings - START
			$this->start_controls_section(
				'ufae_desc_style_section',
				array(
					'label' => esc_html__( 'Description Style', 'ultimate-flipbox-addon-for-elementor' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				)
			);

			$this->start_controls_tabs(
				'ufae_flipbox_desc_style_tabs'
			);

			// desc front style tab - START
			$this->start_controls_tab(
				'ufae_front_desc_tab',
				array(
					'label' => esc_html__( 'Front', 'ultimate-flipbox-addon-for-elementor' ),
				)
			);

			$this->add_control(
				'ufae_front_desc_enable',
				array(
					'label'        => esc_html__( 'Enable Front Description', 'ultimate-flipbox-addon-for-elementor' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'ultimate-flipbox-addon-for-elementor' ),
					'label_off'    => esc_html__( 'No', 'ultimate-flipbox-addon-for-elementor' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'      => 'ufae_desc_front_typography',
					'selector'  => '{{WRAPPER}} ' . esc_html( $this->common_selector ) . ' .ufae-flipbox-front .ufae-desc',
					'condition' => array(
						'ufae_front_desc_enable' => 'yes',
					),
				)
			);

			$this->add_control(
				'ufae_desc_front_bg_color',
				array(
					'label'     => esc_html__( 'Background Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,

					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-desc-front-bg-color: {{VALUE}};',
					),
					'condition' => array(
						'ufae_front_desc_enable' => 'yes',
					),
				)
			);

			$this->add_control(
				'ufae_desc_front_text_color',
				array(
					'label'     => esc_html__( 'Text Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-desc-front-color: {{VALUE}};',
					),
					'condition' => array(
						'ufae_front_desc_enable' => 'yes',
					),
					'default'   => '#FFFFFF',
				)
			);

			$this->add_responsive_control(
				'ufae_desc_front_padding',
				array(
					'label'      => esc_html__( 'Padding', 'ultimate-flipbox-addon-for-elementor' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'default'    => array(
						'top'    => '10',
						'right'  => '10',
						'bottom' => '10',
						'left'   => '10',
						'unit'   => 'px',
					),
					'selectors'  => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-desc-front-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition'  => array(
						'ufae_front_desc_enable' => 'yes',
					),
				)
			);

			$this->add_responsive_control(
				'ufae_desc_front_margin',
				array(
					'label'      => esc_html__( 'Margin', 'ultimate-flipbox-addon-for-elementor' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'default'    => array(
						'top'    => '0',
						'right'  => '0',
						'bottom' => '0',
						'left'   => '0',
						'unit'   => 'px',
					),
					'selectors'  => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-desc-front-margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition'  => array(
						'ufae_front_desc_enable' => 'yes',
					),
				)
			);

			$this->end_controls_tab();
			// desc front style tab - END

			// desc back style tab - START
			$this->start_controls_tab(
				'ufae_back_desc_tab',
				array(
					'label' => esc_html__( 'Back', 'ultimate-flipbox-addon-for-elementor' ),
				)
			);

			$this->add_control(
				'ufae_back_desc_enable',
				array(
					'label'        => esc_html__( 'Enable Back Description', 'ultimate-flipbox-addon-for-elementor' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'ultimate-flipbox-addon-for-elementor' ),
					'label_off'    => esc_html__( 'No', 'ultimate-flipbox-addon-for-elementor' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'      => 'ufae_desc_back_typography',
					'selector'  => '{{WRAPPER}} ' . esc_html( $this->common_selector ) . ' .ufae-flipbox-back .ufae-desc',
					'condition' => array(
						'ufae_back_desc_enable' => 'yes',
					),
				)
			);

			$this->add_control(
				'ufae_desc_back_bg_color',
				array(
					'label'     => esc_html__( 'Background Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-desc-back-bg-color: {{VALUE}};',
					),
					'condition' => array(
						'ufae_back_desc_enable' => 'yes',
					),
				)
			);

			$this->add_control(
				'ufae_desc_back_text_color',
				array(
					'label'     => esc_html__( 'Text Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-desc-back-color: {{VALUE}};',
					),
					'condition' => array(
						'ufae_back_desc_enable' => 'yes',
					),
					'default'   => '#FFFFFF',
				)
			);

			$this->add_responsive_control(
				'ufae_desc_back_padding',
				array(
					'label'      => esc_html__( 'Padding', 'ultimate-flipbox-addon-for-elementor' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'default'    => array(
						'top'    => '10',
						'right'  => '10',
						'bottom' => '10',
						'left'   => '10',
						'unit'   => 'px',
					),
					'selectors'  => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-desc-back-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition'  => array(
						'ufae_back_desc_enable' => 'yes',
					),
				)
			);

			$this->add_responsive_control(
				'ufae_desc_back_margin',
				array(
					'label'      => esc_html__( 'Margin', 'ultimate-flipbox-addon-for-elementor' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'default'    => array(
						'top'    => '0',
						'right'  => '0',
						'bottom' => '0',
						'left'   => '0',
						'unit'   => 'px',
					),
					'selectors'  => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-desc-back-margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition'  => array(
						'ufae_back_desc_enable' => 'yes',
					),
				)
			);

			$this->end_controls_tab();
			// desc back style tab - END

			$this->end_controls_tabs();

			$this->end_controls_section();
			// desc style settings - END
		}

		/**
		 * Register icon styles for the widget.
		 *
		 * This method is used to define the style-related controls for the icon in the Elementor editor.
		 */
		protected function ufae_icon_styles() {
			// icon style settings - START
			$this->start_controls_section(
				'ufae_icon_style_section',
				array(
					'label' => esc_html__( 'Icon Style', 'ultimate-flipbox-addon-for-elementor' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				)
			);

			$this->start_controls_tabs(
				'ufae_flipbox_icon_style_tabs'
			);

			// icon front style tab - START
			$this->start_controls_tab(
				'ufae_front_icon_tab',
				array(
					'label' => esc_html__( 'Front', 'ultimate-flipbox-addon-for-elementor' ),
				)
			);

			$this->add_control(
				'ufae_front_icon_enable',
				array(
					'label'        => esc_html__( 'Enable Front Icon', 'ultimate-flipbox-addon-for-elementor' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'ultimate-flipbox-addon-for-elementor' ),
					'label_off'    => esc_html__( 'No', 'ultimate-flipbox-addon-for-elementor' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_control(
				'ufae_icon_front_bg_color',
				array(
					'label'     => esc_html__( 'Background Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-icon-front-bg-color: {{VALUE}};',
					),
					'condition' => array(
						'ufae_front_icon_enable' => 'yes',
					),
				)
			);

			$this->add_control(
				'ufae_icon_front_color',
				array(
					'label'     => esc_html__( 'Icon Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-icon-front-color: {{VALUE}};',
					),
					'condition' => array(
						'ufae_front_icon_enable' => 'yes',
					),
					'default'   => '#FFFFFF',
				)
			);

			$this->add_responsive_control(
				'ufae_icon_front_size',
				array(
					'label'     => esc_html__( 'Icon Size', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::SLIDER,
					'range'     => array(
						'px' => array(
							'min'  => 0,
							'max'  => 500,
							'step' => 1,
						),
						'em' => array(
							'min'  => 0,
							'max'  => 100,
							'step' => 1,
						),
					),
					'default'   => array(
						'unit' => 'px',
						'size' => 64,
					),
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-icon-front-size: {{SIZE}}{{UNIT}};',
					),
					'condition' => array(
						'ufae_front_icon_enable' => 'yes',
					),
				)
			);

			$this->add_responsive_control(
				'ufae_icon_front_padding',
				array(
					'label'      => esc_html__( 'Icon Padding', 'ultimate-flipbox-addon-for-elementor' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'default'    => array(
						'top'    => '0',
						'right'  => '0',
						'bottom' => '0',
						'left'   => '0',
						'unit'   => 'px',
					),
					'selectors'  => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-icon-front-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition'  => array(
						'ufae_front_icon_enable' => 'yes',
					),
				)
			);

			$this->add_responsive_control(
				'ufae_icon_front_margin',
				array(
					'label'      => esc_html__( 'Icon Margin', 'ultimate-flipbox-addon-for-elementor' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'default'    => array(
						'top'    => '0',
						'right'  => '0',
						'bottom' => '0',
						'left'   => '0',
						'unit'   => 'px',
					),
					'selectors'  => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-icon-front-margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition'  => array(
						'ufae_front_icon_enable' => 'yes',
					),
				)
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				array(
					'name'      => 'ufae_icon_front_border',
					'label'     => esc_html__( 'Icon Border', 'ultimate-flipbox-addon-for-elementor' ),
					'selector'  => '{{WRAPPER}} ' . esc_html( $this->common_selector ) . ' .ufae-flipbox-front .ufae-icon-wrapper',
					'condition' => array(
						'ufae_front_icon_enable' => 'yes',
					),
				)
			);

			$this->end_controls_tab();
			// icon front style tab - END

			// icon back style tab - START
			$this->start_controls_tab(
				'ufae_back_icon_tab',
				array(
					'label' => esc_html__( 'Back', 'ultimate-flipbox-addon-for-elementor' ),
				)
			);

			$this->add_control(
				'ufae_back_icon_enable',
				array(
					'label'        => esc_html__( 'Enable Back Icon', 'ultimate-flipbox-addon-for-elementor' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'ultimate-flipbox-addon-for-elementor' ),
					'label_off'    => esc_html__( 'No', 'ultimate-flipbox-addon-for-elementor' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_control(
				'ufae_icon_back_bg_color',
				array(
					'label'     => esc_html__( 'Background Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-icon-back-bg-color: {{VALUE}};',
					),
					'condition' => array(
						'ufae_back_icon_enable' => 'yes',
					),
				)
			);

			$this->add_control(
				'ufae_icon_back_color',
				array(
					'label'     => esc_html__( 'Icon Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-icon-back-color: {{VALUE}};',
					),
					'condition' => array(
						'ufae_back_icon_enable' => 'yes',
					),
					'default'   => '#FFFFFF',
				)
			);

			$this->add_responsive_control(
				'ufae_icon_back_size',
				array(
					'label'     => esc_html__( 'Icon Size', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::SLIDER,
					'range'     => array(
						'px' => array(
							'min'  => 0,
							'max'  => 500,
							'step' => 1,
						),
						'em' => array(
							'min'  => 0,
							'max'  => 100,
							'step' => 1,
						),
					),
					'default'   => array(
						'unit' => 'px',
						'size' => 64,
					),
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-icon-back-size: {{SIZE}}{{UNIT}};',
					),
					'condition' => array(
						'ufae_back_icon_enable' => 'yes',
					),
				)
			);

			$this->add_responsive_control(
				'ufae_icon_back_padding',
				array(
					'label'      => esc_html__( 'Icon Padding', 'ultimate-flipbox-addon-for-elementor' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'default'    => array(
						'top'    => '0',
						'right'  => '0',
						'bottom' => '0',
						'left'   => '0',
						'unit'   => 'px',
					),
					'selectors'  => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-icon-back-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition'  => array(
						'ufae_back_icon_enable' => 'yes',
					),
				)
			);

			$this->add_responsive_control(
				'ufae_icon_back_margin',
				array(
					'label'      => esc_html__( 'Icon Margin', 'ultimate-flipbox-addon-for-elementor' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'default'    => array(
						'top'    => '0',
						'right'  => '0',
						'bottom' => '0',
						'left'   => '0',
						'unit'   => 'px',
					),
					'selectors'  => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-icon-back-margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition'  => array(
						'ufae_back_icon_enable' => 'yes',
					),
				)
			);

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				array(
					'name'      => 'ufae_icon_back_border',
					'label'     => esc_html__( 'Icon Border', 'ultimate-flipbox-addon-for-elementor' ),
					'selector'  => '{{WRAPPER}} ' . esc_html( $this->common_selector ) . ' .ufae-flipbox-back .ufae-icon-wrapper',
					'condition' => array(
						'ufae_back_icon_enable' => 'yes',
					),
				)
			);

			$this->end_controls_tab();
			// icon back style tab - END

			$this->end_controls_tabs();

			$this->end_controls_section();
			// icon style settings - END
		}

		/**
		 * Register button styles for the widget.
		 *
		 * This method is used to define the style-related controls for the button in the Elementor editor.
		 */
		protected function ufae_btn_styles() {
			// btn style settings - START
			$this->start_controls_section(
				'ufae_btn_style_section',
				array(
					'label' => esc_html__( 'Button Style', 'ultimate-flipbox-addon-for-elementor' ),
					'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
				)
			);

			$this->start_controls_tabs(
				'ufae_flipbox_btn_style_tabs'
			);

			// btn front style tab - START
			$this->start_controls_tab(
				'ufae_front_btn_tab',
				array(
					'label' => esc_html__( 'Front', 'ultimate-flipbox-addon-for-elementor' ),
				)
			);

			$this->add_control(
				'ufae_front_button_enable',
				array(
					'label'        => esc_html__( 'Enable Front Button', 'ultimate-flipbox-addon-for-elementor' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'ultimate-flipbox-addon-for-elementor' ),
					'label_off'    => esc_html__( 'No', 'ultimate-flipbox-addon-for-elementor' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'           => 'ufae_btn_front_typography',
					'label'          => esc_html__( 'Button Typography', 'ultimate-flipbox-addon-for-elementor' ),
					'selector'       => '{{WRAPPER}} ' . esc_html( $this->common_selector ) . ' .ufae-flipbox-front .ufae-btn-wrapper .ufae-button',
					'fields_options' => array(
						'font_family'     => array(
							'default' => 'Arial',
						),
						'font_size'       => array(
							'default' => array(
								'unit' => 'px',
								'size' => 16,
							),
						),
						'text_decoration' => array(
							'default' => 'none',
						),
						'line_height'     => array(
							'default' => array(
								'unit' => 'px',
								'size' => 16,
							),
						),
					),
					'condition'      => array(
						'ufae_front_button_enable' => 'yes',
					),
				)
			);

			$this->add_control(
				'ufae_btn_front_bg_color',
				array(
					'label'     => esc_html__( 'Background Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#000',
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-btn-front-bg-color: {{VALUE}};',
					),
					'condition' => array(
						'ufae_front_button_enable' => 'yes',
					),
					'default'   => '#408FFF',
				)
			);

			$this->add_control(
				'ufae_btn_front_color',
				array(
					'label'     => esc_html__( 'Text Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#fff',
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-btn-front-color: {{VALUE}};',
					),
					'condition' => array(
						'ufae_front_button_enable' => 'yes',
					),
					'default'   => '#FFFFFF',
				)
			);

			$this->add_responsive_control(
				'ufae_btn_front_width',
				array(
					'label'     => esc_html__( 'Button Width', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::SLIDER,
					'range'     => array(
						'px' => array(
							'min'  => 0,
							'max'  => 500,
							'step' => 1,
						),
					),
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-btn-front-width: {{SIZE}}{{UNIT}};',
					),
					'condition' => array(
						'ufae_front_button_enable' => 'yes',
					),
				)
			);

			$this->add_responsive_control(
				'ufae_btn_front_padding',
				array(
					'label'      => esc_html__( 'Padding', 'ultimate-flipbox-addon-for-elementor' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'default'    => array(
						'top'    => '10',
						'right'  => '20',
						'bottom' => '10',
						'left'   => '20',
						'unit'   => 'px',
					),
					'selectors'  => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-btn-front-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition'  => array(
						'ufae_front_button_enable' => 'yes',
					),
				)
			);

			$this->add_responsive_control(
				'ufae_btn_front_margin',
				array(
					'label'      => esc_html__( 'Margin', 'ultimate-flipbox-addon-for-elementor' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'default'    => array(
						'top'    => '0',
						'right'  => '0',
						'bottom' => '0',
						'left'   => '0',
						'unit'   => 'px',
					),
					'selectors'  => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-btn-front-margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition'  => array(
						'ufae_front_button_enable' => 'yes',
					),
				)
			);

			$this->add_control(
				'ufae_btn_front_border_popover',
				array(
					'type'         => \Elementor\Controls_Manager::POPOVER_TOGGLE,
					'label'        => esc_html__( 'Border', 'ultimate-flipbox-addon-for-elementor' ),
					'label_off'    => esc_html__( 'Default', 'ultimate-flipbox-addon-for-elementor' ),
					'label_on'     => esc_html__( 'Custom', 'ultimate-flipbox-addon-for-elementor' ),
					'return_value' => 'yes',
					'default'      => 'yes',
					'condition'    => array(
						'ufae_front_button_enable' => 'yes',
					),
				)
			);

			$this->start_popover();

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				array(
					'name'           => 'ufae_btn_front_border',
					'label'          => esc_html__( 'Button Border', 'ultimate-flipbox-addon-for-elementor' ),
					'selector'       => '{{WRAPPER}} ' . esc_attr( $this->common_selector ) . ' .ufae-flipbox-front .ufae-btn-wrapper .ufae-button',
					'fields_options' => array(
						'width'  => array(
							'selectors' => array(
								'{{SELECTOR}}' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; --border-top-width: {{TOP}}{{UNIT}}; --border-right-width: {{RIGHT}}{{UNIT}}; --border-bottom-width: {{BOTTOM}}{{UNIT}}; --border-left-width: {{LEFT}}{{UNIT}};',
							),
							'default'   => array(
								'top'    => '1',
								'right'  => '1',
								'bottom' => '1',
								'left'   => '1',
								'unit'   => 'px',
							),
						),
						'color'  => array(
							'selectors' => array(
								'{{SELECTOR}}:not(:hover)' => 'border-color: {{VALUE}}; --border-color: {{VALUE}};',
							),
							'default'   => '#fff',
						),
						'border' => array(
							'selectors' => array(
								'{{SELECTOR}}' => 'border-style: {{VALUE}}; --border-style: {{VALUE}};',
							),
							'default'   => 'solid',
						),
					),
					'condition'      => array(
						'ufae_btn_front_border_popover' => 'yes',
						'ufae_front_button_enable'      => 'yes',
					),
				)
			);

			$this->end_popover();

			$this->end_controls_tab();
			// btn front style tab - END

			// btn back style tab - START
			$this->start_controls_tab(
				'ufae_back_btn_tab',
				array(
					'label' => esc_html__( 'Back', 'ultimate-flipbox-addon-for-elementor' ),
				)
			);

			$this->add_control(
				'ufae_back_button_enable',
				array(
					'label'        => esc_html__( 'Enable Back Button', 'ultimate-flipbox-addon-for-elementor' ),
					'type'         => \Elementor\Controls_Manager::SWITCHER,
					'label_on'     => esc_html__( 'Yes', 'ultimate-flipbox-addon-for-elementor' ),
					'label_off'    => esc_html__( 'No', 'ultimate-flipbox-addon-for-elementor' ),
					'return_value' => 'yes',
					'default'      => 'yes',
				)
			);

			$this->add_group_control(
				\Elementor\Group_Control_Typography::get_type(),
				array(
					'name'           => 'ufae_btn_back_typography',
					'label'          => esc_html__( 'Button Typography', 'ultimate-flipbox-addon-for-elementor' ),
					'selector'       => '{{WRAPPER}} ' . esc_html( $this->common_selector ) . ' .ufae-flipbox-back .ufae-btn-wrapper .ufae-button',
					'fields_options' => array(
						'font_family'     => array(
							'default' => 'Arial',
						),
						'font_size'       => array(
							'default' => array(
								'unit' => 'px',
								'size' => 16,
							),
						),
						'text_decoration' => array(
							'default' => 'none',
						),
						'line_height'     => array(
							'default' => array(
								'unit' => 'px',
								'size' => 16,
							),
						),
					),
					'condition'      => array(
						'ufae_back_button_enable' => 'yes',
					),
				)
			);

			$this->add_control(
				'ufae_btn_back_bg_color',
				array(
					'label'     => esc_html__( 'Background Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#000',
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-btn-back-bg-color: {{VALUE}};',
					),
					'condition' => array(
						'ufae_back_button_enable' => 'yes',
					),
					'default'   => '#40D0FF',
				)
			);

			$this->add_control(
				'ufae_btn_back_color',
				array(
					'label'     => esc_html__( 'Text Color', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::COLOR,
					'default'   => '#fff',
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-btn-back-color: {{VALUE}};',
					),
					'condition' => array(
						'ufae_back_button_enable' => 'yes',
					),
					'default'   => '#FFFFFF',
				)
			);

			$this->add_responsive_control(
				'ufae_btn_back_width',
				array(
					'label'     => esc_html__( 'Button Width', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::SLIDER,
					'range'     => array(
						'px' => array(
							'min'  => 0,
							'max'  => 500,
							'step' => 1,
						),
					),
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-btn-back-width: {{SIZE}}{{UNIT}};',
					),
					'condition' => array(
						'ufae_back_button_enable' => 'yes',
					),
				)
			);

			$this->add_responsive_control(
				'ufae_btn_back_padding',
				array(
					'label'      => esc_html__( 'Padding', 'ultimate-flipbox-addon-for-elementor' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'default'    => array(
						'top'    => '10',
						'right'  => '20',
						'bottom' => '10',
						'left'   => '20',
						'unit'   => 'px',
					),
					'selectors'  => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-btn-back-padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition'  => array(
						'ufae_back_button_enable' => 'yes',
					),
				)
			);

			$this->add_responsive_control(
				'ufae_btn_back_margin',
				array(
					'label'      => esc_html__( 'Margin', 'ultimate-flipbox-addon-for-elementor' ),
					'type'       => \Elementor\Controls_Manager::DIMENSIONS,
					'size_units' => array( 'px', '%', 'em' ),
					'default'    => array(
						'top'    => '0',
						'right'  => '0',
						'bottom' => '0',
						'left'   => '0',
						'unit'   => 'px',
					),
					'selectors'  => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-btn-back-margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
					),
					'condition'  => array(
						'ufae_back_button_enable' => 'yes',
					),
				)
			);

			$this->add_control(
				'ufae_btn_back_border_popover',
				array(
					'type'         => \Elementor\Controls_Manager::POPOVER_TOGGLE,
					'label'        => esc_html__( 'Border', 'ultimate-flipbox-addon-for-elementor' ),
					'label_off'    => esc_html__( 'Default', 'ultimate-flipbox-addon-for-elementor' ),
					'label_on'     => esc_html__( 'Custom', 'ultimate-flipbox-addon-for-elementor' ),
					'return_value' => 'yes',
					'default'      => 'yes',
					'condition'    => array(
						'ufae_back_button_enable' => 'yes',
					),
				)
			);

			$this->start_popover();

			$this->add_group_control(
				\Elementor\Group_Control_Border::get_type(),
				array(
					'name'           => 'ufae_btn_back_border',
					'label'          => esc_html__( 'Button Border', 'ultimate-flipbox-addon-for-elementor' ),
					'selector'       => '{{WRAPPER}} ' . esc_attr( $this->common_selector ) . ' .ufae-flipbox-back .ufae-btn-wrapper .ufae-button',
					'fields_options' => array(
						'width'  => array(
							'selectors' => array(
								'{{SELECTOR}}' => 'border-width: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}; --border-top-width: {{TOP}}{{UNIT}}; --border-right-width: {{RIGHT}}{{UNIT}}; --border-bottom-width: {{BOTTOM}}{{UNIT}}; --border-left-width: {{LEFT}}{{UNIT}};',
							),
							'default'   => array(
								'top'    => '1',
								'right'  => '1',
								'bottom' => '1',
								'left'   => '1',
								'unit'   => 'px',
							),
						),
						'color'  => array(
							'selectors' => array(
								'{{SELECTOR}}:not(:hover)' => 'border-color: {{VALUE}}; --border-color: {{VALUE}};',
							),
							'default'   => '#fff',
						),
						'border' => array(
							'selectors' => array(
								'{{SELECTOR}}' => 'border-style: {{VALUE}}; --border-style: {{VALUE}};',
							),
							'default'   => 'solid',
						),
					),
					'condition'      => array(
						'ufae_btn_back_border_popover' => 'yes',
						'ufae_back_button_enable'      => 'yes',
					),
				)
			);

			$this->end_popover();

			$this->end_controls_tab();
			// btn back style tab - END

			$this->end_controls_tabs();

			$this->end_controls_section();
			// btn style settings - END
		}

		/**
		 * Register layout controls for the widget.
		 *
		 * This method is used to define the layout-related controls for the widget in the Elementor editor.
		 */
		protected function ufae_content_layout_controls() {
			$this->start_controls_section(
				'ufae_layout_section',
				array(
					'label' => esc_html__( 'Layout Settings', 'ultimate-flipbox-addon-for-elementor' ),
					'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
				)
			);

			$this->add_control(
				'ufae_layout_option',
				array(
					'label'   => esc_html__( 'Layout Orientation', 'ultimate-flipbox-addon-for-elementor' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'options' => array(
						'vertical'   => __( 'Vertical', 'ultimate-flipbox-addon-for-elementor' ),
						'horizontal' => __( 'Horizontal', 'ultimate-flipbox-addon-for-elementor' ),
					),
					'default' => 'vertical',
				)
			);

			$this->add_control(
				'ufae_design_option',
				array(
					'label'   => esc_html__( 'Select Preset', 'ultimate-flipbox-addon-for-elementor' ),
					'type'    => 'ufae_design_control',
					'options' => array(
						'ufae-design-0' => __( 'Default', 'ultimate-flipbox-addon-for-elementor' ),
						'ufae-design-1' => __( 'Simple', 'ultimate-flipbox-addon-for-elementor' ),
						'ufae-design-2' => __( 'Bold', 'ultimate-flipbox-addon-for-elementor' ),
						'ufae-design-3' => __( 'Clean', 'ultimate-flipbox-addon-for-elementor' ),
					),
					'message' => __( 'This setting will overwrite your current settings with the selected design option.', 'ultimate-flipbox-addon-for-elementor' ),
					'default' => 'ufae-design-0',
				)
			);

			$this->add_control(
				'ufae_title_tag',
				array(
					'label'   => esc_html__( 'Title Tag', 'ultimate-flipbox-addon-for-elementor' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'options' => array(
						'h1'  => 'H1',
						'h2'  => 'H2',
						'h3'  => 'H3',
						'h4'  => 'H4',
						'h5'  => 'H5',
						'h6'  => 'H6',
						'div' => 'Div',
					),
					'default' => 'h2',
				)
			);

			$this->add_responsive_control(
				'ufae_column_control',
				array(
					'label'     => esc_html__( 'Number of Columns', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::NUMBER,
					'default'   => 3,
					'min'       => 1,
					'max'       => 6,
					'step'      => 1,
					'selectors' => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-items-column: {{VALUE}};',
					),
					'condition' => array(
						'ufae_layout_option!' => 'horizontal',
					),
				)
			);

			$this->add_responsive_control(
				'ufae_hr_slider_perview_control',
				array(
					'label'     => esc_html__( 'Slides Per View', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::NUMBER,
					'default'   => 2,
					'min'       => 1,
					'max'       => 6,
					'step'      => 1,
					'condition' => array(
						'ufae_layout_option' => 'horizontal',
					),
				)
			);

			$this->add_responsive_control(
				'ufae_horizontal_slides_gap',
				array(
					'label'       => esc_html__( 'Horizontal Slides Gap', 'ultimate-flipbox-addon-for-elementor' ),
					'type'        => \Elementor\Controls_Manager::SLIDER,
					'default'     => array(
						'size' => 20,
						'unit' => 'px',
					),
					'range'       => array(
						'px' => array(
							'min'  => 0,
							'max'  => 100,
							'step' => 1,
						),
					),
					'selectors'   => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-horizontal-slide-gap: {{SIZE}}{{UNIT}};',
					),
					'condition'   => array(
						'ufae_layout_option' => 'horizontal',
					),
					'render_type' => 'template',
				)
			);

			$this->add_control(
				'ufae_animation_option',
				array(
					'label'   => esc_html__( 'Flipbox Animation', 'ultimate-flipbox-addon-for-elementor' ),
					'type'    => \Elementor\Controls_Manager::SELECT,
					'options' => array(
						'none'    => __( 'None', 'ultimate-flipbox-addon-for-elementor' ),
						'flip'    => __( 'Flip', 'ultimate-flipbox-addon-for-elementor' ),
						'fade'    => __( 'Fade', 'ultimate-flipbox-addon-for-elementor' ),
						'zoom'    => __( 'Zoom', 'ultimate-flipbox-addon-for-elementor' ),
						'slide'   => __( 'Slide', 'ultimate-flipbox-addon-for-elementor' ),
						'curtain' => __( 'Curtain (Hot)', 'ultimate-flipbox-addon-for-elementor' ),
					),
					'default' => 'flip',
				)
			);

			$this->add_control(
				'ufae_flip_direction',
				array(
					'label'     => esc_html__( 'Flip Direction', 'ultimate-flipbox-addon-for-elementor' ),
					'type'      => \Elementor\Controls_Manager::CHOOSE,
					'options'   => array(
						'left'   => array(
							'title' => esc_html__( 'Left', 'ultimate-flipbox-addon-for-elementor' ),
							'icon'  => 'eicon-h-align-left',
						),
						'right'  => array(
							'title' => esc_html__( 'Right', 'ultimate-flipbox-addon-for-elementor' ),
							'icon'  => 'eicon-h-align-right',
						),
						'top'    => array(
							'title' => esc_html__( 'Top', 'ultimate-flipbox-addon-for-elementor' ),
							'icon'  => 'eicon-v-align-top',
						),
						'bottom' => array(
							'title' => esc_html__( 'Bottom', 'ultimate-flipbox-addon-for-elementor' ),
							'icon'  => 'eicon-v-align-bottom',
						),
					),
					'default'   => 'left',
					'condition' => array(
						'ufae_animation_option' => 'flip',
					),
				)
			);

			$this->add_control(
				'ufae_transition_duration',
				array(
					'label'       => esc_html__( 'Transition Duration (ms)', 'ultimate-flipbox-addon-for-elementor' ),
					'type'        => \Elementor\Controls_Manager::NUMBER,
					'default'     => 1000,
					'min'         => 100,
					'max'         => 50000,
					'step'        => 100,
					'description' => esc_html__( 'Set the duration of the CSS transition in milliseconds.', 'ultimate-flipbox-addon-for-elementor' ),
					'selectors'   => array(
						'{{WRAPPER}} ' . esc_html( $this->common_selector ) => '--ufae-transition-timing: {{VALUE}};',
					),
				)
			);

			$this->add_control(
				'ufae_front_element_position',
				array(
					'label'       => esc_html__( 'Front Element Position', 'ultimate-flipbox-addon-for-elementor' ),
					'description' => esc_html__( 'Specify the order of flipbox elements as a comma-separated list (e.g., "icon, title, desc, button"). Ensure each element is separated by a comma.', 'ultimate-flipbox-addon-for-elementor' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'label_block' => true,
					'default'     => 'icon,title,desc,button',
					'ai'          => false,
				)
			);

			$this->add_control(
				'ufae_back_element_position',
				array(
					'label'       => esc_html__( 'Front Element Position', 'ultimate-flipbox-addon-for-elementor' ),
					'description' => esc_html__( 'Specify the order of flipbox elements as a comma-separated list (e.g., "icon, title, desc, button"). Ensure each element is separated by a comma.', 'ultimate-flipbox-addon-for-elementor' ),
					'type'        => \Elementor\Controls_Manager::TEXT,
					'label_block' => true,
					'default'     => 'icon,title,desc,button',
					'ai'          => false,
				)
			);

			$this->end_controls_section();
		}

		/**
		 * Render the widget output on the frontend.
		 *
		 * @return void
		 */
		protected function render() {
			$settings      = $this->get_settings_for_display();
			$frontend_file = UFAE_DIR . 'widget/ufae-frontend/class-ufae-frontend-output.php';
			$loop_file     = UFAE_DIR . 'widget/ufae-frontend/class-ufae-frontend-loop.php';

			if ( file_exists( $loop_file ) ) {
				require_once $loop_file;

				if ( file_exists( $frontend_file ) ) {

					require_once $frontend_file;

					$flipbox = new Ufae_Frontend_Output( $settings, $this );
					$flipbox->render();
				}
			}
		}
		/**
		 * Render the widget's content template in the editor.
		 *
		 * This method is responsible for rendering the content template
		 * of the widget in the Elementor editor. It includes the necessary
		 * files for the editor output and loop, and initializes the editor
		 * output rendering.
		 *
		 * @return void
		 */
		protected function content_template() {
			$frontend_file = UFAE_DIR . 'widget/ufae-editor/class-ufae-editor-output.php';
			$loop_file     = UFAE_DIR . 'widget/ufae-editor/class-ufae-editor-loop.php';

			if ( file_exists( $loop_file ) ) {
				require_once $loop_file;

				if ( file_exists( $frontend_file ) ) {
					require_once $frontend_file;

					$flipbox = new Ufae_Editor_Output();
					$flipbox->render();
				}
			}
		}
	}

}
