<?php
/**
* Plugin Name: Custom CSS for H5P
* Plugin URI: https://github.com/TaylorJadin/tiny-wordpress-plugins
* Description: This plugin adds custom CSS from wp-content/uploads/h5p/custom-h5p.css to H5P iframes
* Based on: https://ldx.design/h5p-custom-fonts-wordpress/#code-snippet
* Version: 1.0
* Author: Taylor Jadin
* Author URI: https://jadin.me/
**/

function myH5P_custom_css(&$styles, $libraries, $embed_type) {
    $styles[] = (object) array(
      // Path must be relative to wp-content/uploads/h5p or absolute.
      'path' => '/custom-h5p.css',
      'version' => '?ver=0.1'
    );
  }
  add_action('h5p_alter_library_styles', 'myH5P_custom_css', 10, 3);
?>