<?php
/**
 * CMS Admin Common Functions
 * Shared functions for all admin CRUD operations
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Check if username exists
 */
if (!function_exists('cms_username_exists')) {
    function cms_username_exists($username) {
        global $wpdb;
        $table = $wpdb->prefix . 'cms_users';
        return $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table WHERE username = %s",
            $username
        )) > 0;
    }
}

/**
 * Check if email exists in any user type
 */
if (!function_exists('cms_email_exists')) {
    function cms_email_exists($email) {
        global $wpdb;
        
        $tables = [
            $wpdb->prefix . 'cms_main_admin',
            $wpdb->prefix . 'cms_admin',
            $wpdb->prefix . 'cms_employee',
            $wpdb->prefix . 'cms_corp_acc'
        ];
        
        foreach ($tables as $table) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM $table WHERE email = %s",
                $email
            ));
            if ($exists > 0) {
                return true;
            }
        }
        
        return false;
    }
}

/**
 * Get user by username
 */
if (!function_exists('cms_get_user')) {
    function cms_get_user($username) {
        global $wpdb;
        $table = $wpdb->prefix . 'cms_users';
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE username = %s",
            $username
        ));
    }
}

/**
 * Get user role
 */
if (!function_exists('cms_get_user_role')) {
    function cms_get_user_role($username) {
        $user = cms_get_user($username);
        return $user ? $user->role : null;
    }
}

/**
 * Get admin by ID from database
 */
if (!function_exists('cms_get_admin_by_id')) {
    function cms_get_admin_by_id($id) {
        global $wpdb;
        
        $table_admin = $wpdb->prefix . 'cms_admin';
        $table_users = $wpdb->prefix . 'cms_users';
        
        // Join with users table to get status
        return $wpdb->get_row($wpdb->prepare(
            "SELECT a.*, u.status, u.last_login, u.role 
             FROM $table_admin a
             INNER JOIN $table_users u ON a.username = u.username
             WHERE a.id = %d",
            $id
        ), ARRAY_A);
    }
}

/**
 * Get admin by username from database
 */
if (!function_exists('cms_get_admin_by_username')) {
    function cms_get_admin_by_username($username) {
        global $wpdb;
        
        $table_admin = $wpdb->prefix . 'cms_admin';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_admin WHERE username = %s",
            $username
        ), ARRAY_A);
    }
}

/**
 * Get all admins with pagination and filters
 */
if (!function_exists('cms_get_all_admins')) {
    function cms_get_all_admins($args = array()) {
        global $wpdb;
        
        $table_admin = $wpdb->prefix . 'cms_admin';
        $table_users = $wpdb->prefix . 'cms_users';
        
        $defaults = array(
            'status' => '',
            'position' => '',
            'search' => '',
            'orderby' => 'a.created_at',
            'order' => 'DESC',
            'limit' => 10,
            'offset' => 0
        );
        
        $params = wp_parse_args($args, $defaults);
        
        // Build WHERE clause
        $where_conditions = array("u.role = 'admin'");
        $where_values = array();
        
        if (!empty($params['status'])) {
            $where_conditions[] = "u.status = %s";
            $where_values[] = $params['status'];
        }
        
        if (!empty($params['position'])) {
            $where_conditions[] = "a.position = %s";
            $where_values[] = $params['position'];
        }
        
        if (!empty($params['search'])) {
            $where_conditions[] = "(a.name LIKE %s OR a.email LIKE %s OR a.username LIKE %s OR a.position LIKE %s)";
            $search_term = '%' . $wpdb->esc_like($params['search']) . '%';
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
            $where_values[] = $search_term;
        }
        
        $where_sql = !empty($where_conditions) ? "WHERE " . implode(" AND ", $where_conditions) : "";
        
        // Get total count for pagination
        $count_sql = "SELECT COUNT(*) FROM $table_admin a 
                      INNER JOIN $table_users u ON a.username = u.username 
                      $where_sql";
        
        if (!empty($where_values)) {
            $total_items = $wpdb->get_var($wpdb->prepare($count_sql, $where_values));
        } else {
            $total_items = $wpdb->get_var($count_sql);
        }
        
        // Get paginated results
        $sql = "SELECT a.*, u.status, u.last_login, u.created_at as user_created 
                FROM $table_admin a
                INNER JOIN $table_users u ON a.username = u.username
                $where_sql
                ORDER BY {$params['orderby']} {$params['order']}
                LIMIT %d OFFSET %d";
        
        $where_values[] = $params['limit'];
        $where_values[] = $params['offset'];
        
        $results = $wpdb->get_results($wpdb->prepare($sql, $where_values), ARRAY_A);
        
        return array(
            'items' => $results,
            'total' => intval($total_items),
            'pages' => ceil($total_items / $params['limit'])
        );
    }
}

/**
 * Delete admin by ID
 */
if (!function_exists('cms_delete_admin_by_id')) {
    function cms_delete_admin_by_id($admin_id, $hard_delete = false) {
        global $wpdb;
        
        $table_admin = $wpdb->prefix . 'cms_admin';
        $table_users = $wpdb->prefix . 'cms_users';
        
        // Get admin username first
        $admin = $wpdb->get_row($wpdb->prepare(
            "SELECT username FROM $table_admin WHERE id = %d",
            $admin_id
        ));
        
        if (!$admin) {
            return false;
        }
        
        $username = $admin->username;
        
        if ($hard_delete) {
            // Hard delete - remove from both tables
            $wpdb->query('START TRANSACTION');
            
            $admin_deleted = $wpdb->delete($table_admin, array('id' => $admin_id), array('%d'));
            $user_deleted = $wpdb->delete($table_users, array('username' => $username), array('%s'));
            
            if ($admin_deleted && $user_deleted) {
                $wpdb->query('COMMIT');
                return true;
            } else {
                $wpdb->query('ROLLBACK');
                return false;
            }
        } else {
            // Soft delete - just mark as inactive
            return $wpdb->update(
                $table_users,
                array('status' => 'inactive'),
                array('username' => $username),
                array('%s'),
                array('%s')
            );
        }
    }
}

/**
 * Get admin activity summary
 */
if (!function_exists('cms_get_admin_activity_summary')) {
    function cms_get_admin_activity_summary($username) {
        global $wpdb;
        
        $table_requests = $wpdb->prefix . 'cms_requests';
        $table_msg_history = $wpdb->prefix . 'cms_msg_history';
        
        $activity = array();
        
        // Count requests processed by this admin
        $activity['requests_processed'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_requests WHERE admin_username = %s",
            $username
        ));
        
        // Count messages sent
        $activity['messages_sent'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_msg_history WHERE username_sender = %s",
            $username
        ));
        
        // Count messages received
        $activity['messages_received'] = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_msg_history WHERE username_receiver = %s",
            $username
        ));
        
        return $activity;
    }
}

/**
 * Get recent activity for admin
 */
if (!function_exists('cms_get_admin_recent_activity')) {
    function cms_get_admin_recent_activity($username, $limit = 5) {
        global $wpdb;
        
        $table_requests = $wpdb->prefix . 'cms_requests';
        $table_msg_history = $wpdb->prefix . 'cms_msg_history';
        $table_employee = $wpdb->prefix . 'cms_employee';
        
        $activities = array();
        
        // Get recent requests processed
        $requests = $wpdb->get_results($wpdb->prepare(
            "SELECT r.*, e.name as employee_name 
             FROM $table_requests r
             LEFT JOIN $table_employee e ON r.username = e.username
             WHERE r.admin_username = %s
             ORDER BY r.processed_at DESC
             LIMIT %d",
            $username,
            $limit
        ), ARRAY_A);
        
        foreach ($requests as $request) {
            $activities[] = array(
                'type' => 'request',
                'action' => 'Processed ' . $request['type'] . ' request',
                'details' => 'For employee: ' . ($request['employee_name'] ?? $request['username']),
                'time' => $request['processed_at'] ?? $request['created_at'],
                'status' => $request['status']
            );
        }
        
        // Get recent messages
        $messages = $wpdb->get_results($wpdb->prepare(
            "SELECT m.*, 
                    CASE 
                        WHEN m.username_sender = %s THEN 'sent' 
                        ELSE 'received' 
                    END as direction
             FROM $table_msg_history m
             WHERE m.username_sender = %s OR m.username_receiver = %s
             ORDER BY m.created_at DESC
             LIMIT %d",
            $username,
            $username,
            $username,
            $limit
        ), ARRAY_A);
        
        foreach ($messages as $message) {
            $activities[] = array(
                'type' => 'message',
                'action' => $message['direction'] === 'sent' ? 'Sent message' : 'Received message',
                'details' => substr($message['message'], 0, 50) . (strlen($message['message']) > 50 ? '...' : ''),
                'time' => $message['created_at'],
                'read' => $message['mark_as_read']
            );
        }
        
        // Sort by time descending
        usort($activities, function($a, $b) {
            return strtotime($b['time']) - strtotime($a['time']);
        });
        
        return array_slice($activities, 0, $limit);
    }
}

/**
 * Get all positions from database for filter
 */
if (!function_exists('cms_get_admin_positions')) {
    function cms_get_admin_positions() {
        global $wpdb;
        $table_admin = $wpdb->prefix . 'cms_admin';
        
        return $wpdb->get_col("SELECT DISTINCT position FROM $table_admin ORDER BY position");
    }
}

/**
 * Get admin statistics for dashboard
 */
if (!function_exists('cms_get_admin_stats')) {
    function cms_get_admin_stats() {
        global $wpdb;
        
        $table_users = $wpdb->prefix . 'cms_users';
        
        $stats = $wpdb->get_results(
            "SELECT status, COUNT(*) as count 
             FROM $table_users 
             WHERE role = 'admin' 
             GROUP BY status",
            OBJECT_K
        );
        
        $total = $wpdb->get_var("SELECT COUNT(*) FROM $table_users WHERE role = 'admin'");
        
        return array(
            'total' => $total,
            'by_status' => $stats
        );
    }
}