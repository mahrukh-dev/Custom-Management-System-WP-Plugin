<?php
/**
 * CMS Employee Dashboard Shortcode
 * Complete dashboard for employees to manage shifts and requests
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
    // Parse attributes
    $atts = shortcode_atts(
        array(
            'title' => 'Employee Dashboard',
            'welcome_message' => 'Welcome to your dashboard',
            'class' => '',
            'show_history' => 'yes',
            'history_limit' => 10,
            'timezone' => 'America/New_York',
            'grace_period' => 20 // minutes of grace period before requiring request
        ),
        $atts,
        'cms_employee_dashboard'
    );
    
    // Set timezone
    date_default_timezone_set($atts['timezone']);
    
    // Get current employee (in real implementation, this would be from session)
    $current_employee = get_current_employee_for_demo();
    
    // Get today's shift assignment
    $today_shift = get_today_shift_assignment($current_employee['username']);
    
    // Get today's shift history
    $today_history = get_today_shift_history($current_employee['username']);
    
    // Get pending requests
    $pending_requests = get_employee_requests($current_employee['username'], 'pending');
    
    // Check if currently logged in
    $is_logged_in = $today_history && !$today_history['actual_logout_time'];
    
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
    
    /* Shift Assignment Card */
    .cms-dash-shift-card {
        background: linear-gradient(145deg, #f0f8ff, #ffffff);
        border: 2px solid var(--dash-primary-light);
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 30px;
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .cms-dash-shift-info {
        display: flex;
        flex-direction: column;
        gap: 10px;
    }
    
    .cms-dash-shift-label {
        font-size: 14px;
        color: #6c7a89;
        text-transform: uppercase;
        letter-spacing: 1px;
        font-weight: 600;
    }
    
    .cms-dash-shift-times {
        display: flex;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    }
    
    .cms-dash-shift-time-box {
        background: white;
        border: 2px solid var(--dash-primary-light);
        border-radius: 12px;
        padding: 15px 25px;
        text-align: center;
        min-width: 150px;
    }
    
    .cms-dash-shift-time-label {
        font-size: 12px;
        color: #6c7a89;
        margin-bottom: 5px;
    }
    
    .cms-dash-shift-time-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--dash-primary-dark);
        font-family: monospace;
    }
    
    .cms-dash-shift-arrow {
        font-size: 32px;
        color: var(--dash-primary-light);
    }
    
    /* Action Buttons */
    .cms-dash-actions {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        justify-content: center;
    }
    
    .cms-dash-btn {
        padding: 18px 36px;
        border: none;
        border-radius: 50px;
        font-size: 18px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    
    .cms-dash-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none !important;
        box-shadow: none !important;
    }
    
    .cms-dash-btn-login {
        background: linear-gradient(145deg, var(--dash-secondary), var(--dash-secondary-dark));
        color: white;
    }
    
    .cms-dash-btn-login:hover:not(:disabled) {
        background: linear-gradient(145deg, var(--dash-secondary-dark), #229954);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(46,204,113,0.3);
    }
    
    .cms-dash-btn-logout {
        background: linear-gradient(145deg, var(--dash-danger), #c0392b);
        color: white;
    }
    
    .cms-dash-btn-logout:hover:not(:disabled) {
        background: linear-gradient(145deg, #c0392b, #a93226);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(231,76,60,0.3);
    }
    
    .cms-dash-btn-request {
        background: linear-gradient(145deg, var(--dash-warning), #e67e22);
        color: white;
    }
    
    /* Request Modal */
    .cms-request-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
    }
    
    .cms-request-modal-content {
        background: white;
        padding: 30px;
        border-radius: 20px;
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .cms-request-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--dash-gray-light);
    }
    
    .cms-request-modal-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--dash-warning);
        margin: 0;
    }
    
    .cms-request-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: var(--dash-gray);
    }
    
    .cms-request-form-group {
        margin-bottom: 20px;
    }
    
    .cms-request-form-label {
        display: block;
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 8px;
    }
    
    .cms-request-form-control {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 15px;
        transition: all 0.2s ease;
    }
    
    .cms-request-form-control:focus {
        outline: none;
        border-color: var(--dash-warning);
        box-shadow: 0 0 0 3px rgba(243,156,18,0.05);
    }
    
    .cms-request-form-textarea {
        width: 100%;
        padding: 12px 15px;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 15px;
        min-height: 100px;
        resize: vertical;
    }
    
    .cms-request-info {
        background: #f0f8ff;
        padding: 15px;
        border-radius: 10px;
        margin-bottom: 20px;
        font-size: 14px;
    }
    
    .cms-request-info-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 8px;
    }
    
    .cms-request-info-label {
        font-weight: 600;
        color: var(--dash-primary-dark);
    }
    
    .cms-request-info-value {
        color: #2c3e50;
    }
    
    .cms-request-info-value.warning {
        color: var(--dash-warning);
        font-weight: 600;
    }
    
    .cms-request-form-actions {
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        margin-top: 25px;
    }
    
    .cms-request-btn {
        padding: 12px 24px;
        border: none;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .cms-request-btn.submit {
        background: var(--dash-warning);
        color: white;
    }
    
    .cms-request-btn.submit:hover {
        background: #e67e22;
        transform: translateY(-1px);
    }
    
    .cms-request-btn.cancel {
        background: #e2e8f0;
        color: #4a5568;
    }
    
    .cms-request-btn.cancel:hover {
        background: #cbd5e0;
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
    
    /* Today's History Section */
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
    
    .cms-dash-highlight {
        background: #f0f8ff;
        font-weight: 600;
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
    
    .cms-dash-status-badge.pending {
        background: #fff3cd;
        color: #856404;
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
        
        .cms-dash-shift-card {
            flex-direction: column;
            text-align: center;
        }
        
        .cms-dash-shift-times {
            justify-content: center;
        }
        
        .cms-dash-shift-arrow {
            display: none;
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
    }
    
    @media (max-width: 480px) {
        .cms-dash-stats-grid {
            grid-template-columns: 1fr;
        }
        
        .cms-dash-shift-time-box {
            min-width: 120px;
            padding: 10px 15px;
        }
        
        .cms-dash-shift-time-value {
            font-size: 20px;
        }
        
        .cms-dash-actions {
            width: 100%;
        }
        
        .cms-dash-btn {
            width: 100%;
            justify-content: center;
        }
    }
    </style>
    
    <div class="cms-dash-container <?php echo esc_attr($atts['class']); ?>" data-grace-period="<?php echo esc_attr($atts['grace_period']); ?>">
        
        <!-- Header with Live Clock -->
        <div class="cms-dash-header">
            <div class="cms-dash-title-section">
                <div class="cms-dash-avatar">
                    <?php echo strtoupper(substr($current_employee['name'], 0, 1)); ?>
                </div>
                <div class="cms-dash-welcome">
                    <h1 class="cms-dash-title"><?php echo esc_html($atts['title']); ?></h1>
                    <p class="cms-dash-welcome-message">
                        <?php echo esc_html($atts['welcome_message']); ?>, <strong><?php echo esc_html($current_employee['name']); ?></strong>
                    </p>
                    <p style="color: #6c7a89; font-size: 13px; margin: 5px 0 0 0;">
                        @<?php echo esc_html($current_employee['username']); ?> • <?php echo esc_html($current_employee['position']); ?> • <?php echo esc_html($current_employee['corp_team']); ?>
                    </p>
                </div>
            </div>
            
            <div class="cms-dash-date-time" id="live-datetime">
                <div class="cms-dash-current-date" id="current-date"></div>
                <div class="cms-dash-current-time" id="current-time"></div>
            </div>
        </div>
        
        <?php
        // Display messages
        if (isset($_GET['shift']) && $_GET['shift'] === 'login_success') {
            echo '<div class="cms-dash-message success">Shift started successfully! Welcome to work.</div>';
        }
        if (isset($_GET['shift']) && $_GET['shift'] === 'logout_success') {
            echo '<div class="cms-dash-message success">Shift ended successfully! Have a good day.</div>';
        }
        if (isset($_GET['shift']) && $_GET['shift'] === 'error') {
            echo '<div class="cms-dash-message error">Operation failed. Please try again.</div>';
        }
        if (isset($_GET['shift']) && $_GET['shift'] === 'already_logged_in') {
            echo '<div class="cms-dash-message warning">You are already logged in for today.</div>';
        }
        if (isset($_GET['shift']) && $_GET['shift'] === 'not_logged_in') {
            echo '<div class="cms-dash-message warning">No active shift found to logout.</div>';
        }
        if (isset($_GET['request']) && $_GET['request'] === 'submitted') {
            echo '<div class="cms-dash-message info">Your request has been submitted successfully and is pending approval.</div>';
        }
        if (isset($_GET['request']) && $_GET['request'] === 'error') {
            echo '<div class="cms-dash-message error">Failed to submit request. Please try again.</div>';
        }
        ?>
        
        <!-- Statistics Cards -->
        <div class="cms-dash-stats-grid">
            <div class="cms-dash-stat-card">
                <div class="cms-dash-stat-value"><?php echo get_total_hours_this_week($current_employee['username']); ?></div>
                <div class="cms-dash-stat-label">Hours This Week</div>
            </div>
            <div class="cms-dash-stat-card">
                <div class="cms-dash-stat-value"><?php echo get_total_hours_this_month($current_employee['username']); ?></div>
                <div class="cms-dash-stat-label">Hours This Month</div>
            </div>
            <div class="cms-dash-stat-card">
                <div class="cms-dash-stat-value"><?php echo get_days_worked_this_month($current_employee['username']); ?></div>
                <div class="cms-dash-stat-label">Days Worked</div>
            </div>
            <div class="cms-dash-stat-card">
                <div class="cms-dash-stat-value"><?php echo get_pending_requests_count($current_employee['username']); ?></div>
                <div class="cms-dash-stat-label">Pending Requests</div>
            </div>
        </div>
        
        <!-- Today's Shift Assignment -->
        <div class="cms-dash-shift-card">
            <div class="cms-dash-shift-info">
                <div class="cms-dash-shift-label">TODAY'S SCHEDULED SHIFT</div>
                <div class="cms-dash-shift-times">
                    <div class="cms-dash-shift-time-box">
                        <div class="cms-dash-shift-time-label">Start Time</div>
                        <div class="cms-dash-shift-time-value"><?php echo $today_shift ? $today_shift['start_time'] : '--:--'; ?></div>
                    </div>
                    <div class="cms-dash-shift-arrow">→</div>
                    <div class="cms-dash-shift-time-box">
                        <div class="cms-dash-shift-time-label">End Time</div>
                        <div class="cms-dash-shift-time-value"><?php echo $today_shift ? $today_shift['end_time'] : '--:--'; ?></div>
                    </div>
                </div>
            </div>
            
            <div class="cms-dash-actions">
                <form method="post" action="" style="display: inline;">
                    <?php wp_nonce_field('cms_shift_action', 'cms_shift_nonce'); ?>
                    <input type="hidden" name="shift_action" value="login">
                    <input type="hidden" name="emp_username" value="<?php echo esc_attr($current_employee['username']); ?>">
                    <input type="hidden" name="scheduled_start" value="<?php echo esc_attr($today_shift['start_time']); ?>">
                    <button type="submit" name="cms_shift_submit" class="cms-dash-btn cms-dash-btn-login" <?php echo $is_logged_in ? 'disabled' : ''; ?>>
                        <span>🔓</span> Start Shift
                    </button>
                </form>
                
                <form method="post" action="" style="display: inline;">
                    <?php wp_nonce_field('cms_shift_action', 'cms_shift_nonce'); ?>
                    <input type="hidden" name="shift_action" value="logout">
                    <input type="hidden" name="emp_username" value="<?php echo esc_attr($current_employee['username']); ?>">
                    <input type="hidden" name="scheduled_end" value="<?php echo esc_attr($today_shift['end_time']); ?>">
                    <button type="submit" name="cms_shift_submit" class="cms-dash-btn cms-dash-btn-logout" <?php echo !$is_logged_in ? 'disabled' : ''; ?>>
                        <span>🔒</span> End Shift
                    </button>
                </form>
                
                <button type="button" class="cms-dash-btn cms-dash-btn-request" onclick="showRequestModal()">
                    <span>📝</span> New Request
                </button>
            </div>
        </div>
        
        <!-- Requests Section -->
        <?php
        $all_requests = get_employee_requests($current_employee['username']);
        if (!empty($all_requests)):
        ?>
        <div class="cms-dash-requests-section">
            <h3 class="cms-dash-requests-title">
                <span>📋</span> My Requests
            </h3>
            
            <table class="cms-requests-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Request</th>
                        <th>Reason</th>
                        <th>Time</th>
                        <th>Status</th>
                        <th>Approved By</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($all_requests as $request): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($request['date'])); ?></td>
                        <td><?php echo esc_html(ucfirst($request['type'])); ?></td>
                        <td><?php echo esc_html($request['request']); ?></td>
                        <td><?php echo esc_html($request['reason']); ?></td>
                        <td><?php echo esc_html($request['late_time'] ?? $request['time_allowed'] ?? '-'); ?></td>
                        <td>
                            <span class="cms-request-status <?php echo esc_attr($request['status']); ?>">
                                <?php echo esc_html(ucfirst($request['status'])); ?>
                            </span>
                        </td>
                        <td><?php echo esc_html($request['admin_username'] ?? '-'); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
        
        <?php if ($atts['show_history'] === 'yes'): ?>
        <!-- Today's Shift History -->
        <div class="cms-dash-history-section">
            <h2 class="cms-dash-section-title">Today's Shift Details</h2>
            
            <?php if ($today_history): ?>
            <table class="cms-dash-history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Login Time</th>
                        <th>Logout Time</th>
                        <th>Actual Hours</th>
                        <th>Counted Hours</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="<?php echo $today_history['actual_logout_time'] ? '' : 'cms-dash-highlight'; ?>">
                        <td><strong><?php echo date('M d, Y'); ?></strong></td>
                        <td><?php echo $today_history['actual_login_time'] ? date('h:i A', strtotime($today_history['actual_login_time'])) : '--:--'; ?></td>
                        <td><?php echo $today_history['actual_logout_time'] ? date('h:i A', strtotime($today_history['actual_logout_time'])) : '<span style="color: #e67e22;">Active</span>'; ?></td>
                        <td>
                            <?php if ($today_history['actual_hours'] !== null): ?>
                                <span class="cms-dash-hours-badge actual">
                                    <?php echo $today_history['actual_hours']; ?>h <?php echo $today_history['actual_mins']; ?>m
                                </span>
                            <?php else: ?>
                                --
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($today_history['counted_hours'] !== null): ?>
                                <span class="cms-dash-hours-badge counted">
                                    <?php echo $today_history['counted_hours']; ?>h <?php echo $today_history['counted_mins']; ?>m
                                </span>
                            <?php else: ?>
                                --
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="cms-dash-status-badge <?php echo $today_history['actual_logout_time'] ? 'completed' : 'active'; ?>">
                                <?php echo $today_history['actual_logout_time'] ? 'Completed' : 'Active'; ?>
                            </span>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php else: ?>
            <div style="text-align: center; padding: 40px; background: #f8fafc; border-radius: 16px; color: #6c7a89;">
                <span style="font-size: 48px; display: block; margin-bottom: 15px;">📋</span>
                No shift history for today. Start your shift to begin tracking.
            </div>
            <?php endif; ?>
            
            <!-- Recent History (Last 5 days) -->
            <?php
            $recent_history = get_recent_shift_history($current_employee['username'], 5);
            if (!empty($recent_history)):
            ?>
            <h3 style="font-size: 18px; color: #2c3e50; margin: 30px 0 15px 0;">Recent Shifts</h3>
            <table class="cms-dash-history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Login</th>
                        <th>Logout</th>
                        <th>Actual Hours</th>
                        <th>Counted Hours</th>
                        <th>Difference</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recent_history as $history): ?>
                    <tr>
                        <td><?php echo date('M d, Y', strtotime($history['date'])); ?></td>
                        <td><?php echo $history['actual_login_time'] ? date('h:i A', strtotime($history['actual_login_time'])) : '--'; ?></td>
                        <td><?php echo $history['actual_logout_time'] ? date('h:i A', strtotime($history['actual_logout_time'])) : '--'; ?></td>
                        <td>
                            <?php if ($history['actual_hours'] !== null): ?>
                                <?php echo $history['actual_hours']; ?>h <?php echo $history['actual_mins']; ?>m
                            <?php else: ?>
                                --
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($history['counted_hours'] !== null): ?>
                                <?php echo $history['counted_hours']; ?>h <?php echo $history['counted_mins']; ?>m
                            <?php else: ?>
                                --
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            if ($history['actual_hours'] !== null && $history['counted_hours'] !== null) {
                                $total_actual = $history['actual_hours'] * 60 + $history['actual_mins'];
                                $total_counted = $history['counted_hours'] * 60 + $history['counted_mins'];
                                $diff = $total_counted - $total_actual;
                                $color = $diff >= 0 ? '#27ae60' : '#e74c3c';
                                echo '<span style="color: ' . $color . '; font-weight: 600;">';
                                echo ($diff >= 0 ? '+' : '') . $diff . ' min';
                                echo '</span>';
                            } else {
                                echo '--';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Request Modal -->
    <div id="cms-request-modal" class="cms-request-modal">
        <div class="cms-request-modal-content">
            <div class="cms-request-modal-header">
                <h3 class="cms-request-modal-title">Submit Request</h3>
                <button class="cms-request-modal-close" onclick="closeRequestModal()">×</button>
            </div>
            
            <form method="post" action="" id="cms-request-form">
                <?php wp_nonce_field('cms_request_action', 'cms_request_nonce'); ?>
                <input type="hidden" name="request_action" value="submit_request">
                <input type="hidden" name="emp_username" value="<?php echo esc_attr($current_employee['username']); ?>">
                
                <div class="cms-request-form-group">
                    <label class="cms-request-form-label">Request Type</label>
                    <select name="request_type" id="request_type" class="cms-request-form-control" required onchange="updateRequestFields()">
                        <option value="">Select Type</option>
                        <option value="early">Early Leave Request</option>
                        <option value="late">Late Arrival Request</option>
                        <option value="absent">Absence Request</option>
                        <option value="other">Other Request</option>
                    </select>
                </div>
                
                <div class="cms-request-form-group">
                    <label class="cms-request-form-label">Date</label>
                    <input type="date" name="request_date" class="cms-request-form-control" required>
                </div>
                
                <div id="time-field-container" class="cms-request-form-group" style="display: none;">
                    <label class="cms-request-form-label" id="time-field-label">Time</label>
                    <input type="time" name="request_time" id="request_time" class="cms-request-form-control">
                </div>
                
                <div class="cms-request-form-group">
                    <label class="cms-request-form-label">Reason</label>
                    <textarea name="request_reason" class="cms-request-form-textarea" required placeholder="Please provide detailed reason for your request..."></textarea>
                </div>
                
                <div class="cms-request-form-actions">
                    <button type="button" class="cms-request-btn cancel" onclick="closeRequestModal()">Cancel</button>
                    <button type="submit" name="cms_request_submit" class="cms-request-btn submit">Submit Request</button>
                </div>
            </form>
        </div>
    </div>
    
    <script>
    // Live clock update
    function updateClock() {
        var now = new Date();
        var options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
        var dateStr = now.toLocaleDateString('en-US', options);
        var timeStr = now.toLocaleTimeString('en-US', { hour12: true, hour: '2-digit', minute: '2-digit', second: '2-digit' });
        
        document.getElementById('current-date').textContent = dateStr;
        document.getElementById('current-time').textContent = timeStr;
    }
    
    updateClock();
    setInterval(updateClock, 1000);
    
    // Request Modal Functions
    function showRequestModal() {
        document.getElementById('cms-request-modal').style.display = 'flex';
    }
    
    function closeRequestModal() {
        document.getElementById('cms-request-modal').style.display = 'none';
        document.getElementById('cms-request-form').reset();
        document.getElementById('time-field-container').style.display = 'none';
    }
    
    function updateRequestFields() {
        var type = document.getElementById('request_type').value;
        var timeField = document.getElementById('time-field-container');
        var timeLabel = document.getElementById('time-field-label');
        
        if (type === 'early') {
            timeField.style.display = 'block';
            timeLabel.textContent = 'Early Leave Time';
            document.getElementById('request_time').required = true;
        } else if (type === 'late') {
            timeField.style.display = 'block';
            timeLabel.textContent = 'Late Arrival Time';
            document.getElementById('request_time').required = true;
        } else {
            timeField.style.display = 'none';
            document.getElementById('request_time').required = false;
        }
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        var modal = document.getElementById('cms-request-modal');
        if (event.target === modal) {
            closeRequestModal();
        }
    }
    
    // Add loading state to forms
    jQuery(document).ready(function($) {
        $('form').on('submit', function() {
            var button = $(this).find('button[type="submit"]');
            button.addClass('loading').prop('disabled', true);
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_employee_dashboard', 'cms_employee_dashboard_shortcode');
add_shortcode(CMS_EMPLOYEE_DASHBOARD_SHORTCODE, 'cms_employee_dashboard_shortcode');

/**
 * Get current employee for demo
 */
function get_current_employee_for_demo() {
    // In real implementation, this would come from session/logged in user
    return array(
        'id' => 201,
        'username' => 'john_employee',
        'name' => 'John Smith',
        'email' => 'john.smith@company.com',
        'position' => 'Senior Software Engineer',
        'corp_team' => 'IT'
    );
}

/**
 * Get today's shift assignment
 */
function get_today_shift_assignment($username) {
    // Mock data - in real implementation, this would come from database
    $shifts = array(
        'john_employee' => array(
            'start_time' => '09:00',
            'end_time' => '17:00'
        ),
        'emily_jones' => array(
            'start_time' => '08:30',
            'end_time' => '16:30'
        ),
        'david_miller' => array(
            'start_time' => '10:00',
            'end_time' => '18:00'
        )
    );
    
    return isset($shifts[$username]) ? $shifts[$username] : array('start_time' => '09:00', 'end_time' => '17:00');
}

/**
 * Get today's shift history
 */
function get_today_shift_history($username) {
    // Mock data - in real implementation, this would come from database
    $histories = array(
        'john_employee' => array(
            'username' => 'john_employee',
            'date' => date('Y-m-d'),
            'actual_login_time' => '09:05:00',
            'actual_logout_time' => null,
            'actual_hours' => null,
            'actual_mins' => null,
            'counted_login_time' => '09:00:00',
            'counted_logout_time' => '17:00:00',
            'counted_hours' => 8,
            'counted_mins' => 0
        )
    );
    
    return isset($histories[$username]) ? $histories[$username] : null;
}

/**
 * Get recent shift history
 */
function get_recent_shift_history($username, $limit = 5) {
    // Mock data - in real implementation, this would come from database
    $all_histories = array(
        'john_employee' => array(
            array(
                'date' => '2024-03-15',
                'actual_login_time' => '09:02:00',
                'actual_logout_time' => '17:03:00',
                'actual_hours' => 8,
                'actual_mins' => 1,
                'counted_login_time' => '09:00:00',
                'counted_logout_time' => '17:00:00',
                'counted_hours' => 8,
                'counted_mins' => 0
            ),
            array(
                'date' => '2024-03-14',
                'actual_login_time' => '08:55:00',
                'actual_logout_time' => '17:05:00',
                'actual_hours' => 8,
                'actual_mins' => 10,
                'counted_login_time' => '09:00:00',
                'counted_logout_time' => '17:00:00',
                'counted_hours' => 8,
                'counted_mins' => 0
            ),
            array(
                'date' => '2024-03-13',
                'actual_login_time' => '09:10:00',
                'actual_logout_time' => '18:00:00',
                'actual_hours' => 8,
                'actual_mins' => 50,
                'counted_login_time' => '09:00:00',
                'counted_logout_time' => '17:00:00',
                'counted_hours' => 8,
                'counted_mins' => 0
            )
        )
    );
    
    return isset($all_histories[$username]) ? array_slice($all_histories[$username], 0, $limit) : array();
}

/**
 * Get employee requests
 */
function get_employee_requests($username, $status = null) {
    // Mock data - in real implementation, this would come from database
    $all_requests = array(
        'john_employee' => array(
            array(
                'id' => 1,
                'username' => 'john_employee',
                'type' => 'late',
                'request' => 'Late Arrival',
                'reason' => 'Traffic jam on highway',
                'date' => '2024-03-10',
                'late_time' => '09:45',
                'time_allowed' => null,
                'status' => 'approved',
                'admin_username' => 'admin_jane'
            ),
            array(
                'id' => 2,
                'username' => 'john_employee',
                'type' => 'early',
                'request' => 'Early Leave',
                'reason' => 'Doctor appointment',
                'date' => '2024-03-12',
                'late_time' => '16:00',
                'time_allowed' => null,
                'status' => 'pending',
                'admin_username' => null
            ),
            array(
                'id' => 3,
                'username' => 'john_employee',
                'type' => 'absent',
                'request' => 'Absence',
                'reason' => 'Family emergency',
                'date' => '2024-03-08',
                'late_time' => null,
                'time_allowed' => '8:00',
                'status' => 'approved',
                'admin_username' => 'admin_mike'
            ),
            array(
                'id' => 4,
                'username' => 'john_employee',
                'type' => 'other',
                'request' => 'Work from home',
                'reason' => 'Internet outage at home',
                'date' => '2024-03-05',
                'late_time' => null,
                'time_allowed' => '4:00',
                'status' => 'rejected',
                'admin_username' => 'admin_sarah'
            )
        )
    );
    
    $requests = isset($all_requests[$username]) ? $all_requests[$username] : array();
    
    if ($status) {
        $requests = array_filter($requests, function($r) use ($status) {
            return $r['status'] === $status;
        });
    }
    
    // Sort by date descending
    usort($requests, function($a, $b) {
        return strcmp($b['date'], $a['date']);
    });
    
    return $requests;
}

/**
 * Get pending requests count
 */
function get_pending_requests_count($username) {
    $requests = get_employee_requests($username, 'pending');
    return count($requests);
}

/**
 * Get total hours this week
 */
function get_total_hours_this_week($username) {
    return '38.5';
}

/**
 * Get total hours this month
 */
function get_total_hours_this_month($username) {
    return '142.0';
}

/**
 * Get days worked this month
 */
function get_days_worked_this_month($username) {
    return '18';
}

/**
 * Handle shift login/logout
 */
function cms_handle_shift_action() {
    if (isset($_POST['cms_shift_submit'])) {
        
        if (!isset($_POST['cms_shift_nonce']) || !wp_verify_nonce($_POST['cms_shift_nonce'], 'cms_shift_action')) {
            wp_redirect(add_query_arg('shift', 'error', wp_get_referer()));
            exit;
        }
        
        $action = sanitize_text_field($_POST['shift_action']);
        $username = sanitize_user($_POST['emp_username']);
        $current_time = current_time('mysql');
        $grace_period = 20; // minutes
        
        if ($action === 'login') {
            // Check if already logged in
            $today_history = get_today_shift_history($username);
            if ($today_history && !$today_history['actual_logout_time']) {
                wp_redirect(add_query_arg('shift', 'already_logged_in', wp_get_referer()));
                exit;
            }
            
            // Check if late
            $scheduled_start = $_POST['scheduled_start'] ?? '09:00';
            $current_hour = current_time('H:i');
            
            $scheduled_timestamp = strtotime($scheduled_start);
            $current_timestamp = strtotime($current_hour);
            $minutes_late = ($current_timestamp - $scheduled_timestamp) / 60;
            
            // If late by more than grace period, automatically create pending request
            if ($minutes_late > $grace_period) {
                // Auto-create late request
                // In real implementation, insert into database
                error_log("Auto-creating late request for $username - " . round($minutes_late) . " minutes late");
            }
            
            // Here you would insert into database
            wp_redirect(add_query_arg('shift', 'login_success', wp_get_referer()));
            exit;
            
        } elseif ($action === 'logout') {
            // Check if logged in
            $today_history = get_today_shift_history($username);
            if (!$today_history || $today_history['actual_logout_time']) {
                wp_redirect(add_query_arg('shift', 'not_logged_in', wp_get_referer()));
                exit;
            }
            
            // Check if early
            $scheduled_end = $_POST['scheduled_end'] ?? '17:00';
            $current_hour = current_time('H:i');
            
            $scheduled_timestamp = strtotime($scheduled_end);
            $current_timestamp = strtotime($current_hour);
            $minutes_early = ($scheduled_timestamp - $current_timestamp) / 60;
            
            // If early by more than grace period, automatically create pending request
            if ($minutes_early > $grace_period) {
                // Auto-create early leave request
                error_log("Auto-creating early leave request for $username - " . round($minutes_early) . " minutes early");
            }
            
            // Calculate actual hours
            $login_time = strtotime($today_history['actual_login_time']);
            $logout_time = strtotime($current_time);
            $actual_seconds = $logout_time - $login_time;
            $actual_hours = floor($actual_seconds / 3600);
            $actual_mins = floor(($actual_seconds % 3600) / 60);
            
            // Here you would update database
            wp_redirect(add_query_arg('shift', 'logout_success', wp_get_referer()));
            exit;
        }
    }
}
add_action('init', 'cms_handle_shift_action');

/**
 * Handle request submission
 */
function cms_handle_request_submission() {
    if (isset($_POST['cms_request_submit'])) {
        
        if (!isset($_POST['cms_request_nonce']) || !wp_verify_nonce($_POST['cms_request_nonce'], 'cms_request_action')) {
            wp_redirect(add_query_arg('request', 'error', wp_get_referer()));
            exit;
        }
        
        $username = sanitize_user($_POST['emp_username']);
        $request_type = sanitize_text_field($_POST['request_type']);
        $request_date = sanitize_text_field($_POST['request_date']);
        $request_reason = sanitize_textarea_field($_POST['request_reason']);
        $request_time = isset($_POST['request_time']) ? sanitize_text_field($_POST['request_time']) : null;
        
        // Prepare request data
        $request_data = array(
            'username' => $username,
            'type' => $request_type,
            'request' => ucfirst($request_type) . ' Request',
            'reason' => $request_reason,
            'date' => $request_date,
            'status' => 'pending',
            'admin_username' => null,
            'submitted_at' => current_time('mysql')
        );
        
        if ($request_time) {
            if ($request_type === 'late') {
                $request_data['late_time'] = $request_time;
            } elseif ($request_type === 'early') {
                $request_data['late_time'] = $request_time; // Using same field for early leave time
            }
        }
        
        // Here you would insert into database
        // $wpdb->insert('employee_requests', $request_data);
        
        wp_redirect(add_query_arg('request', 'submitted', wp_get_referer()));
        exit;
    }
}
add_action('init', 'cms_handle_request_submission');

?>