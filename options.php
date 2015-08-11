<?php
//create custom plugin options menu
add_action('admin_menu', 'feed_options');

function feed_options(){
    //creates a top level menu
    add_menu_page('WP Feed Aggregator Options',
                  'Feed Aggregator Options',
                  'manage_options',
                  'wpfa-options',
                  'generate_page'
                );
    //register settings
    add_action('admin_init','register_options');
}

//defines attributes to be saved
function register_options(){
    //register settings to be saved
    register_setting('settings-group','option-name');
}

//HTML to generate page with forms, buttons etc.
function generate_page(){
    <div class="wrap">
    <h2>"WP Feed Aggregator Options"</h2>
}

 ?>