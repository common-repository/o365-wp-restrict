<?php defined('ABSPATH') OR die('Direct Access Restricted!');

function o365WpRestrictRegisterDeactivationHook()

{

	/****************************save pre-stage bofore deactivarte plugin:end**********************/

	delete_option( 'o365_wp_restrict_lcode' );

	delete_option( 'o365_wp_restrict_verify_auth' );
	delete_option( 'o365_wp_restrict_settings');

}