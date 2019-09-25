<?php
/**
 * Plugin Name: Debug REST API Tools
 * Description: A plugin to assist with debugging REST API requests.
 * Author: Automattic
 * Author URI: https://automattic.com
 * Version: 1.0
 * Requires PHP: 5.6
 * License: GPLv3
 */

add_filter( 'rest_pre_dispatch', function( $ignore, $server, $request ) {
	if ( $request['WP_DEBUG'] || WP_DEBUG ) {
		if ( ! defined( 'SAVEQUERIES' ) ) {
			define( 'SAVEQUERIES', true );
		}
	}
	return $ignore;
}, 10, 3 );

add_filter( 'rest_request_after_callbacks', function ( $response, $handler, $request ) {
	if ( ( $request['WP_DEBUG'] || WP_DEBUG ) && $response instanceof WP_REST_Response ) {
		global $wpdb;
		$queries = array_filter(
			array_map( function ( $query_item ) {
				if ( strpos( $query_item[2], 'Controller' ) !== false ) {
					return $query_item[0];
				} else {
					return null;
				}
			}, $wpdb->queries ),
			function ( $item ) { return $item !== null; }
		);
		$response->header( 'X-A8C-all-queries', wp_json_encode( $queries ) );
		$response->header( 'X-A8C-last-query', $wpdb->last_query );
	}
	return $response;
}, 10, 3 );
