<?php
/**
* Plugin Name: Total Posts Shortcode
* Plugin URI: https://github.com/TaylorJadin/tiny-wordpress-plugins
* Description: This plugin adds the [total_posts] shortcode. It does what you think it does.
* Version: 1.0
* Author: Taylor Jadin
* Author URI: https://jadin.me/
* Code from: https://www.wpbeginner.com/plugins/how-to-show-total-number-of-posts-in-wordpress/
**/
	
function wpb_total_posts() { 
$total = wp_count_posts()->publish;
return $total; 
} 
add_shortcode('total_posts','wpb_total_posts');
?>
