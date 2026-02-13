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
            'view' => 'weekly', // weekly, monthly, or list
            'week_start' => 'monday', // monday or sunday
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
    
    // Get action from URL
    $action = isset($_GET['shift_action']) ? sanitize_text_field($_GET['shift_action']) : 'list';
    $edit_id = isset($_GET['edit_id']) ? intval($_GET['edit_id']) : 0;
    $view_employee = isset($_GET['view_employee']) ? sanitize_text_field($_GET['view_employee']) : '';
    
    // Get mock data
    $employees = get_all_employees_for_shift();
    $corp_accounts = get_all_corp_accounts_for_shift();
    $shift_assignments = get_mock_shift_assignments();
    
    ob_start();
    
    // Handle different views
    if ($action === 'create' && $atts['allow_create'] === 'yes') {
        echo render_shift_create_form($atts, $employees, $corp_accounts, $week_dates);
    } elseif ($action === 'edit' && $edit_id > 0 && $atts['allow_edit'] === 'yes') {
        $assignment = get_shift_assignment_by_id($edit_id, $shift_assignments);
        echo render_shift_edit_form($atts, $assignment, $employees, $corp_accounts, $week_dates);
    } elseif ($action === 'view' && !empty($view_employee)) {
        $employee_shifts = get_employee_shift_assignments($view_employee, $shift_assignments);
        echo render_employee_shift_view($atts, $view_employee, $employee_shifts, $employees, $corp_accounts, $week_dates);
    } else {
        echo render_weekly_shift_table($atts, $employees, $corp_accounts, $shift_assignments, $week_dates);
    }
    
    return ob_get_clean();
}

add_shortcode('cms_emp_shift_management', 'cms_emp_shift_management_shortcode');
add_shortcode(CMS_EMP_SHIFT_MANAGEMENT_SHORTCODE, 'cms_emp_shift_management_shortcode');

/**
 * Render Weekly Shift Table (similar to the image)
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
    
    /* Table Styles - Matching the image */
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
        min-width: 1000px;
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
        min-width: 150px;
    }
    
    .cms-shift-table th.day-header {
        background: var(--shift-primary-light);
    }
    
    .cms-shift-table th.time-header {
        background: var(--shift-secondary);
        font-size: 12px;
        padding: 8px 5px;
    }
    
    .cms-shift-table td {
        border: 1px solid #e2e8f0;
        padding: 12px 8px;
        vertical-align: middle;
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
        text-align: center;
        background: white;
        transition: background 0.2s ease;
    }
    
    .cms-shift-table .shift-cell:hover {
        background: var(--shift-gray-light);
    }
    
    .cms-shift-table .shift-time {
        font-weight: 600;
        color: var(--shift-primary);
        font-family: monospace;
        font-size: 14px;
    }
    
    .cms-shift-table .shift-corp {
        font-size: 11px;
        color: var(--shift-secondary);
        margin-top: 3px;
        padding: 2px 5px;
        background: #e6f7ff;
        border-radius: 12px;
        display: inline-block;
    }
    
    .cms-shift-table .shift-actions {
        display: flex;
        gap: 5px;
        justify-content: center;
        margin-top: 5px;
    }
    
    .cms-shift-table .shift-action-icon {
        padding: 3px 8px;
        border-radius: 4px;
        font-size: 11px;
        text-decoration: none;
        color: white;
        background: var(--shift-primary-light);
    }
    
    .cms-shift-table .shift-action-icon.edit:hover {
        background: var(--shift-warning);
    }
    
    .cms-shift-table .shift-action-icon.delete:hover {
        background: var(--shift-danger);
    }
    
    .cms-shift-table .off-day {
        color: var(--shift-gray);
        font-style: italic;
        background: #f8fafc;
    }
    
    .cms-shift-table .off-day:before {
        content: "⚫";
        font-size: 10px;
        margin-right: 5px;
        color: var(--shift-gray);
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
        background: white;
        border: 2px solid var(--shift-primary);
    }
    
    .cms-shift-legend-color.off {
        background: #f8fafc;
        border: 2px dashed var(--shift-gray);
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
                <?php if ($atts['allow_create'] === 'yes'): ?>
                <a href="<?php echo esc_url(add_query_arg('shift_action', 'create')); ?>" class="cms-shift-action-btn primary">
                    + Create New Shift
                </a>
                <?php endif; ?>
                <a href="<?php echo esc_url(add_query_arg('view', 'list')); ?>" class="cms-shift-action-btn secondary">
                    📋 List View
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
            
            <div class="cms-shift-filter-group">
                <span class="cms-shift-filter-label">Shift Type:</span>
                <select class="cms-shift-filter-select" id="shift-filter" onchange="filterByShiftType()">
                    <option value="">All Shifts</option>
                    <option value="day">Day Shift (6AM-6PM)</option>
                    <option value="night">Night Shift (6PM-6AM)</option>
                    <option value="off">Off Day</option>
                </select>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Weekly Shift Table (similar to image) -->
        <div class="cms-shift-table-container">
            <table class="cms-shift-table" id="shift-table">
                <thead>
                    <tr>
                        <th rowspan="2" class="employee-column">Employee</th>
                        <?php foreach ($day_labels as $index => $day): ?>
                        <th colspan="2" class="day-header"><?php echo esc_html($day); ?><br><small><?php echo date('M d', strtotime($week_dates[strtolower($day)])); ?></small></th>
                        <?php endforeach; ?>
                        <th rowspan="2">Actions</th>
                    </tr>
                    <tr>
                        <?php foreach ($day_labels as $day): ?>
                        <th class="time-header">In</th>
                        <th class="time-header">Out</th>
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
                            $shift = get_shift_for_employee($employee['username'], $date, $shift_assignments);
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
                            <?php if ($shift): ?>
                            <td class="shift-cell" data-corp="<?php echo esc_attr($shift['corp_acc_username'] ?? ''); ?>">
                                <span class="shift-time"><?php echo esc_html($shift['shift_start_time']); ?></span>
                                <?php if ($shift['corp_acc_username']): ?>
                                <div class="shift-corp"><?php echo esc_html($corp_name); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="shift-cell" data-corp="<?php echo esc_attr($shift['corp_acc_username'] ?? ''); ?>">
                                <span class="shift-time"><?php echo esc_html($shift['shift_end_time']); ?></span>
                                <?php if ($atts['allow_edit'] === 'yes'): ?>
                                <div class="shift-actions">
                                    <a href="<?php echo esc_url(add_query_arg(['shift_action' => 'edit', 'edit_id' => $shift['id']])); ?>" class="shift-action-icon edit">✏️</a>
                                    <?php if ($atts['allow_delete'] === 'yes'): ?>
                                    <a href="#" onclick="confirmDelete(<?php echo esc_js($shift['id']); ?>); return false;" class="shift-action-icon delete">🗑️</a>
                                    <?php endif; ?>
                                </div>
                                <?php endif; ?>
                            </td>
                            <?php else: ?>
                            <td class="shift-cell off-day" colspan="2">
                                <span class="off-day">Off</span>
                            </td>
                            <?php endif; ?>
                        <?php endforeach; ?>
                        
                        <!-- Actions Column -->
                        <td class="shift-cell">
                            <a href="<?php echo esc_url(add_query_arg(['shift_action' => 'view', 'view_employee' => $employee['username']])); ?>" class="shift-action-icon" style="background: var(--shift-info);">👁️ View</a>
                        </td>
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
    
    <!-- Delete Confirmation Modal -->
    <div id="cms-shift-delete-modal" class="cms-shift-modal">
        <div class="cms-shift-modal-content">
            <div class="cms-shift-modal-header">
                <h3 class="cms-shift-modal-title">Confirm Delete</h3>
                <button class="cms-shift-modal-close" onclick="closeDeleteModal()">×</button>
            </div>
            <div class="cms-shift-modal-body">
                <p>Are you sure you want to delete this shift assignment?</p>
                <p style="color: #ef4444; font-size: 13px;">This action cannot be undone.</p>
            </div>
            <div class="cms-shift-modal-footer">
                <button class="cms-shift-modal-btn cancel" onclick="closeDeleteModal()">Cancel</button>
                <button class="cms-shift-modal-btn confirm" id="confirm-delete-btn">Delete Shift</button>
            </div>
        </div>
    </div>
    
    <script>
    var currentDeleteId = 0;
    
    function confirmDelete(id) {
        currentDeleteId = id;
        document.getElementById('cms-shift-delete-modal').style.display = 'flex';
    }
    
    function closeDeleteModal() {
        document.getElementById('cms-shift-delete-modal').style.display = 'none';
    }
    
    document.getElementById('confirm-delete-btn').addEventListener('click', function() {
        if (currentDeleteId) {
            window.location.href = '<?php echo esc_url(admin_url('admin-post.php?action=delete_shift&shift_id=')); ?>' + currentDeleteId + '&redirect=' + encodeURIComponent(window.location.href);
        }
    });
    
    function filterByCorp() {
        var corp = document.getElementById('corp-filter').value;
        var cells = document.querySelectorAll('.shift-cell[data-corp]');
        
        cells.forEach(function(cell) {
            if (corp === '') {
                cell.style.opacity = '1';
            } else {
                if (cell.getAttribute('data-corp') === corp) {
                    cell.style.opacity = '1';
                    cell.style.backgroundColor = '#e6f7ff';
                } else {
                    cell.style.opacity = '0.3';
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
    
    function filterByShiftType() {
        var type = document.getElementById('shift-filter').value;
        var rows = document.querySelectorAll('#shift-table tbody tr');
        
        rows.forEach(function(row) {
            var cells = row.querySelectorAll('.shift-cell');
            var hasVisibleShift = false;
            
            cells.forEach(function(cell) {
                if (type === '') {
                    cell.style.display = '';
                } else if (type === 'off') {
                    if (cell.classList.contains('off-day')) {
                        cell.style.display = '';
                        hasVisibleShift = true;
                    } else {
                        cell.style.display = 'none';
                    }
                } else if (type === 'day') {
                    var time = cell.querySelector('.shift-time')?.textContent || '';
                    if (time >= '06:00' && time <= '18:00' && !cell.classList.contains('off-day')) {
                        cell.style.display = '';
                        hasVisibleShift = true;
                    } else {
                        cell.style.display = 'none';
                    }
                } else if (type === 'night') {
                    var time = cell.querySelector('.shift-time')?.textContent || '';
                    if ((time >= '18:00' || time <= '06:00') && !cell.classList.contains('off-day')) {
                        cell.style.display = '';
                        hasVisibleShift = true;
                    } else {
                        cell.style.display = 'none';
                    }
                }
            });
        });
    }
    </script>
    
    <?php
}

/**
 * Render Create Shift Form
 */
function render_shift_create_form($atts, $employees, $corp_accounts, $week_dates) {
    ?>
    <div class="cms-shift-container">
        <div class="cms-shift-header">
            <div class="cms-shift-title-section">
                <h1 class="cms-shift-title">Create New Shift Assignment</h1>
            </div>
            <div class="cms-shift-actions">
                <a href="<?php echo esc_url(remove_query_arg(['shift_action', 'edit_id'])); ?>" class="cms-shift-action-btn secondary">← Back to Schedule</a>
            </div>
        </div>
        
        <style>
        .cms-shift-form {
            max-width: 600px;
            margin: 0 auto;
            background: #f8fafc;
            padding: 30px;
            border-radius: 16px;
        }
        
        .cms-shift-form-group {
            margin-bottom: 20px;
        }
        
        .cms-shift-form-label {
            display: block;
            font-weight: 600;
            color: var(--shift-primary);
            margin-bottom: 8px;
        }
        
        .cms-shift-form-control {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 15px;
            transition: all 0.2s ease;
        }
        
        .cms-shift-form-control:focus {
            outline: none;
            border-color: var(--shift-primary);
            box-shadow: 0 0 0 3px rgba(30,58,138,0.05);
        }
        
        .cms-shift-form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .cms-shift-form-actions {
            display: flex;
            gap: 15px;
            justify-content: flex-end;
            margin-top: 30px;
        }
        
        .cms-shift-form-btn {
            padding: 12px 30px;
            border: none;
            border-radius: 40px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .cms-shift-form-btn.primary {
            background: var(--shift-primary);
            color: white;
        }
        
        .cms-shift-form-btn.primary:hover {
            background: var(--shift-primary-dark);
            transform: translateY(-2px);
        }
        
        .cms-shift-form-btn.secondary {
            background: #e2e8f0;
            color: var(--shift-primary);
        }
        </style>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cms-shift-form">
            <?php wp_nonce_field('cms_shift_management', 'cms_shift_nonce'); ?>
            <input type="hidden" name="action" value="create_shift">
            <input type="hidden" name="redirect" value="<?php echo esc_url(remove_query_arg(['shift_action', 'edit_id'])); ?>">
            
            <div class="cms-shift-form-group">
                <label class="cms-shift-form-label">Select Employee</label>
                <select name="emp_username" class="cms-shift-form-control" required>
                    <option value="">Choose Employee</option>
                    <?php foreach ($employees as $emp): ?>
                    <option value="<?php echo esc_attr($emp['username']); ?>">
                        <?php echo esc_html($emp['name']); ?> (@<?php echo esc_html($emp['username']); ?>) - <?php echo esc_html($emp['position']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="cms-shift-form-group">
                <label class="cms-shift-form-label">Select Date</label>
                <input type="date" name="shift_date" class="cms-shift-form-control" required min="<?php echo date('Y-m-d', strtotime('-1 month')); ?>" max="<?php echo date('Y-m-d', strtotime('+3 months')); ?>">
            </div>
            
            <div class="cms-shift-form-row">
                <div class="cms-shift-form-group">
                    <label class="cms-shift-form-label">Start Time</label>
                    <input type="time" name="shift_start_time" class="cms-shift-form-control" required>
                </div>
                
                <div class="cms-shift-form-group">
                    <label class="cms-shift-form-label">End Time</label>
                    <input type="time" name="shift_end_time" class="cms-shift-form-control" required>
                </div>
            </div>
            
            <div class="cms-shift-form-group">
                <label class="cms-shift-form-label">Assign Corporate Account (Optional)</label>
                <select name="corp_acc_username" class="cms-shift-form-control">
                    <option value="">No Corporate Account</option>
                    <?php foreach ($corp_accounts as $corp): ?>
                    <option value="<?php echo esc_attr($corp['username']); ?>">
                        <?php echo esc_html($corp['company_name']); ?> (@<?php echo esc_html($corp['username']); ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="cms-shift-form-actions">
                <a href="<?php echo esc_url(remove_query_arg(['shift_action', 'edit_id'])); ?>" class="cms-shift-form-btn secondary">Cancel</a>
                <button type="submit" name="submit_shift" class="cms-shift-form-btn primary">Create Shift Assignment</button>
            </div>
        </form>
    </div>
    <?php
}

/**
 * Render Edit Shift Form
 */
function render_shift_edit_form($atts, $assignment, $employees, $corp_accounts, $week_dates) {
    if (!$assignment) {
        echo '<div class="cms-shift-container"><p style="color: #ef4444;">Shift assignment not found.</p></div>';
        return;
    }
    ?>
    <div class="cms-shift-container">
        <div class="cms-shift-header">
            <div class="cms-shift-title-section">
                <h1 class="cms-shift-title">Edit Shift Assignment</h1>
            </div>
            <div class="cms-shift-actions">
                <a href="<?php echo esc_url(remove_query_arg(['shift_action', 'edit_id'])); ?>" class="cms-shift-action-btn secondary">← Back to Schedule</a>
            </div>
        </div>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cms-shift-form">
            <?php wp_nonce_field('cms_shift_management', 'cms_shift_nonce'); ?>
            <input type="hidden" name="action" value="edit_shift">
            <input type="hidden" name="shift_id" value="<?php echo esc_attr($assignment['id']); ?>">
            <input type="hidden" name="redirect" value="<?php echo esc_url(remove_query_arg(['shift_action', 'edit_id'])); ?>">
            
            <div class="cms-shift-form-group">
                <label class="cms-shift-form-label">Employee</label>
                <input type="text" class="cms-shift-form-control" value="<?php echo esc_html($assignment['emp_username']); ?>" readonly disabled>
                <input type="hidden" name="emp_username" value="<?php echo esc_attr($assignment['emp_username']); ?>">
            </div>
            
            <div class="cms-shift-form-group">
                <label class="cms-shift-form-label">Date</label>
                <input type="date" name="shift_date" class="cms-shift-form-control" value="<?php echo esc_attr($assignment['date']); ?>" required>
            </div>
            
            <div class="cms-shift-form-row">
                <div class="cms-shift-form-group">
                    <label class="cms-shift-form-label">Start Time</label>
                    <input type="time" name="shift_start_time" class="cms-shift-form-control" value="<?php echo esc_attr($assignment['shift_start_time']); ?>" required>
                </div>
                
                <div class="cms-shift-form-group">
                    <label class="cms-shift-form-label">End Time</label>
                    <input type="time" name="shift_end_time" class="cms-shift-form-control" value="<?php echo esc_attr($assignment['shift_end_time']); ?>" required>
                </div>
            </div>
            
            <div class="cms-shift-form-group">
                <label class="cms-shift-form-label">Corporate Account</label>
                <select name="corp_acc_username" class="cms-shift-form-control">
                    <option value="">No Corporate Account</option>
                    <?php foreach ($corp_accounts as $corp): ?>
                    <option value="<?php echo esc_attr($corp['username']); ?>" <?php selected($assignment['corp_acc_username'] ?? '', $corp['username']); ?>>
                        <?php echo esc_html($corp['company_name']); ?> (@<?php echo esc_html($corp['username']); ?>)
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="cms-shift-form-actions">
                <a href="<?php echo esc_url(remove_query_arg(['shift_action', 'edit_id'])); ?>" class="cms-shift-form-btn secondary">Cancel</a>
                <button type="submit" name="submit_shift" class="cms-shift-form-btn primary">Update Shift Assignment</button>
            </div>
        </form>
    </div>
    <?php
}

/**
 * Render Employee Shift View
 */
function render_employee_shift_view($atts, $employee_username, $employee_shifts, $employees, $corp_accounts, $week_dates) {
    $employee = null;
    foreach ($employees as $emp) {
        if ($emp['username'] === $employee_username) {
            $employee = $emp;
            break;
        }
    }
    
    if (!$employee) {
        echo '<div class="cms-shift-container"><p style="color: #ef4444;">Employee not found.</p></div>';
        return;
    }
    ?>
    <div class="cms-shift-container">
        <div class="cms-shift-header">
            <div class="cms-shift-title-section">
                <div class="cms-shift-employee-avatar" style="width: 60px; height: 60px; background: var(--shift-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; color: white; font-size: 24px; font-weight: 700;">
                    <?php echo strtoupper(substr($employee['name'], 0, 1)); ?>
                </div>
                <div>
                    <h1 class="cms-shift-title"><?php echo esc_html($employee['name']); ?>'s Shifts</h1>
                    <p style="color: #64748b;">@<?php echo esc_html($employee['username']); ?> • <?php echo esc_html($employee['position']); ?></p>
                </div>
            </div>
            <div class="cms-shift-actions">
                <a href="<?php echo esc_url(remove_query_arg(['shift_action', 'view_employee'])); ?>" class="cms-shift-action-btn secondary">← Back to Schedule</a>
            </div>
        </div>
        
        <style>
        .cms-shift-employee-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .cms-shift-employee-table th {
            background: var(--shift-primary);
            color: white;
            padding: 12px;
            text-align: left;
        }
        
        .cms-shift-employee-table td {
            padding: 12px;
            border-bottom: 1px solid #e2e8f0;
        }
        
        .cms-shift-employee-table tr:hover {
            background: #f8fafc;
        }
        
        .cms-shift-corp-badge {
            background: #e6f7ff;
            color: var(--shift-primary);
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
        }
        </style>
        
        <table class="cms-shift-employee-table">
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Day</th>
                    <th>Shift Start</th>
                    <th>Shift End</th>
                    <th>Duration</th>
                    <th>Corporate Account</th>
                    <?php if ($atts['allow_edit'] === 'yes'): ?>
                    <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php 
                $total_hours = 0;
                $total_minutes = 0;
                
                if (empty($employee_shifts)): 
                ?>
                <tr>
                    <td colspan="7" style="text-align: center; padding: 40px; color: #64748b;">
                        No shifts assigned to this employee yet.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($employee_shifts as $shift): 
                        // Calculate duration
                        $start = strtotime($shift['shift_start_time']);
                        $end = strtotime($shift['shift_end_time']);
                        if ($end < $start) {
                            $end += 24 * 3600; // Add 24 hours if end time is next day
                        }
                        $duration = $end - $start;
                        $hours = floor($duration / 3600);
                        $minutes = floor(($duration % 3600) / 60);
                        $total_hours += $hours;
                        $total_minutes += $minutes;
                        
                        // Find corporate account name
                        $corp_name = 'None';
                        foreach ($corp_accounts as $corp) {
                            if ($corp['username'] === ($shift['corp_acc_username'] ?? '')) {
                                $corp_name = $corp['company_name'];
                                break;
                            }
                        }
                    ?>
                    <tr>
                        <td><strong><?php echo date('M d, Y', strtotime($shift['date'])); ?></strong></td>
                        <td><?php echo date('l', strtotime($shift['date'])); ?></td>
                        <td><?php echo esc_html($shift['shift_start_time']); ?></td>
                        <td><?php echo esc_html($shift['shift_end_time']); ?></td>
                        <td><?php echo $hours; ?>h <?php echo $minutes; ?>m</td>
                        <td>
                            <?php if (!empty($shift['corp_acc_username'])): ?>
                            <span class="cms-shift-corp-badge"><?php echo esc_html($corp_name); ?></span>
                            <?php else: ?>
                            <span style="color: #94a3b8;">—</span>
                            <?php endif; ?>
                        </td>
                        <?php if ($atts['allow_edit'] === 'yes'): ?>
                        <td>
                            <a href="<?php echo esc_url(add_query_arg(['shift_action' => 'edit', 'edit_id' => $shift['id']])); ?>" style="color: var(--shift-primary); text-decoration: none; margin-right: 10px;">✏️ Edit</a>
                            <?php if ($atts['allow_delete'] === 'yes'): ?>
                            <a href="#" onclick="confirmDelete(<?php echo esc_js($shift['id']); ?>); return false;" style="color: #ef4444; text-decoration: none;">🗑️ Delete</a>
                            <?php endif; ?>
                        </td>
                        <?php endif; ?>
                    </tr>
                    <?php endforeach; ?>
                    
                    <!-- Summary Row -->
                    <?php 
                    $total_hours += floor($total_minutes / 60);
                    $total_minutes = $total_minutes % 60;
                    ?>
                    <tr style="background: #f8fafc; font-weight: 600;">
                        <td colspan="4" style="text-align: right;">Total:</td>
                        <td><?php echo $total_hours; ?>h <?php echo $total_minutes; ?>m</td>
                        <td colspan="2"></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
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
            'date' => '2024-03-19',
            'shift_start_time' => '07:00',
            'shift_end_time' => '18:00',
            'corp_acc_username' => 'techcorp'
        ),
        array(
            'id' => 3,
            'emp_username' => 'noshad',
            'date' => '2024-03-20',
            'shift_start_time' => '07:00',
            'shift_end_time' => '18:00',
            'corp_acc_username' => 'techcorp'
        ),
        array(
            'id' => 4,
            'emp_username' => 'noshad',
            'date' => '2024-03-21',
            'shift_start_time' => '07:00',
            'shift_end_time' => '19:00',
            'corp_acc_username' => 'globalfinance'
        ),
        array(
            'id' => 5,
            'emp_username' => 'noshad',
            'date' => '2024-03-23',
            'shift_start_time' => '10:00',
            'shift_end_time' => '20:00',
            'corp_acc_username' => 'green_retail'
        ),
        array(
            'id' => 6,
            'emp_username' => 'ali_ahmad',
            'date' => '2024-03-18',
            'shift_start_time' => '11:00',
            'shift_end_time' => '21:00',
            'corp_acc_username' => 'healthcare_plus'
        ),
        array(
            'id' => 7,
            'emp_username' => 'ali_ahmad',
            'date' => '2024-03-19',
            'shift_start_time' => '11:00',
            'shift_end_time' => '21:00',
            'corp_acc_username' => 'healthcare_plus'
        ),
        array(
            'id' => 8,
            'emp_username' => 'ali_ahmad',
            'date' => '2024-03-20',
            'shift_start_time' => '11:00',
            'shift_end_time' => '21:00',
            'corp_acc_username' => 'healthcare_plus'
        ),
        array(
            'id' => 9,
            'emp_username' => 'ali_ahmad',
            'date' => '2024-03-21',
            'shift_start_time' => '11:00',
            'shift_end_time' => '21:00',
            'corp_acc_username' => 'healthcare_plus'
        ),
        array(
            'id' => 10,
            'emp_username' => 'ali_ahmad',
            'date' => '2024-03-22',
            'shift_start_time' => '11:00',
            'shift_end_time' => '21:00',
            'corp_acc_username' => 'healthcare_plus'
        ),
        array(
            'id' => 11,
            'emp_username' => 'hasnain',
            'date' => '2024-03-20',
            'shift_start_time' => '21:00',
            'shift_end_time' => '07:00',
            'corp_acc_username' => 'techcorp'
        ),
        array(
            'id' => 12,
            'emp_username' => 'hasnain',
            'date' => '2024-03-21',
            'shift_start_time' => '21:00',
            'shift_end_time' => '07:00',
            'corp_acc_username' => 'techcorp'
        ),
        array(
            'id' => 13,
            'emp_username' => 'hasnain',
            'date' => '2024-03-22',
            'shift_start_time' => '19:00',
            'shift_end_time' => '07:00',
            'corp_acc_username' => 'globalfinance'
        ),
        array(
            'id' => 14,
            'emp_username' => 'hasnain',
            'date' => '2024-03-23',
            'shift_start_time' => '19:00',
            'shift_end_time' => '08:00',
            'corp_acc_username' => 'globalfinance'
        ),
        array(
            'id' => 15,
            'emp_username' => 'hasnain',
            'date' => '2024-03-24',
            'shift_start_time' => '20:00',
            'shift_end_time' => '07:00',
            'corp_acc_username' => 'green_retail'
        ),
        array(
            'id' => 16,
            'emp_username' => 'salman',
            'date' => '2024-03-18',
            'shift_start_time' => '21:00',
            'shift_end_time' => '07:00',
            'corp_acc_username' => 'eduworld'
        ),
        array(
            'id' => 17,
            'emp_username' => 'salman',
            'date' => '2024-03-19',
            'shift_start_time' => '21:00',
            'shift_end_time' => '07:00',
            'corp_acc_username' => 'eduworld'
        ),
        array(
            'id' => 18,
            'emp_username' => 'salman',
            'date' => '2024-03-22',
            'shift_start_time' => '21:00',
            'shift_end_time' => '08:00',
            'corp_acc_username' => 'techcorp'
        ),
        array(
            'id' => 19,
            'emp_username' => 'salman',
            'date' => '2024-03-23',
            'shift_start_time' => '21:00',
            'shift_end_time' => '10:00',
            'corp_acc_username' => 'techcorp'
        ),
        array(
            'id' => 20,
            'emp_username' => 'salman',
            'date' => '2024-03-24',
            'shift_start_time' => '19:00',
            'shift_end_time' => '05:00',
            'corp_acc_username' => 'healthcare_plus'
        ),
        array(
            'id' => 21,
            'emp_username' => 'riyan',
            'date' => '2024-03-18',
            'shift_start_time' => '18:00',
            'shift_end_time' => '05:00',
            'corp_acc_username' => 'green_retail'
        ),
        array(
            'id' => 22,
            'emp_username' => 'riyan',
            'date' => '2024-03-19',
            'shift_start_time' => '18:00',
            'shift_end_time' => '05:00',
            'corp_acc_username' => 'green_retail'
        ),
        array(
            'id' => 23,
            'emp_username' => 'riyan',
            'date' => '2024-03-20',
            'shift_start_time' => '18:00',
            'shift_end_time' => '05:00',
            'corp_acc_username' => 'green_retail'
        ),
        array(
            'id' => 24,
            'emp_username' => 'riyan',
            'date' => '2024-03-21',
            'shift_start_time' => '18:00',
            'shift_end_time' => '05:00',
            'corp_acc_username' => 'globalfinance'
        ),
        array(
            'id' => 25,
            'emp_username' => 'riyan',
            'date' => '2024-03-23',
            'shift_start_time' => '07:00',
            'shift_end_time' => '19:00',
            'corp_acc_username' => 'techcorp'
        ),
        array(
            'id' => 26,
            'emp_username' => 'riyan',
            'date' => '2024-03-24',
            'shift_start_time' => '19:00',
            'shift_end_time' => '11:00',
            'corp_acc_username' => 'eduworld'
        ),
    );
}

function get_shift_for_employee($emp_username, $date, $assignments) {
    foreach ($assignments as $assignment) {
        if ($assignment['emp_username'] === $emp_username && $assignment['date'] === $date) {
            return $assignment;
        }
    }
    return null;
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
            // For mock, just redirect with success
            wp_redirect(add_query_arg('shift_created', '1', $redirect_url));
            exit;
        } elseif ($action === 'edit_shift') {
            // Here you would update database
            wp_redirect(add_query_arg('shift_updated', '1', $redirect_url));
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
add_action('admin_post_nopriv_create_shift', 'cms_handle_shift_management_actions');
add_action('admin_post_nopriv_edit_shift', 'cms_handle_shift_management_actions');

?>