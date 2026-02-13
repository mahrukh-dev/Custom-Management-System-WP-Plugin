<?php
/**
 * CMS Login Shortcode
 * Simple login form with no dependencies
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

if (!defined('CMS_LOGIN_SHORTCODE')) {
    define('CMS_LOGIN_SHORTCODE', 'cms_login');
}

/**
 * Simple Login Form Shortcode
 * Usage: [cms_login title="Login" button_text="Login"]
 */
function cms_simple_login_shortcode($atts) {
    // Parse attributes
    $atts = shortcode_atts(
        array(
            'title' => 'Login',
            'button_text' => 'Login',
            'forgot_text' => 'Forgot Password?',
            'email_placeholder' => 'Enter your email',
            'password_placeholder' => 'Enter your password',
            'forgot_link' => '#',
            'class' => ''
        ),
        $atts,
        'cms_login'
    );
    
    ob_start();
    ?>
    
    <style>
    /* Simple Login Form CSS - Line 123 is here */
    .cms-simple-login {
        max-width: 380px;
        margin: 20px auto;
        padding: 30px 25px;
        background: #ffffff;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
    }
    
    .cms-simple-login h3 {
        margin: 0 0 25px 0;
        font-size: 24px;
        color: #333;
        text-align: center;
        font-weight: 600;
    }
    
    .cms-simple-field {
        margin-bottom: 20px;
    }
    
    .cms-simple-field input {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid #ddd;
        border-radius: 6px;
        font-size: 14px;
        box-sizing: border-box;
        transition: all 0.3s ease;
    }
    
    .cms-simple-field input:focus {
        border-color: #0073aa;
        outline: none;
        box-shadow: 0 0 0 2px rgba(0,115,170,0.1);
    }
    
    .cms-simple-button {
        width: 100%;
        padding: 14px 20px;
        background: #0073aa;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 15px;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.3s ease;
    }
    
    .cms-simple-button:hover {
        background: #005a87;
    }
    
    .cms-simple-forgot {
        text-align: right;
        margin-top: 15px;
    }
    
    .cms-simple-forgot a {
        color: #0073aa;
        text-decoration: none;
        font-size: 13px;
        transition: color 0.3s ease;
    }
    
    .cms-simple-forgot a:hover {
        color: #005a87;
        text-decoration: underline;
    }
    
    .cms-login-message {
        padding: 12px 15px;
        margin-bottom: 20px;
        border-radius: 6px;
        font-size: 14px;
    }
    
    .cms-login-message.error {
        background: #f8d7da;
        color: #721c24;
        border: 1px solid #f5c6cb;
    }
    
    .cms-login-message.success {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
    }
    </style>
    
    <div class="cms-simple-login <?php echo esc_attr($atts['class']); ?>">
        <h3><?php echo esc_html($atts['title']); ?></h3>
        
        <?php
        // Display message if exists
        if (isset($_GET['login'])) {
            if ($_GET['login'] === 'failed') {
                echo '<div class="cms-login-message error">Invalid email or password.</div>';
            } elseif ($_GET['login'] === 'success') {
                echo '<div class="cms-login-message success">Login successful!</div>';
            }
        }
        ?>
        
        <form method="post" action="">
            <div class="cms-simple-field">
                <input type="email" 
                       name="cms_email" 
                       placeholder="<?php echo esc_attr($atts['email_placeholder']); ?>" 
                       required>
            </div>
            
            <div class="cms-simple-field">
                <input type="password" 
                       name="cms_password" 
                       placeholder="<?php echo esc_attr($atts['password_placeholder']); ?>" 
                       required>
            </div>
            
            <button type="submit" name="cms_login_submit" class="cms-simple-button">
                <?php echo esc_html($atts['button_text']); ?>
            </button>
            
            <div class="cms-simple-forgot">
                <a href="<?php echo esc_url($atts['forgot_link']); ?>">
                    <?php echo esc_html($atts['forgot_text']); ?>
                </a>
            </div>
        </form>
    </div>
    
    <?php
    return ob_get_clean();
}

// Register the shortcode
// Register the shortcode - use the constant
add_shortcode(CMS_LOGIN_SHORTCODE, 'cms_simple_login_shortcode');