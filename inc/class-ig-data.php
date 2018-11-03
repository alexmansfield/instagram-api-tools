<?php

class IG_API_Tools_Data {

	public function get_data_with_token( $url ) {
		$access_token = get_option( 'igapi_access_token' );

		// check for access token existance

		$response_json = wp_remote_get( trailingslashit( $url ) . '?access_token=' . $access_token );

		$ig_request_monitor = get_option( 'igapi_request_monitor' );

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

		// Did our request succeed?
		if ( $data['response']['code'] == 200 ) {
			$useful_data = $data['body'];
		} else {
			$useful_data = $data;
		}

		return $useful_data;
	}
}
