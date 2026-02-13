<?php
/**
 * CMS Employee Shift History List Shortcode
 * Display shift history with date range filtering
 * 
 * Fields: username, date, actual_login_time, actual_logout_time, actual_hours, actual_mins,
 *         counted_login_time, counted_logout_time, counted_hours, counted_mins
 * 
 * Usage: [cms_emp_shift_history_list]
 * Usage: [cms_emp_shift_history_list title="Shift History" show_filters="yes"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define shortcode slug
if (!defined('CMS_EMP_SHIFT_HISTORY_LIST_SHORTCODE')) {
    define('CMS_EMP_SHIFT_HISTORY_LIST_SHORTCODE', 'cms_emp_shift_history_list');
}

/**
 * Employee Shift History List Shortcode
 */
function cms_emp_shift_history_list_shortcode($atts) {
    // Parse attributes
    $atts = shortcode_atts(
        array(
            'title' => 'Employee Shift History',
            'description' => 'View and filter shift history records',
            'show_filters' => 'yes',
            'show_employee_filter' => 'yes',
            'items_per_page' => 20,
            'class' => '',
            'no_data_message' => 'No shift history found for the selected criteria.',
            'date_format' => 'M d, Y',
            'time_format' => 'h:i A'
        ),
        $atts,
        'cms_emp_shift_history_list'
    );
    
    ob_start();
    
    // Get filter values
    $filter_employee = isset($_GET['filter_employee']) ? sanitize_text_field($_GET['filter_employee']) : '';
    $filter_date_from = isset($_GET['filter_date_from']) ? sanitize_text_field($_GET['filter_date_from']) : date('Y-m-d', strtotime('-30 days'));
    $filter_date_to = isset($_GET['filter_date_to']) ? sanitize_text_field($_GET['filter_date_to']) : date('Y-m-d');
    
    // Get mock data
    $employees = get_all_employees_for_filter();
    $shift_history = get_filtered_shift_history($filter_employee, $filter_date_from, $filter_date_to);
    
    // Calculate totals
    $total_actual_hours = 0;
    $total_actual_mins = 0;
    $total_counted_hours = 0;
    $total_counted_mins = 0;
    
    foreach ($shift_history as $record) {
        $total_actual_hours += $record['actual_hours'];
        $total_actual_mins += $record['actual_mins'];
        $total_counted_hours += $record['counted_hours'];
        $total_counted_mins += $record['counted_mins'];
    }
    
    // Normalize minutes
    $total_actual_hours += floor($total_actual_mins / 60);
    $total_actual_mins = $total_actual_mins % 60;
    $total_counted_hours += floor($total_counted_mins / 60);
    $total_counted_mins = $total_counted_mins % 60;
    
    ?>
    
    <style>
    /* Shift History List Styles - Green Theme */
    :root {
        --history-primary: #27ae60;
        --history-primary-dark: #219a52;
        --history-primary-light: #6fcf97;
        --history-secondary: #3498db;
        --history-danger: #e74c3c;
        --history-warning: #f39c12;
        --history-success: #27ae60;
    }
    
    .cms-history-container {
        max-width: 1300px;
        margin: 30px auto;
        padding: 30px;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(39,174,96,0.08);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        border-top: 5px solid var(--history-primary);
    }
    
    .cms-history-header {
        margin-bottom: 30px;
    }
    
    .cms-history-title {
        margin: 0 0 10px 0;
        font-size: 28px;
        font-weight: 700;
        color: var(--history-primary-dark);
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cms-history-title:before {
        content: '📊';
        font-size: 32px;
    }
    
    .cms-history-description {
        margin: 0;
        font-size: 15px;
        color: #6c7a89;
        line-height: 1.6;
    }
    
    /* Filter Section */
    .cms-history-filters {
        background: #f0f9f4;
        border: 2px solid var(--history-primary-light);
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 30px;
    }
    
    .cms-history-filter-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--history-primary-dark);
        margin: 0 0 15px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .cms-history-filter-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px;
        margin-bottom: 20px;
    }
    
    .cms-history-filter-group {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
    
    .cms-history-filter-group label {
        font-size: 13px;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .cms-history-filter-control {
        padding: 12px 15px;
        border: 2px solid #d1e7d6;
        border-radius: 10px;
        font-size: 14px;
        transition: all 0.25s ease;
        background: white;
    }
    
    .cms-history-filter-control:focus {
        outline: none;
        border-color: var(--history-primary);
        box-shadow: 0 0 0 3px rgba(39,174,96,0.05);
    }
    
    .cms-history-filter-actions {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-top: 20px;
    }
    
    .cms-history-filter-btn {
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
    
    .cms-history-filter-btn.apply {
        background: var(--history-primary);
        color: white;
    }
    
    .cms-history-filter-btn.apply:hover {
        background: var(--history-primary-dark);
        transform: translateY(-1px);
    }
    
    .cms-history-filter-btn.reset {
        background: #e2e8f0;
        color: #4a5568;
    }
    
    .cms-history-filter-btn.reset:hover {
        background: #cbd5e0;
    }
    
    /* Summary Cards */
    .cms-history-summary {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .cms-history-summary-card {
        background: linear-gradient(145deg, #f0f9f4, #ffffff);
        border: 2px solid var(--history-primary-light);
        border-radius: 16px;
        padding: 20px;
        text-align: center;
    }
    
    .cms-history-summary-value {
        font-size: 28px;
        font-weight: 700;
        color: var(--history-primary-dark);
        line-height: 1.2;
        margin-bottom: 5px;
    }
    
    .cms-history-summary-label {
        font-size: 13px;
        color: #6c7a89;
        font-weight: 500;
    }
    
    .cms-history-summary-sub {
        font-size: 12px;
        color: #95a5a6;
        margin-top: 5px;
    }
    
    /* Table Styles */
    .cms-history-table-responsive {
        overflow-x: auto;
        margin-bottom: 30px;
        border-radius: 16px;
        border: 2px solid #eef2f6;
    }
    
    .cms-history-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        background: white;
    }
    
    .cms-history-table th {
        background: #f0f9f4;
        color: var(--history-primary-dark);
        font-weight: 600;
        padding: 16px 12px;
        text-align: left;
        border-bottom: 2px solid var(--history-primary-light);
        white-space: nowrap;
    }
    
    .cms-history-table td {
        padding: 14px 12px;
        border-bottom: 1px solid #eef2f6;
        color: #2c3e50;
    }
    
    .cms-history-table tr:hover {
        background: #f8fafc;
    }
    
    .cms-history-table tr.cms-history-odd {
        background: #fcfcfc;
    }
    
    .cms-history-employee-info {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cms-history-employee-avatar {
        width: 35px;
        height: 35px;
        background: linear-gradient(145deg, var(--history-primary), var(--history-primary-dark));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 14px;
    }
    
    .cms-history-employee-name {
        font-weight: 600;
        color: #2c3e50;
    }
    
    .cms-history-employee-username {
        font-size: 11px;
        color: #718096;
    }
    
    .cms-history-time-badge {
        font-family: monospace;
        font-weight: 600;
    }
    
    .cms-history-hours {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }
    
    .cms-history-hours-actual {
        color: var(--history-primary-dark);
        font-weight: 600;
    }
    
    .cms-history-hours-counted {
        color: var(--history-secondary);
        font-weight: 600;
    }
    
    .cms-history-diff {
        font-weight: 600;
        font-size: 13px;
    }
    
    .cms-history-diff.positive {
        color: var(--history-success);
    }
    
    .cms-history-diff.negative {
        color: var(--history-danger);
    }
    
    .cms-history-diff.neutral {
        color: var(--history-warning);
    }
    
    /* Pagination */
    .cms-history-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 30px;
        flex-wrap: wrap;
    }
    
    .cms-history-page-link {
        padding: 10px 16px;
        background: white;
        border: 1px solid #d1e7d6;
        border-radius: 8px;
        color: #4a5568;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    
    .cms-history-page-link:hover {
        background: #f0f9f4;
        border-color: var(--history-primary);
        color: var(--history-primary);
    }
    
    .cms-history-page-link.active {
        background: var(--history-primary);
        color: white;
        border-color: var(--history-primary);
    }
    
    /* Export Options */
    .cms-history-export {
        display: flex;
        gap: 10px;
        justify-content: flex-end;
        margin-bottom: 20px;
    }
    
    .cms-history-export-btn {
        padding: 10px 20px;
        background: white;
        border: 2px solid var(--history-primary-light);
        border-radius: 40px;
        color: var(--history-primary-dark);
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .cms-history-export-btn:hover {
        background: var(--history-primary);
        color: white;
        border-color: var(--history-primary);
    }
    
    /* No Data */
    .cms-history-no-data {
        text-align: center;
        padding: 60px 20px;
        background: #f0f9f4;
        border-radius: 16px;
        color: #6c7a89;
        font-size: 16px;
    }
    
    .cms-history-no-data:before {
        content: '📊';
        display: block;
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.5;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .cms-history-summary {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .cms-history-filter-grid {
            grid-template-columns: 1fr;
        }
        
        .cms-history-filter-actions {
            flex-direction: column;
        }
        
        .cms-history-filter-btn {
            width: 100%;
            justify-content: center;
        }
        
        .cms-history-table th,
        .cms-history-table td {
            padding: 12px 8px;
            font-size: 13px;
        }
        
        .cms-history-export {
            flex-wrap: wrap;
        }
        
        .cms-history-export-btn {
            flex: 1;
            justify-content: center;
        }
    }
    
    @media (max-width: 480px) {
        .cms-history-summary {
            grid-template-columns: 1fr;
        }
    }
    </style>
    
    <div class="cms-history-container <?php echo esc_attr($atts['class']); ?>">
        
        <div class="cms-history-header">
            <h2 class="cms-history-title"><?php echo esc_html($atts['title']); ?></h2>
            <?php if (!empty($atts['description'])): ?>
                <p class="cms-history-description"><?php echo esc_html($atts['description']); ?></p>
            <?php endif; ?>
        </div>
        
        <?php if ($atts['show_filters'] === 'yes'): ?>
        <!-- Filter Section -->
        <div class="cms-history-filters">
            <div class="cms-history-filter-title">
                <span>🔍</span> Filter Shift History
            </div>
            
            <form method="get" action="" class="cms-history-filter-form">
                <div class="cms-history-filter-grid">
                    <?php if ($atts['show_employee_filter'] === 'yes'): ?>
                    <div class="cms-history-filter-group">
                        <label for="filter_employee">Employee</label>
                        <select name="filter_employee" id="filter_employee" class="cms-history-filter-control">
                            <option value="">All Employees</option>
                            <?php foreach ($employees as $emp): ?>
                            <option value="<?php echo esc_attr($emp['username']); ?>" <?php selected($filter_employee, $emp['username']); ?>>
                                <?php echo esc_html($emp['name']); ?> (@<?php echo esc_html($emp['username']); ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <?php endif; ?>
                    
                    <div class="cms-history-filter-group">
                        <label for="filter_date_from">From Date</label>
                        <input type="date" id="filter_date_from" name="filter_date_from" class="cms-history-filter-control" value="<?php echo esc_attr($filter_date_from); ?>">
                    </div>
                    
                    <div class="cms-history-filter-group">
                        <label for="filter_date_to">To Date</label>
                        <input type="date" id="filter_date_to" name="filter_date_to" class="cms-history-filter-control" value="<?php echo esc_attr($filter_date_to); ?>">
                    </div>
                </div>
                
                <div class="cms-history-filter-actions">
                    <button type="submit" class="cms-history-filter-btn apply">
                        <span>✓</span> Apply Filters
                    </button>
                    <a href="<?php echo esc_url(remove_query_arg(array('filter_employee', 'filter_date_from', 'filter_date_to'))); ?>" class="cms-history-filter-btn reset">
                        <span>↺</span> Reset
                    </a>
                </div>
            </form>
        </div>
        <?php endif; ?>
        
        <!-- Summary Cards -->
        <div class="cms-history-summary">
            <div class="cms-history-summary-card">
                <div class="cms-history-summary-value"><?php echo count($shift_history); ?></div>
                <div class="cms-history-summary-label">Total Records</div>
            </div>
            
            <div class="cms-history-summary-card">
                <div class="cms-history-summary-value"><?php echo $total_actual_hours; ?>h <?php echo $total_actual_mins; ?>m</div>
                <div class="cms-history-summary-label">Total Actual Hours</div>
            </div>
            
            <div class="cms-history-summary-card">
                <div class="cms-history-summary-value"><?php echo $total_counted_hours; ?>h <?php echo $total_counted_mins; ?>m</div>
                <div class="cms-history-summary-label">Total Counted Hours</div>
            </div>
            
            <div class="cms-history-summary-card">
                <?php
                $total_actual_minutes = $total_actual_hours * 60 + $total_actual_mins;
                $total_counted_minutes = $total_counted_hours * 60 + $total_counted_mins;
                $diff_minutes = $total_counted_minutes - $total_actual_minutes;
                $diff_hours = floor(abs($diff_minutes) / 60);
                $diff_mins = abs($diff_minutes) % 60;
                $diff_class = $diff_minutes > 0 ? 'positive' : ($diff_minutes < 0 ? 'negative' : 'neutral');
                ?>
                <div class="cms-history-summary-value <?php echo $diff_class; ?>">
                    <?php echo ($diff_minutes >= 0 ? '+' : '-') . $diff_hours; ?>h <?php echo $diff_mins; ?>m
                </div>
                <div class="cms-history-summary-label">Difference</div>
                <div class="cms-history-summary-sub">(Counted - Actual)</div>
            </div>
        </div>
        
        <!-- Export Buttons -->
        <div class="cms-history-export">
            <button class="cms-history-export-btn" onclick="exportToCSV()">
                <span>📥</span> Export CSV
            </button>
            <button class="cms-history-export-btn" onclick="window.print()">
                <span>🖨️</span> Print
            </button>
        </div>
        
        <?php if (empty($shift_history)): ?>
            <div class="cms-history-no-data">
                <?php echo esc_html($atts['no_data_message']); ?>
            </div>
        <?php else: ?>
        
        <!-- Shift History Table -->
        <div class="cms-history-table-responsive">
            <table class="cms-history-table" id="shift-history-table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Employee</th>
                        <th>Login Time</th>
                        <th>Logout Time</th>
                        <th>Actual Hours</th>
                        <th>Counted Hours</th>
                        <th>Difference</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($shift_history as $index => $record): ?>
                    <tr class="<?php echo $index % 2 === 0 ? 'cms-history-even' : 'cms-history-odd'; ?>">
                        <td>
                            <strong><?php echo date($atts['date_format'], strtotime($record['date'])); ?></strong>
                        </td>
                        <td>
                            <div class="cms-history-employee-info">
                                <div class="cms-history-employee-avatar">
                                    <?php echo strtoupper(substr($record['employee_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="cms-history-employee-name"><?php echo esc_html($record['employee_name']); ?></div>
                                    <div class="cms-history-employee-username">@<?php echo esc_html($record['username']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="cms-history-time-badge">
                                <?php echo $record['actual_login_time'] ? date($atts['time_format'], strtotime($record['actual_login_time'])) : '--:--'; ?>
                            </span>
                            <?php if ($record['counted_login_time']): ?>
                            <div style="font-size: 11px; color: #718096;">
                                Scheduled: <?php echo date($atts['time_format'], strtotime($record['counted_login_time'])); ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="cms-history-time-badge">
                                <?php echo $record['actual_logout_time'] ? date($atts['time_format'], strtotime($record['actual_logout_time'])) : '--:--'; ?>
                            </span>
                            <?php if ($record['counted_logout_time']): ?>
                            <div style="font-size: 11px; color: #718096;">
                                Scheduled: <?php echo date($atts['time_format'], strtotime($record['counted_logout_time'])); ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="cms-history-hours">
                                <span class="cms-history-hours-actual">
                                    <?php echo $record['actual_hours']; ?>h <?php echo $record['actual_mins']; ?>m
                                </span>
                            </div>
                        </td>
                        <td>
                            <div class="cms-history-hours">
                                <span class="cms-history-hours-counted">
                                    <?php echo $record['counted_hours']; ?>h <?php echo $record['counted_mins']; ?>m
                                </span>
                            </div>
                        </td>
                        <td>
                            <?php
                            $record_actual = $record['actual_hours'] * 60 + $record['actual_mins'];
                            $record_counted = $record['counted_hours'] * 60 + $record['counted_mins'];
                            $record_diff = $record_counted - $record_actual;
                            $diff_class = $record_diff > 0 ? 'positive' : ($record_diff < 0 ? 'negative' : 'neutral');
                            ?>
                            <span class="cms-history-diff <?php echo $diff_class; ?>">
                                <?php echo ($record_diff >= 0 ? '+' : '') . $record_diff; ?> min
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <div class="cms-history-pagination">
            <a href="#" class="cms-history-page-link">« Previous</a>
            <a href="#" class="cms-history-page-link active">1</a>
            <a href="#" class="cms-history-page-link">2</a>
            <a href="#" class="cms-history-page-link">3</a>
            <a href="#" class="cms-history-page-link">4</a>
            <a href="#" class="cms-history-page-link">5</a>
            <a href="#" class="cms-history-page-link">Next »</a>
        </div>
        
        <?php endif; ?>
    </div>
    
    <script>
    function exportToCSV() {
        // Get table data
        var rows = document.querySelectorAll('#shift-history-table tbody tr');
        var csv = [];
        
        // Headers
        csv.push('Date,Employee,Username,Login Time,Logout Time,Actual Hours (min),Counted Hours (min),Difference (min)');
        
        // Data rows
        rows.forEach(function(row) {
            var cells = row.querySelectorAll('td');
            var date = cells[0].textContent.trim();
            var employeeName = cells[1].querySelector('.cms-history-employee-name')?.textContent.trim() || '';
            var employeeUsername = cells[1].querySelector('.cms-history-employee-username')?.textContent.trim().replace('@', '') || '';
            var loginTime = cells[2].querySelector('.cms-history-time-badge')?.textContent.trim() || '';
            var logoutTime = cells[3].querySelector('.cms-history-time-badge')?.textContent.trim() || '';
            var actualHours = cells[4].querySelector('.cms-history-hours-actual')?.textContent.trim() || '';
            var countedHours = cells[5].querySelector('.cms-history-hours-counted')?.textContent.trim() || '';
            var diff = cells[6].querySelector('.cms-history-diff')?.textContent.trim() || '';
            
            // Parse hours and minutes to total minutes
            var actualMinutes = 0;
            var countedMinutes = 0;
            
            var actualMatch = actualHours.match(/(\d+)h\s*(\d+)m/);
            if (actualMatch) {
                actualMinutes = parseInt(actualMatch[1]) * 60 + parseInt(actualMatch[2]);
            }
            
            var countedMatch = countedHours.match(/(\d+)h\s*(\d+)m/);
            if (countedMatch) {
                countedMinutes = parseInt(countedMatch[1]) * 60 + parseInt(countedMatch[2]);
            }
            
            csv.push(`"${date}","${employeeName}","${employeeUsername}","${loginTime}","${logoutTime}",${actualMinutes},${countedMinutes},"${diff}"`);
        });
        
        // Download CSV
        var blob = new Blob([csv.join('\n')], { type: 'text/csv' });
        var url = window.URL.createObjectURL(blob);
        var a = document.createElement('a');
        a.href = url;
        a.download = 'shift_history_' + new Date().toISOString().slice(0,10) + '.csv';
        a.click();
        window.URL.revokeObjectURL(url);
    }
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_emp_shift_history_list', 'cms_emp_shift_history_list_shortcode');
add_shortcode(CMS_EMP_SHIFT_HISTORY_LIST_SHORTCODE, 'cms_emp_shift_history_list_shortcode');

/**
 * Get all employees for filter
 */
function get_all_employees_for_filter() {
    return array(
        array(
            'username' => 'john_employee',
            'name' => 'John Smith'
        ),
        array(
            'username' => 'emily_jones',
            'name' => 'Emily Jones'
        ),
        array(
            'username' => 'david_miller',
            'name' => 'David Miller'
        ),
        array(
            'username' => 'sarah_ahmed',
            'name' => 'Sarah Ahmed'
        ),
        array(
            'username' => 'michael_brown',
            'name' => 'Michael Brown'
        )
    );
}

/**
 * Get filtered shift history
 */
function get_filtered_shift_history($filter_employee = '', $date_from = '', $date_to = '') {
    // Mock data - in real implementation, this would come from database with WHERE clauses
    $all_history = array(
        array(
            'username' => 'john_employee',
            'employee_name' => 'John Smith',
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
            'username' => 'john_employee',
            'employee_name' => 'John Smith',
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
            'username' => 'john_employee',
            'employee_name' => 'John Smith',
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
            'username' => 'emily_jones',
            'employee_name' => 'Emily Jones',
            'date' => '2024-03-15',
            'actual_login_time' => '08:30:00',
            'actual_logout_time' => '16:35:00',
            'actual_hours' => 8,
            'actual_mins' => 5,
            'counted_login_time' => '08:30:00',
            'counted_logout_time' => '16:30:00',
            'counted_hours' => 8,
            'counted_mins' => 0
        ),
        array(
            'username' => 'emily_jones',
            'employee_name' => 'Emily Jones',
            'date' => '2024-03-14',
            'actual_login_time' => '08:28:00',
            'actual_logout_time' => '16:32:00',
            'actual_hours' => 8,
            'actual_mins' => 4,
            'counted_login_time' => '08:30:00',
            'counted_logout_time' => '16:30:00',
            'counted_hours' => 8,
            'counted_mins' => 0
        ),
        array(
            'username' => 'david_miller',
            'employee_name' => 'David Miller',
            'date' => '2024-03-15',
            'actual_login_time' => '10:05:00',
            'actual_logout_time' => '18:10:00',
            'actual_hours' => 8,
            'actual_mins' => 5,
            'counted_login_time' => '10:00:00',
            'counted_logout_time' => '18:00:00',
            'counted_hours' => 8,
            'counted_mins' => 0
        ),
        array(
            'username' => 'sarah_ahmed',
            'employee_name' => 'Sarah Ahmed',
            'date' => '2024-03-15',
            'actual_login_time' => '09:15:00',
            'actual_logout_time' => '17:20:00',
            'actual_hours' => 8,
            'actual_mins' => 5,
            'counted_login_time' => '09:00:00',
            'counted_logout_time' => '17:00:00',
            'counted_hours' => 8,
            'counted_mins' => 0
        ),
        array(
            'username' => 'michael_brown',
            'employee_name' => 'Michael Brown',
            'date' => '2024-03-14',
            'actual_login_time' => '09:30:00',
            'actual_logout_time' => '17:45:00',
            'actual_hours' => 8,
            'actual_mins' => 15,
            'counted_login_time' => '09:00:00',
            'counted_logout_time' => '17:00:00',
            'counted_hours' => 8,
            'counted_mins' => 0
        )
    );
    
    // Apply filters
    $filtered = array_filter($all_history, function($record) use ($filter_employee, $date_from, $date_to) {
        if (!empty($filter_employee) && $record['username'] !== $filter_employee) {
            return false;
        }
        if (!empty($date_from) && $record['date'] < $date_from) {
            return false;
        }
        if (!empty($date_to) && $record['date'] > $date_to) {
            return false;
        }
        return true;
    });
    
    // Sort by date descending
    usort($filtered, function($a, $b) {
        return strcmp($b['date'], $a['date']);
    });
    
    return $filtered;
}
?>