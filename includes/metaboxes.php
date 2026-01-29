<?php 
//metaboxes

// Register Meta Box
function add_cms_project_meta_info_meta_box() {
    add_meta_box(
        'cms_project_meta_info',
        'Project Meta Info',
        'cms_project_meta_info_meta_box_callback',
        ["projects"],
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'add_cms_project_meta_info_meta_box');

// Meta Box Callback
function cms_project_meta_info_meta_box_callback($post) {
    wp_nonce_field('cms_project_meta_info_meta_box', 'cms_project_meta_info_meta_box_nonce');
    $values = get_post_meta($post->ID);
    ?>
    <div class="meta-box-container">
        
        <div class="meta-box-field">
            <label for="project_url">Project URL</label>
            <input
                type="url"
                id="project_url"
                name="project_url"
                value="<?php echo esc_attr(isset($values['project_url'][0]) ? $values['project_url'][0] : ''); ?>"
            />
        </div>
        
        <div class="meta-box-field">
            <label for="project_completion_duration">Compleded In</label>
            <input
                type="textarea"
                id="project_completion_duration"
                name="project_completion_duration"
                value="<?php echo esc_attr(isset($values['project_completion_duration'][0]) ? $values['project_completion_duration'][0] : ''); ?>"
            />
        </div>
        
        <div class="meta-box-field">
            <label for="project_estimated_cost">Development Cost</label>
            <input
                type="text"
                id="project_estimated_cost"
                name="project_estimated_cost"
                value="<?php echo esc_attr(isset($values['project_estimated_cost'][0]) ? $values['project_estimated_cost'][0] : ''); ?>"
            />
        </div>
    </div>
    <?php
}

// Save Meta Box Data
function save_cms_project_meta_info_meta_box_data($post_id) {
    if (!isset($_POST['cms_project_meta_info_meta_box_nonce'])) {
        return;
    }
    if (!wp_verify_nonce($_POST['cms_project_meta_info_meta_box_nonce'], 'cms_project_meta_info_meta_box')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    
    if (isset($_POST['project_url'])) {
        update_post_meta($post_id, 'project_url', sanitize_text_field($_POST['project_url']));
    }
    
    if (isset($_POST['project_completion_duration'])) {
        update_post_meta($post_id, 'project_completion_duration', sanitize_text_field($_POST['project_completion_duration']));
    }
    
    if (isset($_POST['project_estimated_cost'])) {
        update_post_meta($post_id, 'project_estimated_cost', sanitize_text_field($_POST['project_estimated_cost']));
    }
}
add_action('save_post', 'save_cms_project_meta_info_meta_box_data');