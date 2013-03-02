<?php


// 0 - none
define ( 'WPDODEBUG_NONE', 0 );

// 1 - call logging only
define ( 'WPDODEBUG_CALL', 1 );

// 2 - calls, and responses
define ( 'WPDODEBUG_RESP', 2 );

// Selected debug level
define ( 'WPDO_API_LEVEL', WPDODEBUG_NONE );



/**
 * This class contains all the functions that actually retrieve information from the GitHub API
 */
class wpdotorg_api {



	/**
	 * Limit chance of timeouts
	 */
	function __construct() {

		add_filter ( 'http_request_timeout', array ( $this, 'http_request_timeout' ) );

	}



	/**
	 * Extend the timeout since API calls can exceed 5 seconds
	 * @param  int $seconds The current timeout setting
	 * @return int          The revised timeout setting
	 */
	function http_request_timeout ( $seconds ) {
		return $seconds < 10 ? 10 : $seconds;
	}



	/**
	 * Call the WP.org API for the request
	 * @param  string $api_url The API endpoint URL to call
	 * @param  string $action  The action we're performing at that URL
	 * @param  object $req     The request data
	 * @return mixed           The response from the API
	 */
	private function call_api ( $api_url, $action, $req ) {

		$args = array ( 'user-agent' => 'WordPress WPDotOrg oEmbed plugin - https://github.com/leewillis77/wp-wpdotorg-embed');

		$this->log ( __FUNCTION__." : $url\nACTION: ".print_r($action,1)."\nDATA: ".print_r(serialize($req),1), WPDODEBUG_CALL );

		$results = wp_remote_post ( $api_url, array ( 'body' => array ( 'action' => $action, 'request' => serialize ( $req ) ) ) );

		$this->log ( __FUNCTION__." : ".print_r($results,1), WPDODEBUG_RESP );

		if ( is_wp_error( $results ) ||
		    ! isset ( $results['response']['code'] ) ||
		    $results['response']['code'] != '200' ) {
			header ( 'HTTP/1.0 404 Not Found' );
			die ( 'Mike Little is lost, and afraid' );
		}

		return $results;

	}



	/**
	 * Get plugin information from the WP.org API
	 * @param  string $slug       The plugin slug
	 * @return object             The response from the WP.org API
	 */
	public function get_plugin ( $slug ) {

		$this->log ( "get_plugin ( $slug )", WPDODEBUG_CALL );

		$plugin = trim ( $plugin, '/' );

		$args = new stdClass();
		$args->slug = $slug;
		$args->fields = array (
		                       'version',
		                       'author',
		                       'requires',
		                       'tested',
		                       'downloaded',
		                       'rating',
		                       'num_ratings',
		                       'sections',
		                       'download_link',
		                       'description',
		                       'short_description',
		                       'name',
		                       'slug',
		                       'author_profile',
		                       'homepage',
		                       'contributors',
		                       'added',
		                       'last_updated'
		                       );

		$results = $this->call_api ( "http://api.wordpress.org/plugins/info/1.0/", 'plugin_information', $args );

		return maybe_unserialize ( $results['body'] );

	}



	/**
	 * Get plugin information from the WP.org API
	 * @param  string $slug       The plugin slug
	 * @return object             The response from the WP.org API
	 */
	public function get_plugin ( $slug ) {

		$this->log ( "get_plugin ( $slug )", WPDODEBUG_CALL );

		$plugin = trim ( $plugin, '/' );

		$args = new stdClass();
		$args->slug = $slug;
		$args->fields = array (
		                       'version',
		                       'author',
		                       'requires',
		                       'tested',
		                       'downloaded',
		                       'rating',
		                       'num_ratings',
		                       'sections',
		                       'download_link',
		                       'description',
		                       'short_description',
		                       'name',
		                       'slug',
		                       'author_profile',
		                       'homepage',
		                       'contributors',
		                       'added',
		                       'last_updated'
		                       );

		$results = $this->call_api ( "http://api.wordpress.org/plugins/info/1.0/", 'plugin_information', $args );

		return maybe_unserialize ( $results['body'] );

	}



	/**
	 * Internal logging function
	 * @param  string $msg   The message to log
	 * @param  int $level    The level of this message
	 */
	private function log ( $msg, $level ) {
		if ( WPDO_API_LEVEL >= $level ) {
			error_log ( "[WPDOE$level]: ".$msg );
		}
	}



}