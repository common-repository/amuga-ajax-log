<?php
/*---------------------------------------------------------------------------
Amuga Ajax Log - Functions
---------------------------------------------------------------------------*/

/*---------------------------------------------------------------------------
 * Don't allow direct access
---------------------------------------------------------------------------*/
if( !defined( 'ABSPATH' ) ) exit;

/*---------------------------------------------------------------------------
 * Amuga Class Autoloader
---------------------------------------------------------------------------*/
spl_autoload_register(
    function ( $class_name ) {

    /*---------------------------------------------------------------------------
     * Get a file
    ---------------------------------------------------------------------------*/
		$class_file = str_replace( '\\', '/', $class_name );

    /*---------------------------------------------------------------------------
     * Only autoload our classes. If we don't do this, we'll screw up a bunch of
     * plugins and basically break the world.
    ---------------------------------------------------------------------------*/
		if( file_exists( AMUGA_AL_DIR . 'classes/' . $class_file . '.php' ) )
			require_once AMUGA_AL_DIR . 'classes/' . $class_file . '.php';
    }
);


/*---------------------------------------------------------------------------
 * Initialize the plugin
---------------------------------------------------------------------------*/
function amuga_al_init(){

  /*---------------------------------------------------------------------------
   * Hook into Admin Init
  ---------------------------------------------------------------------------*/
  add_action( 'admin_init', array( 'Amuga_AL_Logger', 'logit' ) );

  new Amuga_AL_DB; // Load the DB Stuff
  new Amuga_AL_Display; // Start the records display page
  new Amuga_AL_Settings; // Start the options page

}

/*---------------------------------------------------------------------------
 * Add Style
 *
 * We need some CSS. So load it up!
---------------------------------------------------------------------------*/
if( !function_exists( 'amuga_al_style' ) ){
  function amuga_al_style() {

    wp_register_style(
      'amuga-ajax-log',
      AMUGA_AL_URL . 'assets/css/admin.css',
      false,
      '1.0',
      'all'
    );
    wp_enqueue_style( 'amuga-ajax-log' );

  }
  add_action( 'admin_enqueue_scripts', 'amuga_al_style' );
}


/*---------------------------------------------------------------------------
 * Logo
 *
 * Just our logo
---------------------------------------------------------------------------*/
if( !function_exists( 'amuga_al_logo' ) ){
  function amuga_al_logo( $text = '' ){
    ?>
    <div class="amuga-al-logo">
      <img src="<?php echo AMUGA_AL_URL;?>assets/img/amuga-themes-logo-a-60.jpg" />
      <?php
        if( !empty( $text ) ){
          echo '<h1>' . esc_attr( $text ) . '</h1>';
        }
      ?>
    </div>

    <?php
  }
}

/*---------------------------------------------------------------------------
 * Logging Enabled?
 *
 * Check to see if logging is enabled
---------------------------------------------------------------------------*/
if( !function_exists( 'amuga_al_logging' ) ){
  function amuga_al_logging(){

    /*---------------------------------------------------------------------------
     * Get our settings
    ---------------------------------------------------------------------------*/
    $options = get_option( 'amuga_al_settings' );

    /*---------------------------------------------------------------------------
     * If empty, logging is enabled. Otherwise, it's disabled.
    ---------------------------------------------------------------------------*/
  	return ( empty( $options[ 'stoplog' ] ) ) ? true : false;

  }
}


/*---------------------------------------------------------------------------
 * Clear data on deactivation
 *
 * If the option to clear all data on deactiation is set, check for our log
 * file first and kill that, followed by the data from the database.
---------------------------------------------------------------------------*/
if( !function_exists( 'amuga_al_clean_deactivate' ) ){
  function amuga_al_clean_deactivate(){

    /*---------------------------------------------------------------------------
     * Get our settings
    ---------------------------------------------------------------------------*/
    $options = get_option( 'amuga_al_settings' );

    /*---------------------------------------------------------------------------
     * If set, let's clear the data
    ---------------------------------------------------------------------------*/
  	if( !empty( $options[ 'uninstall_data' ] ) ){

      /*---------------------------------------------------------------------------
       * Kill our file if it exists
      ---------------------------------------------------------------------------*/
      $file_path = WP_CONTENT_DIR . '/admin-ajax-log.txt';
      if( file_exists( $file_path ) ){
        wp_delete_file( $file_path ); //delete file here.
      }

      /*---------------------------------------------------------------------------
       * Clear all database data
      ---------------------------------------------------------------------------*/
      Amuga_AL_DB::deactivate;
    }

  }
}



/*---------------------------------------------------------------------------
 * Messages
 *
 * We may use this for more in the future, which is why it's a function.
---------------------------------------------------------------------------*/
if( !function_exists( 'amuga_aal_msg' ) ){
  function amuga_aal_msg( $var ){

    switch( $var ){
      case "failed_location":
        $msg = __( 'Unable to locate. Try using SSH to grep for the function name or action.', 'amuga-ajax-log' );
      break;
      case "built_in":
        $msg = __( 'This process may be part of the WordPress Core.', 'amuga-ajax-log' );
      break;
      default:
      $msg = '';
      break;
    }
    return $msg;
  }
}

/*---------------------------------------------------------------------------
 * Built-in Ajax Gets
 *
 * This is a copy of the Gets from this file: wp-admin/admin-ajax.php
 * This is used to detect known functionless ajax requests
---------------------------------------------------------------------------*/
if( !function_exists( 'amuga_aal_wp_get_list' ) ){
  function amuga_aal_wp_get_list(){

    return array(
    	'fetch-list',
    	'ajax-tag-search',
    	'wp-compression-test',
    	'imgedit-preview',
    	'oembed-cache',
    	'autocomplete-user',
    	'dashboard-widgets',
    	'logged-in',
    	'rest-nonce',
    );

  }
}

/*---------------------------------------------------------------------------
 * Built-in Ajax Posts
 *
 * This is a copy of the Posts from this file: wp-admin/admin-ajax.php
 * This is used to detect known functionless ajax requests
---------------------------------------------------------------------------*/

if( !function_exists( 'amuga_aal_wp_post_list' ) ){
  function amuga_aal_wp_post_list(){

    $core_actions_post = array(
    	'oembed-cache',
    	'image-editor',
    	'delete-comment',
    	'delete-tag',
    	'delete-link',
    	'delete-meta',
    	'delete-post',
    	'trash-post',
    	'untrash-post',
    	'delete-page',
    	'dim-comment',
    	'add-link-category',
    	'add-tag',
    	'get-tagcloud',
    	'get-comments',
    	'replyto-comment',
    	'edit-comment',
    	'add-menu-item',
    	'add-meta',
    	'add-user',
    	'closed-postboxes',
    	'hidden-columns',
    	'update-welcome-panel',
    	'menu-get-metabox',
    	'wp-link-ajax',
    	'menu-locations-save',
    	'menu-quick-search',
    	'meta-box-order',
    	'get-permalink',
    	'sample-permalink',
    	'inline-save',
    	'inline-save-tax',
    	'find_posts',
    	'widgets-order',
    	'save-widget',
    	'delete-inactive-widgets',
    	'set-post-thumbnail',
    	'date_format',
    	'time_format',
    	'wp-remove-post-lock',
    	'dismiss-wp-pointer',
    	'upload-attachment',
    	'get-attachment',
    	'query-attachments',
    	'save-attachment',
    	'save-attachment-compat',
    	'send-link-to-editor',
    	'send-attachment-to-editor',
    	'save-attachment-order',
    	'media-create-image-subsizes',
    	'heartbeat',
    	'get-revision-diffs',
    	'save-user-color-scheme',
    	'update-widget',
    	'query-themes',
    	'parse-embed',
    	'set-attachment-thumbnail',
    	'parse-media-shortcode',
    	'destroy-sessions',
    	'install-plugin',
    	'update-plugin',
    	'crop-image',
    	'generate-password',
    	'save-wporg-username',
    	'delete-plugin',
    	'search-plugins',
    	'search-install-plugins',
    	'activate-plugin',
    	'update-theme',
    	'delete-theme',
    	'install-theme',
    	'get-post-thumbnail-html',
    	'get-community-events',
    	'edit-theme-plugin-file',
    	'wp-privacy-export-personal-data',
    	'wp-privacy-erase-personal-data',
    	'health-check-site-status-result',
    	'health-check-dotorg-communication',
    	'health-check-is-in-debug-mode',
    	'health-check-background-updates',
    	'health-check-loopback-requests',
    	'health-check-get-sizes',
    	'toggle-auto-updates',
    );

    // Deprecated.
    $core_actions_post_deprecated = array( 'wp-fullscreen-save-post', 'press-this-save-post', 'press-this-add-category' );

    return array_merge( $core_actions_post, $core_actions_post_deprecated );

  }
}
