<?php
/*
 * Plugin Name: Custom Management System
 * Description:       Handle Employees, Attendance, Salary and Corporate Accounts
 * Version:           1.0.0
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Mah Rukh
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       custom-management-sys
 * Domain Path:       /languages
 */

if (!defined("ABSPATH")) {
    exit; //Exit if accessed directly
}
if (!defined('CMS_PLUGIN_VERSION')) {
    define("CMS_PLUGIN_VERSION", '1.0.0');
}
if (!defined('CMS_PLUGIN_DIR_PATH')) {
    define('CMS_PLUGIN_DIR_PATH', plugin_dir_path(__FILE__));
    // print_r(CMS_PLUGIN_DIR_PATH);
}

if (!defined('CMS_PLUGIN_DIR_URL')) {
    define('CMS_PLUGIN_DIR_URL', plugin_dir_url(__FILE__));
    // print_r(CMS_PLUGIN_DIR_URL);
}
if (!defined('CMS_PLUGIN_URL')) {
    define('CMS_PLUGIN_URL', plugins_url(__FILE__));
    // print_r(CMS_PLUGIN_URL);
}
if (!defined('CMS_PLUGIN_BASENAME')) {
    define('CMS_PLUGIN_BASENAME', plugin_basename(__FILE__));
    // print_r(CMS_PLUGIN_BASENAME);
}

// include scripts and styles
require_once CMS_PLUGIN_DIR_PATH . 'includes/scripts.php';

//actions and filters
require_once CMS_PLUGIN_DIR_PATH . 'includes/hooks.php';

//include CPT, Taxonomy and metaboxes
require_once CMS_PLUGIN_DIR_PATH . 'includes/cpt.php';
require_once CMS_PLUGIN_DIR_PATH . 'includes/taxonomy.php';
require_once CMS_PLUGIN_DIR_PATH . 'includes/metaboxes.php';

// include shortcodes
require_once CMS_PLUGIN_DIR_PATH . 'includes/shortcodes.php';

//admin side menus
require_once CMS_PLUGIN_DIR_PATH . 'includes/admin-menu.php';
require_once CMS_PLUGIN_DIR_PATH . 'includes/admin-page.php';
require_once CMS_PLUGIN_DIR_PATH . 'includes/admin-settings.php';
