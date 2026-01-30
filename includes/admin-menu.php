<?php 


function cms_plugin_menu() {
    add_menu_page(
        'CMS MENU',
        'CMS',
        'manage_options',
        'cms-plugin',
        'cms_options_page_html',
        'dashicons-superhero',
        10,
    );
    add_submenu_page(
        'cms-plugin',
        'CMS Subpage',
        'Submenu',
        'manage_options',
        'sub-cms',
        'cms_options_subpage_html',
        
    );
}

add_action( 'admin_menu', 'cms_plugin_menu' );