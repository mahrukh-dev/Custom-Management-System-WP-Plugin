<?php
/**
 * CMS List Corporate Accounts Shortcode
 * Display all corporate accounts from database in a table with actions
 * 
 * Usage: [cms_list_corp_acc]
 * Usage: [cms_list_corp_acc items_per_page="10" show_search="yes" show_filters="yes"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define shortcode slug
if (!defined('CMS_CORP_ACC_LIST_SHORTCODE')) {
    define('CMS_CORP_ACC_LIST_SHORTCODE', 'cms_corp_acc_list');
}

/**
 * List Corporate Accounts Shortcode
 */
function cms_list_corp_acc_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'items_per_page' => 10,
            'show_search' => 'yes',
            'show_filters' => 'yes',
            'actions' => 'view,update,delete',
            'no_data_message' => 'No corporate accounts found.',
            'table_class' => '',
            'edit_page' => 'edit-corp-account',
            'view_page' => 'view-corp-account',
            'show_status' => 'yes',
            'show_created_date' => 'no',
            'show_updated_date' => 'no'
        ),
        $atts,
        'cms_list_corp_acc'
    );
    
    // Get corporate accounts from database
    $corp_accounts = cms_get_all_corporate_accounts_from_db();
    
    // Get current page for pagination
    $current_page = isset($_GET['corp_page']) ? max(1, intval($_GET['corp_page'])) : 1;
    $items_per_page = intval($atts['items_per_page']);
    $total_items = count($corp_accounts);
    $total_pages = ceil($total_items / $items_per_page);
    
    // Slice array for pagination
    $offset = ($current_page - 1) * $items_per_page;
    $paged_accounts = array_slice($corp_accounts, $offset, $items_per_page);
    
    ob_start();
    ?>
    
    <style>
    /* Corporate Account List Styles - Purple/Blue Theme */
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
    
    .cms-corp-list-container {
        max-width: 1400px;
        margin: 30px auto;
        background: #ffffff;
        border-radius: 20px;
        box-shadow: 0 10px 40px rgba(108,92,231,0.08);
        padding: 30px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        border-top: 5px solid var(--corp-primary);
    }
    
    .cms-corp-list-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 30px;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .cms-corp-list-title {
        margin: 0;
        font-size: 28px;
        font-weight: 700;
        color: var(--corp-primary-dark);
        display: flex;
        align-items: center;
        gap: 12px;
        letter-spacing: -0.5px;
    }
    
    .cms-corp-list-title:before {
        content: '🏢';
        font-size: 32px;
    }
    
    .cms-corp-stats {
        display: flex;
        gap: 15px;
        align-items: center;
    }
    
    .cms-corp-stat-badge {
        background: var(--corp-bg-light);
        padding: 8px 16px;
        border-radius: 40px;
        color: var(--corp-primary-dark);
        font-size: 14px;
        font-weight: 500;
        border: 1px solid var(--corp-border);
    }
    
    .cms-corp-search-box {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .cms-corp-search-input {
        padding: 12px 20px;
        border: 2px solid var(--corp-border);
        border-radius: 40px;
        width: 300px;
        font-size: 14px;
        transition: all 0.25s ease;
    }
    
    .cms-corp-search-input:focus {
        outline: none;
        border-color: var(--corp-primary);
        box-shadow: 0 0 0 4px rgba(108,92,231,0.05);
    }
    
    .cms-corp-search-button {
        padding: 12px 28px;
        background: linear-gradient(145deg, var(--corp-primary), var(--corp-primary-dark));
        color: white;
        border: none;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s ease;
    }
    
    .cms-corp-search-button:hover {
        background: linear-gradient(145deg, var(--corp-primary-dark), #4338b0);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(108,92,231,0.2);
    }
    
    .cms-corp-filters {
        background: var(--corp-bg-light);
        padding: 20px;
        border-radius: 16px;
        margin-bottom: 30px;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        border: 1px solid var(--corp-border);
    }
    
    .cms-corp-filter-select {
        padding: 10px 18px;
        border: 1px solid var(--corp-border);
        border-radius: 8px;
        background: white;
        min-width: 150px;
        font-size: 14px;
        color: #2c3e50;
    }
    
    .cms-corp-filter-select:focus {
        outline: none;
        border-color: var(--corp-primary);
    }
    
    .cms-corp-table-responsive {
        overflow-x: auto;
        margin-bottom: 30px;
        border-radius: 12px;
        border: 1px solid var(--corp-border);
    }
    
    .cms-corp-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
        background: white;
    }
    
    .cms-corp-table th {
        background: var(--corp-bg-light);
        color: var(--corp-primary-dark);
        font-weight: 600;
        padding: 16px 12px;
        text-align: left;
        border-bottom: 2px solid var(--corp-border);
        white-space: nowrap;
    }
    
    .cms-corp-table td {
        padding: 16px 12px;
        border-bottom: 1px solid var(--corp-border);
        color: #2c3e50;
        vertical-align: middle;
    }
    
    .cms-corp-table tr:hover {
        background: var(--corp-bg-light);
    }
    
    .cms-corp-table tr:last-child td {
        border-bottom: none;
    }
    
    .cms-corp-avatar {
        width: 45px;
        height: 45px;
        background: linear-gradient(145deg, var(--corp-primary), var(--corp-primary-dark));
        color: white;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 20px;
        flex-shrink: 0;
    }
    
    .cms-corp-info {
        display: flex;
        align-items: center;
        gap: 15px;
    }
    
    .cms-corp-company {
        font-weight: 600;
        color: var(--corp-primary-dark);
        margin-bottom: 4px;
        font-size: 15px;
    }
    
    .cms-corp-contact {
        font-size: 12px;
        color: #718096;
    }
    
    .cms-corp-username {
        font-size: 11px;
        color: var(--corp-primary);
        background: var(--corp-bg-light);
        padding: 2px 8px;
        border-radius: 12px;
        display: inline-block;
        margin-top: 3px;
    }
    
    .cms-corp-email-cell {
        max-width: 200px;
    }
    
    .cms-corp-email {
        color: var(--corp-primary);
        text-decoration: none;
        font-weight: 500;
        word-break: break-all;
    }
    
    .cms-corp-email:hover {
        text-decoration: underline;
    }
    
    .cms-corp-phone {
        font-size: 12px;
        color: #718096;
        margin-top: 3px;
    }
    
    .cms-corp-address {
        max-width: 250px;
        white-space: normal;
        line-height: 1.4;
        font-size: 13px;
        color: #4a5568;
    }
    
    .cms-corp-website-link {
        color: var(--corp-primary);
        text-decoration: none;
        font-size: 13px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
        padding: 4px 10px;
        background: var(--corp-bg-light);
        border-radius: 20px;
        border: 1px solid var(--corp-border);
        transition: all 0.2s ease;
    }
    
    .cms-corp-website-link:hover {
        background: var(--corp-primary);
        color: white;
        border-color: var(--corp-primary);
    }
    
    .cms-corp-status {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 40px;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 0.3px;
    }
    
    .cms-corp-status.active {
        background: #e3f7ec;
        color: #0a5c36;
        border: 1px solid #b8e0c2;
    }
    
    .cms-corp-status.inactive {
        background: #ffe8e8;
        color: #b34141;
        border: 1px solid #ffc9c9;
    }
    
    .cms-corp-status.suspended {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
    
    .cms-corp-date {
        font-size: 12px;
        color: #718096;
        white-space: nowrap;
    }
    
    .cms-corp-date strong {
        color: #2c3e50;
        display: block;
        margin-bottom: 2px;
    }
    
    .cms-corp-action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .cms-corp-action-btn {
        padding: 8px 14px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 500;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all 0.2s ease;
        border: none;
        cursor: pointer;
    }
    
    .cms-corp-btn-view {
        background: var(--corp-bg-light);
        color: var(--corp-primary);
        border: 1px solid var(--corp-border);
    }
    
    .cms-corp-btn-view:hover {
        background: var(--corp-primary);
        color: white;
        border-color: var(--corp-primary);
    }
    
    .cms-corp-btn-edit {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
    
    .cms-corp-btn-edit:hover {
        background: #ffe8a1;
        color: #6d5300;
    }
    
    .cms-corp-btn-delete {
        background: #ffe8e8;
        color: #b34141;
        border: 1px solid #ffc9c9;
    }
    
    .cms-corp-btn-delete:hover {
        background: #ffc9c9;
        color: #8b2c2c;
    }
    
    .cms-corp-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 30px;
        flex-wrap: wrap;
    }
    
    .cms-corp-page-link {
        padding: 10px 18px;
        background: white;
        border: 1px solid var(--corp-border);
        border-radius: 8px;
        color: #4a5568;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .cms-corp-page-link:hover {
        background: var(--corp-bg-light);
        border-color: var(--corp-primary);
        color: var(--corp-primary);
    }
    
    .cms-corp-page-link.active {
        background: var(--corp-primary);
        color: white;
        border-color: var(--corp-primary);
    }
    
    .cms-corp-page-link.disabled {
        opacity: 0.5;
        pointer-events: none;
        cursor: not-allowed;
    }
    
    .cms-corp-no-data {
        text-align: center;
        padding: 80px 20px;
        color: #718096;
        font-size: 16px;
        background: var(--corp-bg-light);
        border-radius: 16px;
        border: 2px dashed var(--corp-border);
    }
    
    .cms-corp-no-data:before {
        content: '🏢';
        display: block;
        font-size: 48px;
        margin-bottom: 20px;
        opacity: 0.5;
    }
    
    .cms-corp-loading {
        text-align: center;
        padding: 50px;
        color: var(--corp-primary);
    }
    
    .cms-corp-spinner {
        display: inline-block;
        width: 40px;
        height: 40px;
        border: 3px solid var(--corp-border);
        border-top: 3px solid var(--corp-primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 15px;
    }
    
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }
    
    /* Delete Modal Styles */
    .cms-corp-modal {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        z-index: 9999;
        align-items: center;
        justify-content: center;
        backdrop-filter: blur(5px);
    }
    
    .cms-corp-modal-content {
        background: white;
        padding: 30px;
        border-radius: 20px;
        max-width: 500px;
        width: 90%;
        border-top: 5px solid var(--corp-danger);
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    
    .cms-corp-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #f0f0f0;
    }
    
    .cms-corp-modal-header h3 {
        margin: 0;
        color: var(--corp-danger);
        font-size: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cms-corp-modal-close {
        background: none;
        border: none;
        font-size: 28px;
        cursor: pointer;
        color: #718096;
    }
    
    .cms-corp-modal-body {
        padding: 20px 0;
    }
    
    .cms-corp-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        margin-top: 20px;
    }
    
    .cms-corp-modal-btn {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.2s ease;
    }
    
    .cms-corp-modal-btn-cancel {
        background: #e2e8f0;
        color: #4a5568;
    }
    
    .cms-corp-modal-btn-cancel:hover {
        background: #cbd5e0;
    }
    
    .cms-corp-modal-btn-delete {
        background: var(--corp-danger);
        color: white;
    }
    
    .cms-corp-modal-btn-delete:hover {
        background: #b91c1c;
    }
    
    .cms-corp-modal-btn-delete:disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    
    @media (max-width: 768px) {
        .cms-corp-list-container {
            padding: 20px;
            margin: 15px;
        }
        
        .cms-corp-list-header {
            flex-direction: column;
            align-items: stretch;
        }
        
        .cms-corp-search-box {
            width: 100%;
        }
        
        .cms-corp-search-input {
            width: 100%;
        }
        
        .cms-corp-filters {
            flex-direction: column;
        }
        
        .cms-corp-filter-select {
            width: 100%;
        }
        
        .cms-corp-table th,
        .cms-corp-table td {
            padding: 12px 8px;
            font-size: 13px;
        }
        
        .cms-corp-action-buttons {
            flex-direction: column;
        }
        
        .cms-corp-action-btn {
            width: 100%;
            justify-content: center;
        }
    }
    </style>
    
    <div class="cms-corp-list-container <?php echo esc_attr($atts['table_class']); ?>">
        
        <div class="cms-corp-list-header">
            <div>
                <h2 class="cms-corp-list-title">Corporate Account Management</h2>
                <div class="cms-corp-stats">
                    <span class="cms-corp-stat-badge">Total: <?php echo count($corp_accounts); ?></span>
                    <?php
                    $active_count = count(array_filter($corp_accounts, function($c) { return $c->status === 'active'; }));
                    if ($active_count > 0):
                    ?>
                    <span class="cms-corp-stat-badge">Active: <?php echo $active_count; ?></span>
                    <?php endif; ?>
                </div>
            </div>
            
            <?php if ($atts['show_search'] === 'yes'): ?>
            <div class="cms-corp-search-box">
                <input type="text" id="cms-corp-search" class="cms-corp-search-input" placeholder="Search by company, contact, email...">
                <button class="cms-corp-search-button" id="cms-corp-search-btn">Search</button>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($atts['show_filters'] === 'yes' && !empty($corp_accounts)): ?>
        <div class="cms-corp-filters">
            <select class="cms-corp-filter-select" id="filter-status">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
                <option value="suspended">Suspended</option>
            </select>
            
            <select class="cms-corp-filter-select" id="sort-by">
                <option value="">Sort By</option>
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="company_asc">Company A-Z</option>
                <option value="company_desc">Company Z-A</option>
            </select>
            
            <select class="cms-corp-filter-select" id="items-per-page" onchange="changeItemsPerPage(this.value)">
                <option value="10" <?php selected($items_per_page, 10); ?>>10 per page</option>
                <option value="25" <?php selected($items_per_page, 25); ?>>25 per page</option>
                <option value="50" <?php selected($items_per_page, 50); ?>>50 per page</option>
                <option value="100" <?php selected($items_per_page, 100); ?>>100 per page</option>
            </select>
        </div>
        <?php endif; ?>
        
        <?php if (empty($corp_accounts)): ?>
            <div class="cms-corp-no-data">
                <?php echo esc_html($atts['no_data_message']); ?>
            </div>
        <?php else: ?>
        
        <div class="cms-corp-table-responsive">
            <table class="cms-corp-table" id="cms-corp-table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Contact Person</th>
                        <th>Email & Phone</th>
                        <th>Address</th>
                        <th>Website</th>
                        <?php if ($atts['show_status'] === 'yes'): ?>
                        <th>Status</th>
                        <?php endif; ?>
                        <?php if ($atts['show_created_date'] === 'yes'): ?>
                        <th>Created</th>
                        <?php endif; ?>
                        <?php if ($atts['show_updated_date'] === 'yes'): ?>
                        <th>Updated</th>
                        <?php endif; ?>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($paged_accounts as $corp): ?>
                    <tr id="corp-row-<?php echo esc_attr($corp->id); ?>" data-status="<?php echo esc_attr($corp->status); ?>" data-company="<?php echo esc_attr($corp->company_name); ?>">
                        <td>
                            <div class="cms-corp-info">
                                <div class="cms-corp-avatar">
                                    <?php echo strtoupper(substr($corp->company_name, 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="cms-corp-company"><?php echo esc_html($corp->company_name); ?></div>
                                    <div class="cms-corp-username">@<?php echo esc_html($corp->username); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 500;"><?php echo esc_html($corp->name); ?></div>
                        </td>
                        <td class="cms-corp-email-cell">
                            <a href="mailto:<?php echo esc_attr($corp->email); ?>" class="cms-corp-email">
                                <?php echo esc_html($corp->email); ?>
                            </a>
                            <div class="cms-corp-phone">
                                <?php echo esc_html($corp->phone_no); ?>
                            </div>
                        </td>
                        <td>
                            <div class="cms-corp-address">
                                <?php echo esc_html($corp->address); ?>
                            </div>
                        </td>
                        <td>
                            <?php if(!empty($corp->website) && $corp->website !== 'https://'): ?>
                            <a href="<?php echo esc_url($corp->website); ?>" target="_blank" class="cms-corp-website-link">
                                <span>🌐</span> Visit
                            </a>
                            <?php else: ?>
                            <span style="color: #718096;">—</span>
                            <?php endif; ?>
                        </td>
                        
                        <?php if ($atts['show_status'] === 'yes'): ?>
                        <td>
                            <span class="cms-corp-status <?php echo esc_attr($corp->status); ?>">
                                <?php echo esc_html(ucfirst($corp->status)); ?>
                            </span>
                        </td>
                        <?php endif; ?>
                        
                        <?php if ($atts['show_created_date'] === 'yes'): ?>
                        <td class="cms-corp-date">
                            <?php echo date('M j, Y', strtotime($corp->created_at)); ?>
                        </td>
                        <?php endif; ?>
                        
                        <?php if ($atts['show_updated_date'] === 'yes' && !empty($corp->updated_at) && $corp->updated_at != '0000-00-00 00:00:00'): ?>
                        <td class="cms-corp-date">
                            <?php echo date('M j, Y', strtotime($corp->updated_at)); ?>
                        </td>
                        <?php endif; ?>
                        
                        <td>
                            <div class="cms-corp-action-buttons">
                                <?php if (strpos($atts['actions'], 'view') !== false): ?>
                                <a href="<?php echo esc_url(home_url($atts['view_page'] . '?corp_id=' . $corp->id)); ?>" class="cms-corp-action-btn cms-corp-btn-view" title="View Details">
                                    👁️ View
                                </a>
                                <?php endif; ?>
                                
                                <?php if (strpos($atts['actions'], 'update') !== false): ?>
                                <a href="<?php echo esc_url(home_url($atts['edit_page'] . '?corp_id=' . $corp->id)); ?>" class="cms-corp-action-btn cms-corp-btn-edit" title="Edit Account">
                                    ✏️ Edit
                                </a>
                                <?php endif; ?>
                                
                                <?php if (strpos($atts['actions'], 'delete') !== false): ?>
                                <button class="cms-corp-action-btn cms-corp-btn-delete" onclick="cmsConfirmDeleteCorp(<?php echo esc_js($corp->id); ?>, '<?php echo esc_js($corp->company_name); ?>')" title="Delete Account">
                                    🗑️ Delete
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        
        <?php if ($total_pages > 1): ?>
        <div class="cms-corp-pagination">
            <?php
            // Previous page link
            if ($current_page > 1):
            ?>
            <a href="<?php echo esc_url(add_query_arg('corp_page', $current_page - 1)); ?>" class="cms-corp-page-link">« Previous</a>
            <?php else: ?>
            <span class="cms-corp-page-link disabled">« Previous</span>
            <?php endif; ?>
            
            <?php
            // Page numbers
            $start_page = max(1, $current_page - 2);
            $end_page = min($total_pages, $current_page + 2);
            
            for ($i = $start_page; $i <= $end_page; $i++):
            ?>
            <a href="<?php echo esc_url(add_query_arg('corp_page', $i)); ?>" class="cms-corp-page-link <?php echo $i == $current_page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            
            <?php
            // Next page link
            if ($current_page < $total_pages):
            ?>
            <a href="<?php echo esc_url(add_query_arg('corp_page', $current_page + 1)); ?>" class="cms-corp-page-link">Next »</a>
            <?php else: ?>
            <span class="cms-corp-page-link disabled">Next »</span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
        
        <?php endif; ?>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="cms-corp-delete-modal" class="cms-corp-modal">
        <div class="cms-corp-modal-content">
            <div class="cms-corp-modal-header">
                <h3>
                    <span>🗑️</span> Delete Corporate Account
                </h3>
                <button class="cms-corp-modal-close" onclick="closeDeleteModal()">×</button>
            </div>
            <div class="cms-corp-modal-body">
                <p style="font-size: 16px; margin-bottom: 15px;">Are you sure you want to delete <strong id="delete-company-name"></strong>?</p>
                <p style="color: #718096; font-size: 14px; background: #fff3cd; padding: 12px; border-radius: 8px;">
                    <strong>Warning:</strong> This action cannot be undone. All associated data will be permanently removed from the database.
                </p>
            </div>
            <div class="cms-corp-modal-footer">
                <button class="cms-corp-modal-btn cms-corp-modal-btn-cancel" onclick="closeDeleteModal()">Cancel</button>
                <button id="cms-corp-confirm-delete-btn" class="cms-corp-modal-btn cms-corp-modal-btn-delete">Delete Account</button>
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
        
        // Show loading state on row
        var row = document.getElementById('corp-row-' + currentDeleteId);
        if (row) {
            row.style.opacity = '0.5';
        }
        
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
                    // Remove row with animation
                    if (row) {
                        row.style.transition = 'all 0.3s ease';
                        row.style.opacity = '0';
                        row.style.transform = 'translateX(20px)';
                        setTimeout(function() {
                            row.remove();
                            
                            // Check if table is empty
                            var tbody = document.querySelector('#cms-corp-table tbody');
                            if (tbody && tbody.children.length === 0) {
                                location.reload(); // Reload to show no data message
                            }
                        }, 300);
                    }
                    
                    // Show success message
                    alert('Corporate account deleted successfully!');
                } else {
                    alert('Error: ' + response.data.message);
                    if (row) {
                        row.style.opacity = '1';
                    }
                }
                
                closeDeleteModal();
                deleteBtn.disabled = false;
                deleteBtn.textContent = 'Delete Account';
            },
            error: function(xhr, status, error) {
                console.error('Delete error:', error);
                alert('An error occurred while deleting. Please try again.');
                if (row) {
                    row.style.opacity = '1';
                }
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
    
    // Search functionality
    jQuery(document).ready(function($) {
        // Real-time search
        $('#cms-corp-search').on('keyup', function() {
            var searchTerm = $(this).val().toLowerCase();
            filterTable();
        });
        
        $('#cms-corp-search-btn').on('click', function() {
            filterTable();
        });
        
        // Status filter
        $('#filter-status').on('change', function() {
            filterTable();
        });
        
        // Sort functionality
        $('#sort-by').on('change', function() {
            var sortBy = $(this).val();
            var rows = $('#cms-corp-table tbody tr').get();
            
            rows.sort(function(a, b) {
                var aVal, bVal;
                
                switch(sortBy) {
                    case 'newest':
                        // Assuming ID increments with time
                        aVal = parseInt($(a).attr('id').replace('corp-row-', ''));
                        bVal = parseInt($(b).attr('id').replace('corp-row-', ''));
                        return bVal - aVal;
                    
                    case 'oldest':
                        aVal = parseInt($(a).attr('id').replace('corp-row-', ''));
                        bVal = parseInt($(b).attr('id').replace('corp-row-', ''));
                        return aVal - bVal;
                    
                    case 'company_asc':
                        aVal = $(a).data('company') || '';
                        bVal = $(b).data('company') || '';
                        return aVal.localeCompare(bVal);
                    
                    case 'company_desc':
                        aVal = $(a).data('company') || '';
                        bVal = $(b).data('company') || '';
                        return bVal.localeCompare(aVal);
                    
                    default:
                        return 0;
                }
            });
            
            $.each(rows, function(index, row) {
                $('#cms-corp-table tbody').append(row);
            });
        });
        
        function filterTable() {
            var searchTerm = $('#cms-corp-search').val().toLowerCase();
            var statusFilter = $('#filter-status').val().toLowerCase();
            
            $('#cms-corp-table tbody tr').each(function() {
                var showRow = true;
                var row = $(this);
                
                // Search filter
                if (searchTerm) {
                    var rowText = row.text().toLowerCase();
                    if (!rowText.includes(searchTerm)) {
                        showRow = false;
                    }
                }
                
                // Status filter
                if (statusFilter && showRow) {
                    var rowStatus = row.data('status');
                    if (rowStatus !== statusFilter) {
                        showRow = false;
                    }
                }
                
                row.toggle(showRow);
            });
            
            // Show/hide no results message
            var visibleRows = $('#cms-corp-table tbody tr:visible').length;
            if (visibleRows === 0) {
                if ($('#no-results-message').length === 0) {
                    $('#cms-corp-table tbody').append('<tr id="no-results-message"><td colspan="8" style="text-align: center; padding: 40px; color: #718096;">No matching records found</td></tr>');
                }
            } else {
                $('#no-results-message').remove();
            }
        }
    });
    
    function changeItemsPerPage(value) {
        var url = new URL(window.location.href);
        url.searchParams.set('items_per_page', value);
        url.searchParams.set('corp_page', 1); // Reset to first page
        window.location.href = url.toString();
    }
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_list_corp_acc', 'cms_list_corp_acc_shortcode');
add_shortcode(CMS_CORP_ACC_LIST_SHORTCODE, 'cms_list_corp_acc_shortcode');

/**
 * Get all corporate accounts from database
 */
function cms_get_all_corporate_accounts_from_db() {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_corp_acc';
    
    $results = $wpdb->get_results(
        "SELECT * FROM $table ORDER BY created_at DESC"
    );
    
    return $results ? $results : array();
}

/**
 * AJAX handler for deleting corporate account
 */
function cms_ajax_delete_corporate_account() {
    // Verify nonce
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cms_delete_corp_nonce')) {
        wp_send_json_error(array('message' => 'Security check failed.'));
    }
    
    if (!isset($_POST['corp_id'])) {
        wp_send_json_error(array('message' => 'No account ID provided.'));
    }
    
    $corp_id = intval($_POST['corp_id']);
    
    global $wpdb;
    $table_corp_acc = $wpdb->prefix . 'cms_corp_acc';
    $table_users = $wpdb->prefix . 'cms_users';
    
    // Start transaction
    $wpdb->query('START TRANSACTION');
    
    try {
        // Get username first for logging
        $corp = $wpdb->get_row($wpdb->prepare(
            "SELECT username FROM $table_corp_acc WHERE id = %d",
            $corp_id
        ));
        
        if (!$corp) {
            throw new Exception('Corporate account not found.');
        }
        
        $username = $corp->username;
        
        // Delete from corp_acc table
        $result1 = $wpdb->delete(
            $table_corp_acc,
            array('id' => $corp_id),
            array('%d')
        );
        
        if ($result1 === false) {
            throw new Exception('Failed to delete from corporate accounts table.');
        }
        
        // Delete from users table
        $result2 = $wpdb->delete(
            $table_users,
            array('username' => $username),
            array('%s')
        );
        
        if ($result2 === false) {
            throw new Exception('Failed to delete from users table.');
        }
        
        // Commit transaction
        $wpdb->query('COMMIT');
        
        // Log the deletion
        error_log(sprintf(
            'CMS: Corporate account deleted - ID: %d, Username: %s',
            $corp_id,
            $username
        ));
        
        wp_send_json_success(array(
            'message' => 'Account deleted successfully.',
            'corp_id' => $corp_id
        ));
        
    } catch (Exception $e) {
        // Rollback transaction on error
        $wpdb->query('ROLLBACK');
        
        error_log('CMS Delete Error: ' . $e->getMessage());
        
        wp_send_json_error(array(
            'message' => $e->getMessage()
        ));
    }
}
add_action('wp_ajax_cms_delete_corporate_account', 'cms_ajax_delete_corporate_account');