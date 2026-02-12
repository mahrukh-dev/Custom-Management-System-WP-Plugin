<?php

// basic shortcode
function cms_text_shortcode()
{
    return esc_html('THIS is a test shortcode');
}
add_shortcode('CMS_TEXT', 'cms_text_shortcode');

//enclosing shortcode
function cms_enclosing_shortcode($atts = array(), $content = null)
{
    // Sanitize content
    $content = empty($content) ? '' : wp_kses_post($content);
    
    $html = '<a href="">';
    $html .= $content;
    $html .= "</a>";
    return $html;
}
add_shortcode('CMS_ENCLOSING', 'cms_enclosing_shortcode');

// shortcode with params
function cms_params_shortcode($atts = array(), $content = null)
{
    $atts = shortcode_atts( 
        array(
            'label' => 'Button Label',
            'link'  => 'https://www.google.com',
        ), $atts, 'CMS_PARAMS'
    );
    
    // Sanitize attributes
    $label = sanitize_text_field($atts['label']);
    $link = esc_url_raw($atts['link']);
    
    // Validate URL
    if (!wp_http_validate_url($link)) {
        $link = '#';
    }
    
    $html = '<a href="' . esc_url($link) . '">';
    $html .= esc_html($label);
    $html .= "</a>";
    return $html;
}
add_shortcode('CMS_PARAMS', 'cms_params_shortcode');


// Shortcodes with Parameters from MagicWP
function CMS_test_params_magicwp_shortcode($atts) {
    // Sanitize incoming attributes
    if(!empty($atts) && is_array($atts)) {
        // Sanitize individual attributes
        if(isset($atts['label'])) {
            $atts['label'] = sanitize_text_field($atts['label']);
        }
        if(isset($atts['link'])) {
            $atts['link'] = esc_url_raw($atts['link']);
        }
        
        // Extract and merge attributes with defaults
        $atts = shortcode_atts(array(
            'label' => 'Button Label',
            'link' => 'https://wp-plugin.test/projects/e-commerce-website-development/'
        ), $atts, 'CMS_TEST_PARAM_MAGICWP');
        
        // Validate URL
        if(!wp_http_validate_url($atts['link'])) {
            $atts['link'] = 'https://wp-plugin.test/projects/e-commerce-website-development/';
        }
    } else {
        $atts = array(
            'label' => 'Button Label',
            'link' => 'https://wp-plugin.test/projects/e-commerce-website-development/'
        );
    }
    
    // Start output buffering
    ob_start();
    ?>
    <div class="CMS-button-shortcode">
        <?php if(!empty($atts['link']) && !empty($atts['label'])) { ?>
        <a href="<?php echo esc_url($atts['link']); ?>" 
           style="padding: 10px; background-color: blue; color: white"
           target="_blank" 
           rel="noopener noreferrer">
           <?php echo esc_html($atts['label']); ?>
        </a>
        <?php } else { ?>
            <?php echo esc_html('Please assign link and label'); ?>
        <?php } ?>
    </div>
    <?php
    // Return the buffered content
    return ob_get_clean();
}
add_shortcode('CMS_TEST_PARAM_MAGICWP', 'CMS_test_params_magicwp_shortcode');

/**
 * Project Meta Information
 */
function CMS_project_meta_shortcode($atts) {

    $attrs = shortcode_atts(
        array(
            'id' => get_the_ID(),
        ), $atts, 'PROJECT_META'
    );
    
    // Sanitize 'id' to make sure it's numeric (safe integer)
    $post_id = intval($attrs['id']);
    
    // Validate that the provided post ID actually exists.
    if( !get_post_status( $post_id ) ) {
        return esc_html('Invalid project ID.');
    }
    
    // Get post meta
    $project_url = get_post_meta( $post_id, 'project_url', true );
    $project_completion = get_post_meta( $post_id, 'project_completion_duration', true );
    $project_cost = get_post_meta( $post_id, 'project_estimated_cost', true );
    
    // Sanitize meta values
    $project_url = esc_url_raw($project_url);
    $project_completion = sanitize_text_field($project_completion);
    $project_cost = sanitize_text_field($project_cost);
    
    // Validate URL
    if(!empty($project_url) && !wp_http_validate_url($project_url)) {
        $project_url = '';
    }

    $html = '<div class="project-meta">';
    
    // Only show if URL exists
    if(!empty($project_url)) {
        $html .= '<span><a href="'.esc_url($project_url).'" target="_blank" rel="noopener noreferrer">' . esc_html('Visit Project') . '</a></span>';
    }
    
    // Only show if completion exists
    if(!empty($project_completion)) {
        $html .= '<span>' . esc_html($project_completion) . '</span>';
    }
    
    // Only show if cost exists
    if(!empty($project_cost)) {
        $html .= '<span>' . esc_html($project_cost) . '</span>';
    }
    
    $html .= '</div>';

    return $html;
}
add_shortcode( 'PROJECT_META', 'CMS_project_meta_shortcode' );

function CMS_post_voting_buttons($atts) {
    // Check if user is logged in
    if (!is_user_logged_in()) {
        return esc_html('Please log in to vote');
    }
    
    $attrs = shortcode_atts(
        array(
            'like' => 'Like',
            'dislike' => 'Dislike',
        ), $atts, 'VOTING_BUTTONS'
    );
    
    // Sanitize button labels
    $like_label = sanitize_text_field($attrs['like']);
    $dislike_label = sanitize_text_field($attrs['dislike']);
    
    $post_id = get_the_ID();
    $user_id = get_current_user_id();
    
    // Validate post ID
    $post_id = intval($post_id);
    $user_id = intval($user_id);
    
    // Validate that the post exists and is public
    if( !get_post_status( $post_id ) || !is_post_publicly_viewable( $post_id ) ) {
        return esc_html('Invalid post.');
    }
    
    // Add nonce for security
    $nonce = wp_create_nonce('cms_vote_' . $post_id . '_' . $user_id);
    
    $html ='<div class="cms-votting-buttons" data-nonce="' . esc_attr($nonce) . '">';
    
    $html .= sprintf(
        '<button class="cms-like" data-post-id="%d" data-user-id="%d" data-vote-type="like">%s</button>',
        esc_attr($post_id),
        esc_attr($user_id),
        esc_html($like_label)
    );
    
    $html .= sprintf(
        '<button class="cms-dislike" data-post-id="%d" data-user-id="%d" data-vote-type="dislike">%s</button>',
        esc_attr($post_id),
        esc_attr($user_id),
        esc_html($dislike_label)
    );
    
    $html .= '</div>';
    
    return $html;
}
add_shortcode( 'VOTING_BUTTONS', 'CMS_post_voting_buttons' );