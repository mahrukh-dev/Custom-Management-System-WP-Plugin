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
        // Auth pages
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
        
        // Main Admin pages
        [
            'slug' => 'main-admin',
            'title' => 'Main Admin',
            'content' => '[cms_main_admin_list]'
        ],
        [
            'slug' => 'add-admin',
            'title' => 'Add Admin',
            'content' => '[cms_main_admin_form]'
        ],
        [
            'slug' => 'edit-admin',
            'title' => 'Edit Admin',
            'content' => '[cms_update_main_admin]'
        ],
        [
            'slug' => 'view-admin',
            'title' => 'View Admin',
            'content' => '[cms_view_main_admin]'
        ],
        
        // Admin (Regular) pages
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
        // EMPLOYEE PAGES - ADD THESE
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
        ]
    ];

    foreach ($pages_to_create as $page) {
        $existing_page = get_page_by_path($page['slug']);
        
        if (!$existing_page) {
            error_log('CMS: Creating page - ' . $page['slug']);
            
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
    // Main Admin rewrite rules
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
    
    // Regular Admin rewrite rules
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
    // EMPLOYEE REWRITE RULES - ADD THESE
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
    
    error_log('CMS: Rewrite rules added for Main Admin, Admin, and Employee');
}
add_action('init', 'cms_add_rewrite_rules', 10);

/**
 * Add query vars
 */
function cms_add_query_vars($vars) {
    $vars[] = 'admin_id';
    $vars[] = 'employee_id'; // ADD THIS
    error_log('CMS: Query vars registered - admin_id, employee_id added');
    return $vars;
}
add_filter('query_vars', 'cms_add_query_vars', 99);

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
            strpos($pattern, 'edit-employee') !== false) {
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
?>