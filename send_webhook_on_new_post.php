<?php
/**
 * Plugin Name: Send Webhook on New Post
 * Plugin URI: https://github.com/taylorjadin/tiny-wordpress-plugins
 * Description: Sends a webhook whenever a new blog post is published. Set WEBHOOK_URL in wp-config.php.
 * Version: 1.1
 * Author: Taylor Jadin
 */

// This was designed for use with Discord, but should work with other stuff too.
// Set a constant in wp-config.php to define the Webhook URL like this:
// define('WEBHOOK_URL', 'https://discord.com/api/webhooks/your-webhook-url-here');

// Exit if accessed directly to prevent security breaches
if (!defined("ABSPATH")) {
    exit();
}

add_action("transition_post_status", "simple_webhook_on_publish", 10, 3);

function simple_webhook_on_publish($new_status, $old_status, $post)
{
    // Get GMT timestamp to avoid timzone stuff
    $published_ts = get_post_time('U', true, $post);   // post publish time
    $now_ts       = current_time('timestamp', true);   // current WP time (GMT)

    // Return unless newly published standard post, and not older than 24h
    if (
        $new_status !== "publish" ||
        $old_status === "publish" ||
        $post->post_type !== "post" ||
        ($now_ts - $published_ts) > DAY_IN_SECONDS
    ) {
        return;
    }
   

    // Get the Webhook URL from wp-config.php constant
    if (!defined("WEBHOOK_URL")) {
        error_log(
            "Send Webhook on New Post: WEBHOOK_URL constant is not defined in wp-config.php",
        );
        return;
    }
    $webhook_url = WEBHOOK_URL;

    // Gather Post Data
    $post_url = get_permalink($post->ID);

    // Build the JSON payload
    $payload = wp_json_encode([
        "content" => "$post_url",
    ]);

    // Send it
    $response = wp_remote_post($webhook_url, [
        "headers" => ["Content-Type" => "application/json; charset=utf-8"],
        "body" => $payload,
        "method" => "POST",
        "data_format" => "body",
    ]);

    // Log errors for debugging
    if (is_wp_error($response)) {
        error_log("Webhook error: " . $response->get_error_message());
    } else {
        $status_code = wp_remote_retrieve_response_code($response);
        if ($status_code < 200 || $status_code >= 300) {
            error_log(
                "Webhook returned HTTP $status_code: " .
                    wp_remote_retrieve_body($response),
            );
        }
    }
}
