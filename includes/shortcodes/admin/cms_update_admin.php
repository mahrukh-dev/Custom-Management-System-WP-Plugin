<?php
/**
 * CMS Update Admin Shortcode
 * Form to update existing admin data
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

function cms_update_admin_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'admin_id' => 0,
            'title' => 'Update Admin Profile',
            'button_text' => 'Update Admin',
            'success_message' => 'Admin updated successfully!',
            'class' => ''
        ),
        $atts,
        'cms_update_admin'
    );
    
    $admin_id = $atts['admin_id'];
    if (!$admin_id) {
        $admin_id = get_query_var('admin_id');
        if (!$admin_id && isset($_GET['admin_id'])) {
            $admin_id = intval($_GET['admin_id']);
        }
    }
    
    if (!$admin_id) {
        return '<div style="padding: 20px; background: #ffe8e8; color: #b34141; border-radius: 8px; text-align: center;">No admin selected. Please provide an admin ID.</div>';
    }
    
    $admin_data = get_cms_admin2_by_id($admin_id);
    
    if (!$admin_data) {
        return '<div style="padding: 20px; background: #ffe8e8; color: #b34141; border-radius: 8px; text-align: center;">Admin not found.</div>';
    }
    
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
    
    .cms-message2.error {
        background: #ffe8e8;
        color: #b34141;
        border: 1px solid #ffc9c9;
    }
    
    .cms-info2-box {
        background: #e8f5e9;
        border-left: 4px solid #27ae60;
        padding: 15px 20px;
        border-radius: 8px;
        margin-bottom: 25px;
        font-size: 14px;
        color: #2c3e50;
    }
    </style>
    
    <div class="cms-update2-container <?php echo esc_attr($atts['class']); ?>">
        
        <div class="cms-update2-header">
            <h2 class="cms-update2-title"><?php echo esc_html($atts['title']); ?></h2>
            <a href="<?php echo esc_url(remove_query_arg('admin_id', wp_get_referer())); ?>" class="cms-back2-link">
                ← Back to List
            </a>
        </div>
        
        <?php
        if (isset($_GET['update']) && $_GET['update'] === 'success') {
            echo '<div class="cms-message2 success">' . esc_html($atts['success_message']) . '</div>';
        }
        
        if (isset($_GET['update']) && $_GET['update'] === 'error') {
            echo '<div class="cms-message2 error">Update failed. Please try again.</div>';
        }
        ?>
        
        <div class="cms-info2-box">
            <strong>📝 Editing Admin:</strong> <?php echo esc_html($admin_data['name']); ?> (ID: <?php echo esc_html($admin_id); ?>)
        </div>
        
        <form method="post" action="" id="cms-update-admin2-form">
            <input type="hidden" name="cms_admin2_id" value="<?php echo esc_attr($admin_id); ?>">
            <input type="hidden" name="cms_update2_action" value="update_admin2">
            
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
                            name="username" 
                            class="cms-form2-control" 
                            value="<?php echo esc_attr($admin_data['username']); ?>"
                            readonly
                        >
                    </div>
                    
                    <div class="cms-form2-group">
                        <label for="fullname2">Full Name <span class="cms-required2">*</span></label>
                        <input 
                            type="text" 
                            id="fullname2" 
                            name="fullname" 
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
                            name="email" 
                            class="cms-form2-control" 
                            value="<?php echo esc_attr($admin_data['email']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="cms-form2-group">
                        <label for="fathername2">Father's Name <span class="cms-required2">*</span></label>
                        <input 
                            type="text" 
                            id="fathername2" 
                            name="fathername" 
                            class="cms-form2-control" 
                            value="<?php echo esc_attr($admin_data['father_name']); ?>"
                            required
                        >
                    </div>
                    
                    <div class="cms-form2-group">
                        <label for="position2">Position <span class="cms-required2">*</span></label>
                        <select id="position2" name="position" class="cms-position2-select" required>
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
                                name="contact_code" 
                                class="cms-form2-control cms-country2-code" 
                                value="<?php echo esc_attr(explode(' ', $admin_data['contact'])[0]); ?>"
                                placeholder="+1"
                            >
                            <input 
                                type="tel" 
                                id="contact2" 
                                name="contact" 
                                class="cms-form2-control" 
                                value="<?php echo esc_attr(implode(' ', array_slice(explode(' ', $admin_data['contact']), 1))); ?>"
                                required
                            >
                        </div>
                    </div>
                    
                    <div class="cms-form2-group">
                        <label for="emergency2">Emergency Contact <span class="cms-required2">*</span></label>
                        <div class="cms-phone2-group">
                            <input 
                                type="text" 
                                id="emergency_code2" 
                                name="emergency_code" 
                                class="cms-form2-control cms-country2-code" 
                                value="<?php echo esc_attr(explode(' ', $admin_data['emergency'])[0]); ?>"
                                placeholder="+1"
                            >
                            <input 
                                type="tel" 
                                id="emergency2" 
                                name="emergency" 
                                class="cms-form2-control" 
                                value="<?php echo esc_attr(implode(' ', array_slice(explode(' ', $admin_data['emergency']), 1))); ?>"
                                required
                            >
                        </div>
                    </div>
                    
                    <div class="cms-form2-group">
                        <label for="status2">Status</label>
                        <select id="status2" name="status" class="cms-form2-control">
                            <option value="active" <?php selected($admin_data['status'], 'active'); ?>>Active</option>
                            <option value="pending" <?php selected($admin_data['status'], 'pending'); ?>>Pending</option>
                            <option value="inactive" <?php selected($admin_data['status'], 'inactive'); ?>>Inactive</option>
                        </select>
                    </div>
                </div>
            </div>
            
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
                            name="ref1_name" 
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
                            name="ref1_cno" 
                            class="cms-form2-control" 
                            value="<?php echo esc_attr($admin_data['ref1_cno']); ?>"
                            required
                        >
                    </div>
                </div>
            </div>
            
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
                            name="ref2_name" 
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
                            name="ref2_cno" 
                            class="cms-form2-control" 
                            value="<?php echo esc_attr($admin_data['ref2_cno']); ?>"
                            required
                        >
                    </div>
                </div>
            </div>
            
            <div class="cms-update2-footer">
                <a href="<?php echo esc_url(remove_query_arg('admin_id', wp_get_referer())); ?>" class="cms-cancel2-button">
                    Cancel
                </a>
                <button type="submit" name="cms_update2_submit" class="cms-update2-button">
                    💾 <?php echo esc_html($atts['button_text']); ?>
                </button>
            </div>
        </form>
    </div>
    
    <script>
    jQuery(document).ready(function($) {
        $('#cms-update-admin2-form').on('submit', function(e) {
            var isValid = true;
            
            $(this).find('[required]').each(function() {
                if (!$(this).val()) {
                    $(this).addClass('error');
                    isValid = false;
                } else {
                    $(this).removeClass('error');
                }
            });
            
            var email = $('#email2');
            var emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (email.val() && !emailPattern.test(email.val())) {
                email.addClass('error');
                isValid = false;
            }
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill all required fields correctly.');
                return false;
            }
            
            $(this).find('.cms-update2-button').text('Updating...').prop('disabled', true);
        });
    });
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_update_admin', 'cms_update_admin_shortcode');
add_shortcode(CMS_ADMIN_UPDATE_SHORTCODE, 'cms_update_admin_shortcode');

function get_cms_admin2_by_id($id) {
    $mock_admins = get_cms_mock_admin2_data();
    
    foreach ($mock_admins as $admin) {
        if ($admin['id'] == $id) {
            return $admin;
        }
    }
    
    return null;
}

function cms_handle_admin2_update() {
    if (isset($_POST['cms_update2_submit']) && isset($_POST['cms_update2_action']) && $_POST['cms_update2_action'] === 'update_admin2') {
        
        $admin_id = intval($_POST['cms_admin2_id']);
        
        wp_redirect(add_query_arg('update', 'success', wp_get_referer()));
        exit;
    }
}
add_action('init', 'cms_handle_admin2_update');

?>