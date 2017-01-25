<?php
/*
Plugin Name: vtONE Sections
Plugin URI: http://vt-one.org/
Description: Provides a method to show subpages as sections of the current page
Author: Michael Snow
Author URI: http://vt-one.org/
Version: 0.1.0
*/

/*  Copyright 2015 Michael Snow (e-mail misnow1 at gmail dot com)

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

if ( ! defined( 'VTBSS_PLUGIN_BASENAME' ) )
	define( 'VTBSS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'VTBSS_PLUGIN_NAME' ) )
	define( 'VTBSS_PLUGIN_NAME', trim( dirname( VTBSS_PLUGIN_BASENAME ), '/' ) );

if ( ! defined( 'VTBSS_PLUGIN_DIR' ) )
	define( 'VTBSS_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . VTBSS_PLUGIN_NAME );

if ( ! defined( 'VTBSS_PLUGIN_URL' ) )
	define( 'VTBSS_PLUGIN_URL', WP_PLUGIN_URL . '/' . VTBSS_PLUGIN_NAME );

if ( ! defined( 'VTBSS_PLUGIN_MODULES_DIR' ) )
	define( 'VTBSS_PLUGIN_MODULES_DIR', VTBSS_PLUGIN_DIR . '/modules' );

if (is_admin()) {
	require_once VTBSS_PLUGIN_DIR . '/admin/admin.php';
}
else {
	require_once VTBSS_PLUGIN_DIR . '/includes/controller.php';
}

add_action( 'plugins_loaded', 'VTBSS_set_request_uri', 9 );

function VTBSS_set_request_uri() {
	global $VTBSS_request_uri;

	$VTBSS_request_uri = add_query_arg( array() );
}

function VTBSS_get_request_uri() {
	global $VTBSS_request_uri;

	return (string) $VTBSS_request_uri;
}

function VTBSS_plugin_path( $path = '' ) {
	return path_join( VTBSS_PLUGIN_DIR, trim( $path, '/' ) );
}

function VTBSS_plugin_url( $path = '' ) {
	return plugins_url( $path, VTBSS_PLUGIN_BASENAME );
}

function VTBSS_admin_url( $query = array() ) {
	global $plugin_page;

	if ( ! isset( $query['page'] ) )
		$query['page'] = $plugin_page;

	$path = 'admin.php';

	if ( $query = build_query( $query ) )
		$path .= '?' . $query;

	$url = admin_url( $path );

	return esc_url_raw( $url );
}

?>
