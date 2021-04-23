<?php
/**
* Plugin Name: Order posts by titel
* Plugin URI: https://github.com/TaylorJadin/tiny-wordpress-plugins
* Description: This plugin makes posts display in alphabetical order by title. Based on: https://themeisle.com/blog/re-order-wordpress-blog-posts/
* Version: 1.0
* Author: Taylor Jadin
* Author URI: https://jadin.me/
**/
function order_posts_by_title( $query ) { 
   if ( $query-is_home() && $query-is_main_query() ) { 
     $query-set( 'orderby', 'title' ); 
     $query-set( 'order', 'ASC' ); 
   } 
} 
add_action( 'pre_get_posts', 'order_posts_by_title' );
?>
