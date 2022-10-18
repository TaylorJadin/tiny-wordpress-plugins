<?php
/**
* Plugin Name: Restrict registrations to email domain
* Plugin URI: https://github.com/TaylorJadin/tiny-wordpress-plugins
* Description: This plugin makes it so you need an @reclaimhosting.com email to register an account on this site.
* Version: 1.0
* Author: Taylor Jadin
* Author URI: https://jadin.me/
**/

// Restrict registration to specific domain
function is_valid_email_domain($login, $email, $errors ){
 $valid_email_domains = array("reclaimhosting.com");// allowed domains
 $valid = false; // sets default validation to false
 foreach( $valid_email_domains as $d ){
  $d_length = strlen( $d );
  $current_email_domain = strtolower( substr( $email, -($d_length), $d_length));
 if( $current_email_domain == strtolower($d) ){
  $valid = true;
  break;
 }
 }
 // Return error message for invalid domains
 if( $valid === false ){

$errors->add('domain_whitelist_error',__( '<strong>ERROR</strong>: Registration is only allowed from @reclaimhosting.com email accounts.' ));
 }
}
add_action('register_post', 'is_valid_email_domain',10,3 );
?>
