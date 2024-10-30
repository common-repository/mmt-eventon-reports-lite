<?php

/**
 * Plugin Name: MMT - EventON - Reports
 * Description: EventON addons to display reports on EventON Events.
 * Text Domain: mmt-eo-reports
 * Domain Path: /languages
 * Author: MoMo Themes
 * Version: 2.0.0
 * Author URI: http://www.momothemes.com/
 * Requires at least: 5.4.0
 * Tested up to: 6.5.3
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */
if ( !function_exists( 'mmt_eventon_report_fs' ) ) {
    /**
     * Create a helper function for easy SDK access.
     */
    function mmt_eventon_report_fs() {
        global $mmt_eventon_report_fs;
        if ( !isset( $mmt_eventon_report_fs ) ) {
            // Include Freemius SDK.
            require_once __DIR__ . '/freemius/start.php';
            $mmt_eventon_report_fs = fs_dynamic_init( array(
                'id'             => '15630',
                'slug'           => 'mmt-eventon-reports-lite',
                'type'           => 'plugin',
                'public_key'     => 'pk_e4a3a568ef696b9865d76af8a9746',
                'is_premium'     => false,
                'has_addons'     => false,
                'has_paid_plans' => true,
                'menu'           => array(
                    'slug'    => 'mmt_eo_reports',
                    'account' => true,
                    'support' => true,
                    'parent'  => array(
                        'slug' => 'eventon',
                    ),
                ),
                'is_live'        => true,
            ) );
        }
        return $mmt_eventon_report_fs;
    }

    // Init Freemius.
    mmt_eventon_report_fs();
    // Signal that SDK was initiated.
    do_action( 'mmt_eventon_report_fs_loaded' );
}
class MMT_Eventon_Reports {
    /**
     * Plugin Version
     *
     * @var string
     */
    public $version = '1.3';

    /**
     * Plugin Name
     *
     * @var string
     */
    public $name = 'MMT Eventon Reports - Pro';

    /**
     * Plugin URL
     *
     * @var string
     */
    public $plugin_url;

    /**
     * Plugin Slug
     *
     * @var string
     */
    public $plugin_slug = 'mmt-eo-reports';

    /**
     * Constructor
     */
    public function __construct() {
        add_action( 'plugins_loaded', array($this, 'plugin_init') );
        add_action( 'admin_init', array($this, 'mmt_eo_reports_register_settings') );
    }

    /**
     * Plugin Init
     * Check if EventON main plugin exist
     */
    public function plugin_init() {
        if ( !isset( $GLOBALS['eventon'] ) ) {
            add_action( 'admin_notices', array($this, 'mmt_eo_reports_admin_notice') );
            return false;
        }
        include_once 'includes/class-mmt-eo-reports-search.php';
        $this->search = new MMT_EO_Reports_Search();
        $this->search->mmt_eo_reports_search_db_check_install();
        add_action( 'admin_menu', array($this, 'mmt_eo_reports_set_menu_in_eventon') );
        add_action( 'init', array($this, 'init'), 0 );
        $this->plugin_url = plugin_dir_url( __FILE__ );
    }

    /**
     * Register Settings
     */
    public function mmt_eo_reports_register_settings() {
        register_setting( 'mmt_eo_reports_options', 'mmt_eo_reports_options' );
    }

    /**
     * Eventon missing
     */
    public function mmt_eo_reports_admin_notice() {
        ?>
		<div class="message error">
			<p>
			<?php 
        printf( esc_html( '%s is enabled but not effective! - ' ), esc_html( $this->name ) );
        esc_html_e( 'You do not have EventON main plugin , which is required.', 'mmt-eo-reports' );
        ?>
			</p>
		</div>
		<?php 
    }

    /**
     * Initiate Plugin
     */
    public function init() {
        $options = get_option( 'mmt_eo_reports_options' );
        if ( is_admin() ) {
            include_once 'includes/admin/class-mmt-eo-reports-admin-script-style.php';
            include_once 'includes/admin/class-mmt-eo-reports-admin-ajax.php';
            include_once 'includes/admin/class-mmt-eo-reports-popbox-ajax.php';
            $this->ascripts = new MMT_EO_Reports_Admin_Script_Style();
        }
        include_once 'includes/class-mmt-eo-reports-events.php';
        include_once 'includes/class-mmt-eo-reports-views.php';
        include_once 'includes/class-mmt-eo-reports-helper-functions.php';
        include_once 'includes/class-mmt-eo-reports-cron.php';
        include_once 'includes/admin/class-mmt-eo-reports-admin-functions.php';
        $this->afn = new MMT_EO_Reports_Admin_Functions();
        $this->events = new MMT_EO_Reports_Events();
        $this->views = new MMT_EO_Reports_Views();
        $this->helper = new MMT_EO_Reports_Helper_Functions();
        $this->cron = new MMT_EO_Reports_Cron();
        if ( $this->helper->mmt_eo_reports_check_addons_enabled( 'eventon_au' ) ) {
            include_once 'includes/au/class-mmt-eo-reports-frontend-action-user.php';
        }
        global $evotx;
        include_once $evotx->plugin_path . '/includes/class-evo-tix.php';
        include_once $evotx->plugin_path . '/includes/class-attendees.php';
    }

    /**
     * Set submenu in Eventon Settings Menu
     */
    public function mmt_eo_reports_set_menu_in_eventon() {
        $x = add_submenu_page(
            'eventon',
            esc_html__( 'Reports', 'mmt-eo-reports' ),
            esc_html__( 'Reports', 'mmt-eo-reports' ),
            'manage_options',
            'mmt_eo_reports',
            array($this, 'mmt_eo_reports_settings_page')
        );
    }

    /**
     * Admin Settings Page
     */
    public function mmt_eo_reports_settings_page() {
        include_once 'includes/admin/admin-settings-page.php';
    }

}

// Initiate this addon within the plugin.
$GLOBALS['mmt_eo_reports'] = new MMT_Eventon_Reports();