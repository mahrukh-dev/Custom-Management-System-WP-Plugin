<?php
/**
 * CMS List Corporate Accounts Shortcode
 * Display all corporate accounts in a table with actions
 * 
 * Usage: [cms_list_corp_acc]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define shortcode slug
if (!defined('CMS_CORP_ACC_LIST_SHORTCODE')) {
    define('CMS_CORP_ACC_LIST_SHORTCODE', 'cms_corp_acc_list');
}

function cms_list_corp_acc_shortcode($atts) {
    $atts = shortcode_atts(
        array(
            'items_per_page' => 10,
            'show_search' => 'yes',
            'show_filters' => 'yes',
            'actions' => 'view,update,delete',
            'no_data_message' => 'No corporate accounts found.',
            'table_class' => '',
            'create_page' => 'add-corp-account',
            'edit_page' => 'edit-corp-account',
            'view_page' => 'view-corp-account',
        ),
        $atts,
        'cms_list_corp_acc'
    );
    
    ob_start();
    
    $corp_data = get_cms_mock_corp_data();
    ?>
    
    <style>
    /* Corporate Account List Styles - Purple/Blue Theme */
    .cms-corp-list-container {
        max-width: 1300px;
        margin: 30px auto;
        background: #ffffff;
        border-radius: 16px;
        box-shadow: 0 5px 20px rgba(108,92,231,0.05);
        padding: 25px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        border-top: 4px solid #6c5ce7;
    }
    
    .cms-corp-list-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 25px;
        flex-wrap: wrap;
        gap: 15px;
    }
    
    .cms-corp-list-title {
        margin: 0;
        font-size: 24px;
        font-weight: 700;
        color: #5649c0;
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .cms-corp-list-title:before {
        content: '🏢';
        font-size: 28px;
    }
    
    .cms-corp-search-box {
        display: flex;
        gap: 10px;
        align-items: center;
    }
    
    .cms-corp-search-input {
        padding: 12px 16px;
        border: 2px solid #d9d0ff;
        border-radius: 40px;
        width: 280px;
        font-size: 14px;
        transition: all 0.25s ease;
    }
    
    .cms-corp-search-input:focus {
        outline: none;
        border-color: #6c5ce7;
        box-shadow: 0 0 0 3px rgba(108,92,231,0.05);
    }
    
    .cms-corp-search-button {
        padding: 12px 24px;
        background: #6c5ce7;
        color: white;
        border: none;
        border-radius: 40px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s ease;
    }
    
    .cms-corp-search-button:hover {
        background: #5649c0;
        transform: translateY(-1px);
    }
    
    .cms-corp-filters {
        background: #f5f0ff;
        padding: 20px;
        border-radius: 12px;
        margin-bottom: 25px;
        display: flex;
        gap: 15px;
        flex-wrap: wrap;
        border: 1px solid #d9d0ff;
    }
    
    .cms-corp-filter-select {
        padding: 10px 16px;
        border: 1px solid #d9d0ff;
        border-radius: 8px;
        background: white;
        min-width: 150px;
        font-size: 14px;
    }
    
    .cms-corp-table-responsive {
        overflow-x: auto;
        margin-bottom: 25px;
    }
    
    .cms-corp-table {
        width: 100%;
        border-collapse: collapse;
        font-size: 14px;
    }
    
    .cms-corp-table th {
        background: #f5f0ff;
        color: #5649c0;
        font-weight: 600;
        padding: 16px 12px;
        text-align: left;
        border-bottom: 2px solid #d9d0ff;
        white-space: nowrap;
    }
    
    .cms-corp-table td {
        padding: 16px 12px;
        border-bottom: 1px solid #d9d0ff;
        color: #4a5568;
        vertical-align: middle;
    }
    
    .cms-corp-table tr:hover {
        background: #f5f0ff;
    }
    
    .cms-corp-avatar {
        width: 40px;
        height: 40px;
        background: linear-gradient(145deg, #6c5ce7, #5649c0);
        color: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 18px;
    }
    
    .cms-corp-info {
        display: flex;
        align-items: center;
        gap: 12px;
    }
    
    .cms-corp-company {
        font-weight: 600;
        color: #2c3e50;
        margin-bottom: 4px;
    }
    
    .cms-corp-contact {
        font-size: 12px;
        color: #718096;
    }
    
    .cms-corp-username {
        font-size: 11px;
        color: #6c5ce7;
    }
    
    .cms-corp-industry-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 40px;
        font-size: 12px;
        font-weight: 500;
        background: #f5f0ff;
        color: #5649c0;
    }
    
    .cms-corp-size-badge {
        display: inline-block;
        padding: 4px 8px;
        border-radius: 4px;
        font-size: 11px;
        font-weight: 500;
        background: #00cec9;
        color: white;
    }
    
    .cms-corp-status {
        display: inline-block;
        padding: 4px 10px;
        border-radius: 40px;
        font-size: 12px;
        font-weight: 500;
    }
    
    .cms-corp-status.active {
        background: #e3f7ec;
        color: #0a5c36;
    }
    
    .cms-corp-status.inactive {
        background: #ffe8e8;
        color: #b34141;
    }
    
    .cms-corp-status.suspended {
        background: #fff3cd;
        color: #856404;
    }
    
    .cms-corp-action-buttons {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .cms-corp-action-btn {
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
    
    .cms-corp-btn-view {
        background: #f5f0ff;
        color: #6c5ce7;
        border: 1px solid #d9d0ff;
    }
    
    .cms-corp-btn-view:hover {
        background: #d9d0ff;
        color: #5649c0;
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
    
    .cms-corp-btn-website {
        background: #e0f2fe;
        color: #0369a1;
        border: 1px solid #bae6fd;
        text-decoration: none;
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
        padding: 10px 16px;
        background: white;
        border: 1px solid #d9d0ff;
        border-radius: 8px;
        color: #4a5568;
        text-decoration: none;
        font-size: 14px;
        transition: all 0.2s ease;
    }
    
    .cms-corp-page-link:hover {
        background: #f5f0ff;
        border-color: #6c5ce7;
        color: #6c5ce7;
    }
    
    .cms-corp-page-link.active {
        background: #6c5ce7;
        color: white;
        border-color: #6c5ce7;
    }
    
    .cms-corp-no-data {
        text-align: center;
        padding: 60px 20px;
        color: #718096;
        font-size: 16px;
        background: #f5f0ff;
        border-radius: 12px;
    }
    
    .cms-corp-no-data:before {
        content: '🏢';
        display: block;
        font-size: 48px;
        margin-bottom: 15px;
        opacity: 0.5;
    }
    
    .cms-corp-website-link {
        color: #6c5ce7;
        text-decoration: none;
        font-size: 12px;
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    
    .cms-corp-website-link:hover {
        text-decoration: underline;
    }
    
    @media (max-width: 768px) {
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
        
        .cms-corp-table th,
        .cms-corp-table td {
            padding: 12px 8px;
        }
    }
    </style>
    
    <div class="cms-corp-list-container <?php echo esc_attr($atts['table_class']); ?>">
        
        <div class="cms-corp-list-header">
            <h2 class="cms-corp-list-title">Corporate Account Management</h2>
            
            <?php if ($atts['show_search'] === 'yes'): ?>
            <div class="cms-corp-search-box">
                <input type="text" id="cms-corp-search" class="cms-corp-search-input" placeholder="Search by company, contact, email...">
                <button class="cms-corp-search-button">Search</button>
            </div>
            <?php endif; ?>
        </div>
        
        <?php if ($atts['show_filters'] === 'yes'): ?>
        <div class="cms-corp-filters">
            <select class="cms-corp-filter-select" id="filter-industry">
                <option value="">All Industries</option>
                <option value="technology">Technology</option>
                <option value="finance">Finance</option>
                <option value="healthcare">Healthcare</option>
                <option value="education">Education</option>
                <option value="manufacturing">Manufacturing</option>
                <option value="retail">Retail</option>
            </select>
            
            <select class="cms-corp-filter-select" id="filter-size">
                <option value="">All Sizes</option>
                <option value="1-10">1-10</option>
                <option value="11-50">11-50</option>
                <option value="51-200">51-200</option>
                <option value="201-500">201-500</option>
                <option value="500+">500+</option>
            </select>
            
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
                <option value="company">Company A-Z</option>
            </select>
        </div>
        <?php endif; ?>
        
        <?php if (empty($corp_data)): ?>
            <div class="cms-corp-no-data">
                <?php echo esc_html($atts['no_data_message']); ?>
            </div>
        <?php else: ?>
        
        <div class="cms-corp-table-responsive">
            <table class="cms-corp-table">
                <thead>
                    <tr>
                        <th>Company</th>
                        <th>Contact Person</th>
                        <th>Email & Phone</th>
                        <th>Address</th>
                        <th>Website</th>
                        <th>Industry</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($corp_data as $corp): ?>
                    <tr id="corp-row-<?php echo esc_attr($corp['id']); ?>">
                        <td>
                            <div class="cms-corp-info">
                                <div class="cms-corp-avatar">
                                    <?php echo strtoupper(substr($corp['company_name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <div class="cms-corp-company"><?php echo esc_html($corp['company_name']); ?></div>
                                    <div class="cms-corp-username">@<?php echo esc_html($corp['username']); ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 500;"><?php echo esc_html($corp['contact_name']); ?></div>
                        </td>
                        <td>
                            <div><strong>Email:</strong> <?php echo esc_html($corp['email']); ?></div>
                            <div style="font-size: 12px; color: #718096; margin-top: 3px;">
                                <strong>Phone:</strong> <?php echo esc_html($corp['phone']); ?>
                            </div>
                        </td>
                        <td>
                            <div style="max-width: 200px; white-space: normal;">
                                <?php echo esc_html($corp['address']); ?>
                            </div>
                        </td>
                        <td>
                            <?php if($corp['website']): ?>
                            <a href="https://<?php echo esc_attr($corp['website']); ?>" target="_blank" class="cms-corp-website-link">
                                🌐 Visit
                            </a>
                            <?php else: ?>
                            <span style="color: #718096;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="cms-corp-industry-badge">
                                <?php echo esc_html(ucfirst($corp['industry'])); ?>
                            </span>
                            <?php if($corp['company_size']): ?>
                            <div style="font-size: 11px; color: #718096; margin-top: 3px;">
                                Size: <?php echo esc_html($corp['company_size']); ?>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="cms-corp-status <?php echo esc_attr($corp['status']); ?>">
                                <?php echo esc_html(ucfirst($corp['status'])); ?>
                            </span>
                        </td>
                        <td>
                            <div class="cms-corp-action-buttons">
                                <?php if (strpos($atts['actions'], 'view') !== false): ?>
                                <a href="<?php echo esc_url(home_url('view-corp-account/' . $corp['id'])); ?>" class="cms-corp-action-btn cms-corp-btn-view">
                                    👁️ View
                                </a>
                                <?php endif; ?>
                                
                                <?php if (strpos($atts['actions'], 'update') !== false): ?>
                                <a href="<?php echo esc_url(home_url('edit-corp-account/' . $corp['id'])); ?>" class="cms-corp-action-btn cms-corp-btn-edit">
                                    ✏️ Edit
                                </a>
                                <?php endif; ?>
                                
                                <?php if (strpos($atts['actions'], 'delete') !== false): ?>
                                <button class="cms-corp-action-btn cms-corp-btn-delete" onclick="cmsConfirmDeleteCorp(<?php echo esc_js($corp['id']); ?>)">
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
        
        <div class="cms-corp-pagination">
            <a href="#" class="cms-corp-page-link">« Previous</a>
            <a href="#" class="cms-corp-page-link active">1</a>
            <a href="#" class="cms-corp-page-link">2</a>
            <a href="#" class="cms-corp-page-link">3</a>
            <a href="#" class="cms-corp-page-link">4</a>
            <a href="#" class="cms-corp-page-link">Next »</a>
        </div>
        
        <?php endif; ?>
    </div>
    
    <!-- Delete Confirmation Modal -->
    <div id="cms-corp-delete-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
        <div style="background:white; padding:30px; border-radius:16px; max-width:500px; width:90%; border-top:4px solid #d63031;">
            <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; padding-bottom:15px; border-bottom:2px solid #f0f0f0;">
                <h3 style="margin:0; color:#d63031;">Confirm Delete</h3>
                <button style="background:none; border:none; font-size:24px; cursor:pointer; color:#718096;" onclick="document.getElementById('cms-corp-delete-modal').style.display='none'">×</button>
            </div>
            <div style="padding:20px 0;">
                <p style="font-size:16px; margin-bottom:20px;">Are you sure you want to delete this corporate account?</p>
                <p style="color:#718096; font-size:14px;">This action cannot be undone. All associated data will be permanently removed.</p>
            </div>
            <div style="display:flex; justify-content:flex-end;">
                <button style="background:#e2e8f0; color:#4a5568; padding:12px 24px; border:none; border-radius:8px; cursor:pointer; margin-right:10px;" onclick="document.getElementById('cms-corp-delete-modal').style.display='none'">Cancel</button>
                <button id="cms-corp-confirm-delete-btn" style="background:#d63031; color:white; padding:12px 24px; border:none; border-radius:8px; cursor:pointer; font-weight:600;">Delete Account</button>
            </div>
        </div>
    </div>
    
    <script>
    function cmsConfirmDeleteCorp(corpId) {
        var modal = document.getElementById('cms-corp-delete-modal');
        var confirmBtn = document.getElementById('cms-corp-confirm-delete-btn');
        
        confirmBtn.onclick = function() {
            cmsDeleteCorp(corpId);
        };
        
        modal.style.display = 'flex';
    }
    
    function cmsDeleteCorp(corpId) {
        var row = document.getElementById('corp-row-' + corpId);
        if (row) {
            row.style.opacity = '0.5';
            setTimeout(function() {
                row.remove();
                document.getElementById('cms-corp-delete-modal').style.display = 'none';
                alert('Corporate account deleted successfully!');
                
                if (document.querySelectorAll('.cms-corp-table tbody tr').length === 0) {
                    location.reload();
                }
            }, 500);
        }
    }
    
    document.addEventListener('DOMContentLoaded', function() {
        var searchInput = document.getElementById('cms-corp-search');
        if (searchInput) {
            searchInput.addEventListener('keyup', function() {
                var searchTerm = this.value.toLowerCase();
                var rows = document.querySelectorAll('.cms-corp-table tbody tr');
                
                rows.forEach(function(row) {
                    var text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        }
        
        // Industry filter
        var industryFilter = document.getElementById('filter-industry');
        var sizeFilter = document.getElementById('filter-size');
        var statusFilter = document.getElementById('filter-status');
        
        function applyCorpFilters() {
            var industryValue = industryFilter ? industryFilter.value.toLowerCase() : '';
            var sizeValue = sizeFilter ? sizeFilter.value.toLowerCase() : '';
            var statusValue = statusFilter ? statusFilter.value.toLowerCase() : '';
            var rows = document.querySelectorAll('.cms-corp-table tbody tr');
            
            rows.forEach(function(row) {
                var showRow = true;
                
                if (industryValue) {
                    var industryCell = row.querySelector('.cms-corp-industry-badge');
                    if (industryCell && industryCell.textContent.toLowerCase() !== industryValue) {
                        showRow = false;
                    }
                }
                
                if (sizeValue) {
                    var sizeText = row.querySelector('td:nth-child(6)')?.textContent.toLowerCase() || '';
                    if (!sizeText.includes(sizeValue)) {
                        showRow = false;
                    }
                }
                
                if (statusValue) {
                    var statusCell = row.querySelector('.cms-corp-status');
                    if (statusCell && !statusCell.classList.contains(statusValue)) {
                        showRow = false;
                    }
                }
                
                row.style.display = showRow ? '' : 'none';
            });
        }
        
        if (industryFilter) industryFilter.addEventListener('change', applyCorpFilters);
        if (sizeFilter) sizeFilter.addEventListener('change', applyCorpFilters);
        if (statusFilter) statusFilter.addEventListener('change', applyCorpFilters);
    });
    </script>
    
    <?php
    return ob_get_clean();
}

add_shortcode('cms_list_corp_acc', 'cms_list_corp_acc_shortcode');
add_shortcode(CMS_CORP_ACC_LIST_SHORTCODE, 'cms_list_corp_acc_shortcode');

/**
 * Mock Corporate Account Data
 */
function get_cms_mock_corp_data() {
    return array(
        array(
            'id' => 301,
            'username' => 'techcorp',
            'company_name' => 'TechCorp Solutions',
            'contact_name' => 'John Anderson',
            'email' => 'contact@techcorp.com',
            'phone' => '+1 (415) 555-0123',
            'address' => '123 Silicon Valley Blvd, San Francisco, CA 94105, USA',
            'website' => 'www.techcorp.com',
            'industry' => 'technology',
            'company_size' => '51-200',
            'status' => 'active'
        ),
        array(
            'id' => 302,
            'username' => 'globalfinance',
            'company_name' => 'Global Finance Ltd',
            'contact_name' => 'Sarah Williams',
            'email' => 'info@globalfinance.co.uk',
            'phone' => '+44 20 7123 4567',
            'address' => '45 London Wall, London EC2M 5TE, United Kingdom',
            'website' => 'www.globalfinance.co.uk',
            'industry' => 'finance',
            'company_size' => '201-500',
            'status' => 'active'
        ),
        array(
            'id' => 303,
            'username' => 'healthcare_plus',
            'company_name' => 'Healthcare Plus',
            'contact_name' => 'Dr. Michael Chen',
            'email' => 'admin@healthcareplus.com',
            'phone' => '+1 (212) 555-7890',
            'address' => '555 Medical Center Dr, New York, NY 10001, USA',
            'website' => 'www.healthcareplus.com',
            'industry' => 'healthcare',
            'company_size' => '501-1000',
            'status' => 'active'
        ),
        array(
            'id' => 304,
            'username' => 'eduworld',
            'company_name' => 'EduWorld International',
            'contact_name' => 'Prof. David Miller',
            'email' => 'contact@eduworld.edu',
            'phone' => '+1 (617) 555-4321',
            'address' => '100 Education Parkway, Boston, MA 02108, USA',
            'website' => 'www.eduworld.edu',
            'industry' => 'education',
            'company_size' => '201-500',
            'status' => 'active'
        ),
        array(
            'id' => 305,
            'username' => 'innovate_tech',
            'company_name' => 'InnovateTech Solutions',
            'contact_name' => 'Lisa Thompson',
            'email' => 'hello@innovatetech.io',
            'phone' => '+91 80 4123 4567',
            'address' => 'Embassy Tech Village, Outer Ring Road, Bangalore 560103, India',
            'website' => 'www.innovatetech.io',
            'industry' => 'technology',
            'company_size' => '11-50',
            'status' => 'inactive'
        ),
        array(
            'id' => 306,
            'username' => 'green_retail',
            'company_name' => 'Green Retail Chain',
            'contact_name' => 'Emma Green',
            'email' => 'info@greenretail.com.au',
            'phone' => '+61 2 9876 5432',
            'address' => '456 Oxford Street, Sydney NSW 2000, Australia',
            'website' => 'www.greenretail.com.au',
            'industry' => 'retail',
            'company_size' => '1000+',
            'status' => 'active'
        ),
        array(
            'id' => 307,
            'username' => 'prestige_auto',
            'company_name' => 'Prestige Auto Group',
            'contact_name' => 'Robert Brown',
            'email' => 'sales@prestigeauto.ae',
            'phone' => '+971 4 123 4567',
            'address' => 'Sheikh Zayed Road, Dubai, UAE',
            'website' => 'www.prestigeauto.ae',
            'industry' => 'retail',
            'company_size' => '51-200',
            'status' => 'suspended'
        )
    );
}
?>