<?php
/**
 * CMS Update Admin Shortcode
 * Form to update existing admin data with database integration
 * 
 * Usage: [cms_update_admin]
 * Usage: [cms_update_admin admin_id="101"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define shortcode slug
if (!defined('CMS_ADMIN_UPDATE_SHORTCODE')) {
    define('CMS_ADMIN_UPDATE_SHORTCODE', 'cms_admin_update');
}

/**
 * Update Admin Shortcode
 */
function cms_update_admin_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'admin_id' => 0,
            'title' => 'Update Admin Profile',
            'button_text' => 'Update Admin',
            'success_message' => 'Admin updated successfully!',
            'class' => '',
            'redirect_url' => '/admin-list'
        ),
        $atts,
        'cms_update_admin'
    );
    
    // Get admin ID from various sources
    $admin_id = $atts['admin_id'];
    if (!$admin_id) {
        $admin_id = get_query_var('admin_id');
        if (!$admin_id && isset($_GET['admin_id'])) {
            $admin_id = intval($_GET['admin_id']);
        }
    }
    
    // Also check for username parameter
    if (!$admin_id && isset($_GET['username'])) {
        $username = sanitize_user($_GET['username']);
        $admin_data = cms_get_admin_by_username($username);
        if ($admin_data) {
            $admin_id = $admin_data['id'];
        }
    }
    
    if (!$admin_id) {
        return '<div style="padding: 20px; background: #ffe8e8; color: #b34141; border-radius: 8px; text-align: center;">
            No admin selected. Please provide an admin ID or username.
        </div>';
    }
    
    // Get admin data from database
    $admin_data = cms_get_admin_by_id($admin_id);
    
    if (!$admin_data) {
        return '<div style="padding: 20px; background: #ffe8e8; color: #b34141; border-radius: 8px; text-align: center;">
            Admin not found in database.
        </div>';
    }
    
    // Parse contact numbers
    $contact_parts = preg_split('/\s+/', $admin_data['contact_num'] ?? '', 2);
    $contact_code = $contact_parts[0] ?? '+1';
    $contact_number = $contact_parts[1] ?? $admin_data['contact_num'] ?? '';
    
    $emergency_parts = preg_split('/\s+/', $admin_data['emergency_cno'] ?? '', 2);
    $emergency_code = $emergency_parts[0] ?? '+1';
    $emergency_number = $emergency_parts[1] ?? $admin_data['emergency_cno'] ?? '';
    
    ob_start();
    ?>
    
    <style>
    .cms-update2-container {
        max-width: 800px;
        margin: 30px auto;
        padding: 35px;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        border: 1px solid #f0f0f0;
    }
    
    .cms-update2-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .cms-update2-title {
        margin: 0;
        font-size: 26px;
        font-weight: 700;
        color: #1a2b3c;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .cms-update2-title:before {
        content: '✏️';
        font-size: 28px;
    }
    
    .cms-back2-link {
        padding: 10px 20px;
        background: #f8fafc;
        color: #4a5568;
        text-decoration: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        border: 1px solid #e2e8f0;
    }
    
    .cms-back2-link:hover {
        background: #edf2f7;
        color: #2c3e50;
    }
    
    .cms-update2-section {
        background: #f8fafc;
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 25px;
        border: 1px solid #e9edf2;
    }
    
    .cms-section2-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .cms-section2-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .cms-form2-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .cms-form2-group {
        margin-bottom: 15px;
    }
    
    .cms-form2-group.full-width {
        grid-column: span 2;
    }
    
    .cms-form2-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #34495e;
        font-size: 14px;
    }
    
    .cms-required2 {
        color: #e74c3c;
        margin-left: 4px;
    }
    
    .cms-form2-control {
        width: 100%;
        padding: 14px 18px;
        font-size: 15px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        transition: all 0.25s ease;
        box-sizing: border-box;
        background: #ffffff;
    }
    
    .cms-form2-control:focus {
        outline: none;
        border-color: #27ae60;
        box-shadow: 0 0 0 4px rgba(39,174,96,0.05);
    }
    
    .cms-form2-control[readonly] {
        background: #f8fafc;
        border-color: #e2e8f0;
        color: #718096;
        cursor: not-allowed;
    }
    
    .cms-form2-control.error {
        border-color: #e74c3c;
    }
    
    .cms-phone2-group {
        display: flex;
        gap: 10px;
    }
    
    .cms-country2-code {
        width: 120px;
        flex-shrink: 0;
    }
    
    .cms-position2-select {
        width: 100%;
        padding: 14px 18px;
        font-size: 15px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        background: #ffffff;
    }
    
    .cms-password-section {
        background: #fff3e0;
        border-left: 4px solid #f39c12;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 25px;
    }
    
    .cms-password-toggle {
        margin-bottom: 15px;
    }
    
    .cms-password-toggle label {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        color: #f39c12;
        font-weight: 500;
    }
    
    .cms-password-fields {
        display: none;
    }
    
    .cms-password-fields.show {
        display: block;
    }
    
    .cms-password-strength {
        margin-top: 8px;
        font-size: 12px;
    }
    
    .cms-password-strength.weak {
        color: #e74c3c;
    }
    
    .cms-password-strength.medium {
        color: #f39c12;
    }
    
    .cms-password-strength.strong {
        color: #27ae60;
    }
    
    .cms-update2-footer {
        margin-top: 30px;
        display: flex;
        gap: 15px;
        justify-content: flex-end;
    }
    
    .cms-update2-button {
        padding: 16px 32px;
        background: linear-gradient(145deg, #27ae60, #219a52);
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
    
    .cms-update2-button:hover {
        background: linear-gradient(145deg, #219a52, #1e8449);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(39,174,96,0.2);
    }
    
    .cms-update2-button:disabled {
        background: #95a5a6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    
    .cms-cancel2-button {
        padding: 16px 32px;
        background: #f8fafc;
        color: #4a5568;
        border: 2px solid #e2e8f0;
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
    
    .cms-cancel2-button:hover {
        background: #edf2f7;
        border-color: #cbd5e0;
    }
    
    .cms-message2 {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cms-message2.success {
        background: #e3f7ec;
        color: #0a5c36;
        border: 1px solid #b8e0c2;
    }
    
    .cms-message2.success:before {
        content: '✓';
        font-size: 20px;
        font-weight: bold;
    }
    
    .cms-message2.error {
        background: #ffe8e8;
        color: #b34141;
        border: 1px solid #ffc9c9;
    }
    
    .cms-message2.error:before {
        content: '⚠';
        font-size: 20px;
    }
    
    .cms-info2-box {
        background: #e8f5e9;
        border-left: 4px solid #27ae60;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        font-size: 14px;
        color: #2c3e50;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .cms-info2-box strong {
        color: #27ae60;
    }
    
    .cms-last-login {
        background: #f8fafc;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 12px;
        color: #718096;
    }
    
    .cms-email-warning {
        color: #e74c3c;
        font-size: 12px;
        margin-top: 5px;
        display: none;
    }
    </style>
    
    <div class="cms-update2-container <?php echo esc_attr($atts['class']); ?>">
        
        <div class="cms-update2-header">
            <h2 class="cms-update2-title"><?php echo esc_html($atts['title']); ?></h2>
            <a href="<?php echo esc_url(remove_query_arg(array('admin_id', 'username', 'update'), wp_get_referer())); ?>" class="cms-back2-link">
                ← Back to List
            </a>
        </div>
        
        <?php
        // Display messages
        if (isset($_GET['update']) && $_GET['update'] === 'success') {
            echo '<div class="cms-message2 success">' . esc_html($atts['success_message']) . '</div>';
            
            // Auto redirect after 3 seconds
            if (!empty($atts['redirect_url'])) {
                echo '<script>setTimeout(function() { window.location.href = "' . esc_url(home_url($atts['redirect_url'])) . '"; }, 3000);</script>';
            }
        }
        
        if (isset($_GET['update']) && $_GET['update'] === 'error') {
            $error_msg = isset($_GET['error_msg']) ? urldecode($_GET['error_msg']) : 'Update failed. Please try again.';
            echo '<div class="cms-message2 error">' . esc_html($error_msg) . '</div>';
        }
        ?>
        
        <div class="cms-info2-box">
            <span>
                <strong>📝 Editing Admin:</strong> <?php echo esc_html($admin_data['name']); ?> 
                (ID: <?php echo esc_html($admin_id); ?>, Username: <?php echo esc_html($admin_data['username']); ?>)
            </span>
            <?php if (!empty($admin_data['last_login'])): ?>
                <span class="cms-last-login">Last login: <?php echo esc_html(date('Y-m-d H:i', strtotime($admin_data['last_login']))); ?></span>
            <?php endif; ?>
        </div>
        
        <form method="post" action="" id="cms-update-admin2-form">
            <?php wp_nonce_field('cms_admin_update_action', 'cms_admin_update_nonce'); ?>
            <input type="hidden" name="cms_admin_id" value="<?php echo esc_attr($admin_id); ?>">
            <input type="hidden" name="cms_username" value="<?php echo esc_attr($admin_data['username']); ?>">
            
            <!-- Personal Information Section -->
            <div class="cms-update2-section">
                <div class="cms-section2-header">
                    <h3>Personal Information</h3>
                </div>
                
                <div class="cms-form2-grid">
                    <div class="cms-form2-group">
                        <label for="username2">Username</label>
                        <input 
                            type="text" 
                            id="username2" 
                            class="cms-form2-control" 
                            value="<?php echo esc_attr($admin_data['username']); ?>"
                            readonly
                            disabled
                        >
                    </div>
                    
                    <div class="cms-form2-group">
                        <label for="fullname2">Full Name <span class="cms-required2">*</span></label>
                        <input 
                            type="text" 
                            id="fullname2" 
                            name="cms_fullname" 
                            class="cms-form2-control" 
                            value="<?php echo esc_attr($admin_data['name']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="cms-form2-group">
                        <label for="email2">Email Address <span class="cms-required2">*</span></label>
                        <input 
                            type="email" 
                            id="email2" 
                            name="cms_email" 
                            class="cms-form2-control" 
                            value="<?php echo esc_attr($admin_data['email']); ?>"
                            required
                        >
                        <div id="email-check" class="cms-email-warning"></div>
                    </div>
                    
                    <div class="cms-form2-group">
                        <label for="fathername2">Father's Name <span class="cms-required2">*</span></label>
                        <input 
                            type="text" 
                            id="fathername2" 
                            name="cms_fathername" 
                            class="cms-form2-control" 
                            value="<?php echo esc_attr($admin_data['father_name']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="cms-form2-group">
                        <label for="position2">Position <span class="cms-required2">*</span></label>
                        <select id="position2" name="cms_position" class="cms-position2-select" required>
                            <option value="Senior Admin" <?php selected($admin_data['position'], 'Senior Admin'); ?>>Senior Admin</option>
                            <option value="Junior Admin" <?php selected($admin_data['position'], 'Junior Admin'); ?>>Junior Admin</option>
                            <option value="HR Admin" <?php selected($admin_data['position'], 'HR Admin'); ?>>HR Admin</option>
                            <option value="Finance Admin" <?php selected($admin_data['position'], 'Finance Admin'); ?>>Finance Admin</option>
                            <option value="Operations Admin" <?php selected($admin_data['position'], 'Operations Admin'); ?>>Operations Admin</option>
                            <option value="Support Admin" <?php selected($admin_data['position'], 'Support Admin'); ?>>Support Admin</option>
                            <option value="Technical Admin" <?php selected($admin_data['position'], 'Technical Admin'); ?>>Technical Admin</option>
                        </select>
                    </div>
                    
                    <div class="cms-form2-group">
                        <label for="contact2">Contact Number <span class="cms-required2">*</span></label>
                        <div class="cms-phone2-group">
                            <input 
                                type="text" 
                                id="contact_code2" 
                                name="cms_contact_code" 
                                class="cms-form2-control cms-country2-code" 
                                value="<?php echo esc_attr($contact_code); ?>"
                                placeholder="+1"
                                required
                            >
                            <input 
                                type="tel" 
                                id="contact2" 
                                name="cms_contact" 
                                class="cms-form2-control" 
                                value="<?php echo esc_attr($contact_number); ?>"
                                placeholder="Phone number"
                                required
                                pattern="[0-9]{10,15}"
                                title="Please enter a valid phone number"
                            >
                        </div>
                    </div>
                    
                    <div class="cms-form2-group">
                        <label for="emergency2">Emergency Contact <span class="cms-required2">*</span></label>
                        <div class="cms-phone2-group">
                            <input 
                                type="text" 
                                id="emergency_code2" 
                                name="cms_emergency_code" 
                                class="cms-form2-control cms-country2-code" 
                                value="<?php echo esc_attr($emergency_code); ?>"
                                placeholder="+1"
                                required
                            >
                            <input 
                                type="tel" 
                                id="emergency2" 
                                name="cms_emergency" 
                                class="cms-form2-control" 
                                value="<?php echo esc_attr($emergency_number); ?>"
                                placeholder="Emergency number"
                                required
                                pattern="[0-9]{10,15}"
                                title="Please enter a valid phone number"
                            >
                        </div>
                    </div>
                    
                    <div class="cms-form2-group">
                        <label for="status2">Account Status</label>
                        <select id="status2" name="cms_status" class="cms-form2-control">
                            <option value="active" <?php selected($admin_data['status'], 'active'); ?>>Active</option>
                            <option value="inactive" <?php selected($admin_data['status'], 'inactive'); ?>>Inactive</option>
                            <option value="suspended" <?php selected($admin_data['status'], 'suspended'); ?>>Suspended</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Password Change Section -->
            <div class="cms-update2-section">
                <div class="cms-section2-header">
                    <h3>Change Password (Optional)</h3>
                </div>
                
                <div class="cms-password-section">
                    <div class="cms-password-toggle">
                        <label>
                            <input type="checkbox" id="change_password" name="cms_change_password" value="1">
                            Change Password
                        </label>
                    </div>
                    
                    <div class="cms-password-fields" id="password-fields">
                        <div class="cms-form2-grid">
                            <div class="cms-form2-group">
                                <label for="new_password">New Password</label>
                                <input 
                                    type="password" 
                                    id="new_password" 
                                    name="cms_new_password" 
                                    class="cms-form2-control" 
                                    placeholder="Enter new password"
                                    minlength="8"
                                    pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                                    title="Must contain at least one number, one uppercase and lowercase letter, and at least 8 characters"
                                >
                                <div id="password-strength" class="cms-password-strength"></div>
                            </div>
                            
                            <div class="cms-form2-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input 
                                    type="password" 
                                    id="confirm_password" 
                                    name="cms_confirm_password" 
                                    class="cms-form2-control" 
                                    placeholder="Confirm new password"
                                >
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reference #1 Section -->
            <div class="cms-update2-section">
                <div class="cms-section2-header">
                    <h3>Reference #1</h3>
                </div>
                
                <div class="cms-form2-grid">
                    <div class="cms-form2-group">
                        <label for="ref1_name2">Reference Name <span class="cms-required2">*</span></label>
                        <input 
                            type="text" 
                            id="ref1_name2" 
                            name="cms_ref1_name" 
                            class="cms-form2-control" 
                            value="<?php echo esc_attr($admin_data['ref1_name']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="cms-form2-group">
                        <label for="ref1_cno2">Reference Contact <span class="cms-required2">*</span></label>
                        <input 
                            type="tel" 
                            id="ref1_cno2" 
                            name="cms_ref1_cno" 
                            class="cms-form2-control" 
                            value="<?php echo esc_attr($admin_data['ref1_cno']); ?>"
                            required
                            pattern="[0-9]{10,15}"
                            title="Please enter a valid phone number"
                        >
                    </div>
                </div>
            </div>
            
            <!-- Reference #2 Section -->
            <div class="cms-update2-section">
                <div class="cms-section2-header">
                    <h3>Reference #2</h3>
                </div>
                
                <div class="cms-form2-grid">
                    <div class="cms-form2-group">
                        <label for="ref2_name2">Reference Name <span class="cms-required2">*</span></label>
                        <input 
                            type="text" 
                            id="ref2_name2" 
                            name="cms_ref2_name" 
                            class="cms-form2-control" 
                            value="<?php echo esc_attr($admin_data['ref2_name']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="cms-form2-group">
                        <label for="ref2_cno2">Reference Contact <span class="cms-required2">*</span></label>
                        <input 
                            type="tel" 
                            id="ref2_cno2" 
                            name="cms_ref2_cno" 
                            class="cms-form2-control" 
                            value="<?php echo esc_attr($admin_data['ref2_cno']); ?>"
                            required
                            pattern="[0-9]{10,15}"
                            title="Please enter a valid phone number"
                        >
                    </div>
                </div>
            </div>
            
            <div class="cms-update2-footer">
                <a href="<?php echo esc_url(remove_query_arg(array('admin_id', 'username', 'update'), wp_get_referer())); ?>" class="cms-cancel2-button">
                    Cancel
                </a>
                <button type="submit" name="cms_admin_update_submit" class="cms-update2-button">
                    💾 <?php echo esc_html($atts['button_text']); ?>
                </button>
            </div>
        </form>
    </div>
    
    <script>
jQuery(document).ready(function($) {
    var originalEmail = '<?php echo esc_js($admin_data['email']); ?>';
    
    // Password toggle functionality - FIXED
    $('#change_password').on('change', function() {
        console.log('Toggle changed, checked: ' + $(this).is(':checked')); // Debug
        if ($(this).is(':checked')) {
            $('#password-fields').slideDown(300).addClass('show');
            $('#new_password, #confirm_password').prop('required', true);
            console.log('Password fields shown'); // Debug
        } else {
            $('#password-fields').slideUp(300).removeClass('show');
            $('#new_password, #confirm_password').prop('required', false).val('');
            $('#password-strength').text('').removeClass('weak medium strong');
            console.log('Password fields hidden'); // Debug
        }
    });
    
    // Make sure password fields are hidden by default
    $('#password-fields').hide();
    
    // Password strength indicator
    $('#new_password').on('keyup', function() {
        var password = $(this).val();
        var strength = checkPasswordStrength(password);
        var strengthText = $('#password-strength');
        
        if (password.length === 0) {
            strengthText.text('');
            strengthText.removeClass('weak medium strong');
        } else if (strength < 3) {
            strengthText.text('Weak password').removeClass('medium strong').addClass('weak');
        } else if (strength < 4) {
            strengthText.text('Medium password').removeClass('weak strong').addClass('medium');
        } else {
            strengthText.text('Strong password').removeClass('weak medium').addClass('strong');
        }
    });
    
    function checkPasswordStrength(password) {
        var strength = 0;
        
        // Length check
        if (password.length >= 8) strength++;
        if (password.length >= 10) strength++;
        
        // Contains number
        if (password.match(/\d/)) strength++;
        
        // Contains lowercase
        if (password.match(/[a-z]/)) strength++;
        
        // Contains uppercase
        if (password.match(/[A-Z]/)) strength++;
        
        // Contains special character
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        
        return strength;
    }
    
    // Email availability check
    var emailTimer;
    $('#email2').on('keyup', function() {
        clearTimeout(emailTimer);
        var email = $(this).val();
        
        if (email !== originalEmail && email.length >= 5 && email.includes('@')) {
            emailTimer = setTimeout(function() {
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'cms_check_email',
                        email: email,
                        nonce: '<?php echo wp_create_nonce('cms_check_email'); ?>'
                    },
                    success: function(response) {
                        if (response.data.exists) {
                            $('#email2').addClass('error');
                            $('#email-check').text('Email already exists').show();
                        } else {
                            $('#email2').removeClass('error');
                            $('#email-check').hide();
                        }
                    }
                });
            }, 500);
        } else {
            $('#email2').removeClass('error');
            $('#email-check').hide();
        }
    });
    
    // Form validation
    $('#cms-update-admin2-form').on('submit', function(e) {
        var isValid = true;
        var errorMessages = [];
        
        // Check required fields
        $(this).find('[required]').each(function() {
            if (!$(this).val()) {
                $(this).addClass('error');
                isValid = false;
                var fieldName = $(this).attr('name') || 'field';
                errorMessages.push(fieldName + ' is required');
            } else {
                $(this).removeClass('error');
            }
        });
        
        // Validate email
        var email = $('#email2');
        var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email.val() && !emailPattern.test(email.val())) {
            email.addClass('error');
            isValid = false;
            errorMessages.push('Invalid email format');
        }
        
        // Check if email already exists
        if ($('#email-check').is(':visible')) {
            isValid = false;
            errorMessages.push('Email already exists');
        }
        
        // Validate phone numbers
        $('input[type="tel"]').each(function() {
            var phone = $(this).val();
            var phonePattern = /^[0-9]{10,15}$/;
            if (phone && !phonePattern.test(phone.replace(/\D/g, ''))) {
                $(this).addClass('error');
                isValid = false;
                errorMessages.push('Invalid phone number format');
            }
        });
        
        // Validate password if changing
        if ($('#change_password').is(':checked')) {
            var password = $('#new_password').val();
            var confirmPassword = $('#confirm_password').val();
            var passwordPattern = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/;
            
            if (!passwordPattern.test(password)) {
                $('#new_password').addClass('error');
                isValid = false;
                errorMessages.push('Password must contain at least one number, one uppercase and lowercase letter, and be at least 8 characters long');
            }
            
            if (password !== confirmPassword) {
                $('#confirm_password').addClass('error');
                isValid = false;
                errorMessages.push('Passwords do not match');
            }
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Please fix the following errors:\n- ' + errorMessages.join('\n- '));
            return false;
        }
        
        $(this).find('.cms-update2-button').text('Updating...').prop('disabled', true);
    });
    
    // Remove error class on input
    $('.cms-form2-control').on('input', function() {
        $(this).removeClass('error');
    });
});
</script>
    <?php
    return ob_get_clean();
}

add_shortcode('cms_update_admin', 'cms_update_admin_shortcode');
add_shortcode(CMS_ADMIN_UPDATE_SHORTCODE, 'cms_update_admin_shortcode');

/**
 * Handle Admin Update Submission - RENAMED to avoid conflict
 */
function cms_handle_admin_update_submission() {
    if (isset($_POST['cms_admin_update_submit'])) {
        
        // Verify nonce
        if (!isset($_POST['cms_admin_update_nonce']) || !wp_verify_nonce($_POST['cms_admin_update_nonce'], 'cms_admin_update_action')) {
            wp_redirect(add_query_arg('update', 'error', wp_get_referer()));
            exit;
        }
        
        global $wpdb;
        
        $admin_id = intval($_POST['cms_admin_id']);
        $username = sanitize_user($_POST['cms_username']);
        
        // Get current admin data
        $current_admin = cms_get_admin_by_id($admin_id);
        if (!$current_admin) {
            wp_redirect(add_query_arg('update', 'error', wp_get_referer()));
            exit;
        }
        
        // Sanitize input
        $fullname = sanitize_text_field($_POST['cms_fullname']);
        $email = sanitize_email($_POST['cms_email']);
        $fathername = sanitize_text_field($_POST['cms_fathername']);
        $position = sanitize_text_field($_POST['cms_position']);
        $contact_code = sanitize_text_field($_POST['cms_contact_code']);
        $contact = preg_replace('/[^0-9]/', '', $_POST['cms_contact']);
        $emergency_code = sanitize_text_field($_POST['cms_emergency_code']);
        $emergency = preg_replace('/[^0-9]/', '', $_POST['cms_emergency']);
        $ref1_name = sanitize_text_field($_POST['cms_ref1_name']);
        $ref1_cno = preg_replace('/[^0-9]/', '', $_POST['cms_ref1_cno']);
        $ref2_name = sanitize_text_field($_POST['cms_ref2_name']);
        $ref2_cno = preg_replace('/[^0-9]/', '', $_POST['cms_ref2_cno']);
        $status = sanitize_text_field($_POST['cms_status']);
        
        // Complete phone numbers
        $full_contact = $contact_code . $contact;
        $full_emergency = $emergency_code . $emergency;
        
        // Validation
        $errors = array();
        
        // Check if email exists (if changed)
        if ($email !== $current_admin['email'] && cms_email_exists($email)) {
            $errors[] = 'Email already exists';
        }
        
        // Validate email
        if (!is_email($email)) {
            $errors[] = 'Invalid email format';
        }
        
        // Validate phone numbers
        if (strlen($contact) < 10 || strlen($contact) > 15) {
            $errors[] = 'Invalid contact number';
        }
        
        if (strlen($emergency) < 10 || strlen($emergency) > 15) {
            $errors[] = 'Invalid emergency contact number';
        }
        
        if (strlen($ref1_cno) < 10 || strlen($ref1_cno) > 15) {
            $errors[] = 'Invalid reference 1 contact number';
        }
        
        if (strlen($ref2_cno) < 10 || strlen($ref2_cno) > 15) {
            $errors[] = 'Invalid reference 2 contact number';
        }
        
        // Validate password if changing
        $change_password = isset($_POST['cms_change_password']) && $_POST['cms_change_password'] == '1';
        if ($change_password) {
            $new_password = $_POST['cms_new_password'];
            $confirm_password = $_POST['cms_confirm_password'];
            
            $password_pattern = '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/';
            if (!preg_match($password_pattern, $new_password)) {
                $errors[] = 'Password must contain at least one number, one uppercase and lowercase letter, and be at least 8 characters long';
            }
            
            if ($new_password !== $confirm_password) {
                $errors[] = 'Passwords do not match';
            }
        }
        
        // If there are errors, redirect back
        if (!empty($errors)) {
            $error_string = implode(', ', $errors);
            wp_redirect(add_query_arg(
                array(
                    'update' => 'error',
                    'error_msg' => urlencode($error_string)
                ), 
                wp_get_referer()
            ));
            exit;
        }
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            // Table names
            $table_users = $wpdb->prefix . 'cms_users';
            $table_admin = $wpdb->prefix . 'cms_admin';
            
            // Update admin table
            $admin_updated = $wpdb->update(
                $table_admin,
                array(
                    'name' => $fullname,
                    'email' => $email,
                    'father_name' => $fathername,
                    'contact_num' => $full_contact,
                    'emergency_cno' => $full_emergency,
                    'ref1_name' => $ref1_name,
                    'ref1_cno' => $ref1_cno,
                    'ref2_name' => $ref2_name,
                    'ref2_cno' => $ref2_cno,
                    'position' => $position,
                    'updated_at' => current_time('mysql')
                ),
                array('username' => $username),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
                array('%s')
            );
            
            if ($admin_updated === false) {
                throw new Exception('Failed to update admin profile');
            }
            
            // Update users table status
            $user_updated = $wpdb->update(
                $table_users,
                array('status' => $status),
                array('username' => $username),
                array('%s'),
                array('%s')
            );
            
            if ($user_updated === false) {
                throw new Exception('Failed to update user status');
            }
            
            // Update password if requested
            if ($change_password) {
                $hashed_password = wp_hash_password($new_password);
                $password_updated = $wpdb->update(
                    $table_users,
                    array('password' => $hashed_password),
                    array('username' => $username),
                    array('%s'),
                    array('%s')
                );
                
                if ($password_updated === false) {
                    throw new Exception('Failed to update password');
                }
            }
            
            // Commit transaction
            $wpdb->query('COMMIT');
            
            // Redirect with success
            wp_redirect(add_query_arg(
                array(
                    'update' => 'success',
                    'admin_id' => $admin_id
                ), 
                wp_get_referer()
            ));
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $wpdb->query('ROLLBACK');
            
            error_log('CMS Admin Update Error: ' . $e->getMessage());
            
            wp_redirect(add_query_arg(
                array(
                    'update' => 'error',
                    'error_msg' => urlencode('Update failed: ' . $e->getMessage())
                ), 
                wp_get_referer()
            ));
            exit;
        }
    }
}
add_action('init', 'cms_handle_admin_update_submission');