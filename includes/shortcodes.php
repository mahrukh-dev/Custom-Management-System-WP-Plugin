<?php

// basic shortcode
function cms_text_shortcode()
{
    return 'THIS is a test shortcode';
}
add_shortcode('CMS_TEXT', 'cms_text_shortcode');

//enclosing shortcode
function cms_enclosing_shortcode($atts = array(), $content)
{

    $html = '<a href="">';
    $html .= $content;
    $html .= "</a>";
    return $html;
}
add_shortcode('CMS_ENCLOSING', 'cms_enclosing_shortcode');

// shortcode with params
function cms_params_shortcode($atts = array(), $content)
{
    $atts = shortcode_atts( 
        array(
            'label'=> 'Button Label',
            'link' => 'www.google.com',
        ), $atts
    );
    $html = '<a href="'.$atts['link'].'">';
    $html .= $atts['label'];
    $html .= "</a>";
    return $html;
}
add_shortcode('CMS_PARAMS', 'cms_params_shortcode');


// Shortcodes with Parameters from MagicWP
function CMS_test_params_magicwp_shortcode($atts) {
    if(!empty($atts)) {
        // Extract and merge attributes with defaults
        $atts = shortcode_atts(array(
            'label' => 'Button Label',
            'link' => 'https://wp-plugin.test/projects/e-commerce-website-development/'
        ), $atts, 'CMS_TEST_PARAM_MAGICWP');
    } else {
        $atts = NULL;
    }
    

    // Start output buffering
    ob_start();

    // Your shortcode logic here
    ?>
    <div class="CMS-button-shortcode">
        <?php if(!empty($atts)) { ?>
        <a href="<?php echo $atts['link'] ?>" style="padding: 10px; background-color: blue; color: white"><?php echo $atts['label'] ?></a>
        <?php } else { ?>
            Please assign link and label 
        <?php } ?>
    </div>
    <?php

    // Return the buffered content
    return ob_get_clean();
}
add_shortcode('CMS_TEST_PARAM_MAGICWP', 'CMS_test_params_magicwp_shortcode');
// Usage
// [CMS_TEST_PARAM_MAGICWP label="Extended Button" link="https://wp-plugin.test/"]

/**
 * Project Meta Information
 */
function CMS_project_meta_shortcode($atts) {

    $attrs = shortcode_atts(
		array(
			'id' => get_the_ID(),
		), $atts, 'PROJECT_META'
	);

    $project_url = get_post_meta( $attrs['id'], 'project_url', true );
    $project_completion = get_post_meta( $attrs['id'], 'project_completion_duration', true );
    $project_cost = get_post_meta( $attrs['id'], 'project_estimated_cost', true );

    $html = '<div class="project-meta">';
        $html .= '<span><a href="'.$project_url.'" target="_blank">Visit Project</a></span>';
        $html .= '<span>'.$project_completion.'</span>';
        $html .= '<span>'.$project_cost.'</span>';
    $html .= '</div>';

    return $html;


}
add_shortcode( 'PROJECT_META', 'CMS_project_meta_shortcode' );

function CMS_post_voting_buttons($atts) {
    $attrs = shortcode_atts(
        array(
            'like' => 'Like',
            'dislike' => 'Dislike',
        ), $atts, 'PROJECT_META'
    );
    $post_id = get_the_ID();
    $user_id = get_current_user_id();
    $html ='<div class="cms-votting-buttons">';
    $html .= sprintf(
        '<button class="cms-like" data-post-id="%s" data-user-id="%s">%s</button>',
        esc_attr( $post_id),
        esc_attr( $user_id ),
        esc_html($attrs['like'])
    );
    $html .= sprintf(
        '<button class="cms-dislike" data-post-id="%s" data-user-id="%s">%s</button>',
        esc_attr( $post_id),
        esc_attr( $user_id ),
        esc_html($attrs['dislike'])
    );
    $html .= '</div>';
    return $html;
}
add_shortcode( 'VOTING_BUTTONS', 'CMS_post_voting_buttons' );