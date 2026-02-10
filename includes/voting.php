<?php 

function cms_post_voting_callback() {
    global $wpdb;

    $post_id = intval($_POST["pid"]);
    $user_id = intval($_POST["uid"]);
    $table_name = $wpdb->prefix . 'reactions';
    
    if(!empty($post_id) && !empty($user_id)) {
        $query = $wpdb->insert(
                $table_name,
                array(
                    'post_id' => $post_id,
                    'user_id' => $user_id,
                    'reaction_type' => 'like'
                ),
                array(
                    '%d',
                    '%d',    
                    '%s'
                )
            );
        if( $query ) {
            wp_send_json_success( ['message' => 'Your vote has been recorded']);
        }
        else {
            wp_send_json_success( ['message'=> 'There has been an error'.$wpdb->print_error()]);
        }

    }
    
    wp_die();
}
add_action( 'wp_ajax_cms_post_voting', 'cms_post_voting_callback');
add_action( 'wp_ajax_nopriv_cms_post_voting', 'cms_post_voting_callback');