<?php

class IG_API_Tools_Data {

	/**
	 * Gets data from the API
	 */
	public function get_data( $url, $token = '' ) {
		$this->update_request_count();

		// Is there an access token?
		if ( !empty( $token ) ) {

			// Does the URL already have parameters?
			if ( strpos( $url, '?' ) !== false ) {
				$response_json = wp_remote_get( $url . '&access_token=' . $token );
			} else {
				$response_json = wp_remote_get( trailingslashit( $url ) . '?access_token=' . $token );
			}
		} else {
			$response_json = wp_remote_get( $url );
		}

		return $response_json;
	}

	/**
	 * Updates the API request counter
	 *
	 * APIs usually have a limit on reqests per hour. This function
	 * tracks how many requests have been made in the past hour.
	 */
	public function update_request_count() {
		$request_count_time = intval( $this->get_request_count_time() );

		// Is the request count time over an hour old?
		if ( $request_count_time <= strtotime( '-1 hours' ) ) {
			$this->reset_request_count_time();
			$request_count = 1;
		} else {
			$request_count = intval( $this->get_request_count() );
			$request_count++;
		}

		$this->set_request_count( $request_count );
	}

	/**
	 * Gets the saved request count
	 */
	public function get_request_count() {
		return get_option( 'igapi_request_count' );
	}

	/**
	 * Saves a new request count
	 */
	public function set_request_count( $request_count ) {
		update_option( 'igapi_request_count', $request_count );
	}

	/**
	 * Gets the saved request count timer
	 */
	public function get_request_count_time() {
		return get_option( 'igapi_request_count_time' );
	}

	/**
	 * Resets the request count timer
	 */
	public function reset_request_count_time( $request_count_time ) {
		update_option( 'igapi_request_count_time', time() );
	}

	/**
	 * Gets the request count timer reset time
	 */
	public function get_request_count_time_reset() {
		return date( 'g:i:sa', intval( get_option( 'igapi_request_count_time' ) ) + 3600 );
	}

	/**
	 * Gets the number of minutes until the request count timer will reset
	 */
	public function get_time_till_reset() {
		$reset_time = $this->get_request_count_time_reset();


		return intval( ( strtotime( $reset_time ) - time() ) / 60 ) . 'min';
	}







	public function get_data_with_token( $url, $token = '' ) {
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
