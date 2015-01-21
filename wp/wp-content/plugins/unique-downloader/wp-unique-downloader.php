<?php
/*
Plugin Name: Unique Downloader
Plugin URI: http://vt-one.org/
Description: Provides a system for issuing and redeeming download cards for various things.
Author: Michael Snow
Author URI: http://vt-one.org/
Version: 0.1.0
*/

/*  Copyright 2011 Michael Snow (e-mail misnow1 at gmail dot com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

if ( ! defined( 'WPUS_PLUGIN_BASENAME' ) )
	define( 'WPUS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'WPUS_PLUGIN_NAME' ) )
	define( 'WPUS_PLUGIN_NAME', trim( dirname( WPUS_PLUGIN_BASENAME ), '/' ) );

if ( ! defined( 'WPUS_PLUGIN_DIR' ) )
	define( 'WPUS_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . WPUS_PLUGIN_NAME );

if ( ! defined( 'WPUS_PLUGIN_URL' ) )
	define( 'WPUS_PLUGIN_URL', WP_PLUGIN_URL . '/' . WPUS_PLUGIN_NAME );

if ( ! defined( 'WPUS_PLUGIN_MODULES_DIR' ) )
	define( 'WPUS_PLUGIN_MODULES_DIR', WPUS_PLUGIN_DIR . '/modules' );
	
/*
 * Some required variables and what-not
 */
require_once WPUS_PLUGIN_DIR . '/aws/sdk.class.php';

require_once WPUS_PLUGIN_DIR . '/includes/defines.php';

require_once WPUS_PLUGIN_DIR . '/includes/functions.php';
require_once WPUS_PLUGIN_DIR . '/includes/classes.php';
require_once WPUS_PLUGIN_DIR . '/includes/user.php';
	
/*
 * Create the manager class if we don't have it already
 */
if (!is_object($wpus)) {
	$wpus = new WPUS_Manager();
}


if ( is_admin() ) {
	require_once WPUS_PLUGIN_DIR . '/admin/admin.php';
}
else {
	require_once WPUS_PLUGIN_DIR . '/includes/controller.php';
	require_once WPUS_PLUGIN_DIR . '/includes/forms.php';
	
	add_action('plugins_loaded', 'wpus_check_session');
}

add_action( 'plugins_loaded', 'wpus_set_request_uri', 9 );

function wpus_set_request_uri() {
	global $wpus_request_uri;

	$wpus_request_uri = add_query_arg( array() );
}

function wpus_get_request_uri() {
	global $wpus_request_uri;

	return (string) $wpus_request_uri;
}

function wpus_plugin_path( $path = '' ) {
	return path_join( WPUS_PLUGIN_DIR, trim( $path, '/' ) );
}

function wpus_plugin_url( $path = '' ) {
	return plugins_url( $path, WPUS_PLUGIN_BASENAME );
}

function wpus_admin_url( $query = array() ) {
	if ( ! isset( $query['page'] ) )
		$query['page'] = $_REQUEST['page'];

	$path = 'admin.php';

	if ( $query = build_query( $query ) )
		$path .= '?' . $query;

	$url = admin_url( $path );

	return esc_url_raw( $url );
}

function wpus_table_exists( $table ) {
	global $wpdb, $wpus;

	if ( ! $table = $wpus->{$table} )
		return false;

	return strtolower( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) ) == strtolower( $table );
}

function wpus_projects () {
	global $wpdb, $wpus;

	return $wpdb->get_results( "SELECT id, name FROM $wpus->projects" );
}
?>
