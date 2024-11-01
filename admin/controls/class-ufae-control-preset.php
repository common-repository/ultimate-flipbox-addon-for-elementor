<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Elementor\Base_Data_Control;

if ( ! class_exists( 'Ufae_Control_Preset' ) ) {
	/**
	 * Class Ufae_Control_Preset
	 *
	 * This class handles the custom control for Elementor presets.
	 *
	 * @since 1.0.0
	 */
	class Ufae_Control_Preset extends \Elementor\Base_Data_Control {

		/**
		 * Get the control type.
		 *
		 * @return string Control type.
		 */
		public function get_type() {
			return 'ufae_design_control';
		}

		/**
		 * Get default settings for the control.
		 *
		 * @return array Default settings.
		 */
		protected function get_default_settings() {
			return array(
				'label_block' => false,
				'multiple'    => false,
				'options'     => array(),
			);
		}

		/**
		 * Enqueue the control's scripts and styles.
		 */
		public function enqueue() {
			$ext = '.min';
			wp_register_style( 'ufae-control-style', UFAE_URL . 'admin/controls/assets/css/ufae-control' . $ext . '.css', array(), UFAE_VERSION );
			wp_register_script( 'ufae-control-script', UFAE_URL . 'admin/controls/assets/js/ufae-control' . $ext . '.js', array( 'jquery' ), UFAE_VERSION );
			wp_register_script( 'ufae-tabs-control-handler', UFAE_URL . 'admin/controls/assets/js/ufae-tabs-control-handler' . $ext . '.js', array( 'jquery' ), UFAE_VERSION );

			wp_enqueue_style( 'ufae-control-style' );
			wp_enqueue_script( 'ufae-control-script' );
			wp_enqueue_script( 'ufae-tabs-control-handler' );

			wp_localize_script(
				'ufae-control-script',
				'ufae_preset_control',
				array(
					'ajaxUrl' => admin_url( 'admin-ajax.php' ),
					'nonce'   => wp_create_nonce( 'ufae_preset_nonce' ),
				)
			);
		}

		/**
		 * Render the control's content template.
		 */
		public function content_template() {
			$control_uid = $this->get_control_uid();
			?>
			<div class="ufae-control">
				<div class="ufae-settings">
					<label for="<?php echo esc_attr( $control_uid ); ?>" class="elementor-control-title">{{{ data.label }}}</label>
					<div>
						<select id="<?php echo esc_attr( $control_uid ); ?>" class="elementor-select2" type="select2" {{ multiple }} data-setting="{{ data.name }}">
							<# _.each( data.options, function( option, key ) { #>
								<option value="{{ key }}">{{ option }}</option>
								<# }); #>
						</select>
					</div>
				</div>
				<div class="ufae-control-message">
					<p>
						{{{data.message}}}
					</p>
				</div>
			</div>
			<?php
		}
	}
}
