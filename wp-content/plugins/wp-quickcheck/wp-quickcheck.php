<?php

/**
 * @package   WP_QuickCheck
 * @author    Tito Bakr
 * Plugin Name:     wp-quickcheck
 * Plugin Slug:     wp-quickcheck
 * Text Domain:     wp-quickcheck
 * Description:     Quickcheck Plugin Test
 * Version:         0.1.0  
 * Author:          Tito Bakr
 * License:         GPL v3 or later
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.txt
 * Requires PHP:    7.4
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}

define('WPQC_VERSION', '0.1.0');
define('WPQC_PLUGIN_NAME', 'wp-quickcheck');
define('WPQC_PLUGIN_SLUG', WPQC_PLUGIN_NAME);
define('WPQC_TEXTDOMAIN', WPQC_PLUGIN_NAME);
define('WPQC_PLUGIN_ROOT', plugin_dir_path(__FILE__));
define('WPQC_PLUGIN_ABSOLUTE', __FILE__);
define('WPQC_MIN_PHP_VERSION', '7.4');
define('WPQC_WP_VERSION', '6.8.3');
define('WPQC_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('WPQC_PLUGIN_URL', plugin_dir_url(__FILE__));

// Load Text Domain for Translations if needed
add_action('plugins_loaded', function () {
    load_plugin_textdomain(WPQC_TEXTDOMAIN, false, dirname(WPQC_PLUGIN_BASENAME) . '/languages');
});

/** 
 * Create a shortcode [qc_form] that outputs a form with:
 * Techical question: What happen you adding the shorcode 10 times on the same page? Highlight any potential issues and suggest improvements.
 * What Require once can do here to optimize performance? 
 */
function wpqc_form_shortcode()
{
    //Load plugin assests only if shorcode is used
    require_once WPQC_PLUGIN_ROOT . 'includes/wpqc-assets.php';
    //Load the shortcode template
    require_once WPQC_PLUGIN_ROOT . 'templates/shortcode.php';
    return wpqc_shortcode_template();
}
add_shortcode('qc_form', 'wpqc_form_shortcode');

/**
 *  Create custom table on plugin activation
 * {prefix}_wpqc_inputs with fields: id (INT, auto-increment, primary key) input_text (VARCHAR 255) submitted_at (datetime)
 */
function wpqc_create_table()
{

    global $wpdb;
    $table_name = $wpdb->prefix . 'wpqc_inputs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id int(9) NOT NULL AUTO_INCREMENT,
        input_text varchar(255) NOT NULL,
        submitted_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'wpqc_create_table');

/** 
 *  Store the submitted text into a custom table (details below) using a secure SQL statement. 
 * @param string $input The input text to store
 * Technical question: What security considerations should be taken into account when storing user-submitted data in the database? 
 * What Validation and sanitization steps are necessary to only submit text not numbers or special characters? 
 */
function wpqc_store_input(string $input)
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'wpqc_inputs';

    $wpdb->insert(
        $table_name,
        array(
            'input_text' => $input,
            'submitted_at' => current_time('mysql'),
        ),
        array(
            '%s',
            '%s',
        )
    );
}

/**
 * Register the AJAX endpoint for saving entries
 * Accessible for both logged-in and non-logged-in users to submit data via the form.
 * sanitization and validation of input data.
 */
function wpqc_store_input_ajax()
{
    check_ajax_referer('wpqc_nonce', 'nonce');
    $input = isset($_POST['input']) ? sanitize_text_field($_POST['input']) : '';
    if (empty($input)) {
        wp_send_json_error(array(
            'message' => 'You Entered: Unsafe entery or empty input, please try again.',
            'field'   => 'qc_input'
        ));
    }

    wpqc_store_input($input);

    wp_send_json_success('Saved');
}
add_action('wp_ajax_wpqc_store_input', 'wpqc_store_input_ajax');
add_action('wp_ajax_nopriv_wpqc_store_input', 'wpqc_store_input_ajax');

/**
 * Add an AJAX endpoint that returns the last five saved entries as JSON 
 * Accessible for logged-in users only.
 * Proper capability checks should be in place.
 */
function wpqc_get_last_five_entries()
{
    check_ajax_referer('wpqc_nonce', 'nonce');

    if (!is_user_logged_in() || !current_user_can('edit_posts')) {
        wp_send_json_error('Unauthorized', 403);
    }
    // Delegate the query to a helper so it can be reused and inspected.
    $results = wpqc_get_last_five_entries_array();

    wp_send_json_success($results);
}
add_action('wp_ajax_wpqc_get_last_five_entries', 'wpqc_get_last_five_entries');

/**
 * Helper function to get the last five entries from the database.
 * Return the last five entries as an array (no output).
 * Useful for reuse and for debugging (var_dump).
 * Technical question: What are the benefits of separating database query logic into its own function?
 * How does this improve code maintainability and testability?
 * What is alternative ways to prepare SQL statements in WordPress besides $wpdb->prepare()? 
 * What is the benefits of using of using array_map function instead of a foreach loop here?    
 *
 * @return array
 */
function wpqc_get_last_five_entries_array()
{
    global $wpdb;
    $qctable = $wpdb->prefix . 'wpqc_inputs';

    $query = $wpdb->prepare(
        "SELECT id, input_text, submitted_at
         FROM {$qctable}
         ORDER BY submitted_at DESC, id DESC
         LIMIT %d",
        5
    );

    $results = $wpdb->get_results($query, ARRAY_A);

    return array_map(function ($row) {
        $row['id'] = (int) $row['id'];
        return $row;
    }, $results);
}
