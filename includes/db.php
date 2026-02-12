<?php

// Define database version if not already defined
if (!defined('CMS_PLUGIN_DB_VER')) {
    define('CMS_PLUGIN_DB_VER', '1.0.0');
}

/**
 * Create initial reactions table
 * 
 * @since 1.0.0
 * @return void
 */
function cms_reactions_table() {
    global $wpdb;
    
    // Verify user capabilities - only admins should be able to create tables
    if (!current_user_can('activate_plugins')) {
        return;
    }
    
    $db_version = CMS_PLUGIN_DB_VER;
    
    // Sanitize table name
    $table_name = esc_sql($wpdb->prefix . 'reactions');
    $charset_collate = $wpdb->get_charset_collate();
    
    // Validate table name format
    if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
        error_log('CMS Plugin: Invalid table name format');
        return;
    }
    
    $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        post_id BIGINT UNSIGNED NOT NULL,
        user_id BIGINT UNSIGNED NOT NULL,
        reaction_type VARCHAR(20) NOT NULL,
        reaction_count INT UNSIGNED DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY unique_reaction (post_id, user_id, reaction_type),
        KEY post_index (post_id),
        KEY user_index (user_id)
    ) {$charset_collate};";
    
    // Include WordPress upgrade functions
    if (!function_exists('dbDelta')) {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    }
    
    // Execute table creation
    dbDelta($sql);
    
    // Store version with autoload disabled to save resources
    add_option('cms_db_version', $db_version, '', 'no');
    
    // Log successful creation
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('CMS Plugin: Reactions table created successfully');
    }
}

/**
 * Upgrade database schema
 * 
 * @since 1.0.0
 * @return void
 */
function cms_db_upgrade() {
    global $wpdb;
    
    // Verify user capabilities
    if (!current_user_can('activate_plugins')) {
        return;
    }
    
    $installed_ver = get_option('cms_db_version');
    $curr_ver = CMS_PLUGIN_DB_VER;
    
    // Validate version strings
    if (!is_string($installed_ver) || !is_string($curr_ver)) {
        error_log('CMS Plugin: Invalid version format');
        return;
    }
    
    // Only proceed if versions don't match
    if ($installed_ver !== $curr_ver) {
        
        // Sanitize table name for post_votes
        $table_name = esc_sql($wpdb->prefix . 'post_votes');
        
        // Validate table name
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $table_name)) {
            error_log('CMS Plugin: Invalid post_votes table name');
            return;
        }
        
        // Check if we need to create the post_votes table
        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
            id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
            time DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
            name VARCHAR(255) NOT NULL,
            text LONGTEXT NOT NULL,
            url VARCHAR(500) DEFAULT '' NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY time_index (time),
            KEY name_index (name(191))
        ) {$wpdb->get_charset_collate()};";
        
        // Include WordPress upgrade functions
        if (!function_exists('dbDelta')) {
            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        }
        
        // Execute table creation/update
        dbDelta($sql);
        
        // Update version option
        update_option('cms_db_version', $curr_ver, 'no');
        
        // Log successful upgrade
        if (defined('WP_DEBUG') && WP_DEBUG) {
            error_log('CMS Plugin: Database upgraded to version ' . $curr_ver);
        }
    }
}

/**
 * Check and update database version on plugin load
 * 
 * @since 1.0.0
 * @return void
 */
function cms_plugin_update_db_check() {
    // Only run in admin area to avoid frontend performance impact
    if (!is_admin()) {
        return;
    }
    
    $installed_ver = get_option('cms_db_version');
    $curr_ver = CMS_PLUGIN_DB_VER;
    
    // Validate versions
    if (!is_string($installed_ver) || !is_string($curr_ver)) {
        return;
    }
    
    // Trigger upgrade if versions don't match
    if ($installed_ver !== $curr_ver) {
        cms_db_upgrade();
    }
}
add_action('plugins_loaded', 'cms_plugin_update_db_check');

/**
 * Alternative: Activation hook for initial table creation
 * This ensures tables are created when plugin is activated
 * 
 * @since 1.0.0
 * @return void
 */
function cms_activate_plugin() {
    cms_reactions_table();
    cms_db_upgrade();
}
register_activation_hook(__FILE__, 'cms_activate_plugin');

/**
 * Clean up database on plugin uninstall
 * 
 * @since 1.0.0
 * @return void
 */
function cms_uninstall_plugin() {
    global $wpdb;
    
    // Verify user capabilities
    if (!current_user_can('activate_plugins')) {
        return;
    }
    
    // Check if we should remove tables (optional - based on setting)
    if (get_option('cms_remove_tables_on_uninstall', false)) {
        $tables = array(
            $wpdb->prefix . 'reactions',
            $wpdb->prefix . 'post_votes'
        );
        
        foreach ($tables as $table) {
            $sanitized_table = esc_sql($table);
            if (preg_match('/^[a-zA-Z0-9_]+$/', $sanitized_table)) {
                $wpdb->query("DROP TABLE IF EXISTS {$sanitized_table}");
            }
        }
        
        // Remove options
        delete_option('cms_db_version');
        delete_option('cms_remove_tables_on_uninstall');
    }
}
register_uninstall_hook(__FILE__, 'cms_uninstall_plugin');

/**
 * Add settings link to plugins page
 * 
 * @since 1.0.0
 * @param array $links Plugin action links
 * @return array Modified plugin action links
 */
function cms_plugin_action_links($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=cms-settings') . '">' 
                    . esc_html__('Settings', 'text-domain') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'cms_plugin_action_links');