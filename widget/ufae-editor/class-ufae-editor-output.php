<?php

namespace Ultimate_Flipbox_Addon_For_Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Ultimate_Flipbox_Addon_For_Elementor\Ufae_Editor_Loop;
if ( ! class_exists( 'Ufae_Editor_Output' ) ) {
	/**
	 * Class Ufae_Editor_Output
	 *
	 * Handles the output rendering for the Ultimate Flipbox Addon for Elementor.
	 */
	class Ufae_Editor_Output {

		/**
		 * Instance of the Ufae_Editor_Loop class.
		 *
		 * This property holds the loop object responsible for rendering
		 * the flipbox items in the editor output.
		 *
		 * @var Ufae_Editor_Loop
		 */
		private $loop_obj;

		/**
		 * Ufae_Editor_Output constructor.
		 * Initializes the loop object.
		 */
		public function __construct() {
			$this->loop_obj = new Ufae_Editor_Loop();
		}

		/**
		 * Renders the output for the flipbox items.
		 *
		 * @return void
		 */
		public function render() {
			?>
		<div class="ufae-wrapper">
			<#
				var widgetId=settings.widget_id;
				var layout=settings.ufae_layout_option;
				const horizontal_layout = 'horizontal' === layout;
				var animation=settings.ufae_animation_option ? settings.ufae_animation_option : 'flip' ;
				let animation_dir=settings.ufae_flip_direction && '' !== settings.ufae_flip_direction ? '-'+settings.ufae_flip_direction : '-left' ;
				var transition_time=settings.ufae_transition_duration ? settings.ufae_transition_duration : '1000' ;
				
				animation_dir              = 'flip' === animation ? animation_dir : '';
				
				view.addRenderAttribute( 'ufae_container' , {'class': ['ufae-container','ufae-layout-'+layout],'data-ufae-animation':animation+animation_dir,'data-ufae-transition':transition_time});
				
				if(horizontal_layout){
					
					const horizontal_container_class = horizontal_layout ? 'ufae_horizontal_container' : '';
					
					view.addRenderAttribute( 'ufae_container' , {'class': horizontal_container_class,'data-ufae-slideview':settings.ufae_hr_slider_perview_control || 2});
				}

				#>
				<div {{{ view.getRenderAttributeString( "ufae_container" ) }}}>
				<# if ( horizontal_layout ) { #>
					<div class="ufae-swiper-container"><div class="swiper-wrapper">
				<# } #>

				<?php
				$this->loop_obj->flipbox_items();
				?>

				<# if ( horizontal_layout ) { #>
					</div></div>
				<# } #>

				</div>

				<# if ( horizontal_layout ) { #>
					<div class="swiper-button-next"></div>
					<div class="swiper-button-prev"></div>
					<div class="ufae-swiper-pagination"></div>
				<# } #>
		</div>

			<?php
		}
	}

}
