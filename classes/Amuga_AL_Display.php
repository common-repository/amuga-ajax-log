<?php
/*---------------------------------------------------------------------------
 * Amuga Ajax Log - Display Data
 *
 * Display the data we capture and store in the database with Amuga_AL_Logger.php
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
 * Display Class
 *
 * This class handles the display of our data.
---------------------------------------------------------------------------*/
class Amuga_AL_Display{

	/*---------------------------------------------------------------------------
	 * Construct
	 * Run our actions
	---------------------------------------------------------------------------*/
	function __construct() {
		add_action( 'admin_menu', array( $this, 'create_add_admin_menu' ) );
	}

	/*---------------------------------------------------------------------------
	 * Create the Admin Page Link
	---------------------------------------------------------------------------*/
	public function create_add_admin_menu() {
		add_menu_page(
			'Amuga Ajax Log',
			'Amuga Ajax Log',
			'manage_options',
			'amuga_ajax_log',
			array( $this, 'log_viewer' ),
			'dashicons-list-view',
			3
		);
	}

	/*---------------------------------------------------------------------------
	 * Leaderboard
	 *
	 * Create a table that aggregates the hits, displayed by who hit most
	---------------------------------------------------------------------------*/
	function amuga_al_leaderboard(){
		global $wpdb;

		$options = get_option( 'amuga_al_settings' );
		$options[ 'highlight_limit' ] = ( empty( $options[ 'highlight_limit' ] ) ) ? 10 : absint( $options[ 'highlight_limit' ] );
		$options[ 'highlight_threshold' ] = ( empty( $options[ 'highlight_threshold' ] ) ) ? 500 : absint( $options[ 'highlight_threshold' ] );

		//Run the Query
		$result = $wpdb->get_results( "SELECT action, COUNT(*) as totalcount FROM " . AMUGA_AL_TABLE . " GROUP BY action ORDER BY totalcount DESC LIMIT " . $options[ 'highlight_limit' ] );

		if ( $result ) { ?>

			<table id="amuga-al-leaderboard-table" class="amuga-al-table">
				<tr class="amuga-al-heading">
					<th><?php _e( 'Action', 'amuga-ajax-log' ); ?></th>
					<th><?php _e( 'Total', 'amuga-ajax-log' ); ?></th>
				</tr>

				<?php foreach ( $result as $data ){ ?>
					<tr class="<?php if( $options[ 'highlight_threshold' ] < $data->totalcount ) echo 'amuga-high-count'; ?>">
						<td><?php echo esc_attr( $data->action ); ?></td>
						<td><?php echo absint( $data->totalcount ); ?></td>
					</tr>
				<?php } ?>

			</table>

			<?php

		}

	}

	/*---------------------------------------------------------------------------
	 * Log List
	 *
	 * Get the list of hits
	---------------------------------------------------------------------------*/
	function amuga_al_get_list(){
		global $wpdb;

		/*---------------------------------------------------------------------------
		 * Get our settings
		---------------------------------------------------------------------------*/
		$options = get_option( 'amuga_al_settings' );

		/*---------------------------------------------------------------------------
		 * List limit
		 *
		 * The list limit can be modified from the options page, otherwise we will
		 * pull 50 records by default.
		---------------------------------------------------------------------------*/
		$options[ 'list_display_limit' ] = ( empty( $options[ 'list_display_limit' ] ) ) ? 50 : absint( $options[ 'list_display_limit' ] );

		/*---------------------------------------------------------------------------
		 * Run the query to pull records
		---------------------------------------------------------------------------*/
		$result = $wpdb->get_results( "SELECT * FROM " . AMUGA_AL_TABLE . " ORDER BY id DESC LIMIT " . $options[ 'list_display_limit' ] );

		$i = 0; //For use with even/odd

		/*---------------------------------------------------------------------------
		 * If we have a result, run it through some html and give us a table
		---------------------------------------------------------------------------*/
		if ( $result ) {
			foreach ( $result as $data ){

				$ts_data_array = unserialize( $data->ts_data );

				if( empty( $data->file_location ) ){
					$data->file_location = amuga_aal_msg( 'failed_location' );
				} elseif( is_serialized( $data->file_location ) ) {
					$data->file_location = unserialize( $data->file_location);
					$data->file_location = array_map( 'esc_attr', $data->file_location);
					$data->file_location = implode( '<br/>', $data->file_location);
				} else {
					$data->file_location = esc_attr( $data->file_location);
				}

				?>
				<table class="amuga-al-table">
					<tr class="amuga-al-heading">
						<th colspan="3">
							<?php echo esc_attr( $data->time ); ?>
						</th>
					</tr>

					<tr>
						<td class="amuga-al-table-action">
							<strong><?php _e( 'Action', 'amuga-ajax-log' ); ?></strong><br/>
							<?php echo ( !empty( $data->action ) ) ? esc_attr( $data->action ) : ''; ?>
						</td>
						<td class="amuga-al-table-request-type">
							<strong><?php _e( 'Request Type', 'amuga-ajax-log' ); ?></strong><br/>
							<?php echo ( !empty( $data->request_type ) ) ? esc_attr( $data->request_type ) : ''; ?>
						</td>
						<td class="amuga-al-table-location">
							<strong><?php _e( 'Function Name', 'amuga-ajax-log' ); ?></strong><br/>
							<?php echo ( !empty( $ts_data_array[ 'fname' ] ) ) ? esc_attr( $ts_data_array[ 'fname' ] ) : ''; ?>
						</td>
					</tr>

					<tr>
						<td class="amuga-al-table-location" colspan="3">
							<strong><?php _e( 'File Location', 'amuga-ajax-log' ); ?></strong><br/>
							<?php echo ( !empty( $data->file_location ) ) ? $data->file_location : amuga_aal_msg( 'failed_location' ); ?>
						</td>
					</tr>
					<tr>
						<td class="amuga-al-table-location" colspan="3">
							<strong><?php _e( 'HTTP Referer', 'amuga-ajax-log' ); ?></strong><br/>
							<?php echo ( !empty( $ts_data_array[ 'request_url' ] ) ) ? esc_attr( $ts_data_array[ 'request_url' ] ) : ''; ?>
						</td>
					</tr>
				</table>
				<?php
			}
		}

	}

	/*---------------------------------------------------------------------------
	 * Active Warning
	 *
	 * Display a warning if logging is disabled.
	---------------------------------------------------------------------------*/
	function active_warning(){
		if( !amuga_al_logging() ) {
			?>
			<div class="amuga-al-error"><?php echo sprintf( __( 'Logging is currently disabled. Use the <a href="%s">Settings Panel</a> to enable logging.', 'amuga-ajax-log' ), '?page=amuga_ajax_log_settings' );?></div>
			<?php
		}

	}


	/*---------------------------------------------------------------------------
	 * Show the content
	 *
	 * Send the content to the screen.
	---------------------------------------------------------------------------*/
	function log_viewer() {
		$options = get_option( 'amuga_al_settings' );
		$options['list_display_limit'] = ( empty( $options['list_display_limit'] ) ) ? 50 : absint( $options['list_display_limit'] );
		?>

		<div class="amuga-al-wrap">
			<div class="amuga-al-section">

				<?php $this->active_warning(); ?>
					<?php amuga_al_logo( __( 'Amuga Ajax Log', 'amuga-ajax-log' ) );?>

				<p><?php echo __( 'Hi! If you\'re using this plugin, you\'ve likely noticed a lot of admin-ajax hits in your logs. This plugin can help you troubleshoot heavy admin-ajax activity. It does this by capturing every action that tries to hit admin-ajax.php and logs it for further review.', 'amuga-ajax-log' );?></p>

				<p><?php echo __( 'Below are two tools: Leaderboard and Recent Hits list. The Leaderboard looks at all of the logged action hits, then displays them in ranked order based on which one has been hitting the most. The Recent Hits list shows the most recent action hits, with additional data to help you determine what is calling the hits.', 'amuga-ajax-log' );?></p>

				<p><?php echo sprintf( __( 'Use the <a href="%s">Settings Panel</a> to modify the number of entries in the lists and how many hits constitute Heavy Usage.', 'amuga-ajax-log' ), '?page=amuga_ajax_log_settings' );?></p>

			</div>

			<div class="amuga-al-section">
				<p><h2><?php _e( 'Admin-Ajax Leaderboard', 'amuga-ajax-log' ); ?></h2></p>
				<p><?php echo __( 'Which action is hammering admin-ajax the most? Take a look at the leaderboard below.', 'amuga-ajax-log' );?></p>
				<?php $this->amuga_al_leaderboard();?>
			</div>

			<div class="amuga-al-section">
				<p>
					<h2><?php printf( esc_html__( '%d Recent Admin Ajax Hits', 'amuga-ajax-log' ), $options['list_display_limit'] );?></h2>
				</p>
				<p>
					<?php echo esc_html( 'View collected data that can be used for troubleshooting purposes. If we can not determine where the admin-ajax hit is coming from, try searching for the function name by doing a grep search with SSH.', 'amuga-ajax-log' );?>
				</p>
				<?php $this->amuga_al_get_list();?>
			</div>
		</div>
		<?php

	}

}
