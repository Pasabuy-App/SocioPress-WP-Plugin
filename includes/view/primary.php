
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
    function my_custom_post_product() {
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
          'menu_name'          => 'Move'
        );
        $args = array(
          'labels'        => $labels,
          'description'   => 'Holds our mve and sell post data',
          'public'        => true,
          'menu_position' => 5,
          'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments' ),
          'has_archive'   => true,
        );
        register_post_type( 'move', $args ); 
        //register_post_type( 'sell', $args ); 
      }
      add_action( 'init', 'my_custom_post_product' );
      #endregion
