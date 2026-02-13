<?php
/**
 * CMS List Admin Shortcode
 * Display all admins in a table with actions (View, Update, Delete)
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
        ),
        $atts,
        'cms_list_admin'
    );
    
    $create_url = home_url($atts['create_page']);
    $edit_base = home_url($atts['edit_page']);
    $view_base = home_url($atts['view_page']);

    ob_start();
    
    $admin_data = get_cms_mock_admin2_data();
    ?>
    
    <style>
    .cms-admin2-list-container {
        max-width: 1200px;
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
        content: '👤';
        font-size: 28px;
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
    
    .cms-table2-responsive {
        overflow-x: auto;
        margin-bottom: 25px;
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
    
    .cms-no2-data {
        text-align: center;
        padding: 60px 20px;
        color: #718096;
        font-size: 16px;
        background: #f8fafc;
        border-radius: 12px;
    }
    
    .cms-no2-data:before {
        content: '👤';
        display: block;
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.5;
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
    }
    </style>
    
    <div class="cms-admin2-list-container <?php echo esc_attr($atts['table_class']); ?>">
        
        <div class="cms-list2-header">
            <h2 class="cms-list2-title">Admin Management</h2>
            
            <?php if ($atts['show_search'] === 'yes'): ?>
            <div class="cms-search2-box">
                <input type="text" id="cms-admin2-search" class="cms-search2-input" placeholder="Search by name, email or username...">
                <button class="cms-search2-button">Search</button>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($atts['show_filters'] === 'yes'): ?>
        <div class="cms-filters2">
            <select class="cms-filter2-select" id="filter-status">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="pending">Pending</option>
                <option value="inactive">Inactive</option>
            </select>
            
            <select class="cms-filter2-select" id="filter-position">
                <option value="">All Positions</option>
                <option value="Senior Admin">Senior Admin</option>
                <option value="Junior Admin">Junior Admin</option>
                <option value="HR Admin">HR Admin</option>
                <option value="Finance Admin">Finance Admin</option>
                <option value="Operations Admin">Operations Admin</option>
                <option value="Support Admin">Support Admin</option>
                <option value="Technical Admin">Technical Admin</option>
            </select>
            
            <select class="cms-filter2-select" id="sort-by">
                <option value="">Sort By</option>
                <option value="newest">Newest First</option>
                <option value="oldest">Oldest First</option>
                <option value="name">Name A-Z</option>
            </select>
        </div>
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($admin_data as $admin): ?>
                    <tr id="admin2-row-<?php echo esc_attr($admin['id']); ?>">
                        <td>
                            <div class="cms-admin2-info">
                                <div class="cms-admin2-avatar">
                                    <?php echo strtoupper(substr($admin['name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="cms-admin2-name"><?php echo esc_html($admin['name']); ?></div>
                                    <div class="cms-admin2-username">@<?php echo esc_html($admin['username']); ?></div>
                                    <div style="font-size: 11px; color: #718096;"><?php echo esc_html($admin['email']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="cms-position-badge"><?php echo esc_html($admin['position']); ?></span>
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
                            <span class="cms-badge2 <?php echo esc_attr($admin['status']); ?>">
                                <?php echo esc_html(ucfirst($admin['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <div class="cms-action2-buttons">
                                <?php if (strpos($atts['actions'], 'view') !== false): ?>
                                <a href="<?php echo esc_url(home_url('view-admin2/' . $admin['id'])); ?>" class="cms-action2-btn cms-btn2-view">
                                    👁️ View
                                </a>
                                <?php endif; ?>
                                
                                <?php if (strpos($atts['actions'], 'update') !== false): ?>
                                <a href="<?php echo esc_url(home_url('edit-admin2/' . $admin['id'])); ?>" class="cms-action2-btn cms-btn2-edit">
                                    ✏️ Update
                                </a>
                                <?php endif; ?>
                                
                                <?php if (strpos($atts['actions'], 'delete') !== false): ?>
                                <button class="cms-action2-btn cms-btn2-delete" onclick="cmsConfirmDelete2(<?php echo esc_js($admin['id']); ?>)">
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
        
        <div class="cms-pagination2">
            <a href="#" class="cms-page2-link">« Previous</a>
            <a href="#" class="cms-page2-link active">1</a>
            <a href="#" class="cms-page2-link">2</a>
            <a href="#" class="cms-page2-link">3</a>
            <a href="#" class="cms-page2-link">Next »</a>
        </div>
        
        <?php endif; ?>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="cms-delete2-modal" class="cms-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:white; padding:30px; border-radius:16px; max-width:500px; width:90%;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:15px; border-bottom:2px solid #e2e8f0;">
                <h3 style="margin:0; color:#e74c3c;">Confirm Delete</h3>
                <button style="background:none; border:none; font-size:24px; cursor:pointer; color:#718096;" onclick="document.getElementById('cms-delete2-modal').style.display='none'">×</button>
            </div>
            <div style="padding:20px 0;">
                <p style="font-size:16px; margin-bottom:20px;">Are you sure you want to delete this admin?</p>
                <p style="color:#718096; font-size:14px;">This action cannot be undone.</p>
            </div>
            <div style="display:flex; justify-content:flex-end;">
                <button style="background:#e2e8f0; color:#4a5568; padding:12px 24px; border:none; border-radius:8px; cursor:pointer; margin-left:10px;" onclick="document.getElementById('cms-delete2-modal').style.display='none'">Cancel</button>
                <button id="cms-confirm-delete2-btn" style="background:#e74c3c; color:white; padding:12px 24px; border:none; border-radius:8px; cursor:pointer; font-weight:600;">Delete Admin</button>
            </div>
        </div>
    </div>
    
    <script>
    function cmsConfirmDelete2(adminId) {
        var modal = document.getElementById('cms-delete2-modal');
        var confirmBtn = document.getElementById('cms-confirm-delete2-btn');
        
        confirmBtn.onclick = function() {
            cmsDeleteAdmin2(adminId);
        };
        
        modal.style.display = 'flex';
    }
    
    function cmsDeleteAdmin2(adminId) {
        var row = document.getElementById('admin2-row-' + adminId);
        if (row) {
            row.style.opacity = '0.5';
            setTimeout(function() {
                row.remove();
                document.getElementById('cms-delete2-modal').style.display = 'none';
                alert('Admin deleted successfully!');
                
                if (document.querySelectorAll('.cms-admin2-table tbody tr').length === 0) {
                    location.reload();
                }
            }, 500);
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.getElementById('cms-admin2-search');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                var searchTerm = this.value.toLowerCase();
                var rows = document.querySelectorAll('.cms-admin2-table tbody tr');
                
                rows.forEach(function(row) {
                    var text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }
        
        var statusFilter = document.getElementById('filter-status');
        var positionFilter = document.getElementById('filter-position');
        
        function applyFilters() {
            var statusValue = statusFilter ? statusFilter.value.toLowerCase() : '';
            var positionValue = positionFilter ? positionFilter.value.toLowerCase() : '';
            var rows = document.querySelectorAll('.cms-admin2-table tbody tr');
            
            rows.forEach(function(row) {
                var showRow = true;
                
                if (statusValue) {
                    var statusCell = row.querySelector('.cms-badge2');
                    if (statusCell && !statusCell.classList.contains(statusValue)) {
                        showRow = false;
                    }
                }
                
                if (positionValue) {
                    var positionCell = row.querySelector('.cms-position-badge');
                    if (positionCell && positionCell.textContent.toLowerCase() !== positionValue) {
                        showRow = false;
                    }
                }
                
                row.style.display = showRow ? '' : 'none';
            });
        }
        
        if (statusFilter) statusFilter.addEventListener('change', applyFilters);
        if (positionFilter) positionFilter.addEventListener('change', applyFilters);
    });
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_list_admin', 'cms_list_admin_shortcode');
add_shortcode(CMS_ADMIN_LIST_SHORTCODE, 'cms_list_admin_shortcode');

function get_cms_mock_admin2_data() {
    return array(
        array(
            'id' => 101,
            'username' => 'sarah_ahmed',
            'name' => 'Sarah Ahmed',
            'email' => 'sarah.ahmed@example.com',
            'father_name' => 'Ahmed Khan',
            'position' => 'Senior Admin',
            'contact' => '+1 234-567-8901',
            'emergency' => '+1 234-567-8902',
            'ref1_name' => 'Fatima Hassan',
            'ref1_cno' => '+1 234-567-8903',
            'ref2_name' => 'Omar Farooq',
            'ref2_cno' => '+1 234-567-8904',
            'status' => 'active'
        ),
        array(
            'id' => 102,
            'username' => 'mike_wilson',
            'name' => 'Mike Wilson',
            'email' => 'mike.wilson@example.com',
            'father_name' => 'Robert Wilson',
            'position' => 'Technical Admin',
            'contact' => '+44 20 1234 5678',
            'emergency' => '+44 20 1234 5679',
            'ref1_name' => 'Lisa Cooper',
            'ref1_cno' => '+44 20 1234 5680',
            'ref2_name' => 'David Brown',
            'ref2_cno' => '+44 20 1234 5681',
            'status' => 'active'
        ),
        array(
            'id' => 103,
            'username' => 'priya_sharma',
            'name' => 'Priya Sharma',
            'email' => 'priya.sharma@example.com',
            'father_name' => 'Rajesh Sharma',
            'position' => 'HR Admin',
            'contact' => '+91 98765 43210',
            'emergency' => '+91 98765 43211',
            'ref1_name' => 'Neha Gupta',
            'ref1_cno' => '+91 98765 43212',
            'ref2_name' => 'Rahul Verma',
            'ref2_cno' => '+91 98765 43213',
            'status' => 'pending'
        ),
        array(
            'id' => 104,
            'username' => 'ahmed_malik',
            'name' => 'Ahmed Malik',
            'email' => 'ahmed.malik@example.com',
            'father_name' => 'Malik Ibrahim',
            'position' => 'Finance Admin',
            'contact' => '+92 300 7654321',
            'emergency' => '+92 300 7654322',
            'ref1_name' => 'Bilal Ahmed',
            'ref1_cno' => '+92 300 7654323',
            'ref2_name' => 'Sana Mirza',
            'ref2_cno' => '+92 300 7654324',
            'status' => 'inactive'
        ),
        array(
            'id' => 105,
            'username' => 'emma_watson',
            'name' => 'Emma Watson',
            'email' => 'emma.watson@example.com',
            'father_name' => 'Chris Watson',
            'position' => 'Operations Admin',
            'contact' => '+1 345-678-9012',
            'emergency' => '+1 345-678-9013',
            'ref1_name' => 'Sophie Turner',
            'ref1_cno' => '+1 345-678-9014',
            'ref2_name' => 'Daniel Craig',
            'ref2_cno' => '+1 345-678-9015',
            'status' => 'active'
        )
    );
}
?>