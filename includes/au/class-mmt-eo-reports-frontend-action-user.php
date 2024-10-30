<?php
/**
 * MMT EventON - Reports - Action User
 *
 * @package mmt-eo-reports
 * @author MoMo Themes
 * @since v1.3
 */
class MMT_EO_Reports_Frontend_Action_User {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'mmt_eo_reports_register_frontend_script_style' ) );

		add_filter( 'eventon_shortcode_popup', array( $this, 'mmt_eo_reports_add_shortcode_options' ), 10, 1 );

		add_shortcode( 'action_user_mmt_eo_reports', array( $this, 'mmt_eo_reports_action_user_frontend' ) );

	}
	/**
	 * Register Frontend script and style
	 */
	public function mmt_eo_reports_register_frontend_script_style() {
		global $mmt_eo_reports;
		wp_register_script( 'mmt_eo_reports_script', $mmt_eo_reports->plugin_url . 'assets/js/mmt_eo_reports_fe_au.js', array( 'jquery' ), $mmt_eo_reports->version, true );
		wp_register_style( 'mmt_eo_reports_style', $mmt_eo_reports->plugin_url . 'assets/css/mmt_eo_reports_fe_au.css', array(), $mmt_eo_reports->version );
	}
	/**
	 * Add shortcode option to eventon shortcode popup box
	 *
	 * @param array $shortcode_array Default array.
	 */
	public function mmt_eo_reports_add_shortcode_options( $shortcode_array ) {
		global $evo_shortcode_box;

		$new_shortcode_array = array(
			array(
				'id'        => 'mmt_eo_reports',
				'name'      => esc_html__( 'MMT - Action User Report', 'mmt-eo-reports' ),
				'code'      => 'action_user_mmt_eo_reports',
				'variables' => array(),
			),
		);

		return array_merge( $shortcode_array, $new_shortcode_array );
	}
	/**
	 * Action User Frontend Report
	 */
	public function mmt_eo_reports_action_user_frontend() {
		wp_enqueue_script( 'mmt_eo_reports_script' );
		wp_enqueue_style( 'mmt_eo_reports_style' );
	}
}
new MMT_EO_Reports_Frontend_Action_User();
