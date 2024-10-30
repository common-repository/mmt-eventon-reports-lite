<?php
/**
 * Admin Script and Style for EventON - Reports
 *
 * @package mmt-eo-reports
 * @author MoMo Themes
 * @since v1.0
 */
class MMT_EO_Reports_Admin_Script_Style {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'admin_enqueue_scripts', array( $this, 'mmt_eo_reports_register_style_scripts' ) );

		add_action( 'admin_print_styles-eventon_page_mmt_eo_reports', array( $this, 'mmt_eo_reports_print_admin_styles' ) );
		add_action( 'admin_print_scripts-eventon_page_mmt_eo_reports', array( $this, 'mmt_eo_reports_print_admin_scripts' ) );
	}

	/**
	 * Enqueue Admin Script and Styles
	 */
	public function mmt_eo_reports_register_style_scripts() {
		global $mmt_eo_reports;
		wp_register_style( 'mmt_eo_reports_admin_style', $mmt_eo_reports->plugin_url . 'assets/css/mmt_eo_reports_admin.css', array(), $mmt_eo_reports->version );
		wp_register_style( 'mmt_eo_reports_admin_chart_style', $mmt_eo_reports->plugin_url . 'assets/css/Chart.min.css', array(), $mmt_eo_reports->version );
		wp_register_script( 'mmt_eo_reports_admin_chart_script', $mmt_eo_reports->plugin_url . 'assets/js/chart.min.js', array( 'jquery' ), '3.2.1', true );
		wp_register_script( 'mmt_eo_reports_admin_script', $mmt_eo_reports->plugin_url . 'assets/js/mmt_eo_reports_admin.js', array( 'jquery', 'jquery-ui-datepicker' ), $mmt_eo_reports->version, true );
	}
	/**
	 * Print Admin Styles
	 */
	public function mmt_eo_reports_print_admin_styles() {
		wp_enqueue_style( 'mmt_eo_reports_admin_chart_style' );
		wp_enqueue_style( 'mmt_eo_reports_admin_style' );
	}
	/**
	 * Print admin scripts
	 */
	public function mmt_eo_reports_print_admin_scripts() {
		wp_enqueue_script( 'mmt_eo_reports_admin_chart_script' );
		wp_enqueue_script( 'mmt_eo_reports_admin_script' );
		$ajaxurl = array(
			'ajaxurl'                   => admin_url( 'admin-ajax.php' ),
			'date_format'               => $this->date_format_php_to_js( get_option( 'date_format' ) ),
			'mmt_eo_reports_ajax_nonce' => wp_create_nonce( 'mmt_eo_reports_security_key' ),
		);
		wp_localize_script( 'mmt_eo_reports_admin_script', 'mmt_eo_reports_admin', $ajaxurl );
	}

	/**
	 * Date Format to JS
	 *
	 * @param string $wp_format WP Date Format.
	 */
	public function date_format_php_to_js( $wp_format ) {
		switch ( $wp_format ) {
			case 'F j, Y':
				return( 'MM dd, yy' );
			case 'Y/m/d':
				return( 'yy/mm/dd' );
			case 'm/d/Y':
				return( 'mm/dd/yy' );
			case 'd/m/Y':
				return( 'dd/mm/yy' );
			case 'default':
				return ( 'dd/mm/yy' );
		}
	}
}
