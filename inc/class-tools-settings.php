<?php

class IG_API_Tools_Settings {
	public $page;

	function __construct() {
		add_action( 'admin_init',            array( $this, 'register_settings' ) );
		add_action( 'admin_menu',            array( $this, 'register_settings_page' ) );
		add_action( 'wp_ajax_igapi_ajax',    array( $this, 'settings_ajax' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue' ) );
		add_action( 'admin_init', array( $this, 'new_ig_connection' ) );
	}

	/**
	 * Enqueues scripts for settings page
	 */
	function enqueue( $hook ) {
		if ( $this->page == $hook ) {
			wp_enqueue_script( 'igapi-scripts', plugins_url( 'js/scripts.js', dirname( __FILE__ ) ), array( 'jquery' ) );
			wp_enqueue_style( 'igapi-styles', plugins_url( 'css/igapi.css', dirname( __FILE__ ) ) );
		}
	}

	/**
	 * Registers the plugin settings page
	 */
	public function register_settings_page() {
		$this->page = add_submenu_page(
			'options-general.php',
			__( 'Instagram API Tools', 'instagram_api_tools_textdomain' ),
			__( 'IG Tools', 'instagram_api_tools_textdomain' ),
			'manage_options',
			'igapi_tools',
			array( $this, 'settings_page_content' )
		);
	}

	/**
	 * Registers the individual plugin settings
	 */
	public function register_settings() {
		add_settings_section(
			'igapi_tools_settings_fields',     // ID used to identify this section and with which to register options
			'',                  // Title to be displayed on the administration page
			'__return_false',    // Callback used to render the description of the section
			'igapi_tools'             // Page on which to add this section of options
		);

		add_settings_field(
			'igapi_client_id',
			__( 'Client ID', 'instagram_api_tools_textdomain' ),
			array( $this, 'display_client_id_field' ),
			'igapi_tools',
			'igapi_tools_settings_fields',
			array( __( 'You can find this in your IG account.', 'instagram_api_tools_textdomain' ) )
		);

		register_setting(
			'igapi_tools_settings_fields',
			'igapi_client_id',
			array( $this, 'sanitize_settings' )
		);

		add_settings_field(
			'igapi_client_secret',
			__( 'Client secret', 'instagram_api_tools_textdomain' ),
			array( $this, 'display_client_secret_field' ),
			'igapi_tools',
			'igapi_tools_settings_fields',
			array( __( 'You can find this in your IG account.', 'instagram_api_tools_textdomain' ) )
		);

		register_setting(
			'igapi_tools_settings_fields',
			'igapi_client_secret',
			array( $this, 'sanitize_settings' )
		);

		add_settings_field(
			'igapi_return_url',
			__( 'Return URL', 'instagram_api_tools_textdomain' ),
			array( $this, 'display_return_url_field' ),
			'igapi_tools',
			'igapi_tools_settings_fields',
			array( __( 'Instagram will return data to this URL.', 'instagram_api_tools_textdomain' ) )
		);

		register_setting(
			'igapi_tools_settings_fields',
			'igapi_return_url',
			array( $this, 'sanitize_settings' )
		);

		add_settings_field(
			'igapi_connection',
			__( 'Instagram connection', 'instagram_api_tools_textdomain' ),
			array( $this, 'display_connection_field' ),
			'igapi_tools',
			'igapi_tools_settings_fields',
			array( __( 'Instagram will return data to this URL.', 'instagram_api_tools_textdomain' ) )
		);
	}

	/**
	 * Prepares the plugin settings to be saved to the database
	 */
	public function sanitize_settings( $input ) {
		// write_log('Input: ' . $input );
		// $output = array();
		// $languages = $this->language_list();

		// // Loops through each of the incoming settings
		// foreach( $input as $key => $value ) {
		// 	if( isset( $input[$key] ) ) {
		// 		if ( in_array( $input[$key], $languages ) ) {
		// 			$output[$key] = $input[$key];
		// 		}
		// 	}
		// }

		$output = $input;

		return $output;
	}

	/**
	 * Outputs the contents of the plugin settings page
	 */
	public function settings_page_content() {

		// Does the current user have permission to manage options?
		if ( !current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( !empty( $_GET['ig_code'] ) ) {

			$code = sanitize_text_field( $_GET['ig_code'] );

			add_filter( 'igapi_filter_redirect_url', function(){
				return menu_page_url( 'igapi_tools', false );
			} );

			$auth = new IG_API_Tools_Auth;
			$access_token = $auth->request_new_access_token( $code );

			echo $access_token;
			$auth->set_access_token( $access_token );

		}


		?>
			<div class="wrap">
				<h1><?= esc_html(get_admin_page_title()); ?></h1>
				<div id="connection-settings">
					IG API Connection Settings
					<form action="options.php" method="post">
						<?php
							settings_fields('igapi_tools_settings_fields');
							do_settings_sections('igapi_tools');
							submit_button( __( 'Save Settings', 'instagram_api_tools_textdomain' ) ) ;
						?>
					</form>
				</div>
				<div id="api-testing">
					IG API Testing<br><br>

					<h4>Test URL</h4>
					<p>This doesn't (yet) work with URLs that require a token)</p>
					<p>Add "?__a=1" to the end of a single image URL to get a proper response</p>
					<p><input type="text" id="igapi-test-url" style="width: 90%"></p>
					<p><a id="igapi-get-data" class="button" href="#">Get Data</a></p>

					<pre id="igapi-response"></pre>
				</div>
			</div>
		<?php
	}

	/**
	 * Displays input field for client ID
	 */
	public function display_client_id_field() {
		$client_id = get_option( 'igapi_client_id' );

		echo '<input type="text" value="' . $client_id . '" name="igapi_client_id" class="regular-text">';
		echo '<p class="description">Client ID can be found in the <a href="https://www.instagram.com/developer/clients/manage/">Instagram client manager</a>.</p>';
	}

	/**
	 * Displays input field for client ID
	 */
	public function display_client_secret_field() {
		$client_secret = get_option( 'igapi_client_secret' );

		echo '<input type="text" value="' . $client_secret . '" name="igapi_client_secret" class="regular-text">';
		echo '<p class="description">Client secret can be found in the <a href="https://www.instagram.com/developer/clients/manage/">Instagram client manager</a>.</p>';
	}

	/**
	 * Displays input field for return URL
	 */
	public function display_return_url_field() {
		$return_url = get_option( 'igapi_return_url' );

		echo '<input type="text" value="' . $return_url . '" name="igapi_return_url" class="regular-text">';
		echo '<p class="description">Instagram will return data to this URL. Leave blank to use testing tools below.</p>';
	}

	/**
	 * Displays Instagram connection button/status
	 */
	public function display_connection_field() {
		add_filter( 'igapi_filter_redirect_url', function(){
			return menu_page_url( 'igapi_tools', false );
		} );

		$auth = new IG_API_Tools_Auth;
		$auth_url = $auth->get_ig_auth_url();
		// echo $auth_url;

		$data = new IG_API_Tools_Data;

		if ( $auth->is_access_token_saved() ) {
			echo '<p>Current access token:' . $auth->get_access_token() . '</p>';
			echo '<p>When request limit resets: ' . $data->get_request_count_time_reset() . '</p>';
			echo '<p>Current server time: ' . date( 'g:i:sa' ) . '</p>';
			echo '<p>Time till reset: ' . $data->get_time_till_reset() . '</p>';
			echo '<p><a class="button" href="' . $auth_url . '">Reconnect to Instagram</a></p>';

		} else {

			echo '<p><a class="button" href="' . $auth_url . '">Connect to Instagram</a></p>';
			echo '<p class="description">If the connection fails with an Oath error, you might need to add ' . menu_page_url( 'igapi_tools', false ) . ' to the "Valid redirect URIs" associated with the client ID above.</p>';
		}
	}

	/**
	 * Handles AJAX requests from the settings page
	 */
	public function settings_ajax() {
		$ig_url = esc_url( $_POST['url'] );

		$data_tools = new IG_API_Tools_Data;
		$data = $data_tools->get_data( $ig_url );

		$response = array(
			'request_count' => $data_tools->get_request_count(),
			'request_count_time' => $data_tools->get_request_count_time(),
			'request_response' => $data
		);

		print_r( $response );

		wp_die(); // this is required to terminate immediately and return a proper response
	}

	/**
	 * Handles new Instagram connection
	 *
	 * Instagram doesn't accept URL parameters in it's return URL, so
	 * this plugin uses options-general.php (instead of the plugin
	 * settings page) as the return URL. This function listens for the
	 * "code" parameter that Instagram sends to the options-general.php
	 *  when generating an access token.
	 */
	function new_ig_connection() {
		global $pagenow;

		# Check current admin page.
		if( $pagenow == 'options-general.php' && isset( $_GET['code'] ) ){

			$code = sanitize_text_field( $_GET['code'] );

			wp_redirect( admin_url( '/options-general.php?page=igapi_tools&ig_code=' . $code ), 302 );
			exit;
		}
	}
}