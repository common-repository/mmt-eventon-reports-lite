<?php

/**
 * Admin Settings Page - EventON Reports
 *
 * @package mmt-eo-reports
 * @author MoMo Themes
 * @since v1.0
 */
global $mmt_eo_reports;
$total_search = $mmt_eo_reports->search->mmt_eo_reports_get_search_log_count();
?>
<div class="mer-body-main wrap">
	<span class="mer-title"><?php 
esc_html_e( 'EventON Report - MoMo Themes', 'mmt-eo-reports' );
?></span>
	<div class="mer-navbar">
		<span class="mer-nav-link active" data-nav="#mer-nav-general">
			<?php 
esc_html_e( 'General', 'mmt-eo-reports' );
?>
		</span>
		<?php 
?>
	</div><!-- ends .mer-navbar-->
	<div class="mer-clear"></div>
	<div class="mer-nav-content active" id="mer-nav-general">
		<?php 
require_once 'pages/page-admin-general.php';
?>
	</div><!-- ends .mer-nav-content -->
	<?php 
?>
</div> <!-- ends .mmt-eo-reports-body-main -->
