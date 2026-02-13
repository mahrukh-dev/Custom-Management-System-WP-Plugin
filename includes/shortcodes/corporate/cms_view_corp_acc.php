<?php
/**
 * CMS View Corporate Account Shortcode
 * Display detailed view of a single corporate account
 * 
 * Usage: [cms_view_corp_acc]
 * Usage: [cms_view_corp_acc corp_id="301"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define shortcode slug
if (!defined('CMS_CORP_ACC_VIEW_SHORTCODE')) {
    define('CMS_CORP_ACC_VIEW_SHORTCODE', 'cms_corp_acc_view');
}

function cms_view_corp_acc_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'corp_id' => 0,
            'show_back_button' => 'yes',
            'show_edit_button' => 'yes',
            'class' => ''
        ),
        $atts,
        'cms_view_corp_acc'
    );
    
    $corp_id = $atts['corp_id'];
    if (!$corp_id) {
        $corp_id = get_query_var('corp_id');
        if (!$corp_id && isset($_GET['corp_id'])) {
            $corp_id = intval($_GET['corp_id']);
        }
    }
    
    if (!$corp_id) {
        return '<div style="padding: 30px; background: #f5f0ff; color: #6c5ce7; border-radius: 12px; text-align: center; font-size: 16px;">🔍 Please select a corporate account to view.</div>';
    }
    
    $corp = get_cms_corp_by_id($corp_id);
    
    if (!$corp) {
        return '<div style="padding: 30px; background: #ffe8e8; color: #b34141; border-radius: 12px; text-align: center; font-size: 16px;">❌ Corporate account not found.</div>';
    }
    
    ob_start();
    ?>
    
    <style>
    /* Corporate Account View Styles */
    .cms-corp-view-container {
        max-width: 1000px;
        margin: 30px auto;
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 20px 50px rgba(108,92,231,0.08);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        overflow: hidden;
    }
    
    .cms-corp-view-header {
        background: linear-gradient(145deg, #6c5ce7, #5649c0);
        padding: 40px 35px;
        color: white;
        position: relative;
    }
    
    .cms-corp-view-avatar {
        width: 100px;
        height: 100px;
        background: linear-gradient(145deg, #a29bfe, #6c5ce7);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 42px;
        font-weight: 700;
        color: white;
        margin-bottom: 20px;
        border: 4px solid rgba(255,255,255,0.2);
    }
    
    .cms-corp-view-company {
        font-size: 32px;
        font-weight: 700;
        margin: 0 0 5px 0;
        letter-spacing: -0.5px;
    }
    
    .cms-corp-view-username {
        font-size: 18px;
        opacity: 0.9;
        margin: 0 0 15px 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .cms-corp-view-badge {
        display: inline-block;
        padding: 8px 20px;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        margin-top: 10px;
    }
    
    .cms-corp-badge-active {
        background: #00b894;
        color: white;
    }
    
    .cms-corp-badge-inactive {
        background: #7f8c8d;
        color: white;
    }
    
    .cms-corp-badge-suspended {
        background: #d63031;
        color: white;
    }
    
    .cms-corp-view-nav {
        display: flex;
        gap: 20px;
        margin-top: 25px;
    }
    
    .cms-corp-nav-btn {
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
    
    .cms-corp-nav-btn:hover {
        background: rgba(255,255,255,0.2);
        transform: translateY(-2px);
    }
    
    .cms-corp-view-content {
        padding: 35px;
    }
    
    .cms-corp-info-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 25px;
        margin-bottom: 30px;
    }
    
    .cms-corp-info-card {
        background: #f5f0ff;
        border-radius: 16px;
        padding: 25px;
        border: 1px solid #d9d0ff;
        transition: all 0.2s ease;
    }
    
    .cms-corp-info-card:hover {
        box-shadow: 0 5px 15px rgba(108,92,231,0.05);
        border-color: #6c5ce7;
    }
    
    .cms-corp-card-title {
        font-size: 16px;
        font-weight: 600;
        color: #5649c0;
        margin: 0 0 20px 0;
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 15px;
        border-bottom: 2px solid #d9d0ff;
    }
    
    .cms-corp-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px dashed #d9d0ff;
    }
    
    .cms-corp-info-row:last-child {
        border-bottom: none;
    }
    
    .cms-corp-info-label {
        font-size: 14px;
        color: #718096;
        font-weight: 500;
    }
    
    .cms-corp-info-value {
        font-size: 15px;
        color: #2c3e50;
        font-weight: 600;
    }
    
    .cms-corp-info-value.highlight {
        color: #6c5ce7;
    }
    
    .cms-corp-industry-tag {
        display: inline-block;
        padding: 6px 16px;
        background: #f5f0ff;
        color: #6c5ce7;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
    }
    
    .cms-corp-size-tag {
        display: inline-block;
        padding: 6px 16px;
        background: #00cec9;
        color: white;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        margin-left: 10px;
    }
    
    .cms-corp-website-link {
        color: #6c5ce7;
        text-decoration: none;
        font-size: 16px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }
    
    .cms-corp-website-link:hover {
        text-decoration: underline;
    }
    
    .cms-corp-address-box {
        background: #ffffff;
        border: 1px solid #d9d0ff;
        border-radius: 12px;
        padding: 20px;
        margin-top: 10px;
        line-height: 1.6;
    }
    
    .cms-corp-timeline {
        margin-top: 30px;
        background: #f5f0ff;
        border-radius: 16px;
        padding: 25px;
    }
    
    .cms-corp-timeline-item {
        display: flex;
        gap: 15px;
        padding: 15px 0;
        border-bottom: 1px solid #d9d0ff;
    }
    
    .cms-corp-timeline-item:last-child {
        border-bottom: none;
    }
    
    .cms-corp-timeline-icon {
        width: 40px;
        height: 40px;
        background: #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c5ce7;
        font-size: 18px;
    }
    
    @media (max-width: 768px) {
        .cms-corp-info-grid {
            grid-template-columns: 1fr;
        }
        
        .cms-corp-view-nav {
            flex-direction: column;
        }
        
        .cms-corp-nav-btn {
            width: 100%;
            justify-content: center;
        }
    }
    </style>
    
    <div class="cms-corp-view-container <?php echo esc_attr($atts['class']); ?>">
        
        <div class="cms-corp-view-header">
            <div class="cms-corp-view-avatar">
                <?php echo strtoupper(substr($corp['company_name'], 0, 1)); ?>
            </div>
            
            <div>
                <h1 class="cms-corp-view-company"><?php echo esc_html($corp['company_name']); ?></h1>
                <div class="cms-corp-view-username">
                    <span>@<?php echo esc_html($corp['username']); ?></span>
                    <span style="opacity: 0.5;">•</span>
                    <span><?php echo esc_html($corp['email']); ?></span>
                </div>
                
                <div style="display: flex; align-items: center; gap: 15px; margin-top: 10px;">
                    <span class="cms-corp-view-badge cms-corp-badge-<?php echo esc_attr($corp['status']); ?>">
                        <?php echo esc_html(ucfirst($corp['status'])); ?>
                    </span>
                    
                    <?php if($corp['industry']): ?>
                    <span class="cms-corp-industry-tag">
                        <?php echo esc_html(ucfirst($corp['industry'])); ?>
                    </span>
                    <?php endif; ?>
                    
                    <?php if($corp['company_size']): ?>
                    <span class="cms-corp-size-tag">
                        <?php echo esc_html($corp['company_size']); ?> employees
                    </span>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($atts['show_back_button'] === 'yes' || $atts['show_edit_button'] === 'yes'): ?>
            <div class="cms-corp-view-nav">
                <?php if ($atts['show_back_button'] === 'yes'): ?>
                <a href="<?php echo esc_url(wp_get_referer() ?: home_url('corp-accounts')); ?>" class="cms-corp-nav-btn">
                    ← Back to List
                </a>
                <?php endif; ?>
                
                <?php if ($atts['show_edit_button'] === 'yes'): ?>
                <a href="<?php echo esc_url(home_url('edit-corp-account/' . $corp['id'])); ?>" class="cms-corp-nav-btn">
                    ✏️ Edit Account
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
        
        <div class="cms-corp-view-content">
            
            <!-- Company Information -->
            <div class="cms-corp-info-grid">
                <div class="cms-corp-info-card">
                    <h3 class="cms-corp-card-title">🏢 Company Details</h3>
                    
                    <div class="cms-corp-info-row">
                        <span class="cms-corp-info-label">Company Name</span>
                        <span class="cms-corp-info-value"><?php echo esc_html($corp['company_name']); ?></span>
                    </div>
                    
                    <div class="cms-corp-info-row">
                        <span class="cms-corp-info-label">Username</span>
                        <span class="cms-corp-info-value">@<?php echo esc_html($corp['username']); ?></span>
                    </div>
                    
                    <div class="cms-corp-info-row">
                        <span class="cms-corp-info-label">Account ID</span>
                        <span class="cms-corp-info-value">#<?php echo esc_html($corp['id']); ?></span>
                    </div>
                </div>
                
                <div class="cms-corp-info-card">
                    <h3 class="cms-corp-card-title">👤 Contact Person</h3>
                    
                    <div class="cms-corp-info-row">
                        <span class="cms-corp-info-label">Name</span>
                        <span class="cms-corp-info-value"><?php echo esc_html($corp['contact_name']); ?></span>
                    </div>
                    
                    <div class="cms-corp-info-row">
                        <span class="cms-corp-info-label">Email</span>
                        <span class="cms-corp-info-value"><?php echo esc_html($corp['email']); ?></span>
                    </div>
                    
                    <div class="cms-corp-info-row">
                        <span class="cms-corp-info-label">Phone</span>
                        <span class="cms-corp-info-value"><?php echo esc_html($corp['phone']); ?></span>
                    </div>
                </div>
            </div>
            
            <!-- Address & Website -->
            <div class="cms-corp-info-grid">
                <div class="cms-corp-info-card">
                    <h3 class="cms-corp-card-title">📍 Address</h3>
                    
                    <div class="cms-corp-address-box">
                        <?php echo nl2br(esc_html($corp['address'])); ?>
                    </div>
                </div>
                
                <div class="cms-corp-info-card">
                    <h3 class="cms-corp-card-title">🌐 Website</h3>
                    
                    <?php if($corp['website']): ?>
                    <div style="text-align: center; padding: 20px;">
                        <a href="https://<?php echo esc_attr($corp['website']); ?>" target="_blank" class="cms-corp-website-link">
                            <span style="font-size: 48px;">🌐</span><br>
                            <?php echo esc_html($corp['website']); ?>
                        </a>
                    </div>
                    <?php else: ?>
                    <div style="text-align: center; padding: 20px; color: #718096;">
                        No website provided
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Business Classification -->
            <div class="cms-corp-info-card" style="margin-bottom: 30px;">
                <h3 class="cms-corp-card-title">📊 Business Classification</h3>
                
                <div class="cms-corp-info-grid" style="margin-bottom: 0;">
                    <div>
                        <div class="cms-corp-info-row">
                            <span class="cms-corp-info-label">Industry</span>
                            <span class="cms-corp-info-value highlight"><?php echo esc_html(ucfirst($corp['industry'] ?: 'Not specified')); ?></span>
                        </div>
                    </div>
                    
                    <div>
                        <div class="cms-corp-info-row">
                            <span class="cms-corp-info-label">Company Size</span>
                            <span class="cms-corp-info-value highlight"><?php echo esc_html($corp['company_size'] ?: 'Not specified'); ?></span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Timeline -->
            <div class="cms-corp-timeline">
                <h3 style="font-size: 18px; color: #5649c0; margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px;">
                    <span>📅</span> Account Timeline
                </h3>
                
                <div class="cms-corp-timeline-item">
                    <div class="cms-corp-timeline-icon">🏢</div>
                    <div>
                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">Account Created</div>
                        <div style="font-size: 12px; color: #718096;">January 15, 2024 at 10:30 AM</div>
                    </div>
                </div>
                
                <div class="cms-corp-timeline-item">
                    <div class="cms-corp-timeline-icon">🔄</div>
                    <div>
                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">Last Updated</div>
                        <div style="font-size: 12px; color: #718096;">February 20, 2024 at 2:45 PM</div>
                    </div>
                </div>
                
                <div class="cms-corp-timeline-item">
                    <div class="cms-corp-timeline-icon">📧</div>
                    <div>
                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">Last Contact</div>
                        <div style="font-size: 12px; color: #718096;">March 5, 2024 at 11:20 AM</div>
                    </div>
                </div>
            </div>
            
            <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: flex-end;">
                <button onclick="window.print()" style="padding: 12px 24px; background: white; border: 2px solid #d9d0ff; border-radius: 40px; color: #4a5568; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px;">
                    🖨️ Print Profile
                </button>
                <a href="mailto:<?php echo esc_attr($corp['email']); ?>" style="padding: 12px 24px; background: #6c5ce7; border: none; border-radius: 40px; color: white; font-weight: 600; cursor: pointer; display: flex; align-items: center; gap: 8px; text-decoration: none;">
                    📧 Send Email
                </a>
            </div>
        </div>
    </div>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_view_corp_acc', 'cms_view_corp_acc_shortcode');
add_shortcode(CMS_CORP_ACC_VIEW_SHORTCODE, 'cms_view_corp_acc_shortcode');

?>