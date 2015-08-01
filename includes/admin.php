<?php

function root_admin_scripts() {
    wp_enqueue_style('root-admin-css', get_template_directory_uri() . '/assets/css/admin.css');
    wp_enqueue_script('root-admin-js', get_template_directory_uri() . '/assets/js/admin.js');
}

add_action('admin_enqueue_scripts', 'root_admin_scripts');
