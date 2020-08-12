
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
    /*function my_custom_post_types() {
        $labels = array(
          'name'               => _x( 'Post', 'post type general name' ),
          'singular_name'      => _x( 'Post', 'post type singular name' ),
          'add_new'            => _x( 'Add New', 'post' ),
          'add_new_item'       => __( 'Add New Post' ),
          'edit_item'          => __( 'Edit Post' ),
          'new_item'           => __( 'New Post' ),
          'all_items'          => __( 'All Post' ),
          'view_item'          => __( 'View Post' ),
          'search_items'       => __( 'Search Post' ),
          'not_found'          => __( 'No post found' ),
          'not_found_in_trash' => __( 'No post found in the Trash' ), 
          'parent_item_colon'  => '’',
          'menu_name'          => 'New Post'
        );
        $args = array(
          'labels'        => $labels,
          'description'   => 'Holds our move and sell post data',
          'public'        => true,
          'menu_position' => 5,
          'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments' ),
          'has_archive'   => true,
          'delete_with_user'   => true,
        );
        register_post_type( 'move', $args ); 
        //register_post_type( 'sell', $args ); 
      }
      add_action( 'init', 'my_custom_post_types' );*/

      /* working for insert, update and delete 
      function custom_post_type() {
 
        // Set UI labels for Custom Post Type
            $labels = array(
                'name'                => _x( 'Move', 'Post Type General Name', 'move' ),
                'singular_name'       => _x( 'Move', 'Post Type Singular Name', 'move' ),
                'menu_name'           => __( 'Move', 'move' ),
                'parent_item_colon'   => __( 'Parent Move', 'move' ),
                'all_items'           => __( 'All Move', 'move' ),
                'view_item'           => __( 'View Move', 'move' ),
                'add_new_item'        => __( 'Add New Move', 'move' ),
                'add_new'             => __( 'Add New', 'move' ),
                'edit_item'           => __( 'Edit Move', 'move' ),
                'update_item'         => __( 'Update Move', 'move' ),
                'search_items'        => __( 'Search Move', 'move' ),
                'not_found'           => __( 'Not Found', 'move' ),
                'not_found_in_trash'  => __( 'Not found in Trash', 'move' ),
            );
             
        // Set other options for Custom Post Type
             
            $args = array(
                'label'               => __( 'move', 'move' ),
                'description'         => __( 'Move', 'move' ),
                'labels'              => $labels,
                'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
                'hierarchical'        => false,
                'public'              => true,
                'show_ui'             => true,
                'show_in_menu'        => true,
                'show_in_nav_menus'   => true,
                'show_in_admin_bar'   => true,
                'menu_position'       => 5,
                'can_export'          => true,
                'has_archive'         => true,
                'exclude_from_search' => false,
                'publicly_queryable'  => true,
                'capability_type'     => 'post',
                 
                // This is where we add taxonomies to our CPT
                'taxonomies'          => array( 'category' ),
            );
             
            // Registering your Custom Post Type
            register_post_type( 'move', $args );
            register_post_type( 'sell', $args );
         
        }
         
        /* Hook into the 'init' action so that the function
        * Containing our post type registration is not 
        * unnecessarily executed. 
        */
         
       /* add_action( 'init', 'custom_post_type', 0 );

        add_filter('pre_get_posts', 'query_post_type');
          function query_post_type($query) {
            if( is_category() ) {
              $post_type = get_query_var('post_type');
              if($post_type)
                  $post_type = $post_type;
              else
                  $post_type = array('nav_menu_item', 'post', 'movies'); // don't forget nav_menu_item to allow menus to work!
              $query->set('post_type',$post_type);
              return $query;
              }
          }

          /*
          * Adding a menu to contain the custom post types for frontpage
          */

          // Front Page for custom post type
          function frontpage_admin_menu() {

            add_menu_page('New Post','New Post','read','front-sections','','dashicons-admin-home',4
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
