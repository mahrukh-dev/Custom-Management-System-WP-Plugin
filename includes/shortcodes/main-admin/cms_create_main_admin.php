<?php
/**
 * CMS Main Admin Form Shortcode
 * Complete form for Main Admin registration/profile
 * 
 * Fields: username, name, email, father_name, contact_num, emergency_cno, 
 *         ref1_name, ref1_cno, ref2_name, ref2_cno
 * 
 * Usage: [cms_main_admin_form]
 * Usage: [cms_main_admin_form title="Register as Main Admin" button_text="Submit Application"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define shortcode slug
if (!defined('CMS_MAIN_ADMIN_CREATE_SHORTCODE')) {
    define('CMS_MAIN_ADMIN_CREATE_SHORTCODE', 'cms_main_admin_create');
}

/**
 * Main Admin Form Shortcode
 */
function cms_main_admin_form_shortcode($atts) {
    // Parse attributes
    $atts = shortcode_atts(
        array(
            'title' => 'Main Admin Registration',
            'description' => 'Please fill in all the details below to register as Main Admin.',
            'button_text' => 'Register Admin',
            'success_message' => 'Registration submitted successfully!',
            'class' => '',
            'show_labels' => 'yes',
            'required_field_mark' => '*'
        ),
        $atts,
        'cms_main_admin_form'
    );
    
    ob_start();
    ?>
    
    <style>
    /* Main Admin Form Styles */
    .cms-admin-form-container {
        max-width: 900px;
        margin: 30px auto;
        padding: 40px;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.08);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    
    .cms-admin-header {
        margin-bottom: 35px;
        text-align: center;
    }
    
    .cms-admin-title {
        margin: 0 0 10px 0;
        font-size: 32px;
        font-weight: 700;
        color: #1a2b3c;
        letter-spacing: -0.5px;
    }
    
    .cms-admin-description {
        margin: 0;
        font-size: 15px;
        color: #6c7a89;
        line-height: 1.6;
    }
    
    .cms-admin-section {
        background: #f8fafc;
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 30px;
        border: 1px solid #e9edf2;
    }
    
    .cms-section-title {
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
    
    .cms-section-title:before {
        content: '';
        width: 4px;
        height: 20px;
        background: #007cba;
        border-radius: 2px;
        display: inline-block;
    }
    
    .cms-form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .cms-form-group {
        margin-bottom: 5px;
    }
    
    .cms-form-group.full-width {
        grid-column: span 2;
    }
    
    .cms-form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #34495e;
        font-size: 14px;
    }
    
    .cms-required {
        color: #e74c3c;
        margin-left: 4px;
    }
    
    .cms-form-control {
        width: 100%;
        padding: 14px 18px;
        font-size: 15px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        transition: all 0.25s ease;
        box-sizing: border-box;
        background: #ffffff;
    }
    
    .cms-form-control:focus {
        outline: none;
        border-color: #007cba;
        box-shadow: 0 0 0 4px rgba(0,124,186,0.05);
        background: #ffffff;
    }
    
    .cms-form-control::placeholder {
        color: #a0b3c2;
        font-size: 14px;
    }
    
    .cms-form-control.error {
        border-color: #e74c3c;
    }
    
    .cms-error-text {
        color: #e74c3c;
        font-size: 12px;
        margin-top: 6px;
        display: block;
    }
    
    .cms-phone-input {
        display: flex;
        gap: 10px;
    }
    
    .cms-country-code {
        width: 100px;
        flex-shrink: 0;
    }
    
    .cms-submit-section {
        text-align: center;
        margin-top: 20px;
    }
    
    .cms-submit-button {
        min-width: 250px;
        padding: 16px 32px;
        background: linear-gradient(145deg, #007cba, #0063a0);
        color: white;
        border: none;
        border-radius: 40px;
        font-size: 17px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(0,124,186,0.2);
    }
    
    .cms-submit-button:hover {
        background: linear-gradient(145deg, #0063a0, #005287);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0,124,186,0.3);
    }
    
    .cms-submit-button:active {
        transform: translateY(0);
    }
    
    .cms-message {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 30px;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .cms-message.success {
        background: #e3f7ec;
        color: #0a5c36;
        border: 1px solid #b8e0c2;
    }
    
    .cms-message.success:before {
        content: '✓';
        font-size: 20px;
        font-weight: bold;
    }
    
    .cms-message.error {
        background: #ffe8e8;
        color: #b34141;
        border: 1px solid #ffc9c9;
    }
    
    .cms-message.error:before {
        content: '⚠';
        font-size: 20px;
    }
    
    /* Reference section special styling */
    .cms-ref-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-top: 15px;
    }
    
    .cms-ref-item {
        background: #ffffff;
        padding: 20px;
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }
    
    .cms-ref-title {
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0 0 15px 0;
        padding-bottom: 10px;
        border-bottom: 1px dashed #cbd5e0;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .cms-admin-form-container {
            padding: 25px;
            margin: 20px 15px;
        }
        
        .cms-form-grid {
            grid-template-columns: 1fr;
        }
        
        .cms-form-group.full-width {
            grid-column: span 1;
        }
        
        .cms-ref-grid {
            grid-template-columns: 1fr;
        }
        
        .cms-admin-title {
            font-size: 26px;
        }
    }
    
    /* Preview mode styling */
    .cms-preview-mode .cms-form-control {
        background: #f8fafc;
        border-color: #e2e8f0;
        color: #2c3e50;
    }
    
    .cms-preview-mode .cms-submit-button {
        background: #94a3b8;
        box-shadow: none;
        cursor: not-allowed;
    }
    
    /* Loading state */
    .cms-submit-button.loading {
        opacity: 0.7;
        cursor: not-allowed;
        position: relative;
        padding-right: 50px;
    }
    
    .cms-submit-button.loading:after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        border: 2px solid #ffffff;
        border-top-color: transparent;
        border-radius: 50%;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        animation: cms-spin 1s linear infinite;
    }
    
    @keyframes cms-spin {
        0% { transform: translateY(-50%) rotate(0deg); }
        100% { transform: translateY(-50%) rotate(360deg); }
    }
    </style>
    
    <div class="cms-admin-form-container <?php echo esc_attr($atts['class']); ?>">
        
        <div class="cms-admin-header">
            <h2 class="cms-admin-title"><?php echo esc_html($atts['title']); ?></h2>
            <?php if (!empty($atts['description'])): ?>
                <p class="cms-admin-description"><?php echo esc_html($atts['description']); ?></p>
            <?php endif; ?>
        </div>
        
        <?php
        // Display messages
        if (isset($_GET['admin_reg']) && $_GET['admin_reg'] === 'success') {
            echo '<div class="cms-message success">' . esc_html($atts['success_message']) . '</div>';
        }
        
        if (isset($_GET['admin_reg']) && $_GET['admin_reg'] === 'error') {
            echo '<div class="cms-message error">Registration failed. Please try again.</div>';
        }
        
        if (isset($_GET['admin_reg']) && $_GET['admin_reg'] === 'validation') {
            echo '<div class="cms-message error">Please fill all required fields correctly.</div>';
        }
        ?>
        
        <form method="post" action="" class="cms-admin-form" enctype="multipart/form-data">
            
            <!-- Personal Information Section -->
            <div class="cms-admin-section">
                <h3 class="cms-section-title">Personal Information</h3>
                
                <div class="cms-form-grid">
                    <!-- Username -->
                    <div class="cms-form-group">
                        <label for="cms-username">
                            Username <?php if($atts['required_field_mark']) echo '<span class="cms-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="text" 
                            id="cms-username" 
                            name="cms_username" 
                            class="cms-form-control" 
                            placeholder="Enter username"
                            required
                            autocomplete="off"
                            pattern="[a-zA-Z0-9_]{3,20}"
                            title="Username must be 3-20 characters, can contain letters, numbers and underscores"
                        >
                    </div>
                    
                    <!-- Full Name -->
                    <div class="cms-form-group">
                        <label for="cms-fullname">
                            Full Name <?php if($atts['required_field_mark']) echo '<span class="cms-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="text" 
                            id="cms-fullname" 
                            name="cms_fullname" 
                            class="cms-form-control" 
                            placeholder="Enter full name"
                            required
                        >
                    </div>
                    
                    <!-- Email -->
                    <div class="cms-form-group">
                        <label for="cms-email">
                            Email Address <?php if($atts['required_field_mark']) echo '<span class="cms-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="email" 
                            id="cms-email" 
                            name="cms_email" 
                            class="cms-form-control" 
                            placeholder="Enter email address"
                            required
                            autocomplete="email"
                        >
                    </div>
                    
                    <!-- Father's Name -->
                    <div class="cms-form-group">
                        <label for="cms-fathername">
                            Father's Name <?php if($atts['required_field_mark']) echo '<span class="cms-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="text" 
                            id="cms-fathername" 
                            name="cms_fathername" 
                            class="cms-form-control" 
                            placeholder="Enter father's name"
                            required
                        >
                    </div>
                    
                    <!-- Contact Number -->
                    <div class="cms-form-group">
                        <label for="cms-contact">
                            Contact Number <?php if($atts['required_field_mark']) echo '<span class="cms-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <div class="cms-phone-input">
                            <select name="cms_country_code" class="cms-form-control cms-country-code">
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
                                id="cms-contact" 
                                name="cms_contact" 
                                class="cms-form-control" 
                                placeholder="Phone number"
                                required
                                pattern="[0-9]{10,15}"
                                title="Please enter a valid phone number"
                            >
                        </div>
                    </div>
                    
                    <!-- Emergency Contact Number -->
                    <div class="cms-form-group">
                        <label for="cms-emergency">
                            Emergency Contact Number <?php if($atts['required_field_mark']) echo '<span class="cms-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <div class="cms-phone-input">
                            <select name="cms_emergency_code" class="cms-form-control cms-country-code">
                                <option value="+1">+1</option>
                                <option value="+44">+44</option>
                                <option value="+91">+91</option>
                                <option value="+92">+92</option>
                                <option value="+971">+971</option>
                            </select>
                            <input 
                                type="tel" 
                                id="cms-emergency" 
                                name="cms_emergency" 
                                class="cms-form-control" 
                                placeholder="Emergency contact number"
                                required
                                pattern="[0-9]{10,15}"
                            >
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Reference Information Section -->
            <div class="cms-admin-section">
                <h3 class="cms-section-title">Reference Information</h3>
                
                <div class="cms-ref-grid">
                    <!-- Reference 1 -->
                    <div class="cms-ref-item">
                        <h4 class="cms-ref-title">Reference #1</h4>
                        <div class="cms-form-group">
                            <label for="cms-ref1-name">
                                Reference Name <?php if($atts['required_field_mark']) echo '<span class="cms-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                            </label>
                            <input 
                                type="text" 
                                id="cms-ref1-name" 
                                name="cms_ref1_name" 
                                class="cms-form-control" 
                                placeholder="Enter reference name"
                                required
                            >
                        </div>
                        <div class="cms-form-group">
                            <label for="cms-ref1-cno">
                                Reference Contact <?php if($atts['required_field_mark']) echo '<span class="cms-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                            </label>
                            <input 
                                type="tel" 
                                id="cms-ref1-cno" 
                                name="cms_ref1_cno" 
                                class="cms-form-control" 
                                placeholder="Enter contact number"
                                required
                                pattern="[0-9]{10,15}"
                            >
                        </div>
                    </div>
                    
                    <!-- Reference 2 -->
                    <div class="cms-ref-item">
                        <h4 class="cms-ref-title">Reference #2</h4>
                        <div class="cms-form-group">
                            <label for="cms-ref2-name">
                                Reference Name <?php if($atts['required_field_mark']) echo '<span class="cms-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                            </label>
                            <input 
                                type="text" 
                                id="cms-ref2-name" 
                                name="cms_ref2_name" 
                                class="cms-form-control" 
                                placeholder="Enter reference name"
                                required
                            >
                        </div>
                        <div class="cms-form-group">
                            <label for="cms-ref2-cno">
                                Reference Contact <?php if($atts['required_field_mark']) echo '<span class="cms-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                            </label>
                            <input 
                                type="tel" 
                                id="cms-ref2-cno" 
                                name="cms_ref2_cno" 
                                class="cms-form-control" 
                                placeholder="Enter contact number"
                                required
                                pattern="[0-9]{10,15}"
                            >
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="cms-submit-section">
                <button type="submit" name="cms_admin_submit" class="cms-submit-button">
                    <?php echo esc_html($atts['button_text']); ?>
                </button>
            </div>
            
        </form>
    </div>
    
    <!-- JavaScript for form validation and enhancement -->
    <script>
    jQuery(document).ready(function($) {
        // Form validation
        $('.cms-admin-form').on('submit', function(e) {
            var isValid = true;
            
            // Check all required fields
            $(this).find('[required]').each(function() {
                if (!$(this).val()) {
                    $(this).addClass('error');
                    isValid = false;
                } else {
                    $(this).removeClass('error');
                }
            });
            
            // Validate email format
            var email = $('#cms-email');
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email.val() && !emailPattern.test(email.val())) {
                email.addClass('error');
                isValid = false;
            }
            
            // Validate phone numbers
            $('input[type="tel"]').each(function() {
                var phone = $(this).val();
                var phonePattern = /^[0-9]{10,15}$/;
                if (phone && !phonePattern.test(phone.replace(/\D/g, ''))) {
                    $(this).addClass('error');
                    isValid = false;
                }
            });
            
            // Validate username format
            var username = $('#cms-username');
            var usernamePattern = /^[a-zA-Z0-9_]{3,20}$/;
            if (username.val() && !usernamePattern.test(username.val())) {
                username.addClass('error');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill all fields correctly.');
                return false;
            }
            
            // Add loading state
            $(this).find('.cms-submit-button').addClass('loading');
        });
        
        // Remove error class on input
        $('.cms-form-control').on('input', function() {
            $(this).removeClass('error');
        });
        
        // Country code toggle
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

add_shortcode('cms_main_admin_form', 'cms_main_admin_form_shortcode');
// Also keep the constant registration if needed elsewhere
add_shortcode(CMS_MAIN_ADMIN_CREATE_SHORTCODE, 'cms_main_admin_form_shortcode');
/**
 * Handle Main Admin Form Submission
 * Add this function if you want to handle form submission
 */
function cms_handle_main_admin_submission() {
    if (isset($_POST['cms_admin_submit'])) {
        
        // Collect all form data
        $admin_data = array(
            'username' => sanitize_user($_POST['cms_username']),
            'fullname' => sanitize_text_field($_POST['cms_fullname']),
            'email' => sanitize_email($_POST['cms_email']),
            'fathername' => sanitize_text_field($_POST['cms_fathername']),
            'country_code' => sanitize_text_field($_POST['cms_country_code']),
            'contact' => preg_replace('/[^0-9]/', '', $_POST['cms_contact']),
            'emergency_code' => sanitize_text_field($_POST['cms_emergency_code']),
            'emergency' => preg_replace('/[^0-9]/', '', $_POST['cms_emergency']),
            'ref1_name' => sanitize_text_field($_POST['cms_ref1_name']),
            'ref1_cno' => preg_replace('/[^0-9]/', '', $_POST['cms_ref1_cno']),
            'ref2_name' => sanitize_text_field($_POST['cms_ref2_name']),
            'ref2_cno' => preg_replace('/[^0-9]/', '', $_POST['cms_ref2_cno']),
            'submitted_at' => current_time('mysql')
        );
        
        // Here you can:
        // 1. Save to custom database table
        // 2. Send email notification
        // 3. Create WordPress user
        // 4. Redirect with success message
        
        // For now, just redirect with success
        wp_redirect(add_query_arg('admin_reg', 'success', wp_get_referer()));
        exit;
    }
}
add_action('init', 'cms_handle_main_admin_submission');

?>