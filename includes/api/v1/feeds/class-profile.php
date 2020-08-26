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
		  
        public static function listen(){
            return rest_ensure_response( 
                SP_Newsfeed:: list_open()
            );
        }
         
        public static function list_open(){

			// Initialize WP global variable
			global $wpdb;

			$table_post = WP_POSTS;
			
			// Step 1: Check if prerequisites plugin are missing
			$plugin = SP_Globals::verify_prerequisites();
			if ($plugin !== true) {
				return array(
						"status" => "unknown",
						"message" => "Please contact your administrator. ".$plugin." plugin missing!",
				);
			 }
			 
			// Step 2: Check if wpid and snky is valid
			if (DV_Verification::is_verified() == false) {
				return array(
						"status" => "unknown",
						"message" => "Please contact your administrator. Verification Issues!",
				);
			}
			
			$id = $_POST['wpid'];

			$sql ="SELECT post.id, post.post_content AS content, post.post_date AS date_created
			FROM 
				$table_post AS post
			WHERE 
				post.post_author = $id 
			AND 
				post.post_status = 'publish' ";

			if( isset($_POST['lid']) ){
				
				// Step 4: Validate parameter
                if (empty($_POST['lid']) ) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty.",
                    );
                }
				if ( !is_numeric($_POST["lid"])) {
					return array(
							"status" => "failed",
							"message" => "Parameters not in valid format.",
					);
				}

				// Step 5: Pass the post in variable and continuation of query
				$get_last_id = $_POST['lid'];
				$add_feeds = $get_last_id - 7;
				$sql .= " AND  post.id BETWEEN $add_feeds  AND  ($get_last_id - 1) ";

			}
			
			// Step 6: Get results from database 
			$sql .= " ORDER BY post.id DESC LIMIT 12 "; 
			$result= $wpdb->get_results( $sql, OBJECT);
			
			// Step 7: Check if array count is 0 , return error message if true
			if (count($result) < 1) {
				return array(
						"status" => "success",
						"message" => "No more posts.",
				);
			}

			// Step 8: Pass the last id
			$last_id = min($result); 

			// Step 9: Return a success message and a complete object
			return array(
					"status" => "success",
					"data" => array($result, $last_id
				)
			);
		}	
    }
