<?php
/**
 * MMT EventON - Reports - Cron Jobs
 *
 * @package mmt-eo-reports
 * @author MoMo Themes
 * @since v1.2
 * @mmteorc
 */
class MMT_EO_Reports_Cron {
	/**
	 * Time Now
	 *
	 * @var string
	 */
	public $timenow;
	/**
	 * Constructor
	 */
	public function __construct() {
		$this->settings  = get_option( 'mmt_eo_reports_options' );
		$this->rsvp_we   = isset( $settings['rsvp_weekly_email'] ) ? $settings['rsvp_weekly_email'] : '';
		$this->cron_hook = 'mmteorc_rsvp_admin_report';

		add_filter( 'cron_schedules', array( $this, 'mmteorc_reminder_weekly_schedule' ) );
		add_action( $this->cron_hook, array( $this, 'mmteorc_generate_rsvp_admin_report' ) );

		add_action( 'updated_option', array( $this, 'mmteorc_perform_cron_update' ), 10, 3 );
	}
	/**
	 * Update Cron while saving option
	 *
	 * @param string $option Option name.
	 * @param mixed  $old_value Old Value.
	 * @param mixed  $new_value New Value.
	 */
	public function mmteorc_perform_cron_update( $option, $old_value, $new_value ) {
		global $mmt_eo_reports;
		if ( 'mmt_eo_reports_options' === $option ) {
			$rsvp_addons = $mmt_eo_reports->helper->mmt_eo_reports_check_addons_enabled( 'eventon_rs' );
			if ( isset( $new_value['rsvp_weekly_email'] ) && 'on' === $new_value['rsvp_weekly_email'] && $rsvp_addons ) {
				$weekday = isset( $this->settings['rsvp_notification_day'] ) ? $this->settings['rsvp_notification_day'] : 'monday';
				$time    = isset( $this->settings['rsvp_notification_time'] ) ? $this->settings['rsvp_notification_time'] : '0000';
				$this->mmteorc_enable_weekly_notification( $weekday, $time );
			} else {
				$this->mmteorc_disable_weekly_notification();
			}
		}
	}
	/**
	 * Add Cron Schedule
	 *
	 * @param array $schedules Schedules.
	 */
	public function mmteorc_reminder_weekly_schedule( $schedules ) {
		$schedules['mmteorc_weekly'] = array(
			'interval' => 604800,
			'display'  => esc_html__( 'Once Weekly', 'eventon' ),
		);
		return $schedules;
	}
	/**
	 * Enable Weekly Reminder
	 *
	 * @param string $weekday Weekday.
	 * @param string $time Time.
	 */
	public function mmteorc_enable_weekly_notification( $weekday, $time ) {
		wp_clear_scheduled_hook( $this->cron_hook );
		$next_run = $this->mmt_eo_reports_strtotime( 'next ' . $weekday . ' +' . $time . ' minutes' );
		wp_schedule_event( $next_run, 'mmteorc_weekly', $this->cron_hook );
	}

	/**
	 * Disable Weekly Nootification
	 */
	public function mmteorc_disable_weekly_notification() {
		wp_clear_scheduled_hook( $this->cron_hook );
	}
	/**
	 * Perfor Cron Job.
	 */
	public function mmteorc_generate_rsvp_admin_report() {
		$events        = $this->mmteorc_weekly_events_all( false, 'rsvp' );
		$email_content = $this->mmteorc_generate_email_content( $events );
		$email         = get_option( 'admin_email' );
		$return        = $this->mmteorc_send_emails( $email_content, 'rsvp_notification' );
	}
	/**
	 * Generate Email Contents
	 *
	 * @param array $events Events Array.
	 */
	public function mmteorc_generate_email_content( $events ) {
		$evopt1      = EVO()->cal->get_op( 'evcal_1' );
		$evopt2      = EVO()->cal->get_op( 'evcal_2' );
		$_active_tax = evo_get_ett_count( $evopt1 );

		$title_sy = 'color: #6B6B6B;' .
					'line-height: 120%;' .
					'font-weight: bold;' .
					'padding-bottom: 3px;' .
					'font-family: roboto, \'arial narrow\';' .
					'text-transform: uppercase;' .
					'font-size: 22px;';

		$email_content = '';
		foreach ( $events as $event ) {
			$event_id        = $event['event_id'];
			$cal_lang        = evo_get_current_lang();
			$repeat_interval = EVO()->evo_generator->helper->get_ri_for_event( $event );
			$evo_event       = new EVO_Event( $event['event_id'], $event['event_pmv'], $repeat_interval );
			$evors_event     = new EVORS_Event( $evo_event );
			$rsvp_count      = $evors_event->total_rsvp_counts();
			$evo_event->get_event_post();
			$event_permalink = $evo_event->get_permalink( '', $cal_lang );
			$ev_start_unix   = $event['event_start_unix'];
			$ev_end_unix     = $event['event_end_unix'];
			$datetime        = new evo_datetime();
			$date_start_val  = $datetime->get_formatted_smart_time_piece( $ev_start_unix );
			$tax_names_array = evo_get_localized_ettNames( '', $evopt1, $evopt2 );
			$ett_terms       = wp_get_post_terms( $event_id, 'event_type' );
			$tax_content     = '<span>';
			for ( $b = 1; $b <= $_active_tax; $b++ ) {
				$__tax_slug   = 'event_type' . ( 1 === $b ? '' : '_' . $b );
				$__tax_fields = 'eventtype' . ( 1 === $b ? '' : $b );
				$evcal_terms  = ( 1 === $b ) ? $ett_terms : wp_get_post_terms( $event_id, $__tax_slug );
				if ( $evcal_terms ) {
					$__tax_name   = $tax_names_array[ $b ];
					$tax_content .= '<em style="color: #b8b8b8; margin-right: 4px;">' . $__tax_name . ' :  </em>';

					$i = 1;
					foreach ( $evcal_terms as $term_ ) :
						$term_name    = EVO()->evo_generator->lang( 'evolang_' . $__tax_slug . '_' . $term_->term_id, $term_->name );
						$tax_content .= '<em style="margin-right: 8px;">' . $term_name . ( count( $evcal_terms ) !== $i ? ',' : '' ) . '</em>';
						$i++;
					endforeach;
				}
			}
			$tax_content .= '</span>';

			$event_full_description = $evo_event->content;
			$event_excerpt          = $this->mmteorc_get_event_excerpt( $event_full_description, 40, '', false );

			$img_id  = get_post_thumbnail_id( $event_id );
			$img_src = '';
			if ( '' !== $img_id ) {
				$img_src = wp_get_attachment_image_src( $img_id, 'medium' );
				$img_src = isset( $img_src[0] ) ? $img_src[0] : '';
				$img_src = '<img src="' . $img_src . '"/>';
			} else {
				$img_src = '';
			}
			$ynmspan_style = 'background-color: #eaeaea;
							color: #7b7a7a;
							text-transform: uppercase;
							border-radius: 6px;
							padding: 5px 15px;
							font-size: 14px;
							margin-top: 9px;
							font-weight: bold;';

			$email_content .= '<div style="padding:15px; margin-bottom: 15px;background-color: #F2f2f2;">';

				$email_content .= '<a href="' . $event_permalink . '" style="color: #808080;text-decoration: none;">';

					$email_content .= '<div style="' . $title_sy . '">' . $event['event_title'] . '</div>';
					$email_content .= '<div style="text-transform: uppercase;font-size: 18px;">';
					$email_content .= $date_start_val;
					$email_content .= '</div>';
					$email_content .= '<div style="margin-bottom: 12px;">';
					$email_content .= $tax_content;
					$email_content .= '</div>';

						$email_content .= '<div style="margin-bottom: 12px;">';
						$email_content .= $event_excerpt;
						$email_content .= '</div>';
						$email_content .= '<div style="text-align: center">';
						$email_content .= $img_src;
						$email_content .= '</div>';
						$email_content .= '<div style="padding: 5px;color:#808080;min-height: 65px;">';

						$email_content .= '<em style="width: 30%;float: left;display: block;min-width: 95px;font-style:normal;">';
						$email_content .= '<b style="font-size: 48px;line-height: 100%;margin-right: 10px;">' . $rsvp_count['y'] . '</b>';
						$email_content .= '<span style="' . $ynmspan_style . '">' . esc_html__( 'yes', 'mmt-eo-reports' ) . '</span>';
						$email_content .= '</em>';

						$email_content .= '<em style="width: 30%;float: left;display: block;min-width: 95px;font-style:normal;">';
						$email_content .= '<b style="font-size: 48px;line-height: 100%;margin-right: 10px;">' . $rsvp_count['n'] . '</b>';
						$email_content .= '<span style="' . $ynmspan_style . '">' . esc_html__( 'no', 'mmt-eo-reports' ) . '</span>';
						$email_content .= '</em>';

						$email_content .= '<em style="width: 30%;float: left;display: block;min-width: 95px;font-style:normal;">';
						$email_content .= '<b style="font-size: 48px;line-height: 100%;margin-right: 10px;">' . $rsvp_count['m'] . '</b>';
						$email_content .= '<span style="' . $ynmspan_style . '">' . esc_html__( 'maybe', 'mmt-eo-reports' ) . '</span>';
						$email_content .= '</em>';

						$email_content .= '</div>';

				$email_content .= '</a>';

			$email_content .= '</div>';
		}
		$email_content = '<center style="width: 100%"><div style="font-family: \'open sans\', \'arial narrow\';max-width: 630px;">' . $email_content . '</div></center>';

		return $email_content;
	}
	/**
	 * Get Events
	 *
	 * @param boolean $preview Preview or not.
	 * @param string  $type RSVP or other addons.
	 */
	public function mmteorc_weekly_events_all( $preview = false, $type = 'rsvp' ) {
		global $eventon, $mmt_eo_reports;
		$evo_opt           = $eventon->frontend->evo_options;
		$current_timestamp = strtotime( 'now' );
		$end_timestamp     = strtotime( '+7 days', $current_timestamp );

		$start_date_unix = $current_timestamp;
		$end_date_unix   = $end_timestamp;
		$events_data     = $mmt_eo_reports->events->mmt_eo_reports_generate_events( $start_date_unix, $end_date_unix, $type );
		if ( ! empty( $events_data ) ) :
			return count( $events_data ) > 0 ? $events_data : null;
		else :
			return null;
		endif;
	}
	/**
	 * Custom excerpt
	 *
	 * @param string  $text text.
	 * @param int     $excerpt_length Length.
	 * @param string  $default_excerpt Default.
	 * @param boolean $title Show title.
	 */
	public function mmteorc_get_event_excerpt( $text, $excerpt_length, $default_excerpt = '', $title = true ) {
		global $eventon;
		$content = '';
		if ( empty( $default_excerpt ) ) {
			$words = explode( ' ', $text );

			if ( count( $words ) > $excerpt_length ) {
				$words = array_slice( $words, 0, $excerpt_length, true );
			}
			$content = implode( ' ', $words );
			$content = strip_shortcodes( $content );
			$content = str_replace( ']]>', ']]&gt;', $content );
			$content = wp_strip_all_tags( $content );
		} else {
			$content = $default_excerpt;
		}
		return $content;
	}
	/**
	 * PHP strtotime to local timezone
	 *
	 * @param string $str Time string.
	 */
	public function mmt_eo_reports_strtotime( $str ) {
		$tz_string = get_option( 'timezone_string' );
		$tz_offset = get_option( 'gmt_offset', 0 );

		if ( ! empty( $tz_string ) ) {
			// If site timezone option string exists, use it.
			$timezone = $tz_string;
		} elseif ( 0 === $tz_offset ) {
			// get UTC offset, if it isn’t set then return UTC.
			$timezone = 'UTC';
		} else {
			$timezone = $tz_offset;
			if ( substr( $tz_offset, 0, 1 ) !== '-' && substr( $tz_offset, 0, 1 ) !== '+' && substr( $tz_offset, 0, 1 ) !== 'U' ) {
				$timezone = '+' . $tz_offset;
			}
		}

		$datetime = new DateTime( $str, new DateTimeZone( $timezone ) );
		return $datetime->format( 'U' );
	}
	/**
	 * Send emails
	 *
	 * @param string $email_content Email Content.
	 * @param string $type Email type.
	 */
	public function mmteorc_send_emails( $email_content, $type = 'rsvp_notification' ) {
		$site_name  = get_bloginfo( 'name' );
		$site_email = get_bloginfo( 'admin_email' );
		$settings   = get_option( 'mmt_eo_reports_options' );
		if ( 'rsvp_notification' === $type ) :
			$rsvp_fn = isset( $settings['rsvp_from_name'] ) ? $settings['rsvp_from_name'] : $site_name;
			$rsvp_fe = isset( $settings['rsvp_from_email'] ) ? $settings['rsvp_from_email'] : $site_email;
			$rsvp_es = isset( $settings['rsvp_email_subject'] ) ? $settings['rsvp_email_subject'] : '';
			$rsvp_re = isset( $settings['rsvp_reciever_email'] ) ? $settings['rsvp_reciever_email'] : $site_email;

			$to      = $rsvp_re;
			$subject = $rsvp_es;
			$from    = $rsvp_fe;
			$from_e  = $rsvp_fe;
			$from_n  = $rsvp_fn;
		endif;
		$args = array(
			'html'       => 'yes',
			'to'         => $to,
			'subject'    => $subject,
			'from'       => $from,
			'from_name'  => $from_n,
			'from_email' => $from_e,
			'message'    => $email_content,
		);

		$helper  = new evo_helper();
		$emailed = $helper->send_email( $args );
	}
}
