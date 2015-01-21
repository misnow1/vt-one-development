<?php

function wpps_plugin_path( $path = '' ) {
	return path_join( WPPS_PLUGIN_DIR, trim( $path, '/' ) );
}

function wpps_plugin_url( $path = '' ) {
	return plugins_url( $path, WPPS_PLUGIN_BASENAME );
}

function wpps_admin_url( $query = array() ) {
	global $plugin_page;

	if ( ! isset( $query['page'] ) )
		$query['page'] = $plugin_page;

	$path = 'admin.php';

	if ( $query = build_query( $query ) )
		$path .= '?' . $query;

	$url = admin_url( $path );

	return esc_url_raw( $url );
}

function wpps_table_exists( $table = 'prayerschedules' ) {
	global $wpdb, $wpps;

	if ( 'prayerschedules' != $table )
		return false;

	if ( ! $table = $wpps->{$table} )
		return false;

	return strtolower( $wpdb->get_var( "SHOW TABLES LIKE '$table'" ) ) == strtolower( $table );
}

function wpps() {
	global $wpdb, $wpps;

	if ( is_object( $wpps ) )
		return;

	$wpps = (object) array(
		'prayerschedules' => $wpdb->prefix . "prayer_schedule",
		'prayerscheduleentries' => $wpdb->prefix . "prayer_schedule_entry",
		'processing_within' => '',
		'widget_count' => 0,
		'unit_count' => 0,
		'global_unit_count' => 0 );
}

wpps();

require_once WPPS_PLUGIN_DIR . '/includes/functions.php';
require_once WPPS_PLUGIN_DIR . '/includes/formatting.php';
require_once WPPS_PLUGIN_DIR . '/includes/pipe.php';
require_once WPPS_PLUGIN_DIR . '/includes/shortcodes.php';
require_once WPPS_PLUGIN_DIR . '/includes/classes.php';
require_once WPPS_PLUGIN_DIR . '/includes/taggenerator.php';

if ( is_admin() )
	require_once WPPS_PLUGIN_DIR . '/admin/admin.php';
else
	require_once WPPS_PLUGIN_DIR . '/includes/controller.php';

function wpps_prayer_schedules() {
	global $wpdb, $wpps;

	return $wpdb->get_results( "SELECT id, title FROM $wpps->prayerschedules" );
}

add_action( 'plugins_loaded', 'wpps_set_request_uri', 9 );

function wpps_set_request_uri() {
	global $wpps_request_uri;

	$wpps_request_uri = add_query_arg( array() );
}

function wpps_get_request_uri() {
	global $wpps_request_uri;

	return (string) $wpps_request_uri;
}

?>