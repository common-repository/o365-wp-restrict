<?php
/**
	* Plugin Name: Intranet and Extranet with O365 Login
	* Plugin URI: http://wpintegrate.com
	* Version: 1.6
	* Author: contact@wpintegrate.com 
	* Author URI: contact@wpintegrate.com 
	* Date: Feb 02, 2018
	* Description: Intranet and Extranet Portal gives you everything to add privacy to your website. Supports Office 365, Dynamics CRM and Other Third Party Identity Providers.<a href="https://www.wpintegrate.com">Click Here</a>
	* Text Domain: wpintegrate
	* Requires at least: 3.5.2
	* Tested up to: 6.3.2
*/
defined( 'ABSPATH') OR die('Access denied!' );
define( 'O365_WP_RESTRICT_PATH', plugin_dir_path(__FILE__) );
define( 'O365_WP_RESTRICT_URL', plugin_dir_url(__FILE__) );
define( 'O365_WP_RESTRICT_VERSION', '1.6' );
define( 'O365_WP_RESTRICT_BASENAME', plugin_basename(__FILE__));

/*Registered activation hook*/
require O365_WP_RESTRICT_PATH . 'inc/o365-wp-restrict-activation-hook.php';
register_activation_hook( __FILE__, 'o365WpRestrictRegisterActivationHook');

/*Registered deactivation hook*/
require O365_WP_RESTRICT_PATH . 'inc/o365-wp-restrict-deactivation_hook.php';
register_deactivation_hook( __FILE__, 'o365WpRestrictRegisterDeactivationHook');

/*added actions, filter*/
require O365_WP_RESTRICT_PATH . 'libraries/o365-wp-restrict-plugin-initializer.php';

/*Added WP core files for plugin functions compatibility*/
include_once ABSPATH . '/wp-admin/includes/plugin.php';
require_once( ABSPATH . "wp-includes/pluggable.php" );

/*Added css file for admin area*/
add_action( 'admin_head', 'o365RestrictEnqueueStyle' );
function o365RestrictEnqueueStyle()
{
	wp_enqueue_style( 'o365-wp-restrict-style-icon', O365_WP_RESTRICT_URL."css/o365_wp_restrict_menu_icon.css" );
	wp_enqueue_style( 'o365-wp-restrict-style', O365_WP_RESTRICT_URL."css/o365_wp_restrict.css" );
}

/*Added plugin link in admin menu*/
add_action('admin_menu', 'o365_wp_restrict_menu' );
function o365_wp_restrict_menu()
{
	add_menu_page( "Office 365 - WP Restriction Settings","Site Restrict",'administrator','o365-wp-restrict','o365_wp_restrict_menu_func','');
}
/*Added main class and called actions*/
require O365_WP_RESTRICT_PATH . 'inc/class-o365-wp-restrict.php';	
add_action("init",function(){
	$o365_restrict_run_autologout = new O365_WP_Restrict;
	$o365_restrict_run_autologout->o365_restrict_do_actions();
});