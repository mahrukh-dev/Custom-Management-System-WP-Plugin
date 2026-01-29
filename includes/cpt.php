<?php 

// Register Custom Post Type - same as posts
function cms_register_projects_post_type() {
    $labels = array(
        'name'                  => _x('Projects', 'Post Type General Name', 'custom-management-sys'),
        'singular_name'         => _x('Project', 'Post Type Singular Name', 'custom-management-sys'),
        'menu_name'            => __('Projects', 'custom-management-sys'),
        'all_items'            => __('All Projects', 'custom-management-sys'),
        'add_new_item'         => __('Add New Project', 'custom-management-sys'),
        'add_new'              => __('Add New', 'custom-management-sys'),
        'edit_item'            => __('Edit Project', 'custom-management-sys'),
        'update_item'          => __('Update Project', 'custom-management-sys'),
        'search_items'         => __('Search Project', 'custom-management-sys'),
    );

    $args = array(
        'label'                 => __('Project', 'custom-management-sys'),
        'labels'                => $labels,
        'supports'              => ["title","editor","thumbnail","excerpt","author","comments"],
        'hierarchical'          => false,
        'public'                => true,
        'show_ui'               => true,
        'show_in_menu'          => true,
        'menu_icon'             => 'dashicons-open-folder',
        'show_in_admin_bar'     => true,
        'show_in_nav_menus'     => true,
        'can_export'            => true,
        'has_archive'           => true,
        'exclude_from_search'   => false,
        'publicly_queryable'    => true,
        'capability_type'       => 'post',
        'show_in_rest' => true,
    );

    register_post_type('projects', $args);
}
add_action('init', 'cms_register_projects_post_type', 0);