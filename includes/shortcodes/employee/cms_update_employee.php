<?php
/**
 * CMS Update Employee Shortcode
 * Form to update existing employee data
 * 
 * Usage: [cms_update_employee]
 * Usage: [cms_update_employee employee_id="201"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define shortcode slug
if (!defined('CMS_EMPLOYEE_UPDATE_SHORTCODE')) {
    define('CMS_EMPLOYEE_UPDATE_SHORTCODE', 'cms_employee_update');
}

function cms_update_employee_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'employee_id' => 0,
            'title' => 'Update Employee Profile',
            'button_text' => 'Update Employee',
            'success_message' => 'Employee updated successfully!',
            'class' => ''
        ),
        $atts,
        'cms_update_employee'
    );
    
    $employee_id = $atts['employee_id'];
    if (!$employee_id) {
        $employee_id = get_query_var('employee_id');
        if (!$employee_id && isset($_GET['employee_id'])) {
            $employee_id = intval($_GET['employee_id']);
        }
    }
    
    if (!$employee_id) {
        return '<div style="padding: 20px; background: #ffe8e8; color: #b34141; border-radius: 8px; text-align: center;">No employee selected. Please provide an employee ID.</div>';
    }
    
    $employee = get_cms_employee_by_id($employee_id);
    
    if (!$employee) {
        return '<div style="padding: 20px; background: #ffe8e8; color: #b34141; border-radius: 8px; text-align: center;">Employee not found.</div>';
    }
    
    ob_start();
    ?>
    
    <style>
    .cms-emp-update-container {
        max-width: 1000px;
        margin: 30px auto;
        padding: 35px;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(230,126,34,0.05);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        border-top: 4px solid #e67e22;
    }
    
    .cms-emp-update-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #ffe6d5;
    }
    
    .cms-emp-update-title {
        margin: 0;
        font-size: 26px;
        font-weight: 700;
        color: #d35400;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .cms-emp-update-title:before {
        content: '✏️';
        font-size: 28px;
    }
    
    .cms-emp-back-link {
        padding: 10px 20px;
        background: #fef9f5;
        color: #4a5568;
        text-decoration: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        border: 1px solid #ffe6d5;
    }
    
    .cms-emp-back-link:hover {
        background: #ffe6d5;
        color: #d35400;
    }
    
    .cms-emp-update-section {
        background: #fef9f5;
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 25px;
        border: 1px solid #ffe6d5;
    }
    
    .cms-emp-update-section-title {
        margin: 0 0 20px 0;
        font-size: 18px;
        font-weight: 600;
        color: #d35400;
        padding-bottom: 12px;
        border-bottom: 2px solid #ffe6d5;
    }
    
    .cms-emp-form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .cms-emp-form-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }
    
    .cms-emp-form-group {
        margin-bottom: 5px;
    }
    
    .cms-emp-form-group.full-width {
        grid-column: span 2;
    }
    
    .cms-emp-form-group.full-width-3 {
        grid-column: span 3;
    }
    
    .cms-emp-form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #34495e;
        font-size: 14px;
    }
    
    .cms-emp-required {
        color: #e74c3c;
        margin-left: 4px;
    }
    
    .cms-emp-form-control {
        width: 100%;
        padding: 14px 18px;
        font-size: 15px;
        border: 2px solid #ffe6d5;
        border-radius: 12px;
        transition: all 0.25s ease;
        box-sizing: border-box;
        background: #ffffff;
    }
    
    .cms-emp-form-control:focus {
        outline: none;
        border-color: #e67e22;
        box-shadow: 0 0 0 4px rgba(230,126,34,0.05);
    }
    
    .cms-emp-form-control[readonly] {
        background: #fef9f5;
        border-color: #ffe6d5;
        color: #718096;
        cursor: not-allowed;
    }
    
    .cms-emp-phone-group {
        display: flex;
        gap: 10px;
    }
    
    .cms-emp-country-code {
        width: 120px;
        flex-shrink: 0;
    }
    
    .cms-emp-wage-group {
        display: flex;
        gap: 15px;
    }
    
    .cms-emp-file-info {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 10px 15px;
        background: #ffffff;
        border: 1px solid #ffe6d5;
        border-radius: 8px;
        margin-top: 5px;
    }
    
    .cms-emp-file-link {
        color: #e67e22;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 5px;
        font-size: 13px;
    }
    
    .cms-emp-file-link:hover {
        color: #d35400;
        text-decoration: underline;
    }
    
    .cms-emp-update-footer {
        margin-top: 30px;
        display: flex;
        gap: 15px;
        justify-content: flex-end;
    }
    
    .cms-emp-update-button {
        padding: 16px 32px;
        background: linear-gradient(145deg, #e67e22, #d35400);
        color: white;
        border: none;
        border-radius: 40px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .cms-emp-update-button:hover {
        background: linear-gradient(145deg, #d35400, #a04000);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(230,126,34,0.2);
    }
    
    .cms-emp-cancel-button {
        padding: 16px 32px;
        background: #fef9f5;
        color: #4a5568;
        border: 2px solid #ffe6d5;
        border-radius: 40px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .cms-emp-cancel-button:hover {
        background: #ffe6d5;
        border-color: #e67e22;
    }
    
    .cms-emp-message {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cms-emp-message.success {
        background: #e3f7ec;
        color: #0a5c36;
        border: 1px solid #b8e0c2;
    }
    
    .cms-emp-message.error {
        background: #ffe8e8;
        color: #b34141;
        border: 1px solid #ffc9c9;
    }
    
    .cms-emp-info-box {
        background: #fff4ed;
        border-left: 4px solid #e67e22;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        font-size: 14px;
        color: #2c3e50;
    }
    </style>
    
    <div class="cms-emp-update-container <?php echo esc_attr($atts['class']); ?>">
        
        <div class="cms-emp-update-header">
            <h2 class="cms-emp-update-title"><?php echo esc_html($atts['title']); ?></h2>
            <a href="<?php echo esc_url(remove_query_arg('employee_id', wp_get_referer())); ?>" class="cms-emp-back-link">
                ← Back to List
            </a>
        </div>
        
        <?php
        if (isset($_GET['update']) && $_GET['update'] === 'success') {
            echo '<div class="cms-emp-message success">' . esc_html($atts['success_message']) . '</div>';
        }
        
        if (isset($_GET['update']) && $_GET['update'] === 'error') {
            echo '<div class="cms-emp-message error">Update failed. Please try again.</div>';
        }
        ?>
        
        <div class="cms-emp-info-box">
            <strong>📝 Editing Employee:</strong> <?php echo esc_html($employee['name']); ?> (ID: <?php echo esc_html($employee_id); ?>)
        </div>
        
        <form method="post" action="" id="cms-emp-update-form" enctype="multipart/form-data">
            <?php wp_nonce_field('cms_employee_update', 'cms_emp_update_nonce'); ?>
            <input type="hidden" name="cms_employee_id" value="<?php echo esc_attr($employee_id); ?>">
            <input type="hidden" name="cms_emp_update_action" value="update_employee">
            
            <!-- Personal Information -->
            <div class="cms-emp-update-section">
                <h3 class="cms-emp-update-section-title">Personal Information</h3>
                
                <div class="cms-emp-form-grid">
                    <div class="cms-emp-form-group">
                        <label for="emp-username">Username</label>
                        <input 
                            type="text" 
                            id="emp-username" 
                            name="emp_username" 
                            class="cms-emp-form-control" 
                            value="<?php echo esc_attr($employee['username']); ?>"
                            readonly
                        >
                    </div>
                    
                    <div class="cms-emp-form-group">
                        <label for="emp-fullname">Full Name <span class="cms-emp-required">*</span></label>
                        <input 
                            type="text" 
                            id="emp-fullname" 
                            name="emp_fullname" 
                            class="cms-emp-form-control" 
                            value="<?php echo esc_attr($employee['name']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="cms-emp-form-group">
                        <label for="emp-email">Email Address <span class="cms-emp-required">*</span></label>
                        <input 
                            type="email" 
                            id="emp-email" 
                            name="emp_email" 
                            class="cms-emp-form-control" 
                            value="<?php echo esc_attr($employee['email']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="cms-emp-form-group">
                        <label for="emp-fathername">Father's Name <span class="cms-emp-required">*</span></label>
                        <input 
                            type="text" 
                            id="emp-fathername" 
                            name="emp_fathername" 
                            class="cms-emp-form-control" 
                            value="<?php echo esc_attr($employee['father_name']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="cms-emp-form-group">
                        <label for="emp-cnic">CNIC Number</label>
                        <input 
                            type="text" 
                            id="emp-cnic" 
                            name="emp_cnic" 
                            class="cms-emp-form-control" 
                            value="<?php echo esc_attr($employee['cnic']); ?>"
                            readonly
                        >
                    </div>
                    
                    <div class="cms-emp-form-group">
                        <label for="emp-position">Position <span class="cms-emp-required">*</span></label>
                        <input 
                            type="text" 
                            id="emp-position" 
                            name="emp_position" 
                            class="cms-emp-form-control" 
                            value="<?php echo esc_attr($employee['position']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="cms-emp-form-group">
                        <label for="emp-corp-team">Corporate Team <span class="cms-emp-required">*</span></label>
                        <select id="emp-corp-team" name="emp_corp_team" class="cms-emp-form-control" required>
                            <option value="IT" <?php selected($employee['corp_team'], 'IT'); ?>>IT Department</option>
                            <option value="HR" <?php selected($employee['corp_team'], 'HR'); ?>>Human Resources</option>
                            <option value="Finance" <?php selected($employee['corp_team'], 'Finance'); ?>>Finance & Accounts</option>
                            <option value="Marketing" <?php selected($employee['corp_team'], 'Marketing'); ?>>Marketing</option>
                            <option value="Sales" <?php selected($employee['corp_team'], 'Sales'); ?>>Sales</option>
                            <option value="Operations" <?php selected($employee['corp_team'], 'Operations'); ?>>Operations</option>
                            <option value="Administration" <?php selected($employee['corp_team'], 'Administration'); ?>>Administration</option>
                            <option value="Customer Support" <?php selected($employee['corp_team'], 'Customer Support'); ?>>Customer Support</option>
                            <option value="Research & Development" <?php selected($employee['corp_team'], 'Research & Development'); ?>>R&D</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="cms-emp-update-section">
                <h3 class="cms-emp-update-section-title">Contact Information</h3>
                
                <div class="cms-emp-form-grid">
                    <div class="cms-emp-form-group">
                        <label for="emp-contact">Contact Number <span class="cms-emp-required">*</span></label>
                        <div class="cms-emp-phone-group">
                            <input 
                                type="text" 
                                id="emp-contact-code" 
                                name="emp_contact_code" 
                                class="cms-emp-form-control cms-emp-country-code" 
                                value="<?php echo esc_attr(explode(' ', $employee['contact'])[0]); ?>"
                                placeholder="+1"
                            >
                            <input 
                                type="tel" 
                                id="emp-contact" 
                                name="emp_contact" 
                                class="cms-emp-form-control" 
                                value="<?php echo esc_attr(implode(' ', array_slice(explode(' ', $employee['contact']), 1))); ?>"
                                required
                            >
                        </div>
                    </div>
                    
                    <div class="cms-emp-form-group">
                        <label for="emp-emergency">Emergency Contact <span class="cms-emp-required">*</span></label>
                        <div class="cms-emp-phone-group">
                            <input 
                                type="text" 
                                id="emp-emergency-code" 
                                name="emp_emergency_code" 
                                class="cms-emp-form-control cms-emp-country-code" 
                                value="<?php echo esc_attr(explode(' ', $employee['emergency'])[0]); ?>"
                                placeholder="+1"
                            >
                            <input 
                                type="tel" 
                                id="emp-emergency" 
                                name="emp_emergency" 
                                class="cms-emp-form-control" 
                                value="<?php echo esc_attr(implode(' ', array_slice(explode(' ', $employee['emergency']), 1))); ?>"
                                required
                            >
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Employment Details -->
            <div class="cms-emp-update-section">
                <h3 class="cms-emp-update-section-title">Employment Details</h3>
                
                <div class="cms-emp-form-grid-3">
                    <div class="cms-emp-form-group">
                        <label for="emp-joining-date">Joining Date</label>
                        <input 
                            type="date" 
                            id="emp-joining-date" 
                            name="emp_joining_date" 
                            class="cms-emp-form-control" 
                            value="<?php echo esc_attr($employee['joining_date']); ?>"
                            readonly
                        >
                    </div>
                    
                    <div class="cms-emp-form-group">
                        <label for="emp-wage-type">Wage Type</label>
                        <select id="emp-wage-type" name="emp_wage_type" class="cms-emp-form-control" required>
                            <option value="hourly" <?php selected($employee['wage_type'], 'hourly'); ?>>Hourly</option>
                            <option value="monthly" <?php selected($employee['wage_type'], 'monthly'); ?>>Monthly</option>
                        </select>
                    </div>
                    
                    <div class="cms-emp-form-group">
                        <label for="emp-basic-wage">Basic Wage/Amount <span class="cms-emp-required">*</span></label>
                        <input 
                            type="number" 
                            id="emp-basic-wage" 
                            name="emp_basic_wage" 
                            class="cms-emp-form-control" 
                            value="<?php echo esc_attr($employee['basic_wage']); ?>"
                            required
                            min="0"
                            step="0.01"
                        >
                    </div>
                    
                    <div class="cms-emp-form-group">
                        <label for="emp-increment-date">Increment Date</label>
                        <input 
                            type="date" 
                            id="emp-increment-date" 
                            name="emp_increment_date" 
                            class="cms-emp-form-control" 
                            value="<?php echo esc_attr($employee['increment_date']); ?>"
                        >
                    </div>
                    
                    <div class="cms-emp-form-group">
                        <label for="emp-increment-percentage">Increment Percentage (%)</label>
                        <input 
                            type="number" 
                            id="emp-increment-percentage" 
                            name="emp_increment_percentage" 
                            class="cms-emp-form-control" 
                            value="<?php echo esc_attr($employee['increment_percentage']); ?>"
                            min="0"
                            max="100"
                            step="0.1"
                        >
                    </div>
                    
                    <div class="cms-emp-form-group">
                        <label for="emp-termination-date">Termination Date</label>
                        <input 
                            type="date" 
                            id="emp-termination-date" 
                            name="emp_termination_date" 
                            class="cms-emp-form-control" 
                            value="<?php echo esc_attr($employee['termination_date']); ?>"
                        >
                    </div>
                </div>
            </div>
            
            <!-- Reference Information -->
            <div class="cms-emp-update-section">
                <h3 class="cms-emp-update-section-title">Reference Information</h3>
                
                <div class="cms-emp-form-grid">
                    <div class="cms-emp-form-group">
                        <label for="emp-ref1-name">Reference #1 Name <span class="cms-emp-required">*</span></label>
                        <input 
                            type="text" 
                            id="emp-ref1-name" 
                            name="emp_ref1_name" 
                            class="cms-emp-form-control" 
                            value="<?php echo esc_attr($employee['ref1_name']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="cms-emp-form-group">
                        <label for="emp-ref1-cno">Reference #1 Contact <span class="cms-emp-required">*</span></label>
                        <input 
                            type="tel" 
                            id="emp-ref1-cno" 
                            name="emp_ref1_cno" 
                            class="cms-emp-form-control" 
                            value="<?php echo esc_attr($employee['ref1_cno']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="cms-emp-form-group">
                        <label for="emp-ref2-name">Reference #2 Name <span class="cms-emp-required">*</span></label>
                        <input 
                            type="text" 
                            id="emp-ref2-name" 
                            name="emp_ref2_name" 
                            class="cms-emp-form-control" 
                            value="<?php echo esc_attr($employee['ref2_name']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="cms-emp-form-group">
                        <label for="emp-ref2-cno">Reference #2 Contact <span class="cms-emp-required">*</span></label>
                        <input 
                            type="tel" 
                            id="emp-ref2-cno" 
                            name="emp_ref2_cno" 
                            class="cms-emp-form-control" 
                            value="<?php echo esc_attr($employee['ref2_cno']); ?>"
                            required
                        >
                    </div>
                </div>
            </div>
            
            <!-- Documents Information -->
            <div class="cms-emp-update-section">
                <h3 class="cms-emp-update-section-title">Documents</h3>
                
                <div class="cms-emp-form-grid-3">
                    <div class="cms-emp-form-group">
                        <label>CNIC PDF</label>
                        <?php if($employee['cnic_pdf']): ?>
                        <div class="cms-emp-file-info">
                            <span>📄</span>
                            <a href="<?php echo esc_url($employee['cnic_pdf']); ?>" target="_blank" class="cms-emp-file-link">
                                View Current File
                            </a>
                        </div>
                        <?php endif; ?>
                        <input 
                            type="file" 
                            id="emp-cnic-pdf" 
                            name="emp_cnic_pdf" 
                            accept=".pdf,.PDF"
                        >
                    </div>
                    
                    <div class="cms-emp-form-group">
                        <label for="emp-char-cert-no">Character Certificate #</label>
                        <input 
                            type="text" 
                            id="emp-char-cert-no" 
                            name="emp_char_cert_no" 
                            class="cms-emp-form-control" 
                            value="<?php echo esc_attr($employee['char_cert_no']); ?>"
                        >
                    </div>
                    
                    <div class="cms-emp-form-group">
                        <label for="emp-char-cert-exp">Certificate Expiry</label>
                        <input 
                            type="date" 
                            id="emp-char-cert-exp" 
                            name="emp_char_cert_exp" 
                            class="cms-emp-form-control" 
                            value="<?php echo esc_attr($employee['char_cert_exp']); ?>"
                        >
                    </div>
                    
                    <div class="cms-emp-form-group">
                        <label>Character Certificate PDF</label>
                        <?php if($employee['char_cert_pdf']): ?>
                        <div class="cms-emp-file-info">
                            <span>📜</span>
                            <a href="<?php echo esc_url($employee['char_cert_pdf']); ?>" target="_blank" class="cms-emp-file-link">
                                View Current File
                            </a>
                        </div>
                        <?php endif; ?>
                        <input 
                            type="file" 
                            id="emp-char-cert-pdf" 
                            name="emp_char_cert_pdf" 
                            accept=".pdf,.PDF"
                        >
                    </div>
                    
                    <div class="cms-emp-form-group">
                        <label>Employment Letter PDF</label>
                        <?php if($employee['emp_letter_pdf']): ?>
                        <div class="cms-emp-file-info">
                            <span>📋</span>
                            <a href="<?php echo esc_url($employee['emp_letter_pdf']); ?>" target="_blank" class="cms-emp-file-link">
                                View Current File
                            </a>
                        </div>
                        <?php endif; ?>
                        <input 
                            type="file" 
                            id="emp-letter-pdf" 
                            name="emp_letter_pdf" 
                            accept=".pdf,.PDF"
                        >
                    </div>
                    
                    <div class="cms-emp-form-group">
                        <label for="emp-status">Status</label>
                        <select id="emp-status" name="emp_status" class="cms-emp-form-control">
                            <option value="active" <?php selected($employee['status'], 'active'); ?>>Active</option>
                            <option value="inactive" <?php selected($employee['status'], 'inactive'); ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="cms-emp-update-footer">
                <a href="<?php echo esc_url(remove_query_arg('employee_id', wp_get_referer())); ?>" class="cms-emp-cancel-button">
                    Cancel
                </a>
                <button type="submit" name="cms_emp_update_submit" class="cms-emp-update-button">
                    💾 <?php echo esc_html($atts['button_text']); ?>
                </button>
            </div>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#emp-cnic').on('input', function() {
            var value = $(this).val().replace(/[^0-9]/g, '');
            if (value.length > 5) {
                value = value.substring(0, 5) + '-' + value.substring(5);
            }
            if (value.length > 13) {
                value = value.substring(0, 13) + '-' + value.substring(13, 14);
            }
            $(this).val(value);
        });
        
        $('#cms-emp-update-form').on('submit', function(e) {
            var isValid = true;
            
            $(this).find('[required]').each(function() {
                if (!$(this).val()) {
                    $(this).addClass('error');
                    isValid = false;
                } else {
                    $(this).removeClass('error');
                }
            });
            
            var email = $('#emp-email');
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email.val() && !emailPattern.test(email.val())) {
                email.addClass('error');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill all required fields correctly.');
                return false;
            }
            
            $(this).find('.cms-emp-update-button').text('Updating...').prop('disabled', true);
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_update_employee', 'cms_update_employee_shortcode');
add_shortcode(CMS_EMPLOYEE_UPDATE_SHORTCODE, 'cms_update_employee_shortcode');

function get_cms_employee_by_id($id) {
    $mock_employees = get_cms_mock_employee_data();
    
    foreach ($mock_employees as $employee) {
        if ($employee['id'] == $id) {
            return $employee;
        }
    }
    
    return null;
}

function cms_handle_employee_update() {
    if (isset($_POST['cms_emp_update_submit']) && isset($_POST['cms_emp_update_action']) && $_POST['cms_emp_update_action'] === 'update_employee') {
        
        if (!isset($_POST['cms_emp_update_nonce']) || !wp_verify_nonce($_POST['cms_emp_update_nonce'], 'cms_employee_update')) {
            wp_redirect(add_query_arg('update', 'error', wp_get_referer()));
            exit;
        }
        
        $employee_id = intval($_POST['cms_employee_id']);
        
        wp_redirect(add_query_arg('update', 'success', wp_get_referer()));
        exit;
    }
}
add_action('init', 'cms_handle_employee_update');

?>