<?php
/**
 * CMS View Admin Shortcode
 * Display detailed view of a single admin
 * 
 * Usage: [cms_view_admin]
 * Usage: [cms_view_admin admin_id="101"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define shortcode slug
if (!defined('CMS_ADMIN_VIEW_SHORTCODE')) {
    define('CMS_ADMIN_VIEW_SHORTCODE', 'cms_admin_view');
}

function cms_view_admin_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'admin_id' => 0,
            'show_back_button' => 'yes',
            'show_edit_button' => 'yes',
            'class' => ''
        ),
        $atts,
        'cms_view_admin'
    );
    
    $admin_id = $atts['admin_id'];
    if (!$admin_id) {
        $admin_id = get_query_var('admin_id');
        if (!$admin_id && isset($_GET['admin_id'])) {
            $admin_id = intval($_GET['admin_id']);
        }
    }
    
    if (!$admin_id) {
        return '<div style="padding: 30px; background: #fff3cd; color: #856404; border-radius: 12px; text-align: center; font-size: 16px;">🔍 Please select an admin to view.</div>';
    }
    
    $admin = get_cms_admin2_by_id($admin_id);
    
    if (!$admin) {
        return '<div style="padding: 30px; background: #ffe8e8; color: #b34141; border-radius: 12px; text-align: center; font-size: 16px;">❌ Admin not found.</div>';
    }
    
    ob_start();
    ?>
    
    <style>
    .cms-view2-container {
        max-width: 900px;
        margin: 30px auto;
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.08);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        overflow: hidden;
    }
    
    .cms-view2-header {
        background: linear-gradient(145deg, #1e8449, #0f5c33);
        padding: 40px 35px;
        color: white;
        position: relative;
    }
    
    .cms-view2-avatar {
        width: 100px;
        height: 100px;
        background: linear-gradient(145deg, #27ae60, #1e8449);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 42px;
        font-weight: 700;
        color: white;
        margin-bottom: 20px;
        border: 4px solid rgba(255,255,255,0.2);
    }
    
    .cms-view2-name {
        font-size: 32px;
        font-weight: 700;
        margin: 0 0 5px 0;
        letter-spacing: -0.5px;
    }
    
    .cms-view2-username {
        font-size: 18px;
        opacity: 0.9;
        margin: 0 0 15px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .cms-view2-badge {
        display: inline-block;
        padding: 8px 20px;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        margin-top: 10px;
    }
    
    .cms-badge2-active {
        background: #10b981;
        color: white;
    }
    
    .cms-badge2-pending {
        background: #f59e0b;
        color: white;
    }
    
    .cms-badge2-inactive {
        background: #ef4444;
        color: white;
    }
    
    .cms-view2-nav {
        display: flex;
        gap: 20px;
        margin-top: 25px;
    }
    
    .cms-nav2-btn {
        padding: 12px 24px;
        background: rgba(255,255,255,0.1);
        color: white;
        text-decoration: none;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
        border: 1px solid rgba(255,255,255,0.2);
    }
    
    .cms-nav2-btn:hover {
        background: rgba(255,255,255,0.2);
        transform: translateY(-2px);
    }
    
    .cms-view2-content {
        padding: 35px;
    }
    
    .cms-info2-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
        margin-bottom: 30px;
    }
    
    .cms-info2-card {
        background: #f8fafc;
        border-radius: 16px;
        padding: 25px;
        border: 1px solid #e9edf2;
        transition: all 0.2s ease;
    }
    
    .cms-card2-title {
        font-size: 16px;
        font-weight: 600;
        color: #2c3e50;
        margin: 0 0 20px 0;
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .cms-info2-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px dashed #e2e8f0;
    }
    
    .cms-info2-row:last-child {
        border-bottom: none;
    }
    
    .cms-info2-label {
        font-size: 14px;
        color: #718096;
        font-weight: 500;
    }
    
    .cms-info2-value {
        font-size: 15px;
        color: #1a2b3c;
        font-weight: 600;
    }
    
    .cms-ref2-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-top: 20px;
    }
    
    .cms-ref2-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 25px;
        border: 1px solid #e2e8f0;
    }
    
    .cms-ref2-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .cms-ref2-number {
        width: 32px;
        height: 32px;
        background: #27ae60;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
    }
    
    .cms-timeline2 {
        margin-top: 30px;
        background: #f8fafc;
        border-radius: 16px;
        padding: 25px;
    }
    
    .cms-timeline2-item {
        display: flex;
        gap: 15px;
        padding: 15px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .cms-timeline2-item:last-child {
        border-bottom: none;
    }
    
    .cms-timeline2-icon {
        width: 40px;
        height: 40px;
        background: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #27ae60;
        font-size: 18px;
    }
    
    .cms-position2-tag {
        display: inline-block;
        padding: 6px 16px;
        background: #e8f5e9;
        color: #1e8449;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        margin-left: 15px;
    }
    </style>
    
    <div class="cms-view2-container <?php echo esc_attr($atts['class']); ?>">
        
        <div class="cms-view2-header">
            <div class="cms-view2-avatar">
                <?php echo strtoupper(substr($admin['name'], 0, 1)); ?>
            </div>
            
            <div style="display: flex; align-items: center; justify-content: space-between;">
                <div>
                    <h1 class="cms-view2-name"><?php echo esc_html($admin['name']); ?></h1>
                    <div class="cms-view2-username">
                        <span>@<?php echo esc_html($admin['username']); ?></span>
                        <span style="opacity: 0.5;">•</span>
                        <span><?php echo esc_html($admin['email']); ?></span>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <span class="cms-view2-badge cms-badge2-<?php echo esc_attr($admin['status']); ?>">
                            <?php echo esc_html(ucfirst($admin['status'])); ?>
                        </span>
                        <span class="cms-position2-tag">
                            <?php echo esc_html($admin['position']); ?>
                        </span>
                    </div>
                </div>
            </div>
            
            <?php if ($atts['show_back_button'] === 'yes' || $atts['show_edit_button'] === 'yes'): ?>
            <div class="cms-view2-nav">
                <?php if ($atts['show_back_button'] === 'yes'): ?>
                <a href="<?php echo esc_url(wp_get_referer() ?: home_url('admin-list')); ?>" class="cms-nav2-btn">
                    ← Back to List
                </a>
                <?php endif; ?>
                
                <?php if ($atts['show_edit_button'] === 'yes'): ?>
                <a href="<?php echo esc_url(home_url('edit-admin2/' . $admin['id'])); ?>" class="cms-nav2-btn">
                    ✏️ Edit Profile
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="cms-view2-content">
            
            <div class="cms-info2-grid">
                <div class="cms-info2-card">
                    <h3 class="cms-card2-title">📞 Contact Information</h3>
                    
                    <div class="cms-info2-row">
                        <span class="cms-info2-label">Phone Number</span>
                        <span class="cms-info2-value"><?php echo esc_html($admin['contact']); ?></span>
                    </div>
                    
                    <div class="cms-info2-row">
                        <span class="cms-info2-label">Emergency Contact</span>
                        <span class="cms-info2-value"><?php echo esc_html($admin['emergency']); ?></span>
                    </div>
                    
                    <div class="cms-info2-row">
                        <span class="cms-info2-label">Father's Name</span>
                        <span class="cms-info2-value"><?php echo esc_html($admin['father_name']); ?></span>
                    </div>
                </div>
                
                <div class="cms-info2-card">
                    <h3 class="cms-card2-title">🆔 ID Information</h3>
                    
                    <div class="cms-info2-row">
                        <span class="cms-info2-label">Admin ID</span>
                        <span class="cms-info2-value">#<?php echo esc_html($admin['id']); ?></span>
                    </div>
                    
                    <div class="cms-info2-row">
                        <span class="cms-info2-label">Username</span>
                        <span class="cms-info2-value"><?php echo esc_html($admin['username']); ?></span>
                    </div>
                    
                    <div class="cms-info2-row">
                        <span class="cms-info2-label">Email</span>
                        <span class="cms-info2-value"><?php echo esc_html($admin['email']); ?></span>
                    </div>
                    
                    <div class="cms-info2-row">
                        <span class="cms-info2-label">Position</span>
                        <span class="cms-info2-value"><?php echo esc_html($admin['position']); ?></span>
                    </div>
                </div>
            </div>
            
            <div style="margin-bottom: 30px;">
                <h3 style="font-size: 20px; color: #1a2b3c; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">👥</span> Reference Information
                </h3>
                
                <div class="cms-ref2-section">
                    <div class="cms-ref2-card">
                        <div class="cms-ref2-header">
                            <span class="cms-ref2-number">1</span>
                            <h4 style="margin: 0; font-size: 18px; color: #2c3e50;">Primary Reference</h4>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <div style="font-size: 14px; color: #718096; margin-bottom: 5px;">Full Name</div>
                            <div style="font-size: 18px; font-weight: 600; color: #1a2b3c;"><?php echo esc_html($admin['ref1_name']); ?></div>
                        </div>
                        
                        <div>
                            <div style="font-size: 14px; color: #718096; margin-bottom: 5px;">Contact Number</div>
                            <div style="font-size: 16px; color: #27ae60; font-weight: 600;"><?php echo esc_html($admin['ref1_cno']); ?></div>
                        </div>
                    </div>
                    
                    <div class="cms-ref2-card">
                        <div class="cms-ref2-header">
                            <span class="cms-ref2-number">2</span>
                            <h4 style="margin: 0; font-size: 18px; color: #2c3e50;">Secondary Reference</h4>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <div style="font-size: 14px; color: #718096; margin-bottom: 5px;">Full Name</div>
                            <div style="font-size: 18px; font-weight: 600; color: #1a2b3c;"><?php echo esc_html($admin['ref2_name']); ?></div>
                        </div>
                        
                        <div>
                            <div style="font-size: 14px; color: #718096; margin-bottom: 5px;">Contact Number</div>
                            <div style="font-size: 16px; color: #27ae60; font-weight: 600;"><?php echo esc_html($admin['ref2_cno']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="cms-timeline2">
                <h3 style="font-size: 18px; color: #1a2b3c; margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px;">
                    <span>📅</span> Account Timeline
                </h3>
                
                <div class="cms-timeline2-item">
                    <div class="cms-timeline2-icon">📝</div>
                    <div class="cms-timeline2-content">
                        <div class="cms-timeline2-title" style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">Account Created</div>
                        <div class="cms-timeline2-date" style="font-size: 12px; color: #718096;">January 15, 2024 at 10:30 AM</div>
                    </div>
                </div>
                
                <div class="cms-timeline2-item">
                    <div class="cms-timeline2-icon">🔄</div>
                    <div class="cms-timeline2-content">
                        <div class="cms-timeline2-title" style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">Last Updated</div>
                        <div class="cms-timeline2-date" style="font-size: 12px; color: #718096;">February 20, 2024 at 2:45 PM</div>
                    </div>
                </div>
                
                <div class="cms-timeline2-item">
                    <div class="cms-timeline2-icon">✓</div>
                    <div class="cms-timeline2-content">
                        <div class="cms-timeline2-title" style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">Last Login</div>
                        <div class="cms-timeline2-date" style="font-size: 12px; color: #718096;">Today at 9:15 AM</div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: flex-end;">
                <button onclick="window.print()" style="padding: 12px 24px; background: white; border: 2px solid #e2e8f0; border-radius: 40px; color: #4a5568; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    🖨️ Print Profile
                </button>
                <a href="mailto:<?php echo esc_attr($admin['email']); ?>" style="padding: 12px 24px; background: #27ae60; border: none; border-radius: 40px; color: white; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; text-decoration: none;">
                    📧 Send Email
                </a>
            </div>
        </div>
    </div>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_view_admin', 'cms_view_admin_shortcode');
add_shortcode(CMS_ADMIN_VIEW_SHORTCODE, 'cms_view_admin_shortcode');

?>