<?php
function o365_wp_restrict_recursive_sanitize_text_field($array)
{
	foreach ( $array as $key => &$value ) {
        if ( is_array( $value ) ) {
            $value = o365_wp_restrict_recursive_sanitize_text_field($value);
        }
        else {
            $value = sanitize_text_field( $value );
        }
    }

    return $array;
}
