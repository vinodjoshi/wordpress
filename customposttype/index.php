<?php
/*
Plugin Name: Custom Post Type
Description: Declares a plugin that will create a custom post type.
Version: 1.0
*/
//Custom Post Type
add_action('init', 'myplugin_custom_post_type');
function myplugin_custom_post_type() {
	register_post_type('my_custom_post_type',
        array(
            'labels' => array(
                'name' => 'Custom Post Type',
                'singular_name' => 'Custom Post Type',
                'add_new' => 'Add New',
                'add_new_item' => 'Add New Post',
                'edit' => 'Edit',
                'edit_item' => 'Edit Custom Post',
                'new_item' => 'New Custom Post',
                'view' => 'View',
                'view_item' => 'View Custom Post',
                'search_items' => 'Search Custom Post',
                'not_found' => 'No Custom Post found',
                'not_found_in_trash' => 'No Custom Post found in Trash',
                'parent' => 'Parent Custom Post'
            ),
 
            'public' => true,
            'menu_position' => 15,
            'supports' => array( 'title', 'editor', 'comments', 'thumbnail', 'custom-fields' ),
            'taxonomies' => array( '' ),
            'menu_icon' => plugins_url( 'image.png', __FILE__ ),
            'has_archive' => true
        )
    );
}


//Registering the Custom Function
add_action('admin_init', 'my_admin');
function my_admin() {
    add_meta_box('custom_post_meta_box',
        'Custom Post Details',
        'display_custom_post_meta_box',
        'custom_post', 'normal', 'high'
    );
}

?>