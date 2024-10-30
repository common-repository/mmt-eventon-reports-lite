<?php
/**
 * MMT EventON - Reports - Helper Functions
 *
 * @package mmt-eo-reports
 * @author MoMo Themes
 * @since v1.2
 */
class MMT_EO_Reports_Helper_Functions {
	/**
	 * Check addons enabled or not
	 * evotx - Ticket addons
	 * eventon_rs - RSVP addons
	 *
	 * @param string $addons addons global name.
	 */
	public function mmt_eo_reports_check_addons_enabled( $addons ) {
		if ( isset( $GLOBALS[ $addons ] ) ) {
			return true;
		}
		return false;
	}
	/**
	 * Add Currency Symbol
	 *
	 * @param mixed $price Money.
	 */
	public function add_currency( $price ) {
		$symb = get_woocommerce_currency_symbol();
		return $symb . ' ' . $price;
	}
	/**
	 * Get ticket sales data
	 *
	 * @param evotx_event $evotx_event Event.
	 */
	public function mmt_eo_reports_get_sales_data( $evotx_event ) {
		$event_id           = $evotx_event->event_id;
		$remainging_tickets = is_bool( $evotx_event->has_tickets() ) ? 0 : $evotx_event->has_tickets();
		$orders             = new WP_Query(
			array(
				'post_type'      => 'evo-tix',
				'posts_per_page' => -1,
				'meta_query'     => array(
					array(
						'key'   => '_eventid',
						'value' => $event_id,
					),
				),
			)
		);

		$sales_data            = array();
		$total_tickets_sold    = 0;
		$total_tickets_cost    = 0;
		$total_cancelled_order = 0;
		$processed_order_ids   = array();

		if ( $orders->have_posts() ) :
			while ( $orders->have_posts() ) :
				$orders->the_post();

				$order_id = get_post_meta( $orders->post->ID, '_orderid', true );

				$order_status = get_post_status( $order_id );
				if ( ! $order_status ) {
					continue;
				}
				if ( in_array( $order_id, $processed_order_ids, true ) ) {
					continue;
				}
				$order = new WC_Order( $order_id );

				if ( count( $order->get_items() ) <= 0 ) {
					continue;
				}
				$_order_qty       = 0;
				$_order_cost      = 0;
				$_cancelled_order = 0;

				$order_time      = get_the_date( 'U', $order_id );
				$billing_country = get_post_meta( $order_id, '_billing_country', true );
				$order_status    = $order->get_status();

				foreach ( $order->get_items() as $item_id => $item ) {
					$_order_event_id = ( isset( $item['_event_id'] ) ) ? $item['_event_id'] : '';
					$_order_event_id = ! empty( $_order_event_id ) ? $_order_event_id : get_post_meta( $item['product_id'], '_eventid', true );

					if ( empty( $_order_event_id ) ) {
						continue;
					}
					if ( (int) $_order_event_id !== (int) $event_id ) {
						continue;
					}

					$_order_qty  += (int) $item['qty'];
					$_order_cost += floatval( $item['subtotal'] );
					if ( 'cancelled' === $order_status ) {
						++$_cancelled_order;
					}
					$sales_data[ $item_id ] = array(
						'qty'          => (int) $item['qty'],
						'cost'         => floatval( $item['subtotal'] ),
						'order_id'     => $orders->post->ID,
						'time'         => $order_time,
						'country'      => $billing_country,
						'order_status' => $order_status,
					);

				}
				$total_tickets_sold    += $_order_qty;
				$total_tickets_cost    += $_order_cost;
				$total_cancelled_order += $_cancelled_order;
				$processed_order_ids[]  = $order_id;

			endwhile;
			wp_reset_postdata();
		endif;
		$return = array(
			'sales_data'            => $sales_data,
			'total_tickets_sold'    => $total_tickets_sold,
			'total_tickets_cost'    => $total_tickets_cost,
			'processed_order_ids'   => $processed_order_ids,
			'total_cancelled_order' => $total_cancelled_order,
		);
		return $return;
	}
	/**
	 * Generate Popbox
	 *
	 * @param array $popbox Popbox array.
	 */
	public function mmt_eo_reports_generate_popbox( $popbox = array() ) {
		$id      = isset( $popbox['id'] ) ? $popbox['id'] : '';
		$title   = isset( $popbox['title'] ) ? $popbox['title'] : '';
		$content = isset( $popbox['content'] ) ? $popbox['content'] : '';
		$ctitle  = isset( $popbox['ctitle'] ) ? $popbox['ctitle'] : esc_html__( 'Close Popbox', 'mmt-eo-reports' );
		?>
		<div class="mer-popbox" id="<?php echo esc_attr( $id ); ?>">
			<div class="mer-popbox-content">
				<a href="#" class="mer-popbox-close" title="<?php echo esc_html( $ctitle ); ?>">X</a>
				<div class="mer-popbox-header">
					<?php echo esc_html( $title ); ?>
				</div>
				<div class="mer-popbox-body">
					<?php echo esc_html( $content ); ?>
				</div>
			</div>
		</div>
		<?php
	}
	/**
	 * Get Time List array
	 */
	public function mmt_eo_reports_get_time_list_array() {
		$interval = '+30 minutes';
		$current  = strtotime( '00:00' );
		$end      = strtotime( '23:59' );
		$times    = array();
		$minutes  = 0;
		while ( $current <= $end ) {
			$time              = gmdate( 'H:i', $current );
			$times[ $minutes ] = gmdate( 'h.i A', $current );
			$current           = strtotime( $interval, $current );
			$minutes           = $minutes + 30;
		}
		return $times;
	}
	/**
	 * Get Weekdays
	 */
	public function mmt_eo_reports_get_week_days() {
		return array(
			'sunday'    => esc_html__( 'Sunday', 'mmt-eo-reports' ),
			'monday'    => esc_html__( 'Monday', 'mmt-eo-reports' ),
			'tuesday'   => esc_html__( 'Tuesday', 'mmt-eo-reports' ),
			'wednesday' => esc_html__( 'Wednesday', 'mmt-eo-reports' ),
			'thursday'  => esc_html__( 'Thursday', 'mmt-eo-reports' ),
			'friday'    => esc_html__( 'Friday', 'mmt-eo-reports' ),
			'saturday'  => esc_html__( 'Saturday', 'mmt-eo-reports' ),
		);
	}
}
