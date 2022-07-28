<?php
/**
* Plugin Name: Dashboard Widget
* Plugin URI: https://github.com/TaylorJadin/tiny-wordpress-plugins
* Description: This plugin adds a simple text widget to the dashboard.
* Version: 1.0
* Author: Taylor Jadin
* Author URI: https://jadin.me/
**/

$widget_title = 'Reports';
$widget_text = '
<a href="/stateu_last_logins.csv">Last Logins</a><br>
<a href="/stateu_installatron_applications_list.csv">Installatron Applications</a><br>
';

add_action('wp_dashboard_setup', 'my_custom_dashboard_widgets');
function my_custom_dashboard_widgets() {
global $wp_meta_boxes, $widget_title;
wp_add_dashboard_widget('custom_help_widget', $widget_title, 'custom_dashboard_text');
}

function custom_dashboard_text() {
global $widget_text;
echo $widget_text;
}
?>
