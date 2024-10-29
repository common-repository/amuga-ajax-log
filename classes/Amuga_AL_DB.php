<?php
/*---------------------------------------------------------------------------
 * Amuga Ajax Log - Database Stuff
 *
 * This script creates our database table and purges database data created by
 * our plugin upon deactivation.
 *
 * @link https://amugathemes.com
 *
 * @package Amuga Ajax Log
 * @since 1.0
 * @version 1.0
---------------------------------------------------------------------------*/

/*---------------------------------------------------------------------------
 * Don't allow direct access
---------------------------------------------------------------------------*/
if( !defined( 'ABSPATH' ) ) exit;

/*---------------------------------------------------------------------------
 * Include WP Upgrade file
---------------------------------------------------------------------------*/
require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

/*---------------------------------------------------------------------------
 * Database Class
 *
 * This class handles our data table setup.
---------------------------------------------------------------------------*/
class Amuga_AL_DB{

  /*---------------------------------------------------------------------------
   * Install the table and DB version
  ---------------------------------------------------------------------------*/
  static function activate(){
    global $wpdb;

  	/*---------------------------------------------------------------------------
  	 * Get current db version
  	---------------------------------------------------------------------------*/
  	$current_db_version = get_option( 'amuga_al_db_version' );

  	/*---------------------------------------------------------------------------
  	 * Character set
  	---------------------------------------------------------------------------*/
  	$charset_collate = $wpdb->get_charset_collate();

  	/*---------------------------------------------------------------------------
  	 * Create our table
  	---------------------------------------------------------------------------*/
		$sql = "CREATE TABLE " . AMUGA_AL_TABLE . " (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			action tinytext NOT NULL,
			request_type tinytext NOT NULL,
			file_location text NOT NULL,
			ts_data text NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		dbDelta( $sql );
		add_option( 'amuga_al_db_version', AMUGA_AL_DB );

    if( empty( $current_db_version ) ){
      add_option( 'amuga_al_db_version', AMUGA_AL_DB );
    }

  }

  /*---------------------------------------------------------------------------
   * Uninstall the data
   *
   * If uninstall_data is set, pull out all of our captured and set data
  ---------------------------------------------------------------------------*/
  static function deactivate(){

    global $wpdb;
  	$options = get_option( 'amuga_al_settings' );

  	if( !empty( $options[ 'uninstall_data' ] ) ){

  		/*---------------------------------------------------------------------------
  		 * Get rid of our options data
  		---------------------------------------------------------------------------*/
  		delete_option( 'amuga_al_db_version' );
  		delete_option( 'amuga_al_settings' );

  		/*---------------------------------------------------------------------------
  		 * Drop the log table
  		---------------------------------------------------------------------------*/
  		$wpdb->query( "DROP TABLE IF EXISTS " . AMUGA_AL_TABLE );
  	}
  }

}
