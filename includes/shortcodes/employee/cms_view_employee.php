<?php
/**
 * CMS View Employee Shortcode
 * Display detailed view of a single employee
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

function cms_view_employee_shortcode($atts) {
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
    
    $employee = get_cms_employee_by_id($employee_id);
    
    if (!$employee) {
        return '<div style="padding: 30px; background: #ffe8e8; color: #b34141; border-radius: 12px; text-align: center; font-size: 16px;">❌ Employee not found.</div>';
    }
    
    ob_start();
    ?>
    
    <style>
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
        margin-top: 10px;
    }
    
    .cms-emp-doc-link:hover {
        background: #d35400;
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
            
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <h1 class="cms-emp-view-name"><?php echo esc_html($employee['name']); ?></h1>
                    <div class="cms-emp-view-username">
                        <span>@<?php echo esc_html($employee['username']); ?></span>
                        <span style="opacity: 0.5;">•</span>
                        <span><?php echo esc_html($employee['email']); ?></span>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 15px; margin-top: 10px;">
                        <span class="cms-emp-view-badge cms-emp-badge-<?php echo $employee['termination_date'] ? 'terminated' : esc_attr($employee['status']); ?>">
                            <?php 
                            if ($employee['termination_date']) {
                                echo 'Terminated';
                            } else {
                                echo esc_html(ucfirst($employee['status'])); 
                            }
                            ?>
                        </span>
                        <span class="cms-emp-wage-tag">
                            <?php echo esc_html($employee['corp_team']); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <?php if ($atts['show_back_button'] === 'yes' || $atts['show_edit_button'] === 'yes'): ?>
            <div class="cms-emp-view-nav">
                <?php if ($atts['show_back_button'] === 'yes'): ?>
                <a href="<?php echo esc_url(wp_get_referer() ?: home_url('employee-list')); ?>" class="cms-emp-nav-btn">
                    ← Back to List
                </a>
                <?php endif; ?>
                
                <?php if ($atts['show_edit_button'] === 'yes'): ?>
                <a href="<?php echo esc_url(home_url('edit-employee/' . $employee['id'])); ?>" class="cms-emp-nav-btn">
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
                        <span class="cms-emp-info-value" style="font-family: monospace;"><?php echo esc_html($employee['cnic']); ?></span>
                    </div>
                    
                    <div class="cms-emp-info-row">
                        <span class="cms-emp-info-label">Employee ID</span>
                        <span class="cms-emp-info-value">#<?php echo esc_html($employee['id']); ?></span>
                    </div>
                </div>
                
                <div class="cms-emp-info-card">
                    <h3 class="cms-emp-card-title">📞 Contact Information</h3>
                    
                    <div class="cms-emp-info-row">
                        <span class="cms-emp-info-label">Phone Number</span>
                        <span class="cms-emp-info-value"><?php echo esc_html($employee['contact']); ?></span>
                    </div>
                    
                    <div class="cms-emp-info-row">
                        <span class="cms-emp-info-label">Emergency Contact</span>
                        <span class="cms-emp-info-value"><?php echo esc_html($employee['emergency']); ?></span>
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
                            <?php echo $employee['wage_type'] === 'hourly' ? '$' . number_format($employee['basic_wage'], 2) . '/hr' : '$' . number_format($employee['basic_wage'], 2) . '/mo'; ?>
                        </span>
                    </div>
                    
                    <?php if($employee['increment_percentage']): ?>
                    <div class="cms-emp-info-row">
                        <span class="cms-emp-info-label">Next Increment</span>
                        <span class="cms-emp-info-value">
                            <?php echo $employee['increment_percentage']; ?>% on <?php echo date('M d, Y', strtotime($employee['increment_date'])); ?>
                        </span>
                    </div>
                    <?php endif; ?>
                </div>
                
                <div class="cms-emp-info-card">
                    <h3 class="cms-emp-card-title">📅 Dates</h3>
                    
                    <div class="cms-emp-info-row">
                        <span class="cms-emp-info-label">Joining Date</span>
                        <span class="cms-emp-info-value"><?php echo date('F d, Y', strtotime($employee['joining_date'])); ?></span>
                    </div>
                    
                    <?php if($employee['termination_date']): ?>
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
                    <div class="cms-emp-doc-card">
                        <div class="cms-emp-doc-icon">📄</div>
                        <div class="cms-emp-doc-title">CNIC Copy</div>
                        <?php if($employee['cnic_pdf']): ?>
                            <a href="<?php echo esc_url($employee['cnic_pdf']); ?>" target="_blank" class="cms-emp-doc-link">View PDF</a>
                        <?php else: ?>
                            <div style="color: #e74c3c; font-size: 12px; margin-top: 10px;">Not Uploaded</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="cms-emp-doc-card">
                        <div class="cms-emp-doc-icon">📜</div>
                        <div class="cms-emp-doc-title">Character Certificate</div>
                        <div style="font-size: 12px; color: #718096; margin-bottom: 5px;">
                            #<?php echo esc_html($employee['char_cert_no']); ?>
                        </div>
                        <div style="font-size: 11px; color: #718096; margin-bottom: 10px;">
                            Exp: <?php echo date('M d, Y', strtotime($employee['char_cert_exp'])); ?>
                        </div>
                        <?php if($employee['char_cert_pdf']): ?>
                            <a href="<?php echo esc_url($employee['char_cert_pdf']); ?>" target="_blank" class="cms-emp-doc-link">View PDF</a>
                        <?php else: ?>
                            <div style="color: #e74c3c; font-size: 12px; margin-top: 10px;">Not Uploaded</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="cms-emp-doc-card">
                        <div class="cms-emp-doc-icon">📋</div>
                        <div class="cms-emp-doc-title">Employment Letter</div>
                        <?php if($employee['emp_letter_pdf']): ?>
                            <a href="<?php echo esc_url($employee['emp_letter_pdf']); ?>" target="_blank" class="cms-emp-doc-link">View PDF</a>
                        <?php else: ?>
                            <div style="color: #e74c3c; font-size: 12px; margin-top: 10px;">Not Uploaded</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Timeline -->
            <div class="cms-emp-timeline">
                <h3 style="font-size: 18px; color: #d35400; margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px;">
                    <span>📅</span> Employment Timeline
                </h3>
                
                <div class="cms-emp-timeline-item">
                    <div class="cms-emp-timeline-icon">📝</div>
                    <div>
                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">Employee Created</div>
                        <div style="font-size: 12px; color: #718096;">January 15, 2024 at 10:30 AM</div>
                    </div>
                </div>
                
                <div class="cms-emp-timeline-item">
                    <div class="cms-emp-timeline-icon">🎉</div>
                    <div>
                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">Joining Date</div>
                        <div style="font-size: 12px; color: #718096;"><?php echo date('F d, Y', strtotime($employee['joining_date'])); ?></div>
                    </div>
                </div>
                
                <?php if($employee['increment_date']): ?>
                <div class="cms-emp-timeline-item">
                    <div class="cms-emp-timeline-icon">📈</div>
                    <div>
                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">Next Increment</div>
                        <div style="font-size: 12px; color: #718096;"><?php echo date('F d, Y', strtotime($employee['increment_date'])); ?> (<?php echo esc_html($employee['increment_percentage']); ?>% increase)</div>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if($employee['termination_date']): ?>
                <div class="cms-emp-timeline-item">
                    <div class="cms-emp-timeline-icon">🚫</div>
                    <div>
                        <div style="font-weight: 600; color: #e74c3c; margin-bottom: 5px;">Termination Date</div>
                        <div style="font-size: 12px; color: #718096;"><?php echo date('F d, Y', strtotime($employee['termination_date'])); ?></div>
                    </div>
                </div>
                <?php endif; ?>
                
                <div class="cms-emp-timeline-item">
                    <div class="cms-emp-timeline-icon">🔄</div>
                    <div>
                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">Last Updated</div>
                        <div style="font-size: 12px; color: #718096;">February 20, 2024 at 2:45 PM</div>
                    </div>
                </div>
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
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_view_employee', 'cms_view_employee_shortcode');
add_shortcode(CMS_EMPLOYEE_VIEW_SHORTCODE, 'cms_view_employee_shortcode');

?>