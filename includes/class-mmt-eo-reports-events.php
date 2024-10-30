<?php
/**
 * MMT EventON - Reports - Events Related Queries
 *
 * @package mmt-eo-reports
 * @author MoMo Themes
 * @since v1.0
 */
class MMT_EO_Reports_Events {
	/**
	 * Generate Events List by Start and End
	 *
	 * @param integer $start_date_unix Start Unix Timestamp.
	 * @param integer $end_date_unix End Unix TimeStamp.
	 * @param string  $addons If searcing with addons enabled.
	 */
	public function mmt_eo_reports_generate_events( $start_date_unix, $end_date_unix, $addons = 'none' ) {
		$wp_arguments = array(
			'post_type'      => 'ajde_events',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'order'          => 'ASC',
			'orderby'        => 'menu_order',
		);
		if ( 'rsvp' === $addons ) {
			$wp_arguments['meta_query'] = array(
				array(
					'key'     => 'evors_rsvp',
					'value'   => 'yes',
					'compare' => '=',
				),
			);
		}
		if ( 'ticket' === $addons ) {
			$wp_arguments['meta_query'] = array(
				array(
					'key'     => 'evotx_tix',
					'value'   => 'yes',
					'compare' => '=',
				),
			);
		}
		if ( 'author' === $addons ) {
			$wp_arguments['date_query'] = array(
				array(
					'after'  => gmdate( 'F j, Y g:i a', $start_date_unix ),
					'before' => gmdate( 'F j, Y g:i a', $end_date_unix ),
				),
			);
		}
		$events           = new WP_Query( $wp_arguments );
		$event_list_array = array();
		if ( $events->have_posts() ) :
			$range_data = array(
				'start' => $start_date_unix,
				'end'   => $end_date_unix,
			);

			$events_processed = array();
			$virtual_dates    = array();

			$count = 0;
			while ( $events->have_posts() ) :
				$events->the_post();
				$count ++;

				if ( 'ajde_events' !== $events->post->post_type ) {
					continue;
				}
				$event_id  = $events->post->ID;
				$evo_event = new EVO_Event( $event_id, '', '', true, $events->post );
				$ev_vals   = $evo_event->get_data();

				if ( $evo_event->check_yn( 'evo_exclude_ev' ) ) {
					continue;
				}

				$row_start = $evo_event->get_start_time();
				$row_end   = $evo_event->get_end_time();
				if ( $evo_event->is_repeating_event() && 'author' !== $addons ) {
					$repeat_intervals = $evo_event->get_repeats();
					if ( ! empty( $repeat_intervals ) && is_array( $repeat_intervals ) ) {
						foreach ( $repeat_intervals as $index => $interval ) {

							$evo_event->ri = $index;
							$start_unix    = (int) $interval[0];
							$end_unix      = (int) $interval[1];
							$term_ar       = 'rm';

							$event_year  = gmdate( 'Y', $start_unix );
							$event_month = gmdate( 'n', $start_unix );

							$is_event_inrange = $evo_event->is_event_in_date_range( $range_data['start'], $range_data['end'] );

							if ( ! $is_event_inrange && 'author' !== $addons ) {
								continue;
							}

							$event_list_array[] = array(
								'ID'                    => $event_id,
								'event_id'              => $event_id,
								'event_start_unix'      => (int) $start_unix,
								'event_end_unix'        => (int) $end_unix,
								'event_title'           => get_the_title(),
								'event_type'            => $term_ar,
								'event_pmv'             => $ev_vals,
								'event_repeat_interval' => $index,
								'ri'                    => $index,
								'author'                => $events->post->post_author,
							);

							$events_processed[] = $event_id;

						}// endforeeach
					}
				} else { // Non recurring events.
					$event_year  = gmdate( 'Y', $row_start );
					$event_month = gmdate( 'n', $row_start );

					$is_event_inrange = $evo_event->is_event_in_date_range( $range_data['start'], $range_data['end'] );
					if ( ! $is_event_inrange && 'author' !== $addons ) {
						continue;
					}
					if ( in_array( $event_id, $events_processed, true ) ) {
						continue;
					}

					$event_list_array[] = array(
						'ID'                    => $event_id,
						'event_id'              => $event_id,
						'event_start_unix'      => (int) $row_start,
						'event_end_unix'        => (int) $row_end,
						'event_title'           => get_the_title(),
						'event_type'            => 'nr',
						'event_pmv'             => $ev_vals,
						'event_repeat_interval' => '0',
						'ri'                    => '0',
						'author'                => $events->post->post_author,
						'post_date'             => $events->post->post_date,
						'status'                => $events->post->post_status,
					);

					$events_processed[] = $event_id;
				}
			endwhile;

		endif;
		wp_reset_postdata();

		// Sort By Dates.
		$event_list_array = $this->mmt_eo_reports_sort_events_array( $event_list_array );

		return $event_list_array;
	}
	/**
	 * Sort Events by Start and End dates
	 *
	 * @param array $event_list_array Events List.
	 */
	public function mmt_eo_reports_sort_events_array( $event_list_array ) {
		usort( $event_list_array, array( $this, 'mer_compare_events_enddate' ) );
		usort( $event_list_array, array( $this, 'mer_compare_events_startdate' ) );
		return $event_list_array;
	}

	/**
	 * Sort start date
	 *
	 * @param mixed $a Param.
	 * @param mixed $b param.
	 */
	public function mer_compare_events_startdate( $a, $b ) {
		return $a['event_start_unix'] - $b['event_start_unix'];
	}
	/**
	 * Sort end date
	 *
	 * @param mixed $a param.
	 * @param mixed $b param.
	 */
	public function mer_compare_events_enddate( $a, $b ) {
		return $a['event_end_unix'] - $b['event_end_unix'];
	}
}
