<?php
/**
 * CMS Employee-Corporate Account Assignment Shortcode
 * Display employees and their assigned corporate accounts from database
 * 
 * Fields: id, username_emp, username_corp_acc
 * 
 * Usage: [cms_emp_corp_assign]
 * Usage: [cms_emp_corp_assign title="Employee Corporate Account Assignment"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define shortcode slug
if (!defined('CMS_EMP_CORP_ASSIGN_SHORTCODE')) {
    define('CMS_EMP_CORP_ASSIGN_SHORTCODE', 'cms_emp_corp_assign');
}

/**
 * Employee Corporate Account Assignment Shortcode
 */
function cms_emp_corp_assign_shortcode($atts) {
    global $wpdb;
    
    // Parse attributes
    $atts = shortcode_atts(
        array(
            'title' => 'Employee Corporate Account Assignment',
            'description' => 'Manage which corporate accounts are assigned to employees',
            'show_filters' => 'yes',
            'show_search' => 'yes',
            'items_per_page' => 20,
            'class' => '',
            'no_data_message' => 'No assignments found.',
            'allow_assign' => 'yes',
            'allow_unassign' => 'yes'
        ),
        $atts,
        'cms_emp_corp_assign'
    );
    
    // Get current page for pagination
    $current_page = isset($_GET['assign_page']) ? max(1, intval($_GET['assign_page'])) : 1;
    $items_per_page = intval($atts['items_per_page']);
    $offset = ($current_page - 1) * $items_per_page;
    
    // Table names
    $table_employee = $wpdb->prefix . 'cms_employee';
    $table_corp_acc = $wpdb->prefix . 'cms_corp_acc';
    $table_assignments = $wpdb->prefix . 'cms_emp_corp_assign';
    
    // Get total employees count for pagination (only active employees)
    $total_employees = $wpdb->get_var("SELECT COUNT(*) FROM $table_employee WHERE termination_date IS NULL");
    $total_pages = ceil($total_employees / $items_per_page);
    
    // Get employees with pagination
    $employees = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_employee WHERE termination_date IS NULL ORDER BY name ASC LIMIT %d OFFSET %d",
        $items_per_page,
        $offset
    ), ARRAY_A);
    
    // Get all corporate accounts
    $corp_accounts = $wpdb->get_results("SELECT * FROM $table_corp_acc ORDER BY company_name ASC", ARRAY_A);
    
    // Get all assignments - FIXED: using assigned_at instead of created_at
    $assignments = $wpdb->get_results("SELECT * FROM $table_assignments ORDER BY assigned_at DESC", ARRAY_A);
    
    // Create lookup arrays for quick access
    $corp_lookup = array();
    foreach ($corp_accounts as $corp) {
        $corp_lookup[$corp['username']] = $corp;
    }
    
    // Create assignments lookup by employee
    $assignments_by_emp = array();
    foreach ($assignments as $assignment) {
        $emp_username = $assignment['username_emp'];
        if (!isset($assignments_by_emp[$emp_username])) {
            $assignments_by_emp[$emp_username] = array();
        }
        $assignments_by_emp[$emp_username][] = $assignment;
    }
    
    ob_start();
    ?>
    
    <style>
    /* Employee Corporate Assignment Styles - Teal Theme */
    :root {
        --assign-primary: #008080;
        --assign-primary-dark: #006666;
        --assign-primary-light: #33cccc;
        --assign-secondary: #20b2aa;
        --assign-accent: #5f9ea0;
        --assign-success: #2e8b57;
        --assign-danger: #cd5c5c;
        --assign-warning: #f0ad4e;
        --assign-info: #5bc0de;
    }
    
    .cms-assign-container {
        max-width: 1400px;
        margin: 30px auto;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,128,128,0.08);
        padding: 30px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        border-top: 5px solid var(--assign-primary);
    }
    
    .cms-assign-header {
        margin-bottom: 30px;
        text-align: center;
    }
    
    .cms-assign-title {
        margin: 0 0 10px 0;
        font-size: 28px;
        font-weight: 700;
        color: var(--assign-primary-dark);
        letter-spacing: -0.5px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .cms-assign-title:before {
        content: '🔗';
        font-size: 32px;
    }
    
    .cms-assign-description {
        margin: 0;
        font-size: 15px;
        color: #6c7a89;
        line-height: 1.6;
    }
    
    .cms-assign-controls {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .cms-assign-search-box {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .cms-assign-search-input {
        padding: 12px 16px;
        border: 2px solid #d1e7e7;
        border-radius: 40px;
        width: 300px;
        font-size: 14px;
        transition: all 0.25s ease;
    }
    
    .cms-assign-search-input:focus {
        outline: none;
        border-color: var(--assign-primary);
        box-shadow: 0 0 0 3px rgba(0,128,128,0.05);
    }
    
    .cms-assign-search-button {
        padding: 12px 24px;
        background: var(--assign-primary);
        color: white;
        border: none;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s ease;
    }
    
    .cms-assign-search-button:hover {
        background: var(--assign-primary-dark);
        transform: translateY(-1px);
    }
    
    .cms-assign-filters {
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        margin-bottom: 25px;
        padding: 20px;
        background: #f0f8f8;
        border-radius: 12px;
        border: 1px solid #d1e7e7;
    }
    
    .cms-assign-filter-select {
        padding: 10px 16px;
        border: 1px solid #d1e7e7;
        border-radius: 8px;
        background: white;
        min-width: 180px;
        font-size: 14px;
        color: #2c3e50;
    }
    
    .cms-assign-stats {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .cms-assign-stat-card {
        background: linear-gradient(145deg, #f0f8f8, #ffffff);
        border: 1px solid #d1e7e7;
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        box-shadow: 0 4px 10px rgba(0,128,128,0.03);
    }
    
    .cms-assign-stat-number {
        font-size: 32px;
        font-weight: 700;
        color: var(--assign-primary);
        line-height: 1.2;
        margin-bottom: 5px;
    }
    
    .cms-assign-stat-label {
        font-size: 14px;
        color: #5f6b7a;
        font-weight: 500;
    }
    
    .cms-assign-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
        gap: 25px;
        margin-bottom: 30px;
    }
    
    .cms-assign-card {
        background: #ffffff;
        border: 2px solid #d1e7e7;
        border-radius: 16px;
        overflow: hidden;
        transition: all 0.3s ease;
        position: relative;
    }
    
    .cms-assign-card:hover {
        border-color: var(--assign-primary);
        box-shadow: 0 8px 25px rgba(0,128,128,0.1);
        transform: translateY(-2px);
    }
    
    .cms-assign-card-header {
        background: linear-gradient(145deg, #f0f8f8, #e0f0f0);
        padding: 20px;
        border-bottom: 2px solid #d1e7e7;
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .cms-assign-employee-avatar {
        width: 60px;
        height: 60px;
        background: linear-gradient(145deg, var(--assign-primary), var(--assign-primary-dark));
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        font-weight: 700;
        color: white;
    }
    
    .cms-assign-employee-info {
        flex: 1;
    }
    
    .cms-assign-employee-name {
        font-size: 18px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 5px;
    }
    
    .cms-assign-employee-details {
        font-size: 13px;
        color: #5f6b7a;
        display: flex;
        flex-direction: column;
        gap: 3px;
    }
    
    .cms-assign-employee-username {
        color: var(--assign-primary);
        font-weight: 600;
    }
    
    .cms-assign-employee-team {
        background: #d1e7e7;
        padding: 2px 10px;
        border-radius: 20px;
        display: inline-block;
        font-size: 11px;
        color: var(--assign-primary-dark);
        font-weight: 600;
    }
    
    .cms-assign-card-body {
        padding: 20px;
    }
    
    .cms-assign-section-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--assign-primary-dark);
        margin: 0 0 15px 0;
        display: flex;
        align-items: center;
        gap: 8px;
        padding-bottom: 10px;
        border-bottom: 2px solid #d1e7e7;
    }
    
    .cms-assign-corp-list {
        display: flex;
        flex-direction: column;
        gap: 10px;
        margin-bottom: 20px;
        max-height: 200px;
        overflow-y: auto;
        padding-right: 5px;
    }
    
    .cms-assign-corp-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 15px;
        background: #f0f8f8;
        border: 1px solid #d1e7e7;
        border-radius: 10px;
        transition: all 0.2s ease;
    }
    
    .cms-assign-corp-item:hover {
        background: #e0f0f0;
        border-color: var(--assign-primary);
    }
    
    .cms-assign-corp-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .cms-assign-corp-avatar {
        width: 35px;
        height: 35px;
        background: linear-gradient(145deg, var(--assign-secondary), var(--assign-primary));
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 16px;
    }
    
    .cms-assign-corp-details {
        display: flex;
        flex-direction: column;
    }
    
    .cms-assign-corp-name {
        font-weight: 600;
        color: #2c3e50;
        font-size: 14px;
    }
    
    .cms-assign-corp-username {
        font-size: 11px;
        color: var(--assign-primary);
    }
    
    .cms-assign-unassign-btn {
        padding: 6px 12px;
        background: #ffe8e8;
        color: var(--assign-danger);
        border: 1px solid #ffc9c9;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .cms-assign-unassign-btn:hover {
        background: #ffc9c9;
        color: #a52a2a;
    }
    
    .cms-assign-unassign-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    .cms-assign-add-section {
        margin-top: 20px;
        padding-top: 20px;
        border-top: 2px dashed #d1e7e7;
    }
    
    .cms-assign-add-controls {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .cms-assign-select {
        flex: 1;
        padding: 12px 15px;
        border: 2px solid #d1e7e7;
        border-radius: 40px;
        font-size: 14px;
        color: #2c3e50;
        background: white;
    }
    
    .cms-assign-select:focus {
        outline: none;
        border-color: var(--assign-primary);
    }
    
    .cms-assign-add-btn {
        padding: 12px 24px;
        background: var(--assign-success);
        color: white;
        border: none;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }
    
    .cms-assign-add-btn:hover {
        background: #1e6b3b;
        transform: translateY(-1px);
    }
    
    .cms-assign-add-btn:disabled {
        opacity: 0.5;
        cursor: not-allowed;
        transform: none;
    }
    
    .cms-assign-empty-state {
        text-align: center;
        padding: 40px 20px;
        background: #f0f8f8;
        border-radius: 16px;
        color: #6c7a89;
    }
    
    .cms-assign-empty-state:before {
        content: '🔗';
        display: block;
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.5;
    }
    
    .cms-assign-no-corp {
        color: #6c7a89;
        font-style: italic;
        padding: 15px;
        text-align: center;
        background: #f8f8f8;
        border-radius: 10px;
        font-size: 13px;
    }
    
    .cms-assign-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 40px;
        font-size: 11px;
        font-weight: 600;
    }
    
    .cms-assign-badge.assigned {
        background: #d4edda;
        color: #155724;
    }
    
    .cms-assign-badge.unassigned {
        background: #f8d7da;
        color: #721c24;
    }
    
    .cms-assign-count-badge {
        background: var(--assign-primary);
        color: white;
        border-radius: 20px;
        padding: 2px 10px;
        font-size: 12px;
        font-weight: 600;
        margin-left: 10px;
    }
    
    .cms-assign-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 30px;
        flex-wrap: wrap;
    }
    
    .cms-assign-page-link {
        padding: 10px 16px;
        background: white;
        border: 1px solid #d1e7e7;
        border-radius: 8px;
        color: #4a5568;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    
    .cms-assign-page-link:hover {
        background: #f0f8f8;
        border-color: var(--assign-primary);
        color: var(--assign-primary);
    }
    
    .cms-assign-page-link.active {
        background: var(--assign-primary);
        color: white;
        border-color: var(--assign-primary);
    }
    
    .cms-assign-message {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .cms-assign-message.success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .cms-assign-message.success:before {
        content: '✓';
        font-size: 20px;
        font-weight: bold;
    }
    
    .cms-assign-message.error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .cms-assign-message.error:before {
        content: '⚠';
        font-size: 20px;
    }
    
    .cms-assign-message.info {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }
    
    .cms-assign-message.info:before {
        content: 'ℹ';
        font-size: 20px;
        font-weight: bold;
    }
    
    /* Modal Styles */
    .cms-assign-modal {
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
    
    .cms-assign-modal-content {
        background: white;
        padding: 30px;
        border-radius: 20px;
        max-width: 500px;
        width: 90%;
        max-height: 80vh;
        overflow-y: auto;
    }
    
    .cms-assign-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #d1e7e7;
    }
    
    .cms-assign-modal-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--assign-primary-dark);
        margin: 0;
    }
    
    .cms-assign-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #6c7a89;
    }
    
    .cms-assign-modal-body {
        margin-bottom: 25px;
    }
    
    .cms-assign-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
    }
    
    .cms-assign-modal-btn {
        padding: 12px 24px;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        transition: all 0.2s ease;
    }
    
    .cms-assign-modal-btn.confirm {
        background: var(--assign-danger);
        color: white;
    }
    
    .cms-assign-modal-btn.confirm:hover {
        background: #a52a2a;
    }
    
    .cms-assign-modal-btn.cancel {
        background: #e2e8f0;
        color: #4a5568;
    }
    
    .cms-assign-modal-btn.cancel:hover {
        background: #cbd5e0;
    }
    
    @media (max-width: 768px) {
        .cms-assign-stats {
            grid-template-columns: repeat(2, 1fr);
        }
        
        .cms-assign-controls {
            flex-direction: column;
            align-items: stretch;
        }
        
        .cms-assign-search-box {
            width: 100%;
        }
        
        .cms-assign-search-input {
            width: 100%;
        }
        
        .cms-assign-filters {
            flex-direction: column;
        }
        
        .cms-assign-filter-select {
            width: 100%;
        }
        
        .cms-assign-grid {
            grid-template-columns: 1fr;
        }
        
        .cms-assign-add-controls {
            flex-direction: column;
        }
        
        .cms-assign-add-btn {
            width: 100%;
            justify-content: center;
        }
    }
    </style>
    
    <div class="cms-assign-container <?php echo esc_attr($atts['class']); ?>" data-allow-assign="<?php echo esc_attr($atts['allow_assign']); ?>" data-allow-unassign="<?php echo esc_attr($atts['allow_unassign']); ?>">
        
        <div class="cms-assign-header">
            <h2 class="cms-assign-title"><?php echo esc_html($atts['title']); ?></h2>
            <?php if (!empty($atts['description'])): ?>
                <p class="cms-assign-description"><?php echo esc_html($atts['description']); ?></p>
            <?php endif; ?>
        </div>
        
        <?php
        // Display messages from URL parameters
        if (isset($_GET['assign_msg'])) {
            $msg = sanitize_text_field($_GET['assign_msg']);
            if ($msg === 'success') {
                echo '<div class="cms-assign-message success">' . esc_html__('Assignment completed successfully!', 'cms') . '</div>';
            } elseif ($msg === 'unassign_success') {
                echo '<div class="cms-assign-message success">' . esc_html__('Corporate account unassigned successfully!', 'cms') . '</div>';
            } elseif ($msg === 'error') {
                echo '<div class="cms-assign-message error">' . esc_html__('Operation failed. Please try again.', 'cms') . '</div>';
            } elseif ($msg === 'exists') {
                echo '<div class="cms-assign-message info">' . esc_html__('This corporate account is already assigned to the employee.', 'cms') . '</div>';
            }
        }
        ?>
        
        <!-- Statistics Cards -->
        <div class="cms-assign-stats">
            <div class="cms-assign-stat-card">
                <div class="cms-assign-stat-number"><?php echo intval($total_employees); ?></div>
                <div class="cms-assign-stat-label">Total Employees</div>
            </div>
            <div class="cms-assign-stat-card">
                <div class="cms-assign-stat-number"><?php echo count($corp_accounts); ?></div>
                <div class="cms-assign-stat-label">Corporate Accounts</div>
            </div>
            <div class="cms-assign-stat-card">
                <div class="cms-assign-stat-number"><?php echo count($assignments); ?></div>
                <div class="cms-assign-stat-label">Total Assignments</div>
            </div>
            <div class="cms-assign-stat-card">
                <div class="cms-assign-stat-number">
                    <?php 
                    $employees_with_assignments = count(array_filter($employees, function($emp) use ($assignments_by_emp) {
                        return isset($assignments_by_emp[$emp['username']]) && !empty($assignments_by_emp[$emp['username']]);
                    }));
                    echo $employees_with_assignments;
                    ?>
                </div>
                <div class="cms-assign-stat-label">Employees with Accounts</div>
            </div>
        </div>
        
        <?php if ($atts['show_search'] === 'yes' || $atts['show_filters'] === 'yes'): ?>
        <div class="cms-assign-controls">
            <?php if ($atts['show_search'] === 'yes'): ?>
            <div class="cms-assign-search-box">
                <input type="text" id="cms-assign-search" class="cms-assign-search-input" placeholder="Search employee or corporate account...">
                <button class="cms-assign-search-button" onclick="filterAssignments()">Search</button>
            </div>
            <?php endif; ?>
            
            <?php if ($atts['show_filters'] === 'yes'): ?>
            <div>
                <select id="cms-assign-filter-team" class="cms-assign-filter-select" onchange="filterAssignments()">
                    <option value="">All Teams</option>
                    <?php
                    // Get unique teams from employees
                    $teams = array_unique(array_column($employees, 'corp_team'));
                    foreach ($teams as $team) {
                        if (!empty($team)) {
                            echo '<option value="' . esc_attr($team) . '">' . esc_html($team) . '</option>';
                        }
                    }
                    ?>
                </select>
                
                <select id="cms-assign-filter-status" class="cms-assign-filter-select" onchange="filterAssignments()">
                    <option value="">All Assignment Status</option>
                    <option value="assigned">With Corporate Accounts</option>
                    <option value="unassigned">Without Corporate Accounts</option>
                </select>
            </div>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <!-- Employee Assignment Grid -->
        <div class="cms-assign-grid" id="cms-assign-grid">
            <?php foreach ($employees as $employee): 
                // Get assignments for this employee
                $emp_assignments = isset($assignments_by_emp[$employee['username']]) ? $assignments_by_emp[$employee['username']] : array();
                
                // Get assigned corporate account details
                $assigned_corps = array();
                foreach ($emp_assignments as $assignment) {
                    if (isset($corp_lookup[$assignment['username_corp_acc']])) {
                        $assigned_corps[] = $corp_lookup[$assignment['username_corp_acc']];
                    }
                }
            ?>
            <div class="cms-assign-card" data-employee-id="<?php echo esc_attr($employee['id']); ?>" data-employee-name="<?php echo esc_attr(strtolower($employee['name'])); ?>" data-employee-team="<?php echo esc_attr($employee['corp_team']); ?>" data-assigned-count="<?php echo count($assigned_corps); ?>">
                <div class="cms-assign-card-header">
                    <div class="cms-assign-employee-avatar">
                        <?php echo strtoupper(substr($employee['name'], 0, 1)); ?>
                    </div>
                    <div class="cms-assign-employee-info">
                        <div class="cms-assign-employee-name">
                            <?php echo esc_html($employee['name']); ?>
                            <span class="cms-assign-count-badge"><?php echo count($assigned_corps); ?></span>
                        </div>
                        <div class="cms-assign-employee-details">
                            <span class="cms-assign-employee-username">@<?php echo esc_html($employee['username']); ?></span>
                            <span class="cms-assign-employee-team"><?php echo esc_html($employee['corp_team']); ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="cms-assign-card-body">
                    <div class="cms-assign-section-title">
                        <span>🏢 Assigned Corporate Accounts</span>
                    </div>
                    
                    <div class="cms-assign-corp-list" id="corp-list-<?php echo esc_attr($employee['id']); ?>">
                        <?php if (empty($assigned_corps)): ?>
                            <div class="cms-assign-no-corp">
                                No corporate accounts assigned
                            </div>
                        <?php else: ?>
                            <?php foreach ($assigned_corps as $corp): ?>
                            <div class="cms-assign-corp-item" data-assignment-id="<?php echo esc_attr($employee['username'] . '_' . $corp['username']); ?>">
                                <div class="cms-assign-corp-info">
                                    <div class="cms-assign-corp-avatar">
                                        <?php echo strtoupper(substr($corp['company_name'], 0, 1)); ?>
                                    </div>
                                    <div class="cms-assign-corp-details">
                                        <span class="cms-assign-corp-name"><?php echo esc_html($corp['company_name']); ?></span>
                                        <span class="cms-assign-corp-username">@<?php echo esc_html($corp['username']); ?></span>
                                    </div>
                                </div>
                                <?php if ($atts['allow_unassign'] === 'yes'): ?>
                                <button class="cms-assign-unassign-btn" onclick="unassignCorp('<?php echo esc_js($employee['username']); ?>', '<?php echo esc_js($corp['username']); ?>', <?php echo esc_js($employee['id']); ?>)">
                                    ✕ Unassign
                                </button>
                                <?php endif; ?>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                    
                    <?php if ($atts['allow_assign'] === 'yes'): ?>
                    <div class="cms-assign-add-section">
                        <div class="cms-assign-add-controls">
                            <select class="cms-assign-select" id="corp-select-<?php echo esc_attr($employee['id']); ?>" onchange="updateAssignButton(<?php echo esc_js($employee['id']); ?>)">
                                <option value="">Select corporate account</option>
                                <?php 
                                // Filter out already assigned corps
                                $assigned_usernames = array_column($assigned_corps, 'username');
                                $available_corps = array_filter($corp_accounts, function($corp) use ($assigned_usernames) {
                                    return !in_array($corp['username'], $assigned_usernames);
                                });
                                ?>
                                <?php foreach ($available_corps as $corp): ?>
                                <option value="<?php echo esc_attr($corp['username']); ?>" data-company="<?php echo esc_attr($corp['company_name']); ?>">
                                    <?php echo esc_html($corp['company_name']); ?> (@<?php echo esc_html($corp['username']); ?>)
                                </option>
                                <?php endforeach; ?>
                            </select>
                            <button class="cms-assign-add-btn" id="assign-btn-<?php echo esc_attr($employee['id']); ?>" onclick="assignCorp('<?php echo esc_js($employee['username']); ?>', <?php echo esc_js($employee['id']); ?>)" disabled>
                                + Assign
                            </button>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="cms-assign-pagination">
            <?php
            // Build pagination links
            $base_url = remove_query_arg('assign_page');
            
            // Previous link
            if ($current_page > 1) {
                $prev_url = add_query_arg('assign_page', $current_page - 1, $base_url);
                echo '<a href="' . esc_url($prev_url) . '" class="cms-assign-page-link">« Previous</a>';
            } else {
                echo '<span class="cms-assign-page-link disabled">« Previous</span>';
            }
            
            // Page numbers
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);
            
            for ($i = $start_page; $i <= $end_page; $i++) {
                $page_url = add_query_arg('assign_page', $i, $base_url);
                $active_class = ($i == $current_page) ? 'active' : '';
                echo '<a href="' . esc_url($page_url) . '" class="cms-assign-page-link ' . $active_class . '">' . $i . '</a>';
            }
            
            // Next link
            if ($current_page < $total_pages) {
                $next_url = add_query_arg('assign_page', $current_page + 1, $base_url);
                echo '<a href="' . esc_url($next_url) . '" class="cms-assign-page-link">Next »</a>';
            } else {
                echo '<span class="cms-assign-page-link disabled">Next »</span>';
            }
            ?>
        </div>
        <?php endif; ?>
    </div>
    
    <!-- Unassign Confirmation Modal -->
    <div id="cms-unassign-modal" class="cms-assign-modal">
        <div class="cms-assign-modal-content">
            <div class="cms-assign-modal-header">
                <h3 class="cms-assign-modal-title">Confirm Unassign</h3>
                <button class="cms-assign-modal-close" onclick="closeUnassignModal()">×</button>
            </div>
            <div class="cms-assign-modal-body">
                <p style="font-size: 16px; margin-bottom: 15px;">Are you sure you want to unassign this corporate account?</p>
                <p style="color: #6c7a89; font-size: 14px;">The employee will lose access to this corporate account.</p>
            </div>
            <div class="cms-assign-modal-footer">
                <button class="cms-assign-modal-btn cancel" onclick="closeUnassignModal()">Cancel</button>
                <button class="cms-assign-modal-btn confirm" id="confirm-unassign-btn">Unassign</button>
            </div>
        </div>
    </div>
    
    <script>
    // Global variables for unassign
    var currentEmpUsername = '';
    var currentCorpUsername = '';
    var currentEmpId = 0;
    
    function updateAssignButton(empId) {
        var select = document.getElementById('corp-select-' + empId);
        var button = document.getElementById('assign-btn-' + empId);
        button.disabled = !select.value;
    }
    
    function assignCorp(empUsername, empId) {
        var select = document.getElementById('corp-select-' + empId);
        var corpUsername = select.value;
        var corpName = select.options[select.selectedIndex].text;
        
        if (!corpUsername) {
            alert('Please select a corporate account to assign.');
            return;
        }
        
        // AJAX request to assign
        var button = document.getElementById('assign-btn-' + empId);
        button.disabled = true;
        button.textContent = 'Assigning...';
        
        var formData = new FormData();
        formData.append('action', 'cms_assign_corporate_account');
        formData.append('emp_username', empUsername);
        formData.append('corp_username', corpUsername);
        formData.append('nonce', '<?php echo wp_create_nonce('cms_assign_nonce'); ?>');
        
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            button.disabled = false;
            button.textContent = '+ Assign';
            
            if (data.success) {
                // Add to list
                var corpList = document.getElementById('corp-list-' + empId);
                var noDataDiv = corpList.querySelector('.cms-assign-no-corp');
                if (noDataDiv) {
                    noDataDiv.remove();
                }
                
                var newItem = document.createElement('div');
                newItem.className = 'cms-assign-corp-item';
                newItem.setAttribute('data-assignment-id', empUsername + '_' + corpUsername);
                newItem.innerHTML = `
                    <div class="cms-assign-corp-info">
                        <div class="cms-assign-corp-avatar">${corpName.charAt(0)}</div>
                        <div class="cms-assign-corp-details">
                            <span class="cms-assign-corp-name">${corpName.split(' (')[0]}</span>
                            <span class="cms-assign-corp-username">@${corpUsername}</span>
                        </div>
                    </div>
                    <button class="cms-assign-unassign-btn" onclick="unassignCorp('${empUsername}', '${corpUsername}', ${empId})">
                        ✕ Unassign
                    </button>
                `;
                corpList.appendChild(newItem);
                
                // Remove from select
                select.remove(select.selectedIndex);
                
                // Update count badge
                var countBadge = document.querySelector(`[data-employee-id="${empId}"] .cms-assign-count-badge`);
                var currentCount = parseInt(countBadge.textContent);
                countBadge.textContent = currentCount + 1;
                
                // Update data attribute
                var card = document.querySelector(`[data-employee-id="${empId}"]`);
                card.setAttribute('data-assigned-count', currentCount + 1);
                
                alert('Corporate account assigned successfully!');
            } else {
                alert('Error: ' + data.data);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred. Please try again.');
            button.disabled = false;
            button.textContent = '+ Assign';
        });
    }
    
    function unassignCorp(empUsername, corpUsername, empId) {
        currentEmpUsername = empUsername;
        currentCorpUsername = corpUsername;
        currentEmpId = empId;
        
        var modal = document.getElementById('cms-unassign-modal');
        modal.style.display = 'flex';
    }
    
    function closeUnassignModal() {
        var modal = document.getElementById('cms-unassign-modal');
        modal.style.display = 'none';
    }
    
    document.getElementById('confirm-unassign-btn').addEventListener('click', function() {
        if (currentEmpUsername && currentCorpUsername && currentEmpId) {
            var button = this;
            button.disabled = true;
            button.textContent = 'Unassigning...';
            
            var formData = new FormData();
            formData.append('action', 'cms_unassign_corporate_account');
            formData.append('emp_username', currentEmpUsername);
            formData.append('corp_username', currentCorpUsername);
            formData.append('nonce', '<?php echo wp_create_nonce('cms_unassign_nonce'); ?>');
            
            fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                button.disabled = false;
                button.textContent = 'Unassign';
                closeUnassignModal();
                
                if (data.success) {
                    // Remove from list
                    var corpList = document.getElementById('corp-list-' + currentEmpId);
                    var items = corpList.getElementsByClassName('cms-assign-corp-item');
                    
                    for (var i = 0; i < items.length; i++) {
                        if (items[i].getAttribute('data-assignment-id') === currentEmpUsername + '_' + currentCorpUsername) {
                            var corpName = items[i].querySelector('.cms-assign-corp-name').textContent;
                            var corpDisplay = corpName + ' (@' + currentCorpUsername + ')';
                            
                            // Add back to select
                            var select = document.getElementById('corp-select-' + currentEmpId);
                            var option = document.createElement('option');
                            option.value = currentCorpUsername;
                            option.textContent = corpDisplay;
                            select.appendChild(option);
                            
                            // Remove item
                            items[i].remove();
                            break;
                        }
                    }
                    
                    // Check if list is empty
                    if (corpList.children.length === 0) {
                        corpList.innerHTML = '<div class="cms-assign-no-corp">No corporate accounts assigned</div>';
                    }
                    
                    // Update count badge
                    var countBadge = document.querySelector(`[data-employee-id="${currentEmpId}"] .cms-assign-count-badge`);
                    var currentCount = parseInt(countBadge.textContent);
                    countBadge.textContent = currentCount - 1;
                    
                    // Update data attribute
                    var card = document.querySelector(`[data-employee-id="${currentEmpId}"]`);
                    card.setAttribute('data-assigned-count', currentCount - 1);
                    
                    alert('Corporate account unassigned successfully!');
                } else {
                    alert('Error: ' + data.data);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
                button.disabled = false;
                button.textContent = 'Unassign';
                closeUnassignModal();
            });
        }
    });
    
    function filterAssignments() {
        var searchTerm = document.getElementById('cms-assign-search')?.value.toLowerCase() || '';
        var teamFilter = document.getElementById('cms-assign-filter-team')?.value || '';
        var statusFilter = document.getElementById('cms-assign-filter-status')?.value || '';
        
        var cards = document.querySelectorAll('.cms-assign-card');
        
        cards.forEach(function(card) {
            var showCard = true;
            
            // Search filter
            if (searchTerm) {
                var employeeName = card.getAttribute('data-employee-name') || '';
                var cardText = card.textContent.toLowerCase();
                if (!cardText.includes(searchTerm)) {
                    showCard = false;
                }
            }
            
            // Team filter
            if (teamFilter && showCard) {
                var employeeTeam = card.getAttribute('data-employee-team') || '';
                if (employeeTeam !== teamFilter) {
                    showCard = false;
                }
            }
            
            // Status filter
            if (statusFilter && showCard) {
                var assignedCount = parseInt(card.getAttribute('data-assigned-count') || '0');
                if (statusFilter === 'assigned' && assignedCount === 0) {
                    showCard = false;
                }
                if (statusFilter === 'unassigned' && assignedCount > 0) {
                    showCard = false;
                }
            }
            
            card.style.display = showCard ? '' : 'none';
        });
    }
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        var modal = document.getElementById('cms-unassign-modal');
        if (event.target === modal) {
            closeUnassignModal();
        }
    }
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_emp_corp_assign', 'cms_emp_corp_assign_shortcode');
add_shortcode(CMS_EMP_CORP_ASSIGN_SHORTCODE, 'cms_emp_corp_assign_shortcode');

/**
 * AJAX handler for assigning corporate account to employee
 */
function cms_ajax_assign_corporate_account() {
    global $wpdb;
    
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cms_assign_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    if (!isset($_POST['emp_username']) || !isset($_POST['corp_username'])) {
        wp_send_json_error('Missing parameters');
    }
    
    $emp_username = sanitize_user($_POST['emp_username']);
    $corp_username = sanitize_user($_POST['corp_username']);
    
    $table_assignments = $wpdb->prefix . 'cms_emp_corp_assign';
    
    // Check if assignment already exists
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $table_assignments WHERE username_emp = %s AND username_corp_acc = %s",
        $emp_username,
        $corp_username
    ));
    
    if ($exists) {
        wp_send_json_error('Assignment already exists');
    }
    
    // Insert new assignment - FIXED: using assigned_at instead of created_at
    $result = $wpdb->insert(
        $table_assignments,
        array(
            'username_emp' => $emp_username,
            'username_corp_acc' => $corp_username,
            'assigned_at' => current_time('mysql')
        ),
        array('%s', '%s', '%s')
    );
    
    if ($result) {
        wp_send_json_success('Assignment created successfully');
    } else {
        wp_send_json_error('Database error: ' . $wpdb->last_error);
    }
}
add_action('wp_ajax_cms_assign_corporate_account', 'cms_ajax_assign_corporate_account');

/**
 * AJAX handler for unassigning corporate account from employee
 */
function cms_ajax_unassign_corporate_account() {
    global $wpdb;
    
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cms_unassign_nonce')) {
        wp_send_json_error('Security check failed');
    }
    
    if (!isset($_POST['emp_username']) || !isset($_POST['corp_username'])) {
        wp_send_json_error('Missing parameters');
    }
    
    $emp_username = sanitize_user($_POST['emp_username']);
    $corp_username = sanitize_user($_POST['corp_username']);
    
    $table_assignments = $wpdb->prefix . 'cms_emp_corp_assign';
    
    // Delete assignment
    $result = $wpdb->delete(
        $table_assignments,
        array(
            'username_emp' => $emp_username,
            'username_corp_acc' => $corp_username
        ),
        array('%s', '%s')
    );
    
    if ($result) {
        wp_send_json_success('Assignment deleted successfully');
    } else {
        wp_send_json_error('Database error: ' . $wpdb->last_error);
    }
}
add_action('wp_ajax_cms_unassign_corporate_account', 'cms_ajax_unassign_corporate_account');