<?php
	/*
	Plugin Name: CBX Booking Sample Plugin
	Plugin URI: https://codeboxr.com
	Description: Sample plugin with example filter and action for CBX Restaurant Booking
	Author: Codeboxr
	Version: 1.0.1
	Author URI: https://codeboxr.com
	Text Domain: cbxrbookingsampleplugin
	Domain Path: /languages/
	*/

	// If this file is called directly, abort.
	if (!defined('WPINC')) {
		die;
	}


	class CBXRBookingSamplePlugin{

		public function __construct()
		{
			//load text domain
			load_plugin_textdomain('cbxrbookingsampleplugin', false, dirname(plugin_basename(__FILE__)) . '/languages/');

			//disable form id col in frontend log listing
			add_filter('cbxrbooking_frontendlog_tableheading', array($this, 'cbxrbooking_frontendlog_tableheading_remove_formidcol'), 10, 2);
			add_filter('cbxrbooking_frontendlog_table_colcount', array($this, 'cbxrbooking_frontendlog_table_colcount_remove_formid'));


			//adding a new field in frontendlog plugin [this is for frontendlog addon, not for frontend default booking form of core plugin] booking form
			//log table name dbprefix_cbxrbooking_log_manager
			//log table has an extra col named "metadata"  which data is stored as seriliazed, this field can be used for storing extra informtion
			add_action('cbxrbooking_frontendlog_logform_end', array($this, 'cbxrbooking_frontendlog_logform_end_select_field'), 10, 3); //frontend log listing booking form
			add_action('cbxrbooking_public_logform_end', array($this, 'cbxrbooking_frontendlog_logform_end_select_field'), 10, 3); // core plugin frontend form from shortcode or widget
			add_action('cbxrbooking_admin_logform_end', array($this, 'cbxrbooking_frontendlog_logform_end_select_field'), 10, 3); // core plugin booking form admin part

			add_filter('cbxrbooking_admin_form_meta_data_before_update', array($this, 'cbxrbooking_form_meta_data_before_insert_select_field'), 10, 5); //core plugin before update meta process
			add_filter('cbxrbooking_admin_form_meta_data_before_insert', array($this, 'cbxrbooking_form_meta_data_before_insert_select_field'), 10, 5); //core plugin before insert meta process

			add_filter('cbxrbooking_frontendlog_form_meta_data_before_update', array($this, 'cbxrbooking_form_meta_data_before_insert_select_field'), 10, 5); //frontend log listing addon before update meta process
			add_filter('cbxrbooking_frontendlog_form_meta_data_before_insert', array($this, 'cbxrbooking_form_meta_data_before_insert_select_field'), 10, 5); //frontend log listing addon before insert meta process
			add_filter('cbxrbooking_form_meta_data_before_insert', array($this, 'cbxrbooking_form_meta_data_before_insert_select_field'), 10, 5);

			//adding extra col in frontend log listing
			add_action('cbxrbooking_frontendlog_tableheading_extra', array($this, 'cbxrbooking_frontendlog_tableheading_extra_col'));
			add_action('cbxrbooking_frontendlog_tablecol_extra', array($this, 'cbxrbooking_frontendlog_tablecol_extra_col'));

		}


		/**
		 * Extra column information
		 *
		 * @param $booking_log_data
		 */
		public function cbxrbooking_frontendlog_tablecol_extra_col($booking_log_data){

			$meta_data = $booking_log_data['metadata'];
			$meta_data = maybe_unserialize($meta_data);

			//var_dump($booking_log_data);

			echo '<td>';
			if(isset($meta_data['cbxrb_customfield'])){
				echo $meta_data['cbxrb_customfield'];
			}
			echo '</td>';
		}

		/**
		 * Extra column heading
		 *
		 * @param $cbxrbooking_frontendlog_tableheading
		 */
		public function cbxrbooking_frontendlog_tableheading_extra_col($cbxrbooking_frontendlog_tableheading){
			?>
			<th><?php esc_html_e( 'Custom Column', 'cbxrbookingsampleplugin' ); ?></th>
			<?php
		}

		/**
		 * Add a select choice field in frontendlog booking form
		 *
		 * @param $form_id
		 * @param $booking_id
		 */
		public function cbxrbooking_frontendlog_logform_end_select_field($form_id, $booking_id, $log_data){

			//var_dump($log_data);
			if(isset($log_data->metadata)){
				$meta_data             = $log_data->metadata;
				$meta_data             = maybe_unserialize( $meta_data );
			}
			else{
				$meta_data = array();
			}

			$cbxrb_customfield = '';
			if(isset($meta_data['cbxrb_customfield'])){
				$cbxrb_customfield = $meta_data['cbxrb_customfield'];
			}

			?>
			<div class="form-group">
				<label for="cbxrb_customfield"
				       class="cbxrb-label col-sm-2 control-label"><?php esc_html_e( 'Custom Choice', 'cbxrbookingsampleplugin' ) ?></label>
				<div class="cbxrb-input-wrapper col-sm-4 cbxrbooking-error-msg-show">
					<select class="form-control" name="cbxrb_customfield" id="cbxrb_customfield">
						<option value="" <?php selected($cbxrb_customfield, ''); ?>><?php esc_html_e('Select Option', 'cbxrbookingsampleplugin'); ?></option>
						<option value="option-1" <?php selected($cbxrb_customfield, 'option-1'); ?>><?php esc_html_e('Option 1', 'cbxrbookingsampleplugin'); ?></option>
						<option value="option-2" <?php selected($cbxrb_customfield, 'option-2'); ?>><?php esc_html_e('Option 2', 'cbxrbookingsampleplugin'); ?></option>
					</select>
				</div>
			</div>
			<?php
		}

		/**
		 * Process extra field and saves in meta field
		 *
		 * @param $meta_data
		 * @param $post_data
		 * @param $form_id
		 * @param $booking_id
		 * @param $secret
		 *
		 * return array
		 */
		public function cbxrbooking_form_meta_data_before_insert_select_field($meta_data, $post_data, $form_id, $booking_id, $secret){


			$cbxrb_customfield        = isset( $post_data['cbxrb_customfield'] ) ? sanitize_text_field( $post_data['cbxrb_customfield'] ) : '';
			$meta_data['cbxrb_customfield'] = $cbxrb_customfield;


			return $meta_data;
		}

		/**
		 * Remove form id col
		 */
		public function cbxrbooking_frontendlog_tableheading_remove_formidcol($cols, $data){
			if(isset($cols['form_id'])){
				unset($cols['form_id']);
			}

			return $cols;
		}

		/**
		 * Decrease total col count as we remove form id
		 *
		 * @param $col_count
		 */
		public function cbxrbooking_frontendlog_table_colcount_remove_formid($col_count){
			$col_count--;
			return $col_count;
		}

	}

	add_action('plugins_loaded', 'cbxrbookingsampleplugin_init');

	function cbxrbookingsampleplugin_init(){
		//if the addon plugin is for frontend add we can also check for constant 'CBXRBOOKINGFRONTENDLOGADDON_PLUGIN_NAME'
		if (defined('CBXRBOOKING_PLUGIN_NAME')) {
			new CBXRBookingSamplePlugin();
		}
	}

