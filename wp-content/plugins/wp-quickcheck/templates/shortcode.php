<?php

/**
 * Shortcode template for WP QuickCheck plugin
 *
 * @package WP_QuickCheck
 */

if (!defined('ABSPATH')) {
    die('We\'re sorry, but you can not directly access this file.');
}
/** 
 * Create a shortcode template for the form
 */
function wpqc_shortcode_template($instance)
{
    $form_id = 'qc-form-' . $instance;
    $submit_id = 'qc_submit-' . $instance;
    $input_id = 'qc_input-' . $instance;
    $output_id = 'char_count-' . $instance;
    $list_id = 'entries_list-' . $instance;

    ob_start();
?>
    <form id="<?php echo esc_attr($form_id); ?>" class="qc-form" data-instance="<?php echo esc_attr($instance); ?>">
        <label class="qc_input-label" for="<?php echo esc_attr($input_id); ?>">Enter something:</label>
        <input type="text" id="<?php echo esc_attr($input_id); ?>" name="qc_input" class="qc_text-input" required>
        <button type="submit" id="<?php echo esc_attr($submit_id); ?>" class="qc_submit-btn" aria-disabled="true"
            disabled>Submit</button>
        <output id="<?php echo esc_attr($output_id); ?>" class="qc_text-output" aria-live="polite"
            aria-atomic="true"></output>
    </form>
    <?php if (is_user_logged_in() && current_user_can('edit_posts')) : ?>
        <ul id="<?php echo esc_attr($list_id); ?>" class="qc-list"></ul>
    <?php else : ?>
        <p>You must be logged in to view the last five
            entries.</p>
    <?php endif; ?>

<?php
    return ob_get_clean();
}
