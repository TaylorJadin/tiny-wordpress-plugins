<?php
/**
 * Plugin Name: Really cool neato plugin
 * Description: I am gud programmer.
 * Version: 1.0
 * Author: Taylor Jadin
 */

add_action('wp', 'error_demo');
add_action('admin_init', 'error_demo');

function error_demo() {
    $result = 10 / 0;
}
