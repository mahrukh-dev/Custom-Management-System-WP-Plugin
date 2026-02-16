<?php
/**
 * Enhanced CMS Login System
 * Handles authentication, sessions, and redirects
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Initialize session handling
 */
function cms_init_session() {
    if (!session_id() && !headers_sent()) {
        session_start();
    }
}
add_action('init', 'cms_init_session', 1);

/**
 * Login Form Shortcode
 */
function cms_login_shortcode($atts) {
    $atts = shortcode_atts([
        'title' => 'Login',
        'button_text' => 'Login',
        'username_placeholder' => 'Username',
        'password_placeholder' => 'Password',
        'redirect' => '',
        'class' => ''
    ], $atts, 'cms_login');

    // Redirect if already logged in
    if (cms_is_user_logged_in()) {
        return cms_get_already_loggedin_message();
    }

    ob_start();
    ?>
    <div class="cms-login-wrapper <?php echo esc_attr($atts['class']); ?>">
        <?php cms_display_login_messages(); ?>
        
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" class="cms-login-form">
            <?php wp_nonce_field('cms_login_action', 'cms_login_nonce'); ?>
            <input type="hidden" name="action" value="cms_handle_login">
            <input type="hidden" name="redirect_to" value="<?php echo esc_url($atts['redirect']); ?>">
            <input type="hidden" name="page_url" value="<?php echo esc_url(get_permalink()); ?>">
            
            <h3><?php echo esc_html($atts['title']); ?></h3>
            
            <div class="cms-form-group">
                <input type="text" 
                       name="username" 
                       placeholder="<?php echo esc_attr($atts['username_placeholder']); ?>" 
                       required
                       autocomplete="username">
            </div>
            
            <div class="cms-form-group">
                <input type="password" 
                       name="password" 
                       placeholder="<?php echo esc_attr($atts['password_placeholder']); ?>" 
                       required
                       autocomplete="current-password">
            </div>
            
            <button type="submit" class="cms-login-button">
                <?php echo esc_html($atts['button_text']); ?>
            </button>
            
            <div class="cms-login-footer">
                <a href="<?php echo esc_url(home_url('/forgot-password')); ?>">Forgot Password?</a>
            </div>
        </form>
    </div>
    
    <style>
    .cms-login-wrapper {
        max-width: 400px;
        margin: 50px auto;
        padding: 30px;
        background: #fff;
        border-radius: 8px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .cms-login-wrapper h3 {
        text-align: center;
        margin-bottom: 25px;
        color: #333;
    }
    
    .cms-form-group {
        margin-bottom: 20px;
    }
    
    .cms-form-group input {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 4px;
        font-size: 15px;
        transition: border-color 0.3s;
    }
    
    .cms-form-group input:focus {
        border-color: #0073aa;
        outline: none;
        box-shadow: 0 0 0 2px rgba(0,115,170,0.1);
    }
    
    .cms-login-button {
        width: 100%;
        padding: 14px;
        background: #0073aa;
        color: #fff;
        border: none;
        border-radius: 4px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s;
    }
    
    .cms-login-button:hover {
        background: #005a87;
    }
    
    .cms-login-footer {
        text-align: center;
        margin-top: 20px;
    }
    
    .cms-login-footer a {
        color: #0073aa;
        text-decoration: none;
        font-size: 14px;
    }
    
    .cms-login-footer a:hover {
        text-decoration: underline;
    }
    
    .cms-message {
        padding: 12px 15px;
        margin-bottom: 20px;
        border-radius: 4px;
        font-size: 14px;
    }
    
    .cms-message.success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    
    .cms-message.error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .cms-message.info {
        background: #d1ecf1;
        color: #0c5460;
        border: 1px solid #bee5eb;
    }
    </style>
    <?php
    
    return ob_get_clean();
}
add_shortcode('cms_login', 'cms_login_shortcode');

/**
 * Handle login form submission
 */
function cms_handle_login_submission() {
    // Verify nonce
    if (!isset($_POST['cms_login_nonce']) || !wp_verify_nonce($_POST['cms_login_nonce'], 'cms_login_action')) {
        wp_redirect(add_query_arg('login', 'security_error', wp_get_referer()));
        exit;
    }

    $username = sanitize_user($_POST['username']);
    $password = $_POST['password'];
    $redirect_to = esc_url_raw($_POST['redirect_to']);
    $page_url = esc_url_raw($_POST['page_url']);

    // Validate input
    if (empty($username) || empty($password)) {
        wp_redirect(add_query_arg('login', 'empty_fields', $page_url));
        exit;
    }

    // Attempt login
    $result = cms_authenticate_user($username, $password);

    if ($result['success']) {
        // Set session data
        $_SESSION['cms_user'] = [
            'username' => $result['user']->username,
            'role' => $result['user']->role,
            'logged_in' => true,
            'login_time' => time(),
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT']
        ];

        // Set secure cookies if needed
        setcookie('cms_logged_in', '1', time() + (86400 * 7), COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
        
        // Redirect
        if (!empty($redirect_to)) {
            wp_redirect($redirect_to);
        } else {
            wp_redirect(cms_get_role_based_redirect($result['user']->role));
        }
        exit;
    } else {
        wp_redirect(add_query_arg('login', $result['error_code'], $page_url));
        exit;
    }
}
add_action('admin_post_nopriv_cms_handle_login', 'cms_handle_login_submission');
add_action('admin_post_cms_handle_login', 'cms_handle_login_submission');

/**
 * Authenticate user
 */
function cms_authenticate_user($username, $password) {
    global $wpdb;
    
    $table_users = $wpdb->prefix . 'cms_users';
    
    $user = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_users WHERE username = %s",
        $username
    ));

    if (!$user) {
        return [
            'success' => false,
            'error_code' => 'invalid_username'
        ];
    }

    if (!wp_check_password($password, $user->password)) {
        return [
            'success' => false,
            'error_code' => 'invalid_password'
        ];
    }

    if ($user->status !== 'active') {
        return [
            'success' => false,
            'error_code' => 'inactive_account'
        ];
    }

    // Update last login
    $wpdb->update(
        $table_users,
        ['last_login' => current_time('mysql')],
        ['username' => $username]
    );

    // Log successful login
    cms_log_login_attempt($username, 'success', $_SERVER['REMOTE_ADDR']);

    return [
        'success' => true,
        'user' => $user
    ];
}

/**
 * Log login attempts
 */
function cms_log_login_attempt($username, $status, $ip) {
    global $wpdb;
    
    $table = $wpdb->prefix . 'cms_login_logs';
    
    // Create log table if not exists
    $wpdb->query("CREATE TABLE IF NOT EXISTS $table (
        id INT(11) NOT NULL AUTO_INCREMENT,
        username VARCHAR(50) NOT NULL,
        status VARCHAR(20) NOT NULL,
        ip_address VARCHAR(45) NOT NULL,
        user_agent TEXT,
        login_time DATETIME DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        INDEX idx_username (username),
        INDEX idx_status (status),
        INDEX idx_time (login_time)
    ) {$wpdb->get_charset_collate()};");
    
    $wpdb->insert($table, [
        'username' => $username,
        'status' => $status,
        'ip_address' => $ip,
        'user_agent' => $_SERVER['HTTP_USER_AGENT']
    ]);
}

/**
 * Get role-based redirect URL
 */
function cms_get_role_based_redirect($role) {
    $redirects = [
        'main_admin' => home_url('/mainadmin-dashboard'),
        'admin' => home_url('/admin-dashboard'),
        'employee' => home_url('/employee-dashboard'),
        'corp_account' => home_url('/corporate-dashboard')
    ];
    
    return isset($redirects[$role]) ? $redirects[$role] : home_url();
}

/**
 * Check if user is logged in
 */
function cms_is_user_logged_in() {
    return isset($_SESSION['cms_user']) && $_SESSION['cms_user']['logged_in'] === true;
}

/**
 * Get current user
 */
function cms_get_current_user() {
    if (!cms_is_user_logged_in()) {
        return null;
    }
    return $_SESSION['cms_user'];
}

/**
 * Display login messages
 */
function cms_display_login_messages() {
    if (!isset($_GET['login'])) {
        return;
    }
    
    $messages = [
        'invalid_username' => ['Invalid username.', 'error'],
        'invalid_password' => ['Invalid password.', 'error'],
        'inactive_account' => ['Your account is inactive. Please contact administrator.', 'error'],
        'empty_fields' => ['Please fill in all fields.', 'error'],
        'security_error' => ['Security verification failed. Please try again.', 'error'],
        'loggedout' => ['You have been successfully logged out.', 'success'],
        'session_expired' => ['Your session has expired. Please login again.', 'info']
    ];
    
    $login_param = $_GET['login'];
    
    if (isset($messages[$login_param])) {
        list($message, $type) = $messages[$login_param];
        printf(
            '<div class="cms-message %s">%s</div>',
            esc_attr($type),
            esc_html($message)
        );
    }
}

/**
 * Already logged in message
 */
function cms_get_already_loggedin_message() {
    $user = cms_get_current_user();
    
    ob_start();
    ?>
    <div class="cms-login-wrapper">
        <div class="cms-message info">
            You are already logged in as <strong><?php echo esc_html($user['username']); ?></strong> 
            (<?php echo esc_html($user['role']); ?>).
        </div>
        <p style="text-align: center;">
            <a href="<?php echo esc_url(cms_get_role_based_redirect($user['role'])); ?>" 
               class="cms-login-button" 
               style="display: inline-block; text-decoration: none; margin-bottom: 10px;">
                Go to Dashboard
            </a><br>
            <a href="<?php echo esc_url(home_url('/?cms-logout=true')); ?>" 
               style="color: #dc3545; font-size: 14px;">
                Logout
            </a>
        </p>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Handle logout
 */
function cms_handle_logout() {
    if (isset($_GET['cms-logout']) && $_GET['cms-logout'] === 'true') {
        
        if (isset($_SESSION['cms_user'])) {
            // Log the logout
            error_log("CMS Logout: User {$_SESSION['cms_user']['username']} logged out");
            
            // Clear session
            $_SESSION = array();
            
            // Clear session cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }
            
            // Clear custom cookie
            setcookie('cms_logged_in', '', time() - 3600, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
        }
        
        // Destroy session
        session_destroy();
        
        // Redirect
        wp_redirect(add_query_arg('login', 'loggedout', home_url('/login')));
        exit;
    }
}
add_action('init', 'cms_handle_logout');

/**
 * Session security check
 */
function cms_check_session_security() {
    if (!cms_is_user_logged_in()) {
        return;
    }
    
    $user = $_SESSION['cms_user'];
    
    // Check if session is expired (24 hours)
    if (time() - $user['login_time'] > 86400) {
        cms_handle_logout();
        wp_redirect(add_query_arg('login', 'session_expired', home_url('/login')));
        exit;
    }
    
    // Check IP address for suspicious activity
    if ($user['ip'] !== $_SERVER['REMOTE_ADDR']) {
        // Optional: Log IP change
        error_log("CMS Security: IP address changed for user {$user['username']}");
        // You might want to force logout here for sensitive data
    }
}
add_action('init', 'cms_check_session_security');

/**
 * Shortcode for logout link
 */
function cms_logout_link_shortcode($atts) {
    $atts = shortcode_atts([
        'text' => 'Logout',
        'class' => 'cms-logout-link',
        'redirect' => '/login'
    ], $atts, 'cms_logout_link');
    
    if (!cms_is_user_logged_in()) {
        return '';
    }
    
    $logout_url = add_query_arg('cms-logout', 'true', home_url($atts['redirect']));
    
    return sprintf(
        '<a href="%s" class="%s">%s</a>',
        esc_url($logout_url),
        esc_attr($atts['class']),
        esc_html($atts['text'])
    );
}
add_shortcode('cms_logout_link', 'cms_logout_link_shortcode');

/**
 * Shortcode to show current user info
 */
function cms_current_user_info_shortcode($atts) {
    $atts = shortcode_atts([
        'field' => 'username', // username, role, or full
        'before' => '',
        'after' => ''
    ], $atts, 'cms_user_info');
    
    if (!cms_is_user_logged_in()) {
        return '';
    }
    
    $user = cms_get_current_user();
    
    if ($atts['field'] === 'full') {
        $info = "{$user['username']} ({$user['role']})";
    } else {
        $info = isset($user[$atts['field']]) ? $user[$atts['field']] : '';
    }
    
    if (empty($info)) {
        return '';
    }
    
    return $atts['before'] . esc_html($info) . $atts['after'];
}
add_shortcode('cms_user_info', 'cms_current_user_info_shortcode');

/**
 * Login check shortcode - shows content only to logged in users
 */
function cms_logged_in_only_shortcode($atts, $content = null) {
    if (cms_is_user_logged_in()) {
        return do_shortcode($content);
    }
    return '';
}
add_shortcode('cms_logged_in_only', 'cms_logged_in_only_shortcode');

/**
 * Role-based content shortcode
 */
function cms_role_based_content_shortcode($atts, $content = null) {
    $atts = shortcode_atts([
        'roles' => '', // comma-separated list of roles
        'message' => 'You do not have permission to view this content.'
    ], $atts, 'cms_role_content');
    
    if (!cms_is_user_logged_in()) {
        return '';
    }
    
    $allowed_roles = array_map('trim', explode(',', $atts['roles']));
    $user_role = $_SESSION['cms_user']['role'];
    
    if (in_array($user_role, $allowed_roles)) {
        return do_shortcode($content);
    }
    
    return '<p class="cms-no-permission">' . esc_html($atts['message']) . '</p>';
}
add_shortcode('cms_role_content', 'cms_role_based_content_shortcode');