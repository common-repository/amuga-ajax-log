<?php
/*---------------------------------------------------------------------------
 * Amuga Ajax Log - Logger
 *
 * This script handles the capture and logging of information.
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
 * Logger Class
 *
 * This class handles all hit logging, both for file and database logging.
---------------------------------------------------------------------------*/
class Amuga_AL_Logger{

  private $data = array();

  public static function init(){}

  /*---------------------------------------------------------------------------
   * Find the function that belongs to this action
  ---------------------------------------------------------------------------*/
  public static function find_function( $function_name, $action ) {

  	/*---------------------------------------------------------------------------
  	 * Is there a function with the ajaxified name?
  	 * Nope? OK, well is there a function without the ajaxified name?
  	 * Still no? Suggest the user use SSH to do a GREP search
  	---------------------------------------------------------------------------*/

  	if( function_exists( $function_name ) ){
  		$ref = new ReflectionFunction( $function_name );
  		return $ref->getFileName();
  	} elseif( function_exists( $action ) ){
  		$ref = new ReflectionFunction( $action );
  		return $ref->getFileName();
  	} else{
  		return false;
  	}
  }

  /*---------------------------------------------------------------------------
   * Find the method that belongs to this action
  ---------------------------------------------------------------------------*/
  public static function find_method( $function_name, $action ) {

  	/*---------------------------------------------------------------------------
  	 * This will be harder
  	 * Because classes can share method names (eg. MyClass->Thing, Otherclass->Thing),
     * we need to pull all declared classes and look for method names that match.
  	 * If there are a ton of classes and/or they have a ton of the same method
     * name, this may get resource intensive. This can be disabled via a constant.
     *
     * Example: define( 'AMUGA_AL_METHOD_SKIP', true );
     * Set the define in wp-config.php
  	---------------------------------------------------------------------------*/

    /*---------------------------------------------------------------------------
     * Method Skip
     *
     * As mentioned above, this can be resource intensive so we are adding the
     * option to skip.
    ---------------------------------------------------------------------------*/
    if( defined( 'AMUGA_AL_METHOD_SKIP' ) ){
      return false;
    }

    /*---------------------------------------------------------------------------
     * Get the list of declared classes
    ---------------------------------------------------------------------------*/
    $class_list = get_declared_classes();

	  $class_file_location = array();

		foreach( $class_list as $class_name ){

			$rf_class = new ReflectionClass( $class_name );

      /*---------------------------------------------------------------------------
       * Check for function name first
      ---------------------------------------------------------------------------*/
			if( $rf_class->hasMethod ( $function_name ) ){
				$class_file_location[] = $rf_class->getFileName();
			}

      /*---------------------------------------------------------------------------
       * Check for action name
      ---------------------------------------------------------------------------*/
      if( $rf_class->hasMethod ( $action ) ){
				$class_file_location[] = $rf_class->getFileName();
			}

		}

    if( !empty( $class_file_location ) ){
      return $class_file_location;
    }else {
      return false;
    }

  }

  /*---------------------------------------------------------------------------
   * Log to File
   *
   * We can log our data to a file rather than the database
  ---------------------------------------------------------------------------*/
  public static function logToFile( $data ){

    /*---------------------------------------------------------------------------
     * Create the string of log data
    ---------------------------------------------------------------------------*/
    $logtext = "When: " . date("Y-m-d H:i:s") . "\r\n" .
    "Action: " . esc_attr( $data['action'] ) . "\r\n" .
    "Request Type: " . esc_attr( $data['request_type'] ) . "\r\n" .
    "Function Name: " . esc_attr( $data['fname'] ) . "\r\n" .
    "File Location: " . esc_attr( $data['location'] ) . "\r\n" .
    "HTTP Referrer: " . esc_attr( $data['request_url'] ) . "\r\n\r\n" .
    "-------------------\r\n\r\n";

    /*---------------------------------------------------------------------------
     * Open the file, write to it, close it
    ---------------------------------------------------------------------------*/
    $open = fopen( WP_CONTENT_DIR . '/admin-ajax-log.txt', "a" );
    $write = fputs( $open, $logtext );
    fclose( $open );
  }

  /*---------------------------------------------------------------------------
   * Log to Database
   *
   * Log the captured data to a custom database table
   *
   * Note: $data gets sanitized in
  ---------------------------------------------------------------------------*/
  public static function logToDB( $data ){
    global $wpdb;

    /*---------------------------------------------------------------------------
     * Serialize some variables for additional troubleshooting
     *
     * This can be used to store other information if needed.
    ---------------------------------------------------------------------------*/
    $ts_data = serialize(
      array(
        'fname'         => $data['fname'],
        'request_url'   => $data['request_url'],
      )
    );

    /*---------------------------------------------------------------------------
     * Insert into the DB
     * Note: We Sanitize this in logit
    ---------------------------------------------------------------------------*/
    $wpdb->insert(
      AMUGA_AL_TABLE,
      array(
        'time'            => $data['when'],
        'action'          => $data['action'],
        'request_type'    => $data['request_type'],
        'file_location'   => $data['location'],
        'ts_data'         => $ts_data
      )
    );
  }


  /*---------------------------------------------------------------------------
   * Prepend Function Name
   *
   * Determine if we should use wp_ajax_ or wp_ajax_nopriv_
  ---------------------------------------------------------------------------*/
  public static function prepend_function(){
    return ( is_user_logged_in() ) ? 'wp_ajax_' : 'wp_ajax_nopriv_';
  }


  /*---------------------------------------------------------------------------
   * Log the hit
  ---------------------------------------------------------------------------*/
  public static function logit(){

    /*---------------------------------------------------------------------------
     * Is logging disabled? If so, leave.
    ---------------------------------------------------------------------------*/
    if( !amuga_al_logging() )
  		return;

    /*---------------------------------------------------------------------------
     * Is this an admin-ajax run? No? Later.
    ---------------------------------------------------------------------------*/
    if( !wp_doing_ajax() )
  		return;

    /*---------------------------------------------------------------------------
     * Get our settings
    ---------------------------------------------------------------------------*/
    $options = get_option( 'amuga_al_settings' );

    /*---------------------------------------------------------------------------
     * Pull apart the data
     *
     * Here we are going to take apart the data, build some other data and send
     * it to be stored in our log.
    ---------------------------------------------------------------------------*/
    if( isset( $_REQUEST['action'] ) ){

      $data['when'] = sanitize_text_field( wp_date( 'Y-m-d H:i:s' ) );

      /*---------------------------------------------------------------------------
       * Get the action being run
      ---------------------------------------------------------------------------*/
      $data['action'] = sanitize_text_field( $_REQUEST['action'] );

      /*---------------------------------------------------------------------------
       * Log the suspected function name
      ---------------------------------------------------------------------------*/
      $data['fname'] = self::prepend_function() . $data['action'];

      /*---------------------------------------------------------------------------
       * Get the file location if we can figure it out
      ---------------------------------------------------------------------------*/
		  $data['location'] = self::find_function( $data['fname'], $data['action'] );

      /*---------------------------------------------------------------------------
       * Get the class file location if we can figure it out
      ---------------------------------------------------------------------------*/
		  $data['location'] = ( empty( $data['location'] ) ) ? self::find_method( $data['fname'], $data['action'] ):'';

      /*---------------------------------------------------------------------------
       * Location Check - Array
       *
       * If our location was found in a class, it may have been returned as an array
      ---------------------------------------------------------------------------*/
      if( !empty( $data['location'] ) && is_array( $data['location'] ) ){
        $data['location'] = array_map( 'sanitize_text_field', $data['location'] ); //Sanitize it
        $data['location'] = serialize( $data['location'] ); //Serialize it
      } else{
        $data['location'] = sanitize_text_field( $data['location'] ); //Sanitize it
      }

      /*---------------------------------------------------------------------------
       * Location Check - Failed
       *
       * If we can't determine a location, check to see if this is a known built-in
       * action. If so, notify the user that it may be part of WordPress Core. If
       * not, suggest a grep search via SSH.
      ---------------------------------------------------------------------------*/
      if( empty( $data['location'] ) ){
        if( in_array( $data['action'], amuga_aal_wp_get_list() ) || in_array( $data['action'], amuga_aal_wp_post_list() ) ){
          $data['location'] = amuga_aal_msg( 'built_in' );
        }else{
          $data['location'] = amuga_aal_msg( 'failed_location' ); //Not sure where this is at
        }
      }

      /*---------------------------------------------------------------------------
       * Request Type
      ---------------------------------------------------------------------------*/
  		$data['request_type'] = ( !empty( $_GET['action'] ) ) ? 'GET' : 'POST';

      /*---------------------------------------------------------------------------
       * Request URL
      ---------------------------------------------------------------------------*/
      $data['request_url'] = ( !empty( $_SERVER['HTTP_REFERER'] ) ) ? esc_url_raw( $_SERVER['HTTP_REFERER'] ) : '';

      /*---------------------------------------------------------------------------
       * Log to File or database
      ---------------------------------------------------------------------------*/
      if( empty( $options['storage'] ) || 'db' == $options['storage'] ){
        self::logToDB( $data );
      } else{
        self::logToFile( $data );
      }
  	}else{
      return; // Reqeust was not set
    }
  }
}
