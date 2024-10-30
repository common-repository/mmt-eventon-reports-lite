<?php
/**
 * MMT EventON - Reports - Search Related Queries
 *
 * @package mmt-eo-reports
 * @author MoMo Themes
 * @since v1.0
 */
class MMT_EO_Reports_Search {
	/**
	 * Table Name.
	 *
	 * @var string
	 */
	private $table_name;
	/**
	 * Database Version.
	 *
	 * @var string
	 */
	private $mmt_eo_search_db_version;
	/**
	 * Constructor
	 */
	public function __construct() {
		global $wpdb;
		$this->mmt_eo_search_db_version = '1.0';
		$this->table_name               = $wpdb->prefix . 'mmt_eo_reports_search';

		add_action( 'pre_get_posts', array( $this, 'mmt_eo_reports_store_search_keywords' ), 15, 1 );

		$ajax_events = array(
			'mmt_eo_reports_ajax_store_keyword_to_db' => 'mmt_eo_reports_ajax_store_keyword_to_db',
		);
		foreach ( $ajax_events as $ajax_event => $class ) {
			add_action( 'wp_ajax_' . $ajax_event, array( $this, $class ) );
			add_action( 'wp_ajax_nopriv_' . $ajax_event, array( $this, $class ) );
		}
	}
	/**
	 * Get Table name.
	 */
	public function mmt_eo_reports_get_table_name() {
		return $this->table_name;
	}
	/**
	 * Database check and install
	 */
	public function mmt_eo_reports_search_db_check_install() {
		if ( get_site_option( 'mmt_eo_search_db_version' ) !== $this->mmt_eo_search_db_version ) {
			$this->mmt_eo_reports_search_log_db_install();
		}
	}
	/**
	 * Install Database for Search Log.
	 */
	public function mmt_eo_reports_search_log_db_install() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $this->table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			keyword tinytext NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
		add_option( 'mmt_eo_search_db_version', $this->mmt_eo_search_db_version );
	}
	/**
	 * Store keyword to database
	 *
	 * @param WP_Query $query WP Query.
	 */
	public function mmt_eo_reports_store_search_keywords( $query ) {
		if ( is_admin() ) {
			return $query;
		}
		if ( empty( $query->is_search ) ) {
			return $query;
		}
		if ( $query->is_search ) {
			if ( ! isset( $query->query_vars['post_type'] ) ) {
				return $query;
			}
			if ( is_array( $query->query_vars['post_type'] ) && in_array( 'ajde_events', $query->query_vars['post_type'], true ) ) {
				$keywords = $query->query_vars['s'];
				$this->mmt_eo_reports_store_keywords_to_db( $keywords );
			}
			return $query;
		}
		return $query;
	}
	/**
	 * Ajax call to store keyword
	 */
	public function mmt_eo_reports_ajax_store_keyword_to_db() {
		check_ajax_referer( 'mmt_eo_reports_nonce', 'mmteor_nonce' );
		if ( isset( $_POST['action'] ) && 'mmt_eo_reports_ajax_store_keyword_to_db' !== $_POST['action'] ) {
			return;
		}
		$keyword = isset( $_POST['keyword'] ) ? sanitize_text_field( wp_unslash( $_POST['keyword'] ) ) : '';
		if ( empty( $keyword ) ) {
			echo wp_json_encode(
				array(
					'status'  => 'good',
					'message' => esc_html__( 'Empty keyword provided', 'mmt_eo_reports' ),
				)
			);
			exit;
		} else {
			$this->mmt_eo_reports_store_keywords_to_db( $keyword );
			echo wp_json_encode(
				array(
					'status'  => 'good',
					'message' => $keyword . ' - ' . esc_html__( 'added to search log.', 'mmt_eo_reports' ),
				)
			);
			exit;
		}
	}
	/**
	 * Store Search keywords to database.
	 *
	 * @param string $keywords Keywords.
	 */
	public function mmt_eo_reports_store_keywords_to_db( $keywords ) {
		global $wpdb;
		$wpdb->insert(
			$this->table_name,
			array(
				'time'    => current_time( 'mysql' ),
				'keyword' => $keywords,
			)
		);
	}
	/**
	 * Gets Search log total count
	 */
	public function mmt_eo_reports_get_search_log_count() {
		global $mmt_eo_reports, $wpdb;
		$total = wp_cache_get( 'mmt_eo_reports_search_log_count' );

		if ( false === $total ) {
			$table_name = $mmt_eo_reports->search->mmt_eo_reports_get_table_name();
			$total      = $wpdb->get_var(
				$wpdb->prepare(
					'SELECT COUNT(*)
					FROM %1s',
					$table_name
				)
			);
			wp_cache_set( 'mmt_eo_reports_search_log_count', $total );
		}

		return $total;
	}
	/**
	 * Return Logs Result
	 *
	 * @param string $table_name Table Name.
	 * @param int    $start Limit Start.
	 * @param int    $end Limit End.
	 */
	public function mmt_eo_reports_get_search_logs( $table_name, $start, $end ) {
		global $mmt_eo_reports, $wpdb;
		$search_logs = wp_cache_get( 'mmt_eo_reports_search_logs' );
		if ( false === $search_logs ) {
			$search_logs = $wpdb->get_results(
				$wpdb->prepare(
					'SELECT *
					FROM %1s
					ORDER BY time DESC
					LIMIT %d, %d',
					$table_name,
					$start,
					$end
				)
			);
			wp_cache_set( 'mmt_eo_reports_search_logs', $search_logs );
		}
		return $search_logs;
	}
}
