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

			$table_post = 'wp_posts';
			$id = $_POST['wpid'];
			
			// Step1 : Check if prerequisites plugin are missing
			$plugin = SP_Globals::verify_prerequisites();
			if ($plugin !== true) {
				return array(
						"status" => "unknown",
						"message" => "Please contact your administrator. ".$plugin." plugin missing!",
				);
			 }
			 
			// Step2 : Check if wpid and snky is valid
			if (DV_Verification::is_verified() == false) {
				return array(
						"status" => "unknown",
						"message" => "Please contact your administrator. Verification Issues!",
				);
			}

			if(!isset($_POST['lid'])){

				//Step 6: Get results from database 
				$result= $wpdb->get_results("SELECT 
					id
				FROM 
					$table_post 
				WHERE 
					post_author = $id 
				AND 
					post_status = 'publish'
				ORDER BY 
					id DESC
				LIMIT 12", OBJECT);
				
				//Step 7: Pass the last id or the minimum id
				$last_id = min($result);

				//Step 8: Return a success message and a complete object
				return array(
						"status" => "success",
						"data" => array(
							'list' => $result, 
							'last_id' => $last_id
					)
				);
			}else{
				
				if ( !is_numeric($_POST["lid"])) {
					return array(
							"status" => "failed",
							"message" => "Parameters not in valid format.",
					);
				}

				// Step 4: Pass the processed ids in a variable
				$get_last_id = $_POST['lid'];

				// Step 5: Get 5 new posts
				$add_feeds = $get_last_id - 7;
				
				//Step 6: Get results from database 
				$result= $wpdb->get_results("SELECT 
					id
				FROM 
					$table_post 
				WHERE 
					post_author = $id
				AND 
					id BETWEEN $add_feeds 
				AND 
					($get_last_id - 1) 
				AND 
					post_status = 'publish'
				ORDER BY 
					id DESC 
				LIMIT 12", OBJECT);

				//Step 7: Check if array count is 0 , return error message if true
				if (count($result) < 1) {
					return array(
							"status" => "failed",
							"message" => "No more posts.",
					);
				} else {
					//Pass the last id
					$last_id = min($result);
				}

				//Step 8: Return a success message and a complete object
				return array(
						"status" => "success",
						"data" => array(
							'list' => $result, 
							'last_id' => $last_id
					)
				);


			}

		}






		
    }
