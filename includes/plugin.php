<?php

class CMS_Plugin{
    public function __construct(){
        add_action( 'admin_menu', [$this, 'cms_plugin_menu'] );
    }

    
public function cms_plugin_menu() {
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

}
new CMS_Plugin();