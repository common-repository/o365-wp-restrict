<?php

defined( 'ABSPATH') OR die('Access denied!' );
function o365_wp_restrict_auto_update_check()
{
	require_once O365_WP_RESTRICT_PATH . '/libraries/o365-wp-restrict-auto-update.php';
	$plugin_detail = get_plugin_data( O365_WP_RESTRICT_PATH.'/o365-wp-restrict.php' );
    $wptuts_plugin_current_version = $plugin_detail["Version"];
    $wptuts_plugin_remote_path = 'https://www.wpintegrate.com/update-contacts.php';
    $wptuts_plugin_slug = O365_WP_RESTRICT_BASENAME;
	$o365_wp_restrict = get_option( 'o365_wp_restrict_lcode' , '');
	if( (isset($_REQUEST["plugin"]) && $_REQUEST["plugin"]=="o365-wp-restrict") || !isset($_REQUEST["plugin"]) )
	{
    	new o365_wp_restrict_wp_aut_upd($wptuts_plugin_current_version, $wptuts_plugin_remote_path, $wptuts_plugin_slug,$o365_wp_restrict);
	}
}