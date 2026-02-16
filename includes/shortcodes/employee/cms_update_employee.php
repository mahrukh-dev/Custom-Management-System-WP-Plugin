<?php
/**
 * CMS Update Employee Shortcode
 * Form to update existing employee data in database
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

/**
 * Handle Employee Update Form Submission via init hook
 */
function cms_handle_employee_update_direct() {
    // Check if our form was submitted
    if (!isset($_POST['cms_emp_update_submit'])) {
        return;
    }
    
    global $wpdb;
    
    // Verify nonce
    if (!isset($_POST['cms_emp_update_nonce']) || !wp_verify_nonce($_POST['cms_emp_update_nonce'], 'cms_employee_update')) {
        wp_redirect(add_query_arg('update', 'error', wp_get_referer()));
        exit;
    }
    
    $employee_id = intval($_POST['cms_employee_id']);
    $redirect_url = isset($_POST['redirect_url']) ? esc_url_raw($_POST['redirect_url']) : wp_get_referer();
    
    if (!$employee_id) {
        wp_redirect(add_query_arg('update', 'error', $redirect_url));
        exit;
    }
    
    // Get current employee data
    $table_employee = $wpdb->prefix . 'cms_employee';
    $current_employee = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_employee WHERE id = %d",
        $employee_id
    ), ARRAY_A);
    
    if (!$current_employee) {
        wp_redirect(add_query_arg('update', 'error', $redirect_url));
        exit;
    }
    
    // Handle file uploads
    $uploaded_files = cms_handle_update_file_uploads($current_employee['username']);
    
    // Prepare update data with proper formatting
    $update_data = array(
        'name' => sanitize_text_field($_POST['emp_fullname']),
        'email' => sanitize_email($_POST['emp_email']),
        'father_name' => sanitize_text_field($_POST['emp_fathername']),
        'contact_num' => sanitize_text_field($_POST['emp_contact_code'] . ' ' . preg_replace('/[^0-9]/', '', $_POST['emp_contact'])),
        'emergency_cno' => sanitize_text_field($_POST['emp_emergency_code'] . ' ' . preg_replace('/[^0-9]/', '', $_POST['emp_emergency'])),
        'position' => sanitize_text_field($_POST['emp_position']),
        'corp_team' => sanitize_text_field($_POST['emp_corp_team']),
        'wage_type' => sanitize_text_field($_POST['emp_wage_type']),
        'basic_wage' => floatval($_POST['emp_basic_wage']),
        'ref1_name' => sanitize_text_field($_POST['emp_ref1_name']),
        'ref1_cno' => preg_replace('/[^0-9]/', '', $_POST['emp_ref1_cno']),
        'ref2_name' => sanitize_text_field($_POST['emp_ref2_name']),
        'ref2_cno' => preg_replace('/[^0-9]/', '', $_POST['emp_ref2_cno']),
        'char_cert_no' => sanitize_text_field($_POST['emp_char_cert_no']),
        'status' => sanitize_text_field($_POST['emp_status'])
    );
    
    // Handle optional fields
    if (!empty($_POST['emp_increment_date'])) {
        $update_data['increment_date'] = sanitize_text_field($_POST['emp_increment_date']);
    } else {
        $update_data['increment_date'] = null;
    }
    
    if (!empty($_POST['emp_increment_percentage'])) {
        $update_data['increment_percentage'] = floatval($_POST['emp_increment_percentage']);
    } else {
        $update_data['increment_percentage'] = null;
    }
    
    if (!empty($_POST['emp_termination_date'])) {
        $update_data['termination_date'] = sanitize_text_field($_POST['emp_termination_date']);
    } else {
        $update_data['termination_date'] = null;
    }
    
    if (!empty($_POST['emp_char_cert_exp'])) {
        $update_data['char_cert_exp'] = sanitize_text_field($_POST['emp_char_cert_exp']);
    } else {
        $update_data['char_cert_exp'] = null;
    }
    
    // Calculate updated wage if increment data provided
    if (!empty($update_data['increment_percentage']) && !empty($update_data['increment_date'])) {
        $update_data['updated_wage'] = $update_data['basic_wage'] + ($update_data['basic_wage'] * $update_data['increment_percentage'] / 100);
    } else {
        $update_data['updated_wage'] = null;
    }
    
    // Merge with uploaded file paths
    if (!empty($uploaded_files)) {
        if (isset($uploaded_files['cnic_pdf'])) {
            $update_data['cnic_pdf'] = $uploaded_files['cnic_pdf'];
        }
        if (isset($uploaded_files['char_cert_pdf'])) {
            $update_data['char_cert_pdf'] = $uploaded_files['char_cert_pdf'];
        }
        if (isset($uploaded_files['emp_letter_pdf'])) {
            $update_data['emp_letter_pdf'] = $uploaded_files['emp_letter_pdf'];
        }
    }
    
    // Prepare format array for wpdb->update
    $format = array(
        'name' => '%s',
        'email' => '%s',
        'father_name' => '%s',
        'contact_num' => '%s',
        'emergency_cno' => '%s',
        'position' => '%s',
        'corp_team' => '%s',
        'wage_type' => '%s',
        'basic_wage' => '%f',
        'increment_date' => '%s',
        'increment_percentage' => '%f',
        'updated_wage' => '%f',
        'termination_date' => '%s',
        'ref1_name' => '%s',
        'ref1_cno' => '%s',
        'ref2_name' => '%s',
        'ref2_cno' => '%s',
        'char_cert_no' => '%s',
        'char_cert_exp' => '%s',
        'cnic_pdf' => '%s',
        'char_cert_pdf' => '%s',
        'emp_letter_pdf' => '%s',
        'status' => '%s'
    );
    
    // Remove null values and their formats
    foreach ($update_data as $key => $value) {
        if ($value === null) {
            unset($update_data[$key]);
            unset($format[$key]);
        }
    }
    
    // Debug: Log the update data
    error_log('CMS Update Data: ' . print_r($update_data, true));
    error_log('CMS Update Format: ' . print_r($format, true));
    
    // Start transaction
    $wpdb->query('START TRANSACTION');
    
    try {
        // Update employee table
        $updated = $wpdb->update(
            $table_employee,
            $update_data,
            array('id' => $employee_id),
            $format,
            array('%d')
        );
        
        if ($updated === false) {
            // Get the last error
            $db_error = $wpdb->last_error;
            error_log('CMS DB Error: ' . $db_error);
            throw new Exception('Database error: ' . $db_error);
        }
        
        // If increment data was added, record in increment history
        if (!empty($_POST['emp_increment_date']) && !empty($_POST['emp_increment_percentage'])) {
            $table_increment = $wpdb->prefix . 'cms_increment_history';
            
            // Check if this increment is already recorded
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table_increment 
                 WHERE username = %s AND increment_date = %s",
                $current_employee['username'],
                sanitize_text_field($_POST['emp_increment_date'])
            ));
            
            if (!$exists) {
                $increment_data = array(
                    'username' => $current_employee['username'],
                    'increment_date' => sanitize_text_field($_POST['emp_increment_date']),
                    'basic_wage' => floatval($_POST['emp_basic_wage']),
                    'increment_percentage' => floatval($_POST['emp_increment_percentage'])
                );
                
                if (isset($update_data['updated_wage'])) {
                    $increment_data['updated_wage'] = $update_data['updated_wage'];
                }
                
                $increment_inserted = $wpdb->insert(
                    $table_increment,
                    $increment_data,
                    array('%s', '%s', '%f', '%f', '%f')
                );
                
                if ($increment_inserted === false) {
                    error_log('CMS Increment Insert Error: ' . $wpdb->last_error);
                }
            }
        }
        
        $wpdb->query('COMMIT');
        
        // Log the update
        error_log("CMS: Employee updated successfully - ID: $employee_id, Username: {$current_employee['username']}");
        
        // Redirect with success message
        wp_redirect(add_query_arg('update', 'success', $redirect_url));
        exit;
        
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        error_log('CMS Employee Update Error: ' . $e->getMessage());
        wp_redirect(add_query_arg(array('update' => 'error', 'error_msg' => urlencode($e->getMessage())), $redirect_url));
        exit;
    }
}
add_action('init', 'cms_handle_employee_update_direct');

/**
 * Update Employee Shortcode
 */
function cms_update_employee_shortcode($atts) {
    global $wpdb;
    
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
    
    // Get employee ID from various sources
    $employee_id = $atts['employee_id'];
    if (!$employee_id) {
        $employee_id = get_query_var('employee_id');
        if (!$employee_id && isset($_GET['employee_id'])) {
            $employee_id = intval($_GET['employee_id']);
        }
    }
    
    if (!$employee_id) {
        return '<div style="padding: 30px; background: #fff4ed; color: #e67e22; border-radius: 12px; text-align: center; font-size: 16px;">🔍 Please select an employee to update.</div>';
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
    
    // Check if function exists before using it
    if (function_exists('cms_get_file_url')) {
        $employee['cnic_pdf_url'] = cms_get_file_url($employee['cnic_pdf']);
        $employee['char_cert_pdf_url'] = cms_get_file_url($employee['char_cert_pdf']);
        $employee['emp_letter_pdf_url'] = cms_get_file_url($employee['emp_letter_pdf']);
    } else {
        $employee['cnic_pdf_url'] = $employee['cnic_pdf'];
        $employee['char_cert_pdf_url'] = $employee['char_cert_pdf'];
        $employee['emp_letter_pdf_url'] = $employee['emp_letter_pdf'];
    }
    
    // Parse contact numbers
    $contact_parts = explode(' ', $employee['contact_num'], 2);
    $contact_code = isset($contact_parts[0]) ? $contact_parts[0] : '+1';
    $contact_number = isset($contact_parts[1]) ? $contact_parts[1] : $employee['contact_num'];
    
    $emergency_parts = explode(' ', $employee['emergency_cno'], 2);
    $emergency_code = isset($emergency_parts[0]) ? $emergency_parts[0] : '+1';
    $emergency_number = isset($emergency_parts[1]) ? $emergency_parts[1] : $employee['emergency_cno'];
    
    // Get current URL for form action
    $current_url = add_query_arg('employee_id', $employee_id, get_permalink());
    
    ob_start();
    ?>
    
    <style>
    /* Update Employee Styles - same as before */
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
    
    .cms-emp-form-control.error {
        border-color: #e74c3c;
    }
    
    .cms-emp-phone-group {
        display: flex;
        gap: 10px;
    }
    
    .cms-emp-country-code {
        width: 120px;
        flex-shrink: 0;
    }
    
    .cms-emp-file-info {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 12px 15px;
        background: #f0f9ff;
        border: 1px solid #b8e0ff;
        border-radius: 8px;
        margin-top: 5px;
        margin-bottom: 10px;
    }
    
    .cms-emp-file-link {
        color: #0369a1;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        font-size: 13px;
        font-weight: 500;
        padding: 4px 10px;
        background: white;
        border-radius: 20px;
        border: 1px solid #b8e0ff;
        transition: all 0.2s ease;
    }
    
    .cms-emp-file-link:hover {
        background: #e0f2fe;
        color: #0284c7;
    }
    
    .cms-emp-file-link.view {
        color: #e67e22;
        border-color: #ffe6d5;
    }
    
    .cms-emp-file-link.view:hover {
        background: #fff4ed;
    }
    
    .cms-emp-file-link.download {
        color: #059669;
        border-color: #a7f3d0;
    }
    
    .cms-emp-file-link.download:hover {
        background: #d1fae5;
    }
    
    .cms-emp-file-badge {
        background: #e67e22;
        color: white;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 10px;
        margin-left: 8px;
    }
    
    .cms-emp-file-name {
        font-size: 11px;
        color: #64748b;
        word-break: break-all;
        margin-top: 2px;
    }
    
    .cms-emp-file-note {
        font-size: 12px;
        color: #718096;
        margin-top: 5px;
        display: block;
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
    
    .cms-emp-update-button:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
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
    
    .cms-emp-progress {
        width: 100%;
        height: 4px;
        background: #ffe6d5;
        border-radius: 2px;
        margin: 20px 0;
        overflow: hidden;
        display: none;
    }
    
    .cms-emp-progress-bar {
        height: 100%;
        background: #e67e22;
        width: 0%;
        transition: width 0.3s ease;
    }
    
    .cms-emp-debug-box {
        background: #f1f5f9;
        border-left: 4px solid #3b82f6;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        font-size: 13px;
        color: #1e293b;
        display: none; /* Hide by default, enable for debugging */
    }
    </style>
    
    <div class="cms-emp-update-container <?php echo esc_attr($atts['class']); ?>">
        
        <div class="cms-emp-update-header">
            <h2 class="cms-emp-update-title"><?php echo esc_html($atts['title']); ?></h2>
            <a href="<?php echo esc_url(remove_query_arg('employee_id', wp_get_referer() ?: home_url('employee-list'))); ?>" class="cms-emp-back-link">
                ← Back to List
            </a>
        </div>
        
        <?php
        // Check for update status in URL
        if (isset($_GET['update']) && $_GET['update'] === 'success') {
            echo '<div class="cms-emp-message success">✅ ' . esc_html($atts['success_message']) . '</div>';
        }
        
        if (isset($_GET['update']) && $_GET['update'] === 'error') {
            $error_msg = isset($_GET['error_msg']) ? urldecode($_GET['error_msg']) : 'Please try again.';
            echo '<div class="cms-emp-message error">❌ Update failed: ' . esc_html($error_msg) . '</div>';
        }
        ?>
        
        <!-- Debug Info (remove in production) -->
        <div class="cms-emp-debug-box">
            <strong>Debug Info:</strong> Employee ID: <?php echo $employee_id; ?><br>
            <strong>Table:</strong> <?php echo $table_employee; ?><br>
            <strong>Username:</strong> <?php echo $employee['username']; ?>
        </div>
        
        <div class="cms-emp-info-box">
            <strong>📝 Editing Employee:</strong> <?php echo esc_html($employee['name']); ?> 
            (ID: <?php echo esc_html($employee_id); ?> | Username: @<?php echo esc_html($employee['username']); ?>)
        </div>
        
        <form method="post" action="<?php echo esc_url($current_url); ?>" id="cms-emp-update-form" enctype="multipart/form-data">
            <?php wp_nonce_field('cms_employee_update', 'cms_emp_update_nonce'); ?>
            <input type="hidden" name="cms_employee_id" value="<?php echo esc_attr($employee_id); ?>">
            <input type="hidden" name="redirect_url" value="<?php echo esc_url($current_url); ?>">
            <input type="hidden" name="cms_emp_update_submit" value="1">
            
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
                        <small style="color: #718096; font-size: 11px;">Username cannot be changed</small>
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
                            value="<?php echo esc_attr($employee['cnic_no']); ?>"
                            readonly
                        >
                        <small style="color: #718096; font-size: 11px;">CNIC cannot be changed</small>
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
                            <option value="">Select Team</option>
                            <option value="smart-call" <?php selected($employee['corp_team'], 'smart-call'); ?>>Smart Call</option>
                            <option value="tele" <?php selected($employee['corp_team'], 'tele'); ?>>Tele</option>
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
                                value="<?php echo esc_attr($contact_code); ?>"
                                placeholder="+1"
                                required
                            >
                            <input 
                                type="tel" 
                                id="emp-contact" 
                                name="emp_contact" 
                                class="cms-emp-form-control" 
                                value="<?php echo esc_attr(preg_replace('/[^0-9]/', '', $contact_number)); ?>"
                                required
                                pattern="[0-9]{10,15}"
                                title="Please enter 10-15 digits"
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
                                value="<?php echo esc_attr($emergency_code); ?>"
                                placeholder="+1"
                                required
                            >
                            <input 
                                type="tel" 
                                id="emp-emergency" 
                                name="emp_emergency" 
                                class="cms-emp-form-control" 
                                value="<?php echo esc_attr(preg_replace('/[^0-9]/', '', $emergency_number)); ?>"
                                required
                                pattern="[0-9]{10,15}"
                                title="Please enter 10-15 digits"
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
                        <label for="emp-wage-type">Wage Type <span class="cms-emp-required">*</span></label>
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
                <div class="cms-emp-file-note">
                    ⚠️ Note: If both increment date and percentage are provided, the updated wage will be automatically calculated.
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
                            value="<?php echo esc_attr(preg_replace('/[^0-9]/', '', $employee['ref1_cno'])); ?>"
                            required
                            pattern="[0-9]{10,15}"
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
                            value="<?php echo esc_attr(preg_replace('/[^0-9]/', '', $employee['ref2_cno'])); ?>"
                            required
                            pattern="[0-9]{10,15}"
                        >
                    </div>
                </div>
            </div>
            
            <!-- Documents Section -->
            <div class="cms-emp-update-section">
                <h3 class="cms-emp-update-section-title">Documents & Certificate</h3>
                
                <div class="cms-emp-form-grid-3">
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
                        <label for="emp-status">Employment Status</label>
                        <select id="emp-status" name="emp_status" class="cms-emp-form-control">
                            <option value="active" <?php selected($employee['status'], 'active'); ?>>Active</option>
                            <option value="inactive" <?php selected($employee['status'], 'inactive'); ?>>Inactive</option>
                        </select>
                    </div>
                </div>
                
                <div class="cms-emp-form-grid" style="margin-top: 20px;">
                    <!-- CNIC PDF -->
                    <div class="cms-emp-form-group">
                        <label>CNIC PDF</label>
                        <?php if (!empty($employee['cnic_pdf'])): ?>
                        <div class="cms-emp-file-info">
                            <span style="font-size: 20px;">📄</span>
                            <div style="flex: 1;">
                                <div style="font-weight: 500; font-size: 13px;">Current File</div>
                                <div class="cms-emp-file-name">
                                    <?php echo basename($employee['cnic_pdf']); ?>
                                </div>
                            </div>
                            <div style="display: flex; gap: 5px;">
                                <a href="<?php echo esc_url($employee['cnic_pdf_url']); ?>" 
                                   target="_blank" 
                                   class="cms-emp-file-link view"
                                   onclick="return cmsOpenPDF('<?php echo esc_url($employee['cnic_pdf_url']); ?>')">
                                    👁️ View
                                </a>
                                <a href="<?php echo esc_url($employee['cnic_pdf_url']); ?>" 
                                   download 
                                   class="cms-emp-file-link download">
                                    ⬇️ Download
                                </a>
                            </div>
                        </div>
                        <div style="font-size: 12px; color: #e67e22; margin: 5px 0;">
                            ⚠️ Leave empty to keep current file
                        </div>
                        <?php else: ?>
                        <div style="background: #fef9f5; padding: 10px; border-radius: 8px; margin-bottom: 10px; font-size: 13px; color: #718096;">
                            No file uploaded yet
                        </div>
                        <?php endif; ?>
                        <input 
                            type="file" 
                            id="emp-cnic-pdf" 
                            name="emp_cnic_pdf" 
                            accept=".pdf,.PDF"
                        >
                        <small class="cms-emp-file-note">Upload new PDF only if you want to replace the current file (max 5MB)</small>
                    </div>
                    
                    <!-- Character Certificate PDF -->
                    <div class="cms-emp-form-group">
                        <label>Character Certificate PDF</label>
                        <?php if (!empty($employee['char_cert_pdf'])): ?>
                        <div class="cms-emp-file-info">
                            <span style="font-size: 20px;">📜</span>
                            <div style="flex: 1;">
                                <div style="font-weight: 500; font-size: 13px;">Current File</div>
                                <div class="cms-emp-file-name">
                                    <?php echo basename($employee['char_cert_pdf']); ?>
                                </div>
                            </div>
                            <div style="display: flex; gap: 5px;">
                                <a href="<?php echo esc_url($employee['char_cert_pdf_url']); ?>" 
                                   target="_blank" 
                                   class="cms-emp-file-link view"
                                   onclick="return cmsOpenPDF('<?php echo esc_url($employee['char_cert_pdf_url']); ?>')">
                                    👁️ View
                                </a>
                                <a href="<?php echo esc_url($employee['char_cert_pdf_url']); ?>" 
                                   download 
                                   class="cms-emp-file-link download">
                                    ⬇️ Download
                                </a>
                            </div>
                        </div>
                        <div style="font-size: 12px; color: #e67e22; margin: 5px 0;">
                            ⚠️ Leave empty to keep current file
                        </div>
                        <?php else: ?>
                        <div style="background: #fef9f5; padding: 10px; border-radius: 8px; margin-bottom: 10px; font-size: 13px; color: #718096;">
                            No file uploaded yet
                        </div>
                        <?php endif; ?>
                        <input 
                            type="file" 
                            id="emp-char-cert-pdf" 
                            name="emp_char_cert_pdf" 
                            accept=".pdf,.PDF"
                        >
                        <small class="cms-emp-file-note">Upload new PDF only if you want to replace the current file</small>
                    </div>
                    
                    <!-- Employment Letter PDF -->
                    <div class="cms-emp-form-group">
                        <label>Employment Letter PDF</label>
                        <?php if (!empty($employee['emp_letter_pdf'])): ?>
                        <div class="cms-emp-file-info">
                            <span style="font-size: 20px;">📋</span>
                            <div style="flex: 1;">
                                <div style="font-weight: 500; font-size: 13px;">Current File</div>
                                <div class="cms-emp-file-name">
                                    <?php echo basename($employee['emp_letter_pdf']); ?>
                                </div>
                            </div>
                            <div style="display: flex; gap: 5px;">
                                <a href="<?php echo esc_url($employee['emp_letter_pdf_url']); ?>" 
                                   target="_blank" 
                                   class="cms-emp-file-link view"
                                   onclick="return cmsOpenPDF('<?php echo esc_url($employee['emp_letter_pdf_url']); ?>')">
                                    👁️ View
                                </a>
                                <a href="<?php echo esc_url($employee['emp_letter_pdf_url']); ?>" 
                                   download 
                                   class="cms-emp-file-link download">
                                    ⬇️ Download
                                </a>
                            </div>
                        </div>
                        <div style="font-size: 12px; color: #e67e22; margin: 5px 0;">
                            ⚠️ Leave empty to keep current file
                        </div>
                        <?php else: ?>
                        <div style="background: #fef9f5; padding: 10px; border-radius: 8px; margin-bottom: 10px; font-size: 13px; color: #718096;">
                            No file uploaded yet
                        </div>
                        <?php endif; ?>
                        <input 
                            type="file" 
                            id="emp-letter-pdf" 
                            name="emp_letter_pdf" 
                            accept=".pdf,.PDF"
                        >
                        <small class="cms-emp-file-note">Upload new PDF only if you want to replace the current file</small>
                    </div>
                </div>
            </div>
            
            <!-- Progress Bar -->
            <div class="cms-emp-progress" id="update-progress">
                <div class="cms-emp-progress-bar" id="update-progress-bar"></div>
            </div>
            
            <!-- Form Actions -->
            <div class="cms-emp-update-footer">
                <a href="<?php echo esc_url(remove_query_arg('employee_id', wp_get_referer() ?: home_url('employee-list'))); ?>" class="cms-emp-cancel-button">
                    Cancel
                </a>
                <button type="submit" name="cms_emp_update_submit" class="cms-emp-update-button" id="emp-update-btn">
                    💾 <?php echo esc_html($atts['button_text']); ?>
                </button>
            </div>
        </form>
    </div>
    
    <script>
    function cmsOpenPDF(url) {
        if (!url || url === '' || url === '#') {
            alert('PDF file not found');
            return false;
        }
        
        if (url.startsWith('http') || url.startsWith('/')) {
            window.open(url, '_blank');
            return false;
        } else {
            alert('Invalid PDF file path');
            return false;
        }
    }
    
    jQuery(document).ready(function($) {
        // Form validation
        $('#cms-emp-update-form').on('submit', function(e) {
            var isValid = true;
            
            // Check required fields
            $(this).find('[required]').each(function() {
                if (!$(this).val()) {
                    $(this).addClass('error');
                    isValid = false;
                } else {
                    $(this).removeClass('error');
                }
            });
            
            // Email validation
            var email = $('#emp-email');
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email.val() && !emailPattern.test(email.val())) {
                email.addClass('error');
                isValid = false;
            }
            
            // Phone number validation
            var contact = $('#emp-contact');
            var contactPattern = /^[0-9]{10,15}$/;
            if (contact.val() && !contactPattern.test(contact.val())) {
                contact.addClass('error');
                isValid = false;
            }
            
            // Date validation
            var joiningDate = $('#emp-joining-date').val();
            var terminationDate = $('#emp-termination-date').val();
            if (terminationDate && joiningDate && terminationDate < joiningDate) {
                alert('Termination date cannot be before joining date');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill all required fields correctly.');
                return false;
            }
            
            // Show progress bar
            $('#update-progress').show();
            var progress = 0;
            var interval = setInterval(function() {
                progress += 10;
                $('#update-progress-bar').css('width', progress + '%');
                if (progress >= 100) {
                    clearInterval(interval);
                }
            }, 200);
            
            $('#emp-update-btn').text('Updating...').prop('disabled', true);
        });
        
        // Remove error class on input
        $('.cms-emp-form-control').on('input', function() {
            $(this).removeClass('error');
        });
        
        // File size validation
        $('input[type="file"]').on('change', function() {
            var file = this.files[0];
            if (file && file.size > 5 * 1048576) { // 5MB
                alert('File size must be less than 5MB');
                $(this).val('');
            }
        });
        
        // Auto-calculate updated wage
        $('#emp-basic-wage, #emp-increment-percentage').on('input', function() {
            var basicWage = parseFloat($('#emp-basic-wage').val()) || 0;
            var incrementPercent = parseFloat($('#emp-increment-percentage').val()) || 0;
            
            if (basicWage > 0 && incrementPercent > 0) {
                var updatedWage = basicWage + (basicWage * incrementPercent / 100);
                // Optional: Display calculated wage
            }
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_update_employee', 'cms_update_employee_shortcode');
add_shortcode(CMS_EMPLOYEE_UPDATE_SHORTCODE, 'cms_update_employee_shortcode');

/**
 * Handle file uploads during update
 */
function cms_handle_update_file_uploads($username) {
    $upload_dir = wp_upload_dir();
    $cms_upload_dir = $upload_dir['basedir'] . '/cms-employee-docs/';
    
    // Create directory if not exists
    if (!file_exists($cms_upload_dir)) {
        wp_mkdir_p($cms_upload_dir);
        
        // Add .htaccess to protect directory (but allow PDF access)
        $htaccess_content = "Order Deny,Allow\nDeny from all\n<FilesMatch '\\.pdf$'>\nAllow from all\n</FilesMatch>";
        @file_put_contents($cms_upload_dir . '.htaccess', $htaccess_content);
        
        // Add index.php for security
        @file_put_contents($cms_upload_dir . 'index.php', '<?php // Silence is golden');
    }
    
    $uploaded_files = [];
    $file_fields = [
        'emp_cnic_pdf' => 'cnic_pdf',
        'emp_char_cert_pdf' => 'char_cert_pdf',
        'emp_letter_pdf' => 'emp_letter_pdf'
    ];
    
    foreach ($file_fields as $field => $db_field) {
        if (isset($_FILES[$field]) && $_FILES[$field]['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES[$field];
            
            // Validate file type
            $file_type = wp_check_filetype($file['name']);
            if ($file_type['ext'] !== 'pdf') {
                continue; // Skip invalid files
            }
            
            // Validate file size (5MB max)
            if ($file['size'] > 5 * 1048576) {
                continue; // Skip oversized files
            }
            
            // Generate unique filename with username prefix
            $file_info = pathinfo($file['name']);
            $filename = $username . '_' . $db_field . '_' . uniqid() . '.pdf';
            $filepath = $cms_upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                // Store the full server path
                $uploaded_files[$db_field] = $filepath;
            }
        }
    }
    
    return $uploaded_files;
}

