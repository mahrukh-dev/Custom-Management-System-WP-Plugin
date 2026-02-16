<?php
/**
 * CMS View Employee Shortcode
 * Display detailed view of a single employee from database
 * 
 * Usage: [cms_view_employee]
 * Usage: [cms_view_employee employee_id="201"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define shortcode slug
if (!defined('CMS_EMPLOYEE_VIEW_SHORTCODE')) {
    define('CMS_EMPLOYEE_VIEW_SHORTCODE', 'cms_employee_view');
}

/**
 * View Employee Shortcode
 */
function cms_view_employee_shortcode($atts) {
    global $wpdb;
    
    $atts = shortcode_atts(
        array(
            'employee_id' => 0,
            'show_back_button' => 'yes',
            'show_edit_button' => 'yes',
            'class' => ''
        ),
        $atts,
        'cms_view_employee'
    );
    
    // Get employee ID from various sources
    $employee_id = $atts['employee_id'];
    if (!$employee_id) {
        $employee_id = get_query_var('employee_id');
        if (!$employee_id && isset($_GET['employee_id'])) {
            $employee_id = intval($_GET['employee_id']);
        }
    }
    
    if (!$employee_id) {
        return '<div style="padding: 30px; background: #fff4ed; color: #e67e22; border-radius: 12px; text-align: center; font-size: 16px;">🔍 Please select an employee to view.</div>';
    }
    
    // Get employee from database
    $table_employee = $wpdb->prefix . 'cms_employee';
    $employee = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_employee WHERE id = %d",
        $employee_id
    ), ARRAY_A);
    
    if (!$employee) {
        return '<div style="padding: 30px; background: #ffe8e8; color: #b34141; border-radius: 12px; text-align: center; font-size: 16px;">❌ Employee not found.</div>';
    }
    
    // Convert file paths to URLs
    $employee['cnic_pdf_url'] = cms_get_file_url($employee['cnic_pdf']);
    $employee['char_cert_pdf_url'] = cms_get_file_url($employee['char_cert_pdf']);
    $employee['emp_letter_pdf_url'] = cms_get_file_url($employee['emp_letter_pdf']);
    
    // Get increment history
    $table_increment = $wpdb->prefix . 'cms_increment_history';
    $increment_history = $wpdb->get_results($wpdb->prepare(
        "SELECT * FROM $table_increment WHERE username = %s ORDER BY increment_date DESC",
        $employee['username']
    ), ARRAY_A);
    
    // Get shift history stats
    $table_shifts = $wpdb->prefix . 'cms_shift_history';
    $shift_stats = $wpdb->get_row($wpdb->prepare(
        "SELECT 
            COUNT(*) as total_shifts,
            SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_shifts,
            SUM(actual_hours * 60 + actual_mins) as total_minutes
         FROM $table_shifts 
         WHERE username = %s",
        $employee['username']
    ), ARRAY_A);
    
    ob_start();
    ?>
    
    <style>
    /* View Employee Styles */
    .cms-emp-view-container {
        max-width: 1100px;
        margin: 30px auto;
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 20px 50px rgba(230,126,34,0.08);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        overflow: hidden;
    }
    
    .cms-emp-view-header {
        background: linear-gradient(145deg, #e67e22, #d35400);
        padding: 40px 35px;
        color: white;
        position: relative;
    }
    
    .cms-emp-view-avatar {
        width: 100px;
        height: 100px;
        background: linear-gradient(145deg, #f39c12, #e67e22);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 42px;
        font-weight: 700;
        color: white;
        margin-bottom: 20px;
        border: 4px solid rgba(255,255,255,0.2);
    }
    
    .cms-emp-view-name {
        font-size: 32px;
        font-weight: 700;
        margin: 0 0 5px 0;
        letter-spacing: -0.5px;
    }
    
    .cms-emp-view-username {
        font-size: 18px;
        opacity: 0.9;
        margin: 0 0 15px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .cms-emp-view-badge {
        display: inline-block;
        padding: 8px 20px;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        margin-top: 10px;
    }
    
    .cms-emp-badge-active {
        background: #27ae60;
        color: white;
    }
    
    .cms-emp-badge-inactive {
        background: #7f8c8d;
        color: white;
    }
    
    .cms-emp-badge-terminated {
        background: #e74c3c;
        color: white;
    }
    
    .cms-emp-view-nav {
        display: flex;
        gap: 20px;
        margin-top: 25px;
    }
    
    .cms-emp-nav-btn {
        padding: 12px 24px;
        background: rgba(255,255,255,0.1);
        color: white;
        text-decoration: none;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        border: 1px solid rgba(255,255,255,0.2);
    }
    
    .cms-emp-nav-btn:hover {
        background: rgba(255,255,255,0.2);
        transform: translateY(-2px);
        color: white;
    }
    
    .cms-emp-view-content {
        padding: 35px;
    }
    
    .cms-emp-info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
        margin-bottom: 30px;
    }
    
    .cms-emp-info-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 25px;
        margin-bottom: 30px;
    }
    
    .cms-emp-info-card {
        background: #fef9f5;
        border-radius: 16px;
        padding: 25px;
        border: 1px solid #ffe6d5;
        transition: all 0.2s ease;
    }
    
    .cms-emp-info-card:hover {
        box-shadow: 0 5px 15px rgba(230,126,34,0.05);
        border-color: #e67e22;
    }
    
    .cms-emp-card-title {
        font-size: 16px;
        font-weight: 600;
        color: #d35400;
        margin: 0 0 20px 0;
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 15px;
        border-bottom: 2px solid #ffe6d5;
    }
    
    .cms-emp-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px dashed #ffe6d5;
    }
    
    .cms-emp-info-row:last-child {
        border-bottom: none;
    }
    
    .cms-emp-info-label {
        font-size: 14px;
        color: #718096;
        font-weight: 500;
    }
    
    .cms-emp-info-value {
        font-size: 15px;
        color: #2c3e50;
        font-weight: 600;
    }
    
    .cms-emp-info-value.highlight {
        color: #e67e22;
    }
    
    .cms-emp-docs-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-top: 20px;
    }
    
    .cms-emp-doc-card {
        background: #ffffff;
        border: 1px solid #ffe6d5;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        transition: all 0.2s ease;
    }
    
    .cms-emp-doc-card:hover {
        border-color: #e67e22;
        background: #fef9f5;
    }
    
    .cms-emp-doc-icon {
        font-size: 32px;
        margin-bottom: 10px;
    }
    
    .cms-emp-doc-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 10px;
    }
    
    .cms-emp-doc-link {
        display: inline-block;
        padding: 8px 16px;
        background: #e67e22;
        color: white;
        text-decoration: none;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 500;
        margin: 5px;
        transition: all 0.2s ease;
    }
    
    .cms-emp-doc-link:hover {
        background: #d35400;
        color: white;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(230,126,34,0.2);
    }
    
    .cms-emp-doc-link.download {
        background: #3498db;
    }
    
    .cms-emp-doc-link.download:hover {
        background: #2980b9;
    }
    
    .cms-emp-doc-link.disabled {
        background: #e2e8f0;
        color: #a0aec0;
        pointer-events: none;
        cursor: not-allowed;
    }
    
    .cms-emp-ref-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-top: 20px;
    }
    
    .cms-emp-ref-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 25px;
        border: 1px solid #ffe6d5;
    }
    
    .cms-emp-ref-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #ffe6d5;
    }
    
    .cms-emp-ref-number {
        width: 32px;
        height: 32px;
        background: #e67e22;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
    }
    
    .cms-emp-wage-tag {
        display: inline-block;
        padding: 4px 12px;
        background: #e67e22;
        color: white;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    
    .cms-emp-timeline {
        margin-top: 30px;
        background: #fef9f5;
        border-radius: 16px;
        padding: 25px;
    }
    
    .cms-emp-timeline-item {
        display: flex;
        gap: 15px;
        padding: 15px 0;
        border-bottom: 1px solid #ffe6d5;
    }
    
    .cms-emp-timeline-item:last-child {
        border-bottom: none;
    }
    
    .cms-emp-timeline-icon {
        width: 40px;
        height: 40px;
        background: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #e67e22;
        font-size: 18px;
    }
    
    .cms-emp-stat-box {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        background: #ffffff;
        border-radius: 12px;
        margin-top: 15px;
    }
    
    .cms-emp-stat-label {
        color: #718096;
        font-size: 13px;
    }
    
    .cms-emp-stat-value {
        font-weight: 700;
        color: #e67e22;
        font-size: 18px;
    }
    
    .cms-emp-file-badge {
        display: inline-block;
        padding: 2px 8px;
        background: #27ae60;
        color: white;
        border-radius: 12px;
        font-size: 10px;
        margin-left: 5px;
    }
    
    .cms-emp-file-name {
        font-size: 11px;
        color: #64748b;
        margin: 5px 0;
        word-break: break-all;
    }
    
    @media (max-width: 768px) {
        .cms-emp-info-grid,
        .cms-emp-info-grid-3,
        .cms-emp-ref-section,
        .cms-emp-docs-grid {
            grid-template-columns: 1fr;
        }
        
        .cms-emp-view-nav {
            flex-direction: column;
        }
        
        .cms-emp-nav-btn {
            width: 100%;
            justify-content: center;
        }
    }
    </style>
    
    <div class="cms-emp-view-container <?php echo esc_attr($atts['class']); ?>">
        
        <div class="cms-emp-view-header">
            <div class="cms-emp-view-avatar">
                <?php echo strtoupper(substr($employee['name'], 0, 1)); ?>
            </div>
            
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 20px;">
                <div>
                    <h1 class="cms-emp-view-name"><?php echo esc_html($employee['name']); ?></h1>
                    <div class="cms-emp-view-username">
                        <span>@<?php echo esc_html($employee['username']); ?></span>
                        <span style="opacity: 0.5;">•</span>
                        <span><?php echo esc_html($employee['email']); ?></span>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 15px; margin-top: 10px; flex-wrap: wrap;">
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
                        <span class="cms-emp-view-badge cms-emp-badge-<?php echo $status_class; ?>">
                            <?php echo $status_text; ?>
                        </span>
                        <span class="cms-emp-wage-tag">
                            <?php echo esc_html($employee['corp_team']); ?>
                        </span>
                        <span class="cms-emp-wage-tag" style="background: #3498db;">
                            ID: #<?php echo esc_html($employee['id']); ?>
                        </span>
                    </div>
                </div>
                
                <div style="text-align: right;">
                    <div style="font-size: 14px; opacity: 0.8;">Member since</div>
                    <div style="font-size: 18px; font-weight: 600;"><?php echo date('F Y', strtotime($employee['created_at'] ?? $employee['joining_date'])); ?></div>
                </div>
            </div>
            
            <?php if ($atts['show_back_button'] === 'yes' || $atts['show_edit_button'] === 'yes'): ?>
            <div class="cms-emp-view-nav">
                <?php if ($atts['show_back_button'] === 'yes'): ?>
                <a href="javascript:history.back()" class="cms-emp-nav-btn">
                    ← Back to List
                </a>
                <?php endif; ?>
                
                <?php if ($atts['show_edit_button'] === 'yes'): ?>
                <a href="<?php echo esc_url(home_url('edit-employee?employee_id=' . $employee['id'])); ?>" class="cms-emp-nav-btn">
                    ✏️ Edit Profile
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="cms-emp-view-content">
            
            <!-- Personal Information -->
            <div class="cms-emp-info-grid">
                <div class="cms-emp-info-card">
                    <h3 class="cms-emp-card-title">👤 Personal Information</h3>
                    
                    <div class="cms-emp-info-row">
                        <span class="cms-emp-info-label">Full Name</span>
                        <span class="cms-emp-info-value"><?php echo esc_html($employee['name']); ?></span>
                    </div>
                    
                    <div class="cms-emp-info-row">
                        <span class="cms-emp-info-label">Father's Name</span>
                        <span class="cms-emp-info-value"><?php echo esc_html($employee['father_name']); ?></span>
                    </div>
                    
                    <div class="cms-emp-info-row">
                        <span class="cms-emp-info-label">CNIC Number</span>
                        <span class="cms-emp-info-value" style="font-family: monospace;"><?php echo esc_html($employee['cnic_no']); ?></span>
                    </div>
                    
                    <div class="cms-emp-info-row">
                        <span class="cms-emp-info-label">Username</span>
                        <span class="cms-emp-info-value">@<?php echo esc_html($employee['username']); ?></span>
                    </div>
                </div>
                
                <div class="cms-emp-info-card">
                    <h3 class="cms-emp-card-title">📞 Contact Information</h3>
                    
                    <div class="cms-emp-info-row">
                        <span class="cms-emp-info-label">Phone Number</span>
                        <span class="cms-emp-info-value"><?php echo esc_html($employee['contact_num']); ?></span>
                    </div>
                    
                    <div class="cms-emp-info-row">
                        <span class="cms-emp-info-label">Emergency Contact</span>
                        <span class="cms-emp-info-value"><?php echo esc_html($employee['emergency_cno']); ?></span>
                    </div>
                    
                    <div class="cms-emp-info-row">
                        <span class="cms-emp-info-label">Email</span>
                        <span class="cms-emp-info-value"><?php echo esc_html($employee['email']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Employment Details -->
            <div class="cms-emp-info-grid-3">
                <div class="cms-emp-info-card">
                    <h3 class="cms-emp-card-title">💼 Position</h3>
                    
                    <div class="cms-emp-info-row">
                        <span class="cms-emp-info-label">Team</span>
                        <span class="cms-emp-info-value"><?php echo esc_html($employee['corp_team']); ?></span>
                    </div>
                    
                    <div class="cms-emp-info-row">
                        <span class="cms-emp-info-label">Position</span>
                        <span class="cms-emp-info-value"><?php echo esc_html($employee['position']); ?></span>
                    </div>
                </div>
                
                <div class="cms-emp-info-card">
                    <h3 class="cms-emp-card-title">💰 Compensation</h3>
                    
                    <div class="cms-emp-info-row">
                        <span class="cms-emp-info-label">Wage Type</span>
                        <span class="cms-emp-info-value"><?php echo esc_html(ucfirst($employee['wage_type'])); ?></span>
                    </div>
                    
                    <div class="cms-emp-info-row">
                        <span class="cms-emp-info-label">Basic Wage</span>
                        <span class="cms-emp-info-value highlight">
                            <?php 
                            if ($employee['wage_type'] === 'hourly') {
                                echo '$' . number_format($employee['basic_wage'], 2) . '/hr';
                            } else {
                                echo '$' . number_format($employee['basic_wage'], 2) . '/mo';
                            }
                            ?>
                        </span>
                    </div>
                    
                    <?php if (!empty($employee['updated_wage'])): ?>
                    <div class="cms-emp-info-row">
                        <span class="cms-emp-info-label">Updated Wage</span>
                        <span class="cms-emp-info-value highlight">
                            $<?php echo number_format($employee['updated_wage'], 2); ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="cms-emp-info-card">
                    <h3 class="cms-emp-card-title">📅 Important Dates</h3>
                    
                    <div class="cms-emp-info-row">
                        <span class="cms-emp-info-label">Joining Date</span>
                        <span class="cms-emp-info-value"><?php echo date('F d, Y', strtotime($employee['joining_date'])); ?></span>
                    </div>
                    
                    <?php if (!empty($employee['increment_date'])): ?>
                    <div class="cms-emp-info-row">
                        <span class="cms-emp-info-label">Next Increment</span>
                        <span class="cms-emp-info-value"><?php echo date('F d, Y', strtotime($employee['increment_date'])); ?></span>
                    </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($employee['termination_date'])): ?>
                    <div class="cms-emp-info-row">
                        <span class="cms-emp-info-label">Termination Date</span>
                        <span class="cms-emp-info-value" style="color: #e74c3c;">
                            <?php echo date('F d, Y', strtotime($employee['termination_date'])); ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Reference Information -->
            <div style="margin-bottom: 30px;">
                <h3 style="font-size: 20px; color: #d35400; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">👥</span> Reference Information
                </h3>
                
                <div class="cms-emp-ref-section">
                    <div class="cms-emp-ref-card">
                        <div class="cms-emp-ref-header">
                            <span class="cms-emp-ref-number">1</span>
                            <h4 style="margin: 0; font-size: 18px; color: #d35400;">Primary Reference</h4>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <div style="font-size: 14px; color: #718096; margin-bottom: 5px;">Full Name</div>
                            <div style="font-size: 18px; font-weight: 600; color: #2c3e50;"><?php echo esc_html($employee['ref1_name']); ?></div>
                        </div>
                        
                        <div>
                            <div style="font-size: 14px; color: #718096; margin-bottom: 5px;">Contact Number</div>
                            <div style="font-size: 16px; color: #e67e22; font-weight: 600;"><?php echo esc_html($employee['ref1_cno']); ?></div>
                        </div>
                    </div>
                    
                    <div class="cms-emp-ref-card">
                        <div class="cms-emp-ref-header">
                            <span class="cms-emp-ref-number">2</span>
                            <h4 style="margin: 0; font-size: 18px; color: #d35400;">Secondary Reference</h4>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <div style="font-size: 14px; color: #718096; margin-bottom: 5px;">Full Name</div>
                            <div style="font-size: 18px; font-weight: 600; color: #2c3e50;"><?php echo esc_html($employee['ref2_name']); ?></div>
                        </div>
                        
                        <div>
                            <div style="font-size: 14px; color: #718096; margin-bottom: 5px;">Contact Number</div>
                            <div style="font-size: 16px; color: #e67e22; font-weight: 600;"><?php echo esc_html($employee['ref2_cno']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Documents -->
            <div style="margin-bottom: 30px;">
                <h3 style="font-size: 20px; color: #d35400; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">📄</span> Documents
                </h3>
                
                <div class="cms-emp-docs-grid">
                    <!-- CNIC Card -->
                    <div class="cms-emp-doc-card">
                        <div class="cms-emp-doc-icon">📄</div>
                        <div class="cms-emp-doc-title">CNIC Copy</div>
                        <?php if (!empty($employee['cnic_pdf'])): ?>
                            <div class="cms-emp-file-name">
                                <?php echo basename($employee['cnic_pdf']); ?>
                                <span class="cms-emp-file-badge">PDF</span>
                            </div>
                            <div style="margin: 10px 0;">
                                <a href="<?php echo esc_url($employee['cnic_pdf_url']); ?>" 
                                   target="_blank" 
                                   class="cms-emp-doc-link"
                                   onclick="return cmsOpenPDF('<?php echo esc_url($employee['cnic_pdf_url']); ?>')">
                                    👁️ View
                                </a>
                                <a href="<?php echo esc_url($employee['cnic_pdf_url']); ?>" 
                                   download 
                                   class="cms-emp-doc-link download">
                                    ⬇️ Download
                                </a>
                            </div>
                        <?php else: ?>
                            <div style="color: #e74c3c; font-size: 12px; margin: 15px 0;">Not Uploaded</div>
                            <span class="cms-emp-doc-link disabled">View PDF</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Character Certificate Card -->
                    <div class="cms-emp-doc-card">
                        <div class="cms-emp-doc-icon">📜</div>
                        <div class="cms-emp-doc-title">Character Certificate</div>
                        <?php if (!empty($employee['char_cert_no'])): ?>
                        <div style="font-size: 12px; color: #718096; margin-bottom: 5px;">
                            #<?php echo esc_html($employee['char_cert_no']); ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($employee['char_cert_exp'])): ?>
                        <div style="font-size: 11px; color: #718096; margin-bottom: 10px;">
                            Exp: <?php echo date('M d, Y', strtotime($employee['char_cert_exp'])); ?>
                        </div>
                        <?php endif; ?>
                        <?php if (!empty($employee['char_cert_pdf'])): ?>
                            <div class="cms-emp-file-name">
                                <?php echo basename($employee['char_cert_pdf']); ?>
                            </div>
                            <a href="<?php echo esc_url($employee['char_cert_pdf_url']); ?>" 
                               target="_blank" 
                               class="cms-emp-doc-link"
                               onclick="return cmsOpenPDF('<?php echo esc_url($employee['char_cert_pdf_url']); ?>')">
                                📜 View PDF
                            </a>
                        <?php else: ?>
                            <div style="color: #e74c3c; font-size: 12px; margin-top: 10px;">Not Uploaded</div>
                            <span class="cms-emp-doc-link disabled">View PDF</span>
                        <?php endif; ?>
                    </div>
                    
                    <!-- Employment Letter Card -->
                    <div class="cms-emp-doc-card">
                        <div class="cms-emp-doc-icon">📋</div>
                        <div class="cms-emp-doc-title">Employment Letter</div>
                        <?php if (!empty($employee['emp_letter_pdf'])): ?>
                            <div class="cms-emp-file-name">
                                <?php echo basename($employee['emp_letter_pdf']); ?>
                            </div>
                            <a href="<?php echo esc_url($employee['emp_letter_pdf_url']); ?>" 
                               target="_blank" 
                               class="cms-emp-doc-link"
                               onclick="return cmsOpenPDF('<?php echo esc_url($employee['emp_letter_pdf_url']); ?>')">
                                📋 View PDF
                            </a>
                        <?php else: ?>
                            <div style="color: #e74c3c; font-size: 12px; margin: 15px 0;">Not Uploaded</div>
                            <span class="cms-emp-doc-link disabled">View PDF</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Increment History -->
            <?php if (!empty($increment_history)): ?>
            <div style="margin-bottom: 30px;">
                <h3 style="font-size: 20px; color: #d35400; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">📈</span> Increment History
                </h3>
                
                <div style="background: #fef9f5; border-radius: 16px; padding: 20px; border: 1px solid #ffe6d5;">
                    <table style="width: 100%; border-collapse: collapse;">
                        <thead>
                            <tr style="border-bottom: 2px solid #ffe6d5;">
                                <th style="padding: 12px; text-align: left; color: #d35400;">Date</th>
                                <th style="padding: 12px; text-align: left; color: #d35400;">Basic Wage</th>
                                <th style="padding: 12px; text-align: left; color: #d35400;">Increment %</th>
                                <th style="padding: 12px; text-align: left; color: #d35400;">Updated Wage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($increment_history as $increment): ?>
                            <tr style="border-bottom: 1px solid #ffe6d5;">
                                <td style="padding: 12px;"><?php echo date('M d, Y', strtotime($increment['increment_date'])); ?></td>
                                <td style="padding: 12px;">$<?php echo number_format($increment['basic_wage'], 2); ?></td>
                                <td style="padding: 12px;"><?php echo $increment['increment_percentage']; ?>%</td>
                                <td style="padding: 12px; font-weight: 600; color: #27ae60;">$<?php echo number_format($increment['updated_wage'], 2); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Work Statistics -->
            <?php if ($shift_stats && $shift_stats['total_shifts'] > 0): ?>
            <div style="margin-bottom: 30px;">
                <h3 style="font-size: 20px; color: #d35400; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">📊</span> Work Statistics
                </h3>
                
                <div class="cms-emp-info-grid-3">
                    <div class="cms-emp-info-card">
                        <div class="cms-emp-stat-box">
                            <span class="cms-emp-stat-label">Total Shifts</span>
                            <span class="cms-emp-stat-value"><?php echo intval($shift_stats['total_shifts']); ?></span>
                        </div>
                        <div class="cms-emp-stat-box">
                            <span class="cms-emp-stat-label">Completed Shifts</span>
                            <span class="cms-emp-stat-value"><?php echo intval($shift_stats['completed_shifts']); ?></span>
                        </div>
                    </div>
                    
                    <div class="cms-emp-info-card">
                        <div class="cms-emp-stat-box">
                            <span class="cms-emp-stat-label">Total Hours Worked</span>
                            <span class="cms-emp-stat-value">
                                <?php 
                                $total_hours = floor(intval($shift_stats['total_minutes']) / 60);
                                $total_mins = intval($shift_stats['total_minutes']) % 60;
                                echo $total_hours . 'h ' . $total_mins . 'm';
                                ?>
                            </span>
                        </div>
                        <div class="cms-emp-stat-box">
                            <span class="cms-emp-stat-label">Avg. per Shift</span>
                            <span class="cms-emp-stat-value">
                                <?php 
                                if ($shift_stats['total_shifts'] > 0) {
                                    $avg_minutes = floor(intval($shift_stats['total_minutes']) / $shift_stats['total_shifts']);
                                    echo floor($avg_minutes / 60) . 'h ' . ($avg_minutes % 60) . 'm';
                                } else {
                                    echo '0h 0m';
                                }
                                ?>
                            </span>
                        </div>
                    </div>
                    
                    <div class="cms-emp-info-card">
                        <div class="cms-emp-stat-box">
                            <span class="cms-emp-stat-label">Attendance Rate</span>
                            <span class="cms-emp-stat-value">
                                <?php 
                                $attendance_rate = ($shift_stats['total_shifts'] > 0) 
                                    ? round(($shift_stats['completed_shifts'] / $shift_stats['total_shifts']) * 100, 1)
                                    : 0;
                                echo $attendance_rate . '%';
                                ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            
            <!-- Timeline -->
            <div class="cms-emp-timeline">
                <h3 style="font-size: 18px; color: #d35400; margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px;">
                    <span>📅</span> Employment Timeline
                </h3>
                
                <div class="cms-emp-timeline-item">
                    <div class="cms-emp-timeline-icon">📝</div>
                    <div>
                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">Employee Created</div>
                        <div style="font-size: 12px; color: #718096;">
                            <?php echo date('F d, Y', strtotime($employee['created_at'] ?? $employee['joining_date'])); ?>
                            <?php if (!empty($employee['created_at'])): ?>
                                at <?php echo date('h:i A', strtotime($employee['created_at'])); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <div class="cms-emp-timeline-item">
                    <div class="cms-emp-timeline-icon">🎉</div>
                    <div>
                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">Joining Date</div>
                        <div style="font-size: 12px; color: #718096;"><?php echo date('F d, Y', strtotime($employee['joining_date'])); ?></div>
                    </div>
                </div>
                
                <?php if (!empty($increment_history)): ?>
                <?php foreach (array_slice($increment_history, 0, 2) as $increment): ?>
                <div class="cms-emp-timeline-item">
                    <div class="cms-emp-timeline-icon">📈</div>
                    <div>
                        <div style="font-weight: 600; color: #27ae60; margin-bottom: 5px;">Increment Applied</div>
                        <div style="font-size: 12px; color: #718096;">
                            <?php echo date('F d, Y', strtotime($increment['increment_date'])); ?> 
                            (<?php echo $increment['increment_percentage']; ?>% increase)
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
                
                <?php if (!empty($employee['increment_date']) && empty($increment_history)): ?>
                <div class="cms-emp-timeline-item">
                    <div class="cms-emp-timeline-icon">📈</div>
                    <div>
                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">Next Increment Scheduled</div>
                        <div style="font-size: 12px; color: #718096;">
                            <?php echo date('F d, Y', strtotime($employee['increment_date'])); ?>
                            (<?php echo $employee['increment_percentage']; ?>% increase)
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($employee['termination_date'])): ?>
                <div class="cms-emp-timeline-item">
                    <div class="cms-emp-timeline-icon">🚫</div>
                    <div>
                        <div style="font-weight: 600; color: #e74c3c; margin-bottom: 5px;">Termination Date</div>
                        <div style="font-size: 12px; color: #718096;"><?php echo date('F d, Y', strtotime($employee['termination_date'])); ?></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($employee['updated_at'])): ?>
                <div class="cms-emp-timeline-item">
                    <div class="cms-emp-timeline-icon">🔄</div>
                    <div>
                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">Last Updated</div>
                        <div style="font-size: 12px; color: #718096;">
                            <?php echo date('F d, Y', strtotime($employee['updated_at'])); ?>
                            at <?php echo date('h:i A', strtotime($employee['updated_at'])); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: flex-end;">
                <button onclick="window.print()" style="padding: 12px 24px; background: white; border: 2px solid #ffe6d5; border-radius: 40px; color: #4a5568; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    🖨️ Print Profile
                </button>
                <a href="mailto:<?php echo esc_attr($employee['email']); ?>" style="padding: 12px 24px; background: #e67e22; border: none; border-radius: 40px; color: white; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; text-decoration: none;">
                    📧 Send Email
                </a>
            </div>
        </div>
    </div>
    
    <script>
    function cmsOpenPDF(url) {
        if (!url || url === '' || url === '#') {
            alert('PDF file not found or path is invalid');
            return false;
        }
        
        // Check if URL is valid
        if (url.startsWith('http') || url.startsWith('/')) {
            window.open(url, '_blank');
            return false; // Prevent default link behavior
        } else {
            alert('Invalid PDF file path: ' + url);
            return false;
        }
    }
    
    // Add download functionality and validate links
    document.addEventListener('DOMContentLoaded', function() {
        // Validate all PDF links
        document.querySelectorAll('.cms-emp-doc-link').forEach(function(link) {
            if (link.href && !link.classList.contains('disabled')) {
                // Check if URL seems valid
                if (!link.href.match(/\.pdf$/i) && !link.href.includes('pdf')) {
                    console.log('Warning: Link may not be a PDF:', link.href);
                }
            }
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_view_employee', 'cms_view_employee_shortcode');
add_shortcode(CMS_EMPLOYEE_VIEW_SHORTCODE, 'cms_view_employee_shortcode');