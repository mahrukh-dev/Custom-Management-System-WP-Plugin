<?php
/**
 * Corporate Account Dashboard
 * Dashboard for corporate account users to manage their employees and shifts
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Corporate Dashboard Shortcode
 */
function cms_corporate_dashboard_shortcode($atts) {
    $atts = shortcode_atts([
        'title' => 'Corporate Dashboard',
        'welcome_message' => 'Welcome back',
        'show_stats' => 'yes',
        'show_employees' => 'yes',
        'show_shifts' => 'yes',
        'days_to_show' => 7
    ], $atts, 'cms_corporate_dashboard');

    // Check if user is logged in and is corporate account
    if (!cms_is_user_logged_in()) {
        return cms_get_login_required_message();
    }

    $current_user = cms_get_current_user();
    if ($current_user['role'] !== 'corp_account') {
        return cms_get_access_denied_message();
    }

    // Get corporate account details
    $corp_account = cms_get_corporate_account_by_username($current_user['username']);
    if (!$corp_account) {
        return '<div class="cms-message error">Corporate account not found.</div>';
    }

    ob_start();
    ?>
    <div class="cms-dashboard-wrapper cms-corporate-dashboard">
        <!-- Dashboard Header -->
        <div class="cms-dashboard-header">
            <div class="cms-dashboard-title">
                <h1><?php echo esc_html($atts['title']); ?></h1>
                <p class="cms-welcome-message">
                    <?php echo esc_html($atts['welcome_message'] . ', ' . $corp_account->company_name); ?>
                </p>
            </div>
            <div class="cms-dashboard-actions">
                <a href="<?php echo esc_url(home_url('/employee-list')); ?>" class="cms-btn cms-btn-primary">
                    <i class="dashicons dashicons-businessman"></i> View Employees
                </a>
                <a href="<?php echo esc_url(home_url('/shift-history')); ?>" class="cms-btn cms-btn-secondary">
                    <i class="dashicons dashicons-clock"></i> Shift History
                </a>
                <?php echo do_shortcode('[cms_logout_link text="Logout" class="cms-btn cms-btn-danger"]'); ?>
            </div>
        </div>

        <!-- Corporate Account Info Card -->
        <div class="cms-company-info">
            <div class="cms-company-card">
                <div class="cms-company-header">
                    <i class="dashicons dashicons-building"></i>
                    <h2>Company Information</h2>
                </div>
                <div class="cms-company-details">
                    <div class="cms-detail-row">
                        <span class="cms-detail-label">Company Name:</span>
                        <span class="cms-detail-value"><?php echo esc_html($corp_account->company_name); ?></span>
                    </div>
                    <div class="cms-detail-row">
                        <span class="cms-detail-label">Contact Person:</span>
                        <span class="cms-detail-value"><?php echo esc_html($corp_account->contact_person); ?></span>
                    </div>
                    <div class="cms-detail-row">
                        <span class="cms-detail-label">Email:</span>
                        <span class="cms-detail-value"><?php echo esc_html($corp_account->email); ?></span>
                    </div>
                    <div class="cms-detail-row">
                        <span class="cms-detail-label">Phone:</span>
                        <span class="cms-detail-value"><?php echo esc_html($corp_account->phone); ?></span>
                    </div>
                    <div class="cms-detail-row">
                        <span class="cms-detail-label">Address:</span>
                        <span class="cms-detail-value"><?php echo esc_html($corp_account->address); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Stats -->
        <?php if ($atts['show_stats'] === 'yes'): ?>
        <div class="cms-dashboard-stats">
            <?php echo cms_corporate_get_dashboard_stats($corp_account->id); ?>
        </div>
        <?php endif; ?>

        <!-- Employees Section -->
        <?php if ($atts['show_employees'] === 'yes'): ?>
        <div class="cms-section">
            <div class="cms-section-header">
                <h2>Your Employees</h2>
                <a href="<?php echo esc_url(home_url('/employee-list')); ?>" class="cms-view-all">
                    View All <i class="dashicons dashicons-arrow-right-alt2"></i>
                </a>
            </div>
            <?php echo cms_corporate_get_employees_list($corp_account->id); ?>
        </div>
        <?php endif; ?>

        <!-- Recent Shifts Section -->
        <?php if ($atts['show_shifts'] === 'yes'): ?>
        <div class="cms-section">
            <div class="cms-section-header">
                <h2>Recent Shifts (Last <?php echo esc_html($atts['days_to_show']); ?> Days)</h2>
                <a href="<?php echo esc_url(home_url('/shift-history')); ?>" class="cms-view-all">
                    View All <i class="dashicons dashicons-arrow-right-alt2"></i>
                </a>
            </div>
            <?php echo cms_corporate_get_recent_shifts($corp_account->id, $atts['days_to_show']); ?>
        </div>
        <?php endif; ?>

        <!-- Quick Actions -->
        <div class="cms-quick-actions">
            <h2>Quick Actions</h2>
            <div class="cms-actions-grid">
                <a href="<?php echo esc_url(home_url('/shift-management')); ?>" class="cms-action-card">
                    <i class="dashicons dashicons-clock"></i>
                    <span>Manage Shifts</span>
                </a>
                <a href="<?php echo esc_url(home_url('/shift-history')); ?>" class="cms-action-card">
                    <i class="dashicons dashicons-backup"></i>
                    <span>View Shift History</span>
                </a>
                <a href="<?php echo esc_url(home_url('/employee-list')); ?>" class="cms-action-card">
                    <i class="dashicons dashicons-groups"></i>
                    <span>View All Employees</span>
                </a>
                <a href="<?php echo esc_url(home_url('/edit-corp-account/' . $corp_account->id)); ?>" class="cms-action-card">
                    <i class="dashicons dashicons-edit"></i>
                    <span>Edit Company Profile</span>
                </a>
            </div>
        </div>
    </div>

    <style>
    .cms-dashboard-wrapper {
        max-width: 1400px;
        margin: 30px auto;
        padding: 0 20px;
    }

    .cms-dashboard-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding: 20px;
        background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        border-radius: 10px;
        color: white;
    }

    .cms-dashboard-header h1 {
        margin: 0;
        color: white;
        font-size: 28px;
    }

    .cms-welcome-message {
        margin: 5px 0 0;
        opacity: 0.9;
    }

    .cms-dashboard-actions {
        display: flex;
        gap: 10px;
    }

    .cms-btn {
        padding: 10px 20px;
        border-radius: 5px;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.3s;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .cms-btn-primary {
        background: white;
        color: #1cc88a;
    }

    .cms-btn-primary:hover {
        background: #f8f9fc;
        color: #13855c;
    }

    .cms-btn-secondary {
        background: rgba(255,255,255,0.2);
        color: white;
    }

    .cms-btn-secondary:hover {
        background: rgba(255,255,255,0.3);
        color: white;
    }

    .cms-btn-danger {
        background: #e74a3b;
        color: white;
    }

    .cms-btn-danger:hover {
        background: #be2617;
        color: white;
    }

    .cms-company-info {
        margin-bottom: 30px;
    }

    .cms-company-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .cms-company-header {
        padding: 20px;
        background: linear-gradient(135deg, #f8f9fc 0%, #eaecf4 100%);
        border-bottom: 1px solid #dddfeb;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .cms-company-header i {
        font-size: 24px;
        color: #1cc88a;
    }

    .cms-company-header h2 {
        margin: 0;
        font-size: 18px;
        color: #5a5c69;
    }

    .cms-company-details {
        padding: 20px;
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 15px;
    }

    .cms-detail-row {
        display: flex;
        flex-direction: column;
        padding: 10px;
        background: #f8f9fc;
        border-radius: 5px;
    }

    .cms-detail-label {
        font-size: 12px;
        color: #858796;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 5px;
    }

    .cms-detail-value {
        font-size: 16px;
        color: #5a5c69;
        font-weight: 500;
    }

    .cms-section {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 25px;
        margin-bottom: 30px;
    }

    .cms-section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .cms-section-header h2 {
        margin: 0;
        font-size: 22px;
        color: #5a5c69;
        border-left: 4px solid #1cc88a;
        padding-left: 15px;
    }

    .cms-view-all {
        color: #1cc88a;
        text-decoration: none;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 5px;
    }

    .cms-view-all:hover {
        color: #13855c;
    }

    .cms-quick-actions {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 25px;
    }

    .cms-quick-actions h2 {
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 22px;
        color: #5a5c69;
        border-bottom: 2px solid #1cc88a;
        padding-bottom: 10px;
    }

    .cms-actions-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
    }

    .cms-action-card {
        display: flex;
        flex-direction: column;
        align-items: center;
        padding: 20px;
        background: #f8f9fc;
        border-radius: 8px;
        text-decoration: none;
        color: #5a5c69;
        transition: all 0.3s;
        border: 1px solid #e3e6f0;
    }

    .cms-action-card:hover {
        background: #eaecf4;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .cms-action-card i {
        font-size: 32px;
        width: 32px;
        height: 32px;
        margin-bottom: 10px;
        color: #1cc88a;
    }

    .cms-action-card span {
        font-size: 14px;
        font-weight: 500;
    }

    @media (max-width: 768px) {
        .cms-dashboard-header {
            flex-direction: column;
            text-align: center;
            gap: 15px;
        }
        
        .cms-dashboard-actions {
            flex-wrap: wrap;
            justify-content: center;
        }
        
        .cms-company-details {
            grid-template-columns: 1fr;
        }
        
        .cms-actions-grid {
            grid-template-columns: 1fr;
        }
    }
    </style>
    <?php

    return ob_get_clean();
}
add_shortcode('cms_corporate_dashboard', 'cms_corporate_dashboard_shortcode');

/**
 * Get corporate account by username
 */
function cms_get_corporate_account_by_username($username) {
    global $wpdb;
    
    $table = $wpdb->prefix . 'cms_corp_acc';
    
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE username = %s",
        $username
    ));
}

/**
 * Get dashboard statistics for corporate account
 */
function cms_corporate_get_dashboard_stats($corp_id) {
    global $wpdb;
    
    $emp_table = $wpdb->prefix . 'cms_employee';
    $assign_table = $wpdb->prefix . 'cms_emp_corp_assign';
    $shifts_table = $wpdb->prefix . 'cms_employee_shifts';
    
    // Get employees count for this corporate account
    $employees_count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT e.id) 
         FROM $emp_table e
         INNER JOIN $assign_table a ON e.id = a.employee_id
         WHERE a.corp_id = %d",
        $corp_id
    ));
    
    // Get active employees count
    $active_employees = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(DISTINCT e.id) 
         FROM $emp_table e
         INNER JOIN $assign_table a ON e.id = a.employee_id
         WHERE a.corp_id = %d AND e.status = 'active'",
        $corp_id
    ));
    
    // Get today's shifts count
    $today_shifts = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) 
         FROM $shifts_table s
         INNER JOIN $assign_table a ON s.employee_id = a.employee_id
         WHERE a.corp_id = %d AND DATE(s.shift_date) = CURDATE()",
        $corp_id
    ));
    
    // Get total shifts this month
    $month_shifts = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) 
         FROM $shifts_table s
         INNER JOIN $assign_table a ON s.employee_id = a.employee_id
         WHERE a.corp_id = %d 
         AND MONTH(s.shift_date) = MONTH(CURDATE())
         AND YEAR(s.shift_date) = YEAR(CURDATE())",
        $corp_id
    ));
    
    ob_start();
    ?>
    <div class="cms-stats-grid">
        <div class="cms-stat-card">
            <div class="cms-stat-icon">
                <i class="dashicons dashicons-businessman"></i>
            </div>
            <div class="cms-stat-content">
                <h3><?php echo esc_html($employees_count ?: 0); ?></h3>
                <p>Total Employees</p>
            </div>
        </div>

        <div class="cms-stat-card">
            <div class="cms-stat-icon">
                <i class="dashicons dashicons-yes"></i>
            </div>
            <div class="cms-stat-content">
                <h3><?php echo esc_html($active_employees ?: 0); ?></h3>
                <p>Active Employees</p>
            </div>
        </div>

        <div class="cms-stat-card">
            <div class="cms-stat-icon">
                <i class="dashicons dashicons-clock"></i>
            </div>
            <div class="cms-stat-content">
                <h3><?php echo esc_html($today_shifts ?: 0); ?></h3>
                <p>Today's Shifts</p>
            </div>
        </div>

        <div class="cms-stat-card">
            <div class="cms-stat-icon">
                <i class="dashicons dashicons-calendar"></i>
            </div>
            <div class="cms-stat-content">
                <h3><?php echo esc_html($month_shifts ?: 0); ?></h3>
                <p>This Month</p>
            </div>
        </div>
    </div>

    <style>
    .cms-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .cms-stat-card {
        background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%);
        border-radius: 10px;
        padding: 20px;
        color: white;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .cms-stat-icon {
        background: rgba(255,255,255,0.2);
        width: 50px;
        height: 50px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .cms-stat-icon i {
        font-size: 30px;
        width: 30px;
        height: 30px;
    }

    .cms-stat-content h3 {
        margin: 0;
        font-size: 28px;
        font-weight: 600;
    }

    .cms-stat-content p {
        margin: 5px 0 0;
        opacity: 0.8;
        font-size: 14px;
    }
    </style>
    <?php
    return ob_get_clean();
}

/**
 * Get employees list for corporate account
 */
function cms_corporate_get_employees_list($corp_id) {
    global $wpdb;
    
    $emp_table = $wpdb->prefix . 'cms_employee';
    $assign_table = $wpdb->prefix . 'cms_emp_corp_assign';
    
    $employees = $wpdb->get_results($wpdb->prepare(
        "SELECT e.* 
         FROM $emp_table e
         INNER JOIN $assign_table a ON e.id = a.employee_id
         WHERE a.corp_id = %d
         ORDER BY e.name ASC
         LIMIT 10",
        $corp_id
    ));

    if (empty($employees)) {
        return '<p class="cms-no-data">No employees assigned to your company yet.</p>';
    }

    ob_start();
    ?>
    <div class="cms-employees-list">
        <table class="cms-employees-table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($employees as $employee): ?>
                <tr>
                    <td><?php echo esc_html($employee->name); ?></td>
                    <td><?php echo esc_html($employee->email); ?></td>
                    <td><?php echo esc_html($employee->phone); ?></td>
                    <td>
                        <span class="cms-status-badge cms-status-<?php echo esc_attr($employee->status); ?>">
                            <?php echo esc_html($employee->status); ?>
                        </span>
                    </td>
                    <td>
                        <a href="<?php echo esc_url(home_url('/view-employee/' . $employee->id)); ?>" 
                           class="cms-btn-small">
                            View
                        </a>
                        <a href="<?php echo esc_url(home_url('/employee-shift-history/' . $employee->username)); ?>" 
                           class="cms-btn-small">
                            Shifts
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <style>
    .cms-employees-table {
        width: 100%;
        border-collapse: collapse;
    }

    .cms-employees-table th {
        text-align: left;
        padding: 12px;
        background: #f8f9fc;
        color: #5a5c69;
        font-weight: 600;
        border-bottom: 2px solid #e3e6f0;
    }

    .cms-employees-table td {
        padding: 12px;
        border-bottom: 1px solid #e3e6f0;
    }

    .cms-employees-table tr:hover {
        background: #f8f9fc;
    }

    .cms-status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }

    .cms-status-active {
        background: #d4edda;
        color: #155724;
    }

    .cms-status-inactive {
        background: #f8d7da;
        color: #721c24;
    }

    .cms-btn-small {
        padding: 4px 8px;
        background: #1cc88a;
        color: white;
        text-decoration: none;
        border-radius: 3px;
        font-size: 12px;
        margin-right: 5px;
        display: inline-block;
    }

    .cms-btn-small:hover {
        background: #13855c;
        color: white;
    }

    .cms-no-data {
        padding: 20px;
        text-align: center;
        background: #f8f9fc;
        border-radius: 5px;
        color: #858796;
    }
    </style>
    <?php
    return ob_get_clean();
}

/**
 * Get recent shifts for corporate account
 */
function cms_corporate_get_recent_shifts($corp_id, $days = 7) {
    global $wpdb;
    
    $shifts_table = $wpdb->prefix . 'cms_employee_shifts';
    $emp_table = $wpdb->prefix . 'cms_employee';
    $assign_table = $wpdb->prefix . 'cms_emp_corp_assign';
    
    $shifts = $wpdb->get_results($wpdb->prepare(
        "SELECT s.*, e.name as employee_name, e.username 
         FROM $shifts_table s
         INNER JOIN $emp_table e ON s.employee_id = e.id
         INNER JOIN $assign_table a ON e.id = a.employee_id
         WHERE a.corp_id = %d 
         AND s.shift_date >= DATE_SUB(CURDATE(), INTERVAL %d DAY)
         ORDER BY s.shift_date DESC, s.start_time DESC
         LIMIT 20",
        $corp_id,
        $days
    ));

    if (empty($shifts)) {
        return '<p class="cms-no-data">No shifts found for the recent period.</p>';
    }

    ob_start();
    ?>
    <div class="cms-shifts-list">
        <table class="cms-shifts-table">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Date</th>
                    <th>Start Time</th>
                    <th>End Time</th>
                    <th>Hours</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($shifts as $shift): 
                    $start = strtotime($shift->start_time);
                    $end = strtotime($shift->end_time);
                    $hours = ($end - $start) / 3600;
                ?>
                <tr>
                    <td><?php echo esc_html($shift->employee_name); ?></td>
                    <td><?php echo esc_html(date('Y-m-d', strtotime($shift->shift_date))); ?></td>
                    <td><?php echo esc_html(date('H:i', strtotime($shift->start_time))); ?></td>
                    <td><?php echo esc_html(date('H:i', strtotime($shift->end_time))); ?></td>
                    <td><?php echo esc_html(number_format($hours, 1)); ?></td>
                    <td>
                        <span class="cms-shift-status cms-shift-<?php echo esc_attr($shift->status); ?>">
                            <?php echo esc_html($shift->status); ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <style>
    .cms-shifts-table {
        width: 100%;
        border-collapse: collapse;
    }

    .cms-shifts-table th {
        text-align: left;
        padding: 12px;
        background: #f8f9fc;
        color: #5a5c69;
        font-weight: 600;
        border-bottom: 2px solid #e3e6f0;
    }

    .cms-shifts-table td {
        padding: 12px;
        border-bottom: 1px solid #e3e6f0;
    }

    .cms-shifts-table tr:hover {
        background: #f8f9fc;
    }

    .cms-shift-status {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }

    .cms-shift-completed {
        background: #d4edda;
        color: #155724;
    }

    .cms-shift-scheduled {
        background: #cce5ff;
        color: #004085;
    }

    .cms-shift-in-progress {
        background: #fff3cd;
        color: #856404;
    }
    </style>
    <?php
    return ob_get_clean();
}