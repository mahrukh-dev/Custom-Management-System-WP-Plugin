<?php
/**
 * CMS Database Installation
 * Creates all required tables on plugin activation
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create database tables
 */
function cms_create_database_tables() {
    global $wpdb;
    
    // Set charset collate
    $charset_collate = $wpdb->get_charset_collate();
    
    // Table names with prefix
    $table_users = $wpdb->prefix . 'cms_users';
    $table_main_admin = $wpdb->prefix . 'cms_main_admin';
    $table_admin = $wpdb->prefix . 'cms_admin';
    $table_employee = $wpdb->prefix . 'cms_employee';
    $table_increment_history = $wpdb->prefix . 'cms_increment_history';
    $table_corp_acc = $wpdb->prefix . 'cms_corp_acc';
    $table_shift_history = $wpdb->prefix . 'cms_shift_history';
    $table_requests = $wpdb->prefix . 'cms_requests';
    $table_emp_salary = $wpdb->prefix . 'cms_emp_salary';
    $table_msg_history = $wpdb->prefix . 'cms_msg_history';
    $table_shift_management = $wpdb->prefix . 'cms_shift_management';
    $table_emp_corp_assign = $wpdb->prefix . 'cms_emp_corp_assign';
    
    // SQL for each table
    
    // 1. USERS table
    $sql_users = "CREATE TABLE IF NOT EXISTS $table_users (
        id INT(11) NOT NULL AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        role ENUM('main_admin', 'admin', 'employee', 'corp_account') NOT NULL DEFAULT 'employee',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        last_login DATETIME NULL,
        status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
        PRIMARY KEY (id),
        INDEX idx_username (username),
        INDEX idx_role (role)
    ) $charset_collate;";
    
    // 2. MAIN_ADMIN table
    $sql_main_admin = "CREATE TABLE IF NOT EXISTS $table_main_admin (
        id INT(11) NOT NULL AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        father_name VARCHAR(100) NOT NULL,
        contact_num VARCHAR(20) NOT NULL,
        emergency_cno VARCHAR(20) NOT NULL,
        ref1_name VARCHAR(100) NOT NULL,
        ref1_cno VARCHAR(20) NOT NULL,
        ref2_name VARCHAR(100) NOT NULL,
        ref2_cno VARCHAR(20) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (username) REFERENCES $table_users(username) ON DELETE CASCADE,
        INDEX idx_email (email)
    ) $charset_collate;";
    
    // 3. ADMIN table
    $sql_admin = "CREATE TABLE IF NOT EXISTS $table_admin (
        id INT(11) NOT NULL AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        father_name VARCHAR(100) NOT NULL,
        contact_num VARCHAR(20) NOT NULL,
        emergency_cno VARCHAR(20) NOT NULL,
        ref1_name VARCHAR(100) NOT NULL,
        ref1_cno VARCHAR(20) NOT NULL,
        ref2_name VARCHAR(100) NOT NULL,
        ref2_cno VARCHAR(20) NOT NULL,
        position VARCHAR(50) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (username) REFERENCES $table_users(username) ON DELETE CASCADE,
        INDEX idx_email (email),
        INDEX idx_position (position)
    ) $charset_collate;";
    
    // 4. EMPLOYEE table
    $sql_employee = "CREATE TABLE IF NOT EXISTS $table_employee (
        id INT(11) NOT NULL AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        father_name VARCHAR(100) NOT NULL,
        contact_num VARCHAR(20) NOT NULL,
        emergency_cno VARCHAR(20) NOT NULL,
        ref1_name VARCHAR(100) NOT NULL,
        ref1_cno VARCHAR(20) NOT NULL,
        ref2_name VARCHAR(100) NOT NULL,
        ref2_cno VARCHAR(20) NOT NULL,
        joining_date DATE NOT NULL,
        wage_type ENUM('hourly', 'monthly') NOT NULL,
        basic_wage DECIMAL(10,2) NOT NULL,
        increment_date DATE NULL,
        increment_percentage DECIMAL(5,2) NULL,
        updated_wage DECIMAL(10,2) NULL,
        corp_team VARCHAR(50) NOT NULL,
        position VARCHAR(100) NOT NULL,
        cnic_no VARCHAR(20) NOT NULL UNIQUE,
        cnic_pdf VARCHAR(255) NULL,
        char_cert_no VARCHAR(50) NULL,
        char_cert_exp DATE NULL,
        char_cert_pdf VARCHAR(255) NULL,
        emp_letter_pdf VARCHAR(255) NULL,
        termination_date DATE NULL,
        status ENUM('active', 'inactive', 'terminated') DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (username) REFERENCES $table_users(username) ON DELETE CASCADE,
        INDEX idx_email (email),
        INDEX idx_cnic (cnic_no),
        INDEX idx_team (corp_team),
        INDEX idx_status (status)
    ) $charset_collate;";
    
    // 5. INCREMENT_HISTORY table
    $sql_increment_history = "CREATE TABLE IF NOT EXISTS $table_increment_history (
        id INT(11) NOT NULL AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL,
        increment_date DATE NOT NULL,
        basic_wage DECIMAL(10,2) NOT NULL,
        updated_wage DECIMAL(10,2) NOT NULL,
        increment_percentage DECIMAL(5,2) NOT NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (username) REFERENCES $table_employee(username) ON DELETE CASCADE,
        INDEX idx_username (username),
        INDEX idx_date (increment_date)
    ) $charset_collate;";
    
    // 6. CORP_ACC table
    $sql_corp_acc = "CREATE TABLE IF NOT EXISTS $table_corp_acc (
        id INT(11) NOT NULL AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL UNIQUE,
        company_name VARCHAR(200) NOT NULL,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        phone_no VARCHAR(20) NOT NULL,
        address TEXT NOT NULL,
        website VARCHAR(100) NULL,
        status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (username) REFERENCES $table_users(username) ON DELETE CASCADE,
        INDEX idx_company (company_name),
        INDEX idx_email (email)
    ) $charset_collate;";
    
    // 7. SHIFT_HISTORY table (UPDATED)
$sql_shift_history = "CREATE TABLE IF NOT EXISTS $table_shift_history (
    id INT(11) NOT NULL AUTO_INCREMENT,
    shift_management_id INT(11) NOT NULL,
    username VARCHAR(50) NOT NULL,
    date DATE NOT NULL,
    shift_start_time TIME NOT NULL,
    shift_end_time TIME NOT NULL,
    actual_login_time TIME NULL,
    actual_logout_time TIME NULL,
    actual_hours INT(11) NULL,
    actual_mins INT(11) NULL,
    counted_login_time TIME NULL,
    counted_logout_time TIME NULL,
    counted_hours INT(11) NULL,
    counted_mins INT(11) NULL,
    status ENUM('active', 'completed', 'missed') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (shift_management_id) REFERENCES $table_shift_management(id) ON DELETE CASCADE,
    FOREIGN KEY (username) REFERENCES $table_employee(username) ON DELETE CASCADE,
    UNIQUE KEY unique_shift_instance (shift_management_id, date),
    INDEX idx_username (username),
    INDEX idx_date (date),
    INDEX idx_status (status)
) $charset_collate;";
    // 8. REQUESTS table (UPDATED)
$sql_requests = "CREATE TABLE IF NOT EXISTS $table_requests (
    id INT(11) NOT NULL AUTO_INCREMENT,
    shift_history_id INT(11) NOT NULL,
    username VARCHAR(50) NOT NULL,
    type ENUM('late_login', 'early_login', 'late_logout', 'early_logout', 'absent', 'other') NOT NULL,
    request TEXT NOT NULL,
    date DATE NOT NULL,
    deviation_minutes INT(11) NOT NULL COMMENT 'Minutes deviation from scheduled time',
    scheduled_time TIME NOT NULL,
    actual_time TIME NOT NULL,
    details TEXT NULL COMMENT 'User can fill this later',
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_username VARCHAR(50) NULL,
    admin_notes TEXT NULL,
    processed_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    FOREIGN KEY (shift_history_id) REFERENCES $table_shift_history(id) ON DELETE CASCADE,
    FOREIGN KEY (username) REFERENCES $table_employee(username) ON DELETE CASCADE,
    FOREIGN KEY (admin_username) REFERENCES $table_admin(username) ON DELETE SET NULL,
    UNIQUE KEY unique_shift_request (shift_history_id, type),
    INDEX idx_username (username),
    INDEX idx_status (status),
    INDEX idx_date (date),
    INDEX idx_type (type)
) $charset_collate;";
    // 9. EMP_SALARY table
    $sql_emp_salary = "CREATE TABLE IF NOT EXISTS $table_emp_salary (
        id INT(11) NOT NULL AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL,
        month DATE NOT NULL COMMENT 'First day of month',
        hours DECIMAL(10,2) NULL,
        wage DECIMAL(10,2) NOT NULL,
        status ENUM('paid', 'not_paid', 'partially_paid') DEFAULT 'not_paid',
        bonus DECIMAL(10,2) DEFAULT 0.00,
        tax DECIMAL(10,2) DEFAULT 0.00,
        total_pay DECIMAL(10,2) NULL,
        half_pay_1 DECIMAL(10,2) NULL COMMENT '1st-15th',
        half_pay_2 DECIMAL(10,2) NULL COMMENT '16th-end',
        half_1_status ENUM('paid', 'not_paid') DEFAULT 'not_paid',
        half_2_status ENUM('paid', 'not_paid') DEFAULT 'not_paid',
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (username) REFERENCES $table_employee(username) ON DELETE CASCADE,
        UNIQUE KEY unique_month (username, month),
        INDEX idx_username (username),
        INDEX idx_month (month),
        INDEX idx_status (status)
    ) $charset_collate;";
    
    // 10. MSG_HISTORY table
    $sql_msg_history = "CREATE TABLE IF NOT EXISTS $table_msg_history (
        id INT(11) NOT NULL AUTO_INCREMENT,
        username_sender VARCHAR(50) NOT NULL,
        username_receiver VARCHAR(50) NOT NULL,
        message TEXT NOT NULL,
        mark_as_read BOOLEAN DEFAULT FALSE,
        read_at DATETIME NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (username_sender) REFERENCES $table_users(username) ON DELETE CASCADE,
        FOREIGN KEY (username_receiver) REFERENCES $table_users(username) ON DELETE CASCADE,
        INDEX idx_sender (username_sender),
        INDEX idx_receiver (username_receiver),
        INDEX idx_read (mark_as_read),
        INDEX idx_created (created_at)
    ) $charset_collate;";
    
    // 11. SHIFT_MANAGEMENT table
    $sql_shift_management = "CREATE TABLE IF NOT EXISTS $table_shift_management (
        id INT(11) NOT NULL AUTO_INCREMENT,
        emp_username VARCHAR(50) NOT NULL,
        date DATE NOT NULL,
        shift_start_time TIME NOT NULL,
        shift_end_time TIME NOT NULL,
        corp_acc_username VARCHAR(50) NULL,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        updated_at DATETIME ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (emp_username) REFERENCES $table_employee(username) ON DELETE CASCADE,
        FOREIGN KEY (corp_acc_username) REFERENCES $table_corp_acc(username) ON DELETE SET NULL,
        INDEX idx_emp (emp_username),
        INDEX idx_date (date),
        INDEX idx_corp (corp_acc_username)
    ) $charset_collate;";
    
    // 12. EMP_CORP_ASSIGN table
    $sql_emp_corp_assign = "CREATE TABLE IF NOT EXISTS $table_emp_corp_assign (
        id INT(11) NOT NULL AUTO_INCREMENT,
        username_emp VARCHAR(50) NOT NULL,
        username_corp_acc VARCHAR(50) NOT NULL,
        assigned_at DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        FOREIGN KEY (username_emp) REFERENCES $table_employee(username) ON DELETE CASCADE,
        FOREIGN KEY (username_corp_acc) REFERENCES $table_corp_acc(username) ON DELETE CASCADE,
        UNIQUE KEY unique_assignment (username_emp, username_corp_acc),
        INDEX idx_emp (username_emp),
        INDEX idx_corp (username_corp_acc)
    ) $charset_collate;";
    
    // Include WordPress upgrade library
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    
    // Execute SQL
    dbDelta($sql_users);
    dbDelta($sql_main_admin);
    dbDelta($sql_admin);
    dbDelta($sql_employee);
    dbDelta($sql_increment_history);
    dbDelta($sql_corp_acc);
    dbDelta($sql_shift_history);
    dbDelta($sql_requests);
    dbDelta($sql_emp_salary);
    dbDelta($sql_msg_history);
    dbDelta($sql_shift_management);
    dbDelta($sql_emp_corp_assign);
    
    // Log success
    error_log('CMS: All database tables created successfully');
    
    // Insert default data
    cms_insert_default_data();
}

/**
 * Insert default data (for testing)
 */
/**
 * Insert default data (for testing)
 */
function cms_insert_default_data() {
    global $wpdb;
    
    $table_users = $wpdb->prefix . 'cms_users';
    $table_main_admin = $wpdb->prefix . 'cms_main_admin';
    
    // Check if main admin exists in users table
    $main_admin_exists = $wpdb->get_var("SELECT COUNT(*) FROM $table_users WHERE role = 'main_admin'");
    
    if ($main_admin_exists == 0) {
        // Insert default main admin user
        $wpdb->insert(
            $table_users,
            array(
                'username' => 'admin',
                'password' => wp_hash_password('Admin@123'),
                'role' => 'main_admin',
                'status' => 'active',
                'created_at' => current_time('mysql')
            )
        );
        
        $user_id = $wpdb->insert_id;
        error_log('CMS: Default main admin user created with ID: ' . $user_id);
        
        // Now insert the main admin profile
        $wpdb->insert(
            $table_main_admin,
            array(
                'username' => 'admin',
                'name' => 'System Administrator',
                'email' => 'admin@example.com',
                'father_name' => 'Admin Father',
                'contact_num' => '+1 234-567-8900',
                'emergency_cno' => '+1 234-567-8901',
                'ref1_name' => 'Reference One',
                'ref1_cno' => '+1 234-567-8902',
                'ref2_name' => 'Reference Two',
                'ref2_cno' => '+1 234-567-8903',
                'created_at' => current_time('mysql')
            )
        );
        
        error_log('CMS: Default main admin profile created for username: admin');
        
    } else {
        error_log('CMS: Main admin already exists, skipping default data');
    }
}

/**
 * Drop database tables (on uninstall)
 */
function cms_drop_database_tables() {
    global $wpdb;
    
    $tables = [
        $wpdb->prefix . 'cms_emp_corp_assign',
        $wpdb->prefix . 'cms_shift_management',
        $wpdb->prefix . 'cms_msg_history',
        $wpdb->prefix . 'cms_emp_salary',
        $wpdb->prefix . 'cms_requests',
        $wpdb->prefix . 'cms_shift_history',
        $wpdb->prefix . 'cms_increment_history',
        $wpdb->prefix . 'cms_employee',
        $wpdb->prefix . 'cms_corp_acc',
        $wpdb->prefix . 'cms_admin',
        $wpdb->prefix . 'cms_main_admin',
        $wpdb->prefix . 'cms_users'
    ];
    
    foreach ($tables as $table) {
        $wpdb->query("DROP TABLE IF EXISTS $table");
    }
    
    error_log('CMS: All database tables dropped');
}

/**
 * Database helper functions
 */
// In database.php, around line 302, update these functions:

// Get user by username
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

// Check if username exists
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

// Check if email exists in any user type
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

// Get user role
if (!function_exists('cms_get_user_role')) {
    function cms_get_user_role($username) {
        $user = cms_get_user($username);
        return $user ? $user->role : null;
    }
}

// Update last login
function cms_update_last_login($username) {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_users';
    return $wpdb->update(
        $table,
        array('last_login' => current_time('mysql')),
        array('username' => $username)
    );
}

// Get all employees for a corporate account
function cms_get_corp_employees($corp_username) {
    global $wpdb;
    
    $assign_table = $wpdb->prefix . 'cms_emp_corp_assign';
    $emp_table = $wpdb->prefix . 'cms_employee';
    
    return $wpdb->get_results($wpdb->prepare(
        "SELECT e.* FROM $emp_table e
         INNER JOIN $assign_table a ON e.username = a.username_emp
         WHERE a.username_corp_acc = %s
         ORDER BY e.name ASC",
        $corp_username
    ));
}

// Get pending requests for admin
function cms_get_pending_requests() {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_requests';
    return $wpdb->get_results(
        "SELECT r.*, e.name as employee_name, e.position 
         FROM $table r
         INNER JOIN {$wpdb->prefix}cms_employee e ON r.username = e.username
         WHERE r.status = 'pending'
         ORDER BY r.created_at DESC"
    );
}

// Get unread messages for user
function cms_get_unread_messages($username) {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_msg_history';
    return $wpdb->get_results($wpdb->prepare(
        "SELECT m.*, u.role as sender_role 
         FROM $table m
         INNER JOIN {$wpdb->prefix}cms_users u ON m.username_sender = u.username
         WHERE m.username_receiver = %s AND m.mark_as_read = 0
         ORDER BY m.created_at DESC",
        $username
    ));
}

// Calculate employee hours for salary period
function cms_calculate_employee_hours($username, $start_date, $end_date) {
    global $wpdb;
    $table = $wpdb->prefix . 'cms_shift_history';
    
    $result = $wpdb->get_row($wpdb->prepare(
        "SELECT SUM(actual_hours * 60 + actual_mins) as total_minutes,
                COUNT(*) as days_worked
         FROM $table
         WHERE username = %s 
         AND date BETWEEN %s AND %s
         AND actual_hours IS NOT NULL",
        $username,
        $start_date,
        $end_date
    ));
    
    $total_hours = $result ? floor($result->total_minutes / 60) : 0;
    $total_minutes = $result ? $result->total_minutes % 60 : 0;
    
    return array(
        'hours' => $total_hours,
        'minutes' => $total_minutes,
        'total_minutes' => $result ? $result->total_minutes : 0,
        'days_worked' => $result ? $result->days_worked : 0
    );
}

// Generate salary for employee
function cms_generate_employee_salary($username, $month) {
    global $wpdb;
    
    $emp_table = $wpdb->prefix . 'cms_employee';
    $salary_table = $wpdb->prefix . 'cms_emp_salary';
    
    // Get employee details
    $employee = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $emp_table WHERE username = %s",
        $username
    ));
    
    if (!$employee) {
        return false;
    }
    
    // Calculate period dates
    $month_start = date('Y-m-01', strtotime($month));
    $month_end = date('Y-m-t', strtotime($month));
    $first_half_end = date('Y-m-15', strtotime($month));
    $second_half_start = date('Y-m-16', strtotime($month));
    
    // Calculate hours for each half
    $first_half_hours = cms_calculate_employee_hours($username, $month_start, $first_half_end);
    $second_half_hours = cms_calculate_employee_hours($username, $second_half_start, $month_end);
    
    $total_minutes = $first_half_hours['total_minutes'] + $second_half_hours['total_minutes'];
    $total_hours = floor($total_minutes / 60);
    $total_minutes_remainder = $total_minutes % 60;
    
    // Calculate pay
    $wage = floatval($employee->basic_wage);
    $total_pay = 0;
    $half_pay_1 = 0;
    $half_pay_2 = 0;
    
    if ($employee->wage_type == 'hourly') {
        $half_pay_1 = ($first_half_hours['total_minutes'] / 60) * $wage;
        $half_pay_2 = ($second_half_hours['total_minutes'] / 60) * $wage;
        $total_pay = $half_pay_1 + $half_pay_2;
    } else {
        // Monthly salary - split equally
        $half_pay_1 = $wage / 2;
        $half_pay_2 = $wage / 2;
        $total_pay = $wage;
    }
    
    // Check if salary record exists
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM $salary_table WHERE username = %s AND month = %s",
        $username,
        $month_start
    ));
    
    $data = array(
        'username' => $username,
        'month' => $month_start,
        'hours' => $total_hours + ($total_minutes_remainder / 60),
        'wage' => $wage,
        'total_pay' => round($total_pay, 2),
        'half_pay_1' => round($half_pay_1, 2),
        'half_pay_2' => round($half_pay_2, 2),
        'half_1_status' => 'not_paid',
        'half_2_status' => 'not_paid',
        'status' => 'not_paid'
    );
    
    if ($exists) {
        $wpdb->update($salary_table, $data, array('id' => $exists));
    } else {
        $wpdb->insert($salary_table, $data);
    }
    
    return $data;
}

?>