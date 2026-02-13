<?php
/**
 * CMS Update Corporate Account Shortcode
 * Form to update existing corporate account data
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
    
    $corp = get_cms_corp_by_id($corp_id);
    
    if (!$corp) {
        return '<div style="padding: 20px; background: #ffe8e8; color: #b34141; border-radius: 8px; text-align: center;">Corporate account not found.</div>';
    }
    
    ob_start();
    ?>
    
    <style>
    /* Corporate Account Update Styles */
    .cms-corp-update-container {
        max-width: 900px;
        margin: 30px auto;
        padding: 35px;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(108,92,231,0.05);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        border-top: 4px solid #6c5ce7;
    }
    
    .cms-corp-update-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #d9d0ff;
    }
    
    .cms-corp-update-title {
        margin: 0;
        font-size: 26px;
        font-weight: 700;
        color: #5649c0;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .cms-corp-update-title:before {
        content: '✏️';
        font-size: 28px;
    }
    
    .cms-corp-back-link {
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
        color: #5649c0;
    }
    
    .cms-corp-update-section {
        background: #f5f0ff;
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 25px;
        border: 1px solid #d9d0ff;
    }
    
    .cms-corp-update-section-title {
        margin: 0 0 20px 0;
        font-size: 18px;
        font-weight: 600;
        color: #5649c0;
        padding-bottom: 12px;
        border-bottom: 2px solid #d9d0ff;
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
        color: #d63031;
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
        border-color: #6c5ce7;
        box-shadow: 0 0 0 4px rgba(108,92,231,0.05);
    }
    
    .cms-corp-form-control[readonly] {
        background: #f5f0ff;
        border-color: #d9d0ff;
        color: #718096;
        cursor: not-allowed;
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
        color: #5649c0;
        font-weight: 500;
    }
    
    .cms-corp-website-field {
        flex: 1;
        border-radius: 0 12px 12px 0;
    }
    
    .cms-corp-update-footer {
        margin-top: 30px;
        display: flex;
        gap: 15px;
        justify-content: flex-end;
    }
    
    .cms-corp-update-button {
        padding: 16px 32px;
        background: linear-gradient(145deg, #6c5ce7, #5649c0);
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
    
    .cms-corp-update-button:hover {
        background: linear-gradient(145deg, #5649c0, #4338b0);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(108,92,231,0.2);
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
        gap: 8px;
    }
    
    .cms-corp-cancel-button:hover {
        background: #d9d0ff;
        border-color: #6c5ce7;
    }
    
    .cms-corp-message {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cms-corp-message.success {
        background: #e3f7ec;
        color: #0a5c36;
        border: 1px solid #b8e0c2;
    }
    
    .cms-corp-message.error {
        background: #ffe8e8;
        color: #b34141;
        border: 1px solid #ffc9c9;
    }
    
    .cms-corp-info-box {
        background: #f5f0ff;
        border-left: 4px solid #6c5ce7;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        font-size: 14px;
        color: #2c3e50;
    }
    </style>
    
    <div class="cms-corp-update-container <?php echo esc_attr($atts['class']); ?>">
        
        <div class="cms-corp-update-header">
            <h2 class="cms-corp-update-title"><?php echo esc_html($atts['title']); ?></h2>
            <a href="<?php echo esc_url(remove_query_arg('corp_id', wp_get_referer())); ?>" class="cms-corp-back-link">
                ← Back to List
            </a>
        </div>
        
        <?php
        if (isset($_GET['update']) && $_GET['update'] === 'success') {
            echo '<div class="cms-corp-message success">' . esc_html($atts['success_message']) . '</div>';
        }
        
        if (isset($_GET['update']) && $_GET['update'] === 'error') {
            echo '<div class="cms-corp-message error">Update failed. Please try again.</div>';
        }
        ?>
        
        <div class="cms-corp-info-box">
            <strong>🏢 Editing Corporate Account:</strong> <?php echo esc_html($corp['company_name']); ?> (ID: <?php echo esc_html($corp_id); ?>)
        </div>
        
        <form method="post" action="" id="cms-corp-update-form">
            <?php wp_nonce_field('cms_corp_account_update', 'cms_corp_update_nonce'); ?>
            <input type="hidden" name="cms_corp_id" value="<?php echo esc_attr($corp_id); ?>">
            <input type="hidden" name="cms_corp_update_action" value="update_corp">
            
            <!-- Account Information -->
            <div class="cms-corp-update-section">
                <h3 class="cms-corp-update-section-title">🏢 Account Information</h3>
                
                <div class="cms-corp-form-grid">
                    <div class="cms-corp-form-group">
                        <label for="corp-username">Username</label>
                        <input 
                            type="text" 
                            id="corp-username" 
                            name="corp_username" 
                            class="cms-corp-form-control" 
                            value="<?php echo esc_attr($corp['username']); ?>"
                            readonly
                        >
                    </div>
                    
                    <div class="cms-corp-form-group">
                        <label for="corp-company">Company Name <span class="cms-corp-required">*</span></label>
                        <input 
                            type="text" 
                            id="corp-company" 
                            name="corp_company" 
                            class="cms-corp-form-control" 
                            value="<?php echo esc_attr($corp['company_name']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="cms-corp-form-group">
                        <label for="corp-name">Contact Person <span class="cms-corp-required">*</span></label>
                        <input 
                            type="text" 
                            id="corp-name" 
                            name="corp_name" 
                            class="cms-corp-form-control" 
                            value="<?php echo esc_attr($corp['contact_name']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="cms-corp-form-group">
                        <label for="corp-email">Email Address <span class="cms-corp-required">*</span></label>
                        <input 
                            type="email" 
                            id="corp-email" 
                            name="corp_email" 
                            class="cms-corp-form-control" 
                            value="<?php echo esc_attr($corp['email']); ?>"
                            required
                        >
                    </div>
                </div>
            </div>
            
            <!-- Contact Information -->
            <div class="cms-corp-update-section">
                <h3 class="cms-corp-update-section-title">📞 Contact Information</h3>
                
                <div class="cms-corp-form-grid">
                    <div class="cms-corp-form-group full-width">
                        <label for="corp-phone">Phone Number <span class="cms-corp-required">*</span></label>
                        <?php
                        // Parse phone to get country code and number
                        $phone_parts = explode(' ', $corp['phone'], 2);
                        $country_code = $phone_parts[0];
                        $phone_number = isset($phone_parts[1]) ? $phone_parts[1] : '';
                        ?>
                        <div class="cms-corp-phone-group">
                            <input 
                                type="text" 
                                id="corp-phone-code" 
                                name="corp_phone_code" 
                                class="cms-corp-form-control cms-corp-country-code" 
                                value="<?php echo esc_attr($country_code); ?>"
                                placeholder="+1"
                            >
                            <input 
                                type="tel" 
                                id="corp-phone" 
                                name="corp_phone" 
                                class="cms-corp-form-control" 
                                value="<?php echo esc_attr($phone_number); ?>"
                                required
                                pattern="[0-9\s\-\(\)]{8,20}"
                            >
                        </div>
                    </div>
                    
                    <div class="cms-corp-form-group full-width">
                        <label for="corp-address">Business Address <span class="cms-corp-required">*</span></label>
                        <textarea 
                            id="corp-address" 
                            name="corp_address" 
                            class="cms-corp-textarea" 
                            required
                        ><?php echo esc_textarea($corp['address']); ?></textarea>
                    </div>
                    
                    <div class="cms-corp-form-group full-width">
                        <label for="corp-website">Website URL</label>
                        <?php
                        $website = preg_replace('/^https?:\/\//', '', $corp['website']);
                        ?>
                        <div class="cms-corp-website-input">
                            <span class="cms-corp-website-prefix">https://</span>
                            <input 
                                type="url" 
                                id="corp-website" 
                                name="corp_website" 
                                class="cms-corp-form-control cms-corp-website-field" 
                                value="<?php echo esc_attr($website); ?>"
                                pattern="^(www\.)?[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(\.[a-zA-Z]{2,})?$"
                            >
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Additional Information -->
            <div class="cms-corp-update-section">
                <h3 class="cms-corp-update-section-title">📋 Additional Information</h3>
                
                <div class="cms-corp-form-grid">
                    <div class="cms-corp-form-group">
                        <label for="corp-industry">Industry Type</label>
                        <select id="corp-industry" name="corp_industry" class="cms-corp-form-control">
                            <option value="">Select Industry</option>
                            <option value="technology" <?php selected($corp['industry'], 'technology'); ?>>Technology / IT</option>
                            <option value="finance" <?php selected($corp['industry'], 'finance'); ?>>Finance / Banking</option>
                            <option value="healthcare" <?php selected($corp['industry'], 'healthcare'); ?>>Healthcare</option>
                            <option value="education" <?php selected($corp['industry'], 'education'); ?>>Education</option>
                            <option value="manufacturing" <?php selected($corp['industry'], 'manufacturing'); ?>>Manufacturing</option>
                            <option value="retail" <?php selected($corp['industry'], 'retail'); ?>>Retail / E-commerce</option>
                            <option value="realestate" <?php selected($corp['industry'], 'realestate'); ?>>Real Estate</option>
                            <option value="construction" <?php selected($corp['industry'], 'construction'); ?>>Construction</option>
                            <option value="transportation" <?php selected($corp['industry'], 'transportation'); ?>>Transportation</option>
                            <option value="hospitality" <?php selected($corp['industry'], 'hospitality'); ?>>Hospitality</option>
                            <option value="media" <?php selected($corp['industry'], 'media'); ?>>Media</option>
                            <option value="consulting" <?php selected($corp['industry'], 'consulting'); ?>>Consulting</option>
                            <option value="other" <?php selected($corp['industry'], 'other'); ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="cms-corp-form-group">
                        <label for="corp-size">Company Size</label>
                        <select id="corp-size" name="corp_size" class="cms-corp-form-control">
                            <option value="">Select Size</option>
                            <option value="1-10" <?php selected($corp['company_size'], '1-10'); ?>>1-10 employees</option>
                            <option value="11-50" <?php selected($corp['company_size'], '11-50'); ?>>11-50 employees</option>
                            <option value="51-200" <?php selected($corp['company_size'], '51-200'); ?>>51-200 employees</option>
                            <option value="201-500" <?php selected($corp['company_size'], '201-500'); ?>>201-500 employees</option>
                            <option value="501-1000" <?php selected($corp['company_size'], '501-1000'); ?>>501-1000 employees</option>
                            <option value="1000+" <?php selected($corp['company_size'], '1000+'); ?>>1000+ employees</option>
                        </select>
                    </div>
                    
                    <div class="cms-corp-form-group">
                        <label for="corp-status">Account Status</label>
                        <select id="corp-status" name="corp_status" class="cms-corp-form-control">
                            <option value="active" <?php selected($corp['status'], 'active'); ?>>Active</option>
                            <option value="inactive" <?php selected($corp['status'], 'inactive'); ?>>Inactive</option>
                            <option value="suspended" <?php selected($corp['status'], 'suspended'); ?>>Suspended</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="cms-corp-update-footer">
                <a href="<?php echo esc_url(remove_query_arg('corp_id', wp_get_referer())); ?>" class="cms-corp-cancel-button">
                    Cancel
                </a>
                <button type="submit" name="cms_corp_update_submit" class="cms-corp-update-button">
                    💾 <?php echo esc_html($atts['button_text']); ?>
                </button>
            </div>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Auto-format website input
        $('#corp-website').on('blur', function() {
            var url = $(this).val();
            url = url.replace(/^https?:\/\//i, '');
            url = url.replace(/^www\./i, '');
            $(this).val(url);
        });
        
        $('#cms-corp-update-form').on('submit', function(e) {
            var isValid = true;
            
            $(this).find('[required]').each(function() {
                if (!$(this).val()) {
                    $(this).addClass('error');
                    isValid = false;
                } else {
                    $(this).removeClass('error');
                }
            });
            
            var email = $('#corp-email');
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email.val() && !emailPattern.test(email.val())) {
                email.addClass('error');
                isValid = false;
            }
            
            var website = $('#corp-website');
            if (website.val()) {
                var websitePattern = /^(www\.)?[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,}(\.[a-zA-Z]{2,})?$/;
                if (!websitePattern.test(website.val())) {
                    website.addClass('error');
                    alert('Please enter a valid website domain');
                    isValid = false;
                }
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill all required fields correctly.');
                return false;
            }
            
            $(this).find('.cms-corp-update-button').text('Updating...').prop('disabled', true);
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_update_corp_acc', 'cms_update_corp_acc_shortcode');
add_shortcode(CMS_CORP_ACC_UPDATE_SHORTCODE, 'cms_update_corp_acc_shortcode');

function get_cms_corp_by_id($id) {
    $mock_corps = get_cms_mock_corp_data();
    
    foreach ($mock_corps as $corp) {
        if ($corp['id'] == $id) {
            return $corp;
        }
    }
    
    return null;
}

function cms_handle_corp_update() {
    if (isset($_POST['cms_corp_update_submit']) && isset($_POST['cms_corp_update_action']) && $_POST['cms_corp_update_action'] === 'update_corp') {
        
        if (!isset($_POST['cms_corp_update_nonce']) || !wp_verify_nonce($_POST['cms_corp_update_nonce'], 'cms_corp_account_update')) {
            wp_redirect(add_query_arg('update', 'error', wp_get_referer()));
            exit;
        }
        
        $corp_id = intval($_POST['cms_corp_id']);
        
        wp_redirect(add_query_arg('update', 'success', wp_get_referer()));
        exit;
    }
}
add_action('init', 'cms_handle_corp_update');

?>