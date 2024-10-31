<?php
function o365_wp_restrict_script()
{
	wp_enqueue_style( 'o365-wp-restrict-style', O365_WP_RESTRICT_URL."css/o365_wp_restrict.css" );
}