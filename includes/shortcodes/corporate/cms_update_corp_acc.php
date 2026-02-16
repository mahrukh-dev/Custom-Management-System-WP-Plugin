<?php
/**
 * CMS Update Corporate Account Shortcode
 * Form to update existing corporate account data with database integration
 * 
 * Usage: [cms_update_corp_acc]
 * Usage: [cms_update_corp_acc corp_id="301"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define shortcode slug
if (!defined('CMS_CORP_ACC_UPDATE_SHORTCODE')) {
    define('CMS_CORP_ACC_UPDATE_SHORTCODE', 'cms_corp_acc_update');
}

/**
 * Corporate Account Update Form Shortcode
 */
function cms_update_corp_acc_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'corp_id' => 0,
            'title' => 'Update Corporate Account',
            'button_text' => 'Update Account',
            'success_message' => 'Corporate account updated successfully!',
            'class' => ''
        ),
        $atts,
        'cms_update_corp_acc'
    );
    
    $corp_id = $atts['corp_id'];
    if (!$corp_id) {
        $corp_id = get_query_var('corp_id');
        if (!$corp_id && isset($_GET['corp_id'])) {
            $corp_id = intval($_GET['corp_id']);
        }
    }
    
    if (!$corp_id) {
        return '<div style="padding: 20px; background: #ffe8e8; color: #b34141; border-radius: 8px; text-align: center;">No corporate account selected. Please provide an account ID.</div>';
    }
    
    // Get corporate account from database
    $corp = cms_get_corporate_account_by_id($corp_id);
    
    if (!$corp) {
        return '<div style="padding: 20px; background: #ffe8e8; color: #b34141; border-radius: 8px; text-align: center;">Corporate account not found.</div>';
    }
    
    ob_start();
    ?>
    
    <style>
    /* Corporate Account Update Styles - Matching Create Form Theme */
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
    
    .cms-corp-update-container {
        max-width: 900px;
        margin: 30px auto;
        padding: 40px;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(108,92,231,0.08);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        border-top: 5px solid var(--corp-primary);
    }
    
    .cms-corp-update-header {
        margin-bottom: 35px;
        text-align: center;
        position: relative;
    }
    
    .cms-corp-update-title {
        margin: 0 0 10px 0;
        font-size: 32px;
        font-weight: 700;
        color: var(--corp-primary-dark);
        letter-spacing: -0.5px;
    }
    
    .cms-corp-back-link {
        position: absolute;
        left: 0;
        top: 50%;
        transform: translateY(-50%);
        padding: 10px 20px;
        background: #f5f0ff;
        color: #4a5568;
        text-decoration: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        border: 1px solid #d9d0ff;
    }
    
    .cms-corp-back-link:hover {
        background: #d9d0ff;
        color: var(--corp-primary-dark);
    }
    
    .cms-corp-update-section {
        background: #f5f0ff;
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 30px;
        border: 1px solid #d9d0ff;
        position: relative;
    }
    
    .cms-corp-update-section-title {
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
    
    .cms-corp-update-section-title:before {
        content: '';
        width: 4px;
        height: 20px;
        background: var(--corp-primary);
        border-radius: 2px;
        display: inline-block;
    }
    
    .cms-corp-form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .cms-corp-form-group {
        margin-bottom: 5px;
    }
    
    .cms-corp-form-group.full-width {
        grid-column: span 2;
    }
    
    .cms-corp-form-group label {
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
    
    .cms-corp-form-control {
        width: 100%;
        padding: 14px 18px;
        font-size: 15px;
        border: 2px solid #d9d0ff;
        border-radius: 12px;
        transition: all 0.25s ease;
        box-sizing: border-box;
        background: #ffffff;
    }
    
    .cms-corp-form-control:focus {
        outline: none;
        border-color: var(--corp-primary);
        box-shadow: 0 0 0 4px rgba(108,92,231,0.05);
        background: #ffffff;
    }
    
    .cms-corp-form-control[readonly] {
        background: #f5f0ff;
        border-color: #d9d0ff;
        color: #718096;
        cursor: not-allowed;
    }
    
    .cms-corp-form-control.error {
        border-color: var(--corp-danger);
    }
    
    .cms-corp-form-control.success {
        border-color: var(--corp-success);
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
    
    .cms-corp-phone-group {
        display: flex;
        gap: 10px;
    }
    
    .cms-corp-country-code {
        width: 120px;
        flex-shrink: 0;
    }
    
    .cms-corp-website-input {
        width: 100%;
    }
    
    .cms-corp-website-field {
        width: 100%;
        border-radius: 12px;
    }
    
    .cms-corp-update-footer {
        margin-top: 30px;
        display: flex;
        gap: 15px;
        justify-content: flex-end;
        align-items: center;
    }
    
    .cms-corp-update-button {
        min-width: 200px;
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
    
    .cms-corp-update-button:hover {
        background: linear-gradient(145deg, var(--corp-primary-dark), #4338b0);
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(108,92,231,0.3);
    }
    
    .cms-corp-update-button:active {
        transform: translateY(0);
    }
    
    .cms-corp-update-button:disabled {
        opacity: 0.7;
        cursor: not-allowed;
        transform: none;
    }
    
    .cms-corp-cancel-button {
        padding: 16px 32px;
        background: #f5f0ff;
        color: #4a5568;
        border: 2px solid #d9d0ff;
        border-radius: 40px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .cms-corp-cancel-button:hover {
        background: #d9d0ff;
        border-color: var(--corp-primary);
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
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .cms-corp-updated-badge {
        background: var(--corp-success);
        color: white;
        padding: 5px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .cms-corp-progress-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(255, 255, 255, 0.9);
        z-index: 9999;
        display: none;
        justify-content: center;
        align-items: center;
        flex-direction: column;
    }
    
    .cms-corp-spinner {
        width: 50px;
        height: 50px;
        border: 4px solid #f5f0ff;
        border-top: 4px solid var(--corp-primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 20px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    .cms-corp-progress-text {
        font-size: 18px;
        color: var(--corp-primary-dark);
        font-weight: 500;
    }
    
    .cms-corp-progress-subtext {
        font-size: 14px;
        color: #718096;
        margin-top: 10px;
    }
    
    .cms-corp-field-hint {
        font-size: 12px;
        color: #718096;
        margin-top: 5px;
    }
    
    .cms-corp-last-updated {
        font-size: 13px;
        color: #718096;
        margin-top: 5px;
        text-align: right;
    }
    
    @media (max-width: 768px) {
        .cms-corp-update-container {
            padding: 25px;
            margin: 20px 15px;
        }
        
        .cms-corp-update-header {
            padding-top: 50px;
        }
        
        .cms-corp-back-link {
            position: static;
            transform: none;
            margin-bottom: 15px;
            display: inline-block;
        }
        
        .cms-corp-form-grid {
            grid-template-columns: 1fr;
        }
        
        .cms-corp-form-group.full-width {
            grid-column: span 1;
        }
        
        .cms-corp-update-title {
            font-size: 26px;
        }
        
        .cms-corp-phone-group {
            flex-direction: column;
        }
        
        .cms-corp-country-code {
            width: 100%;
        }
        
        .cms-corp-update-footer {
            flex-direction: column-reverse;
        }
        
        .cms-corp-update-button,
        .cms-corp-cancel-button {
            width: 100%;
            text-align: center;
        }
    }
    </style>
    
    <!-- Progress Overlay -->
    <div class="cms-corp-progress-overlay" id="update-progress-overlay">
        <div class="cms-corp-spinner"></div>
        <div class="cms-corp-progress-text">Updating Account...</div>
        <div class="cms-corp-progress-subtext">Please wait while we save your changes</div>
    </div>
    
    <div class="cms-corp-update-container <?php echo esc_attr($atts['class']); ?>" id="corp-update-container">
        
        <div class="cms-corp-update-header">
            <a href="<?php echo esc_url(remove_query_arg(array('corp_id', 'update'), wp_get_referer())); ?>" class="cms-corp-back-link">
                ← Back to List
            </a>
            <h2 class="cms-corp-update-title"><?php echo esc_html($atts['title']); ?></h2>
        </div>
        
        <!-- Status Message Area -->
        <div id="update-status-message"></div>
        
        <?php
        // Display messages from URL parameters (for page reload scenarios)
        if (isset($_GET['update']) && $_GET['update'] === 'success') {
            echo '<div class="cms-corp-message success" id="url-success-message">' . esc_html($atts['success_message']) . '</div>';
        }
        
        if (isset($_GET['update']) && $_GET['update'] === 'error') {
            echo '<div class="cms-corp-message error" id="url-error-message">Update failed. Please try again.</div>';
        }
        
        if (isset($_GET['update']) && $_GET['update'] === 'duplicate') {
            echo '<div class="cms-corp-message error" id="url-duplicate-message">Email already exists for another account.</div>';
        }
        
        if (isset($_GET['update']) && $_GET['update'] === 'validation') {
            echo '<div class="cms-corp-message error" id="url-validation-message">Please fill all required fields correctly.</div>';
        }
        ?>
        
        <div class="cms-corp-info-box">
            <div>
                <strong>🏢 Editing Corporate Account:</strong> <?php echo esc_html($corp->company_name); ?> 
                (ID: <?php echo esc_html($corp_id); ?> | Username: <?php echo esc_html($corp->username); ?>)
            </div>
            <?php if (isset($_GET['update']) && $_GET['update'] === 'success'): ?>
                <span class="cms-corp-updated-badge">✓ Updated Just Now</span>
            <?php endif; ?>
        </div>
        
        <form method="post" action="" class="cms-corp-form" id="cms-corp-update-form">
            <?php wp_nonce_field('cms_corp_account_update', 'cms_corp_update_nonce'); ?>
            <input type="hidden" name="cms_corp_id" value="<?php echo esc_attr($corp_id); ?>" id="corp-id-field">
            <input type="hidden" name="cms_original_username" value="<?php echo esc_attr($corp->username); ?>">
            <input type="hidden" name="action" value="cms_update_corporate_account">
            
            <!-- Account Information Section -->
            <div class="cms-corp-update-section">
                <h3 class="cms-corp-update-section-title">🏢 Account Information</h3>
                
                <div class="cms-corp-form-grid">
                    <!-- Username - Read Only -->
                    <div class="cms-corp-form-group">
                        <label for="corp-username">
                            Username <span class="cms-corp-required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="corp-username" 
                            name="corp_username" 
                            class="cms-corp-form-control" 
                            value="<?php echo esc_attr($corp->username); ?>"
                            readonly
                        >
                        <div class="cms-corp-field-hint">Username cannot be changed</div>
                    </div>
                    
                    <!-- Company Name -->
                    <div class="cms-corp-form-group">
                        <label for="corp-company">
                            Company Name <span class="cms-corp-required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="corp-company" 
                            name="corp_company" 
                            class="cms-corp-form-control" 
                            value="<?php echo esc_attr($corp->company_name); ?>"
                            required
                        >
                    </div>
                    
                    <!-- Contact Person Name -->
                    <div class="cms-corp-form-group">
                        <label for="corp-name">
                            Contact Person Name <span class="cms-corp-required">*</span>
                        </label>
                        <input 
                            type="text" 
                            id="corp-name" 
                            name="corp_name" 
                            class="cms-corp-form-control" 
                            value="<?php echo esc_attr($corp->name); ?>"
                            required
                        >
                    </div>
                    
                    <!-- Email -->
                    <div class="cms-corp-form-group">
                        <label for="corp-email">
                            Email Address <span class="cms-corp-required">*</span>
                        </label>
                        <input 
                            type="email" 
                            id="corp-email" 
                            name="corp_email" 
                            class="cms-corp-form-control" 
                            value="<?php echo esc_attr($corp->email); ?>"
                            required
                        >
                        <div class="cms-corp-field-hint">Primary contact email for the company</div>
                    </div>
                </div>
            </div>
            
            <!-- Contact Information Section -->
            <div class="cms-corp-update-section">
                <h3 class="cms-corp-update-section-title">📞 Contact Information</h3>
                
                <div class="cms-corp-form-grid">
                    <!-- Phone Number -->
                    <div class="cms-corp-form-group full-width">
                        <label for="corp-phone">
                            Phone Number <span class="cms-corp-required">*</span>
                        </label>
                        <?php
                        // Parse phone to get country code and number
                        $phone_parts = explode(' ', $corp->phone_no, 2);
                        $country_code = $phone_parts[0];
                        $phone_number = isset($phone_parts[1]) ? $phone_parts[1] : '';
                        ?>
                        <div class="cms-corp-phone-group">
                            <select name="corp_country_code" class="cms-corp-form-control cms-corp-country-code" id="corp-country-code">
                                <option value="+1" <?php selected($country_code, '+1'); ?>>+1 (USA/Canada)</option>
                                <option value="+44" <?php selected($country_code, '+44'); ?>>+44 (UK)</option>
                                <option value="+91" <?php selected($country_code, '+91'); ?>>+91 (India)</option>
                                <option value="+92" <?php selected($country_code, '+92'); ?>>+92 (Pakistan)</option>
                                <option value="+971" <?php selected($country_code, '+971'); ?>>+971 (UAE)</option>
                                <option value="+966" <?php selected($country_code, '+966'); ?>>+966 (Saudi Arabia)</option>
                                <option value="+20" <?php selected($country_code, '+20'); ?>>+20 (Egypt)</option>
                                <option value="+65" <?php selected($country_code, '+65'); ?>>+65 (Singapore)</option>
                                <option value="+86" <?php selected($country_code, '+86'); ?>>+86 (China)</option>
                                <option value="+81" <?php selected($country_code, '+81'); ?>>+81 (Japan)</option>
                                <option value="+49" <?php selected($country_code, '+49'); ?>>+49 (Germany)</option>
                                <option value="+33" <?php selected($country_code, '+33'); ?>>+33 (France)</option>
                                <option value="+61" <?php selected($country_code, '+61'); ?>>+61 (Australia)</option>
                                <option value="<?php echo esc_attr($country_code); ?>" <?php echo !in_array($country_code, ['+1', '+44', '+91', '+92', '+971', '+966', '+20', '+65', '+86', '+81', '+49', '+33', '+61']) ? 'selected' : ''; ?>>
                                    <?php echo esc_html($country_code); ?>
                                </option>
                            </select>
                            <input 
                                type="tel" 
                                id="corp-phone" 
                                name="corp_phone" 
                                class="cms-corp-form-control" 
                                value="<?php echo esc_attr($phone_number); ?>"
                                required
                                pattern="[0-9\s\-\(\)]{8,20}"
                                title="Please enter a valid phone number"
                            >
                        </div>
                    </div>
                    
                    <!-- Address -->
                    <div class="cms-corp-form-group full-width">
                        <label for="corp-address">
                            Business Address <span class="cms-corp-required">*</span>
                        </label>
                        <textarea 
                            id="corp-address" 
                            name="corp_address" 
                            class="cms-corp-textarea" 
                            required
                        ><?php echo esc_textarea($corp->address); ?></textarea>
                    </div>
                    
                    <!-- Website -->
                    <div class="cms-corp-form-group full-width">
                        <label for="corp-website">
                            Website URL <span class="cms-corp-required">*</span>
                        </label>
                        <div class="cms-corp-website-input">
                            <input 
                                type="url" 
                                id="corp-website" 
                                name="corp_website" 
                                class="cms-corp-form-control cms-corp-website-field" 
                                value="<?php echo esc_attr($corp->website); ?>"
                                placeholder="https://example.com or https://www.example.co.uk/"
                                required
                            >
                        </div>
                        <div class="cms-corp-field-hint">Enter full website URL including https:// (e.g., https://ashfordpremiertaxi.co.uk/)</div>
                    </div>
                </div>
            </div>
            
            <!-- Status Section -->
            <div class="cms-corp-update-section">
                <h3 class="cms-corp-update-section-title">⚙️ Account Settings</h3>
                
                <div class="cms-corp-form-grid">
                    <div class="cms-corp-form-group">
                        <label for="corp-status">Account Status</label>
                        <select id="corp-status" name="corp_status" class="cms-corp-form-control">
                            <option value="active" <?php selected($corp->status, 'active'); ?>>Active</option>
                            <option value="inactive" <?php selected($corp->status, 'inactive'); ?>>Inactive</option>
                            <option value="suspended" <?php selected($corp->status, 'suspended'); ?>>Suspended</option>
                        </select>
                        <div class="cms-corp-field-hint">Change account access status</div>
                    </div>
                    
                    <div class="cms-corp-form-group">
                        <label for="corp-created">Created Date</label>
                        <input 
                            type="text" 
                            id="corp-created" 
                            class="cms-corp-form-control" 
                            value="<?php echo esc_attr(date('F j, Y \a\t g:i a', strtotime($corp->created_at))); ?>"
                            readonly
                        >
                    </div>
                </div>
                
                <?php if (!empty($corp->updated_at) && $corp->updated_at != '0000-00-00 00:00:00'): ?>
                <div class="cms-corp-last-updated">
                    Last updated: <?php echo esc_html(date('F j, Y \a\t g:i a', strtotime($corp->updated_at))); ?>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Submit Button -->
            <div class="cms-corp-update-footer">
                <a href="<?php echo esc_url(remove_query_arg(array('corp_id', 'update'), wp_get_referer())); ?>" class="cms-corp-cancel-button">
                    Cancel
                </a>
                <button type="submit" name="cms_corp_update_submit" class="cms-corp-update-button" id="corp-update-btn">
                    💾 <?php echo esc_html($atts['button_text']); ?>
                </button>
            </div>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Remove URL parameters on page load to prevent message duplication
        if (window.location.search.includes('update=')) {
            var url = window.location.pathname + window.location.search.replace(/[?&]update=[^&]*/g, '').replace(/^&/, '?');
            window.history.replaceState({}, document.title, url);
        }
        
        // Form submission with AJAX
        $('#cms-corp-update-form').on('submit', function(e) {
            e.preventDefault(); // Prevent default form submission
            
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
            
            // Validate website URL
            var website = $('#corp-website');
            var websitePattern = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/i;
            
            if (website.val() && website.val() !== 'https://') {
                if (!websitePattern.test(website.val())) {
                    website.addClass('error');
                    alert('Please enter a valid website URL (e.g., https://ashfordpremiertaxi.co.uk/)');
                    isValid = false;
                }
            } else {
                website.addClass('error');
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
                alert('Please fill all required fields correctly.');
                return false;
            }
            
            // Show progress overlay
            $('#update-progress-overlay').css('display', 'flex');
            
            // Disable button
            $('#corp-update-btn')
                .text('Updating...')
                .prop('disabled', true);
            
            // Collect form data
            var formData = {
                action: 'cms_update_corporate_account',
                cms_corp_update_nonce: $('#cms_corp_update_nonce').val(),
                cms_corp_id: $('#corp-id-field').val(),
                corp_company: $('#corp-company').val(),
                corp_name: $('#corp-name').val(),
                corp_email: $('#corp-email').val(),
                corp_country_code: $('#corp-country-code').val(),
                corp_phone: $('#corp-phone').val(),
                corp_address: $('#corp-address').val(),
                corp_website: $('#corp-website').val(),
                corp_status: $('#corp-status').val()
            };
            
            // Send AJAX request
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        // Show success message
                        $('#update-status-message').html(
                            '<div class="cms-corp-message success">' + response.data.message + '</div>'
                        );
                        
                        // Update the form with new data
                        if (response.data.data) {
                            updateFormData(response.data.data);
                        }
                        
                        // Add updated badge
                        $('.cms-corp-info-box').append(
                            '<span class="cms-corp-updated-badge">✓ Updated Just Now</span>'
                        );
                        
                        // Update last updated time
                        if (response.data.updated_at) {
                            var lastUpdated = '<div class="cms-corp-last-updated">Last updated: ' + response.data.updated_at + '</div>';
                            $('.cms-corp-update-section:last-child').append(lastUpdated);
                        }
                        
                        // Highlight fields
                        $('.cms-corp-form-control').addClass('success');
                        setTimeout(function() {
                            $('.cms-corp-form-control').removeClass('success');
                        }, 2000);
                        
                        // Hide progress overlay after a short delay
                        setTimeout(function() {
                            $('#update-progress-overlay').fadeOut(500);
                            $('#corp-update-btn')
                                .text('💾 Update Account')
                                .prop('disabled', false);
                        }, 1000);
                        
                    } else {
                        // Show error message
                        $('#update-status-message').html(
                            '<div class="cms-corp-message error">' + response.data.message + '</div>'
                        );
                        
                        // Hide progress overlay
                        $('#update-progress-overlay').fadeOut(500);
                        
                        // Re-enable button
                        $('#corp-update-btn')
                            .text('💾 Update Account')
                            .prop('disabled', false);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX Error:', error);
                    console.error('Response:', xhr.responseText);
                    
                    // Show error message
                    $('#update-status-message').html(
                        '<div class="cms-corp-message error">An error occurred. Please try again.</div>'
                    );
                    
                    // Hide progress overlay
                    $('#update-progress-overlay').fadeOut(500);
                    
                    // Re-enable button
                    $('#corp-update-btn')
                        .text('💾 Update Account')
                        .prop('disabled', false);
                }
            });
        });
        
        // Function to update form data after successful update
        function updateFormData(data) {
            $('#corp-company').val(data.company_name);
            $('#corp-name').val(data.name);
            $('#corp-email').val(data.email);
            
            // Parse phone
            var phoneParts = data.phone_no.split(' ');
            $('#corp-country-code').val(phoneParts[0]);
            $('#corp-phone').val(phoneParts[1] || '');
            
            $('#corp-address').val(data.address);
            $('#corp-website').val(data.website);
            $('#corp-status').val(data.status);
        }
        
        // Remove error class on input
        $('.cms-corp-form-control').on('input change', function() {
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
        
        // Auto-format website input
        $('#corp-website').on('blur', function() {
            var url = $(this).val().trim();
            
            // If empty or just protocol, keep as is
            if (!url || url === 'http://' || url === 'https://') {
                return;
            }
            
            // Add https:// if no protocol specified
            if (!url.match(/^[a-zA-Z]+:\/\//)) {
                url = 'https://' + url;
            }
            
            // Ensure it ends with / if it's a domain
            if (url.match(/^https?:\/\/[^\/]+$/) && !url.endsWith('/')) {
                url = url + '/';
            }
            
            $(this).val(url);
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_update_corp_acc', 'cms_update_corp_acc_shortcode');
add_shortcode(CMS_CORP_ACC_UPDATE_SHORTCODE, 'cms_update_corp_acc_shortcode');

/**
 * Get corporate account by ID from database
 */
function cms_get_corporate_account_by_id($id) {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_corp_acc';
    
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE id = %d",
        $id
    ));
}

/**
 * AJAX handler for corporate account update
 */
function cms_ajax_update_corporate_account() {
    // Check nonce
    if (!isset($_POST['cms_corp_update_nonce']) || !wp_verify_nonce($_POST['cms_corp_update_nonce'], 'cms_corp_account_update')) {
        wp_send_json_error(array('message' => 'Security check failed. Please refresh the page and try again.'));
    }
    
    global $wpdb;
    
    // Get table names
    $table_corp_acc = $wpdb->prefix . 'cms_corp_acc';
    
    // Get and validate ID
    $corp_id = intval($_POST['cms_corp_id']);
    if (!$corp_id) {
        wp_send_json_error(array('message' => 'Invalid corporate account ID.'));
    }
    
    // Collect and sanitize form data
    $company_name = sanitize_text_field($_POST['corp_company']);
    $contact_name = sanitize_text_field($_POST['corp_name']);
    $email = sanitize_email($_POST['corp_email']);
    $country_code = sanitize_text_field($_POST['corp_country_code']);
    $phone = sanitize_text_field($_POST['corp_phone']);
    $full_phone = $country_code . ' ' . $phone;
    $address = sanitize_textarea_field($_POST['corp_address']);
    
    // Website sanitization
    $website = esc_url_raw(trim($_POST['corp_website']));
    if (!empty($website) && $website !== 'https://') {
        if (!preg_match('#^https?://#', $website)) {
            $website = 'https://' . $website;
        }
    }
    
    $status = sanitize_text_field($_POST['corp_status']);
    
    // Validate required fields
    if (empty($company_name) || empty($contact_name) || empty($email) || 
        empty($phone) || empty($address) || empty($website) || $website === 'https://') {
        wp_send_json_error(array('message' => 'Please fill all required fields correctly.'));
    }
    
    // Validate email format
    if (!is_email($email)) {
        wp_send_json_error(array('message' => 'Please enter a valid email address.'));
    }
    
    // Check if email already exists for another account
    $email_exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_corp_acc WHERE email = %s AND id != %d",
        $email,
        $corp_id
    ));
    
    if ($email_exists > 0) {
        wp_send_json_error(array('message' => 'Email already exists for another account.'));
    }
    
    // Update the corporate account
    $result = $wpdb->update(
        $table_corp_acc,
        array(
            'company_name' => $company_name,
            'name' => $contact_name,
            'email' => $email,
            'phone_no' => $full_phone,
            'address' => $address,
            'website' => $website,
            'status' => $status,
            'updated_at' => current_time('mysql')
        ),
        array('id' => $corp_id),
        array('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s'),
        array('%d')
    );
    
    if ($result !== false) {
        // Get the updated account data
        $updated_corp = cms_get_corporate_account_by_id($corp_id);
        
        // Log the update
        error_log(sprintf(
            'CMS: Corporate account updated via AJAX - ID: %d, Company: %s, Email: %s',
            $corp_id,
            $company_name,
            $email
        ));
        
        // Format the updated_at time for display
        $updated_at_formatted = '';
        if (!empty($updated_corp->updated_at) && $updated_corp->updated_at != '0000-00-00 00:00:00') {
            $updated_at_formatted = date('F j, Y \a\t g:i a', strtotime($updated_corp->updated_at));
        }
        
        wp_send_json_success(array(
            'message' => 'Corporate account updated successfully!',
            'data' => $updated_corp,
            'updated_at' => $updated_at_formatted
        ));
    } else {
        // Log error
        error_log('CMS Corporate Account Update Error: Failed to update account ID: ' . $corp_id . ' - DB Error: ' . $wpdb->last_error);
        
        wp_send_json_error(array('message' => 'Failed to update account. Please try again.'));
    }
}
add_action('wp_ajax_cms_update_corporate_account', 'cms_ajax_update_corporate_account');