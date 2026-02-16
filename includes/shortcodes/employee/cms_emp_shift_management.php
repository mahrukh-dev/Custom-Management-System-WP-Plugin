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
    $week_dates = get_week_dates($atts['week_start']);
    
    // Get mock data
    $employees = get_all_employees_for_shift();
    $corp_accounts = get_all_corp_accounts_for_shift();
    $shift_assignments = get_mock_shift_assignments();
    
    ob_start();
    
    // Check if we're in edit mode
    $edit_mode = isset($_GET['edit_mode']) && $_GET['edit_mode'] === '1';
    
    if ($edit_mode) {
        echo render_editable_weekly_shift_table($atts, $employees, $corp_accounts, $shift_assignments, $week_dates);
    } else {
        echo render_weekly_shift_table($atts, $employees, $corp_accounts, $shift_assignments, $week_dates);
    }
    
    return ob_get_clean();
}

add_shortcode('cms_emp_shift_management', 'cms_emp_shift_management_shortcode');
add_shortcode(CMS_EMP_SHIFT_MANAGEMENT_SHORTCODE, 'cms_emp_shift_management_shortcode');

/**
 * Render Weekly Shift Table (View Mode)
 */
function render_weekly_shift_table($atts, $employees, $corp_accounts, $shift_assignments, $week_dates) {
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    $day_labels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
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
        --shift-edit-bg: #fff3e0;
        --shift-edit-border: #f39c12;
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
        min-width: 180px;
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
    
    .cms-shift-table .shift-cell {
        text-align: left;
        background: white;
        min-width: 180px;
    }
    
    .cms-shift-table .shift-entry {
        background: #e6f7ff;
        border-left: 3px solid var(--shift-primary);
        padding: 8px;
        margin-bottom: 5px;
        border-radius: 4px;
        font-size: 12px;
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
    
    .cms-shift-legend-color.off {
        background: #f8fafc;
        border: 1px dashed var(--shift-gray);
    }
    
    /* Modal */
    .cms-shift-modal {
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
    
    .cms-shift-modal-content {
        background: white;
        padding: 30px;
        border-radius: 20px;
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .cms-shift-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--shift-gray-light);
    }
    
    .cms-shift-modal-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--shift-primary);
        margin: 0;
    }
    
    .cms-shift-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: var(--shift-gray);
    }
    
    .cms-shift-modal-body {
        margin-bottom: 25px;
    }
    
    .cms-shift-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    
    .cms-shift-modal-btn {
        padding: 10px 20px;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        border: none;
    }
    
    .cms-shift-modal-btn.confirm {
        background: var(--shift-danger);
        color: white;
    }
    
    .cms-shift-modal-btn.cancel {
        background: var(--shift-gray-light);
        color: var(--shift-primary);
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
                <a href="<?php echo esc_url(add_query_arg('edit_mode', '1')); ?>" class="cms-shift-action-btn edit-mode">
                    ✏️ Edit Schedule
                </a>
            </div>
        </div>
        
        <!-- Filters -->
        <?php if ($atts['show_corp_filter'] === 'yes'): ?>
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
                                <span style="font-size: 10px; color: #64748b;"><?php echo esc_html($employee['position']); ?></span>
                            </div>
                        </td>
                        
                        <!-- Days Columns -->
                        <?php foreach ($day_labels as $index => $day):
                            $date = $week_dates[strtolower($day)];
                            $shifts = get_shifts_for_employee($employee['username'], $date, $shift_assignments);
                        ?>
                            <td class="shift-cell" data-date="<?php echo esc_attr($date); ?>" data-employee="<?php echo esc_attr($employee['username']); ?>">
                                <?php if (!empty($shifts)): ?>
                                    <?php foreach ($shifts as $shift): 
                                        $corp_name = '';
                                        if ($shift && isset($shift['corp_acc_username'])) {
                                            foreach ($corp_accounts as $corp) {
                                                if ($corp['username'] === $shift['corp_acc_username']) {
                                                    $corp_name = $corp['company_name'];
                                                    break;
                                                }
                                            }
                                        }
                                    ?>
                                        <div class="shift-entry" data-shift-id="<?php echo esc_attr($shift['id']); ?>" data-corp="<?php echo esc_attr($shift['corp_acc_username'] ?? ''); ?>">
                                            <span class="shift-time"><?php echo esc_html($shift['shift_start_time']); ?> - <?php echo esc_html($shift['shift_end_time']); ?></span>
                                            <?php if ($shift['corp_acc_username']): ?>
                                                <span class="shift-corp"><?php echo esc_html($corp_name); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="off-day">Off</div>
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
                <span>Active Shift</span>
            </div>
            <div class="cms-shift-legend-item">
                <div class="cms-shift-legend-color off"></div>
                <span>Off Day / No Shift</span>
            </div>
            <div class="cms-shift-legend-item">
                <span>📌 Corporate Account assigned to shift</span>
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
                entry.style.backgroundColor = '#e6f7ff';
            } else {
                if (entry.getAttribute('data-corp') === corp) {
                    entry.style.opacity = '1';
                    entry.style.backgroundColor = '#e6f7ff';
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
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    $day_labels = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
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
                        <?php foreach ($employees as $employee): ?>
                        <tr data-employee="<?php echo esc_attr($employee['username']); ?>">
                            <!-- Employee Column -->
                            <td class="employee-cell">
                                <div class="employee-info">
                                    <span class="employee-name"><?php echo esc_html($employee['name']); ?></span>
                                    <span class="employee-username">@<?php echo esc_html($employee['username']); ?></span>
                                    <input type="hidden" name="shifts[<?php echo esc_attr($employee['username']); ?>][employee]" value="<?php echo esc_attr($employee['username']); ?>">
                                </div>
                            </td>
                            
                            <!-- Days Columns - Editable -->
                            <?php foreach ($day_labels as $index => $day):
                                $date = $week_dates[strtolower($day)];
                                $shifts = get_shifts_for_employee($employee['username'], $date, $shift_assignments);
                            ?>
                                <td class="shift-cell editable-cell" data-date="<?php echo esc_attr($date); ?>" data-employee="<?php echo esc_attr($employee['username']); ?>">
                                    <div class="shift-editor">
                                        <div class="shift-entries" id="entries-<?php echo esc_attr($employee['username']); ?>-<?php echo esc_attr($date); ?>">
                                            <?php if (!empty($shifts)): ?>
                                                <?php foreach ($shifts as $index => $shift): 
                                                    $corp_name = '';
                                                    if ($shift && isset($shift['corp_acc_username'])) {
                                                        foreach ($corp_accounts as $corp) {
                                                            if ($corp['username'] === $shift['corp_acc_username']) {
                                                                $corp_name = $corp['company_name'];
                                                                break;
                                                            }
                                                        }
                                                    }
                                                ?>
                                                    <div class="edit-shift-entry" data-shift-id="<?php echo esc_attr($shift['id']); ?>">
                                                        <input type="hidden" name="shifts[<?php echo esc_attr($employee['username']); ?>][<?php echo esc_attr($date); ?>][<?php echo $index; ?>][id]" value="<?php echo esc_attr($shift['id']); ?>">
                                                        <div class="edit-shift-row">
                                                            <input type="time" name="shifts[<?php echo esc_attr($employee['username']); ?>][<?php echo esc_attr($date); ?>][<?php echo $index; ?>][start]" value="<?php echo esc_attr($shift['shift_start_time']); ?>" class="shift-time-input" required>
                                                            <span>to</span>
                                                            <input type="time" name="shifts[<?php echo esc_attr($employee['username']); ?>][<?php echo esc_attr($date); ?>][<?php echo $index; ?>][end]" value="<?php echo esc_attr($shift['shift_end_time']); ?>" class="shift-time-input" required>
                                                        </div>
                                                        <div class="edit-shift-corp">
                                                            <select name="shifts[<?php echo esc_attr($employee['username']); ?>][<?php echo esc_attr($date); ?>][<?php echo $index; ?>][corp]" class="shift-corp-select">
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
                                                    </div>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <div class="no-shifts">No shifts assigned</div>
                                            <?php endif; ?>
                                        </div>
                                        <?php if ($atts['allow_create'] === 'yes'): ?>
                                        <button type="button" class="add-shift-btn" onclick="addShiftEntry('<?php echo esc_js($employee['username']); ?>', '<?php echo esc_js($date); ?>')">
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
            min-width: 220px;
        }
        
        .shift-editor {
            padding: 5px;
        }
        
        .edit-shift-entry {
            background: #e6f7ff;
            border-left: 3px solid var(--shift-primary);
            padding: 8px;
            margin-bottom: 8px;
            border-radius: 4px;
        }
        
        .edit-shift-row {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 5px;
        }
        
        .shift-time-input {
            padding: 4px 6px;
            border: 1px solid #cbd5e0;
            border-radius: 4px;
            font-family: monospace;
            font-size: 12px;
            width: 80px;
        }
        
        .shift-time-input:focus {
            outline: none;
            border-color: var(--shift-primary);
            box-shadow: 0 0 0 2px rgba(30,58,138,0.1);
        }
        
        .edit-shift-corp {
            display: flex;
            gap: 5px;
            align-items: center;
        }
        
        .shift-corp-select {
            flex: 1;
            padding: 4px 6px;
            border: 1px solid #cbd5e0;
            border-radius: 4px;
            font-size: 11px;
        }
        
        .add-shift-btn {
            width: 100%;
            padding: 6px;
            background: var(--shift-success);
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 5px;
        }
        
        .add-shift-btn:hover {
            background: #0f8f5f;
        }
        
        .remove-shift-btn {
            width: 24px;
            height: 24px;
            background: var(--shift-danger);
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .remove-shift-btn:hover {
            background: #c0392b;
        }
        
        .no-shifts {
            color: var(--shift-gray);
            font-style: italic;
            padding: 8px;
            text-align: center;
            background: #f8fafc;
            border-radius: 4px;
            margin-bottom: 5px;
        }
        
        .edit-shift-entry.template {
            display: none;
        }
        </style>
        
        <script>
        let shiftCounter = {};
        
        function addShiftEntry(employee, date) {
            const container = document.getElementById(`entries-${employee}-${date}`);
            const template = document.createElement('div');
            template.className = 'edit-shift-entry new-shift';
            
            // Generate a temporary ID for new entries
            const tempId = 'new_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            
            // Get the current count for this employee/date combination
            const key = `${employee}_${date}`;
            shiftCounter[key] = (shiftCounter[key] || 0) + 1;
            const index = shiftCounter[key];
            
            template.innerHTML = `
                <input type="hidden" name="shifts[${employee}][${date}][${index}][id]" value="">
                <div class="edit-shift-row">
                    <input type="time" name="shifts[${employee}][${date}][${index}][start]" value="09:00" class="shift-time-input" required>
                    <span>to</span>
                    <input type="time" name="shifts[${employee}][${date}][${index}][end]" value="17:00" class="shift-time-input" required>
                </div>
                <div class="edit-shift-corp">
                    <select name="shifts[${employee}][${date}][${index}][corp]" class="shift-corp-select">
                        <option value="">No Corporate Account</option>
                        <?php foreach ($corp_accounts as $corp): ?>
                        <option value="<?php echo esc_attr($corp['username']); ?>"><?php echo esc_html($corp['company_name']); ?></option>
                        <?php endforeach; ?>
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
            
            timeInputs.forEach(input => {
                if (!input.value) {
                    input.style.borderColor = '#ef4444';
                    isValid = false;
                } else {
                    input.style.borderColor = '#cbd5e0';
                }
            });
            
            if (!isValid) {
                alert('Please fill in all shift times.');
                return;
            }
            
            if (confirm('Save all shift changes?')) {
                document.getElementById('shift-edit-form').submit();
            }
        }
        </script>
        
        <!-- Legend -->
        <div class="cms-shift-legend">
            <div class="cms-shift-legend-item">
                <div class="cms-shift-legend-color normal"></div>
                <span>Edit Mode - Click + Add Shift to add multiple shifts per day</span>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Helper Functions
 */
function get_week_dates($week_start = 'monday') {
    $week_offset = isset($_GET['week_offset']) ? intval($_GET['week_offset']) : 0;
    
    $dates = [];
    $current = strtotime('this ' . $week_start . ' ' . ($week_offset > 0 ? '+' . $week_offset . ' week' : ($week_offset < 0 ? $week_offset . ' week' : '')));
    
    $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
    foreach ($days as $index => $day) {
        $dates[$day] = date('Y-m-d', strtotime('+' . $index . ' days', $current));
    }
    
    return $dates;
}

function get_all_employees_for_shift() {
    return array(
        array('username' => 'noshad', 'name' => 'Noshad', 'position' => 'Senior Developer'),
        array('username' => 'ali_ahmad', 'name' => 'Ali Ahmad', 'position' => 'Developer'),
        array('username' => 'hasnain', 'name' => 'Hasnain', 'position' => 'Support Engineer'),
        array('username' => 'salman', 'name' => 'Salman', 'position' => 'System Admin'),
        array('username' => 'riyan', 'name' => 'Riyan', 'position' => 'Junior Developer'),
    );
}

function get_all_corp_accounts_for_shift() {
    return array(
        array('username' => 'techcorp', 'company_name' => 'TechCorp Solutions'),
        array('username' => 'globalfinance', 'company_name' => 'Global Finance Ltd'),
        array('username' => 'healthcare_plus', 'company_name' => 'Healthcare Plus'),
        array('username' => 'eduworld', 'company_name' => 'EduWorld International'),
        array('username' => 'green_retail', 'company_name' => 'Green Retail Chain'),
    );
}

function get_mock_shift_assignments() {
    return array(
        array(
            'id' => 1,
            'emp_username' => 'noshad',
            'date' => '2024-03-18',
            'shift_start_time' => '07:00',
            'shift_end_time' => '18:00',
            'corp_acc_username' => 'techcorp'
        ),
        array(
            'id' => 2,
            'emp_username' => 'noshad',
            'date' => '2024-03-18',
            'shift_start_time' => '09:00',
            'shift_end_time' => '12:00',
            'corp_acc_username' => 'globalfinance'
        ),
        array(
            'id' => 3,
            'emp_username' => 'noshad',
            'date' => '2024-03-19',
            'shift_start_time' => '07:00',
            'shift_end_time' => '18:00',
            'corp_acc_username' => 'techcorp'
        ),
        array(
            'id' => 4,
            'emp_username' => 'noshad',
            'date' => '2024-03-20',
            'shift_start_time' => '07:00',
            'shift_end_time' => '18:00',
            'corp_acc_username' => 'techcorp'
        ),
        array(
            'id' => 5,
            'emp_username' => 'noshad',
            'date' => '2024-03-21',
            'shift_start_time' => '07:00',
            'shift_end_time' => '19:00',
            'corp_acc_username' => 'globalfinance'
        ),
        array(
            'id' => 6,
            'emp_username' => 'noshad',
            'date' => '2024-03-23',
            'shift_start_time' => '10:00',
            'shift_end_time' => '14:00',
            'corp_acc_username' => 'green_retail'
        ),
        array(
            'id' => 7,
            'emp_username' => 'noshad',
            'date' => '2024-03-23',
            'shift_start_time' => '15:00',
            'shift_end_time' => '20:00',
            'corp_acc_username' => 'eduworld'
        ),
        array(
            'id' => 8,
            'emp_username' => 'ali_ahmad',
            'date' => '2024-03-18',
            'shift_start_time' => '11:00',
            'shift_end_time' => '21:00',
            'corp_acc_username' => 'healthcare_plus'
        ),
        array(
            'id' => 9,
            'emp_username' => 'ali_ahmad',
            'date' => '2024-03-19',
            'shift_start_time' => '11:00',
            'shift_end_time' => '21:00',
            'corp_acc_username' => 'healthcare_plus'
        ),
        array(
            'id' => 10,
            'emp_username' => 'ali_ahmad',
            'date' => '2024-03-20',
            'shift_start_time' => '11:00',
            'shift_end_time' => '21:00',
            'corp_acc_username' => 'healthcare_plus'
        ),
        array(
            'id' => 11,
            'emp_username' => 'ali_ahmad',
            'date' => '2024-03-21',
            'shift_start_time' => '11:00',
            'shift_end_time' => '21:00',
            'corp_acc_username' => 'healthcare_plus'
        ),
        array(
            'id' => 12,
            'emp_username' => 'ali_ahmad',
            'date' => '2024-03-22',
            'shift_start_time' => '11:00',
            'shift_end_time' => '21:00',
            'corp_acc_username' => 'healthcare_plus'
        ),
        array(
            'id' => 13,
            'emp_username' => 'hasnain',
            'date' => '2024-03-20',
            'shift_start_time' => '21:00',
            'shift_end_time' => '07:00',
            'corp_acc_username' => 'techcorp'
        ),
        array(
            'id' => 14,
            'emp_username' => 'hasnain',
            'date' => '2024-03-21',
            'shift_start_time' => '21:00',
            'shift_end_time' => '07:00',
            'corp_acc_username' => 'techcorp'
        ),
        array(
            'id' => 15,
            'emp_username' => 'hasnain',
            'date' => '2024-03-22',
            'shift_start_time' => '19:00',
            'shift_end_time' => '07:00',
            'corp_acc_username' => 'globalfinance'
        ),
        array(
            'id' => 16,
            'emp_username' => 'hasnain',
            'date' => '2024-03-23',
            'shift_start_time' => '19:00',
            'shift_end_time' => '08:00',
            'corp_acc_username' => 'globalfinance'
        ),
        array(
            'id' => 17,
            'emp_username' => 'hasnain',
            'date' => '2024-03-24',
            'shift_start_time' => '20:00',
            'shift_end_time' => '07:00',
            'corp_acc_username' => 'green_retail'
        ),
        array(
            'id' => 18,
            'emp_username' => 'salman',
            'date' => '2024-03-18',
            'shift_start_time' => '21:00',
            'shift_end_time' => '07:00',
            'corp_acc_username' => 'eduworld'
        ),
        array(
            'id' => 19,
            'emp_username' => 'salman',
            'date' => '2024-03-19',
            'shift_start_time' => '21:00',
            'shift_end_time' => '07:00',
            'corp_acc_username' => 'eduworld'
        ),
        array(
            'id' => 20,
            'emp_username' => 'salman',
            'date' => '2024-03-22',
            'shift_start_time' => '21:00',
            'shift_end_time' => '08:00',
            'corp_acc_username' => 'techcorp'
        ),
        array(
            'id' => 21,
            'emp_username' => 'salman',
            'date' => '2024-03-23',
            'shift_start_time' => '21:00',
            'shift_end_time' => '10:00',
            'corp_acc_username' => 'techcorp'
        ),
        array(
            'id' => 22,
            'emp_username' => 'salman',
            'date' => '2024-03-24',
            'shift_start_time' => '19:00',
            'shift_end_time' => '05:00',
            'corp_acc_username' => 'healthcare_plus'
        ),
        array(
            'id' => 23,
            'emp_username' => 'riyan',
            'date' => '2024-03-18',
            'shift_start_time' => '18:00',
            'shift_end_time' => '05:00',
            'corp_acc_username' => 'green_retail'
        ),
        array(
            'id' => 24,
            'emp_username' => 'riyan',
            'date' => '2024-03-19',
            'shift_start_time' => '18:00',
            'shift_end_time' => '05:00',
            'corp_acc_username' => 'green_retail'
        ),
        array(
            'id' => 25,
            'emp_username' => 'riyan',
            'date' => '2024-03-20',
            'shift_start_time' => '18:00',
            'shift_end_time' => '05:00',
            'corp_acc_username' => 'green_retail'
        ),
        array(
            'id' => 26,
            'emp_username' => 'riyan',
            'date' => '2024-03-21',
            'shift_start_time' => '18:00',
            'shift_end_time' => '05:00',
            'corp_acc_username' => 'globalfinance'
        ),
        array(
            'id' => 27,
            'emp_username' => 'riyan',
            'date' => '2024-03-23',
            'shift_start_time' => '07:00',
            'shift_end_time' => '19:00',
            'corp_acc_username' => 'techcorp'
        ),
        array(
            'id' => 28,
            'emp_username' => 'riyan',
            'date' => '2024-03-24',
            'shift_start_time' => '19:00',
            'shift_end_time' => '11:00',
            'corp_acc_username' => 'eduworld'
        ),
    );
}

function get_shifts_for_employee($emp_username, $date, $assignments) {
    $shifts = [];
    foreach ($assignments as $assignment) {
        if ($assignment['emp_username'] === $emp_username && $assignment['date'] === $date) {
            $shifts[] = $assignment;
        }
    }
    // Sort by start time
    usort($shifts, function($a, $b) {
        return strcmp($a['shift_start_time'], $b['shift_start_time']);
    });
    return $shifts;
}

function get_shift_assignment_by_id($id, $assignments) {
    foreach ($assignments as $assignment) {
        if ($assignment['id'] == $id) {
            return $assignment;
        }
    }
    return null;
}

function get_employee_shift_assignments($emp_username, $assignments) {
    $result = [];
    foreach ($assignments as $assignment) {
        if ($assignment['emp_username'] === $emp_username) {
            $result[] = $assignment;
        }
    }
    // Sort by date
    usort($result, function($a, $b) {
        return strcmp($b['date'], $a['date']);
    });
    return $result;
}

/**
 * Handle form submissions
 */
function cms_handle_shift_management_actions() {
    if (isset($_POST['submit_shift'])) {
        if (!isset($_POST['cms_shift_nonce']) || !wp_verify_nonce($_POST['cms_shift_nonce'], 'cms_shift_management')) {
            wp_die('Security check failed');
        }
        
        $action = $_POST['action'];
        $redirect_url = $_POST['redirect'];
        
        if ($action === 'create_shift') {
            // Here you would insert into database
            wp_redirect(add_query_arg('shift_created', '1', $redirect_url));
            exit;
        } elseif ($action === 'edit_shift') {
            // Here you would update database
            wp_redirect(add_query_arg('shift_updated', '1', $redirect_url));
            exit;
        } elseif ($action === 'save_weekly_shifts') {
            // Here you would save all shifts
            // Process $_POST['shifts'] array
            wp_redirect(add_query_arg('shifts_saved', '1', $redirect_url));
            exit;
        }
    }
    
    if (isset($_GET['action']) && $_GET['action'] === 'delete_shift' && isset($_GET['shift_id'])) {
        if (!isset($_GET['_wpnonce']) || !wp_verify_nonce($_GET['_wpnonce'], 'delete_shift_' . $_GET['shift_id'])) {
            wp_die('Security check failed');
        }
        
        $shift_id = intval($_GET['shift_id']);
        $redirect_url = $_GET['redirect'] ?? remove_query_arg(['action', 'shift_id', '_wpnonce', 'redirect']);
        
        // Here you would delete from database
        
        wp_redirect(add_query_arg('shift_deleted', '1', $redirect_url));
        exit;
    }
}
add_action('admin_post_create_shift', 'cms_handle_shift_management_actions');
add_action('admin_post_edit_shift', 'cms_handle_shift_management_actions');
add_action('admin_post_save_weekly_shifts', 'cms_handle_shift_management_actions');
add_action('admin_post_nopriv_create_shift', 'cms_handle_shift_management_actions');
add_action('admin_post_nopriv_edit_shift', 'cms_handle_shift_management_actions');
add_action('admin_post_nopriv_save_weekly_shifts', 'cms_handle_shift_management_actions');

?>