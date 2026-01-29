<?php 
// ACTIONS

function cms_footer_text(){
    echo 'Copyrights &copy; 2026. CMS Plugin';
}
add_action( 'wp_footer', 'cms_footer_text');

//FILTERS

function cms_post_title($title){
    $emoji = '🤍';
    return $emoji . $title;
}
add_filter('the_title', 'cms_post_title');