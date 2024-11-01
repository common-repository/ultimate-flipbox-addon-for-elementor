<?php

namespace Ultimate_Flipbox_Addon_For_Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! class_exists( 'Ufae_Editor_Loop' ) ) {
	/**
	 * Class Ufae_Editor_Loop
	 *
	 * This class handles the editor loop for the Ultimate Flipbox Addon for Elementor.
	 * It is responsible for managing the flipbox items and rendering their content.
	 *
	 * @package Ultimate_Flipbox_Addon_For_Elementor
	 */
	class Ufae_Editor_Loop {

		/**
		 * Renders the flipbox items for the editor.
		 *
		 * This function checks if there are any flipbox items available in the settings.
		 * If items are present, it iterates through each item and renders the front and back
		 * sides of the flipbox based on the configuration.
		 *
		 * @return void
		 */
		public function flipbox_items() {
			?>
			<#

			const listItems=settings.ufae_lists;
			const horizontal_item_class = horizontal_layout ? ' swiper-slide' : '';
			
			if (Array.isArray(listItems) && listItems.length> 0) { #>
				<# _.each(listItems, (item)=> { 
					const backEnabled=item.ufae_back_enable && item.ufae_back_enable==='yes';
					const backDisableCls=backEnabled ? '' : ' ufae-flipbox-front_only'
				#>
				<div class="ufae-flipbox-item elementor-repeater-item-{{{item['_id']}}}{{{horizontal_item_class}}}{{{backDisableCls}}}">
					<div class="ufae-flipbox-inner">
						<div class="ufae-flipbox-inner-overlay">
							<?php
							$this->render_sides_content( 'front', '' );
							?>
							<# if ( 'curtain' === animation && backEnabled ) { #>
								<?php
								$this->render_sides_content( 'front', ' ufae-front_duplicate' );
								?>
							<# } 
							if ( 'curtain' === animation && backEnabled ) { #>
								<?php
								$this->render_sides_content( 'front', ' ufae-front-duplicate_overlay' );
								?>
							<# } 
							if (backEnabled) { #>
								<?php
								$this->render_sides_content( 'back', '' );
								?>
							<# } #>
						</div>
					</div>
				</div>
			<# }); #>
			<# } #>
			<?php
		}

		/**
		 * Renders the content for the specified side of the flipbox.
		 *
		 * This function generates the HTML and JavaScript necessary to display the content
		 * for either the front or back side of the flipbox, based on the provided side parameter.
		 *
		 * @param string $side The side of the flipbox to render ('front' or 'back').
		 * @return void
		 */
		private function render_sides_content( $side, $wrp_cls = '' ) {
			?>
			<#
				let render_side='' ;
			#>
			<?php
			if ( 'front' === $side ) {
				?>
			<#
			render_side='front' ;
				#>
			<?php } elseif ( 'back' === $side ) { ?>
				<#
				render_side='back' ;
					#>
				<?php
			}
			?>
			<#
			<!-- Change elemenet orders - START -->
				var order_settings=settings['ufae_' + render_side + '_element_position' ];
				var predefined_order=new Array('icon', 'title' , 'desc' , 'button' );

				var element_order=[];

				if (order_settings && order_settings.match(/\b(icon|title|desc|button)\b/)) {
				var element_names=Array.from(new Set(order_settings.split(',').filter(Boolean)));

				_.each(element_names, (element_name)=>{
				if (predefined_order.includes(element_name)) {
				element_order.push(element_name);
				}
				});

				}

				_.each(predefined_order, (element) => {
				if (!element_order.includes(element)) {
				element_order.push(element);
				}
				});
				<!-- Change elemenet orders - END -->

				<!-- Elements render according to element order - START -->
				#>
				<div class="ufae-flipbox-{{{render_side}}} <?php echo esc_attr( $wrp_cls ); ?>">
					<div class="ufae-flipbox-content-overlay">
						<div class="ufae-flipbox-content">
							<#
							_.each(element_order, (element)=>{
								const element_enable = ('no' === settings[ 'ufae_' + render_side + '_' + element + '_enable' ] || '' === settings[ 'ufae_' + render_side + '_' + element + '_enable' ]) ? false : true;

								if ( false == element_enable ) {
									return;
								}

								switch (element) {
									case 'icon':
									#>
									<?php
									$this->render_icon( $side );
									?>
									<#
									break;
									case 'title' :
									#>
									<?php
									$this->render_title( $side );
									?>
									<#
									break;
									case 'desc' :
									#>
									<?php
									$this->render_desc( $side );
									?>
									<#
									break;
									case 'button' :
									#>
									<?php
									$this->render_button( $side );
									?>
									<#
									break;
								}
							})
							#>

						</div>
					</div>
				</div>
				<#
					<!-- Elements render according to element order - END -->
				#>
			<?php
		}

		/**
		 * Renders the icon for the flipbox.
		 *
		 * This method outputs the icon based on the specified side (front or back).
		 * It checks the icon type and renders the appropriate HTML for the icon.
		 *
		 * @param string $side The side of the flipbox ('front' or 'back').
		 * @return void
		 */
		private function render_icon( $side ) {
			?>
			<#
			var side='' ;
			#>
			<?php
			if ( 'front' === $side ) {
				?>
			<#
				var side='front' ;
			#>
			<?php } elseif ( 'back' === $side ) { ?>
			<#
				var side='back' ;
			#>
				<?php
			}
			?>
			<# 
			const iconType= item['ufae_' + side + '_icon_type'] && '' !== item['ufae_' + side + '_icon_type'] ? item['ufae_' + side + '_icon_type'] : false;

			if (iconType) {
				if (iconType === 'icon') { #>
					<div class="ufae-icon-wrapper">
					<#
					const iconHtml=elementor.helpers.renderIcon( view, item['ufae_'+side+'_icon'], { 'aria-hidden': true, 'class': 'ufae-icon' }, 'i' , 'object' );
					#>
					{{{iconHtml.value}}}
					</div>
				<# 
				} else if (iconType === 'image' && item['ufae_' + side + '_icon_image'] && '' !== item['ufae_' + side + '_icon_image']['url']) { #>
					<div class="ufae-icon-wrapper">
					<img src="{{{item['ufae_' + side + '_icon_image']['url']}}}" alt="{{{item['ufae_' + side + '_title']}}}">
					</div>
				<# 
				} else if (iconType === 'text') { #>
					<div class="ufae-icon-wrapper">
					<span>{{{item['ufae_' + side + '_icon_text']}}}</span>
					</div>
				<# 
				}
			}
			#>
				<?php
		}

		/**
		 * Renders the title for the specified side of the flipbox.
		 *
		 * This method checks the side parameter ('front' or 'back') and outputs
		 * the corresponding title if it exists in the item data.
		 *
		 * @param string $side The side of the flipbox ('front' or 'back').
		 * @return void
		 */
		private function render_title( $side ) {
			?>
			<#
				var side='' ;
			#>
			<?php
			if ( 'front' === $side ) {
				?>
			<#
				var side='front' ;
			#>
			<?php } elseif ( 'back' === $side ) { ?>
			<#
				var side='back' ;
			#>
				<?php
			}
			?>
			<#

			const title = item['ufae_' + side + '_title'] && '' !== item['ufae_' + side + '_title'] ? item['ufae_' + side + '_title'] : false;

			if (title) {
				const title_tag = settings.ufae_title_tag && '' !== settings.ufae_title_tag ? settings.ufae_title_tag : 'h3';
			#>
				<{{{title_tag}}} class="ufae-title">{{title}}</{{{title_tag}}}>
			<#
			}
			#>
			<?php
		}
		/**
		 * Renders the description for the specified side of the flipbox.
		 *
		 * This method checks the side parameter ('front' or 'back') and outputs
		 * the corresponding description if it exists in the item data.
		 *
		 * @param string $side The side of the flipbox ('front' or 'back').
		 * @return void
		 */
		private function render_desc( $side ) {
			?>
			<#
				var side='' ;
			#>
			<?php
			if ( 'front' === $side ) {
				?>
			<#
				var side='front' ;
			#>
			<?php } elseif ( 'back' === $side ) { ?>
			<#
				var side='back' ;
			#>
				<?php
			}
			?>
			<#
			const description = item['ufae_' + side + '_description'] && '' !== item['ufae_' + side + '_description'] ? item['ufae_' + side + '_description'] : false;

			if (description) { #>
				<p class="ufae-desc">{{{description}}}</p>
			<# } #>
			<?php
		}
		/**
		 * Renders the button for the specified side of the flipbox.
		 *
		 * This method checks the side parameter ('front' or 'back') and outputs
		 * the corresponding button if it exists in the item data.
		 *
		 * @param string $side The side of the flipbox ('front' or 'back').
		 * @return void
		 */
		private function render_button( $side ) {
			?>
			<#
				var side='' ;
			#>
			<?php
			if ( 'front' === $side ) {
				?>
			<#
				var side='front' ;
			#>
			<?php } elseif ( 'back' === $side ) { ?>
			<#
				var side='back' ;
			#>
				<?php
			}
			?>
			<#
			const button_enable = item['ufae_' + side + '_button_enable'] ? item['ufae_' + side + '_button_enable'] : 'no';
			const btn_text = item['ufae_' + side + '_button_text'] && '' !== item['ufae_' + side + '_button_text'] ? item['ufae_' + side + '_button_text'] : false;

			if (btn_text && 'yes' === button_enable) {
				const btn_url_setting = item['ufae_' + side + '_button_url'] && '' !== item['ufae_' + side + '_button_url'] ? item['ufae_' + side + '_button_url'] : false;
				const btn_url = btn_url_setting['url'] && '' !== btn_url_setting['url'] ? btn_url_setting['url'] : false;
				#>
				<div class="ufae-btn-wrapper">
				<#
				if (btn_url) {
					const blank_attr = btn_url_setting['is_external'] && 'on' === btn_url_setting['is_external'] ? '_blank' : '_self';
					const nofollow_attr = btn_url_setting['nofollow'] && 'on' === btn_url_setting['nofollow'] ? 'nofollow' : '';
					var custom_attr = btn_url_setting['custom_attributes'] && '' !== btn_url_setting['custom_attributes'] ? btn_url_setting['custom_attributes'].split('|') : [];
					custom_attr = custom_attr.length > 1 ? custom_attr[0] + '="' + custom_attr[1] + '"' : '';
					#>
					<a href="{{{btn_url}}}" class="ufae-button" target="{{{blank_attr}}}" rel="{{{nofollow_attr}}}"  {{{custom_attr}}}>{{{btn_text}}}</a>
				<# } else { #>
					<button class="ufae-button">{{{btn_text}}}</button>
				<# } #>
				</div>
			<# } #>
			<?php
		}
	}
}
