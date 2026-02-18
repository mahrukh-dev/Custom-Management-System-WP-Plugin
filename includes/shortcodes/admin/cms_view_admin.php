<?php
/**
 * CMS View Admin Shortcode
 * Display detailed view of a single admin from database
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


/**
 * View Admin Shortcode
 */
function cms_view_admin_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'admin_id' => 0,
            'show_back_button' => 'yes',
            'show_edit_button' => 'yes',
            'show_activity' => 'yes',
            'class' => ''
        ),
        $atts,
        'cms_view_admin'
    );
    
    // Get admin ID from various sources
    $admin_id = $atts['admin_id'];
    if (!$admin_id) {
        $admin_id = get_query_var('admin_id');
        if (!$admin_id && isset($_GET['admin_id'])) {
            $admin_id = intval($_GET['admin_id']);
        }
    }
    
    // Also check for username parameter
    if (!$admin_id && isset($_GET['username'])) {
        $username = sanitize_user($_GET['username']);
        $admin = cms_get_admin_by_username($username);
        if ($admin) {
            $admin_id = $admin['id'];
        }
    }
    
    if (!$admin_id) {
        return '<div style="padding: 30px; background: #fff3cd; color: #856404; border-radius: 12px; text-align: center; font-size: 16px;">
            🔍 Please select an admin to view.
        </div>';
    }
    
    // Get admin data from database
    $admin = cms_get_admin_by_id($admin_id);
    
    if (!$admin) {
        return '<div style="padding: 30px; background: #ffe8e8; color: #b34141; border-radius: 12px; text-align: center; font-size: 16px;">
            ❌ Admin not found in database.
        </div>';
    }
    
    // Get activity summary
    $activity_summary = array();
    $recent_activities = array();
    
    if ($atts['show_activity'] === 'yes') {
        $activity_summary = cms_get_admin_activity_summary($admin['username']);
        $recent_activities = cms_get_admin_recent_activity($admin['username'], 5);
    }
    
    // Format dates
    $created_date = !empty($admin['created_at']) ? date('F j, Y', strtotime($admin['created_at'])) : 'N/A';
    $created_time = !empty($admin['created_at']) ? date('g:i A', strtotime($admin['created_at'])) : '';
    
    $updated_date = !empty($admin['updated_at']) ? date('F j, Y', strtotime($admin['updated_at'])) : 'Never';
    $updated_time = !empty($admin['updated_at']) ? date('g:i A', strtotime($admin['updated_at'])) : '';
    
    $last_login_date = !empty($admin['last_login']) ? date('F j, Y', strtotime($admin['last_login'])) : 'Never';
    $last_login_time = !empty($admin['last_login']) ? date('g:i A', strtotime($admin['last_login'])) : '';
    
    ob_start();
    ?>
    
    <style>
    .cms-view2-container {
        max-width: 1000px;
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
        text-transform: uppercase;
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
        flex-wrap: wrap;
    }
    
    .cms-view2-badge {
        display: inline-block;
        padding: 8px 20px;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        margin-top: 10px;
        text-transform: capitalize;
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
    
    .cms-badge2-suspended {
        background: #6b7280;
        color: white;
    }
    
    .cms-view2-nav {
        display: flex;
        gap: 20px;
        margin-top: 25px;
        flex-wrap: wrap;
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
    
    .cms-info2-card:hover {
        box-shadow: 0 5px 15px rgba(0,0,0,0.05);
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
        transition: all 0.2s ease;
    }
    
    .cms-ref2-card:hover {
        border-color: #27ae60;
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
    
    .cms-activity2-summary {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }
    
    .cms-activity2-card {
        background: linear-gradient(145deg, #f8fafc, #ffffff);
        border-radius: 16px;
        padding: 20px;
        text-align: center;
        border: 1px solid #e9edf2;
    }
    
    .cms-activity2-number {
        font-size: 32px;
        font-weight: 700;
        color: #27ae60;
        margin-bottom: 5px;
    }
    
    .cms-activity2-label {
        font-size: 14px;
        color: #718096;
    }
    
    .cms-timeline2 {
        background: #f8fafc;
        border-radius: 16px;
        padding: 25px;
        margin-top: 30px;
    }
    
    .cms-timeline2-item {
        display: flex;
        gap: 15px;
        padding: 15px 0;
        border-bottom: 1px solid #e2e8f0;
        transition: all 0.2s ease;
    }
    
    .cms-timeline2-item:last-child {
        border-bottom: none;
    }
    
    .cms-timeline2-item:hover {
        background: #ffffff;
        padding-left: 15px;
        padding-right: 15px;
        border-radius: 8px;
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
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
    }
    
    .cms-timeline2-content {
        flex: 1;
    }
    
    .cms-timeline2-title {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }
    
    .cms-timeline2-date {
        font-size: 12px;
        color: #718096;
    }
    
    .cms-timeline2-status {
        display: inline-block;
        padding: 2px 8px;
        border-radius: 12px;
        font-size: 10px;
        font-weight: 500;
        margin-left: 10px;
    }
    
    .cms-status-approved {
        background: #e3f7ec;
        color: #0a5c36;
    }
    
    .cms-status-pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .cms-status-rejected {
        background: #ffe8e8;
        color: #b34141;
    }
    
    .cms-position2-tag {
        display: inline-block;
        padding: 6px 16px;
        background: rgba(255,255,255,0.2);
        color: white;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        margin-left: 15px;
        border: 1px solid rgba(255,255,255,0.3);
    }
    
    .cms-id2-badge {
        background: rgba(255,255,255,0.1);
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        display: inline-block;
        margin-left: 10px;
    }
    
    .cms-action2-buttons {
        display: flex;
        gap: 15px;
        margin-top: 30px;
        justify-content: flex-end;
        flex-wrap: wrap;
    }
    
    .cms-action2-btn {
        padding: 12px 24px;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
    }
    
    .cms-action2-btn.print {
        background: white;
        border: 2px solid #e2e8f0;
        color: #4a5568;
    }
    
    .cms-action2-btn.print:hover {
        background: #f8fafc;
        border-color: #cbd5e0;
    }
    
    .cms-action2-btn.email {
        background: #27ae60;
        border: none;
        color: white;
    }
    
    .cms-action2-btn.email:hover {
        background: #1e8449;
        transform: translateY(-2px);
        box-shadow: 0 5px 15px rgba(39,174,96,0.3);
    }
    
    @media (max-width: 768px) {
        .cms-view2-header {
            padding: 30px 20px;
        }
        
        .cms-view2-content {
            padding: 25px 20px;
        }
        
        .cms-info2-grid,
        .cms-ref2-section,
        .cms-activity2-summary {
            grid-template-columns: 1fr;
        }
        
        .cms-view2-nav {
            flex-direction: column;
        }
        
        .cms-nav2-btn {
            width: 100%;
            justify-content: center;
        }
    }
    </style>
    
    <div class="cms-view2-container <?php echo esc_attr($atts['class']); ?>">
        
        <div class="cms-view2-header">
            <div class="cms-view2-avatar">
                <?php echo esc_html(substr($admin['name'], 0, 1)); ?>
            </div>
            
            <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 15px;">
                <div>
                    <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                        <h1 class="cms-view2-name"><?php echo esc_html($admin['name']); ?></h1>
                        <span class="cms-id2-badge">ID: #<?php echo esc_html($admin['id']); ?></span>
                    </div>
                    
                    <div class="cms-view2-username">
                        <span>@<?php echo esc_html($admin['username']); ?></span>
                        <span style="opacity: 0.5;">•</span>
                        <span><?php echo esc_html($admin['email']); ?></span>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
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
                <a href="<?php echo esc_url(home_url('edit-admin?admin_id=' . $admin['id'])); ?>" class="cms-nav2-btn">
                    ✏️ Edit Profile
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="cms-view2-content">
            
            <?php if ($atts['show_activity'] === 'yes' && !empty($activity_summary)): ?>
            <div class="cms-activity2-summary">
                <div class="cms-activity2-card">
                    <div class="cms-activity2-number"><?php echo esc_html($activity_summary['requests_processed']); ?></div>
                    <div class="cms-activity2-label">Requests Processed</div>
                </div>
                <div class="cms-activity2-card">
                    <div class="cms-activity2-number"><?php echo esc_html($activity_summary['messages_sent']); ?></div>
                    <div class="cms-activity2-label">Messages Sent</div>
                </div>
                <div class="cms-activity2-card">
                    <div class="cms-activity2-number"><?php echo esc_html($activity_summary['messages_received']); ?></div>
                    <div class="cms-activity2-label">Messages Received</div>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="cms-info2-grid">
                <div class="cms-info2-card">
                    <h3 class="cms-card2-title">📞 Contact Information</h3>
                    
                    <div class="cms-info2-row">
                        <span class="cms-info2-label">Phone Number</span>
                        <span class="cms-info2-value"><?php echo esc_html($admin['contact_num'] ?: 'Not provided'); ?></span>
                    </div>
                    
                    <div class="cms-info2-row">
                        <span class="cms-info2-label">Emergency Contact</span>
                        <span class="cms-info2-value"><?php echo esc_html($admin['emergency_cno'] ?: 'Not provided'); ?></span>
                    </div>
                    
                    <div class="cms-info2-row">
                        <span class="cms-info2-label">Father's Name</span>
                        <span class="cms-info2-value"><?php echo esc_html($admin['father_name']); ?></span>
                    </div>
                </div>
                
                <div class="cms-info2-card">
                    <h3 class="cms-card2-title">🆔 Account Information</h3>
                    
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
                    
                    <div class="cms-info2-row">
                        <span class="cms-info2-label">Role</span>
                        <span class="cms-info2-value"><?php echo esc_html(ucfirst($admin['role'])); ?></span>
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
                        <div class="cms-timeline2-title">Account Created</div>
                        <div class="cms-timeline2-date"><?php echo esc_html($created_date); ?> at <?php echo esc_html($created_time); ?></div>
                    </div>
                </div>
                
                <div class="cms-timeline2-item">
                    <div class="cms-timeline2-icon">🔄</div>
                    <div class="cms-timeline2-content">
                        <div class="cms-timeline2-title">Last Updated</div>
                        <div class="cms-timeline2-date"><?php echo esc_html($updated_date); ?> <?php echo $updated_time ? 'at ' . esc_html($updated_time) : ''; ?></div>
                    </div>
                </div>
                
                <div class="cms-timeline2-item">
                    <div class="cms-timeline2-icon">✓</div>
                    <div class="cms-timeline2-content">
                        <div class="cms-timeline2-title">Last Login</div>
                        <div class="cms-timeline2-date"><?php echo esc_html($last_login_date); ?> <?php echo $last_login_time ? 'at ' . esc_html($last_login_time) : ''; ?></div>
                    </div>
                </div>
            </div>
            
            <?php if ($atts['show_activity'] === 'yes' && !empty($recent_activities)): ?>
            <div class="cms-timeline2" style="margin-top: 20px;">
                <h3 style="font-size: 18px; color: #1a2b3c; margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px;">
                    <span>📊</span> Recent Activity
                </h3>
                
                <?php foreach ($recent_activities as $activity): ?>
                <div class="cms-timeline2-item">
                    <div class="cms-timeline2-icon">
                        <?php echo $activity['type'] === 'request' ? '📋' : '💬'; ?>
                    </div>
                    <div class="cms-timeline2-content">
                        <div class="cms-timeline2-title">
                            <?php echo esc_html($activity['action']); ?>
                            <?php if (isset($activity['status'])): ?>
                                <span class="cms-timeline2-status cms-status-<?php echo esc_attr($activity['status']); ?>">
                                    <?php echo esc_html(ucfirst($activity['status'])); ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div style="font-size: 13px; color: #4a5568; margin-bottom: 3px;">
                            <?php echo esc_html($activity['details']); ?>
                        </div>
                        <div class="cms-timeline2-date">
                            <?php echo esc_html(date('F j, Y g:i A', strtotime($activity['time']))); ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <div class="cms-action2-buttons">
                <button onclick="window.print()" class="cms-action2-btn print">
                    🖨️ Print Profile
                </button>
                <a href="mailto:<?php echo esc_attr($admin['email']); ?>" class="cms-action2-btn email">
                    📧 Send Email
                </a>
            </div>
        </div>
    </div>
    
    <script>
    // Add print optimization
    window.onbeforeprint = function() {
        document.querySelectorAll('.cms-nav2-btn, .cms-action2-buttons').forEach(el => {
            el.style.display = 'none';
        });
    };
    
    window.onafterprint = function() {
        document.querySelectorAll('.cms-nav2-btn, .cms-action2-buttons').forEach(el => {
            el.style.display = 'flex';
        });
    };
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_view_admin', 'cms_view_admin_shortcode');
add_shortcode(CMS_ADMIN_VIEW_SHORTCODE, 'cms_view_admin_shortcode');