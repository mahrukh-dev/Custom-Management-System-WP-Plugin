<?php
/**
 * CMS Employee Dashboard Shortcode
 * Complete dashboard for employees to manage shifts and requests
 * Updated with automatic request creation and details field
 * Hours calculated using counted hours and minutes
 * Shows corporate client information for each shift
 * Shows non-editable shift management table
 * 
 * Usage: [cms_employee_dashboard]
 * Usage: [cms_employee_dashboard title="Employee Dashboard"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define shortcode slug
if (!defined('CMS_EMPLOYEE_DASHBOARD_SHORTCODE')) {
    define('CMS_EMPLOYEE_DASHBOARD_SHORTCODE', 'cms_employee_dashboard');
}

/**
 * Employee Dashboard Shortcode
 */
function cms_employee_dashboard_shortcode($atts) {
    global $wpdb;
    
    // Parse attributes
    $atts = shortcode_atts(
        array(
            'title' => 'Employee Dashboard',
            'welcome_message' => 'Welcome to your dashboard',
            'class' => '',
            'show_history' => 'yes',
            'show_schedule' => 'yes',
            'schedule_days' => '7', // Number of days to show in schedule
            'history_limit' => 10,
            'timezone' => 'Asia/Karachi',
            'grace_period' => '15' // minutes of grace period before creating request
        ),
        $atts,
        'cms_employee_dashboard'
    );
    
    // Check if user is logged in
    if (!cms_is_user_logged_in()) {
        return '<div class="cms-dash-message error">Please login to view your dashboard.</div>';
    }
    
    // Get current user from session
    $current_user = cms_get_current_user();
    
    // Get employee details from database
    $current_employee = cms_get_employee($current_user['username']);
    
    if (!$current_employee) {
        return '<div class="cms-dash-message error">Employee record not found.</div>';
    }
    
    // Set timezone to Asia/Karachi
    date_default_timezone_set('Asia/Karachi');
    
    // Handle form submissions directly
    $message = cms_handle_dashboard_actions($current_employee->username, $atts['grace_period']);
    
    // Get today's date in Karachi timezone
    $today = date('Y-m-d');
    
    // Get all shifts assigned for today with corporate client info
    $today_shifts = get_employee_today_shift_assignments_with_corp($current_employee->username);
    
    // Get active shift (if any)
    $active_shift = get_employee_active_shift($current_employee->username);
    
    // Get today's shift history records
    $today_history = get_employee_today_shift_history($current_employee->username);
    
    // Get pending requests where details are empty
    $pending_requests_without_details = get_employee_requests_without_details($current_employee->username);
    
    // Get all requests for display
    $all_requests = get_employee_requests_from_db($current_employee->username);
    
    // Get upcoming shifts schedule
    $upcoming_shifts = get_employee_upcoming_shifts($current_employee->username, intval($atts['schedule_days']));
    
    // Get statistics using counted hours
    $weekly_hours = get_employee_weekly_counted_hours($current_employee->username);
    $monthly_hours = get_employee_monthly_counted_hours($current_employee->username);
    $days_worked = get_employee_days_worked_month($current_employee->username);
    $pending_count = count(get_employee_requests_from_db($current_employee->username, 'pending'));
    
    ob_start();
    ?>
    
    <style>
    /* Employee Dashboard Styles - Blue Theme */
    :root {
        --dash-primary: #3498db;
        --dash-primary-dark: #2980b9;
        --dash-primary-light: #5dade2;
        --dash-secondary: #2ecc71;
        --dash-secondary-dark: #27ae60;
        --dash-danger: #e74c3c;
        --dash-warning: #f39c12;
        --dash-info: #3498db;
        --dash-gray: #95a5a6;
        --dash-gray-dark: #7f8c8d;
        --dash-request-bg: #fef5e7;
        --dash-request-border: #f39c12;
        --dash-details-bg: #fff3e0;
        --dash-details-border: #ffb74d;
        --dash-corp-bg: #e8f0fe;
        --dash-corp-border: #bbd6fe;
        --dash-schedule-bg: #f8fafc;
        --dash-schedule-border: #e2e8f0;
    }
    
    .cms-dash-container {
        max-width: 1200px;
        margin: 30px auto;
        padding: 30px;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(52,152,219,0.08);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        border-top: 5px solid var(--dash-primary);
    }
    
    .cms-dash-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .cms-dash-title-section {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .cms-dash-avatar {
        width: 70px;
        height: 70px;
        background: linear-gradient(145deg, var(--dash-primary), var(--dash-primary-dark));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 30px;
        font-weight: 700;
        color: white;
    }
    
    .cms-dash-welcome {
        display: flex;
        flex-direction: column;
    }
    
    .cms-dash-title {
        margin: 0 0 5px 0;
        font-size: 28px;
        font-weight: 700;
        color: var(--dash-primary-dark);
    }
    
    .cms-dash-welcome-message {
        margin: 0;
        font-size: 16px;
        color: #6c7a89;
    }
    
    .cms-dash-date-time {
        background: #f0f8ff;
        padding: 15px 25px;
        border-radius: 50px;
        border: 2px solid var(--dash-primary-light);
        text-align: center;
    }
    
    .cms-dash-current-date {
        font-size: 18px;
        font-weight: 600;
        color: var(--dash-primary-dark);
        margin-bottom: 5px;
    }
    
    .cms-dash-current-time {
        font-size: 32px;
        font-weight: 700;
        color: var(--dash-primary);
        font-family: monospace;
        line-height: 1.2;
    }
    
    .cms-dash-current-time small {
        font-size: 14px;
        font-weight: 400;
        color: #6c7a89;
    }
    
    /* Schedule Section */
    .cms-dash-schedule-section {
        margin-bottom: 40px;
        background: var(--dash-schedule-bg);
        border: 2px solid var(--dash-schedule-border);
        border-radius: 16px;
        padding: 20px;
    }
    
    .cms-dash-schedule-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--dash-primary-dark);
        margin: 0 0 20px 0;
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--dash-primary-light);
    }
    
    .cms-dash-schedule-title:before {
        content: '📅';
        font-size: 24px;
    }
    
    .cms-dash-schedule-table-container {
        overflow-x: auto;
        border-radius: 12px;
    }
    
    .cms-dash-schedule-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 12px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .cms-dash-schedule-table th {
        background: var(--dash-primary);
        color: white;
        font-weight: 600;
        padding: 15px 12px;
        text-align: left;
        font-size: 14px;
    }
    
    .cms-dash-schedule-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
        color: #2c3e50;
        font-size: 14px;
    }
    
    .cms-dash-schedule-table tr:last-child td {
        border-bottom: none;
    }
    
    .cms-dash-schedule-table tr:hover {
        background: #f8fafc;
    }
    
    .cms-dash-schedule-date {
        font-weight: 600;
        color: var(--dash-primary-dark);
    }
    
    .cms-dash-schedule-today {
        background: #fff3cd !important;
        font-weight: 600;
    }
    
    .cms-dash-schedule-today td:first-child {
        border-left: 4px solid var(--dash-warning);
    }
    
    .cms-dash-schedule-corp {
        background: var(--dash-corp-bg);
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        color: #1a56db;
        display: inline-block;
    }
    
    .cms-dash-schedule-time {
        font-family: monospace;
        font-weight: 600;
        color: var(--dash-primary-dark);
    }
    
    .cms-dash-schedule-status-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .cms-dash-schedule-status-badge.upcoming {
        background: #e2e8f0;
        color: #4a5568;
    }
    
    .cms-dash-schedule-status-badge.today {
        background: #fff3cd;
        color: #856404;
    }
    
    .cms-dash-schedule-status-badge.past {
        background: #f8f9fa;
        color: #6c7a89;
    }
    
    /* Multiple Shifts Card */
    .cms-dash-shifts-container {
        margin-bottom: 30px;
    }
    
    .cms-dash-shifts-title {
        font-size: 18px;
        font-weight: 600;
        color: var(--dash-primary-dark);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cms-dash-shifts-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 20px;
    }
    
    .cms-dash-shift-card {
        background: linear-gradient(145deg, #f0f8ff, #ffffff);
        border: 2px solid var(--dash-primary-light);
        border-radius: 16px;
        padding: 20px;
        transition: all 0.3s ease;
    }
    
    .cms-dash-shift-card.active {
        border-color: var(--dash-secondary);
        background: linear-gradient(145deg, #f0fff4, #ffffff);
        box-shadow: 0 5px 20px rgba(46,204,113,0.2);
    }
    
    .cms-dash-shift-card.completed {
        opacity: 0.9;
        background: #f8fafc;
    }
    
    .cms-dash-shift-card.has-request {
        border-color: var(--dash-warning);
        background: #fff9f0;
    }
    
    .cms-dash-shift-card.has-corp {
        border-left: 6px solid var(--dash-info);
    }
    
    .cms-dash-shift-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .cms-dash-shift-number {
        font-weight: 700;
        color: var(--dash-primary-dark);
        background: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 13px;
    }
    
    .cms-dash-shift-status {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .cms-dash-shift-status.pending {
        background: #fef5e7;
        color: #e67e22;
    }
    
    .cms-dash-shift-status.active {
        background: #d4edda;
        color: #155724;
    }
    
    .cms-dash-shift-status.completed {
        background: #cce5ff;
        color: #004085;
    }
    
    .cms-dash-shift-request-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        margin-left: 8px;
    }
    
    .cms-dash-shift-request-badge.late_login {
        background: #ffebee;
        color: #c62828;
    }
    
    .cms-dash-shift-request-badge.early_login {
        background: #fff3e0;
        color: #ef6c00;
    }
    
    .cms-dash-shift-request-badge.late_logout {
        background: #e8eaf6;
        color: #283593;
    }
    
    .cms-dash-shift-request-badge.early_logout {
        background: #f3e5f5;
        color: #7b1fa2;
    }
    
    .cms-dash-shift-corp-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 600;
        background: var(--dash-corp-bg);
        color: #1a56db;
        border: 1px solid var(--dash-corp-border);
        margin-right: 8px;
    }
    
    .cms-dash-shift-times {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 15px;
    }
    
    .cms-dash-shift-time-box {
        text-align: center;
        flex: 1;
    }
    
    .cms-dash-shift-time-label {
        font-size: 11px;
        color: #6c7a89;
        margin-bottom: 5px;
    }
    
    .cms-dash-shift-time-value {
        font-size: 18px;
        font-weight: 700;
        color: var(--dash-primary-dark);
        font-family: monospace;
    }
    
    .cms-dash-shift-arrow {
        font-size: 20px;
        color: var(--dash-primary-light);
        margin: 0 10px;
    }
    
    .cms-dash-shift-corp-info {
        background: var(--dash-corp-bg);
        border: 1px solid var(--dash-corp-border);
        border-radius: 12px;
        padding: 12px;
        margin: 15px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cms-dash-shift-corp-icon {
        width: 40px;
        height: 40px;
        background: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        color: var(--dash-info);
    }
    
    .cms-dash-shift-corp-details {
        flex: 1;
    }
    
    .cms-dash-shift-corp-name {
        font-weight: 700;
        color: #1e3a8a;
        font-size: 14px;
        margin-bottom: 3px;
    }
    
    .cms-dash-shift-corp-contact {
        font-size: 11px;
        color: #4b5563;
        display: flex;
        gap: 15px;
    }
    
    .cms-dash-shift-actions {
        display: flex;
        gap: 10px;
        margin-top: 15px;
    }
    
    .cms-dash-shift-btn {
        flex: 1;
        padding: 12px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-align: center;
        text-decoration: none;
        display: inline-block;
    }
    
    .cms-dash-shift-btn.login {
        background: var(--dash-secondary);
        color: white;
    }
    
    .cms-dash-shift-btn.login:hover:not(:disabled) {
        background: var(--dash-secondary-dark);
        transform: translateY(-1px);
    }
    
    .cms-dash-shift-btn.logout {
        background: var(--dash-danger);
        color: white;
    }
    
    .cms-dash-shift-btn.logout:hover:not(:disabled) {
        background: #c0392b;
        transform: translateY(-1px);
    }
    
    .cms-dash-shift-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .cms-dash-shift-info {
        font-size: 12px;
        color: #6c7a89;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px dashed #e2e8f0;
    }
    
    .cms-dash-shift-info-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 5px;
    }
    
    .cms-dash-shift-info-label {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .cms-dash-shift-deviation {
        font-size: 11px;
        padding: 4px 8px;
        border-radius: 12px;
        background: #fff3e0;
        color: #e65100;
        margin-top: 5px;
        text-align: center;
    }
    
    /* Stats Cards */
    .cms-dash-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .cms-dash-stat-card {
        background: #f0f8ff;
        border: 2px solid var(--dash-primary-light);
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .cms-dash-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(52,152,219,0.1);
    }
    
    .cms-dash-stat-value {
        font-size: 32px;
        font-weight: 700;
        color: var(--dash-primary-dark);
        line-height: 1.2;
        margin-bottom: 5px;
    }
    
    .cms-dash-stat-label {
        font-size: 13px;
        color: #6c7a89;
        font-weight: 500;
    }
    
    /* Pending Details Section */
    .cms-dash-pending-details {
        margin-bottom: 30px;
        background: var(--dash-details-bg);
        border: 2px solid var(--dash-details-border);
        border-radius: 16px;
        padding: 20px;
    }
    
    .cms-dash-pending-title {
        font-size: 18px;
        font-weight: 600;
        color: #e65100;
        margin: 0 0 15px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cms-dash-pending-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 15px;
    }
    
    .cms-dash-pending-item {
        background: white;
        border-radius: 12px;
        padding: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }
    
    .cms-dash-pending-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        padding-bottom: 8px;
        border-bottom: 1px solid #ffe0b2;
    }
    
    .cms-dash-pending-type {
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
        font-size: 12px;
    }
    
    .cms-dash-pending-type.late_login {
        background: #ffebee;
        color: #c62828;
    }
    
    .cms-dash-pending-type.early_login {
        background: #fff3e0;
        color: #ef6c00;
    }
    
    .cms-dash-pending-type.late_logout {
        background: #e8eaf6;
        color: #283593;
    }
    
    .cms-dash-pending-type.early_logout {
        background: #f3e5f5;
        color: #7b1fa2;
    }
    
    .cms-dash-pending-date {
        font-size: 12px;
        color: #6c7a89;
    }
    
    .cms-dash-pending-details-form {
        margin-top: 10px;
    }
    
    .cms-dash-pending-textarea {
        width: 100%;
        padding: 10px;
        border: 2px solid #ffe0b2;
        border-radius: 8px;
        font-size: 13px;
        resize: vertical;
        margin-bottom: 10px;
    }
    
    .cms-dash-pending-textarea:focus {
        outline: none;
        border-color: var(--dash-warning);
    }
    
    .cms-dash-pending-submit {
        background: var(--dash-warning);
        color: white;
        border: none;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .cms-dash-pending-submit:hover {
        background: #e67e22;
    }
    
    /* Requests Section */
    .cms-dash-requests-section {
        margin-top: 40px;
        background: #fef5e7;
        border-radius: 16px;
        padding: 25px;
        border: 2px solid var(--dash-request-border);
    }
    
    .cms-dash-requests-title {
        font-size: 20px;
        font-weight: 700;
        color: #e67e22;
        margin: 0 0 20px 0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cms-requests-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 12px;
        overflow: hidden;
    }
    
    .cms-requests-table th {
        background: #f39c12;
        color: white;
        font-weight: 600;
        padding: 12px;
        text-align: left;
    }
    
    .cms-requests-table td {
        padding: 12px;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .cms-requests-table tr:last-child td {
        border-bottom: none;
    }
    
    .cms-request-status {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .cms-request-status.pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .cms-request-status.approved {
        background: #d4edda;
        color: #155724;
    }
    
    .cms-request-status.rejected {
        background: #f8d7da;
        color: #721c24;
    }
    
    .cms-request-type-badge {
        display: inline-block;
        padding: 3px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .cms-request-type-badge.late_login {
        background: #ffebee;
        color: #c62828;
    }
    
    .cms-request-type-badge.early_login {
        background: #fff3e0;
        color: #ef6c00;
    }
    
    .cms-request-type-badge.late_logout {
        background: #e8eaf6;
        color: #283593;
    }
    
    .cms-request-type-badge.early_logout {
        background: #f3e5f5;
        color: #7b1fa2;
    }
    
    /* History Section */
    .cms-dash-history-section {
        margin-top: 40px;
    }
    
    .cms-dash-section-title {
        font-size: 22px;
        font-weight: 700;
        color: var(--dash-primary-dark);
        margin: 0 0 20px 0;
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 15px;
        border-bottom: 3px solid var(--dash-primary-light);
    }
    
    .cms-dash-section-title:before {
        content: '📋';
        font-size: 24px;
    }
    
    .cms-dash-history-table {
        width: 100%;
        border-collapse: collapse;
        background: white;
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
    }
    
    .cms-dash-history-table th {
        background: #f0f8ff;
        color: var(--dash-primary-dark);
        font-weight: 600;
        padding: 15px;
        text-align: left;
        border-bottom: 2px solid var(--dash-primary-light);
    }
    
    .cms-dash-history-table td {
        padding: 15px;
        border-bottom: 1px solid #eef2f6;
        color: #2c3e50;
    }
    
    .cms-dash-history-table tr:last-child td {
        border-bottom: none;
    }
    
    .cms-dash-history-table tr:hover {
        background: #f8fafc;
    }
    
    .cms-dash-hours-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
    }
    
    .cms-dash-hours-badge.actual {
        background: #d4edda;
        color: #155724;
    }
    
    .cms-dash-hours-badge.counted {
        background: #cce5ff;
        color: #004085;
    }
    
    .cms-dash-status-badge {
        display: inline-block;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .cms-dash-status-badge.active {
        background: #d4edda;
        color: #155724;
    }
    
    .cms-dash-status-badge.completed {
        background: #cce5ff;
        color: #004085;
    }
    
    .cms-dash-status-badge.missed {
        background: #f8d7da;
        color: #721c24;
    }
    
    /* Message Box */
    .cms-dash-message {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .cms-dash-message.success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .cms-dash-message.success:before {
        content: '✓';
        font-size: 20px;
        font-weight: bold;
    }
    
    .cms-dash-message.error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .cms-dash-message.error:before {
        content: '⚠';
        font-size: 20px;
    }
    
    .cms-dash-message.warning {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
    
    .cms-dash-message.warning:before {
        content: '⚠';
        font-size: 20px;
    }
    
    .cms-dash-message.info {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }
    
    .cms-dash-message.info:before {
        content: 'ℹ';
        font-size: 20px;
    }
    
    /* Loading State */
    .cms-dash-btn.loading {
        position: relative;
        padding-right: 60px;
    }
    
    .cms-dash-btn.loading:after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        border: 2px solid #ffffff;
        border-top-color: transparent;
        border-radius: 50%;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        animation: cms-dash-spin 1s linear infinite;
    }
    
    @keyframes cms-dash-spin {
        0% { transform: translateY(-50%) rotate(0deg); }
        100% { transform: translateY(-50%) rotate(360deg); }
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .cms-dash-header {
            flex-direction: column;
            text-align: center;
        }
        
        .cms-dash-title-section {
            flex-direction: column;
        }
        
        .cms-dash-shifts-grid,
        .cms-dash-pending-grid {
            grid-template-columns: 1fr;
        }
        
        .cms-dash-stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .cms-requests-table {
            font-size: 13px;
        }
        
        .cms-requests-table th,
        .cms-requests-table td {
            padding: 10px 8px;
        }
        
        .cms-dash-schedule-table th,
        .cms-dash-schedule-table td {
            padding: 10px 8px;
            font-size: 13px;
        }
    }
    
    @media (max-width: 480px) {
        .cms-dash-stats-grid {
            grid-template-columns: 1fr;
        }
        
        .cms-dash-shift-times {
            flex-direction: column;
            gap: 10px;
        }
        
        .cms-dash-shift-arrow {
            transform: rotate(90deg);
        }
        
        .cms-dash-shift-actions {
            flex-direction: column;
        }
        
        .cms-dash-shift-corp-info {
            flex-direction: column;
            text-align: center;
        }
    }
    </style>
    
    <div class="cms-dash-container <?php echo esc_attr($atts['class']); ?>" data-grace-period="<?php echo esc_attr($atts['grace_period']); ?>">
        
        <!-- Header with Live Clock -->
        <div class="cms-dash-header">
            <div class="cms-dash-title-section">
                <div class="cms-dash-avatar">
                    <?php echo strtoupper(substr($current_employee->name, 0, 1)); ?>
                </div>
                <div class="cms-dash-welcome">
                    <h1 class="cms-dash-title"><?php echo esc_html($atts['title']); ?></h1>
                    <p class="cms-dash-welcome-message">
                        <?php echo esc_html($atts['welcome_message']); ?>, <strong><?php echo esc_html($current_employee->name); ?></strong>
                    </p>
                    <p style="color: #6c7a89; font-size: 13px; margin: 5px 0 0 0;">
                        @<?php echo esc_html($current_employee->username); ?> • <?php echo esc_html($current_employee->position); ?> • <?php echo esc_html($current_employee->corp_team); ?>
                    </p>
                </div>
            </div>
            
            <div class="cms-dash-date-time" id="live-datetime">
                <div class="cms-dash-current-date" id="current-date"></div>
                <div class="cms-dash-current-time" id="current-time"></div>
            </div>
        </div>
        
        <?php 
        // Display message if any
        if ($message) {
            echo $message;
        }
        ?>
        
        <!-- Statistics Cards -->
        <div class="cms-dash-stats-grid">
            <div class="cms-dash-stat-card">
                <div class="cms-dash-stat-value"><?php echo esc_html($weekly_hours); ?></div>
                <div class="cms-dash-stat-label">Counted Hours This Week</div>
            </div>
            <div class="cms-dash-stat-card">
                <div class="cms-dash-stat-value"><?php echo esc_html($monthly_hours); ?></div>
                <div class="cms-dash-stat-label">Counted Hours This Month</div>
            </div>
            <div class="cms-dash-stat-card">
                <div class="cms-dash-stat-value"><?php echo esc_html($days_worked); ?></div>
                <div class="cms-dash-stat-label">Days Worked</div>
            </div>
            <div class="cms-dash-stat-card">
                <div class="cms-dash-stat-value"><?php echo esc_html($pending_count); ?></div>
                <div class="cms-dash-stat-label">Pending Requests</div>
            </div>
        </div>
        
        <?php if ($atts['show_schedule'] === 'yes' && !empty($upcoming_shifts)): ?>
        <!-- Upcoming Shifts Schedule (Non-editable) -->
        <div class="cms-dash-schedule-section">
            <h3 class="cms-dash-schedule-title">
                Upcoming Shift Schedule (Next <?php echo intval($atts['schedule_days']); ?> Days)
            </h3>
            
            <div class="cms-dash-schedule-table-container">
                <table class="cms-dash-schedule-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Day</th>
                            <th>Shift Time</th>
                            <th>Corporate Client</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $today_date = date('Y-m-d');
                        foreach ($upcoming_shifts as $shift): 
                            $is_today = ($shift['date'] === $today_date);
                            $row_class = $is_today ? 'cms-dash-schedule-today' : '';
                            $date_obj = new DateTime($shift['date']);
                        ?>
                        <tr class="<?php echo $row_class; ?>">
                            <td class="cms-dash-schedule-date">
                                <?php echo $date_obj->format('M d, Y'); ?>
                            </td>
                            <td>
                                <?php echo $date_obj->format('l'); ?>
                            </td>
                            <td class="cms-dash-schedule-time">
                                <?php echo esc_html($shift['shift_start_time']); ?> - <?php echo esc_html($shift['shift_end_time']); ?>
                            </td>
                            <td>
                                <?php if (!empty($shift['corp_company_name'])): ?>
                                    <span class="cms-dash-schedule-corp">
                                        🏢 <?php echo esc_html($shift['corp_company_name']); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #6c7a89;">—</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                if ($is_today) {
                                    $status_class = 'today';
                                    $status_text = 'Today';
                                } elseif (strtotime($shift['date']) > strtotime($today_date)) {
                                    $status_class = 'upcoming';
                                    $status_text = 'Upcoming';
                                } else {
                                    $status_class = 'past';
                                    $status_text = 'Past';
                                }
                                ?>
                                <span class="cms-dash-schedule-status-badge <?php echo $status_class; ?>">
                                    <?php echo $status_text; ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Pending Details Section -->
        <?php if (!empty($pending_requests_without_details)): ?>
        <div class="cms-dash-pending-details">
            <h3 class="cms-dash-pending-title">
                <span>📝</span> Requests Pending Details (<?php echo count($pending_requests_without_details); ?>)
            </h3>
            
            <div class="cms-dash-pending-grid">
                <?php foreach ($pending_requests_without_details as $request): ?>
                <div class="cms-dash-pending-item">
                    <div class="cms-dash-pending-header">
                        <span class="cms-dash-pending-type <?php echo esc_attr($request['type']); ?>">
                            <?php 
                            $type_labels = [
                                'late_login' => 'Late Login',
                                'early_login' => 'Early Login',
                                'late_logout' => 'Late Logout',
                                'early_logout' => 'Early Logout'
                            ];
                            echo esc_html($type_labels[$request['type']] ?? ucfirst(str_replace('_', ' ', $request['type'])));
                            ?>
                        </span>
                        <span class="cms-dash-pending-date"><?php echo date('M d, Y', strtotime($request['date'])); ?></span>
                    </div>
                    
                    <div style="font-size: 13px; margin-bottom: 10px; color: #2c3e50;">
                        <strong>Deviation:</strong> <?php echo abs($request['deviation_minutes']); ?> minutes 
                        (Scheduled: <?php echo date('h:i A', strtotime($request['scheduled_time'])); ?>, 
                        Actual: <?php echo date('h:i A', strtotime($request['actual_time'])); ?>)
                    </div>
                    
                    <form method="post" action="" class="cms-dash-pending-details-form">
                        <?php wp_nonce_field('cms_request_details_action', 'cms_details_nonce'); ?>
                        <input type="hidden" name="cms_action" value="update_request_details">
                        <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                        
                        <textarea name="request_details" class="cms-dash-pending-textarea" 
                                  placeholder="Please provide reason for this deviation..." 
                                  rows="3"><?php echo esc_textarea($request['details'] ?? ''); ?></textarea>
                        
                        <button type="submit" name="cms_details_submit" class="cms-dash-pending-submit">
                            Update Details
                        </button>
                    </form>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Today's Shifts -->
        <?php if (!empty($today_shifts)): ?>
        <div class="cms-dash-shifts-container">
            <h3 class="cms-dash-shifts-title">
                <span>⏰</span> Today's Shifts (<?php echo count($today_shifts); ?>)
            </h3>
            
            <div class="cms-dash-shifts-grid">
                <?php foreach ($today_shifts as $index => $shift): 
                    $shift_history = isset($today_history[$shift['id']]) ? $today_history[$shift['id']] : null;
                    $is_active = $active_shift && $active_shift['shift_management_id'] == $shift['id'];
                    $is_completed = $shift_history && $shift_history['actual_logout_time'] !== null;
                    $is_missed = $shift_history && $shift_history['status'] === 'missed';
                    
                    // Check if this shift has any requests
                    $shift_requests = get_shift_requests($shift_history['id'] ?? 0);
                    
                    $card_class = '';
                    if ($is_active) $card_class = 'active';
                    elseif ($is_completed) $card_class = 'completed';
                    elseif ($is_missed) $card_class = 'missed';
                    
                    if (!empty($shift_requests)) $card_class .= ' has-request';
                    if (!empty($shift['corp_username'])) $card_class .= ' has-corp';
                ?>
                <div class="cms-dash-shift-card <?php echo $card_class; ?>" id="shift-<?php echo esc_attr($shift['id']); ?>">
                    <div class="cms-dash-shift-header">
                        <span class="cms-dash-shift-number">Shift #<?php echo $index + 1; ?></span>
                        <div>
                            <?php if (!empty($shift['corp_username'])): ?>
                            <span class="cms-dash-shift-corp-badge" title="Corporate Client">
                                🏢 Corp
                            </span>
                            <?php endif; ?>
                            <span class="cms-dash-shift-status <?php echo $is_active ? 'active' : ($is_completed ? 'completed' : 'pending'); ?>">
                                <?php 
                                if ($is_active) echo 'Active';
                                elseif ($is_completed) echo 'Completed';
                                elseif ($is_missed) echo 'Missed';
                                else echo 'Pending';
                                ?>
                            </span>
                            <?php foreach ($shift_requests as $req): ?>
                            <span class="cms-dash-shift-request-badge <?php echo esc_attr($req['type']); ?>" 
                                  title="<?php echo esc_attr($req['type']); ?>: <?php echo abs($req['deviation_minutes']); ?> min deviation">
                                <?php 
                                if ($req['type'] == 'late_login') echo '⏰ Late';
                                elseif ($req['type'] == 'early_login') echo '⏰ Early';
                                elseif ($req['type'] == 'late_logout') echo '⌛ Late';
                                elseif ($req['type'] == 'early_logout') echo '⌛ Early';
                                ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    
                    <div class="cms-dash-shift-times">
                        <div class="cms-dash-shift-time-box">
                            <div class="cms-dash-shift-time-label">Scheduled Start</div>
                            <div class="cms-dash-shift-time-value"><?php echo esc_html($shift['shift_start_time']); ?></div>
                        </div>
                        <div class="cms-dash-shift-arrow">→</div>
                        <div class="cms-dash-shift-time-box">
                            <div class="cms-dash-shift-time-label">Scheduled End</div>
                            <div class="cms-dash-shift-time-value"><?php echo esc_html($shift['shift_end_time']); ?></div>
                        </div>
                    </div>
                    
                    <!-- Corporate Client Information -->
                    <?php if (!empty($shift['corp_username'])): ?>
                    <div class="cms-dash-shift-corp-info">
                        <div class="cms-dash-shift-corp-icon">
                            🏢
                        </div>
                        <div class="cms-dash-shift-corp-details">
                            <div class="cms-dash-shift-corp-name">
                                <?php echo esc_html($shift['corp_company_name']); ?>
                            </div>
                            <div class="cms-dash-shift-corp-contact">
                                <span>📧 <?php echo esc_html($shift['corp_email']); ?></span>
                                <span>📞 <?php echo esc_html($shift['corp_phone']); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <?php if ($shift_history): ?>
                    <div class="cms-dash-shift-info">
                        <div class="cms-dash-shift-info-item">
                            <span class="cms-dash-shift-info-label">Actual Login:</span>
                            <span><?php echo $shift_history['actual_login_time'] ? date('h:i A', strtotime($shift_history['actual_login_time'])) : '--'; ?></span>
                        </div>
                        <div class="cms-dash-shift-info-item">
                            <span class="cms-dash-shift-info-label">Actual Logout:</span>
                            <span><?php echo $shift_history['actual_logout_time'] ? date('h:i A', strtotime($shift_history['actual_logout_time'])) : '--'; ?></span>
                        </div>
                        <?php if ($shift_history['actual_hours'] !== null): ?>
                        <div class="cms-dash-shift-info-item">
                            <span class="cms-dash-shift-info-label">Counted Hours:</span>
                            <span><?php echo intval($shift_history['counted_hours']); ?>h <?php echo intval($shift_history['counted_mins']); ?>m</span>
                        </div>
                        <?php endif; ?>
                        
                        <?php foreach ($shift_requests as $req): ?>
                        <div class="cms-dash-shift-deviation">
                            <?php 
                            $deviation = abs($req['deviation_minutes']);
                            if ($req['type'] == 'late_login') {
                                echo "⏰ Logged in {$deviation} minutes late";
                            } elseif ($req['type'] == 'early_login') {
                                echo "⏰ Logged in {$deviation} minutes early";
                            } elseif ($req['type'] == 'late_logout') {
                                echo "⌛ Logged out {$deviation} minutes late";
                            } elseif ($req['type'] == 'early_logout') {
                                echo "⌛ Logged out {$deviation} minutes early";
                            }
                            
                            if ($req['details']) {
                                echo '<br><small style="color: #2c3e50;">📝 ' . esc_html($req['details']) . '</small>';
                            } elseif ($req['status'] == 'pending') {
                                echo '<br><small style="color: #e65100;">⚠️ Please add details above</small>';
                            }
                            ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="cms-dash-shift-actions">
                        <?php if (!$is_active && !$is_completed && !$is_missed): ?>
                        <a href="?cms_action=login&shift_id=<?php echo $shift['id']; ?>&_wpnonce=<?php echo wp_create_nonce('cms_shift_action_' . $shift['id']); ?>" 
                           class="cms-dash-shift-btn login">
                            Start Shift
                        </a>
                        <?php elseif ($is_active && !$is_completed): ?>
                        <a href="?cms_action=logout&shift_id=<?php echo $shift['id']; ?>&_wpnonce=<?php echo wp_create_nonce('cms_shift_action_' . $shift['id']); ?>" 
                           class="cms-dash-shift-btn logout">
                            End Shift
                        </a>
                        <?php else: ?>
                        <button class="cms-dash-shift-btn" disabled>
                            <?php echo $is_completed ? 'Completed' : ($is_missed ? 'Missed' : 'Start Shift'); ?>
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php else: ?>
        <div style="text-align: center; padding: 40px; background: #f8fafc; border-radius: 16px; color: #6c7a89; margin-bottom: 30px;">
            <span style="font-size: 48px; display: block; margin-bottom: 15px;">⏰</span>
            No shifts assigned for today.
        </div>
        <?php endif; ?>
        
        <!-- All Requests Section -->
        <?php if (!empty($all_requests)): ?>
        <div class="cms-dash-requests-section">
            <h3 class="cms-dash-requests-title">
                <span>📋</span> My Requests
            </h3>
            
            <table class="cms-requests-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Deviation</th>
                        <th>Schedule vs Actual</th>
                        <th>Details</th>
                        <th>Status</th>
                        <th>Admin Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_requests as $request): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($request['date'])); ?></td>
                        <td>
                            <span class="cms-request-type-badge <?php echo esc_attr($request['type']); ?>">
                                <?php 
                                $type_labels = [
                                    'late_login' => 'Late Login',
                                    'early_login' => 'Early Login',
                                    'late_logout' => 'Late Logout',
                                    'early_logout' => 'Early Logout'
                                ];
                                echo esc_html($type_labels[$request['type']] ?? ucfirst(str_replace('_', ' ', $request['type'])));
                                ?>
                            </span>
                        </td>
                        <td><?php echo abs($request['deviation_minutes']); ?> min</td>
                        <td>
                            <?php echo date('h:i A', strtotime($request['scheduled_time'])); ?> vs 
                            <?php echo date('h:i A', strtotime($request['actual_time'])); ?>
                        </td>
                        <td>
                            <?php if ($request['details']): ?>
                                <span title="<?php echo esc_attr($request['details']); ?>">
                                    <?php echo esc_html(substr($request['details'], 0, 30)) . '...'; ?>
                                </span>
                            <?php else: ?>
                                <em style="color: #e65100;">Pending</em>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="cms-request-status <?php echo esc_attr($request['status']); ?>">
                                <?php echo esc_html(ucfirst($request['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html($request['admin_notes'] ?? '-'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <?php if ($atts['show_history'] === 'yes'): ?>
        <!-- Recent History -->
        <?php
        $recent_history = get_employee_recent_shift_history_with_corp($current_employee->username, 10);
        if (!empty($recent_history)):
        ?>
        <div class="cms-dash-history-section">
            <h2 class="cms-dash-section-title">Recent Shift History</h2>
            
            <table class="cms-dash-history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Corporate Client</th>
                        <th>Shift Time</th>
                        <th>Login</th>
                        <th>Logout</th>
                        <th>Counted Hours</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_history as $history): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($history['date'])); ?></td>
                        <td>
                            <?php if (!empty($history['corp_company_name'])): ?>
                                <span style="font-weight: 600; color: #1a56db;">
                                    🏢 <?php echo esc_html($history['corp_company_name']); ?>
                                </span>
                            <?php else: ?>
                                <span style="color: #6c7a89;">—</span>
                            <?php endif; ?>
                        </td>
                        <td><?php echo esc_html($history['shift_start_time']); ?> - <?php echo esc_html($history['shift_end_time']); ?></td>
                        <td><?php echo $history['actual_login_time'] ? date('h:i A', strtotime($history['actual_login_time'])) : '--'; ?></td>
                        <td><?php echo $history['actual_logout_time'] ? date('h:i A', strtotime($history['actual_logout_time'])) : '--'; ?></td>
                        <td>
                            <?php if ($history['counted_hours'] !== null): ?>
                                <span class="cms-dash-hours-badge counted">
                                    <?php echo intval($history['counted_hours']); ?>h <?php echo intval($history['counted_mins']); ?>m
                                </span>
                            <?php else: ?>
                                --
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="cms-dash-status-badge <?php echo esc_attr($history['status']); ?>">
                                <?php echo esc_html(ucfirst($history['status'])); ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
    
    <script>
    // Live clock update
    function updateClock() {
        var now = new Date();
        var options = { 
            timeZone: 'Asia/Karachi',
            weekday: 'long', 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        };
        var dateStr = now.toLocaleDateString('en-US', options);
        var timeStr = now.toLocaleTimeString('en-US', { 
            timeZone: 'Asia/Karachi',
            hour12: true, 
            hour: '2-digit', 
            minute: '2-digit', 
            second: '2-digit' 
        });
        
        document.getElementById('current-date').textContent = dateStr;
        document.getElementById('current-time').textContent = timeStr;
    }
    
    updateClock();
    setInterval(updateClock, 1000);
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_employee_dashboard', 'cms_employee_dashboard_shortcode');
add_shortcode(CMS_EMPLOYEE_DASHBOARD_SHORTCODE, 'cms_employee_dashboard_shortcode');

/**
 * Handle dashboard actions directly
 */
function cms_handle_dashboard_actions($username, $grace_period = 15) {
    global $wpdb;
    
    // Check if this is a form submission
    if (!isset($_GET['cms_action']) && !isset($_POST['cms_action'])) {
        return '';
    }
    
    // Handle shift actions via GET
    if (isset($_GET['cms_action']) && in_array($_GET['cms_action'], ['login', 'logout'])) {
        $action = $_GET['cms_action'];
        $shift_id = isset($_GET['shift_id']) ? intval($_GET['shift_id']) : 0;
        
        // Verify nonce
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'cms_shift_action_' . $shift_id)) {
            return '<div class="cms-dash-message error">Security check failed.</div>';
        }
        
        // Get current time in Karachi timezone
        $karachi_timezone = new DateTimeZone('Asia/Karachi');
        $now = new DateTime('now', $karachi_timezone);
        $current_time_mysql = $now->format('Y-m-d H:i:s');
        $current_hour = $now->format('H:i:s');
        $current_date = $now->format('Y-m-d');
        
        $table_history = $wpdb->prefix . 'cms_shift_history';
        $table_shifts = $wpdb->prefix . 'cms_shift_management';
        $table_requests = $wpdb->prefix . 'cms_requests';
        
        // Verify shift assignment exists
        $shift = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_shifts WHERE id = %d AND emp_username = %s AND date = %s",
            $shift_id,
            $username,
            $current_date
        ));
        
        if (!$shift) {
            return '<div class="cms-dash-message error">Shift assignment not found.</div>';
        }
        
        if ($action === 'login') {
            // Check if already logged in for this shift
            $existing = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_history WHERE shift_management_id = %d",
                $shift_id
            ));
            
            if ($existing && $existing->actual_logout_time === null) {
                return '<div class="cms-dash-message warning">You are already logged in for this shift.</div>';
            }
            
            if ($existing && $existing->actual_logout_time !== null) {
                return '<div class="cms-dash-message warning">This shift has already been completed.</div>';
            }
            
            // Insert shift history
            $result = $wpdb->insert(
                $table_history,
                array(
                    'shift_management_id' => $shift_id,
                    'username' => $username,
                    'date' => $current_date,
                    'shift_start_time' => $shift->shift_start_time,
                    'shift_end_time' => $shift->shift_end_time,
                    'actual_login_time' => $current_hour,
                    'counted_login_time' => $current_hour,
                    'status' => 'active'
                ),
                array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
            );
            
            if (!$result) {
                return '<div class="cms-dash-message error">Failed to start shift. Please try again.</div>';
            }
            
            $history_id = $wpdb->insert_id;
            
            // Check for early or late login
            $scheduled_timestamp = strtotime($shift->shift_start_time);
            $actual_timestamp = strtotime($current_hour);
            $minutes_diff = ($actual_timestamp - $scheduled_timestamp) / 60;
            
            // Create request if deviation exceeds grace period
            if (abs($minutes_diff) > $grace_period) {
                $request_type = ($minutes_diff > 0) ? 'late_login' : 'early_login';
                $deviation_minutes = round(abs($minutes_diff));
                
                $wpdb->insert(
                    $table_requests,
                    array(
                        'shift_history_id' => $history_id,
                        'username' => $username,
                        'type' => $request_type,
                        'request' => ucfirst(str_replace('_', ' ', $request_type)) . ' - ' . $deviation_minutes . ' minutes',
                        'date' => $current_date,
                        'deviation_minutes' => $deviation_minutes,
                        'scheduled_time' => $shift->shift_start_time,
                        'actual_time' => $current_hour,
                        'details' => null,
                        'status' => 'pending'
                    ),
                    array('%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s')
                );
                
                $message = ($minutes_diff > 0) 
                    ? "You logged in {$deviation_minutes} minutes late. A request has been created. Please add details in the section above."
                    : "You logged in " . abs($deviation_minutes) . " minutes early. A request has been created. Please add details in the section above.";
                
                return '<div class="cms-dash-message warning">' . $message . '</div>';
            }
            
            return '<div class="cms-dash-message success">Shift started successfully at ' . $now->format('h:i A') . '! Welcome to work.</div>';
            
        } elseif ($action === 'logout') {
            
            // Get current shift history
            $history = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table_history WHERE shift_management_id = %d",
                $shift_id
            ));
            
            if (!$history) {
                return '<div class="cms-dash-message warning">No active shift found to logout.</div>';
            }
            
            if ($history->actual_logout_time !== null) {
                return '<div class="cms-dash-message warning">This shift has already been completed.</div>';
            }
            
            // Calculate actual hours worked
            $login_time = strtotime($history->actual_login_time);
            $logout_time = strtotime($current_hour);
            $actual_seconds = $logout_time - $login_time;
            $actual_hours = floor($actual_seconds / 3600);
            $actual_mins = floor(($actual_seconds % 3600) / 60);
            
            // Update shift history with counted hours = actual hours (for now)
            $result = $wpdb->update(
                $table_history,
                array(
                    'actual_logout_time' => $current_hour,
                    'actual_hours' => $actual_hours,
                    'actual_mins' => $actual_mins,
                    'counted_logout_time' => $current_hour,
                    'counted_hours' => $actual_hours,
                    'counted_mins' => $actual_mins,
                    'status' => 'completed'
                ),
                array('id' => $history->id),
                array('%s', '%d', '%d', '%s', '%d', '%d', '%s'),
                array('%d')
            );
            
            if (!$result) {
                return '<div class="cms-dash-message error">Failed to end shift. Please try again.</div>';
            }
            
            // Check for early or late logout
            $scheduled_timestamp = strtotime($shift->shift_end_time);
            $actual_timestamp = strtotime($current_hour);
            $minutes_diff = ($actual_timestamp - $scheduled_timestamp) / 60;
            
            // Create request if deviation exceeds grace period
            if (abs($minutes_diff) > $grace_period) {
                $request_type = ($minutes_diff > 0) ? 'late_logout' : 'early_logout';
                $deviation_minutes = round(abs($minutes_diff));
                
                $wpdb->insert(
                    $table_requests,
                    array(
                        'shift_history_id' => $history->id,
                        'username' => $username,
                        'type' => $request_type,
                        'request' => ucfirst(str_replace('_', ' ', $request_type)) . ' - ' . $deviation_minutes . ' minutes',
                        'date' => $current_date,
                        'deviation_minutes' => $deviation_minutes,
                        'scheduled_time' => $shift->shift_end_time,
                        'actual_time' => $current_hour,
                        'details' => null,
                        'status' => 'pending'
                    ),
                    array('%d', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s')
                );
                
                $message = ($minutes_diff > 0) 
                    ? "You logged out {$deviation_minutes} minutes late. A request has been created. Please add details in the section above."
                    : "You logged out " . abs($deviation_minutes) . " minutes early. A request has been created. Please add details in the section above.";
                
                // Trigger salary calculation if needed
                if (function_exists('cms_generate_employee_salary')) {
                    $current_month = date('Y-m-01');
                    cms_generate_employee_salary($username, $current_month);
                }
                
                return '<div class="cms-dash-message warning">' . $message . '</div>';
            }
            
            // Trigger salary calculation if needed
            if (function_exists('cms_generate_employee_salary')) {
                $current_month = date('Y-m-01');
                cms_generate_employee_salary($username, $current_month);
            }
            
            return '<div class="cms-dash-message success">Shift ended successfully at ' . $now->format('h:i A') . '! Have a good day.</div>';
        }
    }
    
    // Handle updating request details via POST
    if (isset($_POST['cms_action']) && $_POST['cms_action'] === 'update_request_details' && isset($_POST['cms_details_submit'])) {
        
        if (!isset($_POST['cms_details_nonce']) || !wp_verify_nonce($_POST['cms_details_nonce'], 'cms_request_details_action')) {
            return '<div class="cms-dash-message error">Security check failed.</div>';
        }
        
        $request_id = intval($_POST['request_id']);
        $details = sanitize_textarea_field($_POST['request_details']);
        
        // Get current time in Karachi timezone for logging
        $karachi_timezone = new DateTimeZone('Asia/Karachi');
        $now = new DateTime('now', $karachi_timezone);
        
        global $wpdb;
        $table = $wpdb->prefix . 'cms_requests';
        
        // First verify the request belongs to this user
        $request = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $request_id
        ));
        
        if (!$request) {
            return '<div class="cms-dash-message error">Request not found.</div>';
        }
        
        if ($request->username !== $username) {
            return '<div class="cms-dash-message error">You do not have permission to update this request.</div>';
        }
        
        // Update the details
        $result = $wpdb->update(
            $table,
            array(
                'details' => $details,
                'updated_at' => $now->format('Y-m-d H:i:s')
            ),
            array('id' => $request_id),
            array('%s', '%s'),
            array('%d')
        );
        
        if ($result !== false) {
            return '<div class="cms-dash-message success">Request details updated successfully.</div>';
        } else {
            $error = $wpdb->last_error;
            error_log("CMS Details Update Error: " . $error);
            return '<div class="cms-dash-message error">Failed to update details. Database error: ' . $error . '</div>';
        }
    }
    
    return '';
}

/**
 * Get employee upcoming shifts (non-editable schedule view)
 */
function get_employee_upcoming_shifts($username, $days = 7) {
    global $wpdb;
    
    $today = date('Y-m-d');
    $end_date = date('Y-m-d', strtotime("+{$days} days"));
    
    $table_shifts = $wpdb->prefix . 'cms_shift_management';
    $table_corp = $wpdb->prefix . 'cms_corp_acc';
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT s.*, 
                c.company_name as corp_company_name,
                c.name as corp_contact_name,
                c.email as corp_email,
                c.phone_no as corp_phone
         FROM $table_shifts s
         LEFT JOIN $table_corp c ON s.corp_acc_username = c.username
         WHERE s.emp_username = %s 
         AND s.date BETWEEN %s AND %s
         ORDER BY s.date ASC, s.shift_start_time ASC",
        $username,
        $today,
        $end_date
    ), ARRAY_A);
}

/**
 * Get employee today's shift assignments with corporate client info
 */
function get_employee_today_shift_assignments_with_corp($username) {
    global $wpdb;
    
    // Get today's date in Karachi timezone
    $karachi_timezone = new DateTimeZone('Asia/Karachi');
    $now = new DateTime('now', $karachi_timezone);
    $today = $now->format('Y-m-d');
    
    $table_shifts = $wpdb->prefix . 'cms_shift_management';
    $table_corp = $wpdb->prefix . 'cms_corp_acc';
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT s.*, 
                c.company_name as corp_company_name,
                c.name as corp_contact_name,
                c.email as corp_email,
                c.phone_no as corp_phone,
                c.address as corp_address,
                c.website as corp_website
         FROM $table_shifts s
         LEFT JOIN $table_corp c ON s.corp_acc_username = c.username
         WHERE s.emp_username = %s AND s.date = %s
         ORDER BY s.shift_start_time ASC",
        $username,
        $today
    ), ARRAY_A);
}

/**
 * Get employee recent shift history with corporate client info
 */
function get_employee_recent_shift_history_with_corp($username, $limit = 10) {
    global $wpdb;
    
    $table_history = $wpdb->prefix . 'cms_shift_history';
    $table_shifts = $wpdb->prefix . 'cms_shift_management';
    $table_corp = $wpdb->prefix . 'cms_corp_acc';
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT sh.*, 
                sm.shift_start_time, 
                sm.shift_end_time,
                sm.corp_acc_username,
                c.company_name as corp_company_name,
                c.email as corp_email,
                c.phone_no as corp_phone
         FROM $table_history sh
         LEFT JOIN $table_shifts sm ON sh.shift_management_id = sm.id
         LEFT JOIN $table_corp c ON sm.corp_acc_username = c.username
         WHERE sh.username = %s
         ORDER BY sh.date DESC, sm.shift_start_time ASC
         LIMIT %d",
        $username,
        $limit
    ), ARRAY_A);
}

// ... rest of the functions (get_employee_today_shift_assignments, get_employee_active_shift, etc.) remain the same ...
/**
 * Get employee today's shift assignments from database
 */
function get_employee_today_shift_assignments($username) {
    global $wpdb;
    
    // Get today's date in Karachi timezone
    $karachi_timezone = new DateTimeZone('Asia/Karachi');
    $now = new DateTime('now', $karachi_timezone);
    $today = $now->format('Y-m-d');
    
    $table = $wpdb->prefix . 'cms_shift_management';
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table 
         WHERE emp_username = %s AND date = %s
         ORDER BY shift_start_time ASC",
        $username,
        $today
    ), ARRAY_A);
}

/**
 * Get employee active shift
 */
function get_employee_active_shift($username) {
    global $wpdb;
    
    // Get today's date in Karachi timezone
    $karachi_timezone = new DateTimeZone('Asia/Karachi');
    $now = new DateTime('now', $karachi_timezone);
    $today = $now->format('Y-m-d');
    
    $table = $wpdb->prefix . 'cms_shift_history';
    
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table 
         WHERE username = %s AND date = %s AND status = 'active'",
        $username,
        $today
    ), ARRAY_A);
}

/**
 * Get employee today's shift history indexed by shift_management_id
 */
function get_employee_today_shift_history($username) {
    global $wpdb;
    
    // Get today's date in Karachi timezone
    $karachi_timezone = new DateTimeZone('Asia/Karachi');
    $now = new DateTime('now', $karachi_timezone);
    $today = $now->format('Y-m-d');
    
    $table = $wpdb->prefix . 'cms_shift_history';
    
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table 
         WHERE username = %s AND date = %s",
        $username,
        $today
    ), ARRAY_A);
    
    $indexed = [];
    foreach ($results as $row) {
        $indexed[$row['shift_management_id']] = $row;
    }
    
    return $indexed;
}

/**
 * Get requests for a specific shift
 */
function get_shift_requests($shift_history_id) {
    if (!$shift_history_id) return [];
    
    global $wpdb;
    $table = $wpdb->prefix . 'cms_requests';
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table WHERE shift_history_id = %d ORDER BY created_at ASC",
        $shift_history_id
    ), ARRAY_A);
}

/**
 * Get employee requests where details are empty
 */
function get_employee_requests_without_details($username) {
    global $wpdb;
    
    $table = $wpdb->prefix . 'cms_requests';
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table 
         WHERE username = %s 
         AND status = 'pending' 
         AND (details IS NULL OR details = '')
         AND type IN ('late_login', 'early_login', 'late_logout', 'early_logout')
         ORDER BY date DESC, created_at ASC",
        $username
    ), ARRAY_A);
}

/**
 * Get employee requests from database
 */
function get_employee_requests_from_db($username, $status = null) {
    global $wpdb;
    
    $table = $wpdb->prefix . 'cms_requests';
    
    $sql = "SELECT * FROM $table WHERE username = %s";
    $params = array($username);
    
    if ($status) {
        $sql .= " AND status = %s";
        $params[] = $status;
    }
    
    $sql .= " ORDER BY date DESC, created_at DESC";
    
    return $wpdb->get_results($wpdb->prepare($sql, $params), ARRAY_A);
}

/**
 * Get employee recent shift history
 */
function get_employee_recent_shift_history($username, $limit = 10) {
    global $wpdb;
    
    $table = $wpdb->prefix . 'cms_shift_history';
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT sh.*, sm.shift_start_time, sm.shift_end_time 
         FROM $table sh
         LEFT JOIN {$wpdb->prefix}cms_shift_management sm ON sh.shift_management_id = sm.id
         WHERE sh.username = %s
         ORDER BY sh.date DESC, sm.shift_start_time ASC
         LIMIT %d",
        $username,
        $limit
    ), ARRAY_A);
}

/**
 * Get employee weekly counted hours
 * Calculates total counted hours for the current week (Monday to Sunday)
 */
function get_employee_weekly_counted_hours($username) {
    global $wpdb;
    
    // Get start and end of current week (Monday to Sunday)
    $today = new DateTime(current_time('Y-m-d'));
    $day_of_week = (int)$today->format('w');
    $days_to_monday = ($day_of_week == 0) ? 6 : $day_of_week - 1;
    
    $week_start = clone $today;
    $week_start->modify("-{$days_to_monday} days");
    $week_end = clone $week_start;
    $week_end->modify("+6 days");
    
    $table = $wpdb->prefix . 'cms_shift_history';
    
    $result = $wpdb->get_row($wpdb->prepare(
        "SELECT SUM(counted_hours * 60 + counted_mins) as total_minutes
         FROM $table
         WHERE username = %s 
         AND date BETWEEN %s AND %s
         AND counted_hours IS NOT NULL",
        $username,
        $week_start->format('Y-m-d'),
        $week_end->format('Y-m-d')
    ));
    
    if ($result && $result->total_minutes) {
        $hours = floor($result->total_minutes / 60);
        $minutes = $result->total_minutes % 60;
        // Format as hours with 1 decimal place (e.g., 38.5 for 38 hours 30 minutes)
        return number_format($hours + ($minutes / 60), 1);
    }
    
    return '0.0';
}

/**
 * Get employee monthly counted hours
 * Calculates total counted hours for the current month
 */
function get_employee_monthly_counted_hours($username) {
    global $wpdb;
    
    $month_start = date('Y-m-01');
    $month_end = date('Y-m-t');
    
    $table = $wpdb->prefix . 'cms_shift_history';
    
    $result = $wpdb->get_row($wpdb->prepare(
        "SELECT SUM(counted_hours * 60 + counted_mins) as total_minutes
         FROM $table
         WHERE username = %s 
         AND date BETWEEN %s AND %s
         AND counted_hours IS NOT NULL",
        $username,
        $month_start,
        $month_end
    ));
    
    if ($result && $result->total_minutes) {
        $hours = floor($result->total_minutes / 60);
        $minutes = $result->total_minutes % 60;
        // Format as hours with 1 decimal place
        return number_format($hours + ($minutes / 60), 1);
    }
    
    return '0.0';
}

/**
 * Get employee days worked this month
 * Counts distinct days where employee had any shift history
 */
function get_employee_days_worked_month($username) {
    global $wpdb;
    
    $month_start = date('Y-m-01');
    $month_end = date('Y-m-t');
    
    $table = $wpdb->prefix . 'cms_shift_history';
    
    $result = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT date)
         FROM $table
         WHERE username = %s 
         AND date BETWEEN %s AND %s
         AND counted_hours IS NOT NULL",
        $username,
        $month_start,
        $month_end
    ));
    
    return $result ? $result : 0;
}