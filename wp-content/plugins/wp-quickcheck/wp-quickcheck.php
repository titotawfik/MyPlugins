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

define('WPQC_VERSION', '0.1.0 ');
define('WPQC_TEXTDOMAIN', 'wp-quickcheck');
define('WPQC_NAME', 'wp-quickcheck');
define('WPQC_PLUGIN_ROOT', plugin_dir_path(__FILE__));
define('WPQC_PLUGIN_ABSOLUTE', __FILE__);
define('WPQC_MIN_PHP_VERSION', '7.4');
define('WPQC_WP_VERSION', '6.8.3');
define('WPQC_PLUGIN_BASENAME', plugin_basename(__FILE__));
