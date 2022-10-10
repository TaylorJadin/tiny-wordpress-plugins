<?php
/**
* Plugin Name: Blur User Info
* Plugin URI: https://github.com/TaylorJadin/tiny-wordpress-plugins
* Description: Adds CSS to blur user info in the Users view. Idea and CSS from: https://bionicteaching.com/handy-css-for-wp-presentation-privacy/
* Version: 1.0
* Author: Taylor Jadin
* Author URI: https://jadin.me/
**/
function blur_user_info() {
        echo '<style type="text/css">
        .email a, .column-username strong, td.column-name {
                filter: blur(4px);
              }
	</style>';
}
add_action('admin_head', 'blur_user_info');
?>
