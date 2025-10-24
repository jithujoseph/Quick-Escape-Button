<?php
/**
 * Plugin Name:       Quick Escape Button
 * Plugin URI:        https://github.com/jithujoseph/Quick-Escape-Button
 * Description:       Adds a discreet button that quickly navigates away from the current page using window.location.replace().
 * Version:           1.4.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Jithin Ayinickal
 * Author URI:        http://www.jithinaj.com/
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

    // New field for button color.
    add_settings_field(
        'qeb_button_color',
        'Button Color',
        'qeb_button_color_callback',
        'quickEscapeButton',
        'qeb_section_main'
    );
    
    // New section for display rules.
    add_settings_section(
        'qeb_section_display_rules',
        'Display Rules',
        null,
        'quickEscapeButton'
    );
    
    add_settings_field(
        'qeb_display_rule',
        'Show Button On',
        'qeb_display_rule_callback',
        'quickEscapeButton',
        'qeb_section_display_rules'
    );
    
    add_settings_field(
        'qeb_selected_pages',
        'Select Pages',
        'qeb_selected_pages_callback',
        'quickEscapeButton',
        'qeb_section_display_rules'
    );
    
    add_settings_field(
        'qeb_include_child_pages',
        'Include Child Pages',
        'qeb_include_child_pages_callback',
        'qeb_section_display_rules'
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

function qeb_button_color_callback() {
    $options = get_option( 'qeb_settings' );
    $color = isset( $options['button_color'] ) ? $options['button_color'] : '#f44336';
    echo '<input type="color" name="qeb_settings[button_color]" value="' . esc_attr( $color ) . '" />';
    echo '<p class="description">Select a color for the floating button. This option is only for the Floating Button display type.</p>';
}

function qeb_display_rule_callback() {
    $options = get_option( 'qeb_settings' );
    $rule = isset( $options['display_rule'] ) ? $options['display_rule'] : 'all';
    ?>
    <label><input type="radio" name="qeb_settings[display_rule]" value="all" <?php checked( $rule, 'all' ); ?> /> Entire Site</label><br>
    <label><input type="radio" name="qeb_settings[display_rule]" value="pages" <?php checked( $rule, 'pages' ); ?> /> Selected Pages</label>
    <?php
}

function qeb_selected_pages_callback() {
    $options = get_option( 'qeb_settings' );
    $selected_pages = isset( $options['selected_pages'] ) ? (array) $options['selected_pages'] : array();
    
    $pages = get_pages();
    
    echo '<select name="qeb_settings[selected_pages][]" multiple class="regular-text">';
    foreach ( $pages as $page ) {
        $page_id = $page->ID;
        $page_title = $page->post_title;
        $selected = in_array( $page_id, $selected_pages ) ? 'selected' : '';
        echo '<option value="' . esc_attr( $page_id ) . '" ' . $selected . '>' . esc_html( $page_title ) . '</option>';
    }
    echo '</select>';
    echo '<p class="description">Select one or more pages to display the button on. Hold down Ctrl (Windows) or Cmd (Mac) to select multiple pages.</p>';
}

function qeb_include_child_pages_callback() {
    $options = get_option( 'qeb_settings' );
    $include_children = isset( $options['include_child_pages'] ) ? 1 : 0;
    ?>
    <label>
        <input type="checkbox" name="qeb_settings[include_child_pages]" value="1" <?php checked( $include_children, 1 ); ?> /> 
        Include child pages of the selected parent pages.
    </label>
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
    $button_color = isset( $options['button_color'] ) ? sanitize_hex_color( $options['button_color'] ) : '#f44336';
    $display_rule = isset( $options['display_rule'] ) ? $options['display_rule'] : 'all';
    $selected_pages = isset( $options['selected_pages'] ) ? (array) $options['selected_pages'] : array();
    $include_children = isset( $options['include_child_pages'] ) ? true : false;
    
    $show_button = false;
    
    if ( 'all' === $display_rule ) {
        $show_button = true;
    } elseif ( 'pages' === $display_rule && is_page() ) {
        $current_page_id = get_the_ID();
        if ( in_array( $current_page_id, $selected_pages ) ) {
            $show_button = true;
        } elseif ( $include_children ) {
            // Check if the current page is a child of any selected parent page.
            if ( $current_page_id && get_post_ancestors( $current_page_id ) ) {
                $ancestors = get_post_ancestors( $current_page_id );
                foreach ( $selected_pages as $page_id ) {
                    if ( in_array( $page_id, $ancestors ) ) {
                        $show_button = true;
                        break;
                    }
                }
            }
        }
    }
    
    if ( ! $show_button ) {
        return;
    }

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
            .quick-escape-button {
                background-color: {$button_color} !important;
            }
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
    
    // Build the final HTML output.
    $button_html = '';
    if ( ! empty( $container_id ) ) {
        $button_html = sprintf(
            '<div id="%s" class="%s"><button class="quick-escape-button" data-destination="%s">%s</button></div>',
            $container_id,
            $container_class,
            $destination_url,
            $button_text
        );
    }
    
    // **APPLY FILTER HERE**
    // Allows developers to modify the final button HTML before it is output.
    $button_html = apply_filters( 
        'qeb_button_html', 
        $button_html, 
        $options, 
        $display_type, 
        $position 
    );

    // Only display if the resulting HTML is not empty.
    if ( ! empty( $button_html ) ) {
        printf( '<style>%s</style>', $css );
        echo $button_html;
        printf( '<script>%s</script>', $script );
    }
}
add_action( 'wp_footer', 'qeb_render_button_on_frontend' );

