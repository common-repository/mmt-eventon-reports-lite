<?php
/**
 * MMT EventON - Reports - Views Related Queries
 *
 * @package mmt-eo-reports
 * @author MoMo Themes
 * @since v1.0
 */
class MMT_EO_Reports_Views {
	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'eventon_single_content', array( $this, 'mmt_eo_reports_add_count_to_single_page' ), 10 );

		add_action( 'eventon_enqueue_styles', array( $this, 'mmt_eo_reports_add_styles_script' ) );

		$ajax_events = array(
			'mmt_eo_reports_add_count_from_event_top' => 'mmt_eo_reports_add_count_from_event_top',
		);
		foreach ( $ajax_events as $ajax_event => $class ) {
			add_action( 'wp_ajax_' . $ajax_event, array( $this, $class ) );
			add_action( 'wp_ajax_nopriv_' . $ajax_event, array( $this, $class ) );
		}
	}
	/**
	 * Add counter to single page view
	 */
	public function mmt_eo_reports_add_count_to_single_page() {
		$repeat_interval = 0;
		if ( isset( $_GET['ri'] ) ) {
			$repeat_interval = (int) sanitize_text_field( wp_unslash( $_GET['ri'] ) );
		}
		if ( isset( $wp_query->query['var'] ) ) {
			$_url_var = $wp_query->query['var'];
			$url_var  = explode( '.', $_url_var );
			$vars     = array();
			foreach ( $url_var as $var ) {
				$split = explode( '-', $var );
				if ( 'ri' === $split[0] ) {
					$repeat_interval = (int) $split[1];
				}
			}
		}
		$key     = '_mer_single_page_view';
		$post_id = get_the_ID();
		$counter = (array) get_post_meta( $post_id, $key, true );
		$total   = (int) isset( $counter[ $repeat_interval ] ) ? $counter[ $repeat_interval ] : 0;
		$total++;
		$counter[ $repeat_interval ] = $total;
		update_post_meta( $post_id, $key, $counter );
	}
	/**
	 * Add counter that comes from event top.
	 */
	public function mmt_eo_reports_add_count_from_event_top() {
		check_ajax_referer( 'mmt_eo_reports_nonce', 'mmteor_nonce' );
		if ( isset( $_POST['action'] ) && 'mmt_eo_reports_add_count_from_event_top' !== $_POST['action'] ) {
			return;
		}
		$event_id        = isset( $_POST['event_id'] ) ? sanitize_text_field( wp_unslash( $_POST['event_id'] ) ) : '';
		$repeat_interval = isset( $_POST['repeat_interval'] ) ? sanitize_text_field( wp_unslash( $_POST['repeat_interval'] ) ) : '';

		$key     = '_mer_event_top_view';
		$post_id = $event_id;
		$counter = (array) get_post_meta( $post_id, $key, true );
		$total   = (int) isset( $counter[ $repeat_interval ] ) ? $counter[ $repeat_interval ] : 0;
		$total++;
		$counter[ $repeat_interval ] = $total;
		update_post_meta( $post_id, $key, $counter );
		echo wp_json_encode(
			array(
				'status'  => 'good',
				'counter' => $counter,
			)
		);
		exit;
	}
	/**
	 * Load styles and scripts on evnton pages
	 */
	public function mmt_eo_reports_add_styles_script() {
		global $mmt_eo_reports;
		wp_enqueue_script( 'mmt_eo_reports_script', $mmt_eo_reports->plugin_url . 'assets/js/mmt_eo_reports_fe.js', array( 'jquery' ), $mmt_eo_reports->version, true );

		$ajaxurl = array(
			'ajaxurl'              => admin_url( 'admin-ajax.php' ),
			'mmt_eo_reports_nonce' => wp_create_nonce( 'mmt_eo_reports_nonce' ),
		);
		wp_localize_script( 'mmt_eo_reports_script', 'mmt_eo_reports', $ajaxurl );
	}
}
