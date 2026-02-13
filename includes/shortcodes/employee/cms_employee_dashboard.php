<?php
/**
 * CMS Employee Dashboard Shortcode
 * Complete dashboard for employees to manage shifts
 * 
 * Fields: Shift management, login/logout buttons, shift history
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
            'timezone' => 'America/New_York'
        ),
        $atts,
        'cms_employee_dashboard'
    );
    
    // Set timezone
    date_default_timezone_set($atts['timezone']);
    
    // Get current employee (in real implementation, this would be from session)
    // For demo, using a mock employee
    $current_employee = get_current_employee_for_demo();
    
    // Get today's shift assignment
    $today_shift = get_today_shift_assignment($current_employee['username']);
    
    // Get today's shift history
    $today_history = get_today_shift_history($current_employee['username']);
    
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
    
    .cms-dash-btn-break {
        background: linear-gradient(145deg, var(--dash-warning), #e67e22);
        color: white;
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
        
        .cms-dash-history-table {
            font-size: 13px;
        }
        
        .cms-dash-history-table th,
        .cms-dash-history-table td {
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
    
    <div class="cms-dash-container <?php echo esc_attr($atts['class']); ?>">
        
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
                <div class="cms-dash-stat-value"><?php echo get_overtime_hours($current_employee['username']); ?></div>
                <div class="cms-dash-stat-label">Overtime Hours</div>
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
                    <button type="submit" name="cms_shift_submit" class="cms-dash-btn cms-dash-btn-login" <?php echo $is_logged_in ? 'disabled' : ''; ?>>
                        <span>🔓</span> Start Shift
                    </button>
                </form>
                
                <form method="post" action="" style="display: inline;">
                    <?php wp_nonce_field('cms_shift_action', 'cms_shift_nonce'); ?>
                    <input type="hidden" name="shift_action" value="logout">
                    <input type="hidden" name="emp_username" value="<?php echo esc_attr($current_employee['username']); ?>">
                    <button type="submit" name="cms_shift_submit" class="cms-dash-btn cms-dash-btn-logout" <?php echo !$is_logged_in ? 'disabled' : ''; ?>>
                        <span>🔒</span> End Shift
                    </button>
                </form>
            </div>
        </div>
        
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
            'actual_logout_time' => null, // Still active
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
 * Get overtime hours
 */
function get_overtime_hours($username) {
    return '2.5';
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
        
        if ($action === 'login') {
            // Check if already logged in
            $today_history = get_today_shift_history($username);
            if ($today_history && !$today_history['actual_logout_time']) {
                wp_redirect(add_query_arg('shift', 'already_logged_in', wp_get_referer()));
                exit;
            }
            
            // Here you would insert into database
            // $wpdb->insert('shift_history', array(
            //     'username' => $username,
            //     'date' => date('Y-m-d'),
            //     'actual_login_time' => $current_time
            // ));
            
            wp_redirect(add_query_arg('shift', 'login_success', wp_get_referer()));
            exit;
            
        } elseif ($action === 'logout') {
            // Check if logged in
            $today_history = get_today_shift_history($username);
            if (!$today_history || $today_history['actual_logout_time']) {
                wp_redirect(add_query_arg('shift', 'not_logged_in', wp_get_referer()));
                exit;
            }
            
            // Calculate actual hours
            $login_time = strtotime($today_history['actual_login_time']);
            $logout_time = strtotime($current_time);
            $actual_seconds = $logout_time - $login_time;
            $actual_hours = floor($actual_seconds / 3600);
            $actual_mins = floor(($actual_seconds % 3600) / 60);
            
            // Here you would update database
            // $wpdb->update('shift_history',
            //     array(
            //         'actual_logout_time' => $current_time,
            //         'actual_hours' => $actual_hours,
            //         'actual_mins' => $actual_mins
            //     ),
            //     array(
            //         'username' => $username,
            //         'date' => date('Y-m-d')
            //     )
            // );
            
            wp_redirect(add_query_arg('shift', 'logout_success', wp_get_referer()));
            exit;
        }
    }
}
add_action('init', 'cms_handle_shift_action');

?>