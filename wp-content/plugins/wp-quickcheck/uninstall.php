<?php

/**
 * Uninstall WP QuickCheck
 *
 * @package WP_QuickCheck
 */

// Exit if uninstall not called from WordPress
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

global $wpdb;

$table_name = $wpdb->prefix . 'wpqc_inputs';

// Drop table safely
$wpdb->query("DROP TABLE IF EXISTS `{$table_name}`");
