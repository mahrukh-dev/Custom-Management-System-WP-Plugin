<?php
/**
 * CMS Admin Form Shortcode
 * Complete form for Admin registration/profile
 * 
 * Fields: username, name, email, father_name, contact_num, emergency_cno, 
 *         ref1_name, ref1_cno, ref2_name, ref2_cno, position
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
            'success_message' => 'Admin registration submitted successfully!',
            'class' => '',
            'show_labels' => 'yes',
            'required_field_mark' => '*'
        ),
        $atts,
        'cms_admin_form'
    );
    
    ob_start();
    ?>
    
    <style>
    /* Admin Form Styles - Reuse similar styles from main admin */
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
            echo '<div class="cms-message2 success">' . esc_html($atts['success_message']) . '</div>';
        }
        
        if (isset($_GET['admin_reg']) && $_GET['admin_reg'] === 'error') {
            echo '<div class="cms-message2 error">Registration failed. Please try again.</div>';
        }
        
        if (isset($_GET['admin_reg']) && $_GET['admin_reg'] === 'validation') {
            echo '<div class="cms-message2 error">Please fill all required fields correctly.</div>';
        }
        ?>
        
        <form method="post" action="" class="cms-admin2-form" enctype="multipart/form-data">
            
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
        // Form validation
        $('.cms-admin2-form').on('submit', function(e) {
            var isValid = true;
            
            $(this).find('[required]').each(function() {
                if (!$(this).val()) {
                    $(this).addClass('error');
                    isValid = false;
                } else {
                    $(this).removeClass('error');
                }
            });
            
            var email = $('#cms-email2');
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email.val() && !emailPattern.test(email.val())) {
                email.addClass('error');
                isValid = false;
            }
            
            $('input[type="tel"]').each(function() {
                var phone = $(this).val();
                var phonePattern = /^[0-9]{10,15}$/;
                if (phone && !phonePattern.test(phone.replace(/\D/g, ''))) {
                    $(this).addClass('error');
                    isValid = false;
                }
            });
            
            var username = $('#cms-username2');
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
            
            $(this).find('.cms-submit2-button').addClass('loading');
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
 * Handle Admin Form Submission
 */
function cms_handle_admin_submission() {
    if (isset($_POST['cms_admin2_submit'])) {
        
        $admin_data = array(
            'username' => sanitize_user($_POST['cms_username']),
            'fullname' => sanitize_text_field($_POST['cms_fullname']),
            'email' => sanitize_email($_POST['cms_email']),
            'fathername' => sanitize_text_field($_POST['cms_fathername']),
            'position' => sanitize_text_field($_POST['cms_position']),
            'country_code' => sanitize_text_field($_POST['cms_country_code']),
            'contact' => preg_replace('/[^0-9]/', '', $_POST['cms_contact']),
            'emergency_code' => sanitize_text_field($_POST['cms_emergency_code']),
            'emergency' => preg_replace('/[^0-9]/', '', $_POST['cms_emergency']),
            'ref1_name' => sanitize_text_field($_POST['cms_ref1_name']),
            'ref1_cno' => preg_replace('/[^0-9]/', '', $_POST['cms_ref1_cno']),
            'ref2_name' => sanitize_text_field($_POST['cms_ref2_name']),
            'ref2_cno' => preg_replace('/[^0-9]/', '', $_POST['cms_ref2_cno']),
            'submitted_at' => current_time('mysql'),
            'status' => 'pending'
        );
        
        wp_redirect(add_query_arg('admin_reg', 'success', wp_get_referer()));
        exit;
    }
}
add_action('init', 'cms_handle_admin_submission');

?>