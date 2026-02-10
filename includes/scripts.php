<?php


function cms_plugin_admin_scripts(){
    wp_enqueue_style( 'cms-plugin-admin-css', CMS_PLUGIN_DIR_URL.'assets/css/admin.css', '', CMS_PLUGIN_VERSION );
    wp_enqueue_script( 'cms-plugin-admin-js', CMS_PLUGIN_DIR_URL.'assets/js/admin.js', '', CMS_PLUGIN_VERSION, true );

}
add_action( 'admin_enqueue_scripts', 'cms_plugin_admin_scripts' );

function cms_plugin_public_scripts(){
    wp_enqueue_style( 'cms-plugin-public-css', CMS_PLUGIN_DIR_URL.'assets/css/public.css', '', CMS_PLUGIN_VERSION );
    wp_enqueue_script( 'cms-plugin-public-js', CMS_PLUGIN_DIR_URL.'assets/js/public.js', '', CMS_PLUGIN_VERSION, true );
    wp_enqueue_script( 'cms-plugin-ajax-js', CMS_PLUGIN_DIR_URL.'assets/js/ajax.js', ['jquery'], CMS_PLUGIN_VERSION, true );
    wp_localize_script( 'cms-plugin-ajax-js', 'cms_ajax', ['ajax_url' => admin_url('admin-ajax.php')] );
}
add_action( 'wp_enqueue_scripts', 'cms_plugin_public_scripts' );