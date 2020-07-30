<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/** 
        * @package sociopress-wp-plugin
		* @version 0.1.0
		* This is the primary gateway of all the rest api request.
	*/
  	class SP_Posts {
         
        public static function filter_posts(){
            
            // Initialize WP global variable
            global $wpdb;
			
			//Validate user session
			$result = SP_Globals::validate_user();
			
			if ($result !== true) {
				return $result;
			}
			
			//Check if post type filter is set
			if (!isset($_POST["pt"])) {
				return rest_ensure_response( 
					array(
						"status" => "unknown",
						"message" => "Please contact your administrator. Request unknown!",
					)
				);
			}

			//Pass into a variable
			$post_type = $_POST["pt"];

			//Passing define variables to local variables
			$fields = POST_FIELDS;
			$table = POSTS_TABLE;
			$initial_feeds = INITIAL_FEEDS;
			$succeeding_feeds = SUCCEEDING_FEEDS; 

			//Check if last ID is passed, if not, it means this is the initial feed listing
			if(!isset($_POST['lid'])){

				$posts = $wpdb->get_results("
					SELECT $fields
					FROM $table
					WHERE post_type = '$post_type'
					ORDER BY ID DESC
					LIMIT $initial_feeds"
				);

				$last_id = min($posts);

				//Check if rows are found
				if (!$posts) {
					return rest_ensure_response( 
						array(
							"status" => "failed",
							"message" => "No posts found",
						)
					);
				}

				return rest_ensure_response( 
					array(
						"status" => "success",
						"data" => array(
							'list' => $posts, 
							'last_id' => $last_id->ID
						)
					)
				);
			

			//Additional Feeds
			} else {

				if ( !is_numeric($_POST["lid"])) {
					return rest_ensure_response( 
						array(
							"status" => "failed",
							"message" => "Parameters not in valid format!",
						)
					);
				}

				$get_last_id = $_POST['lid'];

				//Get 5 new posts
				$add_feeds = $get_last_id - $succeeding_feeds;

				$posts = $wpdb->get_results("
					SELECT $fields
					FROM $table
					WHERE post_type = '$post_type'
					AND ID BETWEEN $add_feeds AND ($get_last_id - 1)
					ORDER BY ID DESC"
				);

				if (count($posts) < 1) {
					return rest_ensure_response( 
						array(
							"status" => "failed",
							"message" => "No more posts to see",
						)
					);
				} else {
					//Pass the last id
					$last_id = min($posts);
				}

				return rest_ensure_response( 
					array(
						"status" => "success",
						"data" => array(
							'list' => $posts, 
							'last_id' => $last_id->ID
						)
					)
				);

			}

		}

    }
