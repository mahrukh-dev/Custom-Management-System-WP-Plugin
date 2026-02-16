<?php
/**
 * CMS Helper Functions
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Convert file path to URL
 */
function cms_get_file_url($file_path) {
    if (empty($file_path)) {
        return '';
    }
    
    // If it's already a URL, return as is
    if (filter_var($file_path, FILTER_VALIDATE_URL)) {
        return $file_path;
    }
    
    // Get WordPress uploads directory info
    $upload_dir = wp_upload_dir();
    $base_dir = $upload_dir['basedir'];
    $base_url = $upload_dir['baseurl'];
    
    // Convert Windows paths to use forward slashes
    $file_path = str_replace('\\', '/', $file_path);
    $base_dir = str_replace('\\', '/', $base_dir);
    
    // If the path starts with the base directory, replace it with the base URL
    if (strpos($file_path, $base_dir) === 0) {
        $relative_path = substr($file_path, strlen($base_dir));
        return $base_url . $relative_path;
    }
    
    // If it's just a filename, assume it's in the cms-employee-docs folder
    if (strpos($file_path, '/') === false) {
        return $base_url . '/cms-employee-docs/' . $file_path;
    }
    
    // If it's a relative path from uploads
    if (strpos($file_path, 'cms-employee-docs/') !== false) {
        return $base_url . '/' . ltrim($file_path, '/');
    }
    
    // If all else fails, return the original (might be a full server path)
    error_log('CMS: Could not convert file path to URL: ' . $file_path);
    
    return $file_path;
}

/**
 * Check if file exists
 */
function cms_file_exists($file_path) {
    if (empty($file_path)) {
        return false;
    }
    
    // If it's a URL, we can't easily check existence
    if (filter_var($file_path, FILTER_VALIDATE_URL)) {
        return true; // Assume it exists
    }
    
    return file_exists($file_path);
}


/**
 * Get employee by username
 */
function cms_get_employee_by_username($username) {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_employee';
    
    return $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table WHERE username = %s",
        $username
    ), ARRAY_A);
}