<?php
/**
 * MMT EventON - Reports - Page General Report
 *
 * @package mmt-eo-reports
 * @author MoMo Themes
 * @since v1.0
 */

?>
<div class="mer-body-content mer-data-body-container upcoming-container-data" data-ranger="weekly" data-index="0" data-type="general">
	<h3><?php esc_html_e( 'General Report', 'mmt-eo-reports' ); ?></h3>
	<div class="mer-content-wrap">
		<div class="mer-row">
			<div class="mer-card">
				<div class="mer-card-header">
					<div class="mer-header-title">
						<span>
							<?php esc_html_e( 'Upcoming Events', 'mmt-eo-reports' ); ?>
						</span>
					</div>
				</div>
				<div class="mer-card-middle">
					<div class="mer-card-left">
						<span class="mer-range-lr" data-sym="-"><i class="fa fa-angle-left"></i></span>
						<span class="mer-range-lr" data-sym="+"><i class="fa fa-angle-right"></i></span>
					</div>
					<div class="mer-card-right">
						<ul class="mer-range-picker">
							<li class="mer-range current" data-range="weekly">
								<?php esc_html_e( 'Weekly', 'mmt-eo-reports' ); ?>
							</li>
							<li class="mer-range" data-range="monthly">
								<?php esc_html_e( 'Monthly', 'mmt-eo-reports' ); ?>
							</li>
							<li class="mer-range" data-range="yearly">
								<?php esc_html_e( 'Yearly', 'mmt-eo-reports' ); ?>
							</li>
						</ul>
					</div>
					<div class="mer-clear"></div>
				</div>
				<div class="mer-card-body mer-mh-200">
					<div class="mer-upcoming-chart">
						<canvas id="DashboardUpcomingReport" width="100%"></canvas>
					</div>
				</div>
				<div class="mer-card-footer mer-mh-100 upcoming-events-list">
					<span class="mer-table-date-range"></span>
					<table class="mer-table">
						<thead>
							<tr>
								<th>
									<?php esc_html_e( 'Event Name', 'mmt-eo-reports' ); ?>
								</th>
								<th>
									<?php esc_html_e( 'Event Date', 'mmt-eo-reports' ); ?>
								</th>
								<th>
								</th>
							</tr>
						</thead>
						<tbody class="upcoming-events-list-body">
							<tr>
								<td colspan="3" align="center">
								<?php esc_html_e( 'No Event', 'mmt-eo-reports' ); ?>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</div>
</div><!-- ends .mer-body-content -->
