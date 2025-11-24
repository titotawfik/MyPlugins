<?php

/**
 * Enqueue frontend styles and scripts
 *
 * @package WP_QuickCheck
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}

function wpqc_enqueue_styles()
{
    wp_enqueue_style('wpqc-style', WPQC_PLUGIN_URL . 'css/wpqc-style.css', array(), WPQC_VERSION);
}
function wpqc_enqueue_frontend_scripts()
{
    // Enqueue the script
    wp_enqueue_script(
        'wpqc-frontend',
        WPQC_PLUGIN_URL . 'js/wpqc-script.js',
        array('jquery'),
        WPQC_VERSION,
        true
    );
    // Localise the script
    wp_localize_script('wpqc-frontend', 'wpqc_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('wpqc_nonce'),
        'is_logged_in' => is_user_logged_in()
    ));
}

// Register and enqueue plugin assets
function wpqc_enqueue_assets()
{
    wpqc_enqueue_styles();
    wpqc_enqueue_frontend_scripts();
}
add_action('wp_enqueue_scripts', 'wpqc_enqueue_assets');
