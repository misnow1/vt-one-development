<?php
/*
Plugin Name: Prayer Scheduler
Plugin URI: http://contactform7.com/
Description: Hey look tat that, it's for prayer.
Author: Michael Snow
Author URI: http://www.vt-one.org/
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

define( 'WPPS_VERSION', '0.1.0' );

if ( ! defined( 'WPPS_PLUGIN_BASENAME' ) )
	define( 'WPPS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

if ( ! defined( 'WPPS_PLUGIN_NAME' ) )
	define( 'WPPS_PLUGIN_NAME', trim( dirname( WPPS_PLUGIN_BASENAME ), '/' ) );

if ( ! defined( 'WPPS_PLUGIN_DIR' ) )
	define( 'WPPS_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . WPPS_PLUGIN_NAME );

if ( ! defined( 'WPPS_PLUGIN_URL' ) )
	define( 'WPPS_PLUGIN_URL', WP_PLUGIN_URL . '/' . WPPS_PLUGIN_NAME );

if ( ! defined( 'WPPS_PLUGIN_MODULES_DIR' ) )
	define( 'WPPS_PLUGIN_MODULES_DIR', WPPS_PLUGIN_DIR . '/modules' );

if ( ! defined( 'WPPS_LOAD_JS' ) )
	define( 'WPPS_LOAD_JS', true );

if ( ! defined( 'WPPS_LOAD_CSS' ) )
	define( 'WPPS_LOAD_CSS', true );

if ( ! defined( 'WPPS_AUTOP' ) )
	define( 'WPPS_AUTOP', true );

if ( ! defined( 'WPPS_USE_PIPE' ) )
	define( 'WPPS_USE_PIPE', true );

if ( ! defined( 'WPPS_ADMIN_READ_CAPABILITY' ) )
	define( 'WPPS_ADMIN_READ_CAPABILITY', 'edit_posts' );

if ( ! defined( 'WPPS_ADMIN_READ_WRITE_CAPABILITY' ) )
	define( 'WPPS_ADMIN_READ_WRITE_CAPABILITY', 'publish_pages' );

require_once WPPS_PLUGIN_DIR . '/settings.php';

?>