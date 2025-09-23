<?php
/**
 * Plugin Name:       Quick Escape Button
 * Plugin URI:        https://example.com/plugins/quick-escape-button/
 * Description:       Adds a discreet button that quickly navigates away from the current page using window.location.replace().
 * Version:           1.2.3
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
 * Add a new options page to the Settings menu.
 */
function qeb_add_admin_menu() {
    add_options_page(
        'Quick Escape Settings',
        'Quick Escape',
        'manage_options',
        'quick-escape-button',
        'qeb_settings_page_html'
    );
}
add_action( 'admin_menu', 'qeb_add_admin_menu' );

/**
 * Register plugin settings.
 */
function qeb_settings_init() {
    register_setting( 'quickEscapeButton', 'qeb_settings' );

    add_settings_section(
        'qeb_section_main',
        'Button Settings',
        null,
        'quickEscapeButton'
    );

    add_settings_field(
        'qeb_redirect_url',
        'Redirect URL',
        'qeb_redirect_url_callback',
        'quickEscapeButton',
        'qeb_section_main'
    );

    add_settings_field(
        'qeb_button_text',
        'Button Text',
        'qeb_button_text_callback',
        'quickEscapeButton',
        'qeb_section_main'
    );

    add_settings_field(
        'qeb_display_type',
        'Display Type',
        'qeb_display_type_callback',
        'quickEscapeButton',
        'qeb_section_main'
    );

    add_settings_field(
        'qeb_position',
        'Position',
        'qeb_position_callback',
        'quickEscapeButton',
        'qeb_section_main'
    );
}
add_action( 'admin_init', 'qeb_settings_init' );

/**
 * Callbacks to render settings fields.
 */
function qeb_redirect_url_callback() {
    $options = get_option( 'qeb_settings' );
    $url = isset( $options['redirect_url'] ) ? $options['redirect_url'] : 'https://www.google.com/';
    echo '<input type="url" name="qeb_settings[redirect_url]" value="' . esc_attr( $url ) . '" class="regular-text" />';
    echo '<p class="description">The URL to redirect to when the button is clicked.</p>';
}

function qeb_button_text_callback() {
    $options = get_option( 'qeb_settings' );
    $text = isset( $options['button_text'] ) ? $options['button_text'] : 'Quick Exit';
    echo '<input type="text" name="qeb_settings[button_text]" value="' . esc_attr( $text ) . '" class="regular-text" />';
    echo '<p class="description">The text to display on the button.</p>';
}

function qeb_display_type_callback() {
    $options = get_option( 'qeb_settings' );
    $type = isset( $options['display_type'] ) ? $options['display_type'] : 'floating';
    ?>
    <select name="qeb_settings[display_type]">
        <option value="floating" <?php selected( $type, 'floating' ); ?>>Floating Button</option>
        <option value="bar" <?php selected( $type, 'bar' ); ?>>Sticky Bar</option>
    </select>
    <?php
}

function qeb_position_callback() {
    $options = get_option( 'qeb_settings' );
    $position = isset( $options['position'] ) ? $options['position'] : 'bottom-right';
    ?>
    <select name="qeb_settings[position]">
        <optgroup label="Floating Button Positions">
            <option value="top-left" <?php selected( $position, 'top-left' ); ?>>Top-Left</option>
            <option value="top-right" <?php selected( $position, 'top-right' ); ?>>Top-Right</option>
            <option value="bottom-left" <?php selected( $position, 'bottom-left' ); ?>>Bottom-Left</option>
            <option value="bottom-right" <?php selected( $position, 'bottom-right' ); ?>>Bottom-Right</option>
        </optgroup>
        <optgroup label="Sticky Bar Positions">
            <option value="top-bar" <?php selected( $position, 'top-bar' ); ?>>Top</option>
            <option value="bottom-bar" <?php selected( $position, 'bottom-bar' ); ?>>Bottom</option>
        </optgroup>
    </select>
    <?php
}

/**
 * Renders the settings page HTML.
 */
function qeb_settings_page_html() {
    // Check user capabilities.
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }

    // Display form and settings sections.
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="options.php" method="post">
            <?php
            // Output security fields for the registered setting.
            settings_fields( 'quickEscapeButton' );
            // Output setting sections and fields.
            do_settings_sections( 'quickEscapeButton' );
            // Output save button.
            submit_button( 'Save Settings' );
            ?>
        </form>
    </div>
    <?php
}

/**
 * Render the button on the front-end based on saved settings.
 * This is hooked into the wp_footer to ensure it's at the end of the body tag.
 */
function qeb_render_button_on_frontend() {
    $options = get_option( 'qeb_settings' );
    
    // Fallback to default values if settings are not saved.
    $destination_url = isset( $options['redirect_url'] ) ? esc_url( $options['redirect_url'] ) : 'https://www.google.com/';
    $button_text = isset( $options['button_text'] ) ? sanitize_text_field( $options['button_text'] ) : 'Quick Exit';
    $display_type = isset( $options['display_type'] ) ? $options['display_type'] : 'floating';
    $position = isset( $options['position'] ) ? $options['position'] : 'bottom-right';

    $container_id = '';
    $container_class = '';
    $css = '';
    
    if ( 'floating' === $display_type ) {
        $container_id = 'quick-escape-button-container';
        $container_class = $position;
        $css = "
            #quick-escape-button-container {
                position: fixed !important;
                z-index: 1000 !important;
                padding: 15px !important;
            }
            #quick-escape-button-container.top-left { top: 0 !important; left: 0 !important; right: auto !important; bottom: auto !important; }
            #quick-escape-button-container.top-right { top: 0 !important; right: 0 !important; left: auto !important; bottom: auto !important; }
            #quick-escape-button-container.bottom-left { bottom: 0 !important; left: 0 !important; top: auto !important; right: auto !important; }
            #quick-escape-button-container.bottom-right { bottom: 0 !important; right: 0 !important; top: auto !important; left: auto !important; }
        ";
    } elseif ( 'bar' === $display_type ) {
        $container_id = 'quick-escape-button-bar';
        $container_class = $position;
        $css = "
            #quick-escape-button-bar {
                position: fixed !important;
                width: 100% !important;
                z-index: 1000 !important;
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
                padding: 10px 0 !important;
                background-color: rgba(0, 0, 0, 0.8) !important;
                box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
            }
            #quick-escape-button-bar.top-bar { top: 0 !important; left: 0 !important; bottom: auto !important; }
            #quick-escape-button-bar.bottom-bar { bottom: 0 !important; left: 0 !important; top: auto !important; }
        ";
    }

    // Common button styles.
    $css .= "
        .quick-escape-button {
            padding: 10px 20px !important;
            font-size: 16px !important;
            cursor: pointer !important;
            background-color: #f44336 !important;
            color: white !important;
            border: none !important;
            border-radius: 5px !important;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1) !important;
        }
    ";

    // Common JavaScript for the button.
    $script = "
        document.addEventListener('DOMContentLoaded', function() {
            var buttons = document.querySelectorAll('.quick-escape-button');
            buttons.forEach(function(button) {
                button.addEventListener('click', function(e) {
                    e.preventDefault();
                    var destinationUrl = this.getAttribute('data-destination');
                    window.location.replace(destinationUrl);
                });
            });
        });
    ";

    // Only display the button if a valid display type is chosen.
    if ( ! empty( $container_id ) ) {
        printf( '<style>%s</style>', $css );
        printf( '<div id="%s" class="%s"><button class="quick-escape-button" data-destination="%s">%s</button></div>',
            $container_id,
            $container_class,
            $destination_url,
            $button_text
        );
        printf( '<script>%s</script>', $script );
    }
}
add_action( 'wp_footer', 'qeb_render_button_on_frontend' );
