<?php

/**
 * @package   WP_QuickCheck
 * @author    Tito Bakr
 * Plugin Name:     wp-quickcheck
 * Description:     quickcheck plugin test
 * Version:         0.1.0  
 * Author:          Tito Bakr
 * Text Domain:     wp-quickcheck
 * License URI:     http://www.gnu.org/licenses/gpl-3.0.txt
 * Requires PHP:    7.4
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}

define('WPQC_VERSION', '0.1.0');
define('WPQC_TEXTDOMAIN', 'wp-quickcheck');
define('WPQC_NAME', 'wp-quickcheck');
define('WPQC_PLUGIN_ROOT', plugin_dir_path(__FILE__));
define('WPQC_PLUGIN_ABSOLUTE', __FILE__);
define('WPQC_MIN_PHP_VERSION', '7.4');
define('WPQC_WP_VERSION', '6.8.3');
define('WPQC_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('WPQC_PLUGIN_URL', plugin_dir_url(__FILE__));

// Enqueue plugin styles
function wpqc_enqueue_styles()
{
    wp_enqueue_style('wpqc-style', WPQC_PLUGIN_URL . 'css/wpqc-style.css', array(), WPQC_VERSION);
}

// Enqueue frontend scripts and styles
add_action('wp_enqueue_scripts', function () {
    // Add stylesheet 
    wpqc_enqueue_styles();
    // Enqueue the script
    wp_enqueue_script(
        'wpqc-frontend',
        WPQC_PLUGIN_URL . 'js/wpqc-script.js',
        array('jquery'),
        WPQC_VERSION,
        true
    );
    //Localise the script
    wp_localize_script('wpqc-frontend', 'wpqc_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce'    => wp_create_nonce('wpqc_nonce')
    ));
});

/** 
 * Create a shortcode [qc_form]
 */
function wpqc_form_shortcode()
{
    ob_start();
?>
    <form id="qc" class="qc-form">
        <label class="qc_input-label" for="qc_input">Enter something:</label>
        <input type="text" id="qc_input" name="qc_input" class="qc_text-input" required>
        <button type="submit" id="qc_submit" class="qc_submit-btn" disabled>Submit</button>
        <output id="char_count" class="qc_text-output"></output>
    </form>

    <ul id="entries_list" class="qc-list"></ul>
<?php
    return ob_get_clean();
}
add_shortcode('qc_form', 'wpqc_form_shortcode');

/** 
 *  Store the submitted text into a custom table (details below) using a secure SQL statement. 
 * @param string $input The input text to store
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
 *  Create custom table on plugin activation
 * create a table {prefix}_wpqc_inputs with fields: id (INT, auto-increment, primary key) input_text (VARCHAR 255) submitted_at (datetime)
 */
function wpqc_create_table()
{

    global $wpdb;
    $table_name = $wpdb->prefix . 'wpqc_inputs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        input_text varchar(255) NOT NULL,
        submitted_at datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'wpqc_create_table');

/**
 * Register the AJAX endpoint for saving entries
 * Proper capability checks should be in place.
 * Accessible for logged-in users only.
 * sanitization and validation of input data.
 */
function wpqc_store_input_ajax()
{
    check_ajax_referer('wpqc_nonce', 'nonce');

    if (!is_user_logged_in() || !current_user_can('edit_posts')) {
        wp_send_json_error('Unauthorized', 403);
    }

    $input = isset($_POST['input']) ? sanitize_text_field($_POST['input']) : '';
    if (empty($input)) {
        wp_send_json_error(array(
            'message' => 'You Entered: Unsave entery or empty input.',
            'field'   => 'qc_input'
        ));
    }

    wpqc_store_input($input);

    wp_send_json_success('Saved');
}
add_action('wp_ajax_wpqc_store_input', 'wpqc_store_input_ajax');

/**
 *  Add an AJAX endpoint that returns the last five saved entries as JSON 
 * Accessible for logged-in users only.
 * Proper capability checks should be in place.
 */
function wpqc_get_last_five_entries()
{
    check_ajax_referer('wpqc_nonce', 'nonce');

    if (!is_user_logged_in() || !current_user_can('edit_posts')) {
        wp_send_json_error('Unauthorized', 403);
    }

    global $wpdb;
    $table = $wpdb->prefix . 'wpqc_inputs';

    $query = $wpdb->prepare(
        "SELECT id, input_text, submitted_at
         FROM {$table}
         ORDER BY submitted_at DESC, id DESC
         LIMIT %d",
        5
    );

    $results = $wpdb->get_results($query, ARRAY_A);

    foreach ($results as &$row) {
        $row['id'] = (int) $row['id'];
    }

    wp_send_json($results);
}
add_action('wp_ajax_wpqc_get_last_five_entries', 'wpqc_get_last_five_entries');
