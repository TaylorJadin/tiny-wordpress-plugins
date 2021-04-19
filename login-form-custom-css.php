<?php
/**
* Plugin Name: Add custom CSS to login form
* Plugin URI: https://github.com/TaylorJadin/tiny-wordpress-plugins
* Description: This plugin adds custom CSS to the login form.
* Right now I use this to hide everything but the Google button in combination with the Nextend Social login plugin
* Version: 1.0
* Author: Taylor Jadin
* Author URI: https://jadin.me/
**/
function login_form_custom_css() {
    echo '<style type="text/css">
        #login > h1 {display: none;}
        #login > p.message {display: none;}
	#loginform > p:nth-child(1) {display: none;}
	#loginform > div.user-pass-wrap {display: none;}
	#loginform > p.forgetmenot {display: none;}
        #loginform > p.submit {display: none;}
        #nsl-custom-login-form-main .nsl-container-login-layout-below {padding: 0px !important;}
        #nav {display: none;}
	</style>';
}
add_action('login_head', 'login_form_custom_css');
?>
