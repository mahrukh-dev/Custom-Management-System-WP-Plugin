<?php
/**
 * Centralized Slug Configuration
 * Single source of truth for all CMS slugs
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

// Admin (Regular) Shortcodes
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

// Employee Shortcodes
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

// Corporate Account Shortcodes
if (!defined('CMS_CORP_ACC_CREATE_SHORTCODE')) {
    define('CMS_CORP_ACC_CREATE_SHORTCODE', 'cms_corp_acc_create');
}
if (!defined('CMS_CORP_ACC_LIST_SHORTCODE')) {
    define('CMS_CORP_ACC_LIST_SHORTCODE', 'cms_corp_acc_list');
}
if (!defined('CMS_CORP_ACC_UPDATE_SHORTCODE')) {
    define('CMS_CORP_ACC_UPDATE_SHORTCODE', 'cms_corp_acc_update');
}
if (!defined('CMS_CORP_ACC_VIEW_SHORTCODE')) {
    define('CMS_CORP_ACC_VIEW_SHORTCODE', 'cms_corp_acc_view');
}

// Assignment Shortcodes
if (!defined('CMS_EMP_CORP_ASSIGN_SHORTCODE')) {
    define('CMS_EMP_CORP_ASSIGN_SHORTCODE', 'cms_emp_corp_assign');
}

// Employee Dashboard & Shift Management Shortcodes
if (!defined('CMS_EMPLOYEE_DASHBOARD_SHORTCODE')) {
    define('CMS_EMPLOYEE_DASHBOARD_SHORTCODE', 'cms_employee_dashboard');
}
if (!defined('CMS_EMP_SHIFT_HISTORY_LIST_SHORTCODE')) {
    define('CMS_EMP_SHIFT_HISTORY_LIST_SHORTCODE', 'cms_emp_shift_history_list');
}
if (!defined('CMS_SINGLE_EMP_SHIFT_HISTORY_SHORTCODE')) {
    define('CMS_SINGLE_EMP_SHIFT_HISTORY_SHORTCODE', 'cms_single_emp_shift_history');
}
// Add with the other shortcode constants
if (!defined('CMS_EMP_SHIFT_MANAGEMENT_SHORTCODE')) {
    define('CMS_EMP_SHIFT_MANAGEMENT_SHORTCODE', 'cms_emp_shift_management');
}

// Add page slug
if (!defined('CMS_EMP_SHIFT_MANAGEMENT_PAGE_SLUG')) {
    define('CMS_EMP_SHIFT_MANAGEMENT_PAGE_SLUG', 'shift-management');
}
if (!defined('CMS_EMP_SHIFT_MANAGEMENT_PAGE_TITLE')) {
    define('CMS_EMP_SHIFT_MANAGEMENT_PAGE_TITLE', 'Shift Management');
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
    define('CMS_MAIN_ADMIN_CREATE_PAGE_TITLE', 'Add Main Admin');
}
if (!defined('CMS_MAIN_ADMIN_EDIT_PAGE_SLUG')) {
    define('CMS_MAIN_ADMIN_EDIT_PAGE_SLUG', 'edit-admin');
}
if (!defined('CMS_MAIN_ADMIN_EDIT_PAGE_TITLE')) {
    define('CMS_MAIN_ADMIN_EDIT_PAGE_TITLE', 'Edit Main Admin');
}
if (!defined('CMS_MAIN_ADMIN_VIEW_PAGE_SLUG')) {
    define('CMS_MAIN_ADMIN_VIEW_PAGE_SLUG', 'view-admin');
}
if (!defined('CMS_MAIN_ADMIN_VIEW_PAGE_TITLE')) {
    define('CMS_MAIN_ADMIN_VIEW_PAGE_TITLE', 'View Main Admin');
}

// Admin (Regular) Pages
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

// Employee Pages
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

// Corporate Account Pages
if (!defined('CMS_CORP_ACC_PAGE_SLUG')) {
    define('CMS_CORP_ACC_PAGE_SLUG', 'corp-accounts');
}
if (!defined('CMS_CORP_ACC_PAGE_TITLE')) {
    define('CMS_CORP_ACC_PAGE_TITLE', 'Corporate Accounts');
}
if (!defined('CMS_CORP_ACC_CREATE_PAGE_SLUG')) {
    define('CMS_CORP_ACC_CREATE_PAGE_SLUG', 'add-corp-account');
}
if (!defined('CMS_CORP_ACC_CREATE_PAGE_TITLE')) {
    define('CMS_CORP_ACC_CREATE_PAGE_TITLE', 'Add Corporate Account');
}
if (!defined('CMS_CORP_ACC_EDIT_PAGE_SLUG')) {
    define('CMS_CORP_ACC_EDIT_PAGE_SLUG', 'edit-corp-account');
}
if (!defined('CMS_CORP_ACC_EDIT_PAGE_TITLE')) {
    define('CMS_CORP_ACC_EDIT_PAGE_TITLE', 'Edit Corporate Account');
}
if (!defined('CMS_CORP_ACC_VIEW_PAGE_SLUG')) {
    define('CMS_CORP_ACC_VIEW_PAGE_SLUG', 'view-corp-account');
}
if (!defined('CMS_CORP_ACC_VIEW_PAGE_TITLE')) {
    define('CMS_CORP_ACC_VIEW_PAGE_TITLE', 'View Corporate Account');
}

// Assignment Pages
if (!defined('CMS_EMP_CORP_ASSIGN_PAGE_SLUG')) {
    define('CMS_EMP_CORP_ASSIGN_PAGE_SLUG', 'emp-corp-assign');
}
if (!defined('CMS_EMP_CORP_ASSIGN_PAGE_TITLE')) {
    define('CMS_EMP_CORP_ASSIGN_PAGE_TITLE', 'Employee Corporate Assignment');
}

// Employee Dashboard & Shift Management Pages
if (!defined('CMS_EMPLOYEE_DASHBOARD_PAGE_SLUG')) {
    define('CMS_EMPLOYEE_DASHBOARD_PAGE_SLUG', 'employee-dashboard');
}
if (!defined('CMS_EMPLOYEE_DASHBOARD_PAGE_TITLE')) {
    define('CMS_EMPLOYEE_DASHBOARD_PAGE_TITLE', 'Employee Dashboard');
}
if (!defined('CMS_EMP_SHIFT_HISTORY_PAGE_SLUG')) {
    define('CMS_EMP_SHIFT_HISTORY_PAGE_SLUG', 'shift-history');
}
if (!defined('CMS_EMP_SHIFT_HISTORY_PAGE_TITLE')) {
    define('CMS_EMP_SHIFT_HISTORY_PAGE_TITLE', 'Shift History');
}
if (!defined('CMS_SINGLE_EMP_SHIFT_HISTORY_PAGE_SLUG')) {
    define('CMS_SINGLE_EMP_SHIFT_HISTORY_PAGE_SLUG', 'employee-shift-history');
}
if (!defined('CMS_SINGLE_EMP_SHIFT_HISTORY_PAGE_TITLE')) {
    define('CMS_SINGLE_EMP_SHIFT_HISTORY_PAGE_TITLE', 'Employee Shift History');
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
if (!defined('CMS_CORP_ID_PARAM')) {
    define('CMS_CORP_ID_PARAM', 'corp_id');
}
if (!defined('CMS_ASSIGNMENT_ID_PARAM')) {
    define('CMS_ASSIGNMENT_ID_PARAM', 'assignment_id');
}
if (!defined('CMS_SHIFT_ID_PARAM')) {
    define('CMS_SHIFT_ID_PARAM', 'shift_id');
}
if (!defined('CMS_USERNAME_PARAM')) {
    define('CMS_USERNAME_PARAM', 'username');
}
if (!defined('CMS_ACTION_PARAM')) {
    define('CMS_ACTION_PARAM', 'action');
}
if (!defined('CMS_DATE_FROM_PARAM')) {
    define('CMS_DATE_FROM_PARAM', 'date_from');
}
if (!defined('CMS_DATE_TO_PARAM')) {
    define('CMS_DATE_TO_PARAM', 'date_to');
}
?>