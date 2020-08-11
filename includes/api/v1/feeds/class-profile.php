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
  	class SP_Newsfeed {
         
        public static function profile_feeds(){
			// Initialize WP global variable
			global $wpdb;
			// Step 1: Check if ID is passed
			if (!isset($_POST["wpid"]) || !isset($_POST["snky"])) {
				return rest_ensure_response( 
					array(
						"status" => "unknown",
						"message" => "Please contact your administrator. Request unknown!",
					)
				);
			}
			// Step 2: Check if ID is in valid format (integer)
			if (!is_numeric($_POST["wpid"])) {
				return rest_ensure_response( 
					array(
						"status" => "failed",
						"message" => "Please contact your administrator. ID not in valid format!",
					)
				);
			}

			// Step 3: Check if ID exists
			if (!get_user_by("ID", $_POST['wpid'])) {
				return rest_ensure_response( 
					array(
						"status" => "failed",
						"message" => "User not found!",
					)
				);
			}

			if(!isset($_POST['lid'])){
				
				//Step 4: Pass the processed id in a variable
				$id = $_POST['wpid'];
				
				//Step 5: Create table name for posts (bc_posts)
				$table_post = SP_PREFIX.'posts';

				//Step 6: Get results from database 
				$result= $wpdb->get_results("SELECT id
						FROM $table_post 
						WHERE user_id = $id
						ORDER BY id DESC
						LIMIT 12", OBJECT);
				
				//Step 7: Pass the last id or the minimum id
				$last_id = min($result);

				//Step 8: Return a success message and a complete object
				return rest_ensure_response( 
					array(
						"status" => "success",
						"data" => array(
							'list' => $result, 
							'last_id' => $last_id
						)
					)
				);

			}else{

				if ( !is_numeric($_POST["lid"])) {
					return rest_ensure_response( 
						array(
							"status" => "failed",
							"message" => "Parameters not in valid format!",
						)
					);
				}

				// Step 4: Pass the processed ids in a variable
				$id = $_POST['wpid'];
				$get_last_id = $_POST['lid'];

				//Get 5 new posts
				$add_feeds = $get_last_id - 5;

				//Step 5: Create table name for posts (bc_posts)
				$table_post = SP_PREFIX.'posts';
				
				//Step 6: Get results from database 
				$result= $wpdb->get_results("SELECT id
					FROM $table_post 
					WHERE user_id = $id
					AND id BETWEEN $add_feeds AND ($get_last_id - 1)
					ORDER BY id DESC", OBJECT);

				//Step 7: Check if array count is 0 , return error message if true
				if (count($result) < 1) {
					return rest_ensure_response( 
						array(
							"status" => "failed",
							"message" => "No more posts to see",
						)
					);
				} else {
					//Pass the last id
					$last_id = min($result);
				}

				//Step 8: Return a success message and a complete object
				return rest_ensure_response( 
					array(
						"status" => "success",
						"data" => array(
							'list' => $result, 
							'last_id' => $last_id
						)
					)
				);


			}

		}






		
    }