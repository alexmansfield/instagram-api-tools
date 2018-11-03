<?php

class IG_API_Tools_Auth {

	/**
	 * Get the Instagram client ID
	 */
	public function get_client_id() {

		// Allow plugins to set their own client ID.
		$client_id = apply_filters( 'igapi_filter_client_id', '' );

		// Get the client ID saved in the database.
		if ( empty( $client_id ) ) {
			$client_id = get_option( 'igapi_client_id' );
		}

		return $client_id;
	}

	/**
	 * Get the redirect URL
	 */
	public function get_redirect_url() {

		// Allow plugins to set their own redirect URL.
		$redirect_url = apply_filters( 'igapi_filter_redirect_url', '' );

		// Get the redirect URL saved in the database.
		if ( empty( $redirect_url ) ) {
			$redirect_url = get_option( 'igapi_redirect_url' );
		}

		return $redirect_url;
	}

	/**
	 * Get Instagram authentication URL
	 */
	public function get_ig_auth_url() {
		return 'https://api.instagram.com/oauth/authorize/?client_id=' . $this->get_client_id() . '&redirect_uri=' . $this->get_redirect_url() . '&response_type=code';
	}

	/**
	 * Get Instagram access token
	 *
	 * Looks for a saved token in the database. If no saved token is
	 * available, calls the request_new_access_token() function.
	 */
	public function get_access_token() {
		$access_token = get_option( 'igapi_access_token' );

		if ( empty ( $access_token ) ) {
			$access_token = $this->request_new_access_token();
			update_option ( 'igapi_access_token', $access_token );
		}

		return $access_token;
	}

	/**
	 * Save Instagram access token
	 */
	public function set_access_token( $access_token ) {
		update_option ( 'igapi_access_token', sanitize_text_field( $access_token ) );
	}

	/**
	 * Is an access token saved in the database
	 *
	 * Looks for a saved token in the database. Returns true if
	 * a token is found, false if no token is found.
	 */
	public function is_access_token_saved() {
		$access_token = get_option( 'igapi_access_token' );

		if ( empty ( $access_token ) ) {
			$token_exists = false;
		} else {
			$token_exists = true;
		}

		return $token_exists;
	}

	/**
	 * Requests new Instagram access token
	 */
	public function request_new_access_token() {
		// Instagram passes a parameter 'code' in the Redirect Url
		if(isset($_GET['code'])) {
			// echo 'getting access token...<br>';
			$url = 'https://api.instagram.com/oauth/access_token';

			$curlPost = 'client_id='. $this->ig_client_id . '&client_secret=' . $this->ig_client_secret . '&redirect_uri=' . $this->ig_redirect_url . '&code='. $_GET['code'] . '&grant_type=authorization_code';
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
			$data = json_decode(curl_exec($ch), true);
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			if($http_code != '200') {
				// throw new Exception('Error : Failed to receieve access token');
			} else {
				return $data['access_token'];
			}
		}
	}
}
