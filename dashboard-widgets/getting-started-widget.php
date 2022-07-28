<?php
/**
* Plugin Name: Getting Started Widget
* Plugin URI: https://github.com/TaylorJadin/tiny-wordpress-plugins
* Description: This plugin adds a simple text widget to the dashboard.
* Version: 1.0
* Author: Taylor Jadin
* Author URI: https://jadin.me/
**/

$widget_title = 'Getting Started';
$widget_text = '<h2>Customize your Domains Community site:</h2><ul><li>Change colors, menu, header and other theme stuff: <a href="customize.php">Customize</a></li><li>Change the style of the homepage grid: <a href="post.php?post=56&action=edit&section=sc-style">The Post Grid > Site Grid > Style</a></li><li>Edit the submission form: <a href="admin.php?page=gf_edit_forms&id=1">Forms > Submit Site</a></li></ul>';

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