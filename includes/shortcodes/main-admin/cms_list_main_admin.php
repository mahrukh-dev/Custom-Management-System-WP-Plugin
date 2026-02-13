<?php
/**
 * CMS List Main Admin Shortcode
 * Display all main admins in a table with actions (View, Update, Delete)
 * 
 * Usage: [cms_list_main_admin]
 * Usage: [cms_list_main_admin items_per_page="10" show_search="yes"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}


// Define shortcode slug
if (!defined('CMS_MAIN_ADMIN_LIST_SHORTCODE')) {
    define('CMS_MAIN_ADMIN_LIST_SHORTCODE', 'cms_main_admin_list');
}


function cms_list_main_admin_shortcode($atts) {
    // Parse attributes
    $atts = shortcode_atts(
        array(
            'items_per_page' => 10,
            'show_search' => 'yes',
            'show_filters' => 'yes',
            'actions' => 'view,update,delete',
            'no_data_message' => 'No admin records found.',
            'table_class' => '',
            'create_page' => CMS_MAIN_ADMIN_CREATE_PAGE_SLUG ?? 'add-admin',
            'edit_page' => CMS_MAIN_ADMIN_EDIT_PAGE_SLUG ?? 'edit-admin',
            'view_page' => CMS_MAIN_ADMIN_VIEW_PAGE_SLUG ?? 'view-admin',
        ),
        $atts,
        'cms_list_main_admin'
    );
    

        // Build URLs using page slugs
    $create_url = home_url($atts['create_page']);
    $edit_base = home_url($atts['edit_page']);
    $view_base = home_url($atts['view_page']);

    ob_start();
    
    // Simulate admin data (replace with your database queries)
    $admin_data = get_cms_mock_admin_data();
    ?>
    
    <style>
    /* CMS Admin List Styles */
    .cms-admin-list-container {
        max-width: 1200px;
        margin: 30px auto;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        padding: 25px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    
    .cms-list-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .cms-list-title {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        color: #1a2b3c;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cms-list-title:before {
        content: '👥';
        font-size: 28px;
    }
    
    .cms-search-box {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .cms-search-input {
        padding: 12px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 40px;
        width: 280px;
        font-size: 14px;
        transition: all 0.25s ease;
    }
    
    .cms-search-input:focus {
        outline: none;
        border-color: #007cba;
        box-shadow: 0 0 0 3px rgba(0,124,186,0.05);
    }
    
    .cms-search-button {
        padding: 12px 24px;
        background: #007cba;
        color: white;
        border: none;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s ease;
    }
    
    .cms-search-button:hover {
        background: #005a87;
        transform: translateY(-1px);
    }
    
    .cms-filters {
        background: #f8fafc;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        border: 1px solid #e9edf2;
    }
    
    .cms-filter-select {
        padding: 10px 16px;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        background: white;
        min-width: 150px;
        font-size: 14px;
    }
    
    .cms-table-responsive {
        overflow-x: auto;
        margin-bottom: 25px;
    }
    
    .cms-admin-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    
    .cms-admin-table th {
        background: #f8fafc;
        color: #2c3e50;
        font-weight: 600;
        padding: 16px 12px;
        text-align: left;
        border-bottom: 2px solid #e2e8f0;
        white-space: nowrap;
    }
    
    .cms-admin-table td {
        padding: 16px 12px;
        border-bottom: 1px solid #e9edf2;
        color: #4a5568;
        vertical-align: middle;
    }
    
    .cms-admin-table tr:hover {
        background: #f8fafc;
    }
    
    .cms-admin-table tr:last-child td {
        border-bottom: none;
    }
    
    .cms-admin-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(145deg, #007cba, #005a87);
        color: white;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 16px;
    }
    
    .cms-admin-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .cms-admin-name {
        font-weight: 600;
        color: #1a2b3c;
        margin-bottom: 4px;
    }
    
    .cms-admin-username {
        font-size: 12px;
        color: #718096;
    }
    
    .cms-badge {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 40px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .cms-badge.active {
        background: #e3f7ec;
        color: #0a5c36;
    }
    
    .cms-badge.pending {
        background: #fff3cd;
        color: #856404;
    }
    
    .cms-badge.inactive {
        background: #ffe8e8;
        color: #b34141;
    }
    
    .cms-action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .cms-action-btn {
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
    
    .cms-btn-view {
        background: #ebf8ff;
        color: #007cba;
        border: 1px solid #bee3f8;
    }
    
    .cms-btn-view:hover {
        background: #bee3f8;
        color: #005a87;
    }
    
    .cms-btn-edit {
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
    }
    
    .cms-btn-edit:hover {
        background: #ffe8a1;
        color: #6d5300;
    }
    
    .cms-btn-delete {
        background: #ffe8e8;
        color: #b34141;
        border: 1px solid #ffc9c9;
    }
    
    .cms-btn-delete:hover {
        background: #ffc9c9;
        color: #8b2c2c;
    }
    
    .cms-pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 8px;
        margin-top: 30px;
        flex-wrap: wrap;
    }
    
    .cms-page-link {
        padding: 10px 16px;
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        color: #4a5568;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    
    .cms-page-link:hover {
        background: #f8fafc;
        border-color: #007cba;
        color: #007cba;
    }
    
    .cms-page-link.active {
        background: #007cba;
        color: white;
        border-color: #007cba;
    }
    
    .cms-no-data {
        text-align: center;
        padding: 60px 20px;
        color: #718096;
        font-size: 16px;
        background: #f8fafc;
        border-radius: 12px;
    }
    
    .cms-no-data:before {
        content: '📋';
        display: block;
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.5;
    }
    
    /* Modal Styles */
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
        max-height: 80vh;
        overflow-y: auto;
        position: relative;
    }
    
    .cms-modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
        padding-bottom: 15px;
        border-bottom: 2px solid #e2e8f0;
    }
    
    .cms-modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: #718096;
    }
    
    .cms-confirm-delete {
        background: #e74c3c;
        color: white;
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        font-weight: 600;
    }
    
    .cms-cancel-delete {
        background: #e2e8f0;
        color: #4a5568;
        padding: 12px 24px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        margin-left: 10px;
    }
    
    @media (max-width: 768px) {
        .cms-list-header {
            flex-direction: column;
            align-items: stretch;
        }
        
        .cms-search-box {
            width: 100%;
        }
        
        .cms-search-input {
            width: 100%;
        }
        
        .cms-admin-table th,
        .cms-admin-table td {
            padding: 12px 8px;
            font-size: 13px;
        }
        
        .cms-action-btn {
            padding: 6px 10px;
            font-size: 11px;
        }
    }
    </style>
    
    <div class="cms-admin-list-container <?php echo esc_attr($atts['table_class']); ?>">
        
        <div class="cms-list-header">
            <h2 class="cms-list-title">Main Admin Management</h2>
            
            <?php if ($atts['show_search'] === 'yes'): ?>
            <div class="cms-search-box">
                <input type="text" id="cms-admin-search" class="cms-search-input" placeholder="Search by name, email or username...">
                <button class="cms-search-button">Search</button>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($atts['show_filters'] === 'yes'): ?>
        <div class="cms-filters">
            <select class="cms-filter-select">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="pending">Pending</option>
                <option value="inactive">Inactive</option>
            </select>
            
            <select class="cms-filter-select">
                <option value="">Sort By</option>
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="name">Name A-Z</option>
            </select>
        </div>
        <?php endif; ?>
        
        <?php if (empty($admin_data)): ?>
            <div class="cms-no-data">
                <?php echo esc_html($atts['no_data_message']); ?>
            </div>
        <?php else: ?>
        
        <div class="cms-table-responsive">
            <table class="cms-admin-table">
                <thead>
                    <tr>
                        <th>Admin</th>
                        <th>Contact</th>
                        <th>Father's Name</th>
                        <th>Emergency</th>
                        <th>References</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admin_data as $admin): ?>
                    <tr id="admin-row-<?php echo esc_attr($admin['id']); ?>">
                        <td>
                            <div class="cms-admin-info">
                                <div class="cms-admin-avatar">
                                    <?php echo strtoupper(substr($admin['name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="cms-admin-name"><?php echo esc_html($admin['name']); ?></div>
                                    <div class="cms-admin-username">@<?php echo esc_html($admin['username']); ?></div>
                                    <div style="font-size: 11px; color: #718096;"><?php echo esc_html($admin['email']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 500;"><?php echo esc_html($admin['contact']); ?></div>
                        </td>
                        <td><?php echo esc_html($admin['father_name']); ?></td>
                        <td><?php echo esc_html($admin['emergency']); ?></td>
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
                            <span class="cms-badge <?php echo esc_attr($admin['status']); ?>">
                                <?php echo esc_html(ucfirst($admin['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <div class="cms-action-buttons">
                <?php if (strpos($atts['actions'], 'view') !== false): ?>
<a href="<?php echo esc_url(home_url('view-admin/' . $admin['id'])); ?>" class="cms-action-btn cms-btn-view">
    👁️ View
</a>
<?php endif; ?>

<?php if (strpos($atts['actions'], 'update') !== false): ?>
<a href="<?php echo esc_url(home_url('edit-admin/' . $admin['id'])); ?>" class="cms-action-btn cms-btn-edit">
    ✏️ Update
</a>
<?php endif; ?>
                                <?php if (strpos($atts['actions'], 'delete') !== false): ?>
                                <button class="cms-action-btn cms-btn-delete" onclick="cmsConfirmDelete(<?php echo esc_js($admin['id']); ?>)">
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
        
        <!-- Pagination -->
        <div class="cms-pagination">
            <a href="#" class="cms-page-link">« Previous</a>
            <a href="#" class="cms-page-link active">1</a>
            <a href="#" class="cms-page-link">2</a>
            <a href="#" class="cms-page-link">3</a>
            <a href="#" class="cms-page-link">4</a>
            <a href="#" class="cms-page-link">Next »</a>
        </div>
        
        <?php endif; ?>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="cms-delete-modal" class="cms-modal">
        <div class="cms-modal-content">
            <div class="cms-modal-header">
                <h3 style="margin: 0; color: #e74c3c;">Confirm Delete</h3>
                <button class="cms-modal-close" onclick="document.getElementById('cms-delete-modal').style.display='none'">×</button>
            </div>
            <div style="padding: 20px 0;">
                <p style="font-size: 16px; margin-bottom: 20px;">Are you sure you want to delete this admin?</p>
                <p style="color: #718096; font-size: 14px;">This action cannot be undone.</p>
            </div>
            <div style="display: flex; justify-content: flex-end;">
                <button class="cms-cancel-delete" onclick="document.getElementById('cms-delete-modal').style.display='none'">Cancel</button>
                <button id="cms-confirm-delete-btn" class="cms-confirm-delete">Delete Admin</button>
            </div>
        </div>
    </div>
    
    <!-- View Admin Modal -->
    <div id="cms-view-modal" class="cms-modal">
        <div class="cms-modal-content" style="max-width: 600px;">
            <div class="cms-modal-header">
                <h3 style="margin: 0;">Admin Details</h3>
                <button class="cms-modal-close" onclick="document.getElementById('cms-view-modal').style.display='none'">×</button>
            </div>
            <div id="cms-view-content" style="padding: 20px 0;">
                <!-- Dynamic content will be loaded here -->
            </div>
        </div>
    </div>
    
    <script>
    // Delete confirmation
    function cmsConfirmDelete(adminId) {
        var modal = document.getElementById('cms-delete-modal');
        var confirmBtn = document.getElementById('cms-confirm-delete-btn');
        
        confirmBtn.onclick = function() {
            cmsDeleteAdmin(adminId);
        };
        
        modal.style.display = 'flex';
    }
    
    // Delete admin
    function cmsDeleteAdmin(adminId) {
        // Simulate delete - Replace with actual AJAX call
        var row = document.getElementById('admin-row-' + adminId);
        if (row) {
            row.style.opacity = '0.5';
            setTimeout(function() {
                row.remove();
                document.getElementById('cms-delete-modal').style.display = 'none';
                alert('Admin deleted successfully!');
            }, 500);
        }
        
        // Actual AJAX implementation:
        /*
        jQuery.post(ajaxurl, {
            action: 'cms_delete_admin',
            admin_id: adminId,
            nonce: '<?php echo wp_create_nonce("cms_delete_admin"); ?>'
        }, function(response) {
            if (response.success) {
                location.reload();
            }
        });
        */
    }
    
    // View admin
    function cmsViewAdmin(adminId) {
        var modal = document.getElementById('cms-view-modal');
        var content = document.getElementById('cms-view-content');
        
        // Simulate fetching data - Replace with actual AJAX
        <?php foreach ($admin_data as $admin): ?>
        if (adminId == <?php echo $admin['id']; ?>) {
            content.innerHTML = `
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                    <div style="grid-column: span 2; text-align: center; margin-bottom: 20px;">
                        <div style="width: 80px; height: 80px; background: linear-gradient(145deg, #007cba, #005a87); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 32px; font-weight: 600; margin: 0 auto 15px;">
                            <?php echo strtoupper(substr($admin['name'], 0, 1)); ?>
                        </div>
                        <h2 style="margin: 0 0 5px 0; color: #1a2b3c;"><?php echo esc_js($admin['name']); ?></h2>
                        <p style="color: #718096; margin: 0;">@<?php echo esc_js($admin['username']); ?></p>
                    </div>
                    
                    <div style="background: #f8fafc; padding: 15px; border-radius: 10px;">
                        <strong style="color: #2c3e50; display: block; margin-bottom: 5px;">Email</strong>
                        <span><?php echo esc_js($admin['email']); ?></span>
                    </div>
                    
                    <div style="background: #f8fafc; padding: 15px; border-radius: 10px;">
                        <strong style="color: #2c3e50; display: block; margin-bottom: 5px;">Father's Name</strong>
                        <span><?php echo esc_js($admin['father_name']); ?></span>
                    </div>
                    
                    <div style="background: #f8fafc; padding: 15px; border-radius: 10px;">
                        <strong style="color: #2c3e50; display: block; margin-bottom: 5px;">Contact</strong>
                        <span><?php echo esc_js($admin['contact']); ?></span>
                    </div>
                    
                    <div style="background: #f8fafc; padding: 15px; border-radius: 10px;">
                        <strong style="color: #2c3e50; display: block; margin-bottom: 5px;">Emergency</strong>
                        <span><?php echo esc_js($admin['emergency']); ?></span>
                    </div>
                    
                    <div style="grid-column: span 2;">
                        <div style="background: #f8fafc; padding: 20px; border-radius: 10px;">
                            <h4 style="margin: 0 0 15px 0; color: #2c3e50;">References</h4>
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                                <div>
                                    <strong>Reference 1:</strong>
                                    <p style="margin: 5px 0;"><?php echo esc_js($admin['ref1_name']); ?></p>
                                    <p style="color: #718096; margin: 0;"><?php echo esc_js($admin['ref1_cno']); ?></p>
                                </div>
                                <div>
                                    <strong>Reference 2:</strong>
                                    <p style="margin: 5px 0;"><?php echo esc_js($admin['ref2_name']); ?></p>
                                    <p style="color: #718096; margin: 0;"><?php echo esc_js($admin['ref2_cno']); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div style="grid-column: span 2; display: flex; gap: 15px; margin-top: 20px;">
                        <span class="cms-badge <?php echo esc_js($admin['status']); ?>" style="font-size: 14px; padding: 8px 16px;">
                            Status: <?php echo esc_js(ucfirst($admin['status'])); ?>
                        </span>
                        <span style="color: #718096;">Registered: 2024-01-15</span>
                    </div>
                </div>
            `;
        }
        <?php endforeach; ?>
        
        modal.style.display = 'flex';
    }
    
    // Search functionality
    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.getElementById('cms-admin-search');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                var searchTerm = this.value.toLowerCase();
                var rows = document.querySelectorAll('.cms-admin-table tbody tr');
                
                rows.forEach(function(row) {
                    var text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }
    });
    </script>
    
    <?php
    return ob_get_clean();
}
// Register the shortcode - use the constant
add_shortcode(CMS_MAIN_ADMIN_LIST_SHORTCODE, 'cms_list_main_admin_shortcode');

// Mock data function - Replace with your actual database queries
function get_cms_mock_admin_data() {
    return array(
        array(
            'id' => 1,
            'username' => 'john_doe',
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'father_name' => 'Robert Doe',
            'contact' => '+1 234-567-8901',
            'emergency' => '+1 234-567-8902',
            'ref1_name' => 'Sarah Johnson',
            'ref1_cno' => '+1 234-567-8903',
            'ref2_name' => 'Michael Brown',
            'ref2_cno' => '+1 234-567-8904',
            'status' => 'active'
        ),
        array(
            'id' => 2,
            'username' => 'jane_smith',
            'name' => 'Jane Smith',
            'email' => 'jane.smith@example.com',
            'father_name' => 'William Smith',
            'contact' => '+44 20 1234 5678',
            'emergency' => '+44 20 1234 5679',
            'ref1_name' => 'Emma Wilson',
            'ref1_cno' => '+44 20 1234 5680',
            'ref2_name' => 'James Taylor',
            'ref2_cno' => '+44 20 1234 5681',
            'status' => 'pending'
        ),
        array(
            'id' => 3,
            'username' => 'ahmed_khan',
            'name' => 'Ahmed Khan',
            'email' => 'ahmed.khan@example.com',
            'father_name' => 'Mohammed Khan',
            'contact' => '+92 300 1234567',
            'emergency' => '+92 300 1234568',
            'ref1_name' => 'Fatima Ali',
            'ref1_cno' => '+92 300 1234569',
            'ref2_name' => 'Omar Hassan',
            'ref2_cno' => '+92 300 1234570',
            'status' => 'active'
        ),
        array(
            'id' => 4,
            'username' => 'priya_patel',
            'name' => 'Priya Patel',
            'email' => 'priya.patel@example.com',
            'father_name' => 'Rajesh Patel',
            'contact' => '+91 98765 43210',
            'emergency' => '+91 98765 43211',
            'ref1_name' => 'Anita Desai',
            'ref1_cno' => '+91 98765 43212',
            'ref2_name' => 'Vikram Singh',
            'ref2_cno' => '+91 98765 43213',
            'status' => 'inactive'
        )
    );
}
?>