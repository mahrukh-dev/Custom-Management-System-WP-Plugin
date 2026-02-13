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

if (!defined('CMS_PLUGIN_DB_VER')) {
    define('CMS_PLUGIN_DB_VER', '1.0.2');
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
//require_once CMS_PLUGIN_DIR_PATH . 'includes/shortcodes.php';
//auth shortcodes
require_once CMS_PLUGIN_DIR_PATH . 'includes/shortcodes/auth/cms_login.php';
require_once CMS_PLUGIN_DIR_PATH . 'includes/shortcodes/auth/cms_forgot_password.php';
//main admin shortcode
require_once CMS_PLUGIN_DIR_PATH . 'includes/shortcodes/main-admin/cms_create_main_admin.php';
require_once CMS_PLUGIN_DIR_PATH . 'includes/shortcodes/main-admin/cms_list_main_admin.php';
require_once CMS_PLUGIN_DIR_PATH . 'includes/shortcodes/main-admin/cms_update_main_admin.php';
require_once CMS_PLUGIN_DIR_PATH . 'includes/shortcodes/main-admin/cms_view_main_admin.php';
//admin shortcodes
require_once CMS_PLUGIN_DIR_PATH . 'includes/shortcodes/admin/cms_create_admin.php';
require_once CMS_PLUGIN_DIR_PATH . 'includes/shortcodes/admin/cms_list_admin.php';
require_once CMS_PLUGIN_DIR_PATH . 'includes/shortcodes/admin/cms_update_admin.php';
require_once CMS_PLUGIN_DIR_PATH . 'includes/shortcodes/admin/cms_view_admin.php';
// employee shortcodes
require_once CMS_PLUGIN_DIR_PATH . 'includes/shortcodes/employee/cms_create_employee.php';
require_once CMS_PLUGIN_DIR_PATH . 'includes/shortcodes/employee/cms_list_employee.php';
require_once CMS_PLUGIN_DIR_PATH . 'includes/shortcodes/employee/cms_update_employee.php';
require_once CMS_PLUGIN_DIR_PATH . 'includes/shortcodes/employee/cms_view_employee.php';

// Load slug configuration
require_once CMS_PLUGIN_DIR_PATH . 'includes/config/slugs.php';
require_once CMS_PLUGIN_DIR_PATH . 'includes/config/page-router.php';

register_activation_hook(__FILE__, 'cms_activate_plugin');
function cms_activate_plugin() {
    error_log('=== CMS PLUGIN ACTIVATION STARTED ===');
    
    // Load required files
    require_once CMS_PLUGIN_DIR_PATH . 'includes/config/slugs.php';
    require_once CMS_PLUGIN_DIR_PATH . 'includes/config/page-router.php';
    
    // Debug: Check if constants are defined
    error_log('CMS_LOGIN_PAGE_SLUG defined: ' . (defined('CMS_LOGIN_PAGE_SLUG') ? 'YES' : 'NO'));
    if (defined('CMS_LOGIN_PAGE_SLUG')) {
        error_log('CMS_LOGIN_PAGE_SLUG = ' . CMS_LOGIN_PAGE_SLUG);
    }
    
    error_log('CMS_MAIN_ADMIN_PAGE_SLUG defined: ' . (defined('CMS_MAIN_ADMIN_PAGE_SLUG') ? 'YES' : 'NO'));
    
    // Create pages immediately
    if (function_exists('cms_create_required_pages')) {
        error_log('Calling cms_create_required_pages()');
        cms_create_required_pages();
    } else {
        error_log('ERROR: cms_create_required_pages() function not found');
    }
    
    // Flush rewrite rules
    flush_rewrite_rules();
    update_option('cms_permalinks_flushed', true);
    
    error_log('=== CMS PLUGIN ACTIVATION COMPLETED ===');
}

// TEMPORARY: Force flush rewrite rules on every page load
add_action('init', 'cms_force_flush_rules', 999);
function cms_force_flush_rules() {
    global $wp_rewrite;
    $wp_rewrite->flush_rules(true);
    error_log('CMS: Rewrite rules force flushed');
}


// Create upload directory for employee documents
add_action('init', 'cms_create_upload_directory');
function cms_create_upload_directory() {
    $upload_dir = wp_upload_dir();
    $cms_upload_dir = $upload_dir['basedir'] . '/cms-employee-docs/';
    
    if (!file_exists($cms_upload_dir)) {
        wp_mkdir_p($cms_upload_dir);
        
        // Add index.php for security
        $index_file = $cms_upload_dir . 'index.php';
        if (!file_exists($index_file)) {
            file_put_contents($index_file, '<?php // Silence is golden');
        }
        
        // Add .htaccess to prevent direct access
        $htaccess_file = $cms_upload_dir . '.htaccess';
        if (!file_exists($htaccess_file)) {
            file_put_contents($htaccess_file, 'Deny from all');
        }
    }
}
?>