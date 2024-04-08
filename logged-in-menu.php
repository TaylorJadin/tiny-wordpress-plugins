<?php
/*
* Plugin Name: Logged In and Logged Out Menu
* Description: Have a different menu depending on wether you are logged in or out. Name the menus "logged-in" or "logged-out" respectively.
* Version: 1.0
* Author: Taylor Jadin
* Author URI: https://jadin.me/
*/
/* Start Adding Functions Below this Line */
function my_wp_nav_menu_args( $args = '' ) {
 
    if( is_user_logged_in() ) { 
        $args['menu'] = 'logged-in';
    } else { 
        $args['menu'] = 'logged-out';
    } 
        return $args;
    }
    add_filter( 'wp_nav_menu_args', 'my_wp_nav_menu_args' );
?>
