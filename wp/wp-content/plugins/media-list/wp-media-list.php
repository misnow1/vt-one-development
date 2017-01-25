<?php
/*
Plugin Name: Media List
Plugin URI: http://vt-one.org/
Description: Provides a shortcode to list media files
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

if ( ! defined( 'WPML_PLUGIN_BASENAME' ) )
	define( 'WPML_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'WPML_PLUGIN_NAME' ) )
	define( 'WPML_PLUGIN_NAME', trim( dirname( WPML_PLUGIN_BASENAME ), '/' ) );

if ( ! defined( 'WPML_PLUGIN_DIR' ) )
	define( 'WPML_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . WPML_PLUGIN_NAME );

if ( ! defined( 'WPML_PLUGIN_URL' ) )
	define( 'WPML_PLUGIN_URL', WP_PLUGIN_URL . '/' . WPML_PLUGIN_NAME );

if ( ! defined( 'WPML_PLUGIN_MODULES_DIR' ) )
	define( 'WPML_PLUGIN_MODULES_DIR', WPML_PLUGIN_DIR . '/modules' );

if (is_admin()) {
	require_once WPML_PLUGIN_DIR . '/admin/admin.php';
}
else {
	require_once WPML_PLUGIN_DIR . '/includes/controller.php';
}

add_action( 'plugins_loaded', 'wpml_set_request_uri', 9 );

function wpml_set_request_uri() {
	global $wpml_request_uri;

	$wpml_request_uri = add_query_arg( array() );
}

function wpml_get_request_uri() {
	global $wpml_request_uri;

	return (string) $wpml_request_uri;
}

function wpml_plugin_path( $path = '' ) {
	return path_join( WPML_PLUGIN_DIR, trim( $path, '/' ) );
}

function wpml_plugin_url( $path = '' ) {
	return plugins_url( $path, WPML_PLUGIN_BASENAME );
}

function wpml_admin_url( $query = array() ) {
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
