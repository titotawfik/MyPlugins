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
function wpqc_shortcode_template()
{
    ob_start();
?>
    <form id="qc-form" class="qc-form">
        <label class="qc_input-label" for="qc_input">Enter something:</label>
        <input type="text" id="qc_input" name="qc_input" class="qc_text-input" required>
        <button type="submit" id="qc_submit" class="qc_submit-btn" aria-disabled="true" disabled>Submit</button>
        <output id="char_count" class="qc_text-output" aria-live="polite" aria-atomic="true"></output>
    </form>
    <?php if (is_user_logged_in() && current_user_can('edit_posts')) : ?>
        <ul id="entries_list" class="qc-list"></ul>
    <?php else : ?>
        <p>You must be logged in to view the last five
            entries.</p>
    <?php endif; ?>

<?php
    return ob_get_clean();
}
