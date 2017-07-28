<?php
/*
Plugin Name: WordPress mobile app REST API
Description: Easily Using the WordPress REST API in a mobile app
Author: Ibrahim Mohamed Abotaleb
Version: 1.0
Author URI: http://mrkindy.com/
Text Domain: tl-json
Domain Path: /languages
*/
/**
 * Posts API.
 */
require 'api/posts.php';
/**
 * Function to register Posts API.
 */
function tl_register_post_rest_routes() {
    $controller = new Tech_Labs_Posts_Controller();
    $controller->register_routes();
}
add_action( 'rest_api_init', 'tl_register_post_rest_routes' );

/**
 * Users API.
 */
require 'api/users.php';
/**
 * Function to register Posts API.
 */
function tl_register_users_rest_routes() {
    $controller = new Tech_Labs_Users_Controller();
    $controller->register_routes();
}
add_action( 'rest_api_init', 'tl_register_users_rest_routes' );

/**
 * Categories API.
 */
require 'api/categories.php';
/**
 * Function to register Posts API.
 */
function tl_register_categories_rest_routes() {
    $controller = new Tech_Labs_Categories_Controller();
    $controller->register_routes();
}
add_action( 'rest_api_init', 'tl_register_categories_rest_routes' );

/**
 * Comments API.
 */
require 'api/comments.php';
/**
 * Function to register Posts API.
 */
function tl_register_comments_rest_routes() {
    $controller = new Tech_Labs_Comments_Controller();
    $controller->register_routes();
}
add_action( 'rest_api_init', 'tl_register_comments_rest_routes' );

require 'api/menus.php';
/**
 * Function to register Menus API.
 */
function tl_register_menus_rest_routes() {
    $controller = new Tech_Labs_Menus_Controller();
    $controller->register_routes();
}
add_action( 'rest_api_init', 'tl_register_menus_rest_routes' );

/**
 * Register Mobile Menu.
 */
register_nav_menu( 'Mobile', __( 'Mobile Menu', 'tl-json' ) );