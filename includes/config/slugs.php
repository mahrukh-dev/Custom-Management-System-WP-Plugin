<?php
/**
 * Centralized Slug Configuration
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// ==============================================
// SHORTCODE SLUGS
// ==============================================

// Auth Shortcodes
if (!defined('CMS_LOGIN_SHORTCODE')) {
    define('CMS_LOGIN_SHORTCODE', 'cms_login');
}
if (!defined('CMS_FORGOT_PASSWORD_SHORTCODE')) {
    define('CMS_FORGOT_PASSWORD_SHORTCODE', 'cms_forgot_password');
}

// Main Admin Shortcodes
if (!defined('CMS_MAIN_ADMIN_CREATE_SHORTCODE')) {
    define('CMS_MAIN_ADMIN_CREATE_SHORTCODE', 'cms_main_admin_create');
}
if (!defined('CMS_MAIN_ADMIN_LIST_SHORTCODE')) {
    define('CMS_MAIN_ADMIN_LIST_SHORTCODE', 'cms_main_admin_list');
}
if (!defined('CMS_MAIN_ADMIN_UPDATE_SHORTCODE')) {
    define('CMS_MAIN_ADMIN_UPDATE_SHORTCODE', 'cms_main_admin_update');
}
if (!defined('CMS_MAIN_ADMIN_VIEW_SHORTCODE')) {
    define('CMS_MAIN_ADMIN_VIEW_SHORTCODE', 'cms_main_admin_view');
}

// Admin Shortcodes
if (!defined('CMS_ADMIN_CREATE_SHORTCODE')) {
    define('CMS_ADMIN_CREATE_SHORTCODE', 'cms_admin_create');
}
if (!defined('CMS_ADMIN_LIST_SHORTCODE')) {
    define('CMS_ADMIN_LIST_SHORTCODE', 'cms_admin_list');
}
if (!defined('CMS_ADMIN_UPDATE_SHORTCODE')) {
    define('CMS_ADMIN_UPDATE_SHORTCODE', 'cms_admin_update');
}
if (!defined('CMS_ADMIN_VIEW_SHORTCODE')) {
    define('CMS_ADMIN_VIEW_SHORTCODE', 'cms_admin_view');
}

// ==============================================
// EMPLOYEE SHORTCODES - ADD THESE
// ==============================================
if (!defined('CMS_EMPLOYEE_CREATE_SHORTCODE')) {
    define('CMS_EMPLOYEE_CREATE_SHORTCODE', 'cms_employee_create');
}
if (!defined('CMS_EMPLOYEE_LIST_SHORTCODE')) {
    define('CMS_EMPLOYEE_LIST_SHORTCODE', 'cms_employee_list');
}
if (!defined('CMS_EMPLOYEE_UPDATE_SHORTCODE')) {
    define('CMS_EMPLOYEE_UPDATE_SHORTCODE', 'cms_employee_update');
}
if (!defined('CMS_EMPLOYEE_VIEW_SHORTCODE')) {
    define('CMS_EMPLOYEE_VIEW_SHORTCODE', 'cms_employee_view');
}

// ==============================================
// PAGE SLUGS
// ==============================================

// Auth Pages
if (!defined('CMS_LOGIN_PAGE_SLUG')) {
    define('CMS_LOGIN_PAGE_SLUG', 'login');
}
if (!defined('CMS_LOGIN_PAGE_TITLE')) {
    define('CMS_LOGIN_PAGE_TITLE', 'Login');
}
if (!defined('CMS_FORGOT_PASSWORD_PAGE_SLUG')) {
    define('CMS_FORGOT_PASSWORD_PAGE_SLUG', 'forgot-password');
}
if (!defined('CMS_FORGOT_PASSWORD_PAGE_TITLE')) {
    define('CMS_FORGOT_PASSWORD_PAGE_TITLE', 'Forgot Password');
}

// Main Admin Pages
if (!defined('CMS_MAIN_ADMIN_PAGE_SLUG')) {
    define('CMS_MAIN_ADMIN_PAGE_SLUG', 'main-admin');
}
if (!defined('CMS_MAIN_ADMIN_PAGE_TITLE')) {
    define('CMS_MAIN_ADMIN_PAGE_TITLE', 'Main Admin');
}
if (!defined('CMS_MAIN_ADMIN_CREATE_PAGE_SLUG')) {
    define('CMS_MAIN_ADMIN_CREATE_PAGE_SLUG', 'add-admin');
}
if (!defined('CMS_MAIN_ADMIN_CREATE_PAGE_TITLE')) {
    define('CMS_MAIN_ADMIN_CREATE_PAGE_TITLE', 'Add Admin');
}
if (!defined('CMS_MAIN_ADMIN_EDIT_PAGE_SLUG')) {
    define('CMS_MAIN_ADMIN_EDIT_PAGE_SLUG', 'edit-admin');
}
if (!defined('CMS_MAIN_ADMIN_EDIT_PAGE_TITLE')) {
    define('CMS_MAIN_ADMIN_EDIT_PAGE_TITLE', 'Edit Admin');
}
if (!defined('CMS_MAIN_ADMIN_VIEW_PAGE_SLUG')) {
    define('CMS_MAIN_ADMIN_VIEW_PAGE_SLUG', 'view-admin');
}
if (!defined('CMS_MAIN_ADMIN_VIEW_PAGE_TITLE')) {
    define('CMS_MAIN_ADMIN_VIEW_PAGE_TITLE', 'View Admin');
}

// Admin Pages
if (!defined('CMS_ADMIN_PAGE_SLUG')) {
    define('CMS_ADMIN_PAGE_SLUG', 'admin-list');
}
if (!defined('CMS_ADMIN_PAGE_TITLE')) {
    define('CMS_ADMIN_PAGE_TITLE', 'Admin Management');
}
if (!defined('CMS_ADMIN_CREATE_PAGE_SLUG')) {
    define('CMS_ADMIN_CREATE_PAGE_SLUG', 'add-admin2');
}
if (!defined('CMS_ADMIN_CREATE_PAGE_TITLE')) {
    define('CMS_ADMIN_CREATE_PAGE_TITLE', 'Add Admin');
}
if (!defined('CMS_ADMIN_EDIT_PAGE_SLUG')) {
    define('CMS_ADMIN_EDIT_PAGE_SLUG', 'edit-admin2');
}
if (!defined('CMS_ADMIN_EDIT_PAGE_TITLE')) {
    define('CMS_ADMIN_EDIT_PAGE_TITLE', 'Edit Admin');
}
if (!defined('CMS_ADMIN_VIEW_PAGE_SLUG')) {
    define('CMS_ADMIN_VIEW_PAGE_SLUG', 'view-admin2');
}
if (!defined('CMS_ADMIN_VIEW_PAGE_TITLE')) {
    define('CMS_ADMIN_VIEW_PAGE_TITLE', 'View Admin');
}

// ==============================================
// EMPLOYEE PAGES - ADD THESE
// ==============================================
if (!defined('CMS_EMPLOYEE_PAGE_SLUG')) {
    define('CMS_EMPLOYEE_PAGE_SLUG', 'employee-list');
}
if (!defined('CMS_EMPLOYEE_PAGE_TITLE')) {
    define('CMS_EMPLOYEE_PAGE_TITLE', 'Employee Management');
}
if (!defined('CMS_EMPLOYEE_CREATE_PAGE_SLUG')) {
    define('CMS_EMPLOYEE_CREATE_PAGE_SLUG', 'add-employee');
}
if (!defined('CMS_EMPLOYEE_CREATE_PAGE_TITLE')) {
    define('CMS_EMPLOYEE_CREATE_PAGE_TITLE', 'Add Employee');
}
if (!defined('CMS_EMPLOYEE_EDIT_PAGE_SLUG')) {
    define('CMS_EMPLOYEE_EDIT_PAGE_SLUG', 'edit-employee');
}
if (!defined('CMS_EMPLOYEE_EDIT_PAGE_TITLE')) {
    define('CMS_EMPLOYEE_EDIT_PAGE_TITLE', 'Edit Employee');
}
if (!defined('CMS_EMPLOYEE_VIEW_PAGE_SLUG')) {
    define('CMS_EMPLOYEE_VIEW_PAGE_SLUG', 'view-employee');
}
if (!defined('CMS_EMPLOYEE_VIEW_PAGE_TITLE')) {
    define('CMS_EMPLOYEE_VIEW_PAGE_TITLE', 'View Employee');
}

// ==============================================
// URL PARAMETER SLUGS
// ==============================================
if (!defined('CMS_ADMIN_ID_PARAM')) {
    define('CMS_ADMIN_ID_PARAM', 'admin_id');
}
if (!defined('CMS_EMPLOYEE_ID_PARAM')) {
    define('CMS_EMPLOYEE_ID_PARAM', 'employee_id');
}
if (!defined('CMS_ACTION_PARAM')) {
    define('CMS_ACTION_PARAM', 'action');
}
?>