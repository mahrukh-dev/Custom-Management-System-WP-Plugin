<?php 

function cms_post_voting_callback() {
    global $wpdb;
    
    // Verify nonce for security
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'cms_vote_' . $_POST['pid'] . '_' . $_POST['uid'])) {
        wp_send_json_error(['message' => 'Security check failed.']);
        wp_die();
    }
    
    // Check if user is logged in
    if (!is_user_logged_in()) {
        wp_send_json_error(['message' => 'You must be logged in to vote.']);
        wp_die();
    }
    
    // Validate and sanitize POST data
    $post_id = isset($_POST["pid"]) ? intval($_POST["pid"]) : 0;
    $user_id = isset($_POST["uid"]) ? intval($_POST["uid"]) : 0;
    $reaction_type = isset($_POST["reaction_type"]) ? sanitize_text_field($_POST["reaction_type"]) : '';
    
    // Validate required fields
    if(empty($post_id) || empty($user_id) || empty($reaction_type)) {
        wp_send_json_error(['message' => 'Missing required fields.']);
        wp_die();
    }
    
    // Validate reaction type
    $allowed_reactions = array('like', 'dislike');
    if(!in_array($reaction_type, $allowed_reactions)) {
        wp_send_json_error(['message' => 'Invalid reaction type.']);
        wp_die();
    }
    
    // Verify the post exists and is public
    $post = get_post($post_id);
    if(!$post || !is_post_publicly_viewable($post_id)) {
        wp_send_json_error(['message' => 'Invalid post.']);
        wp_die();
    }
    
    // Verify the current user matches the provided user ID
    $current_user_id = get_current_user_id();
    if($current_user_id !== $user_id) {
        wp_send_json_error(['message' => 'User ID mismatch.']);
        wp_die();
    }
    
    // Check if user has already voted on this post
    $table_name = esc_sql($wpdb->prefix . 'reactions');
    
    $existing_vote = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$table_name} WHERE post_id = %d AND user_id = %d",
        $post_id,
        $user_id
    ));
    
    if($existing_vote) {
        // Update existing vote
        $query = $wpdb->update(
            $table_name,
            array(
                'reaction_type' => $reaction_type,
                'updated_at' => current_time('mysql')
            ),
            array(
                'post_id' => $post_id,
                'user_id' => $user_id
            ),
            array(
                '%s',
                '%s'
            ),
            array(
                '%d',
                '%d'
            )
        );
        
        if($query !== false) {
            wp_send_json_success(['message' => 'Your vote has been updated.']);
        } else {
            wp_send_json_error(['message' => 'Failed to update your vote.']);
        }
    } else {
        // Insert new vote
        $query = $wpdb->insert(
            $table_name,
            array(
                'post_id' => $post_id,
                'user_id' => $user_id,
                'reaction_type' => $reaction_type,
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            ),
            array(
                '%d',
                '%d',    
                '%s',
                '%s',
                '%s'
            )
        );
        
        if($query) {
            wp_send_json_success(['message' => 'Your vote has been recorded.']);
        } else {
            // Log error for debugging (not shown to user)
            if(defined('WP_DEBUG') && WP_DEBUG) {
                error_log('Vote insertion failed: ' . $wpdb->last_error);
            }
            wp_send_json_error(['message' => 'Failed to record your vote.']);
        }
    }
    
    wp_die();
}
add_action('wp_ajax_cms_post_voting', 'cms_post_voting_callback');
add_action('wp_ajax_nopriv_cms_post_voting', 'cms_post_voting_callback');
