<?php
/**
 * Main Admin Dashboard
 * Comprehensive dashboard for main administrators with full system control
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main Admin Dashboard Shortcode
 */
function cms_main_admin_dashboard_shortcode($atts) {
    $atts = shortcode_atts([
        'title' => 'Main Admin Dashboard',
        'welcome_message' => 'Welcome back, Main Administrator',
        'show_stats' => 'yes',
        'show_recent_activities' => 'yes',
        'show_system_health' => 'yes'
    ], $atts, 'cms_main_admin_dashboard');

    // Check if user is logged in and is main admin
    if (!cms_is_user_logged_in()) {
        return cms_get_login_required_message();
    }

    $current_user = cms_get_current_user();
    if ($current_user['role'] !== 'main_admin') {
        return cms_get_access_denied_message();
    }

    ob_start();
    ?>
    <div class="cms-dashboard-wrapper cms-main-admin-dashboard">
        <!-- Dashboard Header -->
        <div class="cms-dashboard-header">
            <div class="cms-dashboard-title">
                <h1><?php echo esc_html($atts['title']); ?></h1>
                <p class="cms-welcome-message">
                    <?php echo esc_html($atts['welcome_message'] . ', ' . $current_user['username']); ?>
                </p>
            </div>
            <div class="cms-dashboard-actions">
                <a href="<?php echo esc_url(home_url('/main-admin')); ?>" class="cms-btn cms-btn-primary">
                    <i class="dashicons dashicons-groups"></i> Manage Main Admins
                </a>
                <a href="<?php echo esc_url(home_url('/admin-list')); ?>" class="cms-btn cms-btn-secondary">
                    <i class="dashicons dashicons-admin-users"></i> Manage Admins
                </a>
                <?php echo do_shortcode('[cms_logout_link text="Logout" class="cms-btn cms-btn-danger"]'); ?>
            </div>
        </div>

        <!-- Quick Stats -->
        <?php if ($atts['show_stats'] === 'yes'): ?>
        <div class="cms-dashboard-stats">
            <?php echo cms_main_admin_get_dashboard_stats(); ?>
        </div>
        <?php endif; ?>

        <!-- Management Grid -->
        <div class="cms-management-grid">
            <!-- Main Admin Management -->
            <div class="cms-management-card">
                <div class="cms-card-header">
                    <i class="dashicons dashicons-groups"></i>
                    <h2>Main Administrators</h2>
                </div>
                <div class="cms-card-content">
                    <p>Manage main administrators with full system access.</p>
                    <div class="cms-card-actions">
                        <a href="<?php echo esc_url(home_url('/main-admin')); ?>" class="cms-btn cms-btn-primary">View All</a>
                        <a href="<?php echo esc_url(home_url('/add-admin')); ?>" class="cms-btn cms-btn-success">Add New</a>
                    </div>
                </div>
            </div>

            <!-- Admin Management -->
            <div class="cms-management-card">
                <div class="cms-card-header">
                    <i class="dashicons dashicons-admin-users"></i>
                    <h2>Administrators</h2>
                </div>
                <div class="cms-card-content">
                    <p>Manage regular administrators with limited access.</p>
                    <div class="cms-card-actions">
                        <a href="<?php echo esc_url(home_url('/admin-list')); ?>" class="cms-btn cms-btn-primary">View All</a>
                        <a href="<?php echo esc_url(home_url('/add-admin2')); ?>" class="cms-btn cms-btn-success">Add New</a>
                    </div>
                </div>
            </div>

            <!-- Employee Management -->
            <div class="cms-management-card">
                <div class="cms-card-header">
                    <i class="dashicons dashicons-businessman"></i>
                    <h2>Employees</h2>
                </div>
                <div class="cms-card-content">
                    <p>Manage employees and their assignments.</p>
                    <div class="cms-card-actions">
                        <a href="<?php echo esc_url(home_url('/employee-list')); ?>" class="cms-btn cms-btn-primary">View All</a>
                        <a href="<?php echo esc_url(home_url('/add-employee')); ?>" class="cms-btn cms-btn-success">Add New</a>
                    </div>
                </div>
            </div>

            <!-- Corporate Accounts -->
            <div class="cms-management-card">
                <div class="cms-card-header">
                    <i class="dashicons dashicons-building"></i>
                    <h2>Corporate Accounts</h2>
                </div>
                <div class="cms-card-content">
                    <p>Manage corporate accounts and their details.</p>
                    <div class="cms-card-actions">
                        <a href="<?php echo esc_url(home_url('/corp-accounts')); ?>" class="cms-btn cms-btn-primary">View All</a>
                        <a href="<?php echo esc_url(home_url('/add-corp-account')); ?>" class="cms-btn cms-btn-success">Add New</a>
                    </div>
                </div>
            </div>

            <!-- Assignments -->
            <div class="cms-management-card">
                <div class="cms-card-header">
                    <i class="dashicons dashicons-networking"></i>
                    <h2>Employee Assignments</h2>
                </div>
                <div class="cms-card-content">
                    <p>Assign employees to corporate accounts.</p>
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
                    <div class="cms-card-actions">
                        <a href="<?php echo esc_url(home_url('/shift-management')); ?>" class="cms-btn cms-btn-primary">Manage Shifts</a>
                        <a href="<?php echo esc_url(home_url('/shift-history')); ?>" class="cms-btn cms-btn-secondary">View History</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activities -->
        <?php if ($atts['show_recent_activities'] === 'yes'): ?>
        <div class="cms-recent-activities">
            <h2>Recent Activities</h2>
            <?php echo cms_main_admin_get_recent_activities(); ?>
        </div>
        <?php endif; ?>

        <!-- System Health -->
        <?php if ($atts['show_system_health'] === 'yes'): ?>
        <div class="cms-system-health">
            <h2>System Health</h2>
            <?php echo cms_main_admin_get_system_health(); ?>
        </div>
        <?php endif; ?>
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
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        background: #0073aa;
        color: white;
    }

    .cms-btn-primary:hover {
        background: #005a87;
        color: white;
    }

    .cms-btn-secondary {
        background: #6c757d;
        color: white;
    }

    .cms-btn-secondary:hover {
        background: #545b62;
        color: white;
    }

    .cms-btn-success {
        background: #28a745;
        color: white;
    }

    .cms-btn-success:hover {
        background: #218838;
        color: white;
    }

    .cms-btn-danger {
        background: #dc3545;
        color: white;
    }

    .cms-btn-danger:hover {
        background: #c82333;
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
        transition: transform 0.3s, box-shadow 0.3s;
    }

    .cms-management-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
    }

    .cms-card-header {
        padding: 20px;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-bottom: 1px solid #dee2e6;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .cms-card-header i {
        font-size: 24px;
        color: #0073aa;
    }

    .cms-card-header h2 {
        margin: 0;
        font-size: 18px;
        color: #333;
    }

    .cms-card-content {
        padding: 20px;
    }

    .cms-card-content p {
        margin: 0 0 15px;
        color: #666;
    }

    .cms-card-actions {
        display: flex;
        gap: 10px;
    }

    .cms-recent-activities,
    .cms-system-health {
        background: white;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        padding: 25px;
        margin-bottom: 30px;
    }

    .cms-recent-activities h2,
    .cms-system-health h2 {
        margin-top: 0;
        margin-bottom: 20px;
        font-size: 22px;
        color: #333;
        border-bottom: 2px solid #0073aa;
        padding-bottom: 10px;
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
    }
    </style>
    <?php

    return ob_get_clean();
}
add_shortcode('cms_main_admin_dashboard', 'cms_main_admin_dashboard_shortcode');

/**
 * Get dashboard statistics for main admin
 */
function cms_main_admin_get_dashboard_stats() {
    global $wpdb;
    
    $tables = [
        'main_admins' => $wpdb->prefix . 'cms_main_admin',
        'admins' => $wpdb->prefix . 'cms_admin',
        'employees' => $wpdb->prefix . 'cms_employee',
        'corp_accounts' => $wpdb->prefix . 'cms_corporate_account',
        'assignments' => $wpdb->prefix . 'cms_emp_corp_assign'
    ];

    $stats = [];
    
    // Get counts from each table
    foreach ($tables as $key => $table) {
        $count = $wpdb->get_var("SELECT COUNT(*) FROM $table");
        $stats[$key] = $count ?: 0;
    }

    // Get recent login count (last 24 hours)
    $login_table = $wpdb->prefix . 'cms_login_logs';
    $recent_logins = $wpdb->get_var(
        "SELECT COUNT(*) FROM $login_table 
         WHERE login_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR) 
         AND status = 'success'"
    );

    ob_start();
    ?>
    <div class="cms-stats-grid">
        <div class="cms-stat-card">
            <div class="cms-stat-icon">
                <i class="dashicons dashicons-groups"></i>
            </div>
            <div class="cms-stat-content">
                <h3><?php echo esc_html($stats['main_admins']); ?></h3>
                <p>Main Admins</p>
            </div>
        </div>

        <div class="cms-stat-card">
            <div class="cms-stat-icon">
                <i class="dashicons dashicons-admin-users"></i>
            </div>
            <div class="cms-stat-content">
                <h3><?php echo esc_html($stats['admins']); ?></h3>
                <p>Admins</p>
            </div>
        </div>

        <div class="cms-stat-card">
            <div class="cms-stat-icon">
                <i class="dashicons dashicons-businessman"></i>
            </div>
            <div class="cms-stat-content">
                <h3><?php echo esc_html($stats['employees']); ?></h3>
                <p>Employees</p>
            </div>
        </div>

        <div class="cms-stat-card">
            <div class="cms-stat-icon">
                <i class="dashicons dashicons-building"></i>
            </div>
            <div class="cms-stat-content">
                <h3><?php echo esc_html($stats['corp_accounts']); ?></h3>
                <p>Corporate Accounts</p>
            </div>
        </div>

        <div class="cms-stat-card">
            <div class="cms-stat-icon">
                <i class="dashicons dashicons-networking"></i>
            </div>
            <div class="cms-stat-content">
                <h3><?php echo esc_html($stats['assignments']); ?></h3>
                <p>Assignments</p>
            </div>
        </div>

        <div class="cms-stat-card">
            <div class="cms-stat-icon">
                <i class="dashicons dashicons-clock"></i>
            </div>
            <div class="cms-stat-content">
                <h3><?php echo esc_html($recent_logins); ?></h3>
                <p>Recent Logins (24h)</p>
            </div>
        </div>
    </div>

    <style>
    .cms-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 20px;
        margin-bottom: 30px;
    }

    .cms-stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
 * Get recent activities
 */
function cms_main_admin_get_recent_activities() {
    global $wpdb;
    
    $login_table = $wpdb->prefix . 'cms_login_logs';
    
    $recent_logins = $wpdb->get_results(
        "SELECT * FROM $login_table 
         ORDER BY login_time DESC 
         LIMIT 10"
    );

    if (empty($recent_logins)) {
        return '<p>No recent activities found.</p>';
    }

    ob_start();
    ?>
    <div class="cms-activities-list">
        <table class="cms-activities-table">
            <thead>
                <tr>
                    <th>Username</th>
                    <th>Status</th>
                    <th>IP Address</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recent_logins as $login): ?>
                <tr>
                    <td><?php echo esc_html($login->username); ?></td>
                    <td>
                        <span class="cms-status-badge cms-status-<?php echo esc_attr($login->status); ?>">
                            <?php echo esc_html($login->status); ?>
                        </span>
                    </td>
                    <td><?php echo esc_html($login->ip_address); ?></td>
                    <td><?php echo esc_html(date('Y-m-d H:i:s', strtotime($login->login_time))); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <style>
    .cms-activities-table {
        width: 100%;
        border-collapse: collapse;
    }

    .cms-activities-table th {
        background: #f8f9fa;
        padding: 12px;
        text-align: left;
        font-weight: 600;
        color: #333;
    }

    .cms-activities-table td {
        padding: 12px;
        border-bottom: 1px solid #dee2e6;
    }

    .cms-status-badge {
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 12px;
        font-weight: 600;
    }

    .cms-status-success {
        background: #d4edda;
        color: #155724;
    }

    .cms-status-error {
        background: #f8d7da;
        color: #721c24;
    }

    .cms-activities-table tr:hover {
        background: #f8f9fa;
    }
    </style>
    <?php
    return ob_get_clean();
}

/**
 * Get system health status
 */
function cms_main_admin_get_system_health() {
    global $wpdb;
    
    $health_checks = [];
    
    // Check database connection
    $health_checks[] = [
        'name' => 'Database Connection',
        'status' => $wpdb->check_connection() ? 'good' : 'critical',
        'message' => $wpdb->check_connection() ? 'Connected successfully' : 'Connection failed'
    ];
    
    // Check required tables
    $required_tables = [
        'cms_main_admin',
        'cms_admin',
        'cms_employee',
        'cms_corporate_account',
        'cms_emp_corp_assign'
    ];
    
    foreach ($required_tables as $table) {
        $table_name = $wpdb->prefix . $table;
        $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") === $table_name;
        
        $health_checks[] = [
            'name' => "Table: $table",
            'status' => $table_exists ? 'good' : 'critical',
            'message' => $table_exists ? 'Exists' : 'Missing'
        ];
    }
    
    // Check session configuration
    $health_checks[] = [
        'name' => 'PHP Sessions',
        'status' => session_status() === PHP_SESSION_ACTIVE ? 'good' : 'warning',
        'message' => session_status() === PHP_SESSION_ACTIVE ? 'Active' : 'Not active'
    ];
    
    ob_start();
    ?>
    <div class="cms-health-grid">
        <?php foreach ($health_checks as $check): ?>
        <div class="cms-health-item cms-health-<?php echo esc_attr($check['status']); ?>">
            <div class="cms-health-header">
                <span class="cms-health-name"><?php echo esc_html($check['name']); ?></span>
                <span class="cms-health-status"><?php echo esc_html($check['status']); ?></span>
            </div>
            <div class="cms-health-message"><?php echo esc_html($check['message']); ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <style>
    .cms-health-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
        gap: 15px;
    }

    .cms-health-item {
        padding: 15px;
        border-radius: 5px;
        background: #f8f9fa;
        border-left: 4px solid;
    }

    .cms-health-good {
        border-left-color: #28a745;
    }

    .cms-health-good .cms-health-status {
        background: #28a745;
        color: white;
    }

    .cms-health-warning {
        border-left-color: #ffc107;
    }

    .cms-health-warning .cms-health-status {
        background: #ffc107;
        color: #333;
    }

    .cms-health-critical {
        border-left-color: #dc3545;
    }

    .cms-health-critical .cms-health-status {
        background: #dc3545;
        color: white;
    }

    .cms-health-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 8px;
    }

    .cms-health-name {
        font-weight: 600;
        color: #333;
    }

    .cms-health-status {
        padding: 3px 8px;
        border-radius: 3px;
        font-size: 11px;
        text-transform: uppercase;
        font-weight: 600;
    }

    .cms-health-message {
        color: #666;
        font-size: 13px;
    }
    </style>
    <?php
    return ob_get_clean();
}

/**
 * Login required message
 */
function cms_get_login_required_message() {
    ob_start();
    ?>
    <div class="cms-login-required">
        <div class="cms-message info">
            <i class="dashicons dashicons-lock"></i>
            <p>You need to be logged in to access this page.</p>
            <a href="<?php echo esc_url(home_url('/login')); ?>" class="cms-btn cms-btn-primary">
                Go to Login
            </a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Access denied message
 */
function cms_get_access_denied_message() {
    ob_start();
    ?>
    <div class="cms-access-denied">
        <div class="cms-message error">
            <i class="dashicons dashicons-warning"></i>
            <p>You do not have permission to access this page.</p>
            <a href="<?php echo esc_url(home_url('/')); ?>" class="cms-btn cms-btn-secondary">
                Go to Homepage
            </a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}