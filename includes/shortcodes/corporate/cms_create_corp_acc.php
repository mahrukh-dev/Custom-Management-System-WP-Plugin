<?php
/**
 * CMS Corporate Account Form Shortcode
 * Complete form for Corporate Account registration
 * 
 * Fields: username, company_name, name, email, phone_no, address, website
 * 
 * Usage: [cms_corp_acc_form]
 * Usage: [cms_corp_acc_form title="Register Corporate Account" button_text="Submit Application"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define shortcode slug
if (!defined('CMS_CORP_ACC_CREATE_SHORTCODE')) {
    define('CMS_CORP_ACC_CREATE_SHORTCODE', 'cms_corp_acc_create');
}

/**
 * Corporate Account Form Shortcode
 */
function cms_corp_acc_form_shortcode($atts) {
    // Parse attributes
    $atts = shortcode_atts(
        array(
            'title' => 'Corporate Account Registration',
            'description' => 'Please fill in all the details below to register a Corporate Account.',
            'button_text' => 'Register Corporate Account',
            'success_message' => 'Corporate account registration submitted successfully!',
            'class' => '',
            'show_labels' => 'yes',
            'required_field_mark' => '*'
        ),
        $atts,
        'cms_corp_acc_form'
    );
    
    ob_start();
    ?>
    
    <style>
    /* Corporate Account Form Styles - Purple/Blue Theme */
    :root {
        --corp-primary: #6c5ce7;
        --corp-primary-dark: #5649c0;
        --corp-primary-light: #a29bfe;
        --corp-secondary: #00cec9;
        --corp-accent: #0984e3;
        --corp-success: #00b894;
        --corp-danger: #d63031;
        --corp-warning: #fdcb6e;
    }
    
    .cms-corp-form-container {
        max-width: 900px;
        margin: 30px auto;
        padding: 40px;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(108,92,231,0.08);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        border-top: 5px solid var(--corp-primary);
    }
    
    .cms-corp-header {
        margin-bottom: 35px;
        text-align: center;
    }
    
    .cms-corp-title {
        margin: 0 0 10px 0;
        font-size: 32px;
        font-weight: 700;
        color: var(--corp-primary-dark);
        letter-spacing: -0.5px;
    }
    
    .cms-corp-description {
        margin: 0;
        font-size: 15px;
        color: #6c7a89;
        line-height: 1.6;
    }
    
    .cms-corp-section {
        background: #f5f0ff;
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 30px;
        border: 1px solid #d9d0ff;
        position: relative;
    }
    
    .cms-corp-section-title {
        margin: 0 0 20px 0;
        font-size: 20px;
        font-weight: 600;
        color: var(--corp-primary-dark);
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 15px;
        border-bottom: 2px solid #d9d0ff;
    }
    
    .cms-corp-section-title:before {
        content: '';
        width: 4px;
        height: 20px;
        background: var(--corp-primary);
        border-radius: 2px;
        display: inline-block;
    }
    
    .cms-corp-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .cms-corp-group {
        margin-bottom: 5px;
    }
    
    .cms-corp-group.full-width {
        grid-column: span 2;
    }
    
    .cms-corp-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #34495e;
        font-size: 14px;
    }
    
    .cms-corp-required {
        color: var(--corp-danger);
        margin-left: 4px;
    }
    
    .cms-corp-control {
        width: 100%;
        padding: 14px 18px;
        font-size: 15px;
        border: 2px solid #d9d0ff;
        border-radius: 12px;
        transition: all 0.25s ease;
        box-sizing: border-box;
        background: #ffffff;
    }
    
    .cms-corp-control:focus {
        outline: none;
        border-color: var(--corp-primary);
        box-shadow: 0 0 0 4px rgba(108,92,231,0.05);
        background: #ffffff;
    }
    
    .cms-corp-control::placeholder {
        color: #a0b3c2;
        font-size: 14px;
    }
    
    .cms-corp-control.error {
        border-color: var(--corp-danger);
    }
    
    .cms-corp-error-text {
        color: var(--corp-danger);
        font-size: 12px;
        margin-top: 6px;
        display: block;
    }
    
    .cms-corp-phone-input {
        display: flex;
        gap: 10px;
    }
    
    .cms-corp-country-code {
        width: 120px;
        flex-shrink: 0;
    }
    
    .cms-corp-textarea {
        width: 100%;
        padding: 14px 18px;
        font-size: 15px;
        border: 2px solid #d9d0ff;
        border-radius: 12px;
        transition: all 0.25s ease;
        box-sizing: border-box;
        background: #ffffff;
        min-height: 100px;
        resize: vertical;
        font-family: inherit;
    }
    
    .cms-corp-textarea:focus {
        outline: none;
        border-color: var(--corp-primary);
        box-shadow: 0 0 0 4px rgba(108,92,231,0.05);
    }
    
    .cms-corp-website-input {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cms-corp-website-prefix {
        padding: 14px 18px;
        background: #f5f0ff;
        border: 2px solid #d9d0ff;
        border-right: none;
        border-radius: 12px 0 0 12px;
        color: var(--corp-primary-dark);
        font-weight: 500;
        white-space: nowrap;
    }
    
    .cms-corp-website-field {
        flex: 1;
        border-radius: 0 12px 12px 0;
    }
    
    .cms-corp-submit-section {
        text-align: center;
        margin-top: 20px;
    }
    
    .cms-corp-submit-button {
        min-width: 280px;
        padding: 16px 32px;
        background: linear-gradient(145deg, var(--corp-primary), var(--corp-primary-dark));
        color: white;
        border: none;
        border-radius: 40px;
        font-size: 17px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        letter-spacing: 0.5px;
        box-shadow: 0 4px 15px rgba(108,92,231,0.2);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
    }
    
    .cms-corp-submit-button:hover {
        background: linear-gradient(145deg, var(--corp-primary-dark), #4338b0);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(108,92,231,0.3);
    }
    
    .cms-corp-submit-button:active {
        transform: translateY(0);
    }
    
    .cms-corp-submit-button i {
        font-size: 20px;
    }
    
    .cms-corp-message {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 30px;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .cms-corp-message.success {
        background: #e3f7ec;
        color: #0a5c36;
        border: 1px solid #b8e0c2;
    }
    
    .cms-corp-message.success:before {
        content: '✓';
        font-size: 20px;
        font-weight: bold;
    }
    
    .cms-corp-message.error {
        background: #ffe8e8;
        color: #b34141;
        border: 1px solid #ffc9c9;
    }
    
    .cms-corp-message.error:before {
        content: '⚠';
        font-size: 20px;
    }
    
    .cms-corp-info-box {
        background: #f5f0ff;
        border-left: 4px solid var(--corp-primary);
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        font-size: 14px;
        color: #2c3e50;
    }
    
    .cms-corp-progress {
        width: 100%;
        height: 4px;
        background: #d9d0ff;
        border-radius: 2px;
        margin-top: 20px;
        overflow: hidden;
    }
    
    .cms-corp-progress-bar {
        height: 100%;
        background: var(--corp-primary);
        width: 0%;
        transition: width 0.3s ease;
    }
    
    /* Company avatar/logo placeholder */
    .cms-corp-avatar-preview {
        width: 60px;
        height: 60px;
        background: linear-gradient(145deg, var(--corp-primary), var(--corp-primary-dark));
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 24px;
        font-weight: 600;
        margin-right: 15px;
    }
    
    .cms-corp-field-hint {
        font-size: 12px;
        color: #718096;
        margin-top: 5px;
    }
    
    @media (max-width: 768px) {
        .cms-corp-form-container {
            padding: 25px;
            margin: 20px 15px;
        }
        
        .cms-corp-grid {
            grid-template-columns: 1fr;
        }
        
        .cms-corp-group.full-width {
            grid-column: span 1;
        }
        
        .cms-corp-title {
            font-size: 26px;
        }
        
        .cms-corp-phone-input {
            flex-direction: column;
        }
        
        .cms-corp-country-code {
            width: 100%;
        }
        
        .cms-corp-website-input {
            flex-direction: column;
            align-items: stretch;
        }
        
        .cms-corp-website-prefix {
            border-radius: 12px;
            border-right: 2px solid #d9d0ff;
            text-align: center;
        }
        
        .cms-corp-website-field {
            border-radius: 12px;
        }
    }
    </style>
    
    <div class="cms-corp-form-container <?php echo esc_attr($atts['class']); ?>">
        
        <div class="cms-corp-header">
            <h2 class="cms-corp-title"><?php echo esc_html($atts['title']); ?></h2>
            <?php if (!empty($atts['description'])): ?>
                <p class="cms-corp-description"><?php echo esc_html($atts['description']); ?></p>
            <?php endif; ?>
        </div>
        
        <?php
        // Display messages
        if (isset($_GET['corp_reg']) && $_GET['corp_reg'] === 'success') {
            echo '<div class="cms-corp-message success">' . esc_html($atts['success_message']) . '</div>';
        }
        
        if (isset($_GET['corp_reg']) && $_GET['corp_reg'] === 'error') {
            echo '<div class="cms-corp-message error">Registration failed. Please try again.</div>';
        }
        
        if (isset($_GET['corp_reg']) && $_GET['corp_reg'] === 'validation') {
            echo '<div class="cms-corp-message error">Please fill all required fields correctly.</div>';
        }
        
        if (isset($_GET['corp_reg']) && $_GET['corp_reg'] === 'duplicate') {
            echo '<div class="cms-corp-message error">A corporate account with this email or username already exists.</div>';
        }
        ?>
        
        <form method="post" action="" class="cms-corp-form" enctype="multipart/form-data">
            <?php wp_nonce_field('cms_corp_account_registration', 'cms_corp_nonce'); ?>
            
            <!-- Account Information Section -->
            <div class="cms-corp-section">
                <h3 class="cms-corp-section-title">🏢 Account Information</h3>
                
                <div class="cms-corp-grid">
                    <!-- Username -->
                    <div class="cms-corp-group">
                        <label for="corp-username">
                            Username <?php if($atts['required_field_mark']) echo '<span class="cms-corp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="text" 
                            id="corp-username" 
                            name="corp_username" 
                            class="cms-corp-control" 
                            placeholder="Enter username (e.g., company_abc)"
                            required
                            autocomplete="off"
                            pattern="[a-zA-Z0-9_]{3,30}"
                            title="Username must be 3-30 characters, can contain letters, numbers and underscores"
                        >
                        <div class="cms-corp-field-hint">Used for login: 3-30 characters, letters, numbers, underscore</div>
                    </div>
                    
                    <!-- Company Name -->
                    <div class="cms-corp-group">
                        <label for="corp-company">
                            Company Name <?php if($atts['required_field_mark']) echo '<span class="cms-corp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="text" 
                            id="corp-company" 
                            name="corp_company" 
                            class="cms-corp-control" 
                            placeholder="Enter company name"
                            required
                        >
                    </div>
                    
                    <!-- Contact Person Name -->
                    <div class="cms-corp-group">
                        <label for="corp-name">
                            Contact Person Name <?php if($atts['required_field_mark']) echo '<span class="cms-corp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="text" 
                            id="corp-name" 
                            name="corp_name" 
                            class="cms-corp-control" 
                            placeholder="Enter contact person full name"
                            required
                        >
                    </div>
                    
                    <!-- Email -->
                    <div class="cms-corp-group">
                        <label for="corp-email">
                            Email Address <?php if($atts['required_field_mark']) echo '<span class="cms-corp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <input 
                            type="email" 
                            id="corp-email" 
                            name="corp_email" 
                            class="cms-corp-control" 
                            placeholder="Enter email address"
                            required
                            autocomplete="email"
                        >
                        <div class="cms-corp-field-hint">Primary contact email for the company</div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Information Section -->
            <div class="cms-corp-section">
                <h3 class="cms-corp-section-title">📞 Contact Information</h3>
                
                <div class="cms-corp-grid">
                    <!-- Phone Number -->
                    <div class="cms-corp-group full-width">
                        <label for="corp-phone">
                            Phone Number <?php if($atts['required_field_mark']) echo '<span class="cms-corp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <div class="cms-corp-phone-input">
                            <select name="corp_country_code" class="cms-corp-control cms-corp-country-code" id="corp-country-code">
                                <option value="+1">+1 (USA/Canada)</option>
                                <option value="+44">+44 (UK)</option>
                                <option value="+91">+91 (India)</option>
                                <option value="+92">+92 (Pakistan)</option>
                                <option value="+971">+971 (UAE)</option>
                                <option value="+966">+966 (Saudi Arabia)</option>
                                <option value="+20">+20 (Egypt)</option>
                                <option value="+65">+65 (Singapore)</option>
                                <option value="+86">+86 (China)</option>
                                <option value="+81">+81 (Japan)</option>
                                <option value="+49">+49 (Germany)</option>
                                <option value="+33">+33 (France)</option>
                                <option value="+61">+61 (Australia)</option>
                                <option value="+other">Other</option>
                            </select>
                            <input 
                                type="tel" 
                                id="corp-phone" 
                                name="corp_phone" 
                                class="cms-corp-control" 
                                placeholder="Phone number (without country code)"
                                required
                                pattern="[0-9\s\-\(\)]{8,20}"
                                title="Please enter a valid phone number"
                            >
                        </div>
                    </div>
                    
                    <!-- Address -->
                    <div class="cms-corp-group full-width">
                        <label for="corp-address">
                            Business Address <?php if($atts['required_field_mark']) echo '<span class="cms-corp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <textarea 
                            id="corp-address" 
                            name="corp_address" 
                            class="cms-corp-textarea" 
                            placeholder="Enter complete business address"
                            required
                        ></textarea>
                    </div>
                    
                    <!-- Website -->
                    <div class="cms-corp-group full-width">
                        <label for="corp-website">
                            Website URL <?php if($atts['required_field_mark']) echo '<span class="cms-corp-required">' . esc_html($atts['required_field_mark']) . '</span>'; ?>
                        </label>
                        <div class="cms-corp-website-input">
                            <span class="cms-corp-website-prefix">https://</span>
                            <input 
                                type="url" 
                                id="corp-website" 
                                name="corp_website" 
                                class="cms-corp-control cms-corp-website-field" 
                                placeholder="www.example.com"
                                required
                                pattern="^(www\.)?[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(\.[a-zA-Z]{2,})?$"
                                title="Please enter a valid domain name (e.g., www.example.com or example.com)"
                            >
                        </div>
                        <div class="cms-corp-field-hint">Enter without https:// (e.g., www.company.com or company.com)</div>
                    </div>
                </div>
            </div>
            
            <!-- Additional Information Section -->
            <div class="cms-corp-section">
                <h3 class="cms-corp-section-title">📋 Additional Information</h3>
                
                <div class="cms-corp-grid">
                    <div class="cms-corp-group">
                        <label for="corp-industry">Industry Type</label>
                        <select id="corp-industry" name="corp_industry" class="cms-corp-control">
                            <option value="">Select Industry</option>
                            <option value="technology">Technology / IT</option>
                            <option value="finance">Finance / Banking</option>
                            <option value="healthcare">Healthcare</option>
                            <option value="education">Education</option>
                            <option value="manufacturing">Manufacturing</option>
                            <option value="retail">Retail / E-commerce</option>
                            <option value="realestate">Real Estate</option>
                            <option value="construction">Construction</option>
                            <option value="transportation">Transportation / Logistics</option>
                            <option value="hospitality">Hospitality / Tourism</option>
                            <option value="media">Media / Entertainment</option>
                            <option value="consulting">Consulting</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    
                    <div class="cms-corp-group">
                        <label for="corp-size">Company Size</label>
                        <select id="corp-size" name="corp_size" class="cms-corp-control">
                            <option value="">Select Size</option>
                            <option value="1-10">1-10 employees</option>
                            <option value="11-50">11-50 employees</option>
                            <option value="51-200">51-200 employees</option>
                            <option value="201-500">201-500 employees</option>
                            <option value="501-1000">501-1000 employees</option>
                            <option value="1000+">1000+ employees</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Submit Button -->
            <div class="cms-corp-submit-section">
                <div class="cms-corp-progress" style="display: none;" id="corp-upload-progress">
                    <div class="cms-corp-progress-bar" id="corp-upload-progress-bar"></div>
                </div>
                <button type="submit" name="corp_submit" class="cms-corp-submit-button" id="corp-submit-btn">
                    <span>🏢</span> <?php echo esc_html($atts['button_text']); ?>
                </button>
            </div>
            
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Form validation
        $('.cms-corp-form').on('submit', function(e) {
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
            var email = $('#corp-email');
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email.val() && !emailPattern.test(email.val())) {
                email.addClass('error');
                isValid = false;
            }
            
            // Validate username format
            var username = $('#corp-username');
            var usernamePattern = /^[a-zA-Z0-9_]{3,30}$/;
            if (username.val() && !usernamePattern.test(username.val())) {
                username.addClass('error');
                isValid = false;
            }
            
            // Validate website format
            var website = $('#corp-website');
            var websitePattern = /^(www\.)?[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(\.[a-zA-Z]{2,})?$/;
            if (website.val() && !websitePattern.test(website.val())) {
                website.addClass('error');
                alert('Please enter a valid website domain (e.g., www.example.com)');
                isValid = false;
            }
            
            // Validate phone number
            var phone = $('#corp-phone');
            var phonePattern = /^[0-9\s\-\(\)]{8,20}$/;
            if (phone.val() && !phonePattern.test(phone.val())) {
                phone.addClass('error');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill all required fields correctly.');
                return false;
            }
            
            // Show progress
            $('#corp-upload-progress').show();
            var progress = 0;
            var interval = setInterval(function() {
                progress += 10;
                $('#corp-upload-progress-bar').css('width', progress + '%');
                if (progress >= 100) {
                    clearInterval(interval);
                }
            }, 150);
            
            // Disable button
            $('#corp-submit-btn').addClass('loading').prop('disabled', true);
        });
        
        // Remove error class on input
        $('.cms-corp-control').on('input change', function() {
            $(this).removeClass('error');
        });
        
        // Country code toggle
        $('#corp-country-code').on('change', function() {
            if ($(this).val() === '+other') {
                var customCode = prompt('Enter country code (e.g., +1, +92, +44):');
                if (customCode) {
                    // Validate country code format
                    if (/^\+\d{1,3}$/.test(customCode)) {
                        $(this).append('<option value="' + customCode + '" selected>' + customCode + '</option>');
                    } else {
                        alert('Please enter a valid country code format (e.g., +1, +44, +92)');
                        $(this).val('+1');
                    }
                } else {
                    $(this).val('+1');
                }
            }
        });
        
        // Auto-format website input (remove http:// or https:// if user pastes full URL)
        $('#corp-website').on('blur', function() {
            var url = $(this).val();
            url = url.replace(/^https?:\/\//i, '');
            url = url.replace(/^www\./i, '');
            $(this).val(url);
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_corp_acc_form', 'cms_corp_acc_form_shortcode');
add_shortcode(CMS_CORP_ACC_CREATE_SHORTCODE, 'cms_corp_acc_form_shortcode');

/**
 * Handle Corporate Account Form Submission
 */
function cms_handle_corp_acc_submission() {
    if (isset($_POST['corp_submit'])) {
        
        // Verify nonce
        if (!isset($_POST['cms_corp_nonce']) || !wp_verify_nonce($_POST['cms_corp_nonce'], 'cms_corp_account_registration')) {
            wp_redirect(add_query_arg('corp_reg', 'error', wp_get_referer()));
            exit;
        }
        
        // Collect all form data
        $corp_data = array(
            'username' => sanitize_user($_POST['corp_username']),
            'company_name' => sanitize_text_field($_POST['corp_company']),
            'contact_name' => sanitize_text_field($_POST['corp_name']),
            'email' => sanitize_email($_POST['corp_email']),
            'country_code' => sanitize_text_field($_POST['corp_country_code']),
            'phone' => sanitize_text_field($_POST['corp_phone']),
            'address' => sanitize_textarea_field($_POST['corp_address']),
            'website' => sanitize_text_field(strtolower($_POST['corp_website'])),
            'industry' => isset($_POST['corp_industry']) ? sanitize_text_field($_POST['corp_industry']) : '',
            'company_size' => isset($_POST['corp_size']) ? sanitize_text_field($_POST['corp_size']) : '',
            'submitted_at' => current_time('mysql'),
            'status' => 'active'
        );
        
        // For now, just redirect with success
        wp_redirect(add_query_arg('corp_reg', 'success', wp_get_referer()));
        exit;
    }
}
add_action('init', 'cms_handle_corp_acc_submission');

?>