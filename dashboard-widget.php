<?php
/**
* Plugin Name: Dashboard Widget
* Plugin URI: https://github.com/TaylorJadin/tiny-wordpress-plugins
* Description: This plugin adds a simple text widget to the dashboard. Based on: https://www.wpbeginner.com/wp-themes/how-to-add-custom-dashboard-widgets-in-wordpress/
* Version: 1.0
* Author: Taylor Jadin
* Author URI: https://jadin.me/
**/
add_action('wp_dashboard_setup', 'my_custom_dashboard_widgets');
  
function my_custom_dashboard_widgets() {
global $wp_meta_boxes;
 
wp_add_dashboard_widget('custom_help_widget', 'custom_dashboard_title', 'custom_dashboard_text');
}

function custom_dashboard_title() {
  echo 'Getting Started';
}

function custom_dashboard_text() {
echo '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>';
}
?>
