<?php
/**
 * Plugin Name:       Quick Escape Button
 * Plugin URI:        https://example.com/plugins/quick-escape-button/
 * Description:       Adds a shortcode to place a discreet button that quickly navigates away from the current page using window.location.replace().
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Your Name
 * Author URI:        https://example.com/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       quick-escape-button
 * Domain Path:       /languages
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Enqueue scripts and styles.
 *
 * This function handles adding the necessary CSS and JavaScript files.
 * We'll use a single-file approach by embedding the JS directly.
 */
function qeb_enqueue_scripts() {
    // Check if the shortcode is used on the current page to avoid loading scripts unnecessarily.
    global $post;
    if ( is_a( $post, 'WP_Post' ) && has_shortcode( $post->post_content, 'quick_escape_button' ) ) {
        // Embed the JavaScript directly for simplicity in a single-file plugin.
        $script = "
            document.addEventListener('DOMContentLoaded', function() {
                var buttons = document.querySelectorAll('.quick-escape-button');
                buttons.forEach(function(button) {
                    button.addEventListener('click', function(e) {
                        e.preventDefault();
                        // Get the destination URL from the data-destination attribute.
                        var destinationUrl = this.getAttribute('data-destination');
                        
                        // Replace the current page with the destination URL.
                        // This prevents the current page from being in the browser history.
                        window.location.replace(destinationUrl);
                    });
                });
            });
        ";
        wp_add_inline_script( 'jquery', $script );
    }
}
add_action( 'wp_enqueue_scripts', 'qeb_enqueue_scripts' );

/**
 * Register the Quick Escape Button shortcode.
 *
 * This function creates the [quick_escape_button] shortcode.
 * It accepts two optional attributes: 'text' and 'destination'.
 *
 * @param array $atts Shortcode attributes.
 * @return string The HTML for the button.
 */
function qeb_shortcode( $atts ) {
    // Define default attributes.
    $atts = shortcode_atts(
        array(
            'text'        => 'Quick Exit',
            'destination' => 'https://www.google.com/',
        ),
        $atts,
        'quick_escape_button'
    );

    // Sanitize the attributes for security.
    $button_text = sanitize_text_field( $atts['text'] );
    $destination_url = esc_url( $atts['destination'] );

    // Build the HTML output.
    $output = sprintf(
        '<button class="quick-escape-button" data-destination="%s" style="padding: 10px 20px; font-size: 16px; cursor: pointer; background-color: #f44336; color: white; border: none; border-radius: 5px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">%s</button>',
        $destination_url,
        $button_text
    );

    return $output;
}
add_shortcode( 'quick_escape_button', 'qeb_shortcode' );
