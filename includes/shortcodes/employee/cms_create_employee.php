<?php
/**
 * CMS Employee Form Shortcode with Complete Backend Functionality
 * Complete form for Employee registration with all required fields
 * 
 * Usage: [cms_employee_form]
 * Usage: [cms_employee_form title="Register Employee" button_text="Submit Application"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define shortcode slug
if (!defined('CMS_EMPLOYEE_CREATE_SHORTCODE')) {
    define('CMS_EMPLOYEE_CREATE_SHORTCODE', 'cms_employee_create');
}

/**
 * Employee Form Shortcode
 */
function cms_employee_form_shortcode($atts) {
    // Parse attributes
    $atts = shortcode_atts(
        array(
            'title' => 'Employee Registration',
            'description' => 'Please fill in all the details below to register as Employee.',
            'button_text' => 'Register Employee',
            'success_message' => 'Employee registered successfully!',
            'class' => '',
            'show_labels' => 'yes',
            'required_field_mark' => '*',
            'max_file_size' => '5', // MB
            'allowed_file_types' => '.pdf,.PDF'
        ),
        $atts,
        'cms_employee_form'
    );
    
    $max_size = intval($atts['max_file_size']) * 1048576; // Convert to bytes
    
    ob_start();
    ?>
    
    <style>
    /* Employee Form Styles - Orange/Brown Theme */
    :root {
        --emp-primary: #e67e22;
        --emp-primary-dark: #d35400;
        --emp-primary-light: #f39c12;
        --emp-secondary: #7f8c8d;
        --emp-success: #27ae60;
        --emp-danger: #e74c3c;
        --emp-warning: #f39c12;
        --emp-info: #3498db;
    }
    
    .cms-emp-form-container {
        max-width: 1000px;
        margin: 30px auto;
        padding: 40px;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(230,126,34,0.08);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        border-top: 5px solid var(--emp-primary);
    }
    
    .cms-emp-header {
        margin-bottom: 35px;
        text-align: center;
    }
    
    .cms-emp-title {
        margin: 0 0 10px 0;
        font-size: 32px;
        font-weight: 700;
        color: var(--emp-primary-dark);
        letter-spacing: -0.5px;
    }
    
    .cms-emp-description {
        margin: 0;
        font-size: 15px;
        color: #6c7a89;
        line-height: 1.6;
    }
    
    .cms-emp-section {
        background: #fef9f5;
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 30px;
        border: 1px solid #ffe6d5;
        position: relative;
    }
    
    .cms-emp-section-title {
        margin: 0 0 20px 0;
        font-size: 20px;
        font-weight: 600;
        color: var(--emp-primary-dark);
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 15px;
        border-bottom: 2px solid #ffe6d5;
    }
    
    .cms-emp-section-title:before {
        content: '';
        width: 4px;
        height: 20px;
        background: var(--emp-primary);
        border-radius: 2px;
        display: inline-block;
    }
    
    .cms-emp-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .cms-emp-grid-3 {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
    }
    
    .cms-emp-group {
        margin-bottom: 5px;
    }
    
    .cms-emp-group.full-width {
        grid-column: span 2;
    }
    
    .cms-emp-group.full-width-3 {
        grid-column: span 3;
    }
    
    .cms-emp-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #34495e;
        font-size: 14px;
    }
    
    .cms-emp-required {
        color: var(--emp-danger);
        margin-left: 4px;
    }
    
    .cms-emp-control {
        width: 100%;
        padding: 14px 18px;
        font-size: 15px;
        border: 2px solid #ffe6d5;
        border-radius: 12px;
        transition: all 0.25s ease;
        box-sizing: border-box;
        background: #ffffff;
    }
    
    .cms-emp-control:focus {
        outline: none;
        border-color: var(--emp-primary);
        box-shadow: 0 0 0 4px rgba(230,126,34,0.05);
        background: #ffffff;
    }
    
    .cms-emp-control::placeholder {
        color: #a0b3c2;
        font-size: 14px;
    }
    
    .cms-emp-control.error {
        border-color: var(--emp-danger);
    }
    
    .cms-emp-error-text {
        color: var(--emp-danger);
        font-size: 12px;
        margin-top: 6px;
        display: block;
    }
    
    .cms-emp-phone-input {
        display: flex;
        gap: 10px;
    }
    
    .cms-emp-country-code {
        width: 100px;
        flex-shrink: 0;
    }
    
    .cms-emp-select {
        width: 100%;
        padding: 14px 18px;
        font-size: 15px;
        border: 2px solid #ffe6d5;
        border-radius: 12px;
        background: #ffffff;
        color: #34495e;
    }
    
    .cms-emp-wage-group {
        display: flex;
        gap: 15px;
        align-items: center;
    }
    
    .cms-emp-wage-type {
        flex: 1;
    }
    
    .cms-emp-amount {
        flex: 2;
    }
    
    .cms-emp-file-upload {
        border: 2px dashed #ffe6d5;
        border-radius: 12px;
        padding: 20px;
        text-align: center;
        background: #fef9f5;
        transition: all 0.25s ease;
        position: relative;
    }
    
    .cms-emp-file-upload:hover {
        border-color: var(--emp-primary);
        background: #fff4ed;
    }
    
    .cms-emp-file-input {
        position: absolute;
        width: 100%;
        height: 100%;
        top: 0;
        left: 0;
        opacity: 0;
        cursor: pointer;
    }
    
    .cms-emp-file-label {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 10px;
        color: #7f8c8d;
    }
    
    .cms-emp-file-label i {
        font-size: 32px;
        color: var(--emp-primary);
    }
    
    .cms-emp-file-name {
        margin-top: 10px;
        font-size: 13px;
        color: var(--emp-primary-dark);
        font-weight: 500;
    }
    
    .cms-emp-ref-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-top: 15px;
    }
    
    .cms-emp-ref-item {
        background: #ffffff;
        padding: 20px;
        border-radius: 12px;
        border: 1px solid #ffe6d5;
    }
    
    .cms-emp-ref-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--emp-primary-dark);
        margin: 0 0 15px 0;
        padding-bottom: 10px;
        border-bottom: 1px dashed #ffe6d5;
    }
    
    .cms-emp-submit-section {
        text-align: center;
        margin-top: 20px;
    }
    
    .cms-emp-submit-button {
        min-width: 250px;
        padding: 16px 32px;
        background: linear-gradient(145deg, var(--emp-primary), var(--emp-primary-dark));
        color: white;
        border: none;
        border-radius: 40px;
        font-size: 17px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(230,126,34,0.2);
    }
    
    .cms-emp-submit-button:hover {
        background: linear-gradient(145deg, var(--emp-primary-dark), #a04000);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(230,126,34,0.3);
    }
    
    .cms-emp-submit-button:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    
    .cms-emp-message {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 30px;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 12px;
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
    
    .cms-emp-message.warning {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
    
    .cms-emp-info-box {
        background: #fff4ed;
        border-left: 4px solid var(--emp-primary);
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
        margin-top: 20px;
        overflow: hidden;
    }
    
    .cms-emp-progress-bar {
        height: 100%;
        background: var(--emp-primary);
        width: 0%;
        transition: width 0.3s ease;
    }
    
    /* Date picker styling */
    .cms-emp-date-input {
        position: relative;
    }
    
    .cms-emp-date-input:after {
        content: '📅';
        position: absolute;
        right: 15px;
        top: 50%;
        transform: translateY(-50%);
        pointer-events: none;
    }
    
    .cms-emp-password-strength {
        margin-top: 5px;
        height: 4px;
        border-radius: 2px;
        background: #eee;
    }
    
    .cms-emp-password-strength-bar {
        height: 100%;
        width: 0;
        border-radius: 2px;
        transition: all 0.3s ease;
    }
    
    .cms-emp-password-strength-bar.weak { width: 25%; background: #e74c3c; }
    .cms-emp-password-strength-bar.medium { width: 50%; background: #f39c12; }
    .cms-emp-password-strength-bar.strong { width: 75%; background: #3498db; }
    .cms-emp-password-strength-bar.very-strong { width: 100%; background: #27ae60; }
    
    .cms-emp-password-hint {
        font-size: 12px;
        color: #7f8c8d;
        margin-top: 5px;
    }
    
    @media (max-width: 768px) {
        .cms-emp-form-container {
            padding: 25px;
            margin: 20px 15px;
        }
        
        .cms-emp-grid,
        .cms-emp-grid-3 {
            grid-template-columns: 1fr;
        }
        
        .cms-emp-group.full-width,
        .cms-emp-group.full-width-3 {
            grid-column: span 1;
        }
        
        .cms-emp-ref-grid {
            grid-template-columns: 1fr;
        }
        
        .cms-emp-title {
            font-size: 26px;
        }
        
        .cms-emp-wage-group {
            flex-direction: column;
        }
        
        .cms-emp-wage-type,
        .cms-emp-amount {
            width: 100%;
        }
    }
    </style>
    
    <div class="cms-emp-form-container <?php echo esc_attr($atts['class']); ?>" data-max-size="<?php echo esc_attr($max_size); ?>">
        
        <div class="cms-emp-header">
            <h2 class="cms-emp-title"><?php echo esc_html($atts['title']); ?></h2>
            <?php if (!empty($atts['description'])): ?>
                <p class="cms-emp-description"><?php echo esc_html($atts['description']); ?></p>
            <?php endif; ?>
        </div>
        
        <?php
        // Display messages
        if (isset($_GET['emp_reg'])) {
            $message = '';
            $type = 'error';
            
            switch ($_GET['emp_reg']) {
                case 'success':
                    $message = $atts['success_message'];
                    $type = 'success';
                    break;
                case 'username_exists':
                    $message = 'Username already exists. Please choose a different username.';
                    break;
                case 'email_exists':
                    $message = 'Email already exists. Please use a different email address.';
                    break;
                case 'cnic_exists':
                    $message = 'CNIC already exists. Employee with this CNIC is already registered.';
                    break;
                case 'validation_error':
                    $message = 'Please fill all required fields correctly.';
                    break;
                case 'file_error':
                    $message = 'File upload failed. Please check file size and type.';
                    break;
                case 'db_error':
                    $message = 'Database error occurred. Please try again or contact administrator.';
                    break;
                case 'password_mismatch':
                    $message = 'Passwords do not match.';
                    break;
                case 'weak_password':
                    $message = 'Please choose a stronger password.';
                    break;
                default:
                    $message = 'Registration failed. Please try again.';
            }
            
            if ($message) {
                echo '<div class="cms-emp-message ' . esc_attr($type) . '">' . esc_html($message) . '</div>';
            }
        }
        ?>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cms-emp-form" enctype="multipart/form-data">
            <?php wp_nonce_field('cms_employee_registration', 'cms_emp_nonce'); ?>
            <input type="hidden" name="action" value="cms_handle_employee_registration">
            <input type="hidden" name="redirect_url" value="<?php echo esc_url(get_permalink()); ?>">
            
            <!-- Personal Information Section -->
            <div class="cms-emp-section">
                <h3 class="cms-emp-section-title">Personal Information</h3>
                
                <div class="cms-emp-grid">
                    <div class="cms-emp-group">
                        <label for="emp-username">
                            Username <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="text" 
                            id="emp-username" 
                            name="emp_username" 
                            class="cms-emp-control" 
                            placeholder="Enter username"
                            required
                            autocomplete="off"
                            pattern="[a-zA-Z0-9_]{3,20}"
                            title="Username must be 3-20 characters, can contain letters, numbers and underscores"
                        >
                    </div>
                    
                    <div class="cms-emp-group">
                        <label for="emp-fullname">
                            Full Name <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="text" 
                            id="emp-fullname" 
                            name="emp_fullname" 
                            class="cms-emp-control" 
                            placeholder="Enter full name"
                            required
                        >
                    </div>
                    
                    <div class="cms-emp-group">
                        <label for="emp-email">
                            Email Address <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="email" 
                            id="emp-email" 
                            name="emp_email" 
                            class="cms-emp-control" 
                            placeholder="Enter email address"
                            required
                            autocomplete="email"
                        >
                    </div>
                    
                    <div class="cms-emp-group">
                        <label for="emp-fathername">
                            Father's Name <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="text" 
                            id="emp-fathername" 
                            name="emp_fathername" 
                            class="cms-emp-control" 
                            placeholder="Enter father's name"
                            required
                        >
                    </div>
                    
                    <div class="cms-emp-group">
                        <label for="emp-cnic">
                            CNIC Number <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="text" 
                            id="emp-cnic" 
                            name="emp_cnic" 
                            class="cms-emp-control" 
                            placeholder="XXXXX-XXXXXXX-X"
                            required
                            pattern="[0-9]{5}-[0-9]{7}-[0-9]{1}"
                            title="Please enter valid CNIC format: XXXXX-XXXXXXX-X"
                        >
                    </div>
                    
                    <div class="cms-emp-group">
                        <label for="emp-position">
                            Position <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="text" 
                            id="emp-position" 
                            name="emp_position" 
                            class="cms-emp-control" 
                            placeholder="e.g., Software Engineer, Manager"
                            required
                        >
                    </div>
                    
                    <div class="cms-emp-group">
                        <label for="emp-corp-team">
                            Corporate Team <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <select id="emp-corp-team" name="emp_corp_team" class="cms-emp-select" required>
                            <option value="">Select Team</option>
                            <option value="smart-call">Smart Call Solutions</option>
                            <option value="tele-central">TeleCentral</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Password Section -->
            <div class="cms-emp-section">
                <h3 class="cms-emp-section-title">Account Security</h3>
                
                <div class="cms-emp-grid">
                    <div class="cms-emp-group">
                        <label for="emp-password">
                            Password <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="password" 
                            id="emp-password" 
                            name="emp_password" 
                            class="cms-emp-control" 
                            placeholder="Enter password"
                            required
                            minlength="8"
                        >
                        <div class="cms-emp-password-strength">
                            <div class="cms-emp-password-strength-bar" id="password-strength-bar"></div>
                        </div>
                        <div class="cms-emp-password-hint" id="password-hint">
                            Password must be at least 8 characters with letters and numbers
                        </div>
                    </div>
                    
                    <div class="cms-emp-group">
                        <label for="emp-confirm-password">
                            Confirm Password <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="password" 
                            id="emp-confirm-password" 
                            name="emp_confirm_password" 
                            class="cms-emp-control" 
                            placeholder="Confirm password"
                            required
                        >
                    </div>
                </div>
            </div>
            
            <!-- Contact Information Section -->
            <div class="cms-emp-section">
                <h3 class="cms-emp-section-title">Contact Information</h3>
                
                <div class="cms-emp-grid">
                    <div class="cms-emp-group">
                        <label for="emp-contact">
                            Contact Number <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <div class="cms-emp-phone-input">
                            <select name="emp_country_code" class="cms-emp-control cms-emp-country-code">
                                <option value="+1">+1 (USA)</option>
                                <option value="+44">+44 (UK)</option>
                                <option value="+91">+91 (India)</option>
                                <option value="+92">+92 (Pakistan)</option>
                                <option value="+971">+971 (UAE)</option>
                                <option value="+966">+966 (Saudi)</option>
                                <option value="+20">+20 (Egypt)</option>
                                <option value="+other">Other</option>
                            </select>
                            <input 
                                type="tel" 
                                id="emp-contact" 
                                name="emp_contact" 
                                class="cms-emp-control" 
                                placeholder="Phone number"
                                required
                                pattern="[0-9]{10,15}"
                                title="Please enter a valid phone number"
                            >
                        </div>
                    </div>
                    
                    <div class="cms-emp-group">
                        <label for="emp-emergency">
                            Emergency Contact Number <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <div class="cms-emp-phone-input">
                            <select name="emp_emergency_code" class="cms-emp-control cms-emp-country-code">
                                <option value="+1">+1</option>
                                <option value="+44">+44</option>
                                <option value="+91">+91</option>
                                <option value="+92">+92</option>
                                <option value="+971">+971</option>
                            </select>
                            <input 
                                type="tel" 
                                id="emp-emergency" 
                                name="emp_emergency" 
                                class="cms-emp-control" 
                                placeholder="Emergency contact number"
                                required
                                pattern="[0-9]{10,15}"
                            >
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Employment Details Section -->
            <div class="cms-emp-section">
                <h3 class="cms-emp-section-title">Employment Details</h3>
                
                <div class="cms-emp-grid-3">
                    <div class="cms-emp-group">
                        <label for="emp-joining-date">
                            Joining Date <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <div class="cms-emp-date-input">
                            <input 
                                type="date" 
                                id="emp-joining-date" 
                                name="emp_joining_date" 
                                class="cms-emp-control" 
                                required
                                max="<?php echo date('Y-m-d'); ?>"
                            >
                        </div>
                    </div>
                    
                    <div class="cms-emp-group">
                        <label for="emp-wage-type">
                            Wage Type <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <select id="emp-wage-type" name="emp_wage_type" class="cms-emp-select" required>
                            <option value="">Select Type</option>
                            <option value="hourly">Hourly</option>
                            <option value="monthly">Monthly</option>
                        </select>
                    </div>
                    
                    <div class="cms-emp-group">
                        <label for="emp-basic-wage">
                            Basic Wage/Amount <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <div class="cms-emp-wage-group">
                            <input 
                                type="number" 
                                id="emp-basic-wage" 
                                name="emp_basic_wage" 
                                class="cms-emp-control" 
                                placeholder="Amount"
                                required
                                min="0"
                                step="0.01"
                            >
                        </div>
                    </div>
                    
                    <div class="cms-emp-group">
                        <label for="emp-increment-date">
                            Increment Date
                        </label>
                        <div class="cms-emp-date-input">
                            <input 
                                type="date" 
                                id="emp-increment-date" 
                                name="emp_increment_date" 
                                class="cms-emp-control"
                            >
                        </div>
                    </div>
                    
                    <div class="cms-emp-group">
                        <label for="emp-increment-percentage">
                            Increment Percentage (%)
                        </label>
                        <input 
                            type="number" 
                            id="emp-increment-percentage" 
                            name="emp_increment_percentage" 
                            class="cms-emp-control" 
                            placeholder="e.g., 10"
                            min="0"
                            max="100"
                            step="0.1"
                        >
                    </div>
                    
                    <div class="cms-emp-group">
                        <label for="emp-termination-date">
                            Termination Date
                        </label>
                        <div class="cms-emp-date-input">
                            <input 
                                type="date" 
                                id="emp-termination-date" 
                                name="emp_termination_date" 
                                class="cms-emp-control"
                            >
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reference Information Section -->
            <div class="cms-emp-section">
                <h3 class="cms-emp-section-title">Reference Information</h3>
                
                <div class="cms-emp-ref-grid">
                    <div class="cms-emp-ref-item">
                        <h4 class="cms-emp-ref-title">Reference #1</h4>
                        <div class="cms-emp-group">
                            <label for="emp-ref1-name">
                                Reference Name <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                            </label>
                            <input 
                                type="text" 
                                id="emp-ref1-name" 
                                name="emp_ref1_name" 
                                class="cms-emp-control" 
                                placeholder="Enter reference name"
                                required
                            >
                        </div>
                        <div class="cms-emp-group">
                            <label for="emp-ref1-cno">
                                Reference Contact <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                            </label>
                            <input 
                                type="tel" 
                                id="emp-ref1-cno" 
                                name="emp_ref1_cno" 
                                class="cms-emp-control" 
                                placeholder="Enter contact number"
                                required
                                pattern="[0-9]{10,15}"
                            >
                        </div>
                    </div>
                    
                    <div class="cms-emp-ref-item">
                        <h4 class="cms-emp-ref-title">Reference #2</h4>
                        <div class="cms-emp-group">
                            <label for="emp-ref2-name">
                                Reference Name <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                            </label>
                            <input 
                                type="text" 
                                id="emp-ref2-name" 
                                name="emp_ref2_name" 
                                class="cms-emp-control" 
                                placeholder="Enter reference name"
                                required
                            >
                        </div>
                        <div class="cms-emp-group">
                            <label for="emp-ref2-cno">
                                Reference Contact <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                            </label>
                            <input 
                                type="tel" 
                                id="emp-ref2-cno" 
                                name="emp_ref2_cno" 
                                class="cms-emp-control" 
                                placeholder="Enter contact number"
                                required
                                pattern="[0-9]{10,15}"
                            >
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Documents Section -->
            <div class="cms-emp-section">
                <h3 class="cms-emp-section-title">📄 Documents Upload</h3>
                
                <div class="cms-emp-grid-3">
                    <!-- CNIC PDF -->
                    <div class="cms-emp-group">
                        <label>
                            CNIC Copy (PDF) <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <div class="cms-emp-file-upload">
                            <input 
                                type="file" 
                                id="emp-cnic-pdf" 
                                name="emp_cnic_pdf" 
                                class="cms-emp-file-input" 
                                accept="<?php echo esc_attr($atts['allowed_file_types']); ?>"
                                data-max-size="<?php echo esc_attr($max_size); ?>"
                                required
                            >
                            <div class="cms-emp-file-label">
                                <span style="font-size: 32px;">📄</span>
                                <span>Click or drag PDF file</span>
                                <span style="font-size: 12px;">Max size: <?php echo esc_html($atts['max_file_size']); ?>MB</span>
                            </div>
                            <div class="cms-emp-file-name" id="cnic-file-name"></div>
                        </div>
                    </div>
                    
                    <!-- Character Certificate -->
                    <div class="cms-emp-group">
                        <label>
                            Character Certificate PDF <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <div class="cms-emp-file-upload">
                            <input 
                                type="file" 
                                id="emp-char-cert-pdf" 
                                name="emp_char_cert_pdf" 
                                class="cms-emp-file-input" 
                                accept="<?php echo esc_attr($atts['allowed_file_types']); ?>"
                                data-max-size="<?php echo esc_attr($max_size); ?>"
                                required
                            >
                            <div class="cms-emp-file-label">
                                <span style="font-size: 32px;">📜</span>
                                <span>Click or drag PDF file</span>
                                <span style="font-size: 12px;">Max size: <?php echo esc_html($atts['max_file_size']); ?>MB</span>
                            </div>
                            <div class="cms-emp-file-name" id="char-cert-file-name"></div>
                        </div>
                    </div>
                    
                    <!-- Employment Letter -->
                    <div class="cms-emp-group">
                        <label>
                            Employment Letter PDF <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <div class="cms-emp-file-upload">
                            <input 
                                type="file" 
                                id="emp-letter-pdf" 
                                name="emp_letter_pdf" 
                                class="cms-emp-file-input" 
                                accept="<?php echo esc_attr($atts['allowed_file_types']); ?>"
                                data-max-size="<?php echo esc_attr($max_size); ?>"
                                required
                            >
                            <div class="cms-emp-file-label">
                                <span style="font-size: 32px;">📋</span>
                                <span>Click or drag PDF file</span>
                                <span style="font-size: 12px;">Max size: <?php echo esc_html($atts['max_file_size']); ?>MB</span>
                            </div>
                            <div class="cms-emp-file-name" id="letter-file-name"></div>
                        </div>
                    </div>
                </div>
                
                <div class="cms-emp-grid" style="margin-top: 20px;">
                    <div class="cms-emp-group">
                        <label for="emp-char-cert-no">
                            Character Certificate Number <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="text" 
                            id="emp-char-cert-no" 
                            name="emp_char_cert_no" 
                            class="cms-emp-control" 
                            placeholder="Enter certificate number"
                            required
                        >
                    </div>
                    
                    <div class="cms-emp-group">
                        <label for="emp-char-cert-exp">
                            Character Certificate Expiry <?php if($atts['required_field_mark']) echo '<span class="cms-emp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <div class="cms-emp-date-input">
                            <input 
                                type="date" 
                                id="emp-char-cert-exp" 
                                name="emp_char_cert_exp" 
                                class="cms-emp-control" 
                                required
                            >
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="cms-emp-submit-section">
                <div class="cms-emp-progress" style="margin-bottom: 20px; display: none;" id="upload-progress">
                    <div class="cms-emp-progress-bar" id="upload-progress-bar"></div>
                </div>
                <button type="submit" name="emp_submit" class="cms-emp-submit-button" id="emp-submit-btn">
                    👤 <?php echo esc_html($atts['button_text']); ?>
                </button>
            </div>
            
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // File upload preview
        $('.cms-emp-file-input').on('change', function() {
            var fileName = this.files[0] ? this.files[0].name : 'No file selected';
            var fileSize = this.files[0] ? this.files[0].size : 0;
            var maxSize = $(this).data('max-size');
            var fileId = $(this).attr('id');
            
            if (fileId === 'emp-cnic-pdf') {
                $('#cnic-file-name').text(fileName);
            } else if (fileId === 'emp-char-cert-pdf') {
                $('#char-cert-file-name').text(fileName);
            } else if (fileId === 'emp-letter-pdf') {
                $('#letter-file-name').text(fileName);
            }
            
            if (fileSize > maxSize) {
                alert('File size exceeds maximum limit of ' + (maxSize/1048576) + 'MB');
                $(this).val('');
                if (fileId === 'emp-cnic-pdf') {
                    $('#cnic-file-name').text('');
                } else if (fileId === 'emp-char-cert-pdf') {
                    $('#char-cert-file-name').text('');
                } else if (fileId === 'emp-letter-pdf') {
                    $('#letter-file-name').text('');
                }
            }
        });
        
        // CNIC format auto-formatting
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
        
        // Password strength checker
        $('#emp-password').on('keyup', function() {
            var password = $(this).val();
            var strengthBar = $('#password-strength-bar');
            var strength = 0;
            
            if (password.length >= 8) strength += 1;
            if (password.match(/[a-z]+/)) strength += 1;
            if (password.match(/[A-Z]+/)) strength += 1;
            if (password.match(/[0-9]+/)) strength += 1;
            if (password.match(/[$@#&!]+/)) strength += 1;
            
            strengthBar.removeClass('weak medium strong very-strong');
            
            if (strength <= 2) {
                strengthBar.addClass('weak');
                $('#password-hint').text('Weak password - add more complexity');
            } else if (strength == 3) {
                strengthBar.addClass('medium');
                $('#password-hint').text('Medium password - getting better');
            } else if (strength == 4) {
                strengthBar.addClass('strong');
                $('#password-hint').text('Strong password');
            } else if (strength >= 5) {
                strengthBar.addClass('very-strong');
                $('#password-hint').text('Very strong password');
            }
        });
        
        // Password match check
        $('#emp-confirm-password').on('keyup', function() {
            var password = $('#emp-password').val();
            var confirm = $(this).val();
            
            if (password !== confirm) {
                $(this).addClass('error');
                $('#password-hint').text('Passwords do not match');
            } else {
                $(this).removeClass('error');
            }
        });
        
        // Form validation
        $('.cms-emp-form').on('submit', function(e) {
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
            
            // Username validation
            var username = $('#emp-username');
            var usernamePattern = /^[a-zA-Z0-9_]{3,20}$/;
            if (username.val() && !usernamePattern.test(username.val())) {
                username.addClass('error');
                isValid = false;
            }
            
            // CNIC validation
            var cnic = $('#emp-cnic');
            var cnicPattern = /^[0-9]{5}-[0-9]{7}-[0-9]{1}$/;
            if (cnic.val() && !cnicPattern.test(cnic.val())) {
                cnic.addClass('error');
                alert('Please enter CNIC in format: XXXXX-XXXXXXX-X');
                isValid = false;
            }
            
            // Password match
            var password = $('#emp-password').val();
            var confirmPassword = $('#emp-confirm-password').val();
            if (password !== confirmPassword) {
                $('#emp-confirm-password').addClass('error');
                alert('Passwords do not match');
                isValid = false;
            }
            
            // Password strength
            if (password.length < 8) {
                $('#emp-password').addClass('error');
                alert('Password must be at least 8 characters long');
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
                return false;
            }
            
            // Show progress bar
            $('#upload-progress').show();
            var progress = 0;
            var interval = setInterval(function() {
                progress += 10;
                $('#upload-progress-bar').css('width', progress + '%');
                if (progress >= 100) {
                    clearInterval(interval);
                }
            }, 200);
            
            $('#emp-submit-btn').addClass('loading').prop('disabled', true);
        });
        
        // Remove error class on input
        $('.cms-emp-control').on('input', function() {
            $(this).removeClass('error');
        });
        
        // Country code toggle
        $('select[name="emp_country_code"]').on('change', function() {
            if ($(this).val() === '+other') {
                var customCode = prompt('Enter country code (e.g., +1, +92):');
                if (customCode) {
                    if (!customCode.startsWith('+')) {
                        customCode = '+' + customCode;
                    }
                    $(this).append('<option value="' + customCode + '" selected>' + customCode + '</option>');
                }
            }
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_employee_form', 'cms_employee_form_shortcode');
add_shortcode(CMS_EMPLOYEE_CREATE_SHORTCODE, 'cms_employee_form_shortcode');

/**
 * Handle Employee Form Submission via admin-post.php
 */
function cms_handle_employee_registration() {
    global $wpdb;
    
    // Verify nonce
    if (!isset($_POST['cms_emp_nonce']) || !wp_verify_nonce($_POST['cms_emp_nonce'], 'cms_employee_registration')) {
        wp_redirect(add_query_arg('emp_reg', 'security_error', wp_get_referer()));
        exit;
    }
    
    $redirect_url = isset($_POST['redirect_url']) ? esc_url_raw($_POST['redirect_url']) : home_url();
    
    // Validate required fields
    $required_fields = [
        'emp_username', 'emp_fullname', 'emp_email', 'emp_fathername',
        'emp_cnic', 'emp_position', 'emp_corp_team', 'emp_country_code',
        'emp_contact', 'emp_emergency_code', 'emp_emergency', 'emp_joining_date',
        'emp_wage_type', 'emp_basic_wage', 'emp_ref1_name', 'emp_ref1_cno',
        'emp_ref2_name', 'emp_ref2_cno', 'emp_char_cert_no', 'emp_char_cert_exp',
        'emp_password', 'emp_confirm_password'
    ];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            wp_redirect(add_query_arg('emp_reg', 'validation_error', $redirect_url));
            exit;
        }
    }
    
    // Validate password match
    if ($_POST['emp_password'] !== $_POST['emp_confirm_password']) {
        wp_redirect(add_query_arg('emp_reg', 'password_mismatch', $redirect_url));
        exit;
    }
    
    // Validate password strength
    $password = $_POST['emp_password'];
    if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        wp_redirect(add_query_arg('emp_reg', 'weak_password', $redirect_url));
        exit;
    }
    
    // Check if username exists
    $table_users = $wpdb->prefix . 'cms_users';
    $username_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_users WHERE username = %s",
        sanitize_user($_POST['emp_username'])
    ));
    
    if ($username_exists > 0) {
        wp_redirect(add_query_arg('emp_reg', 'username_exists', $redirect_url));
        exit;
    }
    
    // Check if email exists
    $table_employee = $wpdb->prefix . 'cms_employee';
    $email_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_employee WHERE email = %s",
        sanitize_email($_POST['emp_email'])
    ));
    
    if ($email_exists > 0) {
        wp_redirect(add_query_arg('emp_reg', 'email_exists', $redirect_url));
        exit;
    }
    
    // Check if CNIC exists
    $cnic_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_employee WHERE cnic_no = %s",
        sanitize_text_field($_POST['emp_cnic'])
    ));
    
    if ($cnic_exists > 0) {
        wp_redirect(add_query_arg('emp_reg', 'cnic_exists', $redirect_url));
        exit;
    }
    
    // Handle file uploads
    $uploaded_files = cms_handle_employee_file_uploads();
    if ($uploaded_files === false) {
        wp_redirect(add_query_arg('emp_reg', 'file_error', $redirect_url));
        exit;
    }
    
    // Start transaction
    $wpdb->query('START TRANSACTION');
    
    try {
        // Insert into users table
        $user_inserted = $wpdb->insert(
            $table_users,
            [
                'username' => sanitize_user($_POST['emp_username']),
                'password' => wp_hash_password($_POST['emp_password']),
                'role' => 'employee',
                'status' => 'active',
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%s']
        );
        
        if (!$user_inserted) {
            throw new Exception('Failed to create user account');
        }
        
        // Prepare contact numbers
        $contact = preg_replace('/[^0-9]/', '', $_POST['emp_contact']);
        $emergency = preg_replace('/[^0-9]/', '', $_POST['emp_emergency']);
        $ref1_cno = preg_replace('/[^0-9]/', '', $_POST['emp_ref1_cno']);
        $ref2_cno = preg_replace('/[^0-9]/', '', $_POST['emp_ref2_cno']);
        
        // Calculate updated wage if increment exists
        $updated_wage = null;
        if (!empty($_POST['emp_increment_percentage']) && !empty($_POST['emp_basic_wage'])) {
            $increment_percentage = floatval($_POST['emp_increment_percentage']);
            $basic_wage = floatval($_POST['emp_basic_wage']);
            $updated_wage = $basic_wage + ($basic_wage * $increment_percentage / 100);
        }
        
        // Insert into employee table
        $employee_inserted = $wpdb->insert(
            $table_employee,
            [
                'username' => sanitize_user($_POST['emp_username']),
                'name' => sanitize_text_field($_POST['emp_fullname']),
                'email' => sanitize_email($_POST['emp_email']),
                'father_name' => sanitize_text_field($_POST['emp_fathername']),
                'contact_num' => sanitize_text_field($_POST['emp_country_code'] . $contact),
                'emergency_cno' => sanitize_text_field($_POST['emp_emergency_code'] . $emergency),
                'ref1_name' => sanitize_text_field($_POST['emp_ref1_name']),
                'ref1_cno' => sanitize_text_field($ref1_cno),
                'ref2_name' => sanitize_text_field($_POST['emp_ref2_name']),
                'ref2_cno' => sanitize_text_field($ref2_cno),
                'joining_date' => sanitize_text_field($_POST['emp_joining_date']),
                'wage_type' => sanitize_text_field($_POST['emp_wage_type']),
                'basic_wage' => floatval($_POST['emp_basic_wage']),
                'increment_date' => !empty($_POST['emp_increment_date']) ? sanitize_text_field($_POST['emp_increment_date']) : null,
                'increment_percentage' => !empty($_POST['emp_increment_percentage']) ? floatval($_POST['emp_increment_percentage']) : null,
                'updated_wage' => $updated_wage,
                'corp_team' => sanitize_text_field($_POST['emp_corp_team']),
                'position' => sanitize_text_field($_POST['emp_position']),
                'cnic_no' => sanitize_text_field($_POST['emp_cnic']),
                'cnic_pdf' => $uploaded_files['cnic_pdf'] ?? null,
                'char_cert_no' => sanitize_text_field($_POST['emp_char_cert_no']),
                'char_cert_exp' => sanitize_text_field($_POST['emp_char_cert_exp']),
                'char_cert_pdf' => $uploaded_files['char_cert_pdf'] ?? null,
                'emp_letter_pdf' => $uploaded_files['emp_letter_pdf'] ?? null,
                'termination_date' => !empty($_POST['emp_termination_date']) ? sanitize_text_field($_POST['emp_termination_date']) : null,
                'status' => 'active',
                'created_at' => current_time('mysql')
            ],
            ['%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%f', '%s', '%f', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s']
        );
        
        if (!$employee_inserted) {
            throw new Exception('Failed to create employee profile');
        }
        
        // If increment data exists, add to increment history
        if (!empty($_POST['emp_increment_percentage']) && !empty($_POST['emp_increment_date'])) {
            $table_increment = $wpdb->prefix . 'cms_increment_history';
            $wpdb->insert(
                $table_increment,
                [
                    'username' => sanitize_user($_POST['emp_username']),
                    'increment_date' => sanitize_text_field($_POST['emp_increment_date']),
                    'basic_wage' => floatval($_POST['emp_basic_wage']),
                    'updated_wage' => $updated_wage,
                    'increment_percentage' => floatval($_POST['emp_increment_percentage'])
                ],
                ['%s', '%s', '%f', '%f', '%f']
            );
        }
        
        // Commit transaction
        $wpdb->query('COMMIT');
        
        // Log successful registration
        error_log(sprintf(
            'CMS: New employee registered - Username: %s, Name: %s, Email: %s',
            sanitize_user($_POST['emp_username']),
            sanitize_text_field($_POST['emp_fullname']),
            sanitize_email($_POST['emp_email'])
        ));
        
        // Send notification email to admin
        cms_send_employee_registration_notification(sanitize_user($_POST['emp_username']));
        
        wp_redirect(add_query_arg('emp_reg', 'success', $redirect_url));
        exit;
        
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        error_log('CMS Employee Registration Error: ' . $e->getMessage());
        wp_redirect(add_query_arg('emp_reg', 'db_error', $redirect_url));
        exit;
    }
}
add_action('admin_post_nopriv_cms_handle_employee_registration', 'cms_handle_employee_registration');
add_action('admin_post_cms_handle_employee_registration', 'cms_handle_employee_registration');

/**
 * Handle file uploads for employee documents
 */
function cms_handle_employee_file_uploads() {
    $upload_dir = wp_upload_dir();
    $cms_upload_dir = $upload_dir['basedir'] . '/cms-employee-docs/';
    
    // Create directory if not exists
    if (!file_exists($cms_upload_dir)) {
        wp_mkdir_p($cms_upload_dir);
        
        // Add .htaccess to protect directory
        $htaccess_content = "Deny from all";
        file_put_contents($cms_upload_dir . '.htaccess', $htaccess_content);
        
        // Add index.php for security
        file_put_contents($cms_upload_dir . 'index.php', '<?php // Silence is golden');
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
                return false;
            }
            
            // Validate file size (5MB max)
            if ($file['size'] > 5 * 1048576) {
                return false;
            }
            
            // Generate unique filename
            $file_info = pathinfo($file['name']);
            $filename = uniqid() . '_' . sanitize_title($file_info['filename']) . '.pdf';
            $filepath = $cms_upload_dir . $filename;
            
            if (move_uploaded_file($file['tmp_name'], $filepath)) {
                $uploaded_files[$db_field] = $upload_dir['basedir'] . '/cms-employee-docs/' . $filename;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    
    return $uploaded_files;
}

/**
 * Send notification email to admin about new employee registration
 */
function cms_send_employee_registration_notification($username) {
    global $wpdb;
    
    $table_employee = $wpdb->prefix . 'cms_employee';
    $employee = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_employee WHERE username = %s",
        $username
    ));
    
    if (!$employee) {
        return;
    }
    
    $admin_email = get_option('admin_email');
    $site_name = get_bloginfo('name');
    
    $subject = sprintf('[%s] New Employee Registration: %s', $site_name, $employee->name);
    
    $message = sprintf(
        "A new employee has been registered in the system.\n\n" .
        "Employee Details:\n" .
        "Name: %s\n" .
        "Username: %s\n" .
        "Email: %s\n" .
        "Position: %s\n" .
        "Team: %s\n" .
        "Joining Date: %s\n" .
        "Wage Type: %s\n" .
        "Basic Wage: %s\n\n" .
        "Login URL: %s\n",
        $employee->name,
        $employee->username,
        $employee->email,
        $employee->position,
        $employee->corp_team,
        $employee->joining_date,
        $employee->wage_type,
        $employee->basic_wage,
        wp_login_url()
    );
    
    wp_mail($admin_email, $subject, $message);
}

/**
 * AJAX handler to check username availability
 */
function cms_check_username_availability() {
    if (!isset($_POST['username'])) {
        wp_send_json_error('No username provided');
    }
    
    global $wpdb;
    $table_users = $wpdb->prefix . 'cms_users';
    $username = sanitize_user($_POST['username']);
    
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_users WHERE username = %s",
        $username
    ));
    
    if ($exists > 0) {
        wp_send_json_error('Username already taken');
    } else {
        wp_send_json_success('Username available');
    }
}
add_action('wp_ajax_cms_check_username', 'cms_check_username_availability');
add_action('wp_ajax_nopriv_cms_check_username', 'cms_check_username_availability');

/**
 * AJAX handler to check email availability
 */
function cms_check_email_availability() {
    if (!isset($_POST['email'])) {
        wp_send_json_error('No email provided');
    }
    
    global $wpdb;
    $table_employee = $wpdb->prefix . 'cms_employee';
    $email = sanitize_email($_POST['email']);
    
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_employee WHERE email = %s",
        $email
    ));
    
    if ($exists > 0) {
        wp_send_json_error('Email already registered');
    } else {
        wp_send_json_success('Email available');
    }
}
add_action('wp_ajax_cms_check_email', 'cms_check_email_availability');
add_action('wp_ajax_nopriv_cms_check_email', 'cms_check_email_availability');

/**
 * AJAX handler to check CNIC availability
 */
function cms_check_cnic_availability() {
    if (!isset($_POST['cnic'])) {
        wp_send_json_error('No CNIC provided');
    }
    
    global $wpdb;
    $table_employee = $wpdb->prefix . 'cms_employee';
    $cnic = sanitize_text_field($_POST['cnic']);
    
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_employee WHERE cnic_no = %s",
        $cnic
    ));
    
    if ($exists > 0) {
        wp_send_json_error('CNIC already registered');
    } else {
        wp_send_json_success('CNIC available');
    }
}
add_action('wp_ajax_cms_check_cnic', 'cms_check_cnic_availability');
add_action('wp_ajax_nopriv_cms_check_cnic', 'cms_check_cnic_availability');

/**
 * Get employee by ID or username
 */
function cms_get_employee($identifier, $by = 'username') {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_employee';
    
    if ($by === 'id') {
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d",
            $identifier
        ));
    } else {
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE username = %s",
            $identifier
        ));
    }
}

/**
 * Update employee information
 */
function cms_update_employee($username, $data) {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_employee';
    
    return $wpdb->update(
        $table,
        $data,
        ['username' => $username],
        null,
        ['%s']
    );
}

/**
 * Delete employee (soft delete by changing status)
 */
function cms_delete_employee($username) {
    return cms_update_employee($username, ['status' => 'terminated']);
}

/**
 * Get all employees with optional filters
 */
function cms_get_employees($filters = []) {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_employee';
    
    $where = ['1=1'];
    $values = [];
    
    if (!empty($filters['status'])) {
        $where[] = 'status = %s';
        $values[] = $filters['status'];
    }
    
    if (!empty($filters['corp_team'])) {
        $where[] = 'corp_team = %s';
        $values[] = $filters['corp_team'];
    }
    
    if (!empty($filters['search'])) {
        $where[] = '(name LIKE %s OR email LIKE %s OR username LIKE %s)';
        $search = '%' . $wpdb->esc_like($filters['search']) . '%';
        $values[] = $search;
        $values[] = $search;
        $values[] = $search;
    }
    
    $sql = "SELECT * FROM $table WHERE " . implode(' AND ', $where) . " ORDER BY name ASC";
    
    if (!empty($values)) {
        $sql = $wpdb->prepare($sql, $values);
    }
    
    return $wpdb->get_results($sql);
}

/**
 * Get employee count by status
 */
function cms_get_employee_counts() {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_employee';
    
    $results = $wpdb->get_results("
        SELECT status, COUNT(*) as count 
        FROM $table 
        GROUP BY status
    ");
    
    $counts = ['active' => 0, 'inactive' => 0, 'terminated' => 0, 'total' => 0];
    
    foreach ($results as $row) {
        $counts[$row->status] = intval($row->count);
        $counts['total'] += intval($row->count);
    }
    
    return $counts;
}