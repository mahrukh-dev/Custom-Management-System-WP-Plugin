<?php
/**
 * CMS Single Employee Shift History Shortcode
 * Display complete shift history for a single employee with all fields
 * 
 * Fields: username, date, actual_login_time, actual_logout_time, actual_hours, actual_mins,
 *         counted_login_time, counted_logout_time, counted_hours, counted_mins
 * 
 * Usage: [cms_single_emp_shift_history username="john_employee"]
 * Usage: [cms_single_emp_shift_history employee_id="201" show_summary="yes" days="30"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define shortcode slug
if (!defined('CMS_SINGLE_EMP_SHIFT_HISTORY_SHORTCODE')) {
    define('CMS_SINGLE_EMP_SHIFT_HISTORY_SHORTCODE', 'cms_single_emp_shift_history');
}

/**
 * Single Employee Shift History Shortcode
 */
function cms_single_emp_shift_history_shortcode($atts) {
    // Parse attributes
    $atts = shortcode_atts(
        array(
            'username' => '',
            'employee_id' => 0,
            'title' => 'Employee Shift History',
            'show_summary' => 'yes',
            'show_chart' => 'yes',
            'show_filters' => 'yes',
            'days' => 30,
            'items_per_page' => 20,
            'class' => '',
            'no_data_message' => 'No shift history found for this employee.',
            'date_format' => 'M d, Y',
            'time_format' => 'h:i A'
        ),
        $atts,
        'cms_single_emp_shift_history'
    );
    
    // Get username from attribute or URL parameter
    $username = $atts['username'];
    if (empty($username) && $atts['employee_id'] > 0) {
        // Get username from employee ID
        $employee = get_employee_by_id($atts['employee_id']);
        $username = $employee ? $employee['username'] : '';
    }
    if (empty($username) && isset($_GET['username'])) {
        $username = sanitize_user($_GET['username']);
    }
    if (empty($username) && isset($_GET['employee_id'])) {
        $employee = get_employee_by_id(intval($_GET['employee_id']));
        $username = $employee ? $employee['username'] : '';
    }
    
    // Get date range from filters
    $filter_date_from = isset($_GET['filter_date_from']) ? sanitize_text_field($_GET['filter_date_from']) : date('Y-m-d', strtotime('-' . $atts['days'] . ' days'));
    $filter_date_to = isset($_GET['filter_date_to']) ? sanitize_text_field($_GET['filter_date_to']) : date('Y-m-d');
    
    // Get employee details
    $employee = get_employee_by_username($username);
    
    if (!$employee) {
        return '<div style="padding: 30px; background: #fff3cd; color: #856404; border-radius: 12px; text-align: center; font-size: 16px;">
            🔍 Employee not found. Please provide a valid username or employee ID.
        </div>';
    }
    
    // Get shift history for this employee
    $shift_history = get_employee_shift_history($username, $filter_date_from, $filter_date_to);
    
    // Calculate statistics
    $stats = calculate_shift_statistics($shift_history);
    
    ob_start();
    ?>
    
    <style>
    /* Single Employee Shift History Styles - Orange Theme */
    :root {
        --single-primary: #e67e22;
        --single-primary-dark: #d35400;
        --single-primary-light: #f39c12;
        --single-secondary: #3498db;
        --single-success: #27ae60;
        --single-danger: #e74c3c;
        --single-warning: #f39c12;
        --single-info: #3498db;
        --single-gray: #95a5a6;
    }
    
    .cms-single-history-container {
        max-width: 1300px;
        margin: 30px auto;
        padding: 30px;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(230,126,34,0.08);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        border-top: 5px solid var(--single-primary);
    }
    
    /* Employee Header */
    .cms-single-history-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 20px;
        background: linear-gradient(145deg, #fef5e7, #ffffff);
        padding: 25px;
        border-radius: 16px;
        border: 2px solid var(--single-primary-light);
    }
    
    .cms-single-employee-info {
        display: flex;
        align-items: center;
        gap: 20px;
    }
    
    .cms-single-employee-avatar {
        width: 80px;
        height: 80px;
        background: linear-gradient(145deg, var(--single-primary), var(--single-primary-dark));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 36px;
        font-weight: 700;
        color: white;
        box-shadow: 0 5px 15px rgba(230,126,34,0.3);
    }
    
    .cms-single-employee-details {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .cms-single-employee-name {
        font-size: 28px;
        font-weight: 700;
        color: var(--single-primary-dark);
        margin: 0;
    }
    
    .cms-single-employee-username {
        font-size: 16px;
        color: #6c7a89;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cms-single-employee-meta {
        display: flex;
        gap: 15px;
        margin-top: 5px;
        flex-wrap: wrap;
    }
    
    .cms-single-meta-item {
        background: #f0f8ff;
        padding: 5px 15px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
        color: var(--single-primary-dark);
        border: 1px solid var(--single-primary-light);
    }
    
    .cms-single-date-range {
        background: white;
        padding: 15px 25px;
        border-radius: 50px;
        border: 2px solid var(--single-primary-light);
        text-align: center;
    }
    
    .cms-single-range-label {
        font-size: 12px;
        color: #6c7a89;
        margin-bottom: 5px;
    }
    
    .cms-single-range-value {
        font-size: 18px;
        font-weight: 600;
        color: var(--single-primary-dark);
    }
    
    /* Filter Section */
    .cms-single-filters {
        background: #fef5e7;
        border: 2px solid var(--single-primary-light);
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 30px;
    }
    
    .cms-single-filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        align-items: end;
    }
    
    .cms-single-filter-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .cms-single-filter-group label {
        font-size: 13px;
        font-weight: 600;
        color: var(--single-primary-dark);
    }
    
    .cms-single-filter-control {
        padding: 12px 15px;
        border: 2px solid #ffe6d5;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.25s ease;
        background: white;
    }
    
    .cms-single-filter-control:focus {
        outline: none;
        border-color: var(--single-primary);
        box-shadow: 0 0 0 3px rgba(230,126,34,0.05);
    }
    
    .cms-single-filter-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 20px;
    }
    
    .cms-single-filter-btn {
        padding: 12px 24px;
        border: none;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .cms-single-filter-btn.apply {
        background: var(--single-primary);
        color: white;
    }
    
    .cms-single-filter-btn.apply:hover {
        background: var(--single-primary-dark);
        transform: translateY(-1px);
    }
    
    .cms-single-filter-btn.reset {
        background: #e2e8f0;
        color: #4a5568;
    }
    
    .cms-single-filter-btn.reset:hover {
        background: #cbd5e0;
    }
    
    /* Summary Cards */
    .cms-single-summary {
        display: grid;
        grid-template-columns: repeat(5, 1fr);
        gap: 15px;
        margin-bottom: 30px;
    }
    
    .cms-single-summary-card {
        background: linear-gradient(145deg, #fef5e7, #ffffff);
        border: 2px solid var(--single-primary-light);
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        transition: all 0.3s ease;
    }
    
    .cms-single-summary-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(230,126,34,0.1);
        border-color: var(--single-primary);
    }
    
    .cms-single-summary-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--single-primary-dark);
        line-height: 1.2;
        margin-bottom: 5px;
    }
    
    .cms-single-summary-label {
        font-size: 12px;
        color: #6c7a89;
        font-weight: 500;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .cms-single-summary-sub {
        font-size: 11px;
        color: #95a5a6;
        margin-top: 5px;
    }
    
    /* Chart Section */
    .cms-single-chart-section {
        background: #fef5e7;
        border: 2px solid var(--single-primary-light);
        border-radius: 16px;
        padding: 20px;
        margin-bottom: 30px;
    }
    
    .cms-single-chart-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--single-primary-dark);
        margin: 0 0 15px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .cms-single-chart-container {
        height: 200px;
        display: flex;
        align-items: flex-end;
        justify-content: space-around;
        gap: 5px;
        margin-top: 20px;
    }
    
    .cms-single-chart-bar {
        flex: 1;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 5px;
    }
    
    .cms-single-bar-fill {
        width: 100%;
        background: linear-gradient(to top, var(--single-primary), var(--single-primary-light));
        border-radius: 8px 8px 0 0;
        min-height: 30px;
        transition: height 0.3s ease;
        position: relative;
        cursor: pointer;
    }
    
    .cms-single-bar-fill:hover {
        opacity: 0.9;
    }
    
    .cms-single-bar-label {
        font-size: 11px;
        font-weight: 600;
        color: var(--single-primary-dark);
        transform: rotate(-45deg);
        white-space: nowrap;
        margin-top: 10px;
    }
    
    .cms-single-bar-value {
        position: absolute;
        top: -20px;
        left: 50%;
        transform: translateX(-50%);
        background: var(--single-primary-dark);
        color: white;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 10px;
        white-space: nowrap;
        opacity: 0;
        transition: opacity 0.2s ease;
    }
    
    .cms-single-bar-fill:hover .cms-single-bar-value {
        opacity: 1;
    }
    
    /* Table Styles */
    .cms-single-table-responsive {
        overflow-x: auto;
        margin-bottom: 30px;
        border-radius: 16px;
        border: 2px solid #f0f0f0;
    }
    
    .cms-single-history-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        background: white;
    }
    
    .cms-single-history-table th {
        background: #fef5e7;
        color: var(--single-primary-dark);
        font-weight: 600;
        padding: 16px 12px;
        text-align: left;
        border-bottom: 2px solid var(--single-primary-light);
        white-space: nowrap;
    }
    
    .cms-single-history-table td {
        padding: 14px 12px;
        border-bottom: 1px solid #f0f0f0;
        color: #2c3e50;
    }
    
    .cms-single-history-table tr:hover {
        background: #fef5e7;
    }
    
    .cms-single-history-table .highlight {
        background: #fff4e6;
        font-weight: 600;
    }
    
    .cms-single-date-badge {
        font-weight: 600;
        color: var(--single-primary-dark);
    }
    
    .cms-single-time-badge {
        font-family: monospace;
        font-weight: 500;
        background: #f0f0f0;
        padding: 3px 8px;
        border-radius: 20px;
        display: inline-block;
        font-size: 13px;
    }
    
    .cms-single-hours-actual {
        color: var(--single-primary-dark);
        font-weight: 600;
        background: #fef5e7;
        padding: 3px 10px;
        border-radius: 20px;
        display: inline-block;
    }
    
    .cms-single-hours-counted {
        color: var(--single-secondary);
        font-weight: 600;
        background: #e8f4fd;
        padding: 3px 10px;
        border-radius: 20px;
        display: inline-block;
    }
    
    .cms-single-diff {
        font-weight: 600;
        padding: 3px 10px;
        border-radius: 20px;
        display: inline-block;
    }
    
    .cms-single-diff.positive {
        background: #d4edda;
        color: #155724;
    }
    
    .cms-single-diff.negative {
        background: #f8d7da;
        color: #721c24;
    }
    
    .cms-single-diff.neutral {
        background: #e2e8f0;
        color: #4a5568;
    }
    
    /* Export Buttons */
    .cms-single-export {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-bottom: 20px;
    }
    
    .cms-single-export-btn {
        padding: 10px 20px;
        background: white;
        border: 2px solid var(--single-primary-light);
        border-radius: 40px;
        color: var(--single-primary-dark);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .cms-single-export-btn:hover {
        background: var(--single-primary);
        color: white;
        border-color: var(--single-primary);
    }
    
    /* Pagination */
    .cms-single-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 30px;
        flex-wrap: wrap;
    }
    
    .cms-single-page-link {
        padding: 10px 16px;
        background: white;
        border: 1px solid #ffe6d5;
        border-radius: 8px;
        color: #4a5568;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    
    .cms-single-page-link:hover {
        background: #fef5e7;
        border-color: var(--single-primary);
        color: var(--single-primary);
    }
    
    .cms-single-page-link.active {
        background: var(--single-primary);
        color: white;
        border-color: var(--single-primary);
    }
    
    /* No Data */
    .cms-single-no-data {
        text-align: center;
        padding: 60px 20px;
        background: #fef5e7;
        border-radius: 16px;
        color: #6c7a89;
        font-size: 16px;
    }
    
    .cms-single-no-data:before {
        content: '📊';
        display: block;
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.5;
    }
    
    /* Weekday indicators */
    .cms-single-weekday {
        font-size: 11px;
        color: #95a5a6;
        margin-left: 5px;
    }
    
    .cms-single-weekend {
        background: #fff4e6;
    }
    
    .cms-single-today {
        background: #fff9e6;
        border-left: 4px solid var(--single-primary);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .cms-single-summary {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .cms-single-history-header {
            flex-direction: column;
            text-align: center;
        }
        
        .cms-single-employee-info {
            flex-direction: column;
        }
        
        .cms-single-filter-grid {
            grid-template-columns: 1fr;
        }
        
        .cms-single-filter-actions {
            flex-direction: column;
        }
        
        .cms-single-filter-btn {
            width: 100%;
            justify-content: center;
        }
        
        .cms-single-export {
            flex-wrap: wrap;
        }
        
        .cms-single-export-btn {
            flex: 1;
            justify-content: center;
        }
        
        .cms-single-chart-container {
            height: 150px;
        }
    }
    
    @media (max-width: 480px) {
        .cms-single-summary {
            grid-template-columns: 1fr;
        }
        
        .cms-single-history-table {
            font-size: 12px;
        }
        
        .cms-single-history-table th,
        .cms-single-history-table td {
            padding: 10px 6px;
        }
    }
    </style>
    
    <div class="cms-single-history-container <?php echo esc_attr($atts['class']); ?>">
        
        <!-- Employee Header -->
        <div class="cms-single-history-header">
            <div class="cms-single-employee-info">
                <div class="cms-single-employee-avatar">
                    <?php echo strtoupper(substr($employee['name'], 0, 1)); ?>
                </div>
                <div class="cms-single-employee-details">
                    <h1 class="cms-single-employee-name"><?php echo esc_html($employee['name']); ?></h1>
                    <div class="cms-single-employee-username">
                        <span>@<?php echo esc_html($employee['username']); ?></span>
                        <span>•</span>
                        <span><?php echo esc_html($employee['email']); ?></span>
                    </div>
                    <div class="cms-single-employee-meta">
                        <span class="cms-single-meta-item"><?php echo esc_html($employee['position']); ?></span>
                        <span class="cms-single-meta-item"><?php echo esc_html($employee['corp_team']); ?></span>
                        <span class="cms-single-meta-item">ID: <?php echo esc_html($employee['id']); ?></span>
                    </div>
                </div>
            </div>
            
            <div class="cms-single-date-range">
                <div class="cms-single-range-label">SHOWING DATA FOR</div>
                <div class="cms-single-range-value">
                    <?php echo date('M d, Y', strtotime($filter_date_from)); ?> - <?php echo date('M d, Y', strtotime($filter_date_to)); ?>
                </div>
            </div>
        </div>
        
        <?php if ($atts['show_filters'] === 'yes'): ?>
        <!-- Filter Section -->
        <div class="cms-single-filters">
            <form method="get" action="" class="cms-single-filter-form">
                <?php if (!empty($atts['username'])): ?>
                <input type="hidden" name="username" value="<?php echo esc_attr($atts['username']); ?>">
                <?php endif; ?>
                <?php if (!empty($atts['employee_id'])): ?>
                <input type="hidden" name="employee_id" value="<?php echo esc_attr($atts['employee_id']); ?>">
                <?php endif; ?>
                
                <div class="cms-single-filter-grid">
                    <div class="cms-single-filter-group">
                        <label for="filter_date_from">From Date</label>
                        <input type="date" id="filter_date_from" name="filter_date_from" class="cms-single-filter-control" value="<?php echo esc_attr($filter_date_from); ?>">
                    </div>
                    
                    <div class="cms-single-filter-group">
                        <label for="filter_date_to">To Date</label>
                        <input type="date" id="filter_date_to" name="filter_date_to" class="cms-single-filter-control" value="<?php echo esc_attr($filter_date_to); ?>">
                    </div>
                    
                    <div class="cms-single-filter-group">
                        <label>&nbsp;</label>
                        <div class="cms-single-filter-actions" style="margin-top: 0;">
                            <button type="submit" class="cms-single-filter-btn apply">
                                <span>✓</span> Apply
                            </button>
                            <a href="<?php echo esc_url(remove_query_arg(array('filter_date_from', 'filter_date_to'))); ?>" class="cms-single-filter-btn reset">
                                <span>↺</span> Reset
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
        <?php endif; ?>
        
        <?php if ($atts['show_summary'] === 'yes'): ?>
        <!-- Summary Cards -->
        <div class="cms-single-summary">
            <div class="cms-single-summary-card">
                <div class="cms-single-summary-value"><?php echo $stats['total_days']; ?></div>
                <div class="cms-single-summary-label">Days Worked</div>
                <div class="cms-single-summary-sub">of <?php echo $stats['total_days_in_range']; ?> days</div>
            </div>
            
            <div class="cms-single-summary-card">
                <div class="cms-single-summary-value"><?php echo $stats['total_actual_hours']; ?>h <?php echo $stats['total_actual_mins']; ?>m</div>
                <div class="cms-single-summary-label">Actual Hours</div>
                <div class="cms-single-summary-sub"><?php echo number_format($stats['avg_daily_hours'], 1); ?> avg/day</div>
            </div>
            
            <div class="cms-single-summary-card">
                <div class="cms-single-summary-value"><?php echo $stats['total_counted_hours']; ?>h <?php echo $stats['total_counted_mins']; ?>m</div>
                <div class="cms-single-summary-label">Counted Hours</div>
                <div class="cms-single-summary-sub">scheduled time</div>
            </div>
            
            <div class="cms-single-summary-card">
                <?php
                $diff_class = $stats['total_diff_minutes'] > 0 ? 'positive' : ($stats['total_diff_minutes'] < 0 ? 'negative' : 'neutral');
                $diff_hours = floor(abs($stats['total_diff_minutes']) / 60);
                $diff_mins = abs($stats['total_diff_minutes']) % 60;
                ?>
                <div class="cms-single-summary-value <?php echo $diff_class; ?>">
                    <?php echo ($stats['total_diff_minutes'] >= 0 ? '+' : '-') . $diff_hours; ?>h <?php echo $diff_mins; ?>m
                </div>
                <div class="cms-single-summary-label">Difference</div>
                <div class="cms-single-summary-sub">(Counted - Actual)</div>
            </div>
            
            <div class="cms-single-summary-card">
                <div class="cms-single-summary-value"><?php echo $stats['overtime_days']; ?></div>
                <div class="cms-single-summary-label">Overtime Days</div>
                <div class="cms-single-summary-sub"><?php echo $stats['undertime_days']; ?> undertime days</div>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($atts['show_chart'] === 'yes' && !empty($shift_history)): ?>
        <!-- Mini Chart -->
        <div class="cms-single-chart-section">
            <div class="cms-single-chart-title">
                <span>📊</span> Daily Hours (Last 10 Days)
            </div>
            <div class="cms-single-chart-container" id="hours-chart">
                <?php
                $chart_data = array_slice($shift_history, 0, 10);
                $max_hours = 12; // Max hours for scaling
                foreach ($chart_data as $record):
                    $total_minutes = $record['actual_hours'] * 60 + $record['actual_mins'];
                    $height = min(100, ($total_minutes / ($max_hours * 60)) * 100);
                ?>
                <div class="cms-single-chart-bar">
                    <div class="cms-single-bar-fill" style="height: <?php echo $height; ?>%;">
                        <span class="cms-single-bar-value"><?php echo $record['actual_hours']; ?>h <?php echo $record['actual_mins']; ?>m</span>
                    </div>
                    <span class="cms-single-bar-label"><?php echo date('d', strtotime($record['date'])); ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Export Buttons -->
        <div class="cms-single-export">
            <button class="cms-single-export-btn" onclick="exportSingleToCSV('<?php echo esc_js($employee['name']); ?>')">
                <span>📥</span> Export CSV
            </button>
            <button class="cms-single-export-btn" onclick="window.print()">
                <span>🖨️</span> Print
            </button>
        </div>
        
        <?php if (empty($shift_history)): ?>
            <div class="cms-single-no-data">
                <?php echo esc_html($atts['no_data_message']); ?>
            </div>
        <?php else: ?>
        
        <!-- Shift History Table -->
        <div class="cms-single-table-responsive">
            <table class="cms-single-history-table" id="single-shift-history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Day</th>
                        <th>Actual Login</th>
                        <th>Actual Logout</th>
                        <th>Actual Hours</th>
                        <th>Scheduled Login</th>
                        <th>Scheduled Logout</th>
                        <th>Scheduled Hours</th>
                        <th>Difference</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    $today = date('Y-m-d');
                    foreach ($shift_history as $index => $record): 
                        $is_weekend = in_array(date('N', strtotime($record['date'])), [6, 7]);
                        $is_today = $record['date'] === $today;
                        $row_class = '';
                        if ($is_today) $row_class .= ' cms-single-today';
                        if ($is_weekend) $row_class .= ' cms-single-weekend';
                        
                        // Calculate difference
                        $record_actual = $record['actual_hours'] * 60 + $record['actual_mins'];
                        $record_counted = $record['counted_hours'] * 60 + $record['counted_mins'];
                        $record_diff = $record_counted - $record_actual;
                        $diff_class = $record_diff > 0 ? 'positive' : ($record_diff < 0 ? 'negative' : 'neutral');
                        
                        // Status
                        if (!$record['actual_logout_time']) {
                            $status = 'Active';
                            $status_class = 'positive';
                        } elseif ($record_diff > 30) {
                            $status = 'Overtime';
                            $status_class = 'positive';
                        } elseif ($record_diff < -30) {
                            $status = 'Undertime';
                            $status_class = 'negative';
                        } else {
                            $status = 'On Time';
                            $status_class = 'neutral';
                        }
                    ?>
                    <tr class="<?php echo $row_class; ?>">
                        <td>
                            <span class="cms-single-date-badge"><?php echo date($atts['date_format'], strtotime($record['date'])); ?></span>
                        </td>
                        <td>
                            <?php echo date('l', strtotime($record['date'])); ?>
                        </td>
                        <td>
                            <?php if ($record['actual_login_time']): ?>
                                <span class="cms-single-time-badge">
                                    <?php echo date($atts['time_format'], strtotime($record['actual_login_time'])); ?>
                                </span>
                            <?php else: ?>
                                <span style="color: #95a5a6;">--:--</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($record['actual_logout_time']): ?>
                                <span class="cms-single-time-badge">
                                    <?php echo date($atts['time_format'], strtotime($record['actual_logout_time'])); ?>
                                </span>
                            <?php else: ?>
                                <span style="color: #e67e22; font-weight: 600;">Active</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="cms-single-hours-actual">
                                <?php echo $record['actual_hours']; ?>h <?php echo $record['actual_mins']; ?>m
                            </span>
                        </td>
                        <td>
                            <?php if ($record['counted_login_time']): ?>
                                <span class="cms-single-time-badge" style="background: #e8f4fd;">
                                    <?php echo date($atts['time_format'], strtotime($record['counted_login_time'])); ?>
                                </span>
                            <?php else: ?>
                                <span style="color: #95a5a6;">--:--</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($record['counted_logout_time']): ?>
                                <span class="cms-single-time-badge" style="background: #e8f4fd;">
                                    <?php echo date($atts['time_format'], strtotime($record['counted_logout_time'])); ?>
                                </span>
                            <?php else: ?>
                                <span style="color: #95a5a6;">--:--</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="cms-single-hours-counted">
                                <?php echo $record['counted_hours']; ?>h <?php echo $record['counted_mins']; ?>m
                            </span>
                        </td>
                        <td>
                            <span class="cms-single-diff <?php echo $diff_class; ?>">
                                <?php echo ($record_diff >= 0 ? '+' : '') . $record_diff; ?> min
                            </span>
                        </td>
                        <td>
                            <span class="cms-single-diff <?php echo $status_class; ?>" style="padding: 3px 12px;">
                                <?php echo $status; ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="cms-single-pagination">
            <a href="#" class="cms-single-page-link">« Previous</a>
            <a href="#" class="cms-single-page-link active">1</a>
            <a href="#" class="cms-single-page-link">2</a>
            <a href="#" class="cms-single-page-link">3</a>
            <a href="#" class="cms-single-page-link">Next »</a>
        </div>
        
        <?php endif; ?>
    </div>
    
    <script>
    function exportSingleToCSV(employeeName) {
        // Get table data
        var rows = document.querySelectorAll('#single-shift-history-table tbody tr');
        var csv = [];
        
        // Headers
        csv.push('Date,Day,Actual Login,Actual Logout,Actual Hours (min),Scheduled Login,Scheduled Logout,Scheduled Hours (min),Difference (min),Status');
        
        // Data rows
        rows.forEach(function(row) {
            var cells = row.querySelectorAll('td');
            var date = cells[0].textContent.trim();
            var day = cells[1].textContent.trim();
            var actualLogin = cells[2].querySelector('.cms-single-time-badge')?.textContent.trim() || cells[2].textContent.trim();
            var actualLogout = cells[3].querySelector('.cms-single-time-badge')?.textContent.trim() || cells[3].textContent.trim();
            var actualHours = cells[4].querySelector('.cms-single-hours-actual')?.textContent.trim() || '';
            var scheduledLogin = cells[5].querySelector('.cms-single-time-badge')?.textContent.trim() || cells[5].textContent.trim();
            var scheduledLogout = cells[6].querySelector('.cms-single-time-badge')?.textContent.trim() || cells[6].textContent.trim();
            var scheduledHours = cells[7].querySelector('.cms-single-hours-counted')?.textContent.trim() || '';
            var diff = cells[8].querySelector('.cms-single-diff')?.textContent.trim() || '';
            var status = cells[9].querySelector('.cms-single-diff')?.textContent.trim() || '';
            
            // Parse hours to minutes
            var actualMinutes = 0;
            var scheduledMinutes = 0;
            
            var actualMatch = actualHours.match(/(\d+)h\s*(\d+)m/);
            if (actualMatch) {
                actualMinutes = parseInt(actualMatch[1]) * 60 + parseInt(actualMatch[2]);
            }
            
            var scheduledMatch = scheduledHours.match(/(\d+)h\s*(\d+)m/);
            if (scheduledMatch) {
                scheduledMinutes = parseInt(scheduledMatch[1]) * 60 + parseInt(scheduledMatch[2]);
            }
            
            csv.push(`"${date}","${day}","${actualLogin}","${actualLogout}",${actualMinutes},"${scheduledLogin}","${scheduledLogout}",${scheduledMinutes},"${diff}","${status}"`);
        });
        
        // Download CSV
        var blob = new Blob([csv.join('\n')], { type: 'text/csv' });
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = `shift_history_${employeeName.replace(/\s+/g, '_')}_${new Date().toISOString().slice(0,10)}.csv`;
        a.click();
        window.URL.revokeObjectURL(url);
    }
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_single_emp_shift_history', 'cms_single_emp_shift_history_shortcode');
add_shortcode(CMS_SINGLE_EMP_SHIFT_HISTORY_SHORTCODE, 'cms_single_emp_shift_history_shortcode');

/**
 * Get employee by username
 */
function get_employee_by_username($username) {
    // Mock data - in real implementation, this would come from database
    $employees = array(
        'john_employee' => array(
            'id' => 201,
            'username' => 'john_employee',
            'name' => 'John Smith',
            'email' => 'john.smith@company.com',
            'position' => 'Senior Software Engineer',
            'corp_team' => 'IT'
        ),
        'emily_jones' => array(
            'id' => 202,
            'username' => 'emily_jones',
            'name' => 'Emily Jones',
            'email' => 'emily.jones@company.com',
            'position' => 'HR Manager',
            'corp_team' => 'HR'
        ),
        'david_miller' => array(
            'id' => 203,
            'username' => 'david_miller',
            'name' => 'David Miller',
            'email' => 'david.miller@company.com',
            'position' => 'Financial Analyst',
            'corp_team' => 'Finance'
        ),
        'sarah_ahmed' => array(
            'id' => 204,
            'username' => 'sarah_ahmed',
            'name' => 'Sarah Ahmed',
            'email' => 'sarah.ahmed@company.com',
            'position' => 'Sales Representative',
            'corp_team' => 'Sales'
        ),
        'michael_brown' => array(
            'id' => 205,
            'username' => 'michael_brown',
            'name' => 'Michael Brown',
            'email' => 'michael.brown@company.com',
            'position' => 'Marketing Specialist',
            'corp_team' => 'Marketing'
        )
    );
    
    return isset($employees[$username]) ? $employees[$username] : null;
}

/**
 * Get employee by ID
 */
function get_employee_by_id($id) {
    $employees = array(
        201 => array(
            'id' => 201,
            'username' => 'john_employee',
            'name' => 'John Smith',
            'email' => 'john.smith@company.com',
            'position' => 'Senior Software Engineer',
            'corp_team' => 'IT'
        ),
        202 => array(
            'id' => 202,
            'username' => 'emily_jones',
            'name' => 'Emily Jones',
            'email' => 'emily.jones@company.com',
            'position' => 'HR Manager',
            'corp_team' => 'HR'
        ),
        203 => array(
            'id' => 203,
            'username' => 'david_miller',
            'name' => 'David Miller',
            'email' => 'david.miller@company.com',
            'position' => 'Financial Analyst',
            'corp_team' => 'Finance'
        ),
        204 => array(
            'id' => 204,
            'username' => 'sarah_ahmed',
            'name' => 'Sarah Ahmed',
            'email' => 'sarah.ahmed@company.com',
            'position' => 'Sales Representative',
            'corp_team' => 'Sales'
        ),
        205 => array(
            'id' => 205,
            'username' => 'michael_brown',
            'name' => 'Michael Brown',
            'email' => 'michael.brown@company.com',
            'position' => 'Marketing Specialist',
            'corp_team' => 'Marketing'
        )
    );
    
    return isset($employees[$id]) ? $employees[$id] : null;
}

/**
 * Get employee shift history
 */
function get_employee_shift_history($username, $date_from, $date_to) {
    // Mock data - in real implementation, this would come from database
    $all_history = array(
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
            ),
            array(
                'date' => '2024-03-12',
                'actual_login_time' => '09:00:00',
                'actual_logout_time' => '17:00:00',
                'actual_hours' => 8,
                'actual_mins' => 0,
                'counted_login_time' => '09:00:00',
                'counted_logout_time' => '17:00:00',
                'counted_hours' => 8,
                'counted_mins' => 0
            ),
            array(
                'date' => '2024-03-11',
                'actual_login_time' => '08:45:00',
                'actual_logout_time' => '17:15:00',
                'actual_hours' => 8,
                'actual_mins' => 30,
                'counted_login_time' => '09:00:00',
                'counted_logout_time' => '17:00:00',
                'counted_hours' => 8,
                'counted_mins' => 0
            )
        ),
        'emily_jones' => array(
            array(
                'date' => '2024-03-15',
                'actual_login_time' => '08:30:00',
                'actual_logout_time' => '16:35:00',
                'actual_hours' => 8,
                'actual_mins' => 5,
                'counted_login_time' => '08:30:00',
                'counted_logout_time' => '16:30:00',
                'counted_hours' => 8,
                'counted_mins' => 0
            )
        ),
        'david_miller' => array(
            array(
                'date' => '2024-03-15',
                'actual_login_time' => '10:05:00',
                'actual_logout_time' => '18:10:00',
                'actual_hours' => 8,
                'actual_mins' => 5,
                'counted_login_time' => '10:00:00',
                'counted_logout_time' => '18:00:00',
                'counted_hours' => 8,
                'counted_mins' => 0
            )
        )
    );
    
    $history = isset($all_history[$username]) ? $all_history[$username] : array();
    
    // Apply date filters
    $filtered = array_filter($history, function($record) use ($date_from, $date_to) {
        if ($record['date'] < $date_from) return false;
        if ($record['date'] > $date_to) return false;
        return true;
    });
    
    // Sort by date descending (newest first)
    usort($filtered, function($a, $b) {
        return strcmp($b['date'], $a['date']);
    });
    
    return $filtered;
}

/**
 * Calculate shift statistics
 */
function calculate_shift_statistics($history) {
    $stats = array(
        'total_days' => count($history),
        'total_days_in_range' => 0,
        'total_actual_hours' => 0,
        'total_actual_mins' => 0,
        'total_counted_hours' => 0,
        'total_counted_mins' => 0,
        'total_diff_minutes' => 0,
        'avg_daily_hours' => 0,
        'overtime_days' => 0,
        'undertime_days' => 0
    );
    
    if (empty($history)) {
        return $stats;
    }
    
    // Calculate date range
    $dates = array_column($history, 'date');
    $min_date = min($dates);
    $max_date = max($dates);
    $stats['total_days_in_range'] = ceil((strtotime($max_date) - strtotime($min_date)) / (60 * 60 * 24)) + 1;
    
    foreach ($history as $record) {
        // Actual hours
        $stats['total_actual_hours'] += $record['actual_hours'];
        $stats['total_actual_mins'] += $record['actual_mins'];
        
        // Counted hours
        $stats['total_counted_hours'] += $record['counted_hours'];
        $stats['total_counted_mins'] += $record['counted_mins'];
        
        // Difference
        $actual_total = $record['actual_hours'] * 60 + $record['actual_mins'];
        $counted_total = $record['counted_hours'] * 60 + $record['counted_mins'];
        $diff = $counted_total - $actual_total;
        $stats['total_diff_minutes'] += $diff;
        
        // Overtime/Undertime days
        if ($diff > 30) {
            $stats['overtime_days']++;
        } elseif ($diff < -30) {
            $stats['undertime_days']++;
        }
    }
    
    // Normalize minutes
    $stats['total_actual_hours'] += floor($stats['total_actual_mins'] / 60);
    $stats['total_actual_mins'] = $stats['total_actual_mins'] % 60;
    
    $stats['total_counted_hours'] += floor($stats['total_counted_mins'] / 60);
    $stats['total_counted_mins'] = $stats['total_counted_mins'] % 60;
    
    // Average daily hours
    $total_actual_minutes = $stats['total_actual_hours'] * 60 + $stats['total_actual_mins'];
    $stats['avg_daily_hours'] = $total_actual_minutes / 60 / $stats['total_days'];
    
    return $stats;
}

// Add to slugs.php
// Add this constant: define('CMS_SINGLE_EMP_SHIFT_HISTORY_SHORTCODE', 'cms_single_emp_shift_history');

?>