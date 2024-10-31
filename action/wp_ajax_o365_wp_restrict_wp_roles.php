<?php defined('ABSPATH') OR die('Access denied!');
/**
 * Called by ajax.
 * @return string SP users group lists where data will be synchronized
 */
function o365_wp_restrict_action_wp_ajax_o365_wp_restrict_wp_roles(){
    $selected_role = sanitize_text_field($_POST["wp_selected_role"]);
	$echo_data = wp_dropdown_roles( $selected_role );
	echo $echo_data;
	die();
}