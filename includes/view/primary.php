
<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) 
	{
		exit;
	}

    /**
	    * @package sociopress-wp-plugin
      * @version 0.1.0
    */

      #region for pasabay registration post type

      /*
      * Adding a menu to contain the custom post types for frontpage
      */

    // Front Page for custom post type
    function frontpage_admin_menu() {
      add_menu_page('Feeds','Feeds','read','front-sections','','dashicons-admin-home',4
      );
    }
    add_action( 'admin_menu', 'frontpage_admin_menu' );

      /*
        * Creating a Custom Post type for Features Section
      */

      function register_cpt_features() {

      // Set UI labels for Custom Post Type
          $labels_sell = array(
            'name'                => _x( 'Sell', 'Post Type General Name', 'sell' ),
            'singular_name'       => _x( 'Sell', 'Post Type Singular Name', 'sell' ),
            'menu_name'           => __( 'Sell', 'sell' ),
            'parent_item_colon'   => __( 'Parent Sell', 'sell' ),
            'all_items'           => __( 'Seller', 'sell' ),
            'view_item'           => __( 'View Sell', 'sell' ),
            'add_new_item'        => __( 'Add New Sell', 'sell' ),
            'add_new'             => __( 'Add New', 'sell' ),
            'edit_item'           => __( 'Edit', 'sell' ),
            'update_item'         => __( 'Update', 'sell' ),
            'search_items'        => __( 'Search', 'sell' ),
            'not_found'           => __( 'Not Found', 'sell' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'sell' ),
          );

          $labels_pasabay = array(
            'name'                => _x( 'Pasabay', 'Post Type General Name', 'pasabay' ),
            'singular_name'       => _x( 'Pasabay', 'Post Type Singular Name', 'pasabay' ),
            'menu_name'           => __( 'Pasabay', 'pasabay' ),
            'parent_item_colon'   => __( 'Parent Pasabay', 'pasabay' ),
            'all_items'           => __( 'Pasabay', 'pasabay' ),
            'view_item'           => __( 'View Pasabay', 'pasabay' ),
            'add_new_item'        => __( 'Add New Pasabay', 'pasabay' ),
            'add_new'             => __( 'Add New', 'pasabay' ),
            'edit_item'           => __( 'Edit', 'pasabay' ),
            'update_item'         => __( 'Update', 'pasabay' ),
            'search_items'        => __( 'Search', 'pasabay' ),
            'not_found'           => __( 'Not Found', 'pasabay' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'pasabay' ),
          );

          $labels_status = array(
            'name'                => _x( 'Status', 'Post Type General Name', 'status' ),
            'singular_name'       => _x( 'Status', 'Post Type Singular Name', 'status' ),
            'menu_name'           => __( 'Status', 'status' ),
            'parent_item_colon'   => __( 'Parent Status', 'status' ),
            'all_items'           => __( 'Status', 'status' ),
            'view_item'           => __( 'View Status', 'status' ),
            'add_new_item'        => __( 'Add New Status', 'status' ),
            'add_new'             => __( 'Add New', 'status' ),
            'edit_item'           => __( 'Edit', 'status' ),
            'update_item'         => __( 'Update', 'status' ),
            'search_items'        => __( 'Search', 'status' ),
            'not_found'           => __( 'Not Found', 'status' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'status' ),
          );

          $labels_pahatid = array(
            'name'                => _x( 'Pahatid', 'Post Type General Name', 'pahatid' ),
            'singular_name'       => _x( 'Pahatid', 'Post Type Singular Name', 'pahatid' ),
            'menu_name'           => __( 'Pahatid', 'pahatid' ),
            'parent_item_colon'   => __( 'Parent Pahatid', 'pahatid' ),
            'all_items'           => __( 'Pahatid', 'pahatid' ),
            'view_item'           => __( 'View Pahatid', 'pahatid' ),
            'add_new_item'        => __( 'Add New Pahatid', 'pahatid' ),
            'add_new'             => __( 'Add New', 'pahatid' ),
            'edit_item'           => __( 'Edit', 'pahatid' ),
            'update_item'         => __( 'Update', 'pahatid' ),
            'search_items'        => __( 'Search', 'pahatid' ),
            'not_found'           => __( 'Not Found', 'pahatid' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'pahatid' ),
          );

          $labels_pabili = array(
            'name'                => _x( 'Pabili', 'Post Type General Name', 'pabili' ),
            'singular_name'       => _x( 'Pabili', 'Post Type Singular Name', 'pabili' ),
            'menu_name'           => __( 'Pabili', 'pabili' ),
            'parent_item_colon'   => __( 'Parent Pabili', 'pabili' ),
            'all_items'           => __( 'Pabili', 'pabili' ),
            'view_item'           => __( 'View Pabili', 'pabili' ),
            'add_new_item'        => __( 'Add New Pabili', 'pabili' ),
            'add_new'             => __( 'Add New', 'pabili' ),
            'edit_item'           => __( 'Edit', 'pabili' ),
            'update_item'         => __( 'Update', 'pabili' ),
            'search_items'        => __( 'Search', 'pabili' ),
            'not_found'           => __( 'Not Found', 'pabili' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'pabili' ),
          );

          $labels_pasakay = array(
            'name'                => _x( 'Pasakay', 'Post Type General Name', 'pasakay' ),
            'singular_name'       => _x( 'Pasakay', 'Post Type Singular Name', 'pasakay' ),
            'menu_name'           => __( 'Pasakay', 'pasakay' ),
            'parent_item_colon'   => __( 'Parent Pasakay', 'pasakay' ),
            'all_items'           => __( 'Pasakay', 'pasakay' ),
            'view_item'           => __( 'View Pasakay', 'pasakay' ),
            'add_new_item'        => __( 'Add New Pasakay', 'pasakay' ),
            'add_new'             => __( 'Add New', 'pasakay' ),
            'edit_item'           => __( 'Edit', 'pasakay' ),
            'update_item'         => __( 'Update', 'pasakay' ),
            'search_items'        => __( 'Search', 'pasakay' ),
            'not_found'           => __( 'Not Found', 'pasakay' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'pasakay' ),
          );

          $args_sell = array(
            'label'               => __( 'sell', 'sell' ),
            'description'         => __( 'Sell', 'sell' ),
            'labels'              => $labels_sell,
            'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => 'front-sections',
            
            // This is where we add taxonomies to our CPT
            'taxonomies'          => array( 'category' ),
          );

          $args_pasabay = array(
            'label'               => __( 'pasabay', 'pasabay' ),
            'description'         => __( 'Pasabay', 'pasabay' ),
            'labels'              => $labels_pasabay,
            'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => 'front-sections',
            
            // This is where we add taxonomies to our CPT
            'taxonomies'          => array( 'category' ),
          );

          $args_status = array(
            'label'               => __( 'status', 'status' ),
            'description'         => __( 'Status', 'status' ),
            'labels'              => $labels_status,
            'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => 'front-sections',
            
            // This is where we add taxonomies to our CPT
            'taxonomies'          => array( 'category' ),
          );

          $args_pahatid = array(
            'label'               => __( 'pahatid', 'pahatid' ),
            'description'         => __( 'Pahatid', 'pahatid' ),
            'labels'              => $labels_pahatid,
            'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => 'front-sections',
            
            // This is where we add taxonomies to our CPT
            'taxonomies'          => array( 'category' ),
          );

          $args_pabili = array(
            'label'               => __( 'pabili', 'pabili' ),
            'description'         => __( 'Pabili', 'pabili' ),
            'labels'              => $labels_pabili,
            'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => 'front-sections',
            
            // This is where we add taxonomies to our CPT
            'taxonomies'          => array( 'category' ),
          );

          $args_pasakay = array(
            'label'               => __( 'pasakay', 'pasakay' ),
            'description'         => __( 'Pasakay', 'pasakay' ),
            'labels'              => $labels_pasakay,
            'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => 'front-sections',
            
            // This is where we add taxonomies to our CPT
            'taxonomies'          => array( 'category' ),
          );
          
          register_post_type( 'status', $args_status );
          register_post_type( 'pasabay', $args_pasabay );
          register_post_type( 'pabili', $args_pabili );
          register_post_type( 'pahatid', $args_pahatid );
          register_post_type( 'pasakay', $args_pasakay );
          register_post_type( 'sell', $args_sell );
    }
    add_action( 'init', 'register_cpt_features' );
    #endregion
