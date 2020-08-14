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
  	class SP_GetBy_Recepient {
		  
        public static function listen(){
            return rest_ensure_response( 
                SP_GetBy_Recepient:: list_open()
            );
        }
         
        public static function list_open(){

			// Initialize WP global variable
			global $wpdb;

			$sender = $_POST['wpid'];
			$recepient = $_POST['recepient'];
			
			// Step 1: Check if prerequisites plugin are missing
            $plugin = SP_Globals::verify_prerequisites();
            if ($plugin !== true) {

                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. ".$plugin." plugin missing.",
                );
            }

			// Step 2: Validate user
			if (DV_Verification::is_verified() == false) {
                
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Verification issues.",
                );
			}

			// Step 3: Valdiate user
            $recepients = WP_User::get_data_by( 'ID', $recepient );
            if ( !$recepients ) {
                return array(
                    "status" => "failed",
                    "message" => "User does not exist.",
                );
            }

			if(!isset($_POST['lid'])){

				//Step 4: Get results from database 
				$result= $wpdb->get_results("SELECT
					sp_messages.id, 
					(SELECT sp_revisions.child_val FROM sp_revisions WHERE sp_revisions.id = sp_messages.content) as content
				FROM 
					sp_messages
				WHERE 
					(SELECT sp_revisions.child_val FROM sp_revisions WHERE sp_revisions.id = sp_messages.status) = '1' AND sp_messages.recepient = '$recepient' AND sp_messages.sender = '$sender'
				ORDER BY
					sp_messages.id DESC
				LIMIT 12", OBJECT);
				
				// Step 5: Pass the last id or the minimum id
				$last_id = min($result);

				// Step 6: Return a success message and a complete object
				return array(
						"status" => "success",
						"data" => array(
							'list' => $result, 
							'last_id' => $last_id
					)
				);
			}else{
				
            	// Step 4: Check if parameter is valid
				if ( !is_numeric($_POST["lid"])) {
					return array(
							"status" => "failed",
							"message" => "Parameters not in valid format.",
					);
				}

				// Step 5: Pass the processed ids in a variable
				$get_last_id = $_POST['lid'];

				// Step 6: Get 7 new posts
				$add_feeds = $get_last_id - 7;
				
				//Step 7: Get results from database 
				$result= $wpdb->get_results("SELECT
					sp_messages.id,
					(SELECT sp_revisions.child_val FROM sp_revisions WHERE sp_revisions.id = sp_messages.content ) AS content 
				FROM
					sp_messages 
				WHERE
					(SELECT sp_revisions.child_val FROM sp_revisions WHERE sp_revisions.id = sp_messages.status) = '1' AND sp_messages.recepient = '$recepient' AND sp_messages.sender = '$sender'
					AND sp_messages.id BETWEEN $add_feeds
					AND ($get_last_id - 1)
				ORDER BY
					sp_messages.id DESC 
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
