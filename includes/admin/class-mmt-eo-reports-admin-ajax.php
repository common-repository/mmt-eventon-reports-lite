<?php

/**
 * Eventon Reports Admin Ajax
 *
 * @package mmt-eo-reports
 * @author MoMo Themes
 * @since v1.0
 */
class MMT_EO_Reports_Admin_Ajax {
    /**
     * Constructor
     */
    public function __construct() {
        $ajax_events = array(
            'mmt_eo_report_get_upcoming_chart' => 'mmt_eo_report_get_upcoming_chart',
        );
        foreach ( $ajax_events as $ajax_event => $class ) {
            add_action( 'wp_ajax_' . $ajax_event, array($this, $class) );
            add_action( 'wp_ajax_nopriv_' . $ajax_event, array($this, $class) );
        }
    }

    /**
     * Generate Upcoming Chart
     */
    public function mmt_eo_report_get_upcoming_chart() {
        global $mmt_eo_reports;
        check_ajax_referer( 'mmt_eo_reports_security_key', 'mmt_eo_reports_nonce' );
        if ( isset( $_POST['action'] ) && 'mmt_eo_report_get_upcoming_chart' !== $_POST['action'] ) {
            return;
        }
        $type = ( isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'weekly' );
        $index = ( isset( $_POST['index'] ) ? sanitize_text_field( wp_unslash( $_POST['index'] ) ) : '0' );
        $events = $mmt_eo_reports->afn->mmt_eo_report_get_upcoming_events( $type, $index );
        $chart = $mmt_eo_reports->afn->mmt_eo_report_get_line_chart_data( $events['events'], $type, $index );
        $html = $mmt_eo_reports->afn->mmt_eo_report_get_events_list( $events['events'] );
        echo wp_json_encode( array(
            'status' => 'good',
            'result' => $chart['chart'],
            'title'  => $chart['title'],
            'label'  => esc_html__( 'No. of Event(s)', 'mmt-eo-reports' ),
            'html'   => $html,
        ) );
        exit;
    }

    /**
     * Generate Upcoming Events Views Count
     */
    public function mmt_eo_report_get_upcoming_views_count() {
        global $mmt_eo_reports;
        check_ajax_referer( 'mmt_eo_reports_security_key', 'mmt_eo_reports_nonce' );
        if ( isset( $_POST['action'] ) && 'mmt_eo_report_get_upcoming_views_count' !== $_POST['action'] ) {
            return;
        }
        $type = ( isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'weekly' );
        $index = ( isset( $_POST['index'] ) ? sanitize_text_field( wp_unslash( $_POST['index'] ) ) : '0' );
        $events = $mmt_eo_reports->afn->mmt_eo_report_get_upcoming_events( $type, $index );
        $html = $mmt_eo_reports->afn->mmt_eo_report_get_events_list_count( $events['events'] );
        echo wp_json_encode( array(
            'status' => 'good',
            'title'  => $events['title'],
            'label'  => esc_html__( 'No. of Event(s)', 'mmt-eo-reports' ),
            'html'   => $html,
        ) );
        exit;
    }

    /**
     * Get recent search log
     */
    public function mmt_eo_report_get_recent_search_log() {
        global $mmt_eo_reports, $wpdb;
        check_ajax_referer( 'mmt_eo_reports_security_key', 'mmt_eo_reports_nonce' );
        if ( isset( $_POST['action'] ) && 'mmt_eo_report_get_recent_search_log' !== $_POST['action'] ) {
            return;
        }
        $index = ( isset( $_POST['index'] ) ? sanitize_text_field( wp_unslash( $_POST['index'] ) ) : '0' );
        $page = $index + 1;
        $kw_per_page = 10;
        $start = ($page - 1) * $kw_per_page;
        $table_name = $mmt_eo_reports->search->mmt_eo_reports_get_table_name();
        $search_logs = $mmt_eo_reports->search->mmt_eo_reports_get_search_logs( $table_name, $start, $kw_per_page );
        $output = '';
        $total_logs = $mmt_eo_reports->search->mmt_eo_reports_get_search_log_count();
        if ( $total_logs <= 0 ) {
            $output .= '<tr>';
            $output .= '<td colspan="3" align="center">';
            $output .= esc_html__( 'No Search Log Found', 'mmt-eo-reports' );
            $output .= '</td>';
            $output .= '</tr>';
        } else {
            foreach ( $search_logs as $log ) {
                $output .= '<tr>';
                $output .= '<td>';
                $output .= $log->keyword;
                $output .= '</td>';
                $output .= '<td>';
                $output .= $log->time;
                $output .= '</td>';
                $output .= '</tr>';
            }
        }
        echo wp_json_encode( array(
            'status' => 'good',
            'html'   => $output,
        ) );
        exit;
    }

    /**
     * Get RSVP result
     */
    public function mmt_eo_report_get_upcoming_rsvp() {
        global $mmt_eo_reports;
        check_ajax_referer( 'mmt_eo_reports_security_key', 'mmt_eo_reports_nonce' );
        if ( isset( $_POST['action'] ) && 'mmt_eo_report_get_upcoming_rsvp' !== $_POST['action'] ) {
            return;
        }
        $type = ( isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'weekly' );
        $index = ( isset( $_POST['index'] ) ? sanitize_text_field( wp_unslash( $_POST['index'] ) ) : '0' );
        $events = $mmt_eo_reports->afn->mmt_eo_report_get_upcoming_events( $type, $index, 'rsvp' );
        $chart = $mmt_eo_reports->afn->mmt_eo_report_get_line_chart_data( $events['events'], $type, $index );
        $return = $mmt_eo_reports->afn->mmt_eo_report_get_rsvp_events_list( $events['events'] );
        $html = $return['html'];
        echo wp_json_encode( array(
            'status'   => 'good',
            'counter'  => $return['counter'],
            'result'   => $chart['chart'],
            'title'    => $chart['title'],
            'label'    => esc_html__( 'No. of Event(s)', 'mmt-eo-reports' ),
            'html'     => $html,
            'stepSize' => $chart['stepsize'],
        ) );
        exit;
    }

    /**
     * Get RSVP CSV data
     */
    public function mmt_eo_report_get_rsvp_csv_data() {
        global $mmt_eo_reports;
        wp_verify_nonce( 'mmt_eo_reports_security_key', 'mmt_eo_reports_nonce' );
        if ( isset( $_REQUEST['action'] ) && 'mmt_eo_report_get_rsvp_csv_data' !== $_REQUEST['action'] ) {
            return;
        }
        $type = ( isset( $_REQUEST['type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['type'] ) ) : 'all' );
        if ( 'single' === $type ) {
            $event_id = ( isset( $_REQUEST['event_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['event_id'] ) ) : '' );
            $ri = ( isset( $_REQUEST['ri'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ri'] ) ) : '0' );
            $selected = array($event_id . '_' . $ri);
        } else {
            $selected = ( isset( $_REQUEST['selected'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['selected'] ) ) : '' );
            $selected = explode( ',', $selected );
        }
        if ( empty( $selected ) ) {
            echo wp_json_encode( array(
                'status' => 'bad',
                'msg'    => esc_html__( 'Event not selected', 'mmt-eo-reports' ),
            ) );
            exit;
        }
        $mmt_eo_reports->afn->mmt_eo_reports_generate_rsvp_csv( $type, $selected );
    }

    /**
     * Get RSVP CSV data
     */
    public function mmt_eo_report_get_ticket_csv_data() {
        global $mmt_eo_reports;
        wp_verify_nonce( 'mmt_eo_reports_security_key', 'mmt_eo_reports_nonce' );
        if ( isset( $_REQUEST['action'] ) && 'mmt_eo_report_get_ticket_csv_data' !== $_REQUEST['action'] ) {
            return;
        }
        $type = ( isset( $_REQUEST['type'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['type'] ) ) : 'all' );
        if ( 'single' === $type ) {
            $event_id = ( isset( $_REQUEST['event_id'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['event_id'] ) ) : '' );
            $ri = ( isset( $_REQUEST['ri'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['ri'] ) ) : '0' );
            $selected = array($event_id . '_' . $ri);
        } else {
            $selected = ( isset( $_REQUEST['selected'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['selected'] ) ) : '' );
            $selected = explode( ',', $selected );
        }
        if ( empty( $selected ) ) {
            echo wp_json_encode( array(
                'status' => 'bad',
                'msg'    => esc_html__( 'Event not selected', 'mmt-eo-reports' ),
            ) );
            exit;
        }
        $mmt_eo_reports->afn->mmt_eo_reports_generate_ticket_csv( $type, $selected );
    }

    /**
     * Get Upcoming Ticket
     */
    public function mmt_eo_report_get_upcoming_ticket() {
        global $mmt_eo_reports;
        check_ajax_referer( 'mmt_eo_reports_security_key', 'mmt_eo_reports_nonce' );
        if ( isset( $_POST['action'] ) && 'mmt_eo_report_get_upcoming_ticket' !== $_POST['action'] ) {
            return;
        }
        $type = ( isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'weekly' );
        $index = ( isset( $_POST['index'] ) ? sanitize_text_field( wp_unslash( $_POST['index'] ) ) : '0' );
        $events = $mmt_eo_reports->afn->mmt_eo_report_get_upcoming_events( $type, $index, 'ticket' );
        $chart = $mmt_eo_reports->afn->mmt_eo_report_get_pie_chart_data( $events['events'], $type, $index );
        $return = $mmt_eo_reports->afn->mmt_eo_report_get_ticket_events_list( $events['events'] );
        $html = $return['html'];
        echo wp_json_encode( array(
            'status'    => 'good',
            'counter'   => $return['counter'],
            'result'    => $chart['chart'],
            'title'     => $chart['title'],
            'label'     => esc_html__( 'No. of Event(s)', 'mmt-eo-reports' ),
            'html'      => $html,
            'stepSize'  => $chart['stepsize'],
            'topseller' => $return['topseller'],
        ) );
        exit;
    }

    /**
     * Generate Events Group by author
     */
    public function mmt_eo_report_get_event_submitted_group_by_author() {
        global $mmt_eo_reports;
        check_ajax_referer( 'mmt_eo_reports_security_key', 'mmt_eo_reports_nonce' );
        if ( isset( $_POST['action'] ) && 'mmt_eo_report_get_event_submitted_group_by_author' !== $_POST['action'] ) {
            return;
        }
        $type = ( isset( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : 'weekly' );
        $index = ( isset( $_POST['index'] ) ? sanitize_text_field( wp_unslash( $_POST['index'] ) ) : '0' );
        $events = $mmt_eo_reports->afn->mmt_eo_report_get_upcoming_events( $type, $index, 'author' );
        $chart = $mmt_eo_reports->afn->mmt_eo_report_get_author_wise_chart( $events['events'], $events['title'] );
        $return = $mmt_eo_reports->afn->mmt_eo_report_get_author_events_list( $events['events'] );
        echo wp_json_encode( array(
            'status'  => 'good',
            'counter' => $return['counter'],
            'result'  => $chart,
            'title'   => $events['title'],
            'html'    => $return['html'],
        ) );
        exit;
    }

    /**
     * Get RSVP email preview
     */
    public function mmteorc_rsvp_email_preview() {
        global $mmt_eo_reports;
        check_ajax_referer( 'mmt_eo_reports_security_key', 'mmt_eo_reports_nonce' );
        if ( isset( $_POST['action'] ) && 'mmteorc_rsvp_email_preview' !== $_POST['action'] ) {
            return;
        }
        $step = ( isset( $_POST['step'] ) && !empty( $_POST['step'] ) ? sanitize_text_field( wp_unslash( $_POST['step'] ) ) : 0 );
        $current_timestamp = strtotime( 'now' );
        $end_timestamp = strtotime( '-7 days', $current_timestamp );
        $wp_date_format = get_option( 'date_format' );
        $content = '';
        if ( 0 === (int) $step ) {
            $events = $mmt_eo_reports->cron->mmteorc_weekly_events_all( true, 'rsvp' );
            if ( null !== $events ) {
                echo wp_json_encode( array(
                    'status' => 'good',
                    'msg'    => esc_html__( 'No. of upcoming events: ', 'mmt-eo-reports' ) . count( $events ),
                    'next'   => esc_html__( 'Generating events from ', 'mmt-eo-reports' ) . gmdate( $wp_date_format, $end_timestamp ) . esc_html__( ' to ', 'mmt-eo-reports' ) . gmdate( $wp_date_format, $current_timestamp ),
                ) );
                exit;
            } else {
                echo wp_json_encode( array(
                    'status' => 'bad',
                    'msg'    => esc_html__( 'No upconing event.', 'mmt-eo-reports' ),
                ) );
                exit;
            }
        }
        if ( 1 === (int) $step ) {
            $events = $mmt_eo_reports->cron->mmteorc_weekly_events_all( true, 'rsvp' );
            $data = ( isset( $_POST['added'] ) ? sanitize_text_field( wp_unslash( $_POST['added'] ) ) : null );
            if ( empty( $events ) ) {
                echo wp_json_encode( array(
                    'status' => 'bad',
                    'msg'    => esc_html__( 'No upcoming events.', 'mmt-eo-reports' ),
                ) );
                exit;
            } else {
                $email_content = $mmt_eo_reports->cron->mmteorc_generate_email_content( $events );
                $site_name = get_bloginfo( 'name' );
                $site_email = get_bloginfo( 'admin_email' );
                $settings = get_option( 'mmt_eo_reports_options' );
                $rsvp_fn = ( isset( $settings['rsvp_from_name'] ) ? $settings['rsvp_from_name'] : $site_name );
                $rsvp_fe = ( isset( $settings['rsvp_from_email'] ) ? $settings['rsvp_from_email'] : $site_email );
                $rsvp_es = ( isset( $settings['rsvp_email_subject'] ) ? $settings['rsvp_email_subject'] : '' );
                $rsvp_re = ( isset( $settings['rsvp_reciever_email'] ) ? $settings['rsvp_reciever_email'] : '' );
                $content .= '<p><b>From :  </b>' . $rsvp_fn . '  < ' . $rsvp_fe . ' >';
                $content .= '<p><b>Subject :  </b>' . $rsvp_es;
                $content .= '<p><b>Message :</b>';
                $content .= $email_content;
                echo wp_json_encode( array(
                    'status' => 'good',
                    'html'   => '<div style="padding:2rem;">' . $content . '</div>',
                    'title'  => esc_html__( 'RSVP notification email preview', 'mmt-eo-reports' ),
                    'msg'    => 'Total event(s) matched: ' . count( $events ),
                ) );
                exit;
            }
        }
    }

}

new MMT_EO_Reports_Admin_Ajax();