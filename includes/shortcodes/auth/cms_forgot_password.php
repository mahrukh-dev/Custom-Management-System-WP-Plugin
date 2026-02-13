<?php
/**
 * CMS Forgot Password Shortcode
 * Simple forgot password form with email field only
 * 
 * Usage: [cms_forgot_password]
 * Usage: [cms_forgot_password title="Reset Password" button_text="Send Reset Link"]
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define shortcode slug
if (!defined('CMS_FORGOT_PASSWORD_SHORTCODE')) {
    define('CMS_FORGOT_PASSWORD_SHORTCODE', 'cms_forgot_password');
}


/**
 * Forgot Password Form Shortcode
 */
function cms_forgot_password_shortcode($atts) {
    // Parse attributes
    $atts = shortcode_atts(
        array(
            'title' => 'Forgot Password?',
            'description' => 'Enter your email address and we\'ll send you a link to reset your password.',
            'button_text' => 'Send Reset Link',
            'email_placeholder' => 'Enter your email address',
            'back_to_login_text' => 'Back to Login',
            'back_to_login_link' => '#',
            'success_message' => 'Password reset link sent! Please check your email.',
            'class' => ''
        ),
        $atts,
        'cms_forgot_password'
    );
    
    ob_start();
    ?>
    
    <style>
    /* CMS Forgot Password Form Styles */
    .cms-forgot-container {
        max-width: 420px;
        margin: 30px auto;
        padding: 35px 30px;
        background: #ffffff;
        border-radius: 12px;
        box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        border: 1px solid #f0f0f0;
    }
    
    .cms-forgot-title {
        margin: 0 0 15px 0;
        font-size: 26px;
        font-weight: 700;
        color: #1a2b3c;
        text-align: center;
        letter-spacing: -0.5px;
    }
    
    .cms-forgot-description {
        margin: 0 0 25px 0;
        font-size: 14px;
        line-height: 1.6;
        color: #6c7a89;
        text-align: center;
        padding: 0 10px;
    }
    
    .cms-forgot-field {
        margin-bottom: 25px;
    }
    
    .cms-forgot-field label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
        color: #2c3e50;
        font-size: 14px;
    }
    
    .cms-forgot-input {
        width: 100%;
        padding: 14px 18px;
        font-size: 15px;
        border: 2px solid #eef2f6;
        border-radius: 10px;
        transition: all 0.25s ease;
        box-sizing: border-box;
        background: #fafcfc;
    }
    
    .cms-forgot-input:focus {
        outline: none;
        border-color: #007cba;
        background: #ffffff;
        box-shadow: 0 0 0 4px rgba(0,124,186,0.05);
    }
    
    .cms-forgot-input::placeholder {
        color: #a0b3c2;
        font-size: 14px;
    }
    
    .cms-forgot-button {
        width: 100%;
        padding: 16px 20px;
        background: linear-gradient(145deg, #007cba, #0063a0);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 16px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.25s ease;
        letter-spacing: 0.3px;
        box-shadow: 0 4px 12px rgba(0,124,186,0.15);
    }
    
    .cms-forgot-button:hover {
        background: linear-gradient(145deg, #0063a0, #005287);
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(0,124,186,0.25);
    }
    
    .cms-forgot-button:active {
        transform: translateY(0);
    }
    
    .cms-back-to-login {
        text-align: center;
        margin-top: 25px;
        padding-top: 20px;
        border-top: 1px solid #eef2f6;
    }
    
    .cms-back-to-login a {
        color: #007cba;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: color 0.2s ease;
    }
    
    .cms-back-to-login a:hover {
        color: #005a87;
    }
    
    .cms-back-to-login a:before {
        content: '←';
        font-size: 16px;
    }
    
    .cms-message {
        padding: 14px 18px;
        border-radius: 10px;
        margin-bottom: 25px;
        font-size: 14px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .cms-message.success {
        background: #e3f7ec;
        color: #0a5c36;
        border: 1px solid #b8e0c2;
    }
    
    .cms-message.success:before {
        content: '✓';
        font-size: 16px;
        font-weight: bold;
    }
    
    .cms-message.error {
        background: #ffe8e8;
        color: #b34141;
        border: 1px solid #ffc9c9;
    }
    
    .cms-message.error:before {
        content: '⚠';
        font-size: 16px;
    }
    
    /* Loading state */
    .cms-forgot-button.loading {
        opacity: 0.7;
        cursor: not-allowed;
        position: relative;
    }
    
    .cms-forgot-button.loading:after {
        content: '';
        position: absolute;
        width: 20px;
        height: 20px;
        border: 2px solid #ffffff;
        border-top-color: transparent;
        border-radius: 50%;
        right: 20px;
        top: 50%;
        transform: translateY(-50%);
        animation: cms-spin 1s linear infinite;
    }
    
    @keyframes cms-spin {
        0% { transform: translateY(-50%) rotate(0deg); }
        100% { transform: translateY(-50%) rotate(360deg); }
    }
    
    /* Responsive */
    @media (max-width: 480px) {
        .cms-forgot-container {
            margin: 20px 15px;
            padding: 25px 20px;
        }
        
        .cms-forgot-title {
            font-size: 22px;
        }
    }
    </style>
    
    <div class="cms-forgot-container <?php echo esc_attr($atts['class']); ?>">
        <h3 class="cms-forgot-title"><?php echo esc_html($atts['title']); ?></h3>
        
        <?php if (!empty($atts['description'])): ?>
            <p class="cms-forgot-description"><?php echo esc_html($atts['description']); ?></p>
        <?php endif; ?>
        
        <?php
        // Display success/error messages based on URL parameters
        if (isset($_GET['reset']) && $_GET['reset'] === 'sent') {
            echo '<div class="cms-message success">' . esc_html($atts['success_message']) . '</div>';
        }
        
        if (isset($_GET['reset']) && $_GET['reset'] === 'invalid') {
            echo '<div class="cms-message error">Email address not found. Please try again.</div>';
        }
        
        if (isset($_GET['reset']) && $_GET['reset'] === 'error') {
            echo '<div class="cms-message error">Something went wrong. Please try again later.</div>';
        }
        ?>
        
        <form method="post" action="" class="cms-forgot-form">
            <div class="cms-forgot-field">
                <label for="cms-forgot-email">Email Address</label>
                <input 
                    type="email" 
                    id="cms-forgot-email" 
                    name="cms_forgot_email" 
                    class="cms-forgot-input" 
                    placeholder="<?php echo esc_attr($atts['email_placeholder']); ?>" 
                    required
                    autocomplete="email"
                >
            </div>
            
            <button type="submit" name="cms_forgot_submit" class="cms-forgot-button">
                <?php echo esc_html($atts['button_text']); ?>
            </button>
            
            <div class="cms-back-to-login">
                <a href="<?php echo esc_url($atts['back_to_login_link']); ?>">
                    <?php echo esc_html($atts['back_to_login_text']); ?>
                </a>
            </div>
        </form>
    </div>
    
    <?php
    return ob_get_clean();
}

// Register the shortcode - use the constant
add_shortcode(CMS_FORGOT_PASSWORD_SHORTCODE, 'cms_forgot_password_shortcode');

// Optional: Handle form submission (you can remove this if you handle it elsewhere)
function cms_handle_forgot_password_submission() {
    if (isset($_POST['cms_forgot_submit'])) {
        $email = sanitize_email($_POST['cms_forgot_email']);
        
        // Here you would add your email sending logic
        // For now, just redirect with success message
        
        wp_redirect(add_query_arg('reset', 'sent', wp_get_referer()));
        exit;
    }
}
add_action('init', 'cms_handle_forgot_password_submission');