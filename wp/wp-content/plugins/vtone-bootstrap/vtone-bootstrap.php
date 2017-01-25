<?php
/*
Plugin Name: vtONE Bootstrap
Plugin URI: http://vt-one.org/
Description: Provides Bootstrap and some handy shortcodes to do useful things with it
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

if ( ! defined( 'VTOBS_PLUGIN_BASENAME' ) )
	define( 'VTOBS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'VTOBS_PLUGIN_NAME' ) )
	define( 'VTOBS_PLUGIN_NAME', trim( dirname( VTOBS_PLUGIN_BASENAME ), '/' ) );

if ( ! defined( 'VTOBS_PLUGIN_DIR' ) )
	define( 'VTOBS_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . VTOBS_PLUGIN_NAME );

if ( ! defined( 'VTOBS_PLUGIN_URL' ) )
	define( 'VTOBS_PLUGIN_URL', WP_PLUGIN_URL . '/' . VTOBS_PLUGIN_NAME );

if ( ! defined( 'VTOBS_PLUGIN_MODULES_DIR' ) )
	define( 'VTOBS_PLUGIN_MODULES_DIR', VTOBS_PLUGIN_DIR . '/modules' );

if (is_admin()) {
	require_once VTOBS_PLUGIN_DIR . '/admin/admin.php';
}
else {
	require_once VTOBS_PLUGIN_DIR . '/includes/controller.php';
	require_once VTOBS_PLUGIN_DIR . '/includes/classes.php';

	/* Load all shortcode files */
	$shortcodes_dir = dirname(__FILE__) . '/shortcodes';
	if ($handle = opendir($shortcodes_dir)) {
	    while (false !== ($entry = readdir($handle))) {
	        if (preg_match('/.*\.php$/', $entry)) {
				require_once("$shortcodes_dir/$entry");
			}
	    }

    	closedir($handle);
	}

}

add_action( 'plugins_loaded', 'VTOBS_set_request_uri', 9 );

function VTOBS_set_request_uri() {
	global $VTOBS_request_uri;

	$VTOBS_request_uri = add_query_arg( array() );
}

function VTOBS_get_request_uri() {
	global $VTOBS_request_uri;

	return (string) $VTOBS_request_uri;
}

function VTOBS_plugin_path( $path = '' ) {
	return path_join( VTOBS_PLUGIN_DIR, trim( $path, '/' ) );
}

function VTOBS_plugin_url( $path = '' ) {
	return plugins_url( $path, VTOBS_PLUGIN_BASENAME );
}

function VTOBS_admin_url( $query = array() ) {
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
