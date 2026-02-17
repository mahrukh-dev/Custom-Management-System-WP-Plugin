<?php
/**
 * CMS Employee Shift Management Assignment Shortcode
 * Complete shift management system for assigning weekly shifts to employees
 * 
 * Fields: emp_username, date, shift_start_time, shift_end_time, corp_acc_username
 * 
 * Usage: [cms_emp_shift_management]
 * Usage: [cms_emp_shift_management view="weekly" title="Shift Management"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define shortcode slug
if (!defined('CMS_EMP_SHIFT_MANAGEMENT_SHORTCODE')) {
    define('CMS_EMP_SHIFT_MANAGEMENT_SHORTCODE', 'cms_emp_shift_management');
}

/**
 * Employee Shift Management Shortcode
 */
function cms_emp_shift_management_shortcode($atts) {
    global $wpdb;
    
    // Parse attributes
    $atts = shortcode_atts(
        array(
            'title' => 'Employee Shift Management',
            'view' => 'weekly',
            'week_start' => 'monday',
            'class' => '',
            'show_corp_filter' => 'yes',
            'allow_edit' => 'yes',
            'allow_create' => 'yes',
            'allow_delete' => 'yes'
        ),
        $atts,
        'cms_emp_shift_management'
    );
    
    // Get current week dates
    $week_offset = isset($_GET['week_offset']) ? intval($_GET['week_offset']) : 0;
    $week_dates = get_week_dates($atts['week_start'], $week_offset);
    
    // Get data from database
    $employees = get_all_employees_from_db();
    $corp_accounts = get_all_corp_accounts_from_db();
    $shift_assignments = get_shift_assignments_from_db($week_dates['monday'], $week_dates['sunday']);
    
    ob_start();
    
    // Show success message if shifts were saved
    if (isset($_GET['shifts_saved']) && $_GET['shifts_saved'] === '1') {
        echo '<div class="notice notice-success" style="padding: 15px; margin: 20px 0; background: #d4edda; border-left: 4px solid #28a745; border-radius: 4px; font-weight: 500;">✅ Shifts saved successfully!</div>';
    }
    
    // Check if we're in edit mode
    $edit_mode = isset($_GET['edit_mode']) && $_GET['edit_mode'] === '1';
    
    if ($edit_mode && $atts['allow_edit'] === 'yes') {
        echo render_editable_weekly_shift_table($atts, $employees, $corp_accounts, $shift_assignments, $week_dates);
    } else {
        echo render_weekly_shift_table($atts, $employees, $corp_accounts, $shift_assignments, $week_dates);
    }
    
    return ob_get_clean();
}

add_shortcode('cms_emp_shift_management', 'cms_emp_shift_management_shortcode');
add_shortcode(CMS_EMP_SHIFT_MANAGEMENT_SHORTCODE, 'cms_emp_shift_management_shortcode');

/**
 * Check if shift is overnight (end time is less than start time)
 */
function is_overnight_shift($start_time, $end_time) {
    return strtotime($end_time) < strtotime($start_time);
}

/**
 * Format shift time with overnight indicator
 */
function format_shift_time($start_time, $end_time) {
    $formatted = $start_time . ' - ' . $end_time;
    if (is_overnight_shift($start_time, $end_time)) {
        $formatted .= ' 🌙 (Next Day)';
    }
    return $formatted;
}

/**
 * Get all employees from database
 */
function get_all_employees_from_db() {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_employee';
    
    return $wpdb->get_results(
        "SELECT username, name, position, corp_team 
         FROM $table 
         WHERE termination_date IS NULL 
         ORDER BY name ASC",
        ARRAY_A
    );
}

/**
 * Get all corporate accounts from database
 */
function get_all_corp_accounts_from_db() {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_corp_acc';
    
    return $wpdb->get_results(
        "SELECT username, company_name 
         FROM $table 
         WHERE status = 'active' 
         ORDER BY company_name ASC",
        ARRAY_A
    );
}

/**
 * Get shift assignments from database for date range
 */
function get_shift_assignments_from_db($start_date, $end_date) {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_shift_management';
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table 
         WHERE date BETWEEN %s AND %s 
         ORDER BY date ASC, shift_start_time ASC",
        $start_date,
        $end_date
    ), ARRAY_A);
}

/**
 * Get week dates based on offset
 */
function get_week_dates($week_start = 'monday', $week_offset = 0) {
    $dates = [];
    
    // Map week start to PHP's format
    $start_map = [
        'monday' => 1,
        'sunday' => 0,
        'tuesday' => 2,
        'wednesday' => 3,
        'thursday' => 4,
        'friday' => 5,
        'saturday' => 6
    ];
    
    $start_day = isset($start_map[$week_start]) ? $start_map[$week_start] : 1;
    
    // Calculate the start of the week
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    
    // Get the day of week (0 = Sunday, 1 = Monday, etc.)
    $day_of_week = (int)$today->format('w');
    
    // Calculate days to go back to reach week start
    $days_to_subtract = ($day_of_week - $start_day + 7) % 7;
    
    // Apply week offset
    $week_start_date = clone $today;
    $week_start_date->modify("-{$days_to_subtract} days");
    
    if ($week_offset > 0) {
        $week_start_date->modify("+{$week_offset} weeks");
    } elseif ($week_offset < 0) {
        $week_start_date->modify("{$week_offset} weeks");
    }
    
    // Generate all week days
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    
    foreach ($days as $index => $day) {
        $date = clone $week_start_date;
        $date->modify("+{$index} days");
        $dates[$day] = $date->format('Y-m-d');
    }
    
    return $dates;
}

/**
 * Render Weekly Shift Table (View Mode)
 */
function render_weekly_shift_table($atts, $employees, $corp_accounts, $shift_assignments, $week_dates) {
    $day_labels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    
    // Create lookup array for shifts by employee and date
    $shifts_by_employee_date = [];
    foreach ($shift_assignments as $shift) {
        $key = $shift['emp_username'] . '_' . $shift['date'];
        if (!isset($shifts_by_employee_date[$key])) {
            $shifts_by_employee_date[$key] = [];
        }
        $shifts_by_employee_date[$key][] = $shift;
    }
    
    // Create corp lookup
    $corp_lookup = [];
    foreach ($corp_accounts as $corp) {
        $corp_lookup[$corp['username']] = $corp['company_name'];
    }
    
    ?>
    
    <style>
    /* Shift Management Styles - Navy/Blue Theme */
    :root {
        --shift-primary: #1e3a8a;
        --shift-primary-dark: #1e3a8a;
        --shift-primary-light: #3b82f6;
        --shift-secondary: #64748b;
        --shift-success: #10b981;
        --shift-danger: #ef4444;
        --shift-warning: #f59e0b;
        --shift-info: #3b82f6;
        --shift-gray: #94a3b8;
        --shift-gray-light: #f1f5f9;
        --shift-night-bg: #2d3748;
        --shift-night-text: #e2e8f0;
        --shift-night-border: #4a5568;
    }
    
    .cms-shift-container {
        max-width: 1400px;
        margin: 30px auto;
        padding: 25px;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(30,58,138,0.08);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        border-top: 5px solid var(--shift-primary);
    }
    
    .cms-shift-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .cms-shift-title-section {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .cms-shift-title {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        color: var(--shift-primary-dark);
    }
    
    .cms-shift-week-nav {
        display: flex;
        align-items: center;
        gap: 15px;
        background: var(--shift-gray-light);
        padding: 10px 20px;
        border-radius: 50px;
    }
    
    .cms-shift-week-nav-btn {
        padding: 8px 16px;
        background: white;
        border: 1px solid var(--shift-gray);
        border-radius: 30px;
        color: var(--shift-primary);
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .cms-shift-week-nav-btn:hover {
        background: var(--shift-primary);
        color: white;
        border-color: var(--shift-primary);
    }
    
    .cms-shift-week-range {
        font-weight: 600;
        color: var(--shift-primary);
    }
    
    .cms-shift-actions {
        display: flex;
        gap: 10px;
    }
    
    .cms-shift-action-btn {
        padding: 10px 20px;
        border: none;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .cms-shift-action-btn.primary {
        background: var(--shift-primary);
        color: white;
    }
    
    .cms-shift-action-btn.primary:hover {
        background: var(--shift-primary-dark);
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(30,58,138,0.2);
    }
    
    .cms-shift-action-btn.secondary {
        background: var(--shift-gray-light);
        color: var(--shift-primary);
        border: 1px solid var(--shift-gray);
    }
    
    .cms-shift-action-btn.secondary:hover {
        background: var(--shift-gray);
        color: white;
    }
    
    .cms-shift-action-btn.edit-mode {
        background: var(--shift-warning);
        color: white;
    }
    
    .cms-shift-action-btn.edit-mode:hover {
        background: #e67e22;
    }
    
    .cms-shift-action-btn.save-mode {
        background: var(--shift-success);
        color: white;
    }
    
    .cms-shift-action-btn.save-mode:hover {
        background: #0f8f5f;
    }
    
    /* Filter Section */
    .cms-shift-filters {
        background: var(--shift-gray-light);
        border-radius: 12px;
        padding: 15px 20px;
        margin-bottom: 25px;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: center;
    }
    
    .cms-shift-filter-group {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cms-shift-filter-label {
        font-weight: 600;
        color: var(--shift-primary);
        font-size: 14px;
    }
    
    .cms-shift-filter-select {
        padding: 8px 15px;
        border: 1px solid var(--shift-gray);
        border-radius: 30px;
        font-size: 14px;
        min-width: 180px;
        background: white;
    }
    
    /* Table Styles */
    .cms-shift-table-container {
        overflow-x: auto;
        margin-bottom: 30px;
        border-radius: 16px;
        border: 2px solid var(--shift-gray-light);
    }
    
    .cms-shift-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        background: white;
        min-width: 1200px;
    }
    
    .cms-shift-table th {
        background: var(--shift-primary);
        color: white;
        font-weight: 600;
        padding: 15px 10px;
        text-align: center;
        border: 1px solid var(--shift-primary-dark);
    }
    
    .cms-shift-table th.employee-column {
        background: var(--shift-primary-dark);
        min-width: 200px;
    }
    
    .cms-shift-table th.day-header {
        background: var(--shift-primary-light);
    }
    
    .cms-shift-table td {
        border: 1px solid #e2e8f0;
        padding: 8px;
        vertical-align: top;
    }
    
    .cms-shift-table .employee-cell {
        background: var(--shift-gray-light);
        font-weight: 600;
        color: var(--shift-primary-dark);
        position: sticky;
        left: 0;
        z-index: 10;
    }
    
    .cms-shift-table .employee-info {
        display: flex;
        flex-direction: column;
        align-items: flex-start;
    }
    
    .cms-shift-table .employee-name {
        font-weight: 700;
        color: var(--shift-primary-dark);
    }
    
    .cms-shift-table .employee-username {
        font-size: 11px;
        color: var(--shift-secondary);
    }
    
    .cms-shift-table .employee-team {
        font-size: 10px;
        color: var(--shift-primary);
        background: white;
        padding: 2px 8px;
        border-radius: 12px;
        margin-top: 3px;
    }
    
    .cms-shift-table .shift-cell {
        text-align: left;
        background: white;
        min-width: 200px;
    }
    
    .cms-shift-table .shift-entry {
        background: #e6f7ff;
        border-left: 3px solid var(--shift-primary);
        padding: 8px;
        margin-bottom: 5px;
        border-radius: 4px;
        font-size: 12px;
    }
    
    .cms-shift-table .shift-entry.overnight {
        background: var(--shift-night-bg);
        border-left: 3px solid #fbbf24;
        color: var(--shift-night-text);
    }
    
    .cms-shift-table .shift-entry.overnight .shift-time {
        color: #fbbf24;
    }
    
    .cms-shift-table .shift-time {
        font-weight: 600;
        color: var(--shift-primary);
        font-family: monospace;
        font-size: 13px;
    }
    
    .cms-shift-table .shift-corp {
        font-size: 11px;
        color: var(--shift-secondary);
        margin-top: 3px;
        padding: 2px 5px;
        background: #ffffff;
        border-radius: 12px;
        display: inline-block;
    }
    
    .cms-shift-table .shift-entry.overnight .shift-corp {
        background: var(--shift-night-border);
        color: #e2e8f0;
    }
    
    .cms-shift-table .overnight-badge {
        display: inline-block;
        background: #fbbf24;
        color: #1e3a8a;
        font-size: 10px;
        padding: 2px 6px;
        border-radius: 12px;
        margin-left: 5px;
        font-weight: 600;
    }
    
    .cms-shift-table .off-day {
        color: var(--shift-gray);
        font-style: italic;
        background: #f8fafc;
        padding: 8px;
        text-align: center;
    }
    
    /* Legend */
    .cms-shift-legend {
        display: flex;
        gap: 20px;
        margin-top: 20px;
        padding: 15px;
        background: var(--shift-gray-light);
        border-radius: 12px;
        flex-wrap: wrap;
    }
    
    .cms-shift-legend-item {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
    }
    
    .cms-shift-legend-color {
        width: 20px;
        height: 20px;
        border-radius: 4px;
    }
    
    .cms-shift-legend-color.normal {
        background: #e6f7ff;
        border-left: 3px solid var(--shift-primary);
    }
    
    .cms-shift-legend-color.overnight {
        background: var(--shift-night-bg);
        border-left: 3px solid #fbbf24;
    }
    
    .cms-shift-legend-color.off {
        background: #f8fafc;
        border: 1px dashed var(--shift-gray);
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .cms-shift-header {
            flex-direction: column;
            align-items: stretch;
        }
        
        .cms-shift-week-nav {
            flex-wrap: wrap;
        }
        
        .cms-shift-filters {
            flex-direction: column;
        }
        
        .cms-shift-filter-group {
            width: 100%;
        }
        
        .cms-shift-filter-select {
            width: 100%;
        }
    }
    </style>
    
    <div class="cms-shift-container <?php echo esc_attr($atts['class']); ?>">
        
        <!-- Header -->
        <div class="cms-shift-header">
            <div class="cms-shift-title-section">
                <h1 class="cms-shift-title"><?php echo esc_html($atts['title']); ?></h1>
            </div>
            
            <div class="cms-shift-week-nav">
                <a href="<?php echo esc_url(add_query_arg('week_offset', (isset($_GET['week_offset']) ? intval($_GET['week_offset']) - 1 : -1))); ?>" class="cms-shift-week-nav-btn">← Previous Week</a>
                <span class="cms-shift-week-range">
                    <?php echo date('M d', strtotime($week_dates['monday'])); ?> - <?php echo date('M d, Y', strtotime($week_dates['sunday'])); ?>
                </span>
                <a href="<?php echo esc_url(add_query_arg('week_offset', (isset($_GET['week_offset']) ? intval($_GET['week_offset']) + 1 : 1))); ?>" class="cms-shift-week-nav-btn">Next Week →</a>
                <a href="<?php echo esc_url(remove_query_arg('week_offset')); ?>" class="cms-shift-week-nav-btn">Current Week</a>
            </div>
            
            <div class="cms-shift-actions">
                <?php if ($atts['allow_edit'] === 'yes'): ?>
                <a href="<?php echo esc_url(add_query_arg('edit_mode', '1')); ?>" class="cms-shift-action-btn edit-mode">
                    ✏️ Edit Schedule
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Filters -->
        <?php if ($atts['show_corp_filter'] === 'yes' && !empty($corp_accounts)): ?>
        <div class="cms-shift-filters">
            <div class="cms-shift-filter-group">
                <span class="cms-shift-filter-label">Corporate Account:</span>
                <select class="cms-shift-filter-select" id="corp-filter" onchange="filterByCorp()">
                    <option value="">All Corporate Accounts</option>
                    <?php foreach ($corp_accounts as $corp): ?>
                    <option value="<?php echo esc_attr($corp['username']); ?>"><?php echo esc_html($corp['company_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="cms-shift-filter-group">
                <span class="cms-shift-filter-label">Employee:</span>
                <select class="cms-shift-filter-select" id="employee-filter" onchange="filterByEmployee()">
                    <option value="">All Employees</option>
                    <?php foreach ($employees as $emp): ?>
                    <option value="<?php echo esc_attr($emp['username']); ?>"><?php echo esc_html($emp['name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Weekly Shift Table (View Mode) -->
        <div class="cms-shift-table-container">
            <table class="cms-shift-table" id="shift-table">
                <thead>
                    <tr>
                        <th class="employee-column">Employee</th>
                        <?php foreach ($day_labels as $index => $day): ?>
                        <th class="day-header"><?php echo esc_html($day); ?><br><small><?php echo date('M d', strtotime($week_dates[strtolower($day)])); ?></small></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $employee): ?>
                    <tr data-employee="<?php echo esc_attr($employee['username']); ?>">
                        <!-- Employee Column -->
                        <td class="employee-cell">
                            <div class="employee-info">
                                <span class="employee-name"><?php echo esc_html($employee['name']); ?></span>
                                <span class="employee-username">@<?php echo esc_html($employee['username']); ?></span>
                                <?php if (!empty($employee['corp_team'])): ?>
                                <span class="employee-team"><?php echo esc_html($employee['corp_team']); ?></span>
                                <?php endif; ?>
                            </div>
                        </td>
                        
                        <!-- Days Columns -->
                        <?php foreach ($day_labels as $index => $day):
                            $date = $week_dates[strtolower($day)];
                            $key = $employee['username'] . '_' . $date;
                            $shifts = isset($shifts_by_employee_date[$key]) ? $shifts_by_employee_date[$key] : [];
                        ?>
                            <td class="shift-cell" data-date="<?php echo esc_attr($date); ?>" data-employee="<?php echo esc_attr($employee['username']); ?>">
                                <?php if (!empty($shifts)): ?>
                                    <?php foreach ($shifts as $shift): 
                                        $corp_name = isset($corp_lookup[$shift['corp_acc_username']]) ? $corp_lookup[$shift['corp_acc_username']] : '';
                                        $is_overnight = is_overnight_shift($shift['shift_start_time'], $shift['shift_end_time']);
                                        $overnight_class = $is_overnight ? 'overnight' : '';
                                    ?>
                                        <div class="shift-entry <?php echo $overnight_class; ?>" data-shift-id="<?php echo esc_attr($shift['id']); ?>" data-corp="<?php echo esc_attr($shift['corp_acc_username'] ?? ''); ?>">
                                            <span class="shift-time">
                                                <?php echo esc_html($shift['shift_start_time']); ?> - <?php echo esc_html($shift['shift_end_time']); ?>
                                                <?php if ($is_overnight): ?>
                                                    <span class="overnight-badge">🌙 Next Day</span>
                                                <?php endif; ?>
                                            </span>
                                            <?php if (!empty($shift['corp_acc_username']) && !empty($corp_name)): ?>
                                                <span class="shift-corp"><?php echo esc_html($corp_name); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="off-day">—</div>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Legend -->
        <div class="cms-shift-legend">
            <div class="cms-shift-legend-item">
                <div class="cms-shift-legend-color normal"></div>
                <span>Day Shift</span>
            </div>
            <div class="cms-shift-legend-item">
                <div class="cms-shift-legend-color overnight"></div>
                <span>Overnight Shift (ends next day)</span>
            </div>
            <div class="cms-shift-legend-item">
                <div class="cms-shift-legend-color off"></div>
                <span>Off Day / No Shift</span>
            </div>
        </div>
    </div>
    
    <script>
    function filterByCorp() {
        var corp = document.getElementById('corp-filter').value;
        var entries = document.querySelectorAll('.shift-entry');
        
        entries.forEach(function(entry) {
            if (corp === '') {
                entry.style.opacity = '1';
                entry.style.backgroundColor = '';
            } else {
                if (entry.getAttribute('data-corp') === corp) {
                    entry.style.opacity = '1';
                    entry.style.backgroundColor = '';
                } else {
                    entry.style.opacity = '0.3';
                    entry.style.backgroundColor = '#f1f5f9';
                }
            }
        });
    }
    
    function filterByEmployee() {
        var employee = document.getElementById('employee-filter').value;
        var rows = document.querySelectorAll('#shift-table tbody tr');
        
        rows.forEach(function(row) {
            if (employee === '') {
                row.style.display = '';
            } else {
                if (row.getAttribute('data-employee') === employee) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    }
    </script>
    
    <?php
}

/**
 * Render Editable Weekly Shift Table
 */
function render_editable_weekly_shift_table($atts, $employees, $corp_accounts, $shift_assignments, $week_dates) {
    $day_labels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    
    // Create lookup array for shifts by employee and date
    $shifts_by_employee_date = [];
    foreach ($shift_assignments as $shift) {
        $key = $shift['emp_username'] . '_' . $shift['date'];
        if (!isset($shifts_by_employee_date[$key])) {
            $shifts_by_employee_date[$key] = [];
        }
        $shifts_by_employee_date[$key][] = $shift;
    }
    
    ?>
    
    <div class="cms-shift-container <?php echo esc_attr($atts['class']); ?>">
        
        <!-- Header -->
        <div class="cms-shift-header">
            <div class="cms-shift-title-section">
                <h1 class="cms-shift-title"><?php echo esc_html($atts['title']); ?> - Edit Mode</h1>
            </div>
            
            <div class="cms-shift-week-nav">
                <a href="<?php echo esc_url(add_query_arg(['week_offset' => (isset($_GET['week_offset']) ? intval($_GET['week_offset']) - 1 : -1), 'edit_mode' => '1'])); ?>" class="cms-shift-week-nav-btn">← Previous Week</a>
                <span class="cms-shift-week-range">
                    <?php echo date('M d', strtotime($week_dates['monday'])); ?> - <?php echo date('M d, Y', strtotime($week_dates['sunday'])); ?>
                </span>
                <a href="<?php echo esc_url(add_query_arg(['week_offset' => (isset($_GET['week_offset']) ? intval($_GET['week_offset']) + 1 : 1), 'edit_mode' => '1'])); ?>" class="cms-shift-week-nav-btn">Next Week →</a>
            </div>
            
            <div class="cms-shift-actions">
                <button onclick="saveAllShifts()" class="cms-shift-action-btn save-mode">
                    💾 Save All Changes
                </button>
                <a href="<?php echo esc_url(remove_query_arg('edit_mode')); ?>" class="cms-shift-action-btn secondary">
                    ← Cancel Edit
                </a>
            </div>
        </div>
        
        <!-- Editable Shift Table -->
        <div class="cms-shift-table-container">
            <form id="shift-edit-form" method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
                <?php wp_nonce_field('cms_shift_management', 'cms_shift_nonce'); ?>
                <input type="hidden" name="action" value="save_weekly_shifts">
                <input type="hidden" name="week_start" value="<?php echo esc_attr($week_dates['monday']); ?>">
                <input type="hidden" name="week_end" value="<?php echo esc_attr($week_dates['sunday']); ?>">
                <input type="hidden" name="redirect" value="<?php echo esc_url(remove_query_arg('edit_mode')); ?>">
                
                <table class="cms-shift-table" id="editable-shift-table">
                    <thead>
                        <tr>
                            <th class="employee-column">Employee</th>
                            <?php foreach ($day_labels as $index => $day): ?>
                            <th class="day-header"><?php echo esc_html($day); ?><br><small><?php echo date('M d', strtotime($week_dates[strtolower($day)])); ?></small></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $employee): 
                            $emp_username = $employee['username'];
                        ?>
                        <tr data-employee="<?php echo esc_attr($emp_username); ?>">
                            <!-- Employee Column -->
                            <td class="employee-cell">
                                <div class="employee-info">
                                    <span class="employee-name"><?php echo esc_html($employee['name']); ?></span>
                                    <span class="employee-username">@<?php echo esc_html($emp_username); ?></span>
                                </div>
                            </td>
                            
                            <!-- Days Columns - Editable -->
                            <?php foreach ($day_labels as $index => $day):
                                $date = $week_dates[strtolower($day)];
                                $key = $emp_username . '_' . $date;
                                $shifts = isset($shifts_by_employee_date[$key]) ? $shifts_by_employee_date[$key] : [];
                            ?>
                                <td class="shift-cell editable-cell" data-date="<?php echo esc_attr($date); ?>" data-employee="<?php echo esc_attr($emp_username); ?>">
                                    <div class="shift-editor">
                                        <div class="shift-entries" id="entries-<?php echo esc_attr($emp_username); ?>-<?php echo esc_attr($date); ?>">
                                            <?php if (!empty($shifts)): ?>
                                                <?php foreach ($shifts as $shift_index => $shift): 
                                                    $is_overnight = is_overnight_shift($shift['shift_start_time'], $shift['shift_end_time']);
                                                    // Use a truly unique key that won't conflict
                                                    $unique_key = 'shift_' . ($shift['id'] ?? 'new') . '_' . $shift_index . '_' . time();
                                                ?>
                                                    <div class="edit-shift-entry <?php echo $is_overnight ? 'overnight' : ''; ?>" data-shift-id="<?php echo esc_attr($shift['id'] ?? ''); ?>">
                                                        <input type="hidden" name="shifts[<?php echo esc_attr($emp_username); ?>][<?php echo esc_attr($date); ?>][<?php echo esc_attr($unique_key); ?>][id]" value="<?php echo esc_attr($shift['id'] ?? ''); ?>">
                                                        <div class="edit-shift-row">
                                                            <input type="time" name="shifts[<?php echo esc_attr($emp_username); ?>][<?php echo esc_attr($date); ?>][<?php echo esc_attr($unique_key); ?>][start]" value="<?php echo esc_attr($shift['shift_start_time']); ?>" class="shift-time-input" required>
                                                            <span>to</span>
                                                            <input type="time" name="shifts[<?php echo esc_attr($emp_username); ?>][<?php echo esc_attr($date); ?>][<?php echo esc_attr($unique_key); ?>][end]" value="<?php echo esc_attr($shift['shift_end_time']); ?>" class="shift-time-input" required>
                                                        </div>
                                                        <div class="edit-shift-corp">
                                                            <select name="shifts[<?php echo esc_attr($emp_username); ?>][<?php echo esc_attr($date); ?>][<?php echo esc_attr($unique_key); ?>][corp]" class="shift-corp-select">
                                                                <option value="">No Corporate Account</option>
                                                                <?php foreach ($corp_accounts as $corp): ?>
                                                                <option value="<?php echo esc_attr($corp['username']); ?>" <?php selected($shift['corp_acc_username'] ?? '', $corp['username']); ?>>
                                                                    <?php echo esc_html($corp['company_name']); ?>
                                                                </option>
                                                                <?php endforeach; ?>
                                                            </select>
                                                            <?php if ($atts['allow_delete'] === 'yes'): ?>
                                                            <button type="button" class="remove-shift-btn" onclick="removeShiftEntry(this)" title="Remove Shift">✕</button>
                                                            <?php endif; ?>
                                                        </div>
                                                        <?php if ($is_overnight): ?>
                                                            <div style="font-size: 10px; color: #fbbf24; margin-top: 4px; text-align: right;">🌙 Overnight Shift</div>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="no-shifts">No shifts assigned</div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($atts['allow_create'] === 'yes'): ?>
                                        <button type="button" class="add-shift-btn" onclick="addShiftEntry('<?php echo esc_js($emp_username); ?>', '<?php echo esc_js($date); ?>')">
                                            + Add Shift
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            <?php endforeach; ?>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </form>
        </div>
        
        <style>
        .editable-cell {
            background: #fff9f0;
            min-width: 280px;
        }
        
        .shift-editor {
            padding: 5px;
        }
        
        .shift-entries {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .edit-shift-entry {
            background: #e6f7ff;
            border-left: 3px solid var(--shift-primary);
            padding: 12px;
            margin-bottom: 10px;
            border-radius: 6px;
            position: relative;
            box-shadow: 0 2px 4px rgba(0,0,0,0.05);
        }
        
        .edit-shift-entry.overnight {
            background: var(--shift-night-bg);
            border-left: 3px solid #fbbf24;
            color: var(--shift-night-text);
        }
        
        .edit-shift-entry.overnight .shift-time-input {
            background: #4a5568;
            color: #e2e8f0;
            border-color: #718096;
        }
        
        .edit-shift-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 10px;
        }
        
        .shift-time-input {
            padding: 8px 10px;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            font-family: monospace;
            font-size: 13px;
            width: 100px;
        }
        
        .shift-time-input:focus {
            outline: none;
            border-color: var(--shift-primary);
            box-shadow: 0 0 0 2px rgba(30,58,138,0.1);
        }
        
        .edit-shift-corp {
            display: flex;
            gap: 8px;
            align-items: center;
        }
        
        .shift-corp-select {
            flex: 1;
            padding: 8px 10px;
            border: 1px solid #cbd5e0;
            border-radius: 6px;
            font-size: 12px;
            background: white;
        }
        
        .add-shift-btn {
            width: 100%;
            padding: 10px;
            background: var(--shift-success);
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 10px;
            transition: background 0.2s;
        }
        
        .add-shift-btn:hover {
            background: #0f8f5f;
        }
        
        .remove-shift-btn {
            width: 32px;
            height: 32px;
            background: var(--shift-danger);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background 0.2s;
        }
        
        .remove-shift-btn:hover {
            background: #c0392b;
        }
        
        .no-shifts {
            color: var(--shift-gray);
            font-style: italic;
            padding: 15px;
            text-align: center;
            background: #f8fafc;
            border-radius: 6px;
            margin-bottom: 5px;
            border: 1px dashed #cbd5e0;
        }
        </style>
        
        <script>
        function addShiftEntry(employee, date) {
            const container = document.getElementById(`entries-${employee}-${date}`);
            
            // Create a truly unique key using timestamp + random number
            const uniqueKey = 'new_' + Date.now() + '_' + Math.floor(Math.random() * 1000000);
            
            const template = document.createElement('div');
            template.className = 'edit-shift-entry new-shift';
            
            // Get corporate account options from PHP
            const corpOptions = `<?php 
                $options = '';
                foreach ($corp_accounts as $corp) {
                    $options .= '<option value="' . esc_attr($corp['username']) . '">' . esc_html($corp['company_name']) . '</option>';
                }
                echo $options;
            ?>`;
            
            template.innerHTML = `
                <input type="hidden" name="shifts[${employee}][${date}][${uniqueKey}][id]" value="">
                <div class="edit-shift-row">
                    <input type="time" name="shifts[${employee}][${date}][${uniqueKey}][start]" value="09:00" class="shift-time-input" required>
                    <span>to</span>
                    <input type="time" name="shifts[${employee}][${date}][${uniqueKey}][end]" value="17:00" class="shift-time-input" required>
                </div>
                <div class="edit-shift-corp">
                    <select name="shifts[${employee}][${date}][${uniqueKey}][corp]" class="shift-corp-select">
                        <option value="">No Corporate Account</option>
                        ${corpOptions}
                    </select>
                    <button type="button" class="remove-shift-btn" onclick="removeShiftEntry(this)" title="Remove Shift">✕</button>
                </div>
            `;
            
            // Remove "No shifts assigned" message if present
            const noShiftsDiv = container.querySelector('.no-shifts');
            if (noShiftsDiv) {
                noShiftsDiv.remove();
            }
            
            container.appendChild(template);
            
            // Scroll to the new entry
            template.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
        }
        
        function removeShiftEntry(button) {
            if (confirm('Are you sure you want to remove this shift?')) {
                const entry = button.closest('.edit-shift-entry');
                const container = entry.parentNode;
                entry.remove();
                
                // Check if container is empty
                if (container.children.length === 0) {
                    container.innerHTML = '<div class="no-shifts">No shifts assigned</div>';
                }
            }
        }
        
        function saveAllShifts() {
            // Validate all time inputs
            const timeInputs = document.querySelectorAll('.shift-time-input');
            let isValid = true;
            let firstInvalid = null;
            
            timeInputs.forEach(input => {
                if (!input.value) {
                    input.style.borderColor = '#ef4444';
                    isValid = false;
                    if (!firstInvalid) firstInvalid = input;
                } else {
                    input.style.borderColor = '#cbd5e0';
                }
            });
            
            if (!isValid) {
                alert('Please fill in all shift times before saving.');
                if (firstInvalid) {
                    firstInvalid.focus();
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
                return;
            }
            
            // Count total shifts being saved
            const shiftCount = document.querySelectorAll('.edit-shift-entry').length;
            
            if (confirm(`Save all changes? (${shiftCount} shift${shiftCount !== 1 ? 's' : ''} will be saved)`)) {
                document.getElementById('shift-edit-form').submit();
            }
        }
        </script>
        
        <!-- Legend -->
        <div class="cms-shift-legend">
            <div class="cms-shift-legend-item">
                <div class="cms-shift-legend-color normal"></div>
                <span>Day Shift</span>
            </div>
            <div class="cms-shift-legend-item">
                <div class="cms-shift-legend-color overnight"></div>
                <span>Overnight Shift</span>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Handle form submissions for saving shifts
 */
function cms_handle_shift_management_actions() {
    global $wpdb;
    
    if (isset($_POST['action']) && $_POST['action'] === 'save_weekly_shifts') {
        // Verify nonce
        if (!isset($_POST['cms_shift_nonce']) || !wp_verify_nonce($_POST['cms_shift_nonce'], 'cms_shift_management')) {
            wp_die('Security check failed');
        }
        
        $redirect_url = isset($_POST['redirect']) ? $_POST['redirect'] : home_url();
        $week_start = sanitize_text_field($_POST['week_start']);
        $week_end = sanitize_text_field($_POST['week_end']);
        
        $table = $wpdb->prefix . 'cms_shift_management';
        
        // First, delete all shifts for this week to start fresh
        // This prevents orphaned shifts and ensures clean slate
        $wpdb->query($wpdb->prepare(
            "DELETE FROM $table WHERE date BETWEEN %s AND %s",
            $week_start,
            $week_end
        ));
        
        $insert_count = 0;
        
        // Process shifts data
        if (isset($_POST['shifts']) && is_array($_POST['shifts'])) {
            foreach ($_POST['shifts'] as $emp_username => $dates) {
                foreach ($dates as $date => $shifts) {
                    // Validate date is within the week
                    if ($date < $week_start || $date > $week_end) {
                        continue;
                    }
                    
                    // Insert new shifts
                    if (is_array($shifts)) {
                        foreach ($shifts as $shift_key => $shift) {
                            // Skip empty shifts (where user added but didn't fill times)
                            if (empty($shift['start']) || empty($shift['end'])) {
                                continue;
                            }
                            
                            $result = $wpdb->insert(
                                $table,
                                array(
                                    'emp_username' => sanitize_text_field($emp_username),
                                    'date' => sanitize_text_field($date),
                                    'shift_start_time' => sanitize_text_field($shift['start']),
                                    'shift_end_time' => sanitize_text_field($shift['end']),
                                    'corp_acc_username' => !empty($shift['corp']) ? sanitize_text_field($shift['corp']) : null,
                                    'created_at' => current_time('mysql')
                                ),
                                array('%s', '%s', '%s', '%s', '%s', '%s')
                            );
                            
                            if ($result) {
                                $insert_count++;
                            }
                        }
                    }
                }
            }
        }
        
        // Add count to redirect URL for debugging
        $redirect_url = add_query_arg('shifts_saved', '1', $redirect_url);
        $redirect_url = add_query_arg('shift_count', $insert_count, $redirect_url);
        
        wp_redirect($redirect_url);
        exit;
    }
}
add_action('admin_post_save_weekly_shifts', 'cms_handle_shift_management_actions');