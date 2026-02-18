<?php
/**
 * Admin Dashboard
 * Dashboard for regular administrators with limited management capabilities
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Admin Dashboard Shortcode
 */
function cms_admin_dashboard_shortcode($atts) {
    $atts = shortcode_atts([
        'title' => 'Admin Dashboard',
        'welcome_message' => 'Welcome back',
        'show_stats' => 'yes',
        'show_quick_actions' => 'yes'
    ], $atts, 'cms_admin_dashboard');

    // Check if user is logged in and is admin
    if (!cms_is_user_logged_in()) {
        return cms_get_login_required_message();
    }

    $current_user = cms_get_current_user();
    if ($current_user['role'] !== 'admin') {
        return cms_get_access_denied_message();
    }

    ob_start();
    ?>
    <div class="cms-dashboard-wrapper cms-admin-dashboard">
        <!-- Dashboard Header -->
        <div class="cms-dashboard-header">
            <div class="cms-dashboard-title">
                <h1><?php echo esc_html($atts['title']); ?></h1>
                <p class="cms-welcome-message">
                    <?php echo esc_html($atts['welcome_message'] . ', ' . $current_user['username']); ?>
                </p>
            </div>
            <div class="cms-dashboard-actions">
                <a href="<?php echo esc_url(home_url('/employee-list')); ?>" class="cms-btn cms-btn-primary">
                    <i class="dashicons dashicons-businessman"></i> Manage Employees
                </a>
                <a href="<?php echo esc_url(home_url('/corp-accounts')); ?>" class="cms-btn cms-btn-secondary">
                    <i class="dashicons dashicons-building"></i> Corporate Accounts
                </a>
                <?php echo do_shortcode('[cms_logout_link text="Logout" class="cms-btn cms-btn-danger"]'); ?>
            </div>
        </div>

        <!-- Quick Stats -->
        <?php if ($atts['show_stats'] === 'yes'): ?>
        <div class="cms-dashboard-stats">
            <?php echo cms_admin_get_dashboard_stats(); ?>
        </div>
        <?php endif; ?>

        <!-- Management Sections -->
        <div class="cms-management-grid">
            <!-- Employee Management -->
            <div class="cms-management-card">
                <div class="cms-card-header">
                    <i class="dashicons dashicons-businessman"></i>
                    <h2>Employee Management</h2>
                </div>
                <div class="cms-card-content">
                    <p>Manage employees, view details, and track their assignments.</p>
                    <div class="cms-card-stats">
                        <div class="cms-stat-item">
                            <span class="cms-stat-label">Total Employees:</span>
                            <span class="cms-stat-value"><?php echo esc_html(cms_get_employee_count()); ?></span>
                        </div>
                        <div class="cms-stat-item">
                            <span class="cms-stat-label">Active:</span>
                            <span class="cms-stat-value"><?php echo esc_html(cms_get_active_employee_count()); ?></span>
                        </div>
                    </div>
                    <div class="cms-card-actions">
                        <a href="<?php echo esc_url(home_url('/employee-list')); ?>" class="cms-btn cms-btn-primary">View All</a>
                        <a href="<?php echo esc_url(home_url('/add-employee')); ?>" class="cms-btn cms-btn-success">Add New</a>
                    </div>
                </div>
            </div>

            <!-- Corporate Account Management -->
            <div class="cms-management-card">
                <div class="cms-card-header">
                    <i class="dashicons dashicons-building"></i>
                    <h2>Corporate Accounts</h2>
                </div>
                <div class="cms-card-content">
                    <p>Manage corporate accounts and their details.</p>
                    <div class="cms-card-stats">
                        <div class="cms-stat-item">
                            <span class="cms-stat-label">Total Accounts:</span>
                            <span class="cms-stat-value"><?php echo esc_html(cms_get_corp_account_count()); ?></span>
                        </div>
                        <div class="cms-stat-item">
                            <span class="cms-stat-label">Active:</span>
                            <span class="cms-stat-value"><?php echo esc_html(cms_get_active_corp_account_count()); ?></span>
                        </div>
                    </div>
                    <div class="cms-card-actions">
                        <a href="<?php echo esc_url(home_url('/corp-accounts')); ?>" class="cms-btn cms-btn-primary">View All</a>
                        <a href="<?php echo esc_url(home_url('/add-corp-account')); ?>" class="cms-btn cms-btn-success">Add New</a>
                    </div>
                </div>
            </div>

            <!-- Employee Assignments -->
            <div class="cms-management-card">
                <div class="cms-card-header">
                    <i class="dashicons dashicons-networking"></i>
                    <h2>Employee Assignments</h2>
                </div>
                <div class="cms-card-content">
                    <p>Assign employees to corporate accounts.</p>
                    <div class="cms-card-stats">
                        <div class="cms-stat-item">
                            <span class="cms-stat-label">Total Assignments:</span>
                            <span class="cms-stat-value"><?php echo esc_html(cms_get_assignment_count()); ?></span>
                        </div>
                        <div class="cms-stat-item">
                            <span class="cms-stat-label">Unassigned Employees:</span>
                            <span class="cms-stat-value"><?php echo esc_html(cms_get_unassigned_employee_count()); ?></span>
                        </div>
                    </div>
                    <div class="cms-card-actions">
                        <a href="<?php echo esc_url(home_url('/emp-corp-assign')); ?>" class="cms-btn cms-btn-primary">Manage Assignments</a>
                    </div>
                </div>
            </div>

            <!-- Shift Management -->
            <div class="cms-management-card">
                <div class="cms-card-header">
                    <i class="dashicons dashicons-clock"></i>
                    <h2>Shift Management</h2>
                </div>
                <div class="cms-card-content">
                    <p>View and manage employee shifts.</p>
                    <div class="cms-card-stats">
                        <div class="cms-stat-item">
                            <span class="cms-stat-label">Today's Shifts:</span>
                            <span class="cms-stat-value"><?php echo esc_html(cms_get_today_shifts_count()); ?></span>
                        </div>
                        <div class="cms-stat-item">
                            <span class="cms-stat-label">Active Now:</span>
                            <span class="cms-stat-value"><?php echo esc_html(cms_get_active_shifts_count()); ?></span>
                        </div>
                    </div>
                    <div class="cms-card-actions">
                        <a href="<?php echo esc_url(home_url('/shift-management')); ?>" class="cms-btn cms-btn-primary">Manage Shifts</a>
                        <a href="<?php echo esc_url(home_url('/shift-history')); ?>" class="cms-btn cms-btn-secondary">View History</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <?php if ($atts['show_quick_actions'] === 'yes'): ?>
        <div class="cms-quick-actions">
            <h2>Quick Actions</h2>
            <div class="cms-actions-grid">
                <a href="<?php echo esc_url(home_url('/add-employee')); ?>" class="cms-action-card">
                    <i class="dashicons dashicons-businessman"></i>
                    <span>Add New Employee</span>
                </a>
                <a href="<?php echo esc_url(home_url('/add-corp-account')); ?>" class="cms-action-card">
                    <i class="dashicons dashicons-building"></i>
                    <span>Add Corporate Account</span>
                </a>
                <a href="<?php echo esc_url(home_url('/emp-corp-assign')); ?>" class="cms-action-card">
                    <i class="dashicons dashicons-networking"></i>
                    <span>Assign Employees</span>
                </a>
                <a href="<?php echo esc_url(home_url('/shift-management')); ?>" class="cms-action-card">
                    <i class="dashicons dashicons-clock"></i>
                    <span>Manage Shifts</span>
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Recent Employees -->
        <div class="cms-recent-section">
            <h2>Recently Added Employees</h2>
            <?php echo cms_get_recent_employees(); ?>
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
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
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
        color: #4e73df;
    }

    .cms-btn-primary:hover {
        background: #f8f9fc;
        color: #224abe;
    }

    .cms-btn-secondary {
        background: rgba(255,255,255,0.2);
        color: white;
    }

    .cms-btn-secondary:hover {
        background: rgba(255,255,255,0.3);
        color: white;
    }

    .cms-btn-success {
        background: #1cc88a;
        color: white;
    }

    .cms-btn-success:hover {
        background: #17a673;
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

    .cms-management-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }

    .cms-management-card {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        overflow: hidden;
    }

    .cms-card-header {
        padding: 20px;
        background: linear-gradient(135deg, #f8f9fc 0%, #eaecf4 100%);
        border-bottom: 1px solid #dddfeb;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .cms-card-header i {
        font-size: 24px;
        color: #4e73df;
    }

    .cms-card-header h2 {
        margin: 0;
        font-size: 18px;
        color: #5a5c69;
    }

    .cms-card-content {
        padding: 20px;
    }

    .cms-card-content p {
        margin: 0 0 15px;
        color: #858796;
    }

    .cms-card-stats {
        margin-bottom: 15px;
        padding: 10px;
        background: #f8f9fc;
        border-radius: 5px;
    }

    .cms-stat-item {
        display: flex;
        justify-content: space-between;
        padding: 5px 0;
        font-size: 14px;
    }

    .cms-stat-label {
        color: #858796;
    }

    .cms-stat-value {
        font-weight: 600;
        color: #5a5c69;
    }

    .cms-card-actions {
        display: flex;
        gap: 10px;
    }

    .cms-quick-actions {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 25px;
        margin-bottom: 30px;
    }

    .cms-quick-actions h2 {
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 22px;
        color: #5a5c69;
        border-bottom: 2px solid #4e73df;
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
        color: #4e73df;
    }

    .cms-action-card span {
        font-size: 14px;
        font-weight: 500;
    }

    .cms-recent-section {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 25px;
    }

    .cms-recent-section h2 {
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 22px;
        color: #5a5c69;
        border-bottom: 2px solid #4e73df;
        padding-bottom: 10px;
    }

    .cms-recent-table {
        width: 100%;
        border-collapse: collapse;
    }

    .cms-recent-table th {
        text-align: left;
        padding: 12px;
        background: #f8f9fc;
        color: #5a5c69;
        font-weight: 600;
        border-bottom: 2px solid #e3e6f0;
    }

    .cms-recent-table td {
        padding: 12px;
        border-bottom: 1px solid #e3e6f0;
    }

    .cms-recent-table tr:hover {
        background: #f8f9fc;
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
        
        .cms-management-grid {
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
add_shortcode('cms_admin_dashboard', 'cms_admin_dashboard_shortcode');

/**
 * Get dashboard statistics for admin
 */
function cms_admin_get_dashboard_stats() {
    global $wpdb;
    
    ob_start();
    ?>
    <div class="cms-stats-grid">
        <div class="cms-stat-card">
            <div class="cms-stat-icon" style="background: #4e73df;">
                <i class="dashicons dashicons-businessman"></i>
            </div>
            <div class="cms-stat-content">
                <h3><?php echo esc_html(cms_get_employee_count()); ?></h3>
                <p>Total Employees</p>
            </div>
        </div>

        <div class="cms-stat-card" style="background: #1cc88a;">
            <div class="cms-stat-icon">
                <i class="dashicons dashicons-building"></i>
            </div>
            <div class="cms-stat-content">
                <h3><?php echo esc_html(cms_get_corp_account_count()); ?></h3>
                <p>Corporate Accounts</p>
            </div>
        </div>

        <div class="cms-stat-card" style="background: #36b9cc;">
            <div class="cms-stat-icon">
                <i class="dashicons dashicons-networking"></i>
            </div>
            <div class="cms-stat-content">
                <h3><?php echo esc_html(cms_get_assignment_count()); ?></h3>
                <p>Assignments</p>
            </div>
        </div>

        <div class="cms-stat-card" style="background: #f6c23e;">
            <div class="cms-stat-icon">
                <i class="dashicons dashicons-clock"></i>
            </div>
            <div class="cms-stat-content">
                <h3><?php echo esc_html(cms_get_today_shifts_count()); ?></h3>
                <p>Today's Shifts</p>
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
        border-radius: 10px;
        padding: 20px;
        color: white;
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .cms-stat-card:nth-child(1) { background: linear-gradient(135deg, #4e73df 0%, #224abe 100%); }
    .cms-stat-card:nth-child(2) { background: linear-gradient(135deg, #1cc88a 0%, #13855c 100%); }
    .cms-stat-card:nth-child(3) { background: linear-gradient(135deg, #36b9cc 0%, #258391 100%); }
    .cms-stat-card:nth-child(4) { background: linear-gradient(135deg, #f6c23e 0%, #dda20a 100%); }

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
 * Get recent employees
 */
function cms_get_recent_employees() {
    global $wpdb;
    
    $table = $wpdb->prefix . 'cms_employee';
    $employees = $wpdb->get_results(
        "SELECT * FROM $table 
         ORDER BY id DESC 
         LIMIT 5"
    );

    if (empty($employees)) {
        return '<p>No employees found.</p>';
    }

    ob_start();
    ?>
    <table class="cms-recent-table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Status</th>
                <th>Action</th>
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
                    <a href="<?php echo esc_url(home_url('/edit-employee/' . $employee->id)); ?>" 
                       class="cms-btn-small">
                        Edit
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <style>
    .cms-recent-table {
        width: 100%;
        border-collapse: collapse;
    }

    .cms-recent-table th {
        text-align: left;
        padding: 12px;
        background: #f8f9fc;
        color: #5a5c69;
        font-weight: 600;
        border-bottom: 2px solid #e3e6f0;
    }

    .cms-recent-table td {
        padding: 12px;
        border-bottom: 1px solid #e3e6f0;
    }

    .cms-recent-table tr:hover {
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
        background: #4e73df;
        color: white;
        text-decoration: none;
        border-radius: 3px;
        font-size: 12px;
        margin-right: 5px;
    }

    .cms-btn-small:hover {
        background: #224abe;
        color: white;
    }
    </style>
    <?php
    return ob_get_clean();
}

// Helper functions for statistics
function cms_get_employee_count() {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_employee';
    return $wpdb->get_var("SELECT COUNT(*) FROM $table");
}

function cms_get_active_employee_count() {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_employee';
    return $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'active'");
}

function cms_get_corp_account_count() {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_corporate_account';
    return $wpdb->get_var("SELECT COUNT(*) FROM $table");
}

function cms_get_active_corp_account_count() {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_corporate_account';
    return $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'active'");
}

function cms_get_assignment_count() {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_emp_corp_assign';
    return $wpdb->get_var("SELECT COUNT(*) FROM $table");
}

function cms_get_unassigned_employee_count() {
    global $wpdb;
    $emp_table = $wpdb->prefix . 'cms_employee';
    $assign_table = $wpdb->prefix . 'cms_emp_corp_assign';
    
    return $wpdb->get_var(
        "SELECT COUNT(*) FROM $emp_table e 
         WHERE NOT EXISTS (
             SELECT 1 FROM $assign_table a 
             WHERE a.employee_id = e.id
         )"
    );
}

function cms_get_today_shifts_count() {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_employee_shifts';
    return $wpdb->get_var(
        "SELECT COUNT(*) FROM $table 
         WHERE DATE(shift_date) = CURDATE()"
    );
}

function cms_get_active_shifts_count() {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_employee_shifts';
    return $wpdb->get_var(
        "SELECT COUNT(*) FROM $table 
         WHERE shift_date = CURDATE() 
         AND start_time <= CURTIME() 
         AND end_time >= CURTIME()"
    );
}