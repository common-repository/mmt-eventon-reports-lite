<?php
/**
 * Eventon Reports Popbox Ajax
 *
 * @package mmt-eo-reports
 * @author MoMo Themes
 * @since v1.2
 */
class MMT_EO_Reports_Popbox_Ajax {
	/**
	 * Constructor
	 */
	public function __construct() {
		$ajax_events = array(
			'single_sales_insight' => 'mmt_eo_report_single_sales_insight',
		);
		foreach ( $ajax_events as $ajax_event => $class ) {
			add_action( 'wp_ajax_mmt_eo_report_' . $ajax_event, array( $this, $class ) );
			add_action( 'wp_ajax_nopriv_mmt_eo_report_' . $ajax_event, array( $this, $class ) );
		}
	}
	/**
	 * Generate Upcoming Chart
	 */
	public function mmt_eo_report_single_sales_insight() {
		global $mmt_eo_reports;
		check_ajax_referer( 'mmt_eo_reports_security_key', 'mmt_eo_reports_nonce' );
		if ( isset( $_POST['action'] ) && 'mmt_eo_report_single_sales_insight' !== $_POST['action'] ) {
			return;
		}
		$data     = isset( $_POST['data'] ) && is_array( $_POST['data'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['data'] ) ) : array();
		$event_id = isset( $data['event_id'] ) ? $data['event_id'] : false;
		$ri       = isset( $data['ri'] ) ? $data['ri'] : 0;
		if ( ! $event_id ) {
			return;
		}
		ob_start();

		$source = false;
		$ri     = 'all'; // repeat interval.

		$evotx_attendees = new EVOTX_Attendees();
		$json            = $evotx_attendees->get_tickets_for_event( $event_id, $source );
		$evotx_event     = new evotx_event( $event_id, '', $ri );
		$evotx_data      = $mmt_eo_reports->helper->mmt_eo_reports_get_sales_data( $evotx_event );

		$remaining_tickets = is_bool( $evotx_event->has_tickets() ) ? 0 : $evotx_event->has_tickets();

		$stock     = ( 0 === $remaining_tickets ) ? '<i class="fa fa-infinity"></i>' : $remaining_tickets;
		$sold      = 0;
		$pending   = 0;
		$cancelled = 0;
		$refunded  = 0;

		if ( count( $evotx_data['sales_data'] ) > 0 ) {
			foreach ( $evotx_data['sales_data'] as $id => $data ) {
				switch ( $data['order_status'] ) {
					case 'completed':
						$sold = $data['qty'];
						break;
					case 'processing':
						$pending = $data['qty'];
						break;
					case 'cancelled':
						$cancelled = $data['qty'];
						break;
					case 'refunded':
						$refunded = $data['qty'];
						break;
				}
			}
		}
		$total = $stock + $sold + $pending + $cancelled + $refunded;
		if ( 0 === $remaining_tickets ) {
			$stock_pc = 100;
		} else {
			$stock_pc = ( $stock / $total ) * 100;
		}
		$sold_pc      = ( $sold / $total ) * 100;
		$pending_pc   = ( $pending / $total ) * 100;
		$cancelled_pc = ( $cancelled / $total ) * 100;
		$refunded_pc  = ( $refunded / $total ) * 100;
		?>
		<div class="mer-sales-insight-single">
		<?php
		if ( ! count( $json ) > 0 ) {
			?>
			<div class='mer-popbox-nodata'>
			<p class='nodata'><?php esc_html_e( 'Could not find attendees with completed orders.', 'mmt-er-reports' ); ?></p>	
			</div>
			<?php
		} else {
			?>
			<div class="mer-attn-holder">
				<span class="title">
					<?php esc_html_e( 'Attendee(s) List', 'mmt-eo-reports' ); ?>
				</span>
				<table class="mer-table">
					<thead>
						<tr>
							<th>
								<?php esc_html_e( 'Ticket No.', 'mmt-eo-reports' ); ?>
							</th>
							<th>
								<?php esc_html_e( 'Ticket Holder', 'mmt-eo-reports' ); ?>
							</th>
							<th>
								<?php esc_html_e( 'Order Status', 'mmt-eo-reports' ); ?>
							</th>
							<th>
								<?php esc_html_e( 'Payment type', 'mmt-eo-reports' ); ?>
							</th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ( $json as $ticket_number => $details ) : ?>
						<tr>
							<td>
								<?php echo esc_html( $ticket_number ); ?>
							</td>
							<td>
								<?php echo esc_html( $details['name'] ); ?>
							</td>
							<td>
								<?php echo esc_html( $details['oS'] ); ?>
							</td>
							<td>
								<?php echo esc_html( $details['payment_method'] ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
					</tbody>
				</table>
			</div>
			<?php
		}
		?>
		</div><!-- ends mer-sales-insight-single -->
		<?php
		$content = ob_get_clean();
		echo wp_json_encode(
			array(
				'status' => 'good',
				'html'   => $content,
			)
		);
		exit;
	}
}
new MMT_EO_Reports_Popbox_Ajax();
