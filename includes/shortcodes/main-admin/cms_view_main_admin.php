<?php
/**
 * CMS View Main Admin Shortcode
 * Display detailed view of a single admin
 * 
 * Usage: [cms_view_main_admin]
 * Usage: [cms_view_main_admin admin_id="1"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


// Define shortcode slug
if (!defined('CMS_MAIN_ADMIN_VIEW_SHORTCODE')) {
    define('CMS_MAIN_ADMIN_VIEW_SHORTCODE', 'cms_main_admin_view');
}


function cms_view_main_admin_shortcode($atts) {
    // Parse attributes
    $atts = shortcode_atts(
        array(
            'admin_id' => 0,
            'show_back_button' => 'yes',
            'show_edit_button' => 'yes',
            'class' => ''
        ),
        $atts,
        'cms_view_main_admin'
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
error_log('CMS View - Admin ID: ' . $admin_id);
    
    if (!$admin_id) {
        return '<div style="padding: 30px; background: #fff3cd; color: #856404; border-radius: 12px; text-align: center; font-size: 16px;">🔍 Please select an admin to view.</div>';
    }
    
    // Get admin data - Replace with your database query
    $admin = get_cms_admin_by_id($admin_id);
    
    if (!$admin) {
        return '<div style="padding: 30px; background: #ffe8e8; color: #b34141; border-radius: 12px; text-align: center; font-size: 16px;">❌ Admin not found.</div>';
    }
    
    ob_start();
    ?>
    
    <style>
    /* View Profile Styles */
    .cms-view-container {
        max-width: 900px;
        margin: 30px auto;
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 20px 50px rgba(0,0,0,0.08);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        overflow: hidden;
    }
    
    .cms-view-header {
        background: linear-gradient(145deg, #1a2b3c, #0f1a26);
        padding: 40px 35px;
        color: white;
        position: relative;
    }
    
    .cms-view-avatar {
        width: 100px;
        height: 100px;
        background: linear-gradient(145deg, #007cba, #005a87);
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
    
    .cms-view-name {
        font-size: 32px;
        font-weight: 700;
        margin: 0 0 5px 0;
        letter-spacing: -0.5px;
    }
    
    .cms-view-username {
        font-size: 18px;
        opacity: 0.9;
        margin: 0 0 15px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .cms-view-badge {
        display: inline-block;
        padding: 8px 20px;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        margin-top: 10px;
    }
    
    .cms-badge-active {
        background: #10b981;
        color: white;
    }
    
    .cms-badge-pending {
        background: #f59e0b;
        color: white;
    }
    
    .cms-badge-inactive {
        background: #ef4444;
        color: white;
    }
    
    .cms-view-nav {
        display: flex;
        gap: 20px;
        margin-top: 25px;
    }
    
    .cms-nav-btn {
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
    
    .cms-nav-btn:hover {
        background: rgba(255,255,255,0.2);
        transform: translateY(-2px);
    }
    
    .cms-view-content {
        padding: 35px;
    }
    
    .cms-info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
        margin-bottom: 30px;
    }
    
    .cms-info-card {
        background: #f8fafc;
        border-radius: 16px;
        padding: 25px;
        border: 1px solid #e9edf2;
        transition: all 0.2s ease;
    }
    
    .cms-info-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.02);
        border-color: #cbd5e0;
    }
    
    .cms-card-title {
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
    
    .cms-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px dashed #e2e8f0;
    }
    
    .cms-info-row:last-child {
        border-bottom: none;
    }
    
    .cms-info-label {
        font-size: 14px;
        color: #718096;
        font-weight: 500;
    }
    
    .cms-info-value {
        font-size: 15px;
        color: #1a2b3c;
        font-weight: 600;
    }
    
    .cms-ref-section {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 25px;
        margin-top: 20px;
    }
    
    .cms-ref-card {
        background: #ffffff;
        border-radius: 16px;
        padding: 25px;
        border: 1px solid #e2e8f0;
    }
    
    .cms-ref-header {
        display: flex;
        align-items: center;
        gap: 12px;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .cms-ref-number {
        width: 32px;
        height: 32px;
        background: #007cba;
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
    }
    
    .cms-timeline {
        margin-top: 30px;
        background: #f8fafc;
        border-radius: 16px;
        padding: 25px;
    }
    
    .cms-timeline-item {
        display: flex;
        gap: 15px;
        padding: 15px 0;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .cms-timeline-item:last-child {
        border-bottom: none;
    }
    
    .cms-timeline-icon {
        width: 40px;
        height: 40px;
        background: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #007cba;
        font-size: 18px;
    }
    
    .cms-timeline-content {
        flex: 1;
    }
    
    .cms-timeline-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 5px;
    }
    
    .cms-timeline-date {
        font-size: 12px;
        color: #718096;
    }
    
    @media (max-width: 768px) {
        .cms-view-header {
            padding: 30px 25px;
        }
        
        .cms-view-name {
            font-size: 26px;
        }
        
        .cms-view-content {
            padding: 25px;
        }
        
        .cms-info-grid {
            grid-template-columns: 1fr;
        }
        
        .cms-ref-section {
            grid-template-columns: 1fr;
        }
        
        .cms-view-nav {
            flex-direction: column;
        }
        
        .cms-nav-btn {
            width: 100%;
            justify-content: center;
        }
    }
    </style>
    
    <div class="cms-view-container <?php echo esc_attr($atts['class']); ?>">
        
        <!-- Header Section -->
        <div class="cms-view-header">
            <div class="cms-view-avatar">
                <?php echo strtoupper(substr($admin['name'], 0, 1)); ?>
            </div>
            
            <h1 class="cms-view-name"><?php echo esc_html($admin['name']); ?></h1>
            <div class="cms-view-username">
                <span>@<?php echo esc_html($admin['username']); ?></span>
                <span style="opacity: 0.5;">•</span>
                <span><?php echo esc_html($admin['email']); ?></span>
            </div>
            
            <span class="cms-view-badge cms-badge-<?php echo esc_attr($admin['status']); ?>">
                <?php echo esc_html(ucfirst($admin['status'])); ?>
            </span>
            
            <?php if ($atts['show_back_button'] === 'yes' || $atts['show_edit_button'] === 'yes'): ?>
            <div class="cms-view-nav">
                <?php if ($atts['show_back_button'] === 'yes'): ?>
                <a href="<?php echo esc_url(wp_get_referer() ?: home_url()); ?>" class="cms-nav-btn">
                    ← Back to List
                </a>
                <?php endif; ?>
                
                <?php if ($atts['show_edit_button'] === 'yes'): ?>
                <a href="<?php echo esc_url(add_query_arg('admin_id', $admin['id'], get_permalink())); ?>&action=edit" class="cms-nav-btn">
                    ✏️ Edit Profile
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- Content Section -->
        <div class="cms-view-content">
            
            <!-- Contact Information -->
            <div class="cms-info-grid">
                <div class="cms-info-card">
                    <h3 class="cms-card-title">📞 Contact Information</h3>
                    
                    <div class="cms-info-row">
                        <span class="cms-info-label">Phone Number</span>
                        <span class="cms-info-value"><?php echo esc_html($admin['contact']); ?></span>
                    </div>
                    
                    <div class="cms-info-row">
                        <span class="cms-info-label">Emergency Contact</span>
                        <span class="cms-info-value"><?php echo esc_html($admin['emergency']); ?></span>
                    </div>
                    
                    <div class="cms-info-row">
                        <span class="cms-info-label">Father's Name</span>
                        <span class="cms-info-value"><?php echo esc_html($admin['father_name']); ?></span>
                    </div>
                </div>
                
                <div class="cms-info-card">
                    <h3 class="cms-card-title">🆔 ID Information</h3>
                    
                    <div class="cms-info-row">
                        <span class="cms-info-label">Admin ID</span>
                        <span class="cms-info-value">#<?php echo esc_html($admin['id']); ?></span>
                    </div>
                    
                    <div class="cms-info-row">
                        <span class="cms-info-label">Username</span>
                        <span class="cms-info-value"><?php echo esc_html($admin['username']); ?></span>
                    </div>
                    
                    <div class="cms-info-row">
                        <span class="cms-info-label">Email</span>
                        <span class="cms-info-value"><?php echo esc_html($admin['email']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- References Section -->
            <div style="margin-bottom: 30px;">
                <h3 style="font-size: 20px; color: #1a2b3c; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                    <span style="font-size: 24px;">👥</span> Reference Information
                </h3>
                
                <div class="cms-ref-section">
                    <!-- Reference 1 -->
                    <div class="cms-ref-card">
                        <div class="cms-ref-header">
                            <span class="cms-ref-number">1</span>
                            <h4 style="margin: 0; font-size: 18px; color: #2c3e50;">Primary Reference</h4>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <div style="font-size: 14px; color: #718096; margin-bottom: 5px;">Full Name</div>
                            <div style="font-size: 18px; font-weight: 600; color: #1a2b3c;"><?php echo esc_html($admin['ref1_name']); ?></div>
                        </div>
                        
                        <div>
                            <div style="font-size: 14px; color: #718096; margin-bottom: 5px;">Contact Number</div>
                            <div style="font-size: 16px; color: #007cba; font-weight: 600;"><?php echo esc_html($admin['ref1_cno']); ?></div>
                        </div>
                    </div>
                    
                    <!-- Reference 2 -->
                    <div class="cms-ref-card">
                        <div class="cms-ref-header">
                            <span class="cms-ref-number">2</span>
                            <h4 style="margin: 0; font-size: 18px; color: #2c3e50;">Secondary Reference</h4>
                        </div>
                        
                        <div style="margin-bottom: 15px;">
                            <div style="font-size: 14px; color: #718096; margin-bottom: 5px;">Full Name</div>
                            <div style="font-size: 18px; font-weight: 600; color: #1a2b3c;"><?php echo esc_html($admin['ref2_name']); ?></div>
                        </div>
                        
                        <div>
                            <div style="font-size: 14px; color: #718096; margin-bottom: 5px;">Contact Number</div>
                            <div style="font-size: 16px; color: #007cba; font-weight: 600;"><?php echo esc_html($admin['ref2_cno']); ?></div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Account Timeline -->
            <div class="cms-timeline">
                <h3 style="font-size: 18px; color: #1a2b3c; margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px;">
                    <span>📅</span> Account Timeline
                </h3>
                
                <div class="cms-timeline-item">
                    <div class="cms-timeline-icon">📝</div>
                    <div class="cms-timeline-content">
                        <div class="cms-timeline-title">Account Created</div>
                        <div class="cms-timeline-date">January 15, 2024 at 10:30 AM</div>
                    </div>
                </div>
                
                <div class="cms-timeline-item">
                    <div class="cms-timeline-icon">🔄</div>
                    <div class="cms-timeline-content">
                        <div class="cms-timeline-title">Last Updated</div>
                        <div class="cms-timeline-date">February 20, 2024 at 2:45 PM</div>
                    </div>
                </div>
                
                <div class="cms-timeline-item">
                    <div class="cms-timeline-icon">✓</div>
                    <div class="cms-timeline-content">
                        <div class="cms-timeline-title">Last Login</div>
                        <div class="cms-timeline-date">Today at 9:15 AM</div>
                    </div>
                </div>
            </div>
            
            <!-- Print/Export Section -->
            <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: flex-end;">
                <button onclick="window.print()" style="padding: 12px 24px; background: white; border: 2px solid #e2e8f0; border-radius: 40px; color: #4a5568; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    🖨️ Print Profile
                </button>
                <button style="padding: 12px 24px; background: #007cba; border: none; border-radius: 40px; color: white; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    📧 Send Email
                </button>
            </div>
        </div>
    </div>
    
    <?php
    return ob_get_clean();
}
add_shortcode('cms_view_main_admin', 'cms_view_main_admin_shortcode');
add_shortcode(CMS_MAIN_ADMIN_VIEW_SHORTCODE, 'cms_view_main_admin_shortcode');
?>