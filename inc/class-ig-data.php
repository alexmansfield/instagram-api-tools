<?php

class IG_API_Tools_Data {

	/**
	 * Get new access token from Instagram
	 */
	public function get_new_access_token($client_id, $redirect_uri, $client_secret, $code) {
		$url = 'https://api.instagram.com/oauth/access_token';

		$curlPost = 'client_id='. $client_id . '&redirect_uri=' . $redirect_uri . '&client_secret=' . $client_secret . '&code='. $code . '&grant_type=authorization_code';
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
			echo '<pre>';
			print_r($data['meta']);
			echo '</pre>';
			throw new Exception('Error : Failed to receieve access token');
		}
		update_option( 'ig_access_token', $data['access_token'] );

		$ig_access_token = $data['access_token'];

		return $ig_access_token;
	}

	public function get_data_with_token( $url ) {
		$ig_access_token = get_option( 'ig_access_token' );

		// check for access token existance

		$response_json = wp_remote_get( trailingslashit( $url ) . '?access_token=' . $ig_access_token );

		$ig_request_monitor = get_option( 'ig_request_monitor' );

		/*

		request monitor should keep track of two things:
		- when the current hour started
		- how many requests have been made since then

		if (more than one hour has elapsed ) {
			set new hour time
			clear request count
		} else {
			increment request count
		}

		if (return data is good) {
			return body of response
		} else {
			handle error
		}

		*/

		return $response_json;
	}

	public function get_data_without_token( $url ) {
		$data = wp_remote_get( $url );

		return $data;
	}

	public function get_useful_data_without_token( $url ) {
		$data = $this->get_data_without_token( $url );


		if ( $data['response']['code'] == 200 ) {
			$useful_data = $data['body'];
		} else {
			$useful_data = $data['response'];
		}

		return $useful_data;
	}
}
