<?php
/**
 * Eventon Reports Admin functions
 *
 * @package mmt-eo-reports
 * @author MoMo Themes
 * @since v1.0
 */
class MMT_EO_Reports_Admin_Functions {
	/**
	 * Get Chart Data
	 *
	 * @param array  $events Events List.
	 * @param string $type Chart type.
	 * @param string $index Index String.
	 * @param string $addons Addons Type.
	 */
	public function mmt_eo_report_get_line_chart_data( $events, $type, $index, $addons = 'normal' ) {
		$chart = array();
		if ( 'weekly' === $type ) {
			$week_array = array(
				esc_html__( 'Sunday', 'mmt-eo-reports' ),
				esc_html__( 'Monday', 'mmt-eo-reports' ),
				esc_html__( 'Tuesday', 'mmt-eo-reports' ),
				esc_html__( 'Wednesday', 'mmt-eo-reports' ),
				esc_html__( 'Thursday', 'mmt-eo-reports' ),
				esc_html__( 'Friday', 'mmt-eo-reports' ),
				esc_html__( 'Saturday', 'mmt-eo-reports' ),
			);

			$start_of_week = get_option( 'start_of_week' );
			if ( ! $start_of_week ) {
				$start_of_week = 0;
			}
			$start_date_unix = strtotime( 'next ' . $week_array[ $start_of_week ] );
			$end_date_unix   = strtotime( '+6 days', $start_date_unix );
			if ( '0' !== $index ) {
				$start_date_unix = strtotime( $index . ' weeks', $start_date_unix );
				$end_date_unix   = strtotime( $index . ' weeks', $end_date_unix );
			}

			$weekly    = array();
			$start_day = $start_date_unix;
			foreach ( $week_array as $weekday ) {
				$next_day                    = strtotime( '+1 day', $start_day );
				$weekly[ $weekday ]['start'] = $start_day;
				$weekly[ $weekday ]['end']   = $next_day - 1;

				$start_day = $next_day;
			}
			if ( empty( $events ) ) {
				foreach ( $weekly as $week => $se ) {
					$count          = isset( $chart[ $week ] ) ? $chart[ $week ] : 0;
					$chart[ $week ] = $count;
				}
			} else {
				foreach ( $events as $event ) {
					$start_event = $event['event_start_unix'];
					foreach ( $weekly as $week => $se ) {
						$count          = isset( $chart[ $week ] ) ? $chart[ $week ] : 0;
						$chart[ $week ] = $count;
						if ( $start_event >= $se['start'] && $start_event <= $se['end'] ) {
							$chart[ $week ] = $count + 1;
						}
					}
				}
			}
		} elseif ( 'monthly' === $type ) {
			$index      = (int) $index;
			$start_date = new DateTime( 'first day of this month' );
			if ( 0 !== $index ) {
				$start_date->modify( ( $index > 0 ? '+' : '-' ) . abs( $index ) . ' months' );
				$start_date->modify( 'first day of this month' );
			}

			// Clone the start date for the end date calculation.
			$end_date = clone $start_date;
			$end_date->modify( 'last day of this month' );

			// Get Unix timestamps.
			$start_date_unix = $start_date->getTimestamp();
			$end_date_unix   = $end_date->getTimestamp();

			$now         = $start_date_unix;
			$last        = $end_date_unix;
			$month_array = array();
			$total_c     = 0;
			while ( $now <= $last ) {
				++$total_c;

				$index                          = gmdate( 'd-M', $now );
				$month_array[ $index ]['start'] = $now;
				$now                            = strtotime( '+1 day', $now );
				$month_array[ $index ]['end']   = $now - 1;
			}
			if ( empty( $events ) ) {
				foreach ( $month_array as $day => $se ) {
					$count         = isset( $chart[ $day ] ) ? $chart[ $day ] : 0;
					$chart[ $day ] = $count;
				}
			} else {
				foreach ( $events as $event ) {
					$start_event = $event['event_start_unix'];
					foreach ( $month_array as $day => $se ) {
						$count         = isset( $chart[ $day ] ) ? $chart[ $day ] : 0;
						$chart[ $day ] = $count;
						if ( $start_event >= $se['start'] && $start_event <= $se['end'] ) {
							$chart[ $day ] = $count + 1;
						}
					}
				}
			}
		} elseif ( 'yearly' === $type ) {
			$start_date_unix = strtotime( 'first day of this month' );
			$end_date_unix   = strtotime( '+1 year', $start_date_unix );
			if ( '0' !== $index ) {
				$start_date_unix = strtotime( $index . ' years', $start_date_unix );
				$end_date_unix   = strtotime( $index . ' years', $end_date_unix );
			}
			$now          = $start_date_unix;
			$last         = $end_date_unix;
			$yearly_array = array();
			$total_c      = 0;
			while ( $now <= $last ) {
				++$total_c;

				$index_                           = gmdate( 'M', $now );
				$yearly_array[ $index_ ]['start'] = $now;
				$now                              = strtotime( '+1 month', $now );
				$yearly_array[ $index_ ]['end']   = $now - 1;
				if ( 12 === $total_c ) {
					break;
				}
			}
			if ( empty( $events ) ) {
				foreach ( $yearly_array as $month => $se ) {
					$count           = isset( $chart[ $month ] ) ? $chart[ $month ] : 0;
					$chart[ $month ] = $count;
				}
			} else {
				foreach ( $events as $event ) {
					$start_event = $event['event_start_unix'];
					foreach ( $yearly_array as $month => $se ) {
						$count           = isset( $chart[ $month ] ) ? $chart[ $month ] : 0;
						$chart[ $month ] = $count;
						if ( $start_event >= $se['start'] && $start_event <= $se['end'] ) {
							$chart[ $month ] = $count + 1;
						}
					}
				}
			}
		}
		$wp_date_format = get_option( 'date_format' );
		$start          = gmdate( $wp_date_format, $start_date_unix );
		$end            = gmdate( $wp_date_format, $end_date_unix );
		$title          = $start . ' - ' . $end;
		$total          = count( $chart );
		$stepsize       = ( $total > 10 ) ? ( $total % 10 ) + 1 : 10;
		return array(
			'stepsize' => $stepsize,
			'chart'    => $chart,
			'title'    => $title,
			'label'    => esc_html__( 'No. of Event(s)', 'mmt-eo-reports' ),
		);

	}
	/**
	 * Get Chart Data
	 *
	 * @param array  $events Events List.
	 * @param string $type Chart type.
	 * @param string $index Index String.
	 * @param string $addons Addons Type.
	 */
	public function mmt_eo_report_get_pie_chart_data( $events, $type, $index, $addons = 'normal' ) {
		$chart = array();

		$today_day = date_i18n( 'j' );
		$__date    = $today_day;
		$__month   = date_i18n( 'n' );
		$__year    = date_i18n( 'Y' );
		if ( 'daily' === $type ) {
			$start_date_unix = mktime( 0, 0, 0, $__month, $__date, $__year );
			$end_date_unix   = mktime( 23, 59, 59, $__month, $__date, $__year );
			if ( '0' !== $index ) {
				$start_date_unix = strtotime( $index . ' days', $start_date_unix );
				$end_date_unix   = strtotime( $index . ' days', $end_date_unix );
			}
		} elseif ( 'weekly' === $type ) {
			$week_array = array(
				esc_html__( 'Sunday', 'mmt-eo-reports' ),
				esc_html__( 'Monday', 'mmt-eo-reports' ),
				esc_html__( 'Tuesday', 'mmt-eo-reports' ),
				esc_html__( 'Wednesday', 'mmt-eo-reports' ),
				esc_html__( 'Thursday', 'mmt-eo-reports' ),
				esc_html__( 'Friday', 'mmt-eo-reports' ),
				esc_html__( 'Saturday', 'mmt-eo-reports' ),
			);

			$start_of_week = get_option( 'start_of_week' );
			if ( ! $start_of_week ) {
				$start_of_week = 0;
			}
			$start_date_unix = strtotime( 'next ' . $week_array[ $start_of_week ] );
			$end_date_unix   = strtotime( '+6 days', $start_date_unix );
			if ( '0' !== $index ) {
				$start_date_unix = strtotime( $index . ' weeks', $start_date_unix );
				$end_date_unix   = strtotime( $index . ' weeks', $end_date_unix );
			}

			$weekly    = array();
			$start_day = $start_date_unix;
			foreach ( $week_array as $weekday ) {
				$next_day                    = strtotime( '+1 day', $start_day );
				$weekly[ $weekday ]['start'] = $start_day;
				$weekly[ $weekday ]['end']   = $next_day - 1;

				$start_day = $next_day;
			}
			if ( empty( $events ) ) {
				foreach ( $weekly as $week => $se ) {
					$count          = isset( $chart[ $week ] ) ? $chart[ $week ] : 0;
					$chart[ $week ] = $count;
				}
			} else {
				foreach ( $events as $event ) {
					$start_event = $event['event_start_unix'];
					foreach ( $weekly as $week => $se ) {
						$count          = isset( $chart[ $week ] ) ? $chart[ $week ] : 0;
						$chart[ $week ] = $count;
						if ( $start_event >= $se['start'] && $start_event <= $se['end'] ) {
							$chart[ $week ] = $count + 1;
						}
					}
				}
			}
		} elseif ( 'monthly' === $type ) {
			$index      = (int) $index;
			$start_date = new DateTime( 'first day of this month' );
			if ( 0 !== $index ) {
				$start_date->modify( ( $index > 0 ? '+' : '-' ) . abs( $index ) . ' months' );
				$start_date->modify( 'first day of this month' );
			}

			// Clone the start date for the end date calculation.
			$end_date = clone $start_date;
			$end_date->modify( 'last day of this month' );

			// Get Unix timestamps.
			$start_date_unix = $start_date->getTimestamp();
			$end_date_unix   = $end_date->getTimestamp();

			$now         = $start_date_unix;
			$last        = $end_date_unix;
			$month_array = array();
			$total_c     = 0;
			while ( $now <= $last ) {
				++$total_c;

				$index                          = gmdate( 'd-M', $now );
				$month_array[ $index ]['start'] = $now;
				$now                            = strtotime( '+1 day', $now );
				$month_array[ $index ]['end']   = $now - 1;
			}
			if ( empty( $events ) ) {
				foreach ( $month_array as $day => $se ) {
					$count         = isset( $chart[ $day ] ) ? $chart[ $day ] : 0;
					$chart[ $day ] = $count;
				}
			} else {
				foreach ( $events as $event ) {
					$start_event = $event['event_start_unix'];
					foreach ( $month_array as $day => $se ) {
						$count         = isset( $chart[ $day ] ) ? $chart[ $day ] : 0;
						$chart[ $day ] = $count;
						if ( $start_event >= $se['start'] && $start_event <= $se['end'] ) {
							$chart[ $day ] = $count + 1;
						}
					}
				}
			}
		} elseif ( 'yearly' === $type ) {
			$start_date_unix = strtotime( 'first day of this month' );
			$end_date_unix   = strtotime( '+1 year', $start_date_unix );
			if ( '0' !== $index ) {
				$start_date_unix = strtotime( $index . ' years', $start_date_unix );
				$end_date_unix   = strtotime( $index . ' years', $end_date_unix );
			}
			$now          = $start_date_unix;
			$last         = $end_date_unix;
			$yearly_array = array();
			$total_c      = 0;
			while ( $now <= $last ) {
				++$total_c;

				$index_                           = gmdate( 'M', $now );
				$yearly_array[ $index_ ]['start'] = $now;
				$now                              = strtotime( '+1 month', $now );
				$yearly_array[ $index_ ]['end']   = $now - 1;
				if ( 12 === $total_c ) {
					break;
				}
			}
			if ( empty( $events ) ) {
				foreach ( $yearly_array as $month => $se ) {
					$count           = isset( $chart[ $month ] ) ? $chart[ $month ] : 0;
					$chart[ $month ] = $count;
				}
			} else {
				foreach ( $events as $event ) {
					$start_event = $event['event_start_unix'];
					foreach ( $yearly_array as $month => $se ) {
						$count           = isset( $chart[ $month ] ) ? $chart[ $month ] : 0;
						$chart[ $month ] = $count;
						if ( $start_event >= $se['start'] && $start_event <= $se['end'] ) {
							$chart[ $month ] = $count + 1;
						}
					}
				}
			}
		}
		$wp_date_format = get_option( 'date_format' );
		$start          = gmdate( $wp_date_format, $start_date_unix );
		$end            = gmdate( $wp_date_format, $end_date_unix );
		$title          = $start . ' - ' . $end;
		$total          = count( $chart );
		$stepsize       = ( $total > 10 ) ? ( $total % 10 ) + 1 : 10;
		return array(
			'stepsize' => $stepsize,
			'chart'    => $chart,
			'title'    => $title,
			'label'    => esc_html__( 'No. of Event(s)', 'mmt-eo-reports' ),
		);

	}
	/**
	 * Get Upcoming Events
	 *
	 * @param string $type Type.
	 * @param string $index Index String.
	 * @param string $addons Addons name.
	 */
	public function mmt_eo_report_get_upcoming_events( $type, $index, $addons = 'normal' ) {
		global $eventon, $mmt_eo_reports;
		$today_day = date_i18n( 'j' );
		$__date    = $today_day;
		$__month   = date_i18n( 'n' );
		$__year    = date_i18n( 'Y' );
		if ( 'daily' === $type ) {
			$start_date_unix = mktime( 0, 0, 0, $__month, $__date, $__year );
			$end_date_unix   = mktime( 23, 59, 59, $__month, $__date, $__year );
			if ( '0' !== $index ) {
				$start_date_unix = strtotime( $index . ' days', $start_date_unix );
				$end_date_unix   = strtotime( $index . ' days', $end_date_unix );
			}
		} elseif ( 'weekly' === $type ) {
			$week_array = array(
				esc_html__( 'Sunday', 'mmt-eo-reports' ),
				esc_html__( 'Monday', 'mmt-eo-reports' ),
				esc_html__( 'Tuesday', 'mmt-eo-reports' ),
				esc_html__( 'Wednesday', 'mmt-eo-reports' ),
				esc_html__( 'Thursday', 'mmt-eo-reports' ),
				esc_html__( 'Friday', 'mmt-eo-reports' ),
				esc_html__( 'Saturday', 'mmt-eo-reports' ),
			);

			$start_of_week = get_option( 'start_of_week' );
			if ( ! $start_of_week ) {
				$start_of_week = 0;
			}
			if ( 'author' === $addons ) {
				$start_date_unix = strtotime( 'last ' . $week_array[ $start_of_week ] );
			} else {
				$start_date_unix = strtotime( 'next ' . $week_array[ $start_of_week ] );
			}
			$end_date_unix = strtotime( '+6 days', $start_date_unix );
			if ( '0' !== $index ) {
				$start_date_unix = strtotime( $index . ' weeks', $start_date_unix );
				$end_date_unix   = strtotime( $index . ' weeks', $end_date_unix );
			}
		} elseif ( 'monthly' === $type ) {
			$index      = (int) $index;
			$start_date = new DateTime( 'first day of this month' );
			if ( 0 !== $index ) {
				$start_date->modify( ( $index > 0 ? '+' : '-' ) . abs( $index ) . ' months' );
				$start_date->modify( 'first day of this month' );
			}

			// Clone the start date for the end date calculation.
			$end_date = clone $start_date;
			$end_date->modify( 'last day of this month' );

			// Get Unix timestamps.
			$start_date_unix = $start_date->getTimestamp();
			$end_date_unix   = $end_date->getTimestamp();
		} elseif ( 'yearly' === $type ) {
			$start_date_unix = strtotime( 'first day of this month' );
			$end_date_unix   = strtotime( '+1 year', $start_date_unix );
			if ( '0' !== $index ) {
				$start_date_unix = strtotime( $index . ' years', $start_date_unix );
				$end_date_unix   = strtotime( $index . ' years', $end_date_unix );
			}
		}
		$new_arguments = array(
			'focus_start_date_range' => $start_date_unix,
			'focus_end_date_range'   => $end_date_unix,
		);

		$wp_date_format = get_option( 'date_format' );
		$start          = gmdate( $wp_date_format, $start_date_unix );
		$end            = gmdate( $wp_date_format, $end_date_unix );
		$events_list    = $mmt_eo_reports->events->mmt_eo_reports_generate_events( $start_date_unix, $end_date_unix, $addons );
		$title          = $start . ' - ' . $end;
		return array(
			'events' => $events_list,
			'title'  => $title,
		);
	}
	/**
	 * Get Events List tbody
	 *
	 * @param array $events Events List.
	 */
	public function mmt_eo_report_get_events_list( $events ) {
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		$output = '';

		if ( empty( $events ) ) {
			$output .= '<tr>';
			$output .= '<td colspan="3" align="center">';
			$output .= esc_html__( 'No Event', 'mmt-eo-reports' );
			$output .= '</td>';
			$output .= '</tr>';
		} else {
			foreach ( $events as $event_ ) {
				$repeat_interval = EVO()->evo_generator->helper->get_ri_for_event( $event_ );
				$evo_event       = new EVO_Event( $event_['event_id'], $event_['event_pmv'], $repeat_interval );
				$event_pmv       = $event_['event_pmv'];
				$event_obj       = $evo_event;
				$evo_event->get_event_post();
				$is_recurring_event = $evo_event->is_repeating_event();

				$permalink   = $evo_event->get_permalink( $repeat_interval );
				$event_title = $event_obj->post_title;
				$event_id    = (int) $event_['event_id'];

				$event_data['event_start_unix'] = (int) $event_['event_start_unix'];
				$event_data['event_end_unix']   = (int) $event_['event_end_unix'];

				$start_date = gmdate( $date_format, $event_['event_start_unix'] );
				$start_time = gmdate( $time_format, $event_['event_start_unix'] );

				$output .= '<tr>';
				$output .= '<td>';
				$output .= $event_title;
				$output .= '</td>';
				$output .= '<td align="center">';
				$output .= $start_date . ' - ' . $start_time;
				$output .= '</td>';
				$output .= '<td>';
				$output .= '<a href="' . $permalink . '" target="_blank"><i class="fa fa-link"></i></a>';
				$output .= '</td>';
				$output .= '</tr>';
			}
		}
		return $output;
	}
	/**
	 * Get Events List tbody
	 *
	 * @param array $events Events List.
	 */
	public function mmt_eo_report_get_rsvp_events_list( $events ) {
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );
		$yes         = 0;
		$no          = 0;
		$maybe       = 0;
		$output      = '';
		if ( empty( $events ) ) {
			$output .= '<tr>';
			$output .= '<td colspan="5" align="center">';
			$output .= esc_html__( 'No Event', 'mmt-eo-reports' );
			$output .= '</td>';
			$output .= '</tr>';
		} else {
			foreach ( $events as $event_ ) {
				$repeat_interval = EVO()->evo_generator->helper->get_ri_for_event( $event_ );
				$evo_event       = new EVO_Event( $event_['event_id'], $event_['event_pmv'], $repeat_interval );
				$event_pmv       = $event_['event_pmv'];
				$event_obj       = $evo_event;
				$evo_event->get_event_post();
				$is_recurring_event = $evo_event->is_repeating_event();

				$permalink   = $evo_event->get_permalink( $repeat_interval );
				$event_title = $event_obj->post_title;
				$event_id    = (int) $event_['event_id'];
				$title       = $evo_event->get_title();

				$event_data['event_start_unix'] = (int) $event_['event_start_unix'];
				$event_data['event_end_unix']   = (int) $event_['event_end_unix'];

				$start_date = gmdate( $date_format, $event_['event_start_unix'] );
				$start_time = gmdate( $time_format, $event_['event_start_unix'] );

				$evors_event = new EVORS_Event( $event_['event_id'], $repeat_interval );
				$synced      = $evors_event->total_rsvp_counts();
				$capacity    = $evors_event->is_capacity_limit_set() ? $evors_event->get_total_adjusted_capacity() : '<i class="fa fa-infinity"></i>';
				$ccapacity   = $evors_event->is_capacity_limit_set() ? $evors_event->get_total_adjusted_capacity() : 0;

				$yes   += $synced['y'];
				$no    += $synced['n'];
				$maybe += $synced['m'];

				/** Download Single CSV */
				$export_csv_rsvp = add_query_arg(
					array(
						'action'               => 'mmt_eo_report_get_rsvp_csv_data',
						'event_id'             => $event_['event_id'],
						'ri'                   => $repeat_interval,
						'type'                 => 'single',
						'mmt_eo_reports_nonce' => wp_create_nonce( 'mmt_eo_reports_nonce' ),
					),
					admin_url( 'admin-ajax.php' )
				);

				$output .= '<tr data-event_id="' . $event_['event_id'] . '">';
				$output .= '<td>';
				$output .= '<input type="checkbox" class="mmt_eo_single_check" name="mmt_eo_reports_single_rsvp_event" value="' . $event_['event_id'] . '"/ data-ri="' . $repeat_interval . '"/>';
				$output .= '</td>';
				$output .= '<td>';
				$output .= $event_title;
				$output .= '</td>';
				$output .= '<td align="center">';
				$output .= $start_date . ' - ' . $start_time;
				$output .= '</td>';
				$output .= '<td align="center" class="rsvp_count">';
				$output .= '<span class="yes" title="' . esc_html__( 'Yes', 'mmtre' ) . '">' . $synced['y'] . '</span>';
				$output .= '<span class="no" title="' . esc_html__( 'No', 'mmtre' ) . '">' . $synced['n'] . '</span>';
				$output .= '<span class="maybe" title="' . esc_html__( 'Maybe', 'mmtre' ) . '">' . $synced['m'] . '</span>';
				$output .= '<canvas class="rsvp_small_chart" data-y="' . $synced['y'] . '" data-n="' . $synced['n'] . '" data-mb="' . $synced['m'] . '" data-c="' . $ccapacity . '"></canvas>';
				$output .= '</td>';
				$output .= '<td align="center" class="rsvp_capacity">';
				$output .= $capacity;
				$output .= '</td>';
				$output .= '<td class="rsvp_action action_col">';
				$output .= '<a href="' . $permalink . '" target="_blank" title="' . $title . '"><i class="fa fa-globe"></i></a>';
				$output .= '<span data-url="' . $export_csv_rsvp . '" title="' . esc_html__( 'Download CSV', 'mmtre' ) . '" class="mer-download-csv-rsvp-single" data-event_id="' . $event_['event_id'] . '"><i class="fa fa-download"></i></span>';
				$output .= '</td>';
				$output .= '</tr>';
			}
		}
		return array(
			'html'    => $output,
			'counter' => array(
				'yes'   => $yes,
				'no'    => $no,
				'maybe' => $maybe,
			),
		);
	}
	/**
	 * Get Events List tbody
	 *
	 * @param array $events Events List.
	 */
	public function mmt_eo_report_get_ticket_events_list( $events ) {
		global $mmt_eo_reports;
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		$total_ticket_sold  = 0;
		$total_ticket_sales = 0;

		$total_sales    = 0;
		$orders         = 0;
		$cancelled      = 0;
		$top_seller_id  = null;
		$top_seller_ri  = null;
		$top_seller_amt = 0;
		$output         = '';

		if ( empty( $events ) ) {
			$output .= '<tr>';
			$output .= '<td colspan="5" align="center">';
			$output .= esc_html__( 'No Event', 'mmt-eo-reports' );
			$output .= '</td>';
			$output .= '</tr>';
		} else {
			foreach ( $events as $event_ ) {
				$repeat_interval = EVO()->evo_generator->helper->get_ri_for_event( $event_ );
				$evo_event       = new EVO_Event( $event_['event_id'], $event_['event_pmv'], $repeat_interval );
				$event_pmv       = $event_['event_pmv'];
				$event_obj       = $evo_event;
				$evo_event->get_event_post();
				$is_recurring_event = $evo_event->is_repeating_event();

				$permalink   = $evo_event->get_permalink( $repeat_interval );
				$event_title = $event_obj->post_title;
				$event_id    = (int) $event_['event_id'];
				$title       = $evo_event->get_title();

				$event_data['event_start_unix'] = (int) $event_['event_start_unix'];
				$event_data['event_end_unix']   = (int) $event_['event_end_unix'];

				$start_date = gmdate( $date_format, $event_['event_start_unix'] );
				$start_time = gmdate( $time_format, $event_['event_start_unix'] );

				$evotx_event = new evotx_event( $event_['event_id'], $event_['event_pmv'] );
				$ticket_sold = $evotx_event->get_ticket_stock_status();
				$capacity    = $evotx_event->get_ticket_stock_status() ? $evotx_event->get_ticket_stock_status() : '<i class="fa fa-infinity"></i>';
				$cap_class   = $evotx_event->get_ticket_stock_status() ? $evotx_event->get_ticket_stock_status() : '';

				$manage_stock = $evotx_event->get_wc_prop( '_manage_stock' );
				$remaining    = '';
				if ( 'yes' === $manage_stock ) {
					$remaining = $evotx_event->get_wc_prop( '_stock' );
				}

				$sales_data   = $mmt_eo_reports->helper->mmt_eo_reports_get_sales_data( $evotx_event );
				$tickets_sold = $sales_data['total_tickets_sold'];
				$tickets_cost = $sales_data['total_tickets_cost'];
				$ticket_sales = $mmt_eo_reports->helper->add_currency( $tickets_cost );
				$orders       = $orders + count( $sales_data['sales_data'] );
				$cancelled    = $cancelled + $sales_data['total_cancelled_order'];

				$total_ticket_sold  += $tickets_sold;
				$total_ticket_sales += $tickets_cost;
				if ( $tickets_cost > $top_seller_amt ) {
					$top_seller_id  = $event_['event_id'];
					$top_seller_ri  = $repeat_interval;
					$top_seller_amt = $tickets_cost;
				}
				/** Download Single CSV */
				$export_csv_ticket = add_query_arg(
					array(
						'action'               => 'mmt_eo_report_get_ticket_csv_data',
						'event_id'             => $event_['event_id'],
						'ri'                   => $repeat_interval,
						'type'                 => 'single',
						'mmt_eo_reports_nonce' => wp_create_nonce( 'mmt_eo_reports_nonce' ),
					),
					admin_url( 'admin-ajax.php' )
				);

				$output .= '<tr data-event_id="' . $event_['event_id'] . '" data-ri="' . $repeat_interval . '" data-ts="' . $tickets_sold . '" data-tc="' . $tickets_cost . '" data-title="' . $event_title . '">';
				$output .= '<td>';
				$output .= '<input type="checkbox" class="mmt_eo_single_check" name="mmt_eo_reports_single_ticket_event" value="' . $event_['event_id'] . '" data-ri="' . $repeat_interval . '"/>';
				$output .= '</td>';
				$output .= '<td class="title">';
				$output .= $event_title;
				$output .= '</td>';
				$output .= '<td align="center">';
				$output .= $start_date . ' - ' . $start_time;
				$output .= '</td>';
				$output .= '<td align="center" class="ticket_sold_count">';
				$output .= $tickets_sold;
				$output .= '</td>';
				$output .= '<td align="center" class="remaining_count">';
				$output .= '<span>' . $remaining . '</span>';
				$output .= '</td>';
				$output .= '<td align="center" class="ticket_sales">';
				$output .= $ticket_sales;
				$output .= '</td>';
				$output .= '<td align="center" class="ticket_stock">';
				$output .= '<span class=" ' . $cap_class . '">' . $capacity . '</span>';
				$output .= '</td>';
				$output .= '<td class="ticket_action action_col">';
				$output .= '<a href="#" data-target="#sales-insight-single" class="mer-popbox-opener" data-ri="' . $repeat_interval . '" data-event_id="' . $event_id . '" title="' . esc_html__( 'Sales Insight', ' mmt-eo-reports' ) . '" data-title="' . esc_html__( 'Sales Insight', ' mmt-eo-reports' ) . '" data-action="single_sales_insight"><i class="fa fa-chart-bar"></i></a>';
				$output .= '<a href="' . $permalink . '" target="_blank" title="' . $title . '"><i class="fa fa-globe"></i></a>';
				$output .= '<span title="' . esc_html__( 'Download CSV', 'mmtre' ) . '" class="mer-download-csv-ticket-single" data-event_id="' . $event_['event_id'] . '" data-ri="' . $repeat_interval . '" data-url="' . $export_csv_ticket . '"><i class="fa fa-download"></i></span>';
				$output .= '</td>';
				$output .= '</tr>';
			}
			$output .= '<tr class="footer">';
			$output .= '<th colspan="3" class="footer_total">';
			$output .= esc_html__( 'Total', 'mmt-eo-reports' );
			$output .= '</th>';
			$output .= '<th>';
			$output .= $total_ticket_sold;
			$output .= '</th>';
			$output .= '<th>';
			$output .= '</th>';
			$output .= '<th>';
			$output .= $mmt_eo_reports->helper->add_currency( $total_ticket_sales );
			$output .= '</th>';
			$output .= '<th>';
			$output .= '</th>';
			$output .= '<th>';
			$output .= '</th>';
			$output .= '</tr>';
		}
		return array(
			'html'      => $output,
			'topseller' => array(
				'id'    => $top_seller_id,
				'ri'    => $top_seller_ri,
				'icon'  => '<i class="mer-topseller-icon fa fa-award" title="' . esc_html__( 'Top Seller', 'mmt-eo-reports' ) . '"></i>',
				'style' => 'rgba(185, 223, 168, 0.5)',
			),
			'counter'   => array(
				'sales'     => $total_ticket_sales,
				'orders'    => $orders,
				'cancelled' => $cancelled,
				'symbol'    => get_woocommerce_currency_symbol(),
			),
		);
	}
	/**
	 * Get Events List (Views/Count) tbody
	 *
	 * @param array $events Events List.
	 */
	public function mmt_eo_report_get_events_list_count( $events ) {
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		$output = '';
		if ( empty( $events ) ) {
			$output .= '<tr>';
			$output .= '<td colspan="4" align="center">';
			$output .= esc_html__( 'No Event', 'mmt-eo-reports' );
			$output .= '</td>';
			$output .= '</tr>';
		} else {
			foreach ( $events as $event_ ) {
				$repeat_interval = EVO()->evo_generator->helper->get_ri_for_event( $event_ );
				$evo_event       = new EVO_Event( $event_['event_id'], $event_['event_pmv'], $repeat_interval );
				$event_pmv       = $event_['event_pmv'];
				$event_obj       = $evo_event;
				$evo_event->get_event_post();
				$is_recurring_event = $evo_event->is_repeating_event();

				$permalink   = $evo_event->get_permalink( $repeat_interval );
				$event_title = $event_obj->post_title;
				$event_id    = (int) $event_['event_id'];

				$event_data['event_start_unix'] = (int) $event_['event_start_unix'];
				$event_data['event_end_unix']   = (int) $event_['event_end_unix'];

				$start_date = gmdate( $date_format, $event_['event_start_unix'] );
				$start_time = gmdate( $time_format, $event_['event_start_unix'] );

				$event_top_count    = $evo_event->get_prop( '_mer_event_top_view' );
				$event_top_count    = (int) isset( $event_top_count[ $repeat_interval ] ) ? $event_top_count[ $repeat_interval ] : 0;
				$single_event_count = $evo_event->get_prop( '_mer_single_page_view' );
				$single_event_count = (int) isset( $single_event_count[ $repeat_interval ] ) ? $single_event_count[ $repeat_interval ] : 0;

				$output .= '<tr>';
				$output .= '<td>';
				$output .= $event_title;
				$output .= '</td>';
				$output .= '<td align="center">';
				$output .= $start_date . ' - ' . $start_time;
				$output .= '</td>';
				$output .= '<td align="center">';
				$output .= $event_top_count;
				$output .= '</td>';
				$output .= '<td align="center">';
				$output .= $single_event_count;
				$output .= '</td>';
				$output .= '<td>';
				$output .= '<a href="' . $permalink . '" target="_blank"><i class="fa fa-link"></i></a>';
				$output .= '</td>';
				$output .= '</tr>';
			}
		}

		return $output;
	}
	/**
	 * Get Events List tbody
	 *
	 * @param array $events Events List.
	 */
	public function mmt_eo_report_get_author_events_list( $events ) {
		global $mmt_eo_reports;
		$date_format = get_option( 'date_format' );
		$time_format = get_option( 'time_format' );

		$authors = array();
		foreach ( $events as $event ) {
			$authors[ $event['author'] ][] = $event;
		}
		$total_events  = count( $events );
		$total_authors = count( $authors );

		$top_author_id = null;
		$output        = '';

		if ( empty( $events ) ) {
			$output .= '<tr>';
			$output .= '<td colspan="5" align="center">';
			$output .= esc_html__( 'No Event', 'mmt-eo-reports' );
			$output .= '</td>';
			$output .= '</tr>';
		} else {
			foreach ( $events as $event_ ) {
				$repeat_interval = EVO()->evo_generator->helper->get_ri_for_event( $event_ );
				$evo_event       = new EVO_Event( $event_['event_id'], $event_['event_pmv'], $repeat_interval );
				$event_pmv       = $event_['event_pmv'];
				$event_obj       = $evo_event;
				$evo_event->get_event_post();
				$is_recurring_event = $evo_event->is_repeating_event();

				$author_name = get_the_author_meta( 'display_name', $event_['author'] );

				$permalink   = $evo_event->get_permalink( $repeat_interval );
				$event_title = $event_obj->post_title;
				$event_id    = (int) $event_['event_id'];
				$title       = $evo_event->get_title();

				$event_data['event_start_unix'] = (int) $event_['event_start_unix'];
				$event_data['event_end_unix']   = (int) $event_['event_end_unix'];

				$start_date = gmdate( $date_format, $event_['event_start_unix'] );
				$start_time = gmdate( $time_format, $event_['event_start_unix'] );

				$output .= '<tr data-event_id="' . $event_['event_id'] . '" data-ri="' . $repeat_interval . '" data-title="' . $event_title . '">';
				$output .= '<td>';
				$output .= '<input type="checkbox" class="mmt_eo_single_check" name="mmt_eo_reports_single_author_event" value="' . $event_['event_id'] . '" data-ri="' . $repeat_interval . '"/>';
				$output .= '</td>';
				$output .= '<td class="title">';
				$output .= $event_title;
				$output .= '</td>';
				$output .= '<td align="center">';
				$output .= $start_date . ' - ' . $start_time;
				$output .= '</td>';
				$output .= '<td align="center" class="author_name">';
				$output .= $author_name;
				$output .= '</td>';
				$output .= '<td align="center" class="publish_date">';
				$output .= $event_['post_date'];
				$output .= '</td>';
				$output .= '<td align="center" class="status">';
				$output .= '<span class=" ' . $event_['status'] . '">' . $event_['status'] . '</span>';
				$output .= '</td>';
				$output .= '<td class="ticket_action action_col">';
				$output .= '<a href="' . $permalink . '" target="_blank" title="' . $title . '"><i class="fa fa-globe"></i></a>';
				$output .= '</td>';
				$output .= '</tr>';
			}
			$output .= '<tr class="footer">';
			$output .= '<th colspan="3" class="footer_total">';
			$output .= '</th>';
			$output .= '<th>';
			$output .= '</th>';
			$output .= '<th>';
			$output .= '</th>';
			$output .= '<th>';
			$output .= '</th>';
			$output .= '<th>';
			$output .= '</th>';
			$output .= '</tr>';
		}
		return array(
			'html'      => $output,
			'topauthor' => array(
				'id'    => $top_author_id,
				'icon'  => '<i class="mer-topseller-icon fa fa-award" title="' . esc_html__( 'Top Seller', 'mmt-eo-reports' ) . '"></i>',
				'style' => 'rgba(185, 223, 168, 0.5)',
			),
			'counter'   => array(
				'authors' => $total_authors,
				'events'  => $total_events,
			),
		);
	}
	/**
	 * Generate RSVP CSV Report
	 *
	 * @param string $type Single or Selected.
	 * @param array  $selected Event ID(s).
	 */
	public function mmt_eo_reports_generate_rsvp_csv( $type, $selected ) {
		$datetime    = new evo_datetime();
		$csv_headers = array(
			'rsvp_id'    => 'RSVP ID',
			'e_name'     => 'Event Name',
			'e_time'     => 'Event Time',
			'e_location' => 'Event Location',
			'first_name' => 'First Name',
			'last_name'  => 'Last Name',
			'email'      => 'Email',
			'phone'      => 'Phone',
			'rsvp_t'     => 'RSVP Type',
			'status'     => 'Status',
			'count'      => 'Count',
			'names'      => 'Other Attendees',
			'p_date'     => 'Publish Date',
		);
		header( 'Content-Encoding: UTF-8' );
		header( 'Content-type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename=Events_RSVP_' . gmdate( 'd-m-y' ) . '.csv' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
		echo "\xEF\xBB\xBF";
		echo esc_html( implode( ',', $csv_headers ) . "\n" );
		foreach ( $selected as $event ) {
			$edata    = explode( '_', $event );
			$event_id = $edata[0];
			$ri       = $edata[1];
			$rsvp_obj = new EVORS_Event( $event_id, $ri );
			$epmv     = get_post_custom( $event_id );
			$entries  = new WP_Query(
				array(
					'posts_per_page' => -1,
					'post_type'      => 'evo-rsvp',
					'meta_query'     => array(
						array(
							'key'     => 'e_id',
							'value'   => $event_id,
							'compare' => '=',
						),
					),
				)
			);
			if ( $entries->have_posts() ) :
				$array = EVORS()->rsvp_array;
				while ( $entries->have_posts() ) :
					$entries->the_post();
					$rsvp_id  = get_the_ID();
					$rsvp_cpt = new EVO_RSVP_CPT( $rsvp_id );
					$rpmv     = $rsvp_cpt->pmv;
					$repeat_i = $rsvp_cpt->repeat_interval();
					$event_o  = new EVO_Event( $event_id, $epmv, $repeat_i );

					$event_times = $datetime->get_correct_event_time( $event_id, $repeat_i );
					$event_time  = $datetime->get_formatted_smart_time( $event_times['start'], $event_times['end'], '', $event_id );
					foreach ( $csv_headers as $field => $header ) {
						$switch_run = false;
						switch ( $field ) {
							case 'rsvp_id':
								echo esc_html( $rsvp_id . ',' );
								$switch_run = true;
								break;
							case 'e_name':
								echo esc_html( get_the_title( $event_id ) . ',' );
								$switch_run = true;
								break;
							case 'status':
								$_checkin_st    = ( $rsvp_cpt->get_prop( 'status' ) ) ? $rsvp_cpt->get_prop( 'status' ) : '';
								$checkin_status = EVORS()->frontend->get_checkin_status( $_checkin_st );
								echo esc_html( $checkin_status . ',' );
								$switch_run = true;
								break;
							case 'rsvp_t':
								echo esc_html( $rsvp_cpt->get_rsvp_type() . ',' );
								$switch_run = true;
								break;
							case 'e_time':
								echo esc_html( str_replace( ',', '', $event_time ) . ',' );
								$switch_run = true;
								break;
							case 'first_name':
								if ( isset( $rpmv['first_name'][0] ) && ! empty( $rpmv['first_name'][0] ) ) {
									$first_name = $rpmv['first_name'][0];
									echo esc_html( $first_name ) . ',';
								} else {
									echo ',';
								}
								$switch_run = true;
								break;
							case 'last_name':
								if ( isset( $rpmv['last_name'][0] ) && ! empty( $rpmv['last_name'][0] ) ) {
									$last_name = $rpmv['last_name'][0];
									echo esc_html( $last_name ) . ',';
								} else {
									echo ',';
								}
								$switch_run = true;
								break;
							case 'e_location':
								$location = $event_o->get_location_data();
								if ( $location ) {
									$name = isset( $location['name'] ) ? $location['name'] : '';
									echo esc_html( $name ) . ',';
								} else {
									echo ',';
								}
								$switch_run = true;
								break;
							case 'p_date':
								echo esc_html( str_replace( ',', '', get_the_date() ) . ',' );
								$switch_run = true;
								break;
						}

						if ( $switch_run ) {
							continue;
						}
						if ( isset( $rpmv[ $field ] ) ) {
							$cover = in_array( $field, array( 'last_name', 'first_name', 'email', 'phone' ), true ) ? '' : '"';
							echo esc_html( $cover . $rpmv[ $field ][0] . $cover );
						} else {
							echo '';
						}
						echo ',';
					}
					echo "\n";
				endwhile;
			endif;
		} // Ends For Each
		exit;
	}
	/**
	 * Generate Ticket CSV Report
	 *
	 * @param string $type Single or Selected.
	 * @param array  $selected Event ID(s).
	 */
	public function mmt_eo_reports_generate_ticket_csv( $type, $selected ) {
		$datetime    = new evo_datetime();
		$csv_headers = array(
			'event_name'   => 'Event Name',
			'ticket_num'   => 'Ticket Number',
			'name'         => 'Name',
			'email'        => 'Email',
			'address'      => 'Address',
			'phone'        => 'Phone',
			'event_time'   => 'Event Time',
			'ticket_type'  => 'Ticket Type',
			'order_status' => 'Order Status',
			'order_date'   => 'Order Date',
		);
		header( 'Content-Encoding: UTF-8' );
		header( 'Content-type: text/csv; charset=UTF-8' );
		header( 'Content-Disposition: attachment; filename=Events_Tickets_' . gmdate( 'd-m-y' ) . '.csv' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );
		echo "\xEF\xBB\xBF";
		echo esc_html( implode( ',', $csv_headers ) . "\n" );
		foreach ( $selected as $event ) {
			$edata    = explode( '_', $event );
			$event_id = $edata[0];
			$ri       = $edata[1];
			$rsvp_obj = new EVO_Event( $event_id, $ri );
			$epmv     = get_post_custom( $event_id );

			$evotx_attendees = new EVOTX_Attendees();
			$event_tickets   = $evotx_attendees->get_tickets_for_event( $event_id );
			if ( $event_tickets ) :
				foreach ( $event_tickets as $tn => $td ) :
					$event_o = new EVO_Event( $event_id, $epmv, $ri );

					$event_times = $datetime->get_correct_event_time( $event_id, $ri );
					$event_time  = $datetime->get_formatted_smart_time( $event_times['start'], $event_times['end'], '', $event_id );
					foreach ( $csv_headers as $field => $header ) {
						$switch_run = false;
						switch ( $field ) {
							case 'event_name':
								echo esc_html( get_the_title( $event_id ) . ',' );
								$switch_run = true;
								break;
							case 'ticket_num':
								echo esc_html( $tn . ',' );
								$switch_run = true;
								break;
							case 'name':
								echo esc_html( $td['n'] . ',' );
								$switch_run = true;
								break;
							case 'email':
								echo esc_html( $td['e'] . ',' );
								$switch_run = true;
								break;
							case 'address':
								echo esc_html( str_replace( '"', '', $td['aD'] ) . ',' );
								$switch_run = true;
								break;
							case 'phone':
								$phone = isset( $td['phone'] ) ? $td['phone'] : '';
								echo esc_html( $phone . ',' );
								$switch_run = true;
								break;
							case 'event_time':
								echo esc_html( str_replace( ',', '', $event_time ) . ',' );
								$switch_run = true;
								break;
							case 'ticket_type':
								echo esc_html( $td['type'] . ',' );
								$switch_run = true;
								break;
							case 'order_status':
								echo esc_html( $td['oS'] . ',' );
								$switch_run = true;
								break;
							case 'order_date':
								$od = isset( $td['oD']['ordered_date'] ) ? $td['oD']['ordered_date'] : '';
								echo esc_html( $od );
								$switch_run = true;
								break;
						}

						if ( $switch_run ) {
							continue;
						}
					}
					echo "\n";
				endforeach;
			endif;
		} // Ends For Each
		exit;
	}
	/**
	 * Get Chart Data
	 *
	 * @param array  $events Events List.
	 * @param string $title Title (date).
	 */
	public function mmt_eo_report_get_author_wise_chart( $events, $title ) {
		$chart   = array();
		$authors = array();
		foreach ( $events as $event ) {
			$authors[ $event['author'] ][] = $event;
		}
		foreach ( $authors as $author_id => $events ) {
			$author_name           = get_the_author_meta( 'display_name', $author_id );
			$chart[ $author_name ] = count( $events );
		}
		return array(
			'chart' => $chart,
			'title' => $title,
		);
	}

}
