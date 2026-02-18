<?php
/**
 * CMS List Admin Shortcode
 * Display all admins from database in a table with actions (View, Update, Delete)
 * 
 * Usage: [cms_list_admin]
 * Usage: [cms_list_admin items_per_page="10" show_search="yes"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define shortcode slug
if (!defined('CMS_ADMIN_LIST_SHORTCODE')) {
    define('CMS_ADMIN_LIST_SHORTCODE', 'cms_admin_list');
}


/**
 * List Admin Shortcode
 */
function cms_list_admin_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'items_per_page' => 10,
            'show_search' => 'yes',
            'show_filters' => 'yes',
            'actions' => 'view,update,delete',
            'no_data_message' => 'No admin records found.',
            'table_class' => '',
            'create_page' => 'add-admin2',
            'edit_page' => 'edit-admin2',
            'view_page' => 'view-admin2',
            'delete_action' => 'soft' // soft or hard
        ),
        $atts,
        'cms_list_admin'
    );
    
    // Get current page
    $current_page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($current_page - 1) * $atts['items_per_page'];
    
    // Get filters from URL
    $search = isset($_GET['search']) ? sanitize_text_field($_GET['search']) : '';
    $status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
    $position_filter = isset($_GET['position']) ? sanitize_text_field($_GET['position']) : '';
    $sort_by = isset($_GET['sort']) ? sanitize_text_field($_GET['sort']) : 'newest';
    
    // Build order by
    $orderby = 'a.created_at';
    $order = 'DESC';
    
    switch ($sort_by) {
        case 'oldest':
            $orderby = 'a.created_at';
            $order = 'ASC';
            break;
        case 'name':
            $orderby = 'a.name';
            $order = 'ASC';
            break;
        case 'newest':
        default:
            $orderby = 'a.created_at';
            $order = 'DESC';
            break;
    }
    
    // Get data from database
    $result = cms_get_all_admins(array(
        'status' => $status_filter,
        'position' => $position_filter,
        'search' => $search,
        'orderby' => $orderby,
        'order' => $order,
        'limit' => $atts['items_per_page'],
        'offset' => $offset
    ));
    
    $admin_data = $result['items'];
    $total_pages = $result['pages'];
    $total_items = $result['total'];
    
    // Handle delete action via AJAX - make sure this is unique
if (isset($_POST['cms_admin_ajax_delete']) && isset($_POST['admin_id'])) {
    $admin_id = intval($_POST['admin_id']);
    $hard_delete = ($atts['delete_action'] === 'hard');
    $deleted = cms_delete_admin_by_id($admin_id, $hard_delete);
    
    if ($deleted) {
        wp_send_json_success(array('message' => 'Admin deleted successfully'));
    } else {
        wp_send_json_error(array('message' => 'Failed to delete admin'));
    }
    exit;
}
    
    // Build URLs
    $create_url = home_url($atts['create_page']);
    
    ob_start();
    ?>
    
    <style>
    .cms-admin2-list-container {
        max-width: 1400px;
        margin: 30px auto;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        padding: 25px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    
    .cms-list2-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .cms-list2-title {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        color: #1a2b3c;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cms-list2-title:before {
        content: '👥';
        font-size: 28px;
    }
    
    .cms-create2-button {
        padding: 12px 24px;
        background: linear-gradient(145deg, #27ae60, #219a52);
        color: white;
        border: none;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        transition: all 0.3s ease;
    }
    
    .cms-create2-button:hover {
        background: linear-gradient(145deg, #219a52, #1e8449);
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(39,174,96,0.2);
    }
    
    .cms-search2-box {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .cms-search2-input {
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 40px;
        width: 280px;
        font-size: 14px;
        transition: all 0.25s ease;
    }
    
    .cms-search2-input:focus {
        outline: none;
        border-color: #27ae60;
        box-shadow: 0 0 0 3px rgba(39,174,96,0.05);
    }
    
    .cms-search2-button {
        padding: 12px 24px;
        background: #27ae60;
        color: white;
        border: none;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s ease;
    }
    
    .cms-search2-button:hover {
        background: #1e8449;
        transform: translateY(-1px);
    }
    
    .cms-filters2 {
        background: #f8fafc;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        border: 1px solid #e9edf2;
    }
    
    .cms-filter2-select {
        padding: 10px 16px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: white;
        min-width: 150px;
        font-size: 14px;
    }
    
    .cms-filter2-select:focus {
        outline: none;
        border-color: #27ae60;
    }
    
    .cms-reset2-filters {
        padding: 10px 20px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        color: #4a5568;
        text-decoration: none;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }
    
    .cms-reset2-filters:hover {
        background: #edf2f7;
    }
    
    .cms-table2-responsive {
        overflow-x: auto;
        margin-bottom: 25px;
        border-radius: 12px;
        border: 1px solid #e9edf2;
    }
    
    .cms-admin2-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    
    .cms-admin2-table th {
        background: #f8fafc;
        color: #2c3e50;
        font-weight: 600;
        padding: 16px 12px;
        text-align: left;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }
    
    .cms-admin2-table td {
        padding: 16px 12px;
        border-bottom: 1px solid #e9edf2;
        color: #4a5568;
        vertical-align: middle;
    }
    
    .cms-admin2-table tr:hover {
        background: #f8fafc;
    }
    
    .cms-admin2-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(145deg, #27ae60, #1e8449);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 16px;
        text-transform: uppercase;
    }
    
    .cms-admin2-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .cms-admin2-name {
        font-weight: 600;
        color: #1a2b3c;
        margin-bottom: 4px;
    }
    
    .cms-admin2-username {
        font-size: 12px;
        color: #718096;
    }
    
    .cms-position-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 40px;
        font-size: 12px;
        font-weight: 500;
        background: #e8f5e9;
        color: #1e8449;
    }
    
    .cms-badge2 {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 40px;
        font-size: 12px;
        font-weight: 500;
        text-transform: capitalize;
    }
    
    .cms-badge2.active {
        background: #e3f7ec;
        color: #0a5c36;
    }
    
    .cms-badge2.pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .cms-badge2.inactive {
        background: #ffe8e8;
        color: #b34141;
    }
    
    .cms-badge2.suspended {
        background: #f8d7da;
        color: #721c24;
    }
    
    .cms-action2-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .cms-action2-btn {
        padding: 8px 14px;
        border-radius: 6px;
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
    
    .cms-btn2-view {
        background: #ebf8ff;
        color: #007cba;
        border: 1px solid #bee3f8;
    }
    
    .cms-btn2-view:hover {
        background: #bee3f8;
        color: #005a87;
    }
    
    .cms-btn2-edit {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
    
    .cms-btn2-edit:hover {
        background: #ffe8a1;
        color: #6d5300;
    }
    
    .cms-btn2-delete {
        background: #ffe8e8;
        color: #b34141;
        border: 1px solid #ffc9c9;
    }
    
    .cms-btn2-delete:hover {
        background: #ffc9c9;
        color: #8b2c2c;
    }
    
    .cms-stats2-info {
        font-size: 12px;
        color: #718096;
        margin-top: 4px;
    }
    
    .cms-last2-login {
        font-size: 11px;
        color: #a0b3c2;
        margin-top: 2px;
    }
    
    .cms-pagination2 {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 30px;
        flex-wrap: wrap;
    }
    
    .cms-page2-link {
        padding: 10px 16px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        color: #4a5568;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    
    .cms-page2-link:hover {
        background: #f8fafc;
        border-color: #27ae60;
        color: #27ae60;
    }
    
    .cms-page2-link.active {
        background: #27ae60;
        color: white;
        border-color: #27ae60;
    }
    
    .cms-page2-link.disabled {
        opacity: 0.5;
        pointer-events: none;
    }
    
    .cms-summary2 {
        margin-top: 20px;
        padding: 15px;
        background: #f8fafc;
        border-radius: 8px;
        font-size: 14px;
        color: #4a5568;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    
    .cms-no2-data {
        text-align: center;
        padding: 60px 20px;
        color: #718096;
        font-size: 16px;
        background: #f8fafc;
        border-radius: 12px;
    }
    
    .cms-no2-data:before {
        content: '👥';
        display: block;
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.5;
    }
    
    .cms-modal {
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
    }
    
    .cms-modal-content {
        background: white;
        padding: 30px;
        border-radius: 16px;
        max-width: 500px;
        width: 90%;
        animation: modalSlideIn 0.3s ease;
    }
    
    @keyframes modalSlideIn {
        from {
            transform: translateY(-20px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
    
    .cms-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .cms-modal-header h3 {
        margin: 0;
        color: #e74c3c;
    }
    
    .cms-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #718096;
    }
    
    .cms-modal-body {
        padding: 20px 0;
    }
    
    .cms-modal-footer {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        padding-top: 20px;
        border-top: 1px solid #e2e8f0;
    }
    
    .cms-modal-btn {
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 500;
        transition: all 0.2s ease;
    }
    
    .cms-modal-btn.cancel {
        background: #e2e8f0;
        color: #4a5568;
    }
    
    .cms-modal-btn.cancel:hover {
        background: #cbd5e0;
    }
    
    .cms-modal-btn.delete {
        background: #e74c3c;
        color: white;
    }
    
    .cms-modal-btn.delete:hover {
        background: #c0392b;
    }
    
    .cms-modal-btn.delete:disabled {
        background: #95a5a6;
        cursor: not-allowed;
    }
    
    @media (max-width: 768px) {
        .cms-list2-header {
            flex-direction: column;
            align-items: stretch;
        }
        
        .cms-search2-box {
            width: 100%;
        }
        
        .cms-search2-input {
            width: 100%;
        }
        
        .cms-filters2 {
            flex-direction: column;
        }
        
        .cms-filter2-select {
            width: 100%;
        }
    }
    </style>
    
    <div class="cms-admin2-list-container <?php echo esc_attr($atts['table_class']); ?>">
        
        <div class="cms-list2-header">
            <h2 class="cms-list2-title">Admin Management</h2>
            
            <div style="display: flex; gap: 10px;">
                <?php if ($atts['show_search'] === 'yes'): ?>
                <form method="get" class="cms-search2-box">
                    <?php 
                    // Preserve other query parameters
                    foreach ($_GET as $key => $value) {
                        if ($key !== 'search' && $key !== 'paged') {
                            echo '<input type="hidden" name="' . esc_attr($key) . '" value="' . esc_attr($value) . '">';
                        }
                    }
                    ?>
                    <input type="text" name="search" class="cms-search2-input" 
                           placeholder="Search by name, email or username..." 
                           value="<?php echo esc_attr($search); ?>">
                    <button type="submit" class="cms-search2-button">Search</button>
                </form>
                <?php endif; ?>
                
                <a href="<?php echo esc_url($create_url); ?>" class="cms-create2-button">
                    ➕ Add New Admin
                </a>
            </div>
        </div>
        
        <?php if ($atts['show_filters'] === 'yes'): ?>
        <form method="get" class="cms-filters2">
            <?php 
            // Preserve search parameter
            if (!empty($search)) {
                echo '<input type="hidden" name="search" value="' . esc_attr($search) . '">';
            }
            ?>
            
            <select name="status" class="cms-filter2-select" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="active" <?php selected($status_filter, 'active'); ?>>Active</option>
                <option value="pending" <?php selected($status_filter, 'pending'); ?>>Pending</option>
                <option value="inactive" <?php selected($status_filter, 'inactive'); ?>>Inactive</option>
                <option value="suspended" <?php selected($status_filter, 'suspended'); ?>>Suspended</option>
            </select>
            
            <select name="position" class="cms-filter2-select" onchange="this.form.submit()">
                <option value="">All Positions</option>
                <option value="Senior Admin" <?php selected($position_filter, 'Senior Admin'); ?>>Senior Admin</option>
                <option value="Junior Admin" <?php selected($position_filter, 'Junior Admin'); ?>>Junior Admin</option>
                <option value="HR Admin" <?php selected($position_filter, 'HR Admin'); ?>>HR Admin</option>
                <option value="Finance Admin" <?php selected($position_filter, 'Finance Admin'); ?>>Finance Admin</option>
                <option value="Operations Admin" <?php selected($position_filter, 'Operations Admin'); ?>>Operations Admin</option>
                <option value="Support Admin" <?php selected($position_filter, 'Support Admin'); ?>>Support Admin</option>
                <option value="Technical Admin" <?php selected($position_filter, 'Technical Admin'); ?>>Technical Admin</option>
            </select>
            
            <select name="sort" class="cms-filter2-select" onchange="this.form.submit()">
                <option value="">Sort By</option>
                <option value="newest" <?php selected($sort_by, 'newest'); ?>>Newest First</option>
                <option value="oldest" <?php selected($sort_by, 'oldest'); ?>>Oldest First</option>
                <option value="name" <?php selected($sort_by, 'name'); ?>>Name A-Z</option>
            </select>
            
            <?php if (!empty($status_filter) || !empty($position_filter) || !empty($search) || $sort_by !== 'newest'): ?>
            <a href="<?php echo esc_url(remove_query_arg(array('status', 'position', 'search', 'sort', 'paged'))); ?>" class="cms-reset2-filters">
                ✕ Clear Filters
            </a>
            <?php endif; ?>
        </form>
        <?php endif; ?>
        
        <?php if (empty($admin_data)): ?>
            <div class="cms-no2-data">
                <?php echo esc_html($atts['no_data_message']); ?>
            </div>
        <?php else: ?>
        
        <div class="cms-table2-responsive">
            <table class="cms-admin2-table">
                <thead>
                    <tr>
                        <th>Admin</th>
                        <th>Position</th>
                        <th>Contact</th>
                        <th>Father's Name</th>
                        <th>Emergency</th>
                        <th>References</th>
                        <th>Status</th>
                        <th>Last Login</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admin_data as $admin): ?>
                    <tr id="admin-row-<?php echo esc_attr($admin['id']); ?>">
                        <td>
                            <div class="cms-admin2-info">
                                <div class="cms-admin2-avatar">
                                    <?php echo esc_html(substr($admin['name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="cms-admin2-name"><?php echo esc_html($admin['name']); ?></div>
                                    <div class="cms-admin2-username">@<?php echo esc_html($admin['username']); ?></div>
                                    <div class="cms-stats2-info"><?php echo esc_html($admin['email']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="cms-position-badge"><?php echo esc_html($admin['position']); ?></span>
                        </td>
                        <td>
                            <div style="font-weight: 500;"><?php echo esc_html($admin['contact_num']); ?></div>
                        </td>
                        <td><?php echo esc_html($admin['father_name']); ?></td>
                        <td><?php echo esc_html($admin['emergency_cno']); ?></td>
                        <td>
                            <div style="font-size: 12px;">
                                <strong>R1:</strong> <?php echo esc_html($admin['ref1_name']); ?><br>
                                <span style="color: #718096;"><?php echo esc_html($admin['ref1_cno']); ?></span>
                                <br><br>
                                <strong>R2:</strong> <?php echo esc_html($admin['ref2_name']); ?><br>
                                <span style="color: #718096;"><?php echo esc_html($admin['ref2_cno']); ?></span>
                            </div>
                        </td>
                        <td>
                            <span class="cms-badge2 <?php echo esc_attr($admin['status']); ?>">
                                <?php echo esc_html(ucfirst($admin['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <?php if (!empty($admin['last_login'])): ?>
                                <div><?php echo esc_html(date('Y-m-d', strtotime($admin['last_login']))); ?></div>
                                <div class="cms-last2-login"><?php echo esc_html(date('H:i', strtotime($admin['last_login']))); ?></div>
                            <?php else: ?>
                                <span style="color: #a0b3c2;">Never</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="cms-action2-buttons">
                                <?php if (strpos($atts['actions'], 'view') !== false): ?>
                                <a href="<?php echo esc_url(home_url($atts['view_page'] . '?admin_id=' . $admin['id'])); ?>" class="cms-action2-btn cms-btn2-view">
                                    👁️ View
                                </a>
                                <?php endif; ?>
                                
                                <?php if (strpos($atts['actions'], 'update') !== false): ?>
                                <a href="<?php echo esc_url(home_url($atts['edit_page'] . '?admin_id=' . $admin['id'])); ?>" class="cms-action2-btn cms-btn2-edit">
                                    ✏️ Update
                                </a>
                                <?php endif; ?>
                                
                                <?php if (strpos($atts['actions'], 'delete') !== false): ?>
                                <button class="cms-action2-btn cms-btn2-delete" onclick="cmsConfirmDelete(<?php echo esc_js($admin['id']); ?>, '<?php echo esc_js($admin['name']); ?>')">
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
        <div class="cms-pagination2">
            <?php
            // Build pagination links
            $base_url = remove_query_arg('paged');
            $base_url = add_query_arg($_GET, $base_url);
            
            if ($current_page > 1) {
                $prev_url = add_query_arg('paged', $current_page - 1, $base_url);
                echo '<a href="' . esc_url($prev_url) . '" class="cms-page2-link">« Previous</a>';
            } else {
                echo '<span class="cms-page2-link disabled">« Previous</span>';
            }
            
            for ($i = 1; $i <= $total_pages; $i++) {
                if ($i == $current_page) {
                    echo '<span class="cms-page2-link active">' . $i . '</span>';
                } else {
                    $page_url = add_query_arg('paged', $i, $base_url);
                    echo '<a href="' . esc_url($page_url) . '" class="cms-page2-link">' . $i . '</a>';
                }
            }
            
            if ($current_page < $total_pages) {
                $next_url = add_query_arg('paged', $current_page + 1, $base_url);
                echo '<a href="' . esc_url($next_url) . '" class="cms-page2-link">Next »</a>';
            } else {
                echo '<span class="cms-page2-link disabled">Next »</span>';
            }
            ?>
        </div>
        
        <div class="cms-summary2">
            <span>Showing <?php echo count($admin_data); ?> of <?php echo $total_items; ?> admins</span>
            <span>Page <?php echo $current_page; ?> of <?php echo $total_pages; ?></span>
        </div>
        <?php endif; ?>
        
        <?php endif; ?>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="cms-delete-modal" class="cms-modal">
        <div class="cms-modal-content">
            <div class="cms-modal-header">
                <h3>Confirm Delete</h3>
                <button class="cms-modal-close" onclick="closeDeleteModal()">×</button>
            </div>
            <div class="cms-modal-body">
                <p id="delete-message">Are you sure you want to delete this admin?</p>
                <p style="color: #e74c3c; font-size: 14px;">This action cannot be undone.</p>
            </div>
            <div class="cms-modal-footer">
                <button class="cms-modal-btn cancel" onclick="closeDeleteModal()">Cancel</button>
                <button id="cms-confirm-delete-btn" class="cms-modal-btn delete">Delete Admin</button>
            </div>
        </div>
    </div>
    
    <script>
    let currentDeleteId = null;
    
    function cmsConfirmDelete(adminId, adminName) {
        currentDeleteId = adminId;
        const modal = document.getElementById('cms-delete-modal');
        const message = document.getElementById('delete-message');
        message.textContent = `Are you sure you want to delete "${adminName}"?`;
        modal.style.display = 'flex';
    }
    
    function closeDeleteModal() {
        document.getElementById('cms-delete-modal').style.display = 'none';
        currentDeleteId = null;
    }
    
    document.getElementById('cms-confirm-delete-btn').addEventListener('click', function() {
        if (!currentDeleteId) return;
        
        const btn = this;
        btn.disabled = true;
        btn.textContent = 'Deleting...';
        
        // Send AJAX request to delete admin
        const formData = new FormData();
        formData.append('cms_admin_ajax_delete', '1');
        formData.append('admin_id', currentDeleteId);
        
        fetch(window.location.href, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove row from table
                const row = document.getElementById('admin-row-' + currentDeleteId);
                if (row) {
                    row.style.animation = 'fadeOut 0.3s ease';
                    setTimeout(() => {
                        row.remove();
                        
                        // Check if table is empty
                        const rows = document.querySelectorAll('.cms-admin2-table tbody tr');
                        if (rows.length === 0) {
                            location.reload();
                        }
                    }, 300);
                }
                
                // Show success message
                alert(data.data.message);
                closeDeleteModal();
            } else {
                alert('Error: ' + data.data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while deleting the admin.');
        })
        .finally(() => {
            btn.disabled = false;
            btn.textContent = 'Delete Admin';
        });
    });
    
    // Close modal when clicking outside
    window.onclick = function(event) {
        const modal = document.getElementById('cms-delete-modal');
        if (event.target === modal) {
            closeDeleteModal();
        }
    }
    
    // Add animation style
    const style = document.createElement('style');
    style.textContent = `
        @keyframes fadeOut {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(-20px); }
        }
    `;
    document.head.appendChild(style);
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_list_admin', 'cms_list_admin_shortcode');
add_shortcode(CMS_ADMIN_LIST_SHORTCODE, 'cms_list_admin_shortcode');