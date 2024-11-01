<?php

namespace Ultimate_Flipbox_Addon_For_Elementor;

use Ultimate_Flipbox_Addon_For_Elementor\Ufae_Frontend_Loop;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Ufae_Frontend_Output' ) ) {
	/**
	 * Class Ufae_Frontend_Output
	 *
	 * Handles the output for the Ultimate Flipbox Addon for Elementor.
	 */
	class Ufae_Frontend_Output {

		/**
		 * Settings for the frontend output.
		 *
		 * @var array
		 */
		private $settings;

		/**
		 * Parent_obj widget instance.
		 *
		 * @var object
		 */
		private $parent_obj;

		/**
		 * Loop object for rendering flipbox items.
		 *
		 * @var Ufae_Frontend_Loop
		 */
		private $loop_obj;

		/**
		 * Ufae_Frontend_Output constructor.
		 *
		 * @param array  $settings Settings for the frontend output.
		 * @param object $parent_obj parent_obj widget instance.
		 */
		public function __construct( $settings, $parent_obj ) {
			$this->settings   = $settings;
			$this->parent_obj = $parent_obj;
			$this->loop_obj   = new Ufae_Frontend_Loop( $settings );
		}

		/**
		 * Renders the frontend output.
		 */
		public function render() {
			$widget_id                  = $this->parent_obj->get_id();
			$list_items                 = $this->settings['ufae_lists'];
			$layout                     = $this->settings['ufae_layout_option'];
			$animation                  = isset( $this->settings['ufae_animation_option'] ) && ! empty( $this->settings['ufae_animation_option'] ) ? $this->settings['ufae_animation_option'] : 'flip';
			$animation_dir              = isset( $this->settings['ufae_flip_direction'] ) && ! empty( $this->settings['ufae_flip_direction'] ) ? '-' . $this->settings['ufae_flip_direction'] : '-left';
			$animation_dir              = 'flip' === $animation ? $animation_dir : '';
			$transition_time            = isset( $this->settings['ufae_transition_duration'] ) && ! empty( $this->settings['ufae_transition_duration'] ) ? $this->settings['ufae_transition_duration'] : '1000';
			$horizontal_layout          = 'horizontal' === $layout;
			$horizontal_container_class = $horizontal_layout ? 'ufae_horizontal_container' : '';

			$this->parent_obj->add_render_attribute(
				'ufae_container',
				array(
					'id'                   => 'ufae_' . esc_attr( $widget_id ),
					'class'                => array(
						'ufae-container',
						'ufae-layout-' . esc_attr( $layout ),
					),
					'data-ufae-animation'  => esc_attr( $animation . $animation_dir ),
					'data-ufae-transition' => esc_attr( $transition_time ),
				)
			);

			if ( $horizontal_layout ) {
				$this->parent_obj->add_render_attribute(
					'ufae_container',
					array(
						'class'               => array(
							esc_html( $horizontal_container_class ),
						),
						'data-ufae-slideview' => isset( $this->settings['ufae_hr_slider_perview_control'] ) ? esc_attr( $this->settings['ufae_hr_slider_perview_control'] ) : 2,
					)
				);
			}

			echo '<div class="ufae-wrapper">';
			echo '<div ' . $this->parent_obj->get_render_attribute_string( 'ufae_container' ) . '>';
			if ( $horizontal_layout ) {
				echo '<div class="ufae-swiper-container"><div class="swiper-wrapper">';
			}
			if ( is_array( $list_items ) && count( $list_items ) > 0 ) {
				foreach ( $list_items as $item ) {
					$this->loop_obj->flipbox_items( $item );
				}
			}
			if ( $horizontal_layout ) {
				echo '</div></div>';
			}
			echo '</div>';

			if ( $horizontal_layout ) {
				echo '<div class="swiper-button-next"></div>';
				echo '<div class="swiper-button-prev"></div>';
				echo '<div class="ufae-swiper-pagination"></div>';
			}

			echo '</div>';
		}
	}

}
