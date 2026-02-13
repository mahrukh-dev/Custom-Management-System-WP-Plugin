<?php
/**
 * CMS List Employee Shortcode
 * Display all employees in a table with actions
 * 
 * Usage: [cms_list_employee]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define shortcode slug
if (!defined('CMS_EMPLOYEE_LIST_SHORTCODE')) {
    define('CMS_EMPLOYEE_LIST_SHORTCODE', 'cms_employee_list');
}

function cms_list_employee_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'items_per_page' => 10,
            'show_search' => 'yes',
            'show_filters' => 'yes',
            'actions' => 'view,update,delete',
            'no_data_message' => 'No employee records found.',
            'table_class' => '',
            'create_page' => 'add-employee',
            'edit_page' => 'edit-employee',
            'view_page' => 'view-employee',
        ),
        $atts,
        'cms_list_employee'
    );
    
    ob_start();
    
    $employee_data = get_cms_mock_employee_data();
    ?>
    
    <style>
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
    
    .cms-emp-table-responsive {
        overflow-x: auto;
        margin-bottom: 25px;
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
    }
    </style>
    
    <div class="cms-emp-list-container <?php echo esc_attr($atts['table_class']); ?>">
        
        <div class="cms-emp-list-header">
            <h2 class="cms-emp-list-title">Employee Management</h2>
            
            <?php if ($atts['show_search'] === 'yes'): ?>
            <div class="cms-emp-search-box">
                <input type="text" id="cms-emp-search" class="cms-emp-search-input" placeholder="Search by name, email, CNIC...">
                <button class="cms-emp-search-button">Search</button>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($atts['show_filters'] === 'yes'): ?>
        <div class="cms-emp-filters">
            <select class="cms-emp-filter-select" id="filter-team">
                <option value="">All Teams</option>
                <option value="IT">IT</option>
                <option value="HR">HR</option>
                <option value="Finance">Finance</option>
                <option value="Marketing">Marketing</option>
                <option value="Sales">Sales</option>
                <option value="Operations">Operations</option>
            </select>
            
            <select class="cms-emp-filter-select" id="filter-wage">
                <option value="">All Wage Types</option>
                <option value="hourly">Hourly</option>
                <option value="monthly">Monthly</option>
            </select>
            
            <select class="cms-emp-filter-select" id="filter-status">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="terminated">Terminated</option>
            </select>
            
            <select class="cms-emp-filter-select" id="sort-by">
                <option value="">Sort By</option>
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="name">Name A-Z</option>
                <option value="wage">Wage (High-Low)</option>
            </select>
        </div>
        <?php endif; ?>
        
        <?php if (empty($employee_data)): ?>
            <div class="cms-emp-no-data">
                <?php echo esc_html($atts['no_data_message']); ?>
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
                            <div style="font-family: monospace;"><?php echo esc_html($employee['cnic']); ?></div>
                        </td>
                        <td>
                            <span class="cms-emp-team-badge"><?php echo esc_html($employee['corp_team']); ?></span>
                            <div style="font-size: 12px; margin-top: 5px; color: #718096;">
                                <?php echo esc_html($employee['position']); ?>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 500;"><?php echo esc_html($employee['contact']); ?></div>
                            <div style="font-size: 11px; color: #718096; margin-top: 3px;">
                                Emergency: <?php echo esc_html($employee['emergency']); ?>
                            </div>
                        </td>
                        <td>
                            <span class="cms-emp-wage-badge <?php echo esc_attr($employee['wage_type']); ?>">
                                <?php echo esc_html(ucfirst($employee['wage_type'])); ?>
                            </span>
                            <div style="font-size: 14px; font-weight: 600; color: #2c3e50; margin-top: 5px;">
                                <?php echo esc_html($employee['wage_type'] === 'hourly' ? '$' . number_format($employee['basic_wage'], 2) . '/hr' : '$' . number_format($employee['basic_wage'], 2) . '/mo'); ?>
                            </div>
                            <?php if ($employee['increment_percentage']): ?>
                            <div style="font-size: 11px; color: #27ae60; margin-top: 3px;">
                                +<?php echo esc_html($employee['increment_percentage']); ?>% increment
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo date('M d, Y', strtotime($employee['joining_date'])); ?>
                        </td>
                        <td>
                            <div class="cms-emp-doc-indicator">
                                📄 CNIC
                                <?php if($employee['cnic_pdf']): ?>✓<?php else: ?>✗<?php endif; ?>
                            </div>
                            <div class="cms-emp-doc-indicator">
                                📜 Certificate
                                <?php if($employee['char_cert_pdf']): ?>✓<?php else: ?>✗<?php endif; ?>
                            </div>
                            <div class="cms-emp-doc-indicator">
                                📋 Letter
                                <?php if($employee['emp_letter_pdf']): ?>✓<?php else: ?>✗<?php endif; ?>
                            </div>
                        </td>
                        <td>
                            <span class="cms-emp-status <?php echo $employee['termination_date'] ? 'terminated' : esc_attr($employee['status']); ?>">
                                <?php 
                                if ($employee['termination_date']) {
                                    echo 'Terminated';
                                } else {
                                    echo esc_html(ucfirst($employee['status'])); 
                                }
                                ?>
                            </span>
                            <?php if($employee['termination_date']): ?>
                            <div style="font-size: 10px; color: #a94442; margin-top: 3px;">
                                <?php echo date('M d, Y', strtotime($employee['termination_date'])); ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="cms-emp-action-buttons">
                                <?php if (strpos($atts['actions'], 'view') !== false): ?>
                                <a href="<?php echo esc_url(home_url('view-employee/' . $employee['id'])); ?>" class="cms-emp-action-btn cms-emp-btn-view">
                                    👁️ View
                                </a>
                                <?php endif; ?>
                                
                                <?php if (strpos($atts['actions'], 'update') !== false): ?>
                                <a href="<?php echo esc_url(home_url('edit-employee/' . $employee['id'])); ?>" class="cms-emp-action-btn cms-emp-btn-edit">
                                    ✏️ Edit
                                </a>
                                <?php endif; ?>
                                
                                <?php if (strpos($atts['actions'], 'delete') !== false): ?>
                                <button class="cms-emp-action-btn cms-emp-btn-delete" onclick="cmsConfirmDeleteEmp(<?php echo esc_js($employee['id']); ?>)">
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
        
        <div class="cms-emp-pagination">
            <a href="#" class="cms-emp-page-link">« Previous</a>
            <a href="#" class="cms-emp-page-link active">1</a>
            <a href="#" class="cms-emp-page-link">2</a>
            <a href="#" class="cms-emp-page-link">3</a>
            <a href="#" class="cms-emp-page-link">Next »</a>
        </div>
        
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
                <p style="font-size:16px; margin-bottom:20px;">Are you sure you want to delete this employee?</p>
                <p style="color:#718096; font-size:14px;">This action cannot be undone. All employee data including documents will be permanently removed.</p>
            </div>
            <div style="display:flex; justify-content:flex-end;">
                <button style="background:#e2e8f0; color:#4a5568; padding:12px 24px; border:none; border-radius:8px; cursor:pointer; margin-right:10px;" onclick="document.getElementById('cms-emp-delete-modal').style.display='none'">Cancel</button>
                <button id="cms-emp-confirm-delete-btn" style="background:#e74c3c; color:white; padding:12px 24px; border:none; border-radius:8px; cursor:pointer; font-weight:600;">Delete Employee</button>
            </div>
        </div>
    </div>
    
    <script>
    function cmsConfirmDeleteEmp(employeeId) {
        var modal = document.getElementById('cms-emp-delete-modal');
        var confirmBtn = document.getElementById('cms-emp-confirm-delete-btn');
        
        confirmBtn.onclick = function() {
            cmsDeleteEmployee(employeeId);
        };
        
        modal.style.display = 'flex';
    }
    
    function cmsDeleteEmployee(employeeId) {
        var row = document.getElementById('emp-row-' + employeeId);
        if (row) {
            row.style.opacity = '0.5';
            setTimeout(function() {
                row.remove();
                document.getElementById('cms-emp-delete-modal').style.display = 'none';
                alert('Employee deleted successfully!');
                
                if (document.querySelectorAll('.cms-emp-table tbody tr').length === 0) {
                    location.reload();
                }
            }, 500);
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.getElementById('cms-emp-search');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                var searchTerm = this.value.toLowerCase();
                var rows = document.querySelectorAll('.cms-emp-table tbody tr');
                
                rows.forEach(function(row) {
                    var text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }
        
        // Team filter
        var teamFilter = document.getElementById('filter-team');
        var wageFilter = document.getElementById('filter-wage');
        var statusFilter = document.getElementById('filter-status');
        
        function applyEmpFilters() {
            var teamValue = teamFilter ? teamFilter.value.toLowerCase() : '';
            var wageValue = wageFilter ? wageFilter.value.toLowerCase() : '';
            var statusValue = statusFilter ? statusFilter.value.toLowerCase() : '';
            var rows = document.querySelectorAll('.cms-emp-table tbody tr');
            
            rows.forEach(function(row) {
                var showRow = true;
                
                if (teamValue) {
                    var teamCell = row.querySelector('.cms-emp-team-badge');
                    if (teamCell && teamCell.textContent.toLowerCase() !== teamValue) {
                        showRow = false;
                    }
                }
                
                if (wageValue) {
                    var wageCell = row.querySelector('.cms-emp-wage-badge');
                    if (wageCell && !wageCell.classList.contains(wageValue)) {
                        showRow = false;
                    }
                }
                
                if (statusValue) {
                    var statusCell = row.querySelector('.cms-emp-status');
                    if (statusCell) {
                        if (statusValue === 'terminated') {
                            if (!statusCell.classList.contains('terminated')) showRow = false;
                        } else {
                            if (!statusCell.classList.contains(statusValue)) showRow = false;
                        }
                    }
                }
                
                row.style.display = showRow ? '' : 'none';
            });
        }
        
        if (teamFilter) teamFilter.addEventListener('change', applyEmpFilters);
        if (wageFilter) wageFilter.addEventListener('change', applyEmpFilters);
        if (statusFilter) statusFilter.addEventListener('change', applyEmpFilters);
    });
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_list_employee', 'cms_list_employee_shortcode');
add_shortcode(CMS_EMPLOYEE_LIST_SHORTCODE, 'cms_list_employee_shortcode');

/**
 * Mock Employee Data
 */
function get_cms_mock_employee_data() {
    return array(
        array(
            'id' => 201,
            'username' => 'john_employee',
            'name' => 'John Smith',
            'email' => 'john.smith@company.com',
            'father_name' => 'Robert Smith',
            'cnic' => '12345-1234567-1',
            'position' => 'Senior Software Engineer',
            'corp_team' => 'IT',
            'contact' => '+1 234-567-8901',
            'emergency' => '+1 234-567-8902',
            'joining_date' => '2024-01-15',
            'wage_type' => 'monthly',
            'basic_wage' => 5000.00,
            'increment_date' => '2024-07-15',
            'increment_percentage' => 10.5,
            'termination_date' => null,
            'ref1_name' => 'Michael Johnson',
            'ref1_cno' => '+1 234-567-8903',
            'ref2_name' => 'Sarah Williams',
            'ref2_cno' => '+1 234-567-8904',
            'char_cert_no' => 'CERT-2024-001',
            'char_cert_exp' => '2026-01-15',
            'cnic_pdf' => '/uploads/cms-employee-docs/cnic_001.pdf',
            'char_cert_pdf' => '/uploads/cms-employee-docs/cert_001.pdf',
            'emp_letter_pdf' => '/uploads/cms-employee-docs/letter_001.pdf',
            'status' => 'active'
        ),
        array(
            'id' => 202,
            'username' => 'emily_jones',
            'name' => 'Emily Jones',
            'email' => 'emily.jones@company.com',
            'father_name' => 'David Jones',
            'cnic' => '12345-2345678-2',
            'position' => 'HR Manager',
            'corp_team' => 'HR',
            'contact' => '+44 20 1234 5678',
            'emergency' => '+44 20 1234 5679',
            'joining_date' => '2023-06-01',
            'wage_type' => 'monthly',
            'basic_wage' => 4500.00,
            'increment_date' => '2024-06-01',
            'increment_percentage' => 8.0,
            'termination_date' => null,
            'ref1_name' => 'Lisa Cooper',
            'ref1_cno' => '+44 20 1234 5680',
            'ref2_name' => 'James Wilson',
            'ref2_cno' => '+44 20 1234 5681',
            'char_cert_no' => 'CERT-2023-089',
            'char_cert_exp' => '2025-06-01',
            'cnic_pdf' => '/uploads/cms-employee-docs/cnic_002.pdf',
            'char_cert_pdf' => '/uploads/cms-employee-docs/cert_002.pdf',
            'emp_letter_pdf' => '/uploads/cms-employee-docs/letter_002.pdf',
            'status' => 'active'
        ),
        array(
            'id' => 203,
            'username' => 'david_miller',
            'name' => 'David Miller',
            'email' => 'david.miller@company.com',
            'father_name' => 'Thomas Miller',
            'cnic' => '12345-3456789-3',
            'position' => 'Financial Analyst',
            'corp_team' => 'Finance',
            'contact' => '+91 98765 43210',
            'emergency' => '+91 98765 43211',
            'joining_date' => '2024-02-01',
            'wage_type' => 'monthly',
            'basic_wage' => 3500.00,
            'increment_date' => null,
            'increment_percentage' => null,
            'termination_date' => null,
            'ref1_name' => 'Priya Sharma',
            'ref1_cno' => '+91 98765 43212',
            'ref2_name' => 'Rajesh Kumar',
            'ref2_cno' => '+91 98765 43213',
            'char_cert_no' => 'CERT-2024-023',
            'char_cert_exp' => '2026-02-01',
            'cnic_pdf' => '/uploads/cms-employee-docs/cnic_003.pdf',
            'char_cert_pdf' => '/uploads/cms-employee-docs/cert_003.pdf',
            'emp_letter_pdf' => '/uploads/cms-employee-docs/letter_003.pdf',
            'status' => 'active'
        ),
        array(
            'id' => 204,
            'username' => 'sarah_ahmed',
            'name' => 'Sarah Ahmed',
            'email' => 'sarah.ahmed@company.com',
            'father_name' => 'Ahmed Khan',
            'cnic' => '12345-4567890-4',
            'position' => 'Sales Representative',
            'corp_team' => 'Sales',
            'contact' => '+92 300 7654321',
            'emergency' => '+92 300 7654322',
            'joining_date' => '2023-11-15',
            'wage_type' => 'hourly',
            'basic_wage' => 25.50,
            'increment_date' => '2024-05-15',
            'increment_percentage' => 5.0,
            'termination_date' => null,
            'ref1_name' => 'Bilal Ahmed',
            'ref1_cno' => '+92 300 7654323',
            'ref2_name' => 'Fatima Hassan',
            'ref2_cno' => '+92 300 7654324',
            'char_cert_no' => 'CERT-2023-156',
            'char_cert_exp' => '2025-11-15',
            'cnic_pdf' => '/uploads/cms-employee-docs/cnic_004.pdf',
            'char_cert_pdf' => '/uploads/cms-employee-docs/cert_004.pdf',
            'emp_letter_pdf' => '/uploads/cms-employee-docs/letter_004.pdf',
            'status' => 'active'
        ),
        array(
            'id' => 205,
            'username' => 'michael_brown',
            'name' => 'Michael Brown',
            'email' => 'michael.brown@company.com',
            'father_name' => 'Charles Brown',
            'cnic' => '12345-5678901-5',
            'position' => 'Marketing Specialist',
            'corp_team' => 'Marketing',
            'contact' => '+1 345-678-9012',
            'emergency' => '+1 345-678-9013',
            'joining_date' => '2024-03-01',
            'wage_type' => 'monthly',
            'basic_wage' => 3800.00,
            'increment_date' => null,
            'increment_percentage' => null,
            'termination_date' => '2024-12-31',
            'ref1_name' => 'Jennifer Lee',
            'ref1_cno' => '+1 345-678-9014',
            'ref2_name' => 'Robert Chen',
            'ref2_cno' => '+1 345-678-9015',
            'char_cert_no' => 'CERT-2024-045',
            'char_cert_exp' => '2026-03-01',
            'cnic_pdf' => '/uploads/cms-employee-docs/cnic_005.pdf',
            'char_cert_pdf' => '/uploads/cms-employee-docs/cert_005.pdf',
            'emp_letter_pdf' => '/uploads/cms-employee-docs/letter_005.pdf',
            'status' => 'inactive'
        )
    );
}
?>