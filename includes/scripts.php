<?php


function cms_plugin_admin_scripts(){
    wp_enqueue_style( 'cms-plugin-admin-css', CMS_PLUGIN_DIR_URL.'admin/css/admin.css', '', CMS_PLUGIN_VERSION );
    wp_enqueue_script( 'cms-plugin-admin-js', CMS_PLUGIN_DIR_URL.'admin/js/admin.js', '', CMS_PLUGIN_VERSION, true );

}
add_action( 'admin_enqueue_scripts', 'cms_plugin_admin_scripts' );

function cms_plugin_public_scripts(){
    wp_enqueue_style( 'cms-plugin-public-css', CMS_PLUGIN_DIR_URL.'public/css/public.css', '', CMS_PLUGIN_VERSION );
    wp_enqueue_script( 'cms-plugin-public-js', CMS_PLUGIN_DIR_URL.'public/js/public.js', '', CMS_PLUGIN_VERSION, true );

}
add_action( 'wp_enqueue_scripts', 'cms_plugin_public_scripts' );

