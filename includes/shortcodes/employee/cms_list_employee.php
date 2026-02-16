<?php
/**
 * CMS List Employee Shortcode
 * Display all employees from database in a table with actions
 * 
 * Usage: [cms_list_employee]
 * Usage: [cms_list_employee items_per_page="20" show_search="yes" show_filters="yes"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define shortcode slug
if (!defined('CMS_EMPLOYEE_LIST_SHORTCODE')) {
    define('CMS_EMPLOYEE_LIST_SHORTCODE', 'cms_employee_list');
}

/**
 * Main shortcode function to display employees from database
 */
function cms_list_employee_shortcode($atts) {
    global $wpdb;
    
    // Parse attributes
    $atts = shortcode_atts(
        array(
            'items_per_page' => 10,
            'show_search' => 'yes',
            'show_filters' => 'yes',
            'actions' => 'view,update,delete',
            'no_data_message' => 'No employee records found.',
            'table_class' => '',
            'show_terminated' => 'yes',
            'show_inactive' => 'yes',
            'create_page' => 'add-employee',
            'edit_page' => 'edit-employee',
            'view_page' => 'view-employee',
        ),
        $atts,
        'cms_list_employee'
    );
    
    // Get current page
    $current_page = isset($_GET['emp_page']) ? max(1, intval($_GET['emp_page'])) : 1;
    $offset = ($current_page - 1) * intval($atts['items_per_page']);
    
    // Build query filters
    $where_conditions = array();
    $query_params = array();
    
    // Status filter
    if ($atts['show_terminated'] !== 'yes') {
        $where_conditions[] = "termination_date IS NULL";
    }
    
    // Search filter
    $search_term = isset($_GET['emp_search']) ? sanitize_text_field($_GET['emp_search']) : '';
    if (!empty($search_term)) {
        $where_conditions[] = "(e.name LIKE %s OR e.email LIKE %s OR e.username LIKE %s OR e.cnic_no LIKE %s OR e.position LIKE %s)";
        $like_term = '%' . $wpdb->esc_like($search_term) . '%';
        $query_params = array_merge($query_params, array($like_term, $like_term, $like_term, $like_term, $like_term));
    }
    
    // Team filter
    $team_filter = isset($_GET['emp_team']) ? sanitize_text_field($_GET['emp_team']) : '';
    if (!empty($team_filter)) {
        $where_conditions[] = "e.corp_team = %s";
        $query_params[] = $team_filter;
    }
    
    // Wage type filter
    $wage_filter = isset($_GET['emp_wage']) ? sanitize_text_field($_GET['emp_wage']) : '';
    if (!empty($wage_filter)) {
        $where_conditions[] = "e.wage_type = %s";
        $query_params[] = $wage_filter;
    }
    
    // Status filter from dropdown
    $status_filter = isset($_GET['emp_status']) ? sanitize_text_field($_GET['emp_status']) : '';
    if (!empty($status_filter)) {
        if ($status_filter === 'terminated') {
            $where_conditions[] = "e.termination_date IS NOT NULL";
        } else {
            $where_conditions[] = "e.status = %s AND e.termination_date IS NULL";
            $query_params[] = $status_filter;
        }
    }
    
    // Build WHERE clause
    $where_sql = '';
    if (!empty($where_conditions)) {
        $where_sql = 'WHERE ' . implode(' AND ', $where_conditions);
    }
    
    // Get total records for pagination
    $table_employee = $wpdb->prefix . 'cms_employee';
    
    $count_sql = "SELECT COUNT(*) FROM $table_employee e $where_sql";
    if (!empty($query_params)) {
        $count_sql = $wpdb->prepare($count_sql, $query_params);
    }
    $total_records = $wpdb->get_var($count_sql);
    $total_pages = ceil($total_records / intval($atts['items_per_page']));
    
    // Get sorting
    $order_by = 'e.name ASC';
    $sort_by = isset($_GET['emp_sort']) ? sanitize_text_field($_GET['emp_sort']) : '';
    
    switch ($sort_by) {
        case 'newest':
            $order_by = 'e.joining_date DESC';
            break;
        case 'oldest':
            $order_by = 'e.joining_date ASC';
            break;
        case 'name_desc':
            $order_by = 'e.name DESC';
            break;
        case 'wage_high':
            $order_by = 'e.basic_wage DESC';
            break;
        case 'wage_low':
            $order_by = 'e.basic_wage ASC';
            break;
        default:
            $order_by = 'e.name ASC';
    }
    
    // Get employees with pagination
    $sql = "SELECT e.* FROM $table_employee e 
            $where_sql 
            ORDER BY $order_by 
            LIMIT %d OFFSET %d";
    
    // Add pagination parameters
    $all_params = array_merge($query_params, array($atts['items_per_page'], $offset));
    $prepared_sql = !empty($all_params) ? $wpdb->prepare($sql, $all_params) : $sql;
    
    $employee_data = $wpdb->get_results($prepared_sql, ARRAY_A);
    
    ob_start();
    ?>
    
    <style>
    /* Employee List Styles */
    .cms-emp-list-container {
        max-width: 1400px;
        margin: 30px auto;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(230,126,34,0.05);
        padding: 25px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        border-top: 4px solid #e67e22;
    }
    
    .cms-emp-list-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .cms-emp-list-title {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        color: #d35400;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cms-emp-list-title:before {
        content: '👥';
        font-size: 28px;
    }
    
    .cms-emp-stats {
        display: flex;
        gap: 15px;
        background: #fef9f5;
        padding: 10px 20px;
        border-radius: 40px;
        font-size: 14px;
    }
    
    .cms-emp-stat-item {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .cms-emp-stat-value {
        font-weight: 700;
        color: #e67e22;
    }
    
    .cms-emp-search-box {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .cms-emp-search-input {
        padding: 12px 16px;
        border: 2px solid #ffe6d5;
        border-radius: 40px;
        width: 280px;
        font-size: 14px;
        transition: all 0.25s ease;
    }
    
    .cms-emp-search-input:focus {
        outline: none;
        border-color: #e67e22;
        box-shadow: 0 0 0 3px rgba(230,126,34,0.05);
    }
    
    .cms-emp-search-button {
        padding: 12px 24px;
        background: #e67e22;
        color: white;
        border: none;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s ease;
    }
    
    .cms-emp-search-button:hover {
        background: #d35400;
        transform: translateY(-1px);
    }
    
    .cms-emp-filters {
        background: #fef9f5;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        align-items: center;
        border: 1px solid #ffe6d5;
    }
    
    .cms-emp-filter-select {
        padding: 10px 16px;
        border: 1px solid #ffe6d5;
        border-radius: 8px;
        background: white;
        min-width: 150px;
        font-size: 14px;
    }
    
    .cms-emp-filter-select:focus {
        outline: none;
        border-color: #e67e22;
    }
    
    .cms-emp-filter-label {
        font-weight: 600;
        color: #2c3e50;
        margin-right: 5px;
    }
    
    .cms-emp-reset-filters {
        padding: 10px 20px;
        background: white;
        border: 1px solid #ffe6d5;
        border-radius: 8px;
        color: #718096;
        cursor: pointer;
        transition: all 0.25s ease;
    }
    
    .cms-emp-reset-filters:hover {
        border-color: #e67e22;
        color: #e67e22;
    }
    
    .cms-emp-add-new {
        padding: 10px 20px;
        background: #27ae60;
        color: white;
        border: none;
        border-radius: 8px;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-left: auto;
    }
    
    .cms-emp-add-new:hover {
        background: #219a52;
        color: white;
    }
    
    .cms-emp-table-responsive {
        overflow-x: auto;
        margin-bottom: 25px;
        border-radius: 12px;
        border: 1px solid #ffe6d5;
    }
    
    .cms-emp-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    
    .cms-emp-table th {
        background: #fef9f5;
        color: #d35400;
        font-weight: 600;
        padding: 16px 12px;
        text-align: left;
        border-bottom: 2px solid #ffe6d5;
        white-space: nowrap;
    }
    
    .cms-emp-table td {
        padding: 16px 12px;
        border-bottom: 1px solid #ffe6d5;
        color: #4a5568;
        vertical-align: middle;
    }
    
    .cms-emp-table tr:hover {
        background: #fef9f5;
    }
    
    .cms-emp-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(145deg, #e67e22, #d35400);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 16px;
        flex-shrink: 0;
    }
    
    .cms-emp-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .cms-emp-name {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 4px;
    }
    
    .cms-emp-username {
        font-size: 12px;
        color: #718096;
    }
    
    .cms-emp-team-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 40px;
        font-size: 12px;
        font-weight: 500;
        background: #fff4ed;
        color: #d35400;
    }
    
    .cms-emp-wage-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 40px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .cms-emp-wage-badge.hourly {
        background: #e3f2fd;
        color: #0d47a1;
    }
    
    .cms-emp-wage-badge.monthly {
        background: #e8f5e9;
        color: #1e8449;
    }
    
    .cms-emp-status {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 40px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .cms-emp-status.active {
        background: #e3f7ec;
        color: #0a5c36;
    }
    
    .cms-emp-status.inactive {
        background: #ffe8e8;
        color: #b34141;
    }
    
    .cms-emp-status.terminated {
        background: #fddede;
        color: #a94442;
    }
    
    .cms-emp-action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .cms-emp-action-btn {
        padding: 8px 14px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }
    
    .cms-emp-btn-view {
        background: #fff4ed;
        color: #e67e22;
        border: 1px solid #ffe6d5;
    }
    
    .cms-emp-btn-view:hover {
        background: #ffe6d5;
        color: #d35400;
    }
    
    .cms-emp-btn-edit {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
    
    .cms-emp-btn-edit:hover {
        background: #ffe8a1;
        color: #6d5300;
    }
    
    .cms-emp-btn-delete {
        background: #ffe8e8;
        color: #b34141;
        border: 1px solid #ffc9c9;
    }
    
    .cms-emp-btn-delete:hover {
        background: #ffc9c9;
        color: #8b2c2c;
    }
    
    .cms-emp-btn-docs {
        background: #e8eaf6;
        color: #3f51b5;
        border: 1px solid #c5cae9;
    }
    
    .cms-emp-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 30px;
        flex-wrap: wrap;
    }
    
    .cms-emp-page-link {
        padding: 10px 16px;
        background: white;
        border: 1px solid #ffe6d5;
        border-radius: 8px;
        color: #4a5568;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    
    .cms-emp-page-link:hover {
        background: #fef9f5;
        border-color: #e67e22;
        color: #e67e22;
    }
    
    .cms-emp-page-link.active {
        background: #e67e22;
        color: white;
        border-color: #e67e22;
    }
    
    .cms-emp-page-link.disabled {
        opacity: 0.5;
        pointer-events: none;
    }
    
    .cms-emp-no-data {
        text-align: center;
        padding: 60px 20px;
        color: #718096;
        font-size: 16px;
        background: #fef9f5;
        border-radius: 12px;
    }
    
    .cms-emp-no-data:before {
        content: '👥';
        display: block;
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.5;
    }
    
    .cms-emp-doc-indicator {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: #e67e22;
        font-size: 12px;
        margin-right: 8px;
    }
    
    .cms-emp-doc-check {
        color: #27ae60;
    }
    
    .cms-emp-doc-cross {
        color: #e74c3c;
    }
    
    .cms-emp-loading {
        display: none;
        text-align: center;
        padding: 40px;
    }
    
    .cms-emp-loading-spinner {
        border: 4px solid #f3f3f3;
        border-top: 4px solid #e67e22;
        border-radius: 50%;
        width: 40px;
        height: 40px;
        animation: spin 1s linear infinite;
        margin: 0 auto 15px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .cms-emp-download-all {
        background: #3498db;
        color: white;
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        margin-left: 10px;
    }
    
    .cms-emp-export {
        background: #2c3e50;
        color: white;
        padding: 8px 16px;
        border-radius: 6px;
        text-decoration: none;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    </style>
    
    <div class="cms-emp-list-container <?php echo esc_attr($atts['table_class']); ?>" 
         data-perpage="<?php echo esc_attr($atts['items_per_page']); ?>">
        
        <!-- Header with Stats -->
        <div class="cms-emp-list-header">
            <h2 class="cms-emp-list-title">Employee Management</h2>
            
            <?php 
            // Get employee counts for stats - use the function from main plugin if exists
            if (function_exists('cms_get_employee_counts')) {
                $stats = cms_get_employee_counts();
            } else {
                // Fallback stats calculation
                $stats = cms_list_get_employee_counts_fallback();
            }
            ?>
            <div class="cms-emp-stats">
                <span class="cms-emp-stat-item">
                    <span>Total:</span>
                    <span class="cms-emp-stat-value"><?php echo intval($stats['total']); ?></span>
                </span>
                <span class="cms-emp-stat-item">
                    <span>Active:</span>
                    <span class="cms-emp-stat-value" style="color:#27ae60;"><?php echo intval($stats['active']); ?></span>
                </span>
                <span class="cms-emp-stat-item">
                    <span>Inactive:</span>
                    <span class="cms-emp-stat-value" style="color:#e74c3c;"><?php echo intval($stats['inactive'] + $stats['terminated']); ?></span>
                </span>
            </div>
            
            <a href="<?php echo esc_url(home_url($atts['create_page'])); ?>" class="cms-emp-add-new">
                ➕ Add New Employee
            </a>
        </div>
        
        <!-- Search Box -->
        <?php if ($atts['show_search'] === 'yes'): ?>
        <form method="get" action="" class="cms-emp-search-box" style="margin-bottom: 20px;">
            <?php 
            // Preserve other query parameters
            foreach ($_GET as $key => $value) {
                if ($key !== 'emp_search' && $key !== 'emp_page') {
                    echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
                }
            }
            ?>
            <input type="text" name="emp_search" class="cms-emp-search-input" 
                   placeholder="Search by name, email, CNIC, position..." 
                   value="<?php echo esc_attr($search_term); ?>">
            <button type="submit" class="cms-emp-search-button">Search</button>
            <?php if (!empty($search_term)): ?>
                <a href="<?php echo esc_url(remove_query_arg('emp_search')); ?>" class="cms-emp-reset-filters">Clear</a>
            <?php endif; ?>
        </form>
        <?php endif; ?>
        
        <!-- Filters -->
        <?php if ($atts['show_filters'] === 'yes'): ?>
        <form method="get" action="" class="cms-emp-filters">
            <?php 
            // Preserve search term
            if (!empty($search_term)) {
                echo '<input type="hidden" name="emp_search" value="' . esc_attr($search_term) . '">';
            }
            ?>
            
            <span class="cms-emp-filter-label">Filter by:</span>
            
            <select name="emp_team" class="cms-emp-filter-select">
                <option value="">All Teams</option>
                <?php
                // Get unique teams from database
                $teams = $wpdb->get_col("SELECT DISTINCT corp_team FROM $table_employee WHERE corp_team != '' ORDER BY corp_team");
                foreach ($teams as $team) {
                    $selected = ($team_filter == $team) ? 'selected' : '';
                    echo '<option value="' . esc_attr($team) . '" ' . $selected . '>' . esc_html($team) . '</option>';
                }
                ?>
            </select>
            
            <select name="emp_wage" class="cms-emp-filter-select">
                <option value="">All Wage Types</option>
                <option value="hourly" <?php selected($wage_filter, 'hourly'); ?>>Hourly</option>
                <option value="monthly" <?php selected($wage_filter, 'monthly'); ?>>Monthly</option>
            </select>
            
            <select name="emp_status" class="cms-emp-filter-select">
                <option value="">All Status</option>
                <option value="active" <?php selected($status_filter, 'active'); ?>>Active</option>
                <option value="inactive" <?php selected($status_filter, 'inactive'); ?>>Inactive</option>
                <option value="terminated" <?php selected($status_filter, 'terminated'); ?>>Terminated</option>
            </select>
            
            <select name="emp_sort" class="cms-emp-filter-select">
                <option value="">Sort By</option>
                <option value="newest" <?php selected($sort_by, 'newest'); ?>>Newest First</option>
                <option value="oldest" <?php selected($sort_by, 'oldest'); ?>>Oldest First</option>
                <option value="name" <?php selected($sort_by, 'name'); ?>>Name A-Z</option>
                <option value="name_desc" <?php selected($sort_by, 'name_desc'); ?>>Name Z-A</option>
                <option value="wage_high" <?php selected($sort_by, 'wage_high'); ?>>Wage (High-Low)</option>
                <option value="wage_low" <?php selected($sort_by, 'wage_low'); ?>>Wage (Low-High)</option>
            </select>
            
            <button type="submit" class="cms-emp-search-button">Apply Filters</button>
            
            <?php
            // Check if any filters are active
            $active_filters = !empty($team_filter) || !empty($wage_filter) || !empty($status_filter) || !empty($sort_by);
            if ($active_filters):
            ?>
                <a href="<?php echo esc_url(remove_query_arg(array('emp_team', 'emp_wage', 'emp_status', 'emp_sort'))); ?>" class="cms-emp-reset-filters">
                    Reset Filters
                </a>
            <?php endif; ?>
        </form>
        <?php endif; ?>
        
        <!-- Employee Table -->
        <?php if (empty($employee_data)): ?>
            <div class="cms-emp-no-data">
                <?php echo esc_html($atts['no_data_message']); ?>
                <?php if (!empty($search_term) || $active_filters): ?>
                    <br><br>
                    <a href="<?php echo esc_url(remove_query_arg(array('emp_search', 'emp_team', 'emp_wage', 'emp_status', 'emp_sort'))); ?>" 
                       class="cms-emp-reset-filters">Clear all filters</a>
                <?php endif; ?>
            </div>
        <?php else: ?>
        
        <div class="cms-emp-table-responsive">
            <table class="cms-emp-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>CNIC</th>
                        <th>Team/Position</th>
                        <th>Contact</th>
                        <th>Wage Details</th>
                        <th>Joining Date</th>
                        <th>Documents</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employee_data as $employee): ?>
                    <tr id="emp-row-<?php echo esc_attr($employee['id']); ?>">
                        <td>
                            <div class="cms-emp-info">
                                <div class="cms-emp-avatar">
                                    <?php echo strtoupper(substr($employee['name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="cms-emp-name"><?php echo esc_html($employee['name']); ?></div>
                                    <div class="cms-emp-username">@<?php echo esc_html($employee['username']); ?></div>
                                    <div style="font-size: 11px; color: #718096;"><?php echo esc_html($employee['email']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-family: monospace;"><?php echo esc_html($employee['cnic_no']); ?></div>
                        </td>
                        <td>
                            <span class="cms-emp-team-badge"><?php echo esc_html($employee['corp_team']); ?></span>
                            <div style="font-size: 12px; margin-top: 5px; color: #718096;">
                                <?php echo esc_html($employee['position']); ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 500;"><?php echo esc_html($employee['contact_num']); ?></div>
                            <div style="font-size: 11px; color: #718096; margin-top: 3px;">
                                Emergency: <?php echo esc_html($employee['emergency_cno']); ?>
                            </div>
                        </td>
                        <td>
                            <span class="cms-emp-wage-badge <?php echo esc_attr($employee['wage_type']); ?>">
                                <?php echo esc_html(ucfirst($employee['wage_type'])); ?>
                            </span>
                            <div style="font-size: 14px; font-weight: 600; color: #2c3e50; margin-top: 5px;">
                                <?php 
                                if ($employee['wage_type'] === 'hourly') {
                                    echo 'PKR' . number_format($employee['basic_wage'], 2) . '/hr';
                                } else {
                                    echo 'PKR' . number_format($employee['basic_wage'], 2) . '/mo';
                                }
                                ?>
                            </div>
                        </td>
                        <td>
                            <?php echo date('M d, Y', strtotime($employee['joining_date'])); ?>
                        </td>
                        <td>
                            <div class="cms-emp-doc-indicator">
                                <span>📄 CNIC</span>
                                <span class="<?php echo !empty($employee['cnic_pdf']) ? 'cms-emp-doc-check' : 'cms-emp-doc-cross'; ?>">
                                    <?php echo !empty($employee['cnic_pdf']) ? '✓' : '✗'; ?>
                                </span>
                            </div>
                            <div class="cms-emp-doc-indicator">
                                <span>📜 Certificate</span>
                                <span class="<?php echo !empty($employee['char_cert_pdf']) ? 'cms-emp-doc-check' : 'cms-emp-doc-cross'; ?>">
                                    <?php echo !empty($employee['char_cert_pdf']) ? '✓' : '✗'; ?>
                                </span>
                            </div>
                            <div class="cms-emp-doc-indicator">
                                <span>📋 Letter</span>
                                <span class="<?php echo !empty($employee['emp_letter_pdf']) ? 'cms-emp-doc-check' : 'cms-emp-doc-cross'; ?>">
                                    <?php echo !empty($employee['emp_letter_pdf']) ? '✓' : '✗'; ?>
                                </span>
                            </div>
                            <?php if (!empty($employee['char_cert_exp'])): ?>
                            <div style="font-size: 10px; color: #718096; margin-top: 5px;">
                                Cert Exp: <?php echo date('M d, Y', strtotime($employee['char_cert_exp'])); ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php 
                            $status_class = 'active';
                            $status_text = 'Active';
                            
                            if (!empty($employee['termination_date'])) {
                                $status_class = 'terminated';
                                $status_text = 'Terminated';
                            } else {
                                switch ($employee['status']) {
                                    case 'inactive':
                                        $status_class = 'inactive';
                                        $status_text = 'Inactive';
                                        break;
                                    case 'terminated':
                                        $status_class = 'terminated';
                                        $status_text = 'Terminated';
                                        break;
                                }
                            }
                            ?>
                            <span class="cms-emp-status <?php echo $status_class; ?>">
                                <?php echo $status_text; ?>
                            </span>
                            <?php if(!empty($employee['termination_date'])): ?>
                            <div style="font-size: 10px; color: #a94442; margin-top: 3px;">
                                <?php echo date('M d, Y', strtotime($employee['termination_date'])); ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="cms-emp-action-buttons">
                                <?php if (strpos($atts['actions'], 'view') !== false): ?>
                                <a href="<?php echo esc_url(home_url($atts['view_page'] . '/' . $employee['id'])); ?>" class="cms-emp-action-btn cms-emp-btn-view">
                                    👁️ View
                                </a>
                                <?php endif; ?>
                                
                                <?php if (strpos($atts['actions'], 'update') !== false): ?>
                                <a href="<?php echo esc_url(home_url($atts['edit_page'] . '/' . $employee['id'])); ?>" class="cms-emp-action-btn cms-emp-btn-edit">
                                    ✏️ Edit
                                </a>
                                <?php endif; ?>
                                
                                <?php if (strpos($atts['actions'], 'delete') !== false && empty($employee['termination_date'])): ?>
                                <button class="cms-emp-action-btn cms-emp-btn-delete" onclick="cmsConfirmDeleteEmp(<?php echo esc_js($employee['id']); ?>, '<?php echo esc_js($employee['username']); ?>')">
                                    🗑️ Delete
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="cms-emp-pagination">
            <?php
            // Build pagination links
            $base_url = remove_query_arg('emp_page');
            
            // Previous link
            if ($current_page > 1) {
                $prev_url = add_query_arg('emp_page', $current_page - 1, $base_url);
                echo '<a href="' . esc_url($prev_url) . '" class="cms-emp-page-link">« Previous</a>';
            } else {
                echo '<span class="cms-emp-page-link disabled">« Previous</span>';
            }
            
            // Page numbers
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);
            
            for ($i = $start_page; $i <= $end_page; $i++) {
                $page_url = add_query_arg('emp_page', $i, $base_url);
                $active_class = ($i == $current_page) ? 'active' : '';
                echo '<a href="' . esc_url($page_url) . '" class="cms-emp-page-link ' . $active_class . '">' . $i . '</a>';
            }
            
            // Next link
            if ($current_page < $total_pages) {
                $next_url = add_query_arg('emp_page', $current_page + 1, $base_url);
                echo '<a href="' . esc_url($next_url) . '" class="cms-emp-page-link">Next »</a>';
            } else {
                echo '<span class="cms-emp-page-link disabled">Next »</span>';
            }
            ?>
        </div>
        
        <div style="text-align: center; margin-top: 15px; font-size: 13px; color: #718096;">
            Showing <?php echo ($offset + 1); ?> - <?php echo min($offset + intval($atts['items_per_page']), $total_records); ?> 
            of <?php echo $total_records; ?> employees
        </div>
        <?php endif; ?>
        
        <?php endif; ?>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="cms-emp-delete-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:white; padding:30px; border-radius:16px; max-width:500px; width:90%; border-top:4px solid #e74c3c;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:15px; border-bottom:2px solid #f0f0f0;">
                <h3 style="margin:0; color:#e74c3c;">Confirm Delete</h3>
                <button style="background:none; border:none; font-size:24px; cursor:pointer; color:#718096;" onclick="document.getElementById('cms-emp-delete-modal').style.display='none'">×</button>
            </div>
            <div style="padding:20px 0;">
                <p style="font-size:16px; margin-bottom:20px;">Are you sure you want to delete employee <span id="delete-emp-name" style="font-weight:600;">this employee</span>?</p>
                <p style="color:#718096; font-size:14px;">This action cannot be undone. All employee data will be permanently removed.</p>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:10px;">
                <button style="background:#e2e8f0; color:#4a5568; padding:12px 24px; border:none; border-radius:8px; cursor:pointer;" onclick="document.getElementById('cms-emp-delete-modal').style.display='none'">Cancel</button>
                <button id="cms-emp-confirm-delete-btn" style="background:#e74c3c; color:white; padding:12px 24px; border:none; border-radius:8px; cursor:pointer; font-weight:600;">Delete Employee</button>
            </div>
        </div>
    </div>
    
    <!-- Loading Spinner -->
    <div id="cms-emp-loading" class="cms-emp-loading">
        <div class="cms-emp-loading-spinner"></div>
        <p>Processing...</p>
    </div>
    
    <script>
    let currentDeleteId = null;
    
    function cmsConfirmDeleteEmp(employeeId, employeeName) {
        currentDeleteId = employeeId;
        document.getElementById('delete-emp-name').textContent = employeeName;
        document.getElementById('cms-emp-delete-modal').style.display = 'flex';
    }
    
    document.getElementById('cms-emp-confirm-delete-btn').addEventListener('click', function() {
        if (currentDeleteId) {
            cmsDeleteEmployee(currentDeleteId);
        }
    });
    
    function cmsDeleteEmployee(employeeId) {
        // Show loading
        document.getElementById('cms-emp-loading').style.display = 'block';
        document.getElementById('cms-emp-delete-modal').style.display = 'none';
        
        // AJAX delete request
        var formData = new FormData();
        formData.append('action', 'cms_delete_employee');
        formData.append('employee_id', employeeId);
        formData.append('nonce', '<?php echo wp_create_nonce('cms_delete_employee_nonce'); ?>');
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('cms-emp-loading').style.display = 'none';
            
            if (data.success) {
                // Remove row from table
                var row = document.getElementById('emp-row-' + employeeId);
                if (row) {
                    row.style.opacity = '0.5';
                    setTimeout(function() {
                        row.remove();
                        alert('Employee deleted successfully!');
                        
                        // Reload if no more rows
                        if (document.querySelectorAll('.cms-emp-table tbody tr').length === 0) {
                            location.reload();
                        }
                    }, 300);
                }
            } else {
                alert('Error deleting employee: ' + data.data);
            }
        })
        .catch(error => {
            document.getElementById('cms-emp-loading').style.display = 'none';
            alert('Error: ' + error);
        });
    }
    
    // Live search functionality (optional)
    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.getElementById('cms-emp-search');
        if (searchInput) {
            var searchTimeout;
            searchInput.addEventListener('keyup', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(function() {
                    document.querySelector('.cms-emp-search-box').submit();
                }, 500);
            });
        }
    });
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_list_employee', 'cms_list_employee_shortcode');
add_shortcode(CMS_EMPLOYEE_LIST_SHORTCODE, 'cms_list_employee_shortcode');

/**
 * AJAX handler for deleting employee
 */
function cms_ajax_delete_employee() {
    global $wpdb;
    
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cms_delete_employee_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    if (!isset($_POST['employee_id'])) {
        wp_send_json_error('No employee ID provided');
    }
    
    $employee_id = intval($_POST['employee_id']);
    $table_employee = $wpdb->prefix . 'cms_employee';
    $table_users = $wpdb->prefix . 'cms_users';
    
    // Get employee username first
    $employee = $wpdb->get_row($wpdb->prepare(
        "SELECT username FROM $table_employee WHERE id = %d",
        $employee_id
    ));
    
    if (!$employee) {
        wp_send_json_error('Employee not found');
    }
    
    // Start transaction
    $wpdb->query('START TRANSACTION');
    
    try {
        // Delete from employee table
        $wpdb->delete(
            $table_employee,
            array('id' => $employee_id),
            array('%d')
        );
        
        // Delete from users table (cascade will handle related records)
        $wpdb->delete(
            $table_users,
            array('username' => $employee->username),
            array('%s')
        );
        
        $wpdb->query('COMMIT');
        
        // Log the deletion
        error_log("CMS: Employee deleted - ID: $employee_id, Username: {$employee->username}");
        
        wp_send_json_success('Employee deleted successfully');
        
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        wp_send_json_error('Database error: ' . $e->getMessage());
    }
}
add_action('wp_ajax_cms_delete_employee', 'cms_ajax_delete_employee');

/**
 * Get employee by ID
 */
function cms_get_employee_by_id($employee_id) {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_employee';
    
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d",
        $employee_id
    ), ARRAY_A);
}

/**
 * Fallback function to get employee counts if main function doesn't exist
 */
function cms_list_get_employee_counts_fallback() {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_employee';
    
    $total = $wpdb->get_var("SELECT COUNT(*) FROM $table");
    
    $active = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'active' AND termination_date IS NULL");
    
    $inactive = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE status = 'inactive' AND termination_date IS NULL");
    
    $terminated = $wpdb->get_var("SELECT COUNT(*) FROM $table WHERE termination_date IS NOT NULL");
    
    return array(
        'total' => $total,
        'active' => $active,
        'inactive' => $inactive,
        'terminated' => $terminated
    );
}

/**
 * Export employees to CSV
 */
function cms_export_employees_csv() {
    if (!current_user_can('manage_options')) {
        wp_die('Unauthorized');
    }
    
    global $wpdb;
    $table = $wpdb->prefix . 'cms_employee';
    
    $employees = $wpdb->get_results("SELECT * FROM $table ORDER BY name ASC", ARRAY_A);
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="employees-' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add headers
    fputcsv($output, array('ID', 'Username', 'Name', 'Email', 'CNIC', 'Position', 'Team', 'Contact', 'Wage Type', 'Basic Wage', 'Status', 'Joining Date'));
    
    // Add data
    foreach ($employees as $emp) {
        fputcsv($output, array(
            $emp['id'],
            $emp['username'],
            $emp['name'],
            $emp['email'],
            $emp['cnic_no'],
            $emp['position'],
            $emp['corp_team'],
            $emp['contact_num'],
            $emp['wage_type'],
            $emp['basic_wage'],
            !empty($emp['termination_date']) ? 'Terminated' : $emp['status'],
            $emp['joining_date']
        ));
    }
    
    fclose($output);
    exit;
}
add_action('admin_post_cms_export_employees', 'cms_export_employees_csv');