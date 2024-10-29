<?php
/*---------------------------------------------------------------------------
Plugin Name: Amuga Ajax Log
Plugin URI: https://amugathemes.com/plugins/ajax-log/
Description: Log admin-ajax hits for troubleshooting purposes
Version: 1.0
Author: Amuga Themes
Author URI: https://amugathemes.com
License: GPL3
---------------------------------------------------------------------------*/

/*---------------------------------------------------------------------------
 * Don't allow direct access
---------------------------------------------------------------------------*/
if( !defined( 'ABSPATH' ) ) exit;

global $wpdb;

/*---------------------------------------------------------------------------
 * Set some constants
---------------------------------------------------------------------------*/
define( 'AMUGA_AL_VERSION', '1.0' );
define( 'AMUGA_AL_DIR', trailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'AMUGA_AL_URL', trailingslashit( plugin_dir_url( __FILE__ ) ) );
define( 'AMUGA_AL_TABLE', $wpdb->prefix . 'amuga_ajax_log' );
define( 'AMUGA_AL_DB', '1.3' ); // Database version

/*---------------------------------------------------------------------------
 * Get our functions and autoloader
---------------------------------------------------------------------------*/
require_once( AMUGA_AL_DIR . 'functions.php' );

/*---------------------------------------------------------------------------
 * Activation and Deactivation hooks
---------------------------------------------------------------------------*/
register_activation_hook( __FILE__, array( 'Amuga_AL_DB', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'Amuga_AL_DB', 'deactivate' ) );

/*---------------------------------------------------------------------------
 * Initialize our stuff
---------------------------------------------------------------------------*/
amuga_al_init();
