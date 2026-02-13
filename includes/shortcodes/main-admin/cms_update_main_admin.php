<?php
/**
 * CMS Update Main Admin Shortcode
 * Form to update existing admin data
 * 
 * Usage: [cms_update_main_admin]
 * Usage: [cms_update_main_admin admin_id="1"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define shortcode slug
if (!defined('CMS_MAIN_ADMIN_UPDATE_SHORTCODE')) {
    define('CMS_MAIN_ADMIN_UPDATE_SHORTCODE', 'cms_main_admin_update');
}


function cms_update_main_admin_shortcode($atts) {
    // Parse attributes
    $atts = shortcode_atts(
        array(
            'admin_id' => 0,
            'title' => 'Update Admin Profile',
            'button_text' => 'Update Admin',
            'success_message' => 'Admin updated successfully!',
            'class' => ''
        ),
        $atts,
        'cms_update_main_admin'
    );
    
    // Get admin ID from URL if not set in shortcode
$admin_id = $atts['admin_id'];
if (!$admin_id) {
    // Try from query var first (for pretty URLs)
    $admin_id = get_query_var('admin_id');
    
    // If not found, try from GET parameter
    if (!$admin_id && isset($_GET['admin_id'])) {
        $admin_id = intval($_GET['admin_id']);
    }
}

// Debug - remove after fixing
error_log('CMS Update - Admin ID: ' . $admin_id);
    
    if (!$admin_id) {
        return '<div style="padding: 20px; background: #ffe8e8; color: #b34141; border-radius: 8px; text-align: center;">No admin selected. Please provide an admin ID.</div>';
    }
    
    // Get admin data - Replace with your database query
    $admin_data = get_cms_admin_by_id($admin_id);
    
    if (!$admin_data) {
        return '<div style="padding: 20px; background: #ffe8e8; color: #b34141; border-radius: 8px; text-align: center;">Admin not found.</div>';
    }
    
    ob_start();
    ?>
    
    <style>
    /* Update Form Styles */
    .cms-update-container {
        max-width: 800px;
        margin: 30px auto;
        padding: 35px;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(0,0,0,0.05);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        border: 1px solid #f0f0f0;
    }
    
    .cms-update-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        padding-bottom: 20px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .cms-update-title {
        margin: 0;
        font-size: 26px;
        font-weight: 700;
        color: #1a2b3c;
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .cms-update-title:before {
        content: '✏️';
        font-size: 28px;
    }
    
    .cms-back-link {
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
    
    .cms-back-link:hover {
        background: #edf2f7;
        color: #2c3e50;
    }
    
    .cms-update-section {
        background: #f8fafc;
        border-radius: 16px;
        padding: 25px;
        margin-bottom: 25px;
        border: 1px solid #e9edf2;
    }
    
    .cms-section-header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
        padding-bottom: 12px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .cms-section-header h3 {
        margin: 0;
        font-size: 18px;
        font-weight: 600;
        color: #2c3e50;
    }
    
    .cms-form-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 20px;
    }
    
    .cms-form-group {
        margin-bottom: 15px;
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
    }
    
    .cms-form-control[readonly] {
        background: #f8fafc;
        border-color: #e2e8f0;
        color: #718096;
        cursor: not-allowed;
    }
    
    .cms-phone-group {
        display: flex;
        gap: 10px;
    }
    
    .cms-country-code {
        width: 120px;
        flex-shrink: 0;
    }
    
    .cms-update-footer {
        margin-top: 30px;
        display: flex;
        gap: 15px;
        justify-content: flex-end;
    }
    
    .cms-update-button {
        padding: 16px 32px;
        background: linear-gradient(145deg, #007cba, #0063a0);
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
    
    .cms-update-button:hover {
        background: linear-gradient(145deg, #0063a0, #005287);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(0,124,186,0.2);
    }
    
    .cms-cancel-button {
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
    
    .cms-cancel-button:hover {
        background: #edf2f7;
        border-color: #cbd5e0;
    }
    
    .cms-message {
        padding: 16px 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cms-message.success {
        background: #e3f7ec;
        color: #0a5c36;
        border: 1px solid #b8e0c2;
    }
    
    .cms-message.error {
        background: #ffe8e8;
        color: #b34141;
        border: 1px solid #ffc9c9;
    }
    
    .cms-info-box {
        background: #e6f3ff;
        border-left: 4px solid #007cba;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        font-size: 14px;
        color: #2c3e50;
    }
    
    @media (max-width: 768px) {
        .cms-update-container {
            padding: 25px;
            margin: 20px 15px;
        }
        
        .cms-form-grid {
            grid-template-columns: 1fr;
        }
        
        .cms-form-group.full-width {
            grid-column: span 1;
        }
        
        .cms-phone-group {
            flex-direction: column;
        }
        
        .cms-country-code {
            width: 100%;
        }
        
        .cms-update-footer {
            flex-direction: column;
        }
        
        .cms-update-button,
        .cms-cancel-button {
            width: 100%;
            justify-content: center;
        }
    }
    </style>
    
    <div class="cms-update-container <?php echo esc_attr($atts['class']); ?>">
        
        <div class="cms-update-header">
            <h2 class="cms-update-title"><?php echo esc_html($atts['title']); ?></h2>
            <a href="<?php echo esc_url(remove_query_arg('admin_id', wp_get_referer())); ?>" class="cms-back-link">
                ← Back to List
            </a>
        </div>
        
        <?php
        // Display success/error messages
        if (isset($_GET['update']) && $_GET['update'] === 'success') {
            echo '<div class="cms-message success">' . esc_html($atts['success_message']) . '</div>';
        }
        
        if (isset($_GET['update']) && $_GET['update'] === 'error') {
            echo '<div class="cms-message error">Update failed. Please try again.</div>';
        }
        ?>
        
        <div class="cms-info-box">
            <strong>📝 Editing Admin:</strong> <?php echo esc_html($admin_data['name']); ?> (ID: <?php echo esc_html($admin_id); ?>)
        </div>
        
        <form method="post" action="" id="cms-update-admin-form">
            <input type="hidden" name="cms_admin_id" value="<?php echo esc_attr($admin_id); ?>">
            <input type="hidden" name="cms_update_action" value="update_admin">
            
            <!-- Personal Information -->
            <div class="cms-update-section">
                <div class="cms-section-header">
                    <h3>Personal Information</h3>
                </div>
                
                <div class="cms-form-grid">
                    <div class="cms-form-group">
                        <label for="username">Username</label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            class="cms-form-control" 
                            value="<?php echo esc_attr($admin_data['username']); ?>"
                            readonly
                        >
                    </div>
                    
                    <div class="cms-form-group">
                        <label for="fullname">Full Name <span class="cms-required">*</span></label>
                        <input 
                            type="text" 
                            id="fullname" 
                            name="fullname" 
                            class="cms-form-control" 
                            value="<?php echo esc_attr($admin_data['name']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="cms-form-group">
                        <label for="email">Email Address <span class="cms-required">*</span></label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            class="cms-form-control" 
                            value="<?php echo esc_attr($admin_data['email']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="cms-form-group">
                        <label for="fathername">Father's Name <span class="cms-required">*</span></label>
                        <input 
                            type="text" 
                            id="fathername" 
                            name="fathername" 
                            class="cms-form-control" 
                            value="<?php echo esc_attr($admin_data['father_name']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="cms-form-group">
                        <label for="contact">Contact Number <span class="cms-required">*</span></label>
                        <div class="cms-phone-group">
                            <input 
                                type="text" 
                                id="contact_code" 
                                name="contact_code" 
                                class="cms-form-control cms-country-code" 
                                value="<?php echo esc_attr(explode(' ', $admin_data['contact'])[0]); ?>"
                                placeholder="+1"
                            >
                            <input 
                                type="tel" 
                                id="contact" 
                                name="contact" 
                                class="cms-form-control" 
                                value="<?php echo esc_attr(implode(' ', array_slice(explode(' ', $admin_data['contact']), 1))); ?>"
                                required
                            >
                        </div>
                    </div>
                    
                    <div class="cms-form-group">
                        <label for="emergency">Emergency Contact <span class="cms-required">*</span></label>
                        <div class="cms-phone-group">
                            <input 
                                type="text" 
                                id="emergency_code" 
                                name="emergency_code" 
                                class="cms-form-control cms-country-code" 
                                value="<?php echo esc_attr(explode(' ', $admin_data['emergency'])[0]); ?>"
                                placeholder="+1"
                            >
                            <input 
                                type="tel" 
                                id="emergency" 
                                name="emergency" 
                                class="cms-form-control" 
                                value="<?php echo esc_attr(implode(' ', array_slice(explode(' ', $admin_data['emergency']), 1))); ?>"
                                required
                            >
                        </div>
                    </div>
                    
                    <div class="cms-form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="cms-form-control">
                            <option value="active" <?php selected($admin_data['status'], 'active'); ?>>Active</option>
                            <option value="pending" <?php selected($admin_data['status'], 'pending'); ?>>Pending</option>
                            <option value="inactive" <?php selected($admin_data['status'], 'inactive'); ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <!-- Reference 1 -->
            <div class="cms-update-section">
                <div class="cms-section-header">
                    <h3>Reference #1</h3>
                </div>
                
                <div class="cms-form-grid">
                    <div class="cms-form-group">
                        <label for="ref1_name">Reference Name <span class="cms-required">*</span></label>
                        <input 
                            type="text" 
                            id="ref1_name" 
                            name="ref1_name" 
                            class="cms-form-control" 
                            value="<?php echo esc_attr($admin_data['ref1_name']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="cms-form-group">
                        <label for="ref1_cno">Reference Contact <span class="cms-required">*</span></label>
                        <input 
                            type="tel" 
                            id="ref1_cno" 
                            name="ref1_cno" 
                            class="cms-form-control" 
                            value="<?php echo esc_attr($admin_data['ref1_cno']); ?>"
                            required
                        >
                    </div>
                </div>
            </div>
            
            <!-- Reference 2 -->
            <div class="cms-update-section">
                <div class="cms-section-header">
                    <h3>Reference #2</h3>
                </div>
                
                <div class="cms-form-grid">
                    <div class="cms-form-group">
                        <label for="ref2_name">Reference Name <span class="cms-required">*</span></label>
                        <input 
                            type="text" 
                            id="ref2_name" 
                            name="ref2_name" 
                            class="cms-form-control" 
                            value="<?php echo esc_attr($admin_data['ref2_name']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="cms-form-group">
                        <label for="ref2_cno">Reference Contact <span class="cms-required">*</span></label>
                        <input 
                            type="tel" 
                            id="ref2_cno" 
                            name="ref2_cno" 
                            class="cms-form-control" 
                            value="<?php echo esc_attr($admin_data['ref2_cno']); ?>"
                            required
                        >
                    </div>
                </div>
            </div>
            
            <div class="cms-update-footer">
                <a href="<?php echo esc_url(remove_query_arg('admin_id', wp_get_referer())); ?>" class="cms-cancel-button">
                    Cancel
                </a>
                <button type="submit" name="cms_update_submit" class="cms-update-button">
                    💾 <?php echo esc_html($atts['button_text']); ?>
                </button>
            </div>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        // Form validation
        $('#cms-update-admin-form').on('submit', function(e) {
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
            
            // Validate email
            var email = $('#email');
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email.val() && !emailPattern.test(email.val())) {
                email.addClass('error');
                isValid = false;
            }
            
            // Validate phone numbers
            $('input[type="tel"]').each(function() {
                var phone = $(this).val();
                var phonePattern = /^[0-9\s\-\(\)]+$/;
                if (phone && !phonePattern.test(phone)) {
                    $(this).addClass('error');
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill all required fields correctly.');
                return false;
            }
            
            $(this).find('.cms-update-button').text('Updating...').prop('disabled', true);
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}
add_shortcode('cms_update_main_admin', 'cms_update_main_admin_shortcode');
add_shortcode(CMS_MAIN_ADMIN_UPDATE_SHORTCODE, 'cms_update_main_admin_shortcode');
// Helper function to get admin by ID
function get_cms_admin_by_id($id) {
    // Replace this with your actual database query
    $mock_admins = get_cms_mock_admin_data();
    
    foreach ($mock_admins as $admin) {
        if ($admin['id'] == $id) {
            return $admin;
        }
    }
    
    return null;
}

// Handle update form submission
function cms_handle_admin_update() {
    if (isset($_POST['cms_update_submit']) && isset($_POST['cms_update_action']) && $_POST['cms_update_action'] === 'update_admin') {
        
        $admin_id = intval($_POST['cms_admin_id']);
        
        // Here you would update the database
        // $wpdb->update('your_table', $data, array('id' => $admin_id));
        
        // Redirect with success message
        wp_redirect(add_query_arg('update', 'success', wp_get_referer()));
        exit;
    }
}
add_action('init', 'cms_handle_admin_update');
?>