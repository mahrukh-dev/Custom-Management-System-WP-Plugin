<?php 

// Register Custom Taxonomy - same as categories, can classify our cpts
function register_cms_project_industry_taxonomy() {
    $labels = array(
        'name'                       => _x('Industries', 'Taxonomy General Name', 'custom-management-sys'),
        'singular_name'              => _x('Industry', 'Taxonomy Singular Name', 'custom-management-sys'),
        'menu_name'                  => __('Industries', 'custom-management-sys'),
        'all_items'                  => __('All Industries', 'custom-management-sys'),
        'parent_item'                => __('Parent Industry', 'custom-management-sys'),
        'parent_item_colon'          => __('Parent Industry:', 'custom-management-sys'),
        'new_item_name'              => __('New Industry Name', 'custom-management-sys'),
        'add_new_item'               => __('Add New Industry', 'custom-management-sys'),
        'edit_item'                  => __('Edit Industry', 'custom-management-sys'),
        'update_item'                => __('Update Industry', 'custom-management-sys'),
        'view_item'                  => __('View Industry', 'custom-management-sys'),
        'search_items'               => __('Search Industries', 'custom-management-sys'),
    );

    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'publicly_queryable'         => true,
        'show_ui'                    => true,
        'show_in_menu'               => true,
        'show_in_nav_menus'          => true,
        'show_in_rest'               => true,
        'rest_base'                  => 'cms_project_industry',
        'show_tagcloud'              => true,
        'show_in_quick_edit'         => true,
        'show_admin_column'          => true,
    );

    register_taxonomy('cms_project_industry', ["projects"], $args);
}
add_action('init', 'register_cms_project_industry_taxonomy', 0);


// Register Custom Taxonomy
function register_cms_project_technology_taxonomy() {
    $labels = array(
        'name'                       => _x('Technologies', 'Taxonomy General Name', 'custom-management-sys'),
        'singular_name'              => _x('Technology', 'Taxonomy Singular Name', 'custom-management-sys'),
        'menu_name'                  => __('Technologies', 'custom-management-sys'),
        'all_items'                  => __('All Technologies', 'custom-management-sys'),
        'parent_item'                => __('Parent Technology', 'custom-management-sys'),
        'parent_item_colon'          => __('Parent Technology:', 'custom-management-sys'),
        'new_item_name'              => __('New Technology Name', 'custom-management-sys'),
        'add_new_item'               => __('Add New Technology', 'custom-management-sys'),
        'edit_item'                  => __('Edit Technology', 'custom-management-sys'),
        'update_item'                => __('Update Technology', 'custom-management-sys'),
        'view_item'                  => __('View Technology', 'custom-management-sys'),
        'search_items'               => __('Search Technologies', 'custom-management-sys'),
    );

    $args = array(
        'labels'                     => $labels,
        'hierarchical'               => true,
        'public'                     => true,
        'publicly_queryable'         => true,
        'show_ui'                    => true,
        'show_in_menu'               => true,
        'show_in_nav_menus'          => true,
        'show_in_rest'               => true,
        'rest_base'                  => 'cms_project_technology',
        'show_tagcloud'              => true,
        'show_in_quick_edit'         => true,
        'show_admin_column'          => true,
    );

    register_taxonomy('cms_project_technology', ["projects"], $args);
}
add_action('init', 'register_cms_project_technology_taxonomy', 0);