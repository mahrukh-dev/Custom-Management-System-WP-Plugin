<?php
/**
 * CMS Admin Form Shortcode
 * Complete form for Admin registration/profile with database integration
 * 
 * Fields: username, name, email, father_name, contact_num, emergency_cno, 
 *         ref1_name, ref1_cno, ref2_name, ref2_cno, position, password
 * 
 * Usage: [cms_admin_form]
 * Usage: [cms_admin_form title="Register as Admin" button_text="Submit Application"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define shortcode slug
if (!defined('CMS_ADMIN_CREATE_SHORTCODE')) {
    define('CMS_ADMIN_CREATE_SHORTCODE', 'cms_admin_create');
}


/**
 * Admin Form Shortcode
 */
function cms_admin_form_shortcode($atts) {
    // Parse attributes
    $atts = shortcode_atts(
        array(
            'title' => 'Admin Registration',
            'description' => 'Please fill in all the details below to register as Admin.',
            'button_text' => 'Register Admin',
            'success_message' => 'Admin registered successfully!',
            'class' => '',
            'show_labels' => 'yes',
            'required_field_mark' => '*',
            'redirect_url' => '/admin-list'
        ),
        $atts,
        'cms_admin_form'
    );
    
    ob_start();
    ?>
    
    <style>
    /* Admin Form Styles */
    .cms-admin2-form-container {
        max-width: 900px;
        margin: 30px auto;
        padding: 40px;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    
    .cms-admin2-header {
        margin-bottom: 35px;
        text-align: center;
    }
    
    .cms-admin2-title {
        margin: 0 0 10px 0;
        font-size: 32px;
        font-weight: 700;
        color: #1a2b3c;
        letter-spacing: -0.5px;
    }
    
    .cms-admin2-description {
        margin: 0;
        font-size: 15px;
        color: #6c7a89;
        line-height: 1.6;
    }
    
    .cms-admin2-section {
        background: #f8fafc;
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 30px;
        border: 1px solid #e9edf2;
    }
    
    .cms-section2-title {
        margin: 0 0 20px 0;
        font-size: 20px;
        font-weight: 600;
        color: #2c3e50;
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .cms-section2-title:before {
        content: '';
        width: 4px;
        height: 20px;
        background: #27ae60;
        border-radius: 2px;
        display: inline-block;
    }
    
    .cms-form2-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .cms-form2-group {
        margin-bottom: 5px;
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
        background: #ffffff;
    }
    
    .cms-form2-control::placeholder {
        color: #a0b3c2;
        font-size: 14px;
    }
    
    .cms-form2-control.error {
        border-color: #e74c3c;
    }
    
    .cms-error2-text {
        color: #e74c3c;
        font-size: 12px;
        margin-top: 6px;
        display: block;
    }
    
    .cms-phone2-input {
        display: flex;
        gap: 10px;
    }
    
    .cms-country2-code {
        width: 100px;
        flex-shrink: 0;
    }
    
    .cms-position-select {
        width: 100%;
        padding: 14px 18px;
        font-size: 15px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        background: #ffffff;
        color: #34495e;
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
    
    .cms-submit2-section {
        text-align: center;
        margin-top: 20px;
    }
    
    .cms-submit2-button {
        min-width: 250px;
        padding: 16px 32px;
        background: linear-gradient(145deg, #27ae60, #219a52);
        color: white;
        border: none;
        border-radius: 40px;
        font-size: 17px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(39,174,96,0.2);
    }
    
    .cms-submit2-button:hover {
        background: linear-gradient(145deg, #219a52, #1e8449);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(39,174,96,0.3);
    }
    
    .cms-submit2-button:active {
        transform: translateY(0);
    }
    
    .cms-submit2-button:disabled {
        background: #95a5a6;
        cursor: not-allowed;
        transform: none;
        box-shadow: none;
    }
    
    .cms-message2 {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 30px;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 12px;
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
    
    .cms-ref2-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-top: 15px;
    }
    
    .cms-ref2-item {
        background: #ffffff;
        padding: 20px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }
    
    .cms-ref2-title {
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0 0 15px 0;
        padding-bottom: 10px;
        border-bottom: 1px dashed #cbd5e0;
    }
    
    @media (max-width: 768px) {
        .cms-admin2-form-container {
            padding: 25px;
            margin: 20px 15px;
        }
        
        .cms-form2-grid {
            grid-template-columns: 1fr;
        }
        
        .cms-form2-group.full-width {
            grid-column: span 1;
        }
        
        .cms-ref2-grid {
            grid-template-columns: 1fr;
        }
        
        .cms-admin2-title {
            font-size: 26px;
        }
    }
    </style>
    
    <div class="cms-admin2-form-container <?php echo esc_attr($atts['class']); ?>">
        
        <div class="cms-admin2-header">
            <h2 class="cms-admin2-title"><?php echo esc_html($atts['title']); ?></h2>
            <?php if (!empty($atts['description'])): ?>
                <p class="cms-admin2-description"><?php echo esc_html($atts['description']); ?></p>
            <?php endif; ?>
        </div>
        
        <?php
        // Display messages
        if (isset($_GET['admin_reg']) && $_GET['admin_reg'] === 'success') {
            $username = isset($_GET['username']) ? urldecode($_GET['username']) : '';
            $message = esc_html($atts['success_message']);
            if ($username) {
                $message .= ' Username: <strong>' . esc_html($username) . '</strong>';
            }
            echo '<div class="cms-message2 success">' . $message . '</div>';
            
            // Auto redirect after 3 seconds
            if (!empty($atts['redirect_url'])) {
                echo '<script>setTimeout(function() { window.location.href = "' . esc_url(home_url($atts['redirect_url'])) . '"; }, 3000);</script>';
            }
        }
        
        if (isset($_GET['admin_reg']) && $_GET['admin_reg'] === 'error') {
            $error_msg = isset($_GET['error_msg']) ? urldecode($_GET['error_msg']) : 'Registration failed. Please try again.';
            echo '<div class="cms-message2 error">' . esc_html($error_msg) . '</div>';
        }
        ?>
        
        <form method="post" action="" class="cms-admin2-form" id="cms-admin-create-form">
            <?php wp_nonce_field('cms_admin_registration', 'cms_admin_nonce'); ?>
            
            <!-- Personal Information Section -->
            <div class="cms-admin2-section">
                <h3 class="cms-section2-title">Personal Information</h3>
                
                <div class="cms-form2-grid">
                    <!-- Username -->
                    <div class="cms-form2-group">
                        <label for="cms-username2">
                            Username <?php if($atts['required_field_mark']) echo '<span class="cms-required2">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="text" 
                            id="cms-username2" 
                            name="cms_username" 
                            class="cms-form2-control" 
                            placeholder="Enter username"
                            required
                            autocomplete="off"
                            pattern="[a-zA-Z0-9_]{3,20}"
                            title="Username must be 3-20 characters, can contain letters, numbers and underscores"
                        >
                        <div id="username-check" class="cms-error2-text" style="display:none;"></div>
                    </div>
                    
                    <!-- Full Name -->
                    <div class="cms-form2-group">
                        <label for="cms-fullname2">
                            Full Name <?php if($atts['required_field_mark']) echo '<span class="cms-required2">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="text" 
                            id="cms-fullname2" 
                            name="cms_fullname" 
                            class="cms-form2-control" 
                            placeholder="Enter full name"
                            required
                        >
                    </div>
                    
                    <!-- Email -->
                    <div class="cms-form2-group">
                        <label for="cms-email2">
                            Email Address <?php if($atts['required_field_mark']) echo '<span class="cms-required2">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="email" 
                            id="cms-email2" 
                            name="cms_email" 
                            class="cms-form2-control" 
                            placeholder="Enter email address"
                            required
                            autocomplete="email"
                        >
                        <div id="email-check" class="cms-error2-text" style="display:none;"></div>
                    </div>
                    
                    <!-- Father's Name -->
                    <div class="cms-form2-group">
                        <label for="cms-fathername2">
                            Father's Name <?php if($atts['required_field_mark']) echo '<span class="cms-required2">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="text" 
                            id="cms-fathername2" 
                            name="cms_fathername" 
                            class="cms-form2-control" 
                            placeholder="Enter father's name"
                            required
                        >
                    </div>
                    
                    <!-- Position -->
                    <div class="cms-form2-group">
                        <label for="cms-position2">
                            Position <?php if($atts['required_field_mark']) echo '<span class="cms-required2">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <select id="cms-position2" name="cms_position" class="cms-position-select" required>
                            <option value="">Select Position</option>
                            <option value="Senior Admin">Senior Admin</option>
                            <option value="Junior Admin">Junior Admin</option>
                            <option value="HR Admin">HR Admin</option>
                            <option value="Finance Admin">Finance Admin</option>
                            <option value="Operations Admin">Operations Admin</option>
                            <option value="Support Admin">Support Admin</option>
                            <option value="Technical Admin">Technical Admin</option>
                        </select>
                    </div>
                    
                    <!-- Contact Number -->
                    <div class="cms-form2-group">
                        <label for="cms-contact2">
                            Contact Number <?php if($atts['required_field_mark']) echo '<span class="cms-required2">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <div class="cms-phone2-input">
                            <select name="cms_country_code" class="cms-form2-control cms-country2-code">
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
                                id="cms-contact2" 
                                name="cms_contact" 
                                class="cms-form2-control" 
                                placeholder="Phone number"
                                required
                                pattern="[0-9]{10,15}"
                                title="Please enter a valid phone number"
                            >
                        </div>
                    </div>
                    
                    <!-- Emergency Contact Number -->
                    <div class="cms-form2-group">
                        <label for="cms-emergency2">
                            Emergency Contact Number <?php if($atts['required_field_mark']) echo '<span class="cms-required2">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <div class="cms-phone2-input">
                            <select name="cms_emergency_code" class="cms-form2-control cms-country2-code">
                                <option value="+1">+1</option>
                                <option value="+44">+44</option>
                                <option value="+91">+91</option>
                                <option value="+92">+92</option>
                                <option value="+971">+971</option>
                            </select>
                            <input 
                                type="tel" 
                                id="cms-emergency2" 
                                name="cms_emergency" 
                                class="cms-form2-control" 
                                placeholder="Emergency contact number"
                                required
                                pattern="[0-9]{10,15}"
                            >
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Account Security Section -->
            <div class="cms-admin2-section">
                <h3 class="cms-section2-title">Account Security</h3>
                
                <div class="cms-form2-grid">
                    <!-- Password -->
                    <div class="cms-form2-group">
                        <label for="cms-password2">
                            Password <?php if($atts['required_field_mark']) echo '<span class="cms-required2">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="password" 
                            id="cms-password2" 
                            name="cms_password" 
                            class="cms-form2-control" 
                            placeholder="Enter password"
                            required
                            minlength="8"
                            pattern="(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}"
                            title="Must contain at least one number, one uppercase and lowercase letter, and at least 8 characters"
                        >
                        <div id="password-strength" class="cms-password-strength"></div>
                    </div>
                    
                    <!-- Confirm Password -->
                    <div class="cms-form2-group">
                        <label for="cms-confirm-password2">
                            Confirm Password <?php if($atts['required_field_mark']) echo '<span class="cms-required2">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="password" 
                            id="cms-confirm-password2" 
                            name="cms_confirm_password" 
                            class="cms-form2-control" 
                            placeholder="Confirm password"
                            required
                        >
                    </div>
                </div>
            </div>
            
            <!-- Reference Information Section -->
            <div class="cms-admin2-section">
                <h3 class="cms-section2-title">Reference Information</h3>
                
                <div class="cms-ref2-grid">
                    <!-- Reference 1 -->
                    <div class="cms-ref2-item">
                        <h4 class="cms-ref2-title">Reference #1</h4>
                        <div class="cms-form2-group">
                            <label for="cms-ref1-name2">
                                Reference Name <?php if($atts['required_field_mark']) echo '<span class="cms-required2">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                            </label>
                            <input 
                                type="text" 
                                id="cms-ref1-name2" 
                                name="cms_ref1_name" 
                                class="cms-form2-control" 
                                placeholder="Enter reference name"
                                required
                            >
                        </div>
                        <div class="cms-form2-group">
                            <label for="cms-ref1-cno2">
                                Reference Contact <?php if($atts['required_field_mark']) echo '<span class="cms-required2">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                            </label>
                            <input 
                                type="tel" 
                                id="cms-ref1-cno2" 
                                name="cms_ref1_cno" 
                                class="cms-form2-control" 
                                placeholder="Enter contact number"
                                required
                                pattern="[0-9]{10,15}"
                            >
                        </div>
                    </div>
                    
                    <!-- Reference 2 -->
                    <div class="cms-ref2-item">
                        <h4 class="cms-ref2-title">Reference #2</h4>
                        <div class="cms-form2-group">
                            <label for="cms-ref2-name2">
                                Reference Name <?php if($atts['required_field_mark']) echo '<span class="cms-required2">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                            </label>
                            <input 
                                type="text" 
                                id="cms-ref2-name2" 
                                name="cms_ref2_name" 
                                class="cms-form2-control" 
                                placeholder="Enter reference name"
                                required
                            >
                        </div>
                        <div class="cms-form2-group">
                            <label for="cms-ref2-cno2">
                                Reference Contact <?php if($atts['required_field_mark']) echo '<span class="cms-required2">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                            </label>
                            <input 
                                type="tel" 
                                id="cms-ref2-cno2" 
                                name="cms_ref2_cno" 
                                class="cms-form2-control" 
                                placeholder="Enter contact number"
                                required
                                pattern="[0-9]{10,15}"
                            >
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="cms-submit2-section">
                <button type="submit" name="cms_admin2_submit" class="cms-submit2-button">
                    <?php echo esc_html($atts['button_text']); ?>
                </button>
            </div>
            
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Password strength indicator
        $('#cms-password2').on('keyup', function() {
            var password = $(this).val();
            var strength = checkPasswordStrength(password);
            var strengthText = $('#password-strength');
            
            if (password.length === 0) {
                strengthText.text('');
                strengthText.removeClass('weak medium strong');
            } else if (strength < 3) {
                strengthText.text('Weak password');
                strengthText.removeClass('medium strong').addClass('weak');
            } else if (strength < 4) {
                strengthText.text('Medium password');
                strengthText.removeClass('weak strong').addClass('medium');
            } else {
                strengthText.text('Strong password');
                strengthText.removeClass('weak medium').addClass('strong');
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
        
        // Username availability check
        var usernameTimer;
        $('#cms-username2').on('keyup', function() {
            clearTimeout(usernameTimer);
            var username = $(this).val();
            
            if (username.length >= 3) {
                usernameTimer = setTimeout(function() {
                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        type: 'POST',
                        data: {
                            action: 'cms_check_username',
                            username: username,
                            nonce: '<?php echo wp_create_nonce('cms_check_username'); ?>'
                        },
                        success: function(response) {
                            if (response.data.exists) {
                                $('#cms-username2').addClass('error');
                                $('#username-check').text('Username already exists').show();
                            } else {
                                $('#cms-username2').removeClass('error');
                                $('#username-check').hide();
                            }
                        }
                    });
                }, 500);
            }
        });
        
        // Email availability check
        var emailTimer;
        $('#cms-email2').on('keyup', function() {
            clearTimeout(emailTimer);
            var email = $(this).val();
            
            if (email.length >= 5 && email.includes('@')) {
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
                                $('#cms-email2').addClass('error');
                                $('#email-check').text('Email already exists').show();
                            } else {
                                $('#cms-email2').removeClass('error');
                                $('#email-check').hide();
                            }
                        }
                    });
                }, 500);
            }
        });
        
        // Form validation
        $('#cms-admin-create-form').on('submit', function(e) {
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
            var email = $('#cms-email2');
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email.val() && !emailPattern.test(email.val())) {
                email.addClass('error');
                isValid = false;
                errorMessages.push('Invalid email format');
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
            
            // Validate username
            var username = $('#cms-username2');
            var usernamePattern = /^[a-zA-Z0-9_]{3,20}$/;
            if (username.val() && !usernamePattern.test(username.val())) {
                username.addClass('error');
                isValid = false;
                errorMessages.push('Username must be 3-20 characters and can only contain letters, numbers, and underscores');
            }
            
            // Check if username already exists
            if ($('#username-check').is(':visible')) {
                isValid = false;
                errorMessages.push('Username already exists');
            }
            
            // Check if email already exists
            if ($('#email-check').is(':visible')) {
                isValid = false;
                errorMessages.push('Email already exists');
            }
            
            // Validate password
            var password = $('#cms-password2').val();
            var confirmPassword = $('#cms-confirm-password2').val();
            var passwordPattern = /(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}/;
            
            if (!passwordPattern.test(password)) {
                $('#cms-password2').addClass('error');
                isValid = false;
                errorMessages.push('Password must contain at least one number, one uppercase and lowercase letter, and be at least 8 characters long');
            }
            
            if (password !== confirmPassword) {
                $('#cms-confirm-password2').addClass('error');
                isValid = false;
                errorMessages.push('Passwords do not match');
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fix the following errors:\n- ' + errorMessages.join('\n- '));
                return false;
            }
            
            $(this).find('.cms-submit2-button').prop('disabled', true).text('Processing...');
        });
        
        $('.cms-form2-control').on('input', function() {
            $(this).removeClass('error');
        });
        
        $('select[name="cms_country_code"]').on('change', function() {
            if ($(this).val() === '+other') {
                var customCode = prompt('Enter country code (e.g., +1, +92):');
                if (customCode) {
                    $(this).append('<option value="' + customCode + '" selected>' + customCode + '</option>');
                }
            }
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_admin_form', 'cms_admin_form_shortcode');
add_shortcode(CMS_ADMIN_CREATE_SHORTCODE, 'cms_admin_form_shortcode');

/**
 * Handle Admin Form Submission with Database Integration
 */
function cms_handle_admin_create_submission() {
    if (isset($_POST['cms_admin2_submit'])) {
        
        // Verify nonce
        if (!isset($_POST['cms_admin_nonce']) || !wp_verify_nonce($_POST['cms_admin_nonce'], 'cms_admin_registration')) {
            wp_redirect(add_query_arg('admin_reg', 'error', wp_get_referer()));
            exit;
        }
        
        global $wpdb;
        
        // Sanitize and validate input
        $username = sanitize_user($_POST['cms_username']);
        $email = sanitize_email($_POST['cms_email']);
        $password = $_POST['cms_password']; // Don't sanitize password
        $confirm_password = $_POST['cms_confirm_password'];
        $fullname = sanitize_text_field($_POST['cms_fullname']);
        $fathername = sanitize_text_field($_POST['cms_fathername']);
        $position = sanitize_text_field($_POST['cms_position']);
        $country_code = sanitize_text_field($_POST['cms_country_code']);
        $contact = preg_replace('/[^0-9]/', '', $_POST['cms_contact']);
        $emergency_code = sanitize_text_field($_POST['cms_emergency_code']);
        $emergency = preg_replace('/[^0-9]/', '', $_POST['cms_emergency']);
        $ref1_name = sanitize_text_field($_POST['cms_ref1_name']);
        $ref1_cno = preg_replace('/[^0-9]/', '', $_POST['cms_ref1_cno']);
        $ref2_name = sanitize_text_field($_POST['cms_ref2_name']);
        $ref2_cno = preg_replace('/[^0-9]/', '', $_POST['cms_ref2_cno']);
        
        // Complete phone numbers
        $full_contact = $country_code . $contact;
        $full_emergency = $emergency_code . $emergency;
        $full_ref1_cno = $ref1_cno;
        $full_ref2_cno = $ref2_cno;
        
        // Validation
        $errors = array();
        
        // Check if username exists
        if (cms_username_exists($username)) {
            $errors[] = 'Username already exists';
        }
        
        // Check if email exists
        if (cms_email_exists($email)) {
            $errors[] = 'Email already exists';
        }
        
        // Validate username format
        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            $errors[] = 'Invalid username format';
        }
        
        // Validate email
        if (!is_email($email)) {
            $errors[] = 'Invalid email format';
        }
        
        // Validate password
        $password_pattern = '/^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}$/';
        if (!preg_match($password_pattern, $password)) {
            $errors[] = 'Password must contain at least one number, one uppercase and lowercase letter, and be at least 8 characters long';
        }
        
        // Check if passwords match
        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }
        
        // Validate phone numbers
        if (strlen($contact) < 10 || strlen($contact) > 15) {
            $errors[] = 'Invalid contact number';
        }
        
        if (strlen($emergency) < 10 || strlen($emergency) > 15) {
            $errors[] = 'Invalid emergency contact number';
        }
        
        // Validate reference phone numbers
        if (strlen($ref1_cno) < 10 || strlen($ref1_cno) > 15) {
            $errors[] = 'Invalid reference 1 contact number';
        }
        
        if (strlen($ref2_cno) < 10 || strlen($ref2_cno) > 15) {
            $errors[] = 'Invalid reference 2 contact number';
        }
        
        // If there are errors, redirect back with error message
        if (!empty($errors)) {
            $error_string = implode(', ', $errors);
            wp_redirect(add_query_arg(
                array(
                    'admin_reg' => 'error',
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
            
            // Hash the password
            $hashed_password = wp_hash_password($password);
            
            // 1. Insert into users table
            $user_inserted = $wpdb->insert(
                $table_users,
                array(
                    'username' => $username,
                    'password' => $hashed_password,
                    'role' => 'admin',
                    'status' => 'active',
                    'created_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%s', '%s')
            );
            
            if (!$user_inserted) {
                throw new Exception('Failed to create user account');
            }
            
            // 2. Insert into admin table
            $admin_inserted = $wpdb->insert(
                $table_admin,
                array(
                    'username' => $username,
                    'name' => $fullname,
                    'email' => $email,
                    'father_name' => $fathername,
                    'contact_num' => $full_contact,
                    'emergency_cno' => $full_emergency,
                    'ref1_name' => $ref1_name,
                    'ref1_cno' => $full_ref1_cno,
                    'ref2_name' => $ref2_name,
                    'ref2_cno' => $full_ref2_cno,
                    'position' => $position,
                    'created_at' => current_time('mysql')
                ),
                array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
            );
            
            if (!$admin_inserted) {
                throw new Exception('Failed to create admin profile');
            }
            
            // Commit transaction
            $wpdb->query('COMMIT');
            
            // Redirect with success
            wp_redirect(add_query_arg(
                array(
                    'admin_reg' => 'success',
                    'username' => urlencode($username)
                ), 
                wp_get_referer()
            ));
            exit;
            
        } catch (Exception $e) {
            // Rollback transaction on error
            $wpdb->query('ROLLBACK');
            
            error_log('CMS Admin Creation Error: ' . $e->getMessage());
            
            wp_redirect(add_query_arg(
                array(
                    'admin_reg' => 'error',
                    'error_msg' => urlencode('Registration failed: ' . $e->getMessage())
                ), 
                wp_get_referer()
            ));
            exit;
        }
    }
}
add_action('init', 'cms_handle_admin_create_submission');

/**
 * AJAX handlers for username and email checks
 */
function cms_ajax_check_username() {
    check_ajax_referer('cms_check_username', 'nonce');
    
    $username = sanitize_user($_POST['username']);
    $exists = cms_username_exists($username);
    
    wp_send_json_success(array('exists' => $exists));
}
add_action('wp_ajax_cms_check_username', 'cms_ajax_check_username');
add_action('wp_ajax_nopriv_cms_check_username', 'cms_ajax_check_username');

function cms_ajax_check_email() {
    check_ajax_referer('cms_check_email', 'nonce');
    
    $email = sanitize_email($_POST['email']);
    $exists = cms_email_exists($email);
    
    wp_send_json_success(array('exists' => $exists));
}
add_action('wp_ajax_cms_check_email', 'cms_ajax_check_email');
add_action('wp_ajax_nopriv_cms_check_email', 'cms_ajax_check_email');