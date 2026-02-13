<?php
/**
 * CMS Page Router
 * Handles automatic page creation and URL routing
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Automatically create pages on plugin activation
 */
function cms_create_required_pages() {
    error_log('CMS: Starting page creation');
    
    $pages_to_create = [
        // ==============================================
        // AUTH PAGES
        // ==============================================
        [
            'slug' => 'login',
            'title' => 'Login',
            'content' => '[cms_login forgot_link="/forgot-password"]'
        ],
        [
            'slug' => 'forgot-password',
            'title' => 'Forgot Password',
            'content' => '[cms_forgot_password back_to_login_link="/login"]'
        ],
        
        // ==============================================
        // MAIN ADMIN PAGES
        // ==============================================
        [
            'slug' => 'main-admin',
            'title' => 'Main Admin',
            'content' => '[cms_main_admin_list]'
        ],
        [
            'slug' => 'add-admin',
            'title' => 'Add Main Admin',
            'content' => '[cms_main_admin_form]'
        ],
        [
            'slug' => 'edit-admin',
            'title' => 'Edit Main Admin',
            'content' => '[cms_update_main_admin]'
        ],
        [
            'slug' => 'view-admin',
            'title' => 'View Main Admin',
            'content' => '[cms_view_main_admin]'
        ],
        
        // ==============================================
        // REGULAR ADMIN PAGES
        // ==============================================
        [
            'slug' => 'admin-list',
            'title' => 'Admin Management',
            'content' => '[cms_list_admin]'
        ],
        [
            'slug' => 'add-admin2',
            'title' => 'Add Admin',
            'content' => '[cms_admin_form]'
        ],
        [
            'slug' => 'edit-admin2',
            'title' => 'Edit Admin',
            'content' => '[cms_update_admin]'
        ],
        [
            'slug' => 'view-admin2',
            'title' => 'View Admin',
            'content' => '[cms_view_admin]'
        ],
        
        // ==============================================
        // EMPLOYEE PAGES
        // ==============================================
        [
            'slug' => 'employee-list',
            'title' => 'Employee Management',
            'content' => '[cms_list_employee]'
        ],
        [
            'slug' => 'add-employee',
            'title' => 'Add Employee',
            'content' => '[cms_employee_form]'
        ],
        [
            'slug' => 'edit-employee',
            'title' => 'Edit Employee',
            'content' => '[cms_update_employee]'
        ],
        [
            'slug' => 'view-employee',
            'title' => 'View Employee',
            'content' => '[cms_view_employee]'
        ],
        
        // ==============================================
        // CORPORATE ACCOUNT PAGES
        // ==============================================
        [
            'slug' => 'corp-accounts',
            'title' => 'Corporate Accounts',
            'content' => '[cms_list_corp_acc]'
        ],
        [
            'slug' => 'add-corp-account',
            'title' => 'Add Corporate Account',
            'content' => '[cms_corp_acc_form]'
        ],
        [
            'slug' => 'edit-corp-account',
            'title' => 'Edit Corporate Account',
            'content' => '[cms_update_corp_acc]'
        ],
        [
            'slug' => 'view-corp-account',
            'title' => 'View Corporate Account',
            'content' => '[cms_view_corp_acc]'
        ],
        
        // ==============================================
        // ASSIGNMENT PAGES
        // ==============================================
        [
            'slug' => 'emp-corp-assign',
            'title' => 'Employee Corporate Assignment',
            'content' => '[cms_emp_corp_assign title="Employee Corporate Account Assignment" show_filters="yes" show_search="yes"]'
        ],
        
        // ==============================================
        // EMPLOYEE DASHBOARD & SHIFT MANAGEMENT PAGES
        // ==============================================
        [
            'slug' => 'employee-dashboard',
            'title' => 'Employee Dashboard',
            'content' => '[cms_employee_dashboard title="Employee Dashboard" welcome_message="Welcome back" show_history="yes"]'
        ],
        [
            'slug' => 'shift-history',
            'title' => 'Shift History',
            'content' => '[cms_emp_shift_history_list title="Employee Shift History" show_filters="yes" show_employee_filter="yes"]'
        ],
        [
            'slug' => 'employee-shift-history',
            'title' => 'Employee Shift History',
            'content' => '[cms_single_emp_shift_history title="Employee Shift History" show_summary="yes" show_chart="yes" days="30"]'
        ],
        // Add with other pages
[
    'slug' => 'shift-management',
    'title' => 'Shift Management',
    'content' => '[cms_emp_shift_management title="Employee Shift Management" show_corp_filter="yes"]'
]
    ];

    foreach ($pages_to_create as $page) {
        // Check if page already exists by slug
        $existing_page = get_page_by_path($page['slug']);
        
        if (!$existing_page) {
            error_log('CMS: Creating page - ' . $page['slug']);
            
            // Create the page
            $page_id = wp_insert_post([
                'post_title' => $page['title'],
                'post_name' => $page['slug'],
                'post_content' => $page['content'],
                'post_status' => 'publish',
                'post_type' => 'page',
                'comment_status' => 'closed',
                'ping_status' => 'closed',
            ]);
            
            if (is_wp_error($page_id)) {
                error_log('CMS: Failed to create page ' . $page['slug'] . ' - ' . $page_id->get_error_message());
            } else {
                error_log('CMS: Successfully created page ' . $page['slug'] . ' with ID ' . $page_id);
            }
        } else {
            error_log('CMS: Page already exists - ' . $page['slug']);
        }
    }
    
    update_option('cms_pages_created', true);
    error_log('CMS: Page creation completed');
}

/**
 * Add rewrite rules for pretty URLs
 */
function cms_add_rewrite_rules() {
    // ==============================================
    // MAIN ADMIN REWRITE RULES
    // ==============================================
    add_rewrite_rule(
        '^view-admin/([0-9]+)/?$',
        'index.php?pagename=view-admin&admin_id=$matches[1]',
        'top'
    );
    
    add_rewrite_rule(
        '^edit-admin/([0-9]+)/?$',
        'index.php?pagename=edit-admin&admin_id=$matches[1]',
        'top'
    );
    
    // ==============================================
    // REGULAR ADMIN REWRITE RULES
    // ==============================================
    add_rewrite_rule(
        '^view-admin2/([0-9]+)/?$',
        'index.php?pagename=view-admin2&admin_id=$matches[1]',
        'top'
    );
    
    add_rewrite_rule(
        '^edit-admin2/([0-9]+)/?$',
        'index.php?pagename=edit-admin2&admin_id=$matches[1]',
        'top'
    );
    
    // ==============================================
    // EMPLOYEE REWRITE RULES
    // ==============================================
    add_rewrite_rule(
        '^view-employee/([0-9]+)/?$',
        'index.php?pagename=view-employee&employee_id=$matches[1]',
        'top'
    );
    
    add_rewrite_rule(
        '^edit-employee/([0-9]+)/?$',
        'index.php?pagename=edit-employee&employee_id=$matches[1]',
        'top'
    );
    
    // ==============================================
    // CORPORATE ACCOUNT REWRITE RULES
    // ==============================================
    add_rewrite_rule(
        '^view-corp-account/([0-9]+)/?$',
        'index.php?pagename=view-corp-account&corp_id=$matches[1]',
        'top'
    );
    
    add_rewrite_rule(
        '^edit-corp-account/([0-9]+)/?$',
        'index.php?pagename=edit-corp-account&corp_id=$matches[1]',
        'top'
    );
    
    // ==============================================
    // EMPLOYEE SHIFT HISTORY REWRITE RULES
    // ==============================================
    add_rewrite_rule(
        '^employee-shift-history/([^/]+)/?$',
        'index.php?pagename=employee-shift-history&username=$matches[1]',
        'top'
    );
    
    add_rewrite_rule(
        '^employee-shift-history/id/([0-9]+)/?$',
        'index.php?pagename=employee-shift-history&employee_id=$matches[1]',
        'top'
    );
    
    error_log('CMS: Rewrite rules added for all modules');
}
add_action('init', 'cms_add_rewrite_rules', 10);

/**
 * Add query vars
 */
function cms_add_query_vars($vars) {
    $vars[] = 'admin_id';
    $vars[] = 'employee_id';
    $vars[] = 'corp_id';
    $vars[] = 'assignment_id';
    $vars[] = 'shift_id';
    $vars[] = 'username';
    $vars[] = 'date_from';
    $vars[] = 'date_to';
    error_log('CMS: Query vars registered - admin_id, employee_id, corp_id, assignment_id, shift_id, username, date_from, date_to');
    return $vars;
}
add_filter('query_vars', 'cms_add_query_vars', 99);

/**
 * Template redirect for handling dynamic pages
 */
function cms_template_redirect() {
    global $wp_query;
    
    // Handle employee shift history by username
    if (isset($wp_query->query_vars['pagename']) && $wp_query->query_vars['pagename'] === 'employee-shift-history') {
        $username = get_query_var('username');
        $employee_id = get_query_var('employee_id');
        
        if (empty($username) && !empty($employee_id)) {
            // We don't define get_employee_by_id here, it should be loaded from the shortcode file
            // The function will be available when the shortcode is loaded
            if (function_exists('get_employee_by_id')) {
                $employee = get_employee_by_id($employee_id);
                if ($employee) {
                    $wp_query->query_vars['username'] = $employee['username'];
                }
            }
        }
    }
}
add_action('template_redirect', 'cms_template_redirect');

/**
 * Debug rewrite rules
 */
add_action('init', 'cms_debug_rewrites', 999);
function cms_debug_rewrites() {
    global $wp_rewrite;
    error_log('=== CMS REWRITE RULES ===');
    $rules = $wp_rewrite->wp_rewrite_rules();
    foreach ($rules as $pattern => $query) {
        if (strpos($pattern, 'view-admin') !== false || 
            strpos($pattern, 'edit-admin') !== false ||
            strpos($pattern, 'view-employee') !== false || 
            strpos($pattern, 'edit-employee') !== false ||
            strpos($pattern, 'view-corp-account') !== false || 
            strpos($pattern, 'edit-corp-account') !== false ||
            strpos($pattern, 'employee-shift-history') !== false) {
            error_log($pattern . ' => ' . $query);
        }
    }
    error_log('==========================');
}

/**
 * Flush rewrite rules on plugin activation
 */
function cms_flush_rewrites_on_activation() {
    cms_add_rewrite_rules();
    flush_rewrite_rules();
    update_option('cms_permalinks_flushed', true);
}

// REMOVED: The duplicate get_employee_by_id function that was causing the fatal error