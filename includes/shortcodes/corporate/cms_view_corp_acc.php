<?php
/**
 * CMS View Corporate Account Shortcode
 * Display detailed view of a single corporate account from database
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

/**
 * View Corporate Account Shortcode
 */
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
    
    // Get corporate account from database
    $corp = cms_get_corporate_account_by_id($corp_id);
    
    if (!$corp) {
        return '<div style="padding: 30px; background: #ffe8e8; color: #b34141; border-radius: 12px; text-align: center; font-size: 16px;">❌ Corporate account not found.</div>';
    }
    
    ob_start();
    ?>
    
    <style>
    /* Corporate Account View Styles - Matching Theme */
    :root {
        --corp-primary: #6c5ce7;
        --corp-primary-dark: #5649c0;
        --corp-primary-light: #a29bfe;
        --corp-secondary: #00cec9;
        --corp-accent: #0984e3;
        --corp-success: #00b894;
        --corp-danger: #d63031;
        --corp-warning: #fdcb6e;
        --corp-bg-light: #f5f0ff;
        --corp-border: #d9d0ff;
    }
    
    .cms-corp-view-container {
        max-width: 1000px;
        margin: 30px auto;
        background: #ffffff;
        border-radius: 24px;
        box-shadow: 0 20px 50px rgba(108,92,231,0.08);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        overflow: hidden;
        border-top: 5px solid var(--corp-primary);
    }
    
    .cms-corp-view-header {
        background: linear-gradient(145deg, var(--corp-primary), var(--corp-primary-dark));
        padding: 40px 35px;
        color: white;
        position: relative;
    }
    
    .cms-corp-view-avatar {
        width: 100px;
        height: 100px;
        background: linear-gradient(145deg, var(--corp-primary-light), var(--corp-primary));
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 42px;
        font-weight: 700;
        color: white;
        margin-bottom: 20px;
        border: 4px solid rgba(255,255,255,0.2);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
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
        flex-wrap: wrap;
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
        background: var(--corp-success);
        color: white;
    }
    
    .cms-corp-badge-inactive {
        background: #7f8c8d;
        color: white;
    }
    
    .cms-corp-badge-suspended {
        background: var(--corp-danger);
        color: white;
    }
    
    .cms-corp-view-nav {
        display: flex;
        gap: 15px;
        margin-top: 25px;
        flex-wrap: wrap;
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
        backdrop-filter: blur(5px);
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
        background: var(--corp-bg-light);
        border-radius: 16px;
        padding: 25px;
        border: 1px solid var(--corp-border);
        transition: all 0.2s ease;
    }
    
    .cms-corp-info-card:hover {
        box-shadow: 0 8px 20px rgba(108,92,231,0.1);
        border-color: var(--corp-primary);
    }
    
    .cms-corp-card-title {
        font-size: 16px;
        font-weight: 600;
        color: var(--corp-primary-dark);
        margin: 0 0 20px 0;
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 15px;
        border-bottom: 2px solid var(--corp-border);
    }
    
    .cms-corp-info-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 12px 0;
        border-bottom: 1px dashed var(--corp-border);
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
        color: var(--corp-primary);
    }
    
    .cms-corp-industry-tag {
        display: inline-block;
        padding: 6px 16px;
        background: var(--corp-bg-light);
        color: var(--corp-primary);
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        border: 1px solid var(--corp-border);
    }
    
    .cms-corp-size-tag {
        display: inline-block;
        padding: 6px 16px;
        background: var(--corp-secondary);
        color: white;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        margin-left: 10px;
    }
    
    .cms-corp-website-link {
        color: var(--corp-primary);
        text-decoration: none;
        font-size: 16px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        word-break: break-all;
    }
    
    .cms-corp-website-link:hover {
        text-decoration: underline;
    }
    
    .cms-corp-address-box {
        background: #ffffff;
        border: 1px solid var(--corp-border);
        border-radius: 12px;
        padding: 20px;
        margin-top: 10px;
        line-height: 1.6;
        color: #2c3e50;
    }
    
    .cms-corp-timeline {
        margin-top: 30px;
        background: var(--corp-bg-light);
        border-radius: 16px;
        padding: 25px;
        border: 1px solid var(--corp-border);
    }
    
    .cms-corp-timeline-item {
        display: flex;
        gap: 15px;
        padding: 15px 0;
        border-bottom: 1px solid var(--corp-border);
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
        color: var(--corp-primary);
        font-size: 18px;
        border: 2px solid var(--corp-border);
    }
    
    .cms-corp-print-btn {
        padding: 12px 24px;
        background: white;
        border: 2px solid var(--corp-border);
        border-radius: 40px;
        color: #4a5568;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.2s ease;
    }
    
    .cms-corp-print-btn:hover {
        background: var(--corp-bg-light);
        border-color: var(--corp-primary);
    }
    
    .cms-corp-email-btn {
        padding: 12px 24px;
        background: linear-gradient(145deg, var(--corp-primary), var(--corp-primary-dark));
        border: none;
        border-radius: 40px;
        color: white;
        font-weight: 600;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    
    .cms-corp-email-btn:hover {
        background: linear-gradient(145deg, var(--corp-primary-dark), #4338b0);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(108,92,231,0.2);
    }
    
    .cms-corp-meta-info {
        display: flex;
        gap: 20px;
        margin-top: 15px;
        flex-wrap: wrap;
    }
    
    .cms-corp-meta-item {
        display: flex;
        align-items: center;
        gap: 8px;
        background: rgba(255,255,255,0.1);
        padding: 8px 16px;
        border-radius: 40px;
        font-size: 13px;
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
        
        .cms-corp-view-header {
            padding: 30px 20px;
        }
        
        .cms-corp-view-content {
            padding: 20px;
        }
        
        .cms-corp-meta-info {
            flex-direction: column;
            gap: 10px;
        }
    }
    
    @media print {
        .cms-corp-view-nav,
        .cms-corp-print-btn,
        .cms-corp-email-btn {
            display: none;
        }
        
        .cms-corp-view-container {
            box-shadow: none;
            border: 1px solid #ddd;
        }
    }
    </style>
    
    <div class="cms-corp-view-container <?php echo esc_attr($atts['class']); ?>">
        
        <div class="cms-corp-view-header">
            <div class="cms-corp-view-avatar">
                <?php echo strtoupper(substr($corp->company_name, 0, 1)); ?>
            </div>
            
            <div>
                <h1 class="cms-corp-view-company"><?php echo esc_html($corp->company_name); ?></h1>
                <div class="cms-corp-view-username">
                    <span>@<?php echo esc_html($corp->username); ?></span>
                    <span style="opacity: 0.5;">•</span>
                    <span><?php echo esc_html($corp->email); ?></span>
                </div>
                
                <div class="cms-corp-meta-info">
                    <span class="cms-corp-view-badge cms-corp-badge-<?php echo esc_attr($corp->status); ?>">
                        <?php echo esc_html(ucfirst($corp->status)); ?>
                    </span>
                    
                    <span class="cms-corp-meta-item">
                        <span>🆔</span> ID: #<?php echo esc_html($corp->id); ?>
                    </span>
                    
                    <?php if(!empty($corp->phone_no)): ?>
                    <span class="cms-corp-meta-item">
                        <span>📞</span> <?php echo esc_html($corp->phone_no); ?>
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
                <a href="<?php echo esc_url(home_url('edit-corp-account/?corp_id=' . $corp->id)); ?>" class="cms-corp-nav-btn">
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
                        <span class="cms-corp-info-value"><?php echo esc_html($corp->company_name); ?></span>
                    </div>
                    
                    <div class="cms-corp-info-row">
                        <span class="cms-corp-info-label">Username</span>
                        <span class="cms-corp-info-value">@<?php echo esc_html($corp->username); ?></span>
                    </div>
                    
                    <div class="cms-corp-info-row">
                        <span class="cms-corp-info-label">Account ID</span>
                        <span class="cms-corp-info-value">#<?php echo esc_html($corp->id); ?></span>
                    </div>
                    
                    <div class="cms-corp-info-row">
                        <span class="cms-corp-info-label">Status</span>
                        <span class="cms-corp-info-value highlight"><?php echo esc_html(ucfirst($corp->status)); ?></span>
                    </div>
                </div>
                
                <div class="cms-corp-info-card">
                    <h3 class="cms-corp-card-title">👤 Contact Person</h3>
                    
                    <div class="cms-corp-info-row">
                        <span class="cms-corp-info-label">Name</span>
                        <span class="cms-corp-info-value"><?php echo esc_html($corp->name); ?></span>
                    </div>
                    
                    <div class="cms-corp-info-row">
                        <span class="cms-corp-info-label">Email</span>
                        <span class="cms-corp-info-value">
                            <a href="mailto:<?php echo esc_attr($corp->email); ?>" style="color: var(--corp-primary); text-decoration: none;">
                                <?php echo esc_html($corp->email); ?>
                            </a>
                        </span>
                    </div>
                    
                    <div class="cms-corp-info-row">
                        <span class="cms-corp-info-label">Phone</span>
                        <span class="cms-corp-info-value">
                            <a href="tel:<?php echo esc_attr($corp->phone_no); ?>" style="color: var(--corp-primary); text-decoration: none;">
                                <?php echo esc_html($corp->phone_no); ?>
                            </a>
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Address & Website -->
            <div class="cms-corp-info-grid">
                <div class="cms-corp-info-card">
                    <h3 class="cms-corp-card-title">📍 Business Address</h3>
                    
                    <div class="cms-corp-address-box">
                        <?php echo nl2br(esc_html($corp->address)); ?>
                    </div>
                </div>
                
                <div class="cms-corp-info-card">
                    <h3 class="cms-corp-card-title">🌐 Website</h3>
                    
                    <?php if(!empty($corp->website) && $corp->website !== 'https://'): ?>
                    <div style="text-align: center; padding: 20px;">
                        <a href="<?php echo esc_url($corp->website); ?>" target="_blank" class="cms-corp-website-link">
                            <span style="font-size: 48px; display: block; margin-bottom: 10px;">🌐</span>
                            <?php 
                            $display_url = preg_replace('#^https?://#', '', $corp->website);
                            echo esc_html($display_url); 
                            ?>
                        </a>
                        <div style="margin-top: 10px; font-size: 12px; color: #718096;">
                            Click to visit website
                        </div>
                    </div>
                    <?php else: ?>
                    <div style="text-align: center; padding: 30px; color: #718096;">
                        <span style="font-size: 48px; display: block; margin-bottom: 10px; opacity: 0.5;">🌐</span>
                        No website provided
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Account Timeline -->
            <div class="cms-corp-timeline">
                <h3 style="font-size: 18px; color: var(--corp-primary-dark); margin: 0 0 20px 0; display: flex; align-items: center; gap: 10px;">
                    <span>📅</span> Account Timeline
                </h3>
                
                <div class="cms-corp-timeline-item">
                    <div class="cms-corp-timeline-icon">🏢</div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">Account Created</div>
                        <div style="font-size: 13px; color: #718096;">
                            <?php echo date('F j, Y \a\t g:i a', strtotime($corp->created_at)); ?>
                        </div>
                    </div>
                </div>
                
                <?php if(!empty($corp->updated_at) && $corp->updated_at != '0000-00-00 00:00:00'): ?>
                <div class="cms-corp-timeline-item">
                    <div class="cms-corp-timeline-icon">🔄</div>
                    <div style="flex: 1;">
                        <div style="font-weight: 600; color: #2c3e50; margin-bottom: 5px;">Last Updated</div>
                        <div style="font-size: 13px; color: #718096;">
                            <?php echo date('F j, Y \a\t g:i a', strtotime($corp->updated_at)); ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Action Buttons -->
            <div style="margin-top: 30px; display: flex; gap: 15px; justify-content: flex-end; flex-wrap: wrap;">
                <button onclick="window.print()" class="cms-corp-print-btn">
                    🖨️ Print Profile
                </button>
                <a href="mailto:<?php echo esc_attr($corp->email); ?>?subject=Regarding%20Corporate%20Account&body=Dear%20<?php echo urlencode($corp->name); ?>%2C" class="cms-corp-email-btn">
                    📧 Send Email
                </a>
            </div>
            
            <!-- Delete Option (if user has permission) -->
            <?php if (current_user_can('manage_options')): ?>
            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px dashed var(--corp-border); text-align: right;">
                <button onclick="cmsConfirmDeleteCorp(<?php echo esc_js($corp->id); ?>, '<?php echo esc_js($corp->company_name); ?>')" style="background: none; border: 1px solid var(--corp-danger); color: var(--corp-danger); padding: 8px 16px; border-radius: 40px; font-size: 13px; cursor: pointer;">
                    🗑️ Delete Account
                </button>
            </div>
            <?php endif; ?>
            
        </div>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="cms-corp-delete-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center; backdrop-filter: blur(5px);">
        <div style="background:white; padding:30px; border-radius:20px; max-width:500px; width:90%; border-top:5px solid var(--corp-danger);">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:15px; border-bottom:2px solid #f0f0f0;">
                <h3 style="margin:0; color: var(--corp-danger); display: flex; align-items: center; gap: 10px;">
                    <span>🗑️</span> Confirm Delete
                </h3>
                <button style="background:none; border:none; font-size:24px; cursor:pointer; color:#718096;" onclick="closeDeleteModal()">×</button>
            </div>
            <div style="padding:20px 0;">
                <p style="font-size:16px; margin-bottom:15px;">Are you sure you want to delete <strong id="delete-company-name"></strong>?</p>
                <p style="color:#718096; font-size:14px; background:#fff3cd; padding:15px; border-radius:8px;">
                    <strong>Warning:</strong> This action cannot be undone. All associated data will be permanently removed from the database.
                </p>
            </div>
            <div style="display:flex; justify-content:flex-end; gap:12px;">
                <button style="background:#e2e8f0; color:#4a5568; padding:12px 24px; border:none; border-radius:8px; cursor:pointer; font-weight:600;" onclick="closeDeleteModal()">Cancel</button>
                <button id="cms-corp-confirm-delete-btn" style="background:var(--corp-danger); color:white; padding:12px 24px; border:none; border-radius:8px; cursor:pointer; font-weight:600;">Delete Account</button>
            </div>
        </div>
    </div>
    
    <script>
    var currentDeleteId = null;
    
    function cmsConfirmDeleteCorp(corpId, companyName) {
        currentDeleteId = corpId;
        document.getElementById('delete-company-name').textContent = companyName;
        document.getElementById('cms-corp-delete-modal').style.display = 'flex';
    }
    
    function closeDeleteModal() {
        document.getElementById('cms-corp-delete-modal').style.display = 'none';
        currentDeleteId = null;
    }
    
    function cmsDeleteCorp() {
        if (!currentDeleteId) return;
        
        var deleteBtn = document.getElementById('cms-corp-confirm-delete-btn');
        deleteBtn.disabled = true;
        deleteBtn.textContent = 'Deleting...';
        
        // AJAX delete request
        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'cms_delete_corporate_account',
                corp_id: currentDeleteId,
                nonce: '<?php echo wp_create_nonce('cms_delete_corp_nonce'); ?>'
            },
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    alert('Corporate account deleted successfully!');
                    // Redirect to list page
                    window.location.href = '<?php echo esc_url(home_url('corp-accounts')); ?>';
                } else {
                    alert('Error: ' + response.data.message);
                }
                closeDeleteModal();
                deleteBtn.disabled = false;
                deleteBtn.textContent = 'Delete Account';
            },
            error: function(xhr, status, error) {
                console.error('Delete error:', error);
                alert('An error occurred while deleting. Please try again.');
                closeDeleteModal();
                deleteBtn.disabled = false;
                deleteBtn.textContent = 'Delete Account';
            }
        });
    }
    
    // Attach delete function to confirm button
    document.getElementById('cms-corp-confirm-delete-btn').addEventListener('click', cmsDeleteCorp);
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        var modal = document.getElementById('cms-corp-delete-modal');
        if (event.target === modal) {
            closeDeleteModal();
        }
    };
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_view_corp_acc', 'cms_view_corp_acc_shortcode');
add_shortcode(CMS_CORP_ACC_VIEW_SHORTCODE, 'cms_view_corp_acc_shortcode');
