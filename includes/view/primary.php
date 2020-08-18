
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

    #region for move registration post type
      /*
      * Adding a menu to contain the custom post types for frontpage
      */

      // Front Page for custom post type
      function frontpage_admin_menu() {
        add_menu_page('Feeds','Feeds','read','front-sections','','dashicons-admin-home',4
        ;
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
            'all_items'           => __( 'Sell Post', 'sell' ),
            'view_item'           => __( 'View Sell', 'sell' ),
            'add_new_item'        => __( 'Add New Sell', 'sell' ),
            'add_new'             => __( 'Add New', 'sell' ),
            'edit_item'           => __( 'Edit', 'sell' ),
            'update_item'         => __( 'Update', 'sell' ),
            'search_items'        => __( 'Search', 'sell' ),
            'not_found'           => __( 'Not Found', 'sell' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'sell' ),
          );

          $labels_move = array(
            'name'                => _x( 'Move', 'Post Type General Name', 'move' ),
            'singular_name'       => _x( 'Move', 'Post Type Singular Name', 'move' ),
            'menu_name'           => __( 'Move', 'move' ),
            'parent_item_colon'   => __( 'Parent Move', 'move' ),
            'all_items'           => __( 'Move Post', 'move' ),
            'view_item'           => __( 'View Move', 'move' ),
            'add_new_item'        => __( 'Add New Move', 'move' ),
            'add_new'             => __( 'Add New', 'move' ),
            'edit_item'           => __( 'Edit', 'move' ),
            'update_item'         => __( 'Update', 'move' ),
            'search_items'        => __( 'Search', 'move' ),
            'not_found'           => __( 'Not Found', 'move' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'move' ),
          );

          $labels_status = array(
            'name'                => _x( 'Status', 'Post Type General Name', 'status' ),
            'singular_name'       => _x( 'Status', 'Post Type Singular Name', 'status' ),
            'menu_name'           => __( 'Status', 'status' ),
            'parent_item_colon'   => __( 'Parent Status', 'status' ),
            'all_items'           => __( 'Status Post', 'status' ),
            'view_item'           => __( 'View Status', 'status' ),
            'add_new_item'        => __( 'Add New Status', 'status' ),
            'add_new'             => __( 'Add New', 'status' ),
            'edit_item'           => __( 'Edit', 'status' ),
            'update_item'         => __( 'Update', 'status' ),
            'search_items'        => __( 'Search', 'status' ),
            'not_found'           => __( 'Not Found', 'status' ),
            'not_found_in_trash'  => __( 'Not found in Trash', 'status' ),
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

          $args_move = array(
            'label'               => __( 'move', 'move' ),
            'description'         => __( 'Move', 'move' ),
            'labels'              => $labels_move,
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
          
          register_post_type( 'move', $args_move );
          register_post_type( 'sell', $args_sell );
          register_post_type( 'status', $args_status );
    }
          
          add_action( 'init', 'register_cpt_features' );

    #endregion
