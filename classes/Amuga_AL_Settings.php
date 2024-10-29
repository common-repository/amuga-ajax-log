<?php
/*---------------------------------------------------------------------------
 * Amuga Admin Ajax Log - Settings Panel
 *
 * Create, display, and process the settings panel for our plugin. This way
 * you can modify various settings the plugin applies by default.
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
 * Options Class
 *
 * This class handles our settings panel.
---------------------------------------------------------------------------*/
class Amuga_AL_Settings{

	/*---------------------------------------------------------------------------
	 * Construct
	 *
	 * Run our actions
	---------------------------------------------------------------------------*/
	function __construct() {

		add_action( 'admin_menu', array( $this, 'create_add_admin_menu' ) );
		add_action( 'admin_init', array( $this, 'settings_init' ) );

		if( isset( $_POST ) ){
			$this->purge_db();
		}
	}

	/*---------------------------------------------------------------------------
	 * Create the Admin Page and Link
	---------------------------------------------------------------------------*/
	public function create_add_admin_menu() {
		add_submenu_page(
			'amuga_ajax_log', // Parent
			'Amuga Ajax Log Settings', // Page Title
			'Settings', // Link Title
			'manage_options', // User capabilities for page access
			'amuga_ajax_log_settings', // Page slug
			array( $this, 'settings_page' ) // Function to call that generates page content
		);
	}

	/*---------------------------------------------------------------------------
	 * Initialize our stuff
	---------------------------------------------------------------------------*/
	function settings_init() {

		/*---------------------------------------------------------------------------
		 * Register the Setting
		---------------------------------------------------------------------------*/
		register_setting(
			'amuga_ajax_log',
			'amuga_al_settings',
			array( $this, 'sanitizer' )
		);

		/*---------------------------------------------------------------------------
		 * Create a section
		---------------------------------------------------------------------------*/
		add_settings_section(
			'amuga_ajax_log_general_section', // Section ID
			__( 'Plugin Settings', 'amuga-ajax-log' ), // Section Title
			array( $this, 'general_section_callback' ), // The second content
			'amuga_ajax_log' // Parent Page
		);

		$fields = $this->field_array();

    /*---------------------------------------------------------------------------
		 * Run the fields array through a loop to create the fields
		---------------------------------------------------------------------------*/
		foreach( $fields as $field => $field_settings ){

			add_settings_field(
				$field,
				$field_settings['title'],
				array( $this, $field_settings['callback'] ),
				$field_settings['page'],
				$field_settings['section'],
				$field_settings['args']
			);

		}

	}


	/*---------------------------------------------------------------------------
	 * Field Array
	 *
	 * This array has all of the settings for creating various form fields
	---------------------------------------------------------------------------*/
	function field_array(){
		$field = array(

      /*---------------------------------------------------------------------------
    	 * Stop Logging
    	 *
    	 * This option makes it possible to disable all logging activity.
    	---------------------------------------------------------------------------*/
			'stoplog' 			=> array(
				'title'				=> __( 'Stop Logging', 'amuga-ajax-log' ),
				'callback'		=> 'field_checkbox',
				'page'				=> 'amuga_ajax_log',
				'section'			=> 'amuga_ajax_log_general_section',
				'args'				=> array(
					'field_id'	=> 'stoplog',
					'default'		=> '0',
				),
			),

      /*---------------------------------------------------------------------------
    	 * Log Type
    	 *
    	 * How would you like to log your data? Database or file?
    	---------------------------------------------------------------------------*/
			'storage' => array(
				'title'			=> __( 'Log Storage', 'amuga-ajax-log' ),
				'callback'	=> 'field_select',
				'page'			=> 'amuga_ajax_log',
				'section'		=> 'amuga_ajax_log_general_section',
				'args'			=> array(
					'field_id'	=> 'storage',
					'options'	=> array(
							'db'			=> __( 'Database', 'amuga-ajax-log' ),
							'file'		=> __( 'File', 'amuga-ajax-log' ),
							)
				)
			),

      /*---------------------------------------------------------------------------
    	 * Hit Leaderboard
    	 *
    	 * These are the actions hitting the most
    	---------------------------------------------------------------------------*/
			'leaderboard_limit' => array(
				'title'			=> __( 'Admin-Ajax Leaderboards', 'amuga-ajax-log' ),
				'callback'	=> 'field_text',
				'page'			=> 'amuga_ajax_log',
				'section'		=> 'amuga_ajax_log_general_section',
				'args'			=> array(
					'field_id'	=> 'leaderboard_limit',
					'default'	=> '10'
				),
			),

      /*---------------------------------------------------------------------------
    	 * Leaderboard Threashold
    	 *
    	 * How many hits are too many?
    	---------------------------------------------------------------------------*/
			'leaderboard_threshold' => array(
				'title'			=> __( 'Leaderboard Threshold', 'amuga-ajax-log' ),
				'callback'	=> 'field_text',
				'page'			=> 'amuga_ajax_log',
				'section'		=> 'amuga_ajax_log_general_section',
				'args'			=> array(
					'field_id'	=> 'leaderboard_threshold',
					'default'	=> '500'
				),
			),

      /*---------------------------------------------------------------------------
    	 * Recent Hits Limit
    	 *
    	 * How many hits are too many?
    	---------------------------------------------------------------------------*/
			'list_display_limit' => array(
				'title'			=> __( 'Recent Hits', 'amuga-ajax-log' ),
				'callback'	=> 'field_text',
				'page'			=> 'amuga_ajax_log',
				'section'		=> 'amuga_ajax_log_general_section',
				'args'			=> array(
					'field_id'	=> 'list_display_limit',
					'default'	=> '50'
				),
			),

      /*---------------------------------------------------------------------------
    	 * Purge Database
    	 *
    	 * Enable this to purge the captured data
    	---------------------------------------------------------------------------*/
			'purge' => array(
				'title'			=> __( 'Purge Current Database Log', 'amuga-ajax-log' ),
				'callback'	=> 'field_checkbox',
				'page'			=> 'amuga_ajax_log',
				'section'		=> 'amuga_ajax_log_general_section',
				'args'			=> array(
					'field_id'	=> 'purge',
					'default'	=> '0'
				),
			),

      /*---------------------------------------------------------------------------
    	 * Remove Data on Deactivation
    	 *
    	 * Enable this option to erase all of our stuff when the plugin is deactivated
    	---------------------------------------------------------------------------*/
			'uninstall_data' => array(
				'title'			=> __( 'Remove All Data on Deactivation', 'amuga-ajax-log' ),
				'callback'	=> 'field_checkbox',
				'page'			=> 'amuga_ajax_log',
				'section'		=> 'amuga_ajax_log_general_section',
				'args'			=> array(
					'field_id'	=> 'uninstall_data',
					'default'	=> '0'
				),
			),
		);
		return $field;
	}

	/*---------------------------------------------------------------------------
	 * Field - Text
	 *
	 * Create a text field
	---------------------------------------------------------------------------*/
	function field_text( $args ) {
		$options = get_option( 'amuga_al_settings' );

		// Set a default
		if( empty( $options[ $args['field_id'] ] ) && !empty( $args['default'] ) )
			$options[ $args['field_id'] ] = $args['default'];

		?>

		<input type='text' name='<?php echo 'amuga_al_settings'; ?>[<?php echo esc_attr( $args['field_id'] );?>]' value='<?php echo esc_attr( $options[ $args['field_id'] ] ); ?>' />
		<?php
	}

	/*---------------------------------------------------------------------------
	 * Field - Select
	 *
	 * Create a select field
	---------------------------------------------------------------------------*/
	function field_select( $args ) {
		$options = get_option( 'amuga_al_settings' );

		// Set a default
		if( empty( $options[ $args['field_id'] ] ) && !empty( $args['default'] ) )
			$options[ $args['field_id'] ] = $args['default'];
		?>

		<select name='<?php echo 'amuga_al_settings'; ?>[<?php echo esc_attr( $args['field_id'] );?>]'>
			<?php foreach( $args['options'] as $option => $name ) : ?>
				<option value="<?php echo $option; ?>" <?php selected( $options[ $args['field_id'] ], $option, true ); ?>><?php echo $name; ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/*---------------------------------------------------------------------------
	 * Field - Checkbox
	 *
	 * Create a checkbox field
	---------------------------------------------------------------------------*/
	function field_checkbox( $args ) {
		$options = get_option( 'amuga_al_settings' );

		// If nothing is set, give it a 0 to appease PHP Error Gods
		if( empty( $options[ $args['field_id'] ] ) ){
			$options[ $args['field_id'] ] = 0;
		}
		?>

		<input type='checkbox' name='<?php echo 'amuga_al_settings'; ?>[<?php echo esc_attr( $args['field_id'] );?>]' <?php checked( $options[ $args['field_id'] ], 1 ); ?> value='1' />

		<?php
	}

	/*---------------------------------------------------------------------------
	 * Sanitizer
	 *
	 * Run each option from our array through sanitization
	---------------------------------------------------------------------------*/
	private function sanitizer( $input ){
		$output = array();
		if( is_array( $input ) ){
			// Loop through each of the incoming options
			foreach( $input as $key => $value ) {
				// Check to see if the current option has a value. If so, process it.
				if( isset( $input[$key] ) ) {
					// Strip all HTML and PHP tags and properly handle quoted strings
					$output[$key] = sanitize_text_field( $input[ $key ] );
				} // end if
			} // end foreach
		}

		return $output; // return sanitized input
	}


	/*---------------------------------------------------------------------------
	 * Purge Records
	 *
	 * If the purge option is set, we will kill the DB records and then disable
	 * the option.
	---------------------------------------------------------------------------*/
	function purge_db(){
		global $wpdb;
		$options = get_option( 'amuga_al_settings' );

		if( !empty( $options['purge'] ) ){
			$wpdb->query( "TRUNCATE TABLE " . AMUGA_AL_TABLE );
			$options['purge'] = '';
			update_option( 'amuga_al_settings', $options ); // Update the options
		}
	}

	/*---------------------------------------------------------------------------
	 * General Section Text
	---------------------------------------------------------------------------*/
	function general_section_callback() {
		echo __( 'If the Purge setting is enabled, once you hit Save, it will clear the database and unset the Purge option.', 'amuga-ajax-log' );
	}

	/*---------------------------------------------------------------------------
	 * Options Page - HTML
	 *
	 * Create the HTML for the options page
	---------------------------------------------------------------------------*/
	function settings_page() {

		?>
		<div class="amuga-al-wrap">
			<div id="amuga-al-settings-wrap" class="amuga-al-section">

				<?php amuga_al_logo( __( 'Amuga Ajax Log Settings', 'amuga-ajax-log' ) );?>

				<form action='options.php' method='post'>

					<p><?php echo __( 'Amuga Ajax Log is a tool to capture a list of which functions are hitting admin-ajax. The data the plugin logs can be useful when diagnosing performance issues. Use this options panel to modify the settings of the plugin.', 'amuga-ajax-log' );?></p>
					<p class="amuga-al-warning"><?php echo __( 'Keep in mind that this plugin can create a large log set, especially if you have a lot of admin-ajax activity on a high traffic site. We do not recommend leaving logging enabled for an extended period of time if you have a busy site or a site that gets a lot of admin-ajax hits.', 'amuga-ajax-log' );?></p>
					<?php
					settings_fields( 'amuga_ajax_log' );
					do_settings_sections( 'amuga_ajax_log' );
					submit_button();
					?>

				</form>
			</div>
		</div>
		<?php

	}

}
