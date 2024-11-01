<?php

namespace Ultimate_Flipbox_Addon_For_Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Icons_Manager;

if ( ! class_exists( 'Ufae_Frontend_Loop' ) ) {
	/**
	 * Class Ufae_Frontend_Loop
	 *
	 * This class handles the frontend loop for the Ultimate Flipbox Addon for Elementor.
	 * It manages the rendering of flipbox items and their settings.
	 */
	class Ufae_Frontend_Loop {

		/**
		 * This array contains the configuration settings for the flipbox frontend rendering.
		 *
		 * @var array $settings The settings for the frontend loop.
		 */
		private $settings = array();

		/**
		 * This array holds the individual flipbox items that will be rendered in the frontend.
		 *
		 * @var array $item The items for the flipbox.
		 */
		private $item = array();

		/**
		 * This array defines the sequence in which the front elements (icon, title, description, button) are rendered.
		 *
		 * @var array $front_element_order The order of elements displayed on the front side of the flipbox.
		 */
		private $front_element_order = array();

		/**
		 * This array defines the sequence in which the back elements (icon, title, description, button) are rendered.
		 *
		 * @var array $back_element_order The order of elements displayed on the back side of the flipbox.
		 */
		private $back_element_order = array();

		/**
		 * Ufae_Frontend_Loop constructor.
		 *
		 * This constructor accepts settings as a parameter and assigns them to the instance variable.
		 *
		 * @param array $settings The settings to be used for the frontend loop.
		 */
		public function __construct( $settings ) {
			$this->settings = $settings;

			$this->front_element_order = array(
				'icon',
				'title',
				'desc',
				'button',
			);

			$this->back_element_order = array(
				'icon',
				'title',
				'desc',
				'button',
			);

			$this->update_element_order();
		}

		/**
		 * Renders the flipbox items.
		 *
		 * This method outputs the HTML structure for a flipbox item, including the front and back sides.
		 *
		 * @param array $item The item data to render.
		 */
		public function flipbox_items( $item ) {
			$this->item            = $item;
			$layout                = $this->settings['ufae_layout_option'];
			$horizontal_layout     = 'horizontal' === $layout;
			$horizontal_item_class = $horizontal_layout ? ' swiper-slide' : '';
			$animation             = isset( $this->settings['ufae_animation_option'] ) && ! empty( $this->settings['ufae_animation_option'] ) ? $this->settings['ufae_animation_option'] : 'flip';
			$back_enabled          = isset( $this->item['ufae_back_enable'] ) && 'yes' === $this->item['ufae_back_enable'];
			$back_disable_cls      = $back_enabled ? '' : ' ufae-flipbox-front_only';

			echo '<div class="ufae-flipbox-item elementor-repeater-item-' . esc_attr( $this->item['_id'] ) . esc_attr( $horizontal_item_class ) . esc_attr( $back_disable_cls ) . '">';
			echo '<div class="ufae-flipbox-inner">';
			echo '<div class="ufae-flipbox-inner-overlay">';
			$this->render_sides_content( 'front', '' );
			if ( 'curtain' === $animation && $back_enabled) {
				$this->render_sides_content( 'front', ' ufae-front_duplicate' );
				$this->render_sides_content( 'front', ' ufae-front-duplicate_overlay' );
			}
			if ( $back_enabled ) {
				$this->render_sides_content( 'back', '' );
			}
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}

		/**
		 * Renders the content for the specified side of the flipbox.
		 *
		 * This method outputs the HTML structure for the specified side (front or back) of the flipbox,
		 * including the elements in the defined order.
		 *
		 * @param string $side The side of the flipbox to render ('front' or 'back').
		 */
		private function render_sides_content( $side, $wrp_cls = '' ) {
			$side = esc_html( $side );

			$element_order = $this->{$side . '_element_order'};

			echo '<div class="ufae-flipbox-' . esc_attr( $side ) . esc_attr( $wrp_cls ) . '">';
			echo '<div class="ufae-flipbox-content-overlay">';
			echo '<div class="ufae-flipbox-content">';

			foreach ( $element_order as $element ) {

				$element_enable = isset( $this->settings[ 'ufae_' . $side . '_' . $element . '_enable' ] ) && 'yes' !== $this->settings[ 'ufae_' . $side . '_' . $element . '_enable' ] ? false : true;

				if ( ! $element_enable ) {
					continue;
				}

				switch ( $element ) {
					case 'icon':
						$this->render_icon( $side );
						break;
					case 'title':
						$this->render_title( $side );
						break;
					case 'desc':
						$this->render_desc( $side );
						break;
					case 'button':
						$this->render_button( $side );
						break;
				}
			}

			echo '</div>';
			echo '</div>';
			echo '</div>';
		}

		/**
		 * Renders the icon for the specified side of the flipbox.
		 *
		 * This method outputs the HTML for the icon based on the icon type (icon, image, or text).
		 *
		 * @param string $side The side of the flipbox to render the icon for ('front' or 'back').
		 */
		private function render_icon( $side ) {
			$side      = esc_html( $side );
			$icon_type = isset( $this->item[ 'ufae_' . $side . '_icon_type' ] ) && ! empty( $this->item[ 'ufae_' . $side . '_icon_type' ] ) ? $this->item[ 'ufae_' . $side . '_icon_type' ] : false;

			if ( $icon_type ) {
				if ( 'icon' === $icon_type ) {
					echo '<div class="ufae-icon-wrapper">';
					Icons_Manager::render_icon(
						$this->item[ 'ufae_' . $side . '_icon' ],
						array(
							'aria-hidden' => 'true',
							'class'       => 'ufae-icon',
						)
					);
					echo '</div>';
				} elseif ( 'image' === $icon_type && ! empty( $this->item[ 'ufae_' . $side . '_icon_image' ]['url'] ) ) {
					echo '<div class="ufae-icon-wrapper">';
					echo '<img src="' . esc_url( $this->item[ 'ufae_' . $side . '_icon_image' ]['url'] ) . '" alt="' . esc_attr( $this->item[ 'ufae_' . $side . '_title' ] ) . '">';
					echo '</div>';
				} elseif ( 'text' === $icon_type ) {
					echo '<div class="ufae-icon-wrapper">';
					echo '<span>' . esc_html( $this->item[ 'ufae_' . $side . '_icon_text' ] ) . '</span>';
					echo '</div>';
				}
			}
		}

		/**
		 * Renders the title for the specified side of the flipbox.
		 *
		 * This method outputs the HTML for the title element, using the specified title tag.
		 *
		 * @param string $side The side of the flipbox to render the title for ('front' or 'back').
		 */
		private function render_title( $side ) {
			$title = isset( $this->item[ 'ufae_' . $side . '_title' ] ) && ! empty( $this->item[ 'ufae_' . $side . '_title' ] ) ? $this->item[ 'ufae_' . $side . '_title' ] : false;

			if ( $title ) {
				$title_tag = isset( $this->settings['ufae_title_tag'] ) && ! empty( $this->settings['ufae_title_tag'] ) ? $this->settings['ufae_title_tag'] : 'h3';
				echo '<' . esc_html( $title_tag ) . ' class="ufae-title">' . esc_html( $title ) . '</' . esc_html( $title_tag ) . '>';
			}
		}

		/**
		 * Renders the description for the specified side of the flipbox.
		 *
		 * This method outputs the HTML for the description element.
		 *
		 * @param string $side The side of the flipbox to render the description for ('front' or 'back').
		 */
		private function render_desc( $side ) {
			$description = isset( $this->item[ 'ufae_' . $side . '_description' ] ) && ! empty( $this->item[ 'ufae_' . $side . '_description' ] ) ? $this->item[ 'ufae_' . $side . '_description' ] : false;

			if ( $description ) {
				echo '<p class="ufae-desc">' . esc_html( $description ) . '</p>';
			}
		}

		/**
		 * Renders the button for the specified side of the flipbox.
		 *
		 * This method outputs the HTML for the button element, including the URL and attributes.
		 *
		 * @param string $side The side of the flipbox to render the button for ('front' or 'back').
		 */
		private function render_button( $side ) {
			$button_enable = isset( $this->item[ 'ufae_' . $side . '_button_enable' ] ) ? esc_html( $this->item[ 'ufae_' . $side . '_button_enable' ] ) : 'no';
			$btn_text      = isset( $this->item[ 'ufae_' . $side . '_button_text' ] ) && ! empty( $this->item[ 'ufae_' . $side . '_button_text' ] ) ? $this->item[ 'ufae_' . $side . '_button_text' ] : false;

			if ( $btn_text && 'yes' === $button_enable ) {
				$btn_url_setting = isset( $this->item[ 'ufae_' . $side . '_button_url' ] ) && ! empty( $this->item[ 'ufae_' . $side . '_button_url' ] ) ? $this->item[ 'ufae_' . $side . '_button_url' ] : false;
				$btn_url         = isset( $btn_url_setting['url'] ) && ! empty( $btn_url_setting['url'] ) ? $btn_url_setting['url'] : false;

				echo '<div class="ufae-btn-wrapper">';
				if ( $btn_url ) {
					$blank_attr    = isset( $btn_url_setting['is_external'] ) && 'on' === $btn_url_setting['is_external'] ? '_blank' : '_self';
					$nofollow_attr = isset( $btn_url_setting['nofollow'] ) && 'on' === $btn_url_setting['nofollow'] ? 'nofollow' : '';
					$custom_attr   = isset( $btn_url_setting['custom_attributes'] ) && ! empty( $btn_url_setting['custom_attributes'] ) ? explode( '|', $btn_url_setting['custom_attributes'] ) : array();

					$custom_attr = count( $custom_attr ) > 1 ? esc_html( $custom_attr[0] ) . '="' . esc_attr( $custom_attr[1] ) . '"' : '';

					echo '<a href="' . esc_url( $btn_url ) . '" class="ufae-button" target="' . esc_attr( $blank_attr ) . '" rel="' . esc_attr( $nofollow_attr ) . '" ' . trim( $custom_attr ) . '>' . esc_html( $btn_text ) . '</a>';
				} else {
					echo '<button class="ufae-button">' . esc_html( $btn_text ) . '</button>';
				}
				echo '</div>';
			}
		}

		/**
		 * Updates the order of elements for the front and back sides of the flipbox.
		 *
		 * This method processes the settings to determine the order in which elements are displayed.
		 */
		private function update_element_order() {
			$sides              = array( 'front', 'back' );
			$predefined_element = array( 'icon', 'title', 'desc', 'button' );

			foreach ( $sides as $side ) {
				$order_settings = $this->settings[ 'ufae_' . $side . '_element_position' ];
				$order_settings = esc_html( trim( $order_settings ) );
				$new_order      = array();

				if ( ! empty( $order_settings ) && preg_match( '/\b(icon|title|desc|button)\b/', $order_settings ) ) {
					$element_names = array_unique( array_filter( explode( ',', $order_settings ) ) );

					foreach ( $element_names as $element_name ) {
						if ( in_array( $element_name, $predefined_element, true ) ) {
							$new_order[] = $element_name;
						}
					}

					$new_order = array_merge( $new_order, array_diff( $predefined_element, $new_order ) );

					$this->{$side . '_element_order'} = $new_order;
				}
			}
		}
	}

}
