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
			
			// Step 1: Check if prerequisites plugin are missing
            $plugin = SP_Globals::verify_prerequisites();
            if ($plugin !== true) {
                return array(
                    "status"  => "unknown",
                    "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }

			// Step 2: Validate user
			if (DV_Verification::is_verified() == false) {
                return array(
                    "status"  => "unknown",
                    "message" => "Please contact your administrator. Verification Issues!",
                );
			}
			

			$sender = $_POST['wpid'];
			$recepient = $_POST['recepient'];

			// Step 3: Valdiate user using user id
            $recepients = WP_User::get_data_by( 'ID', $recepient );
            if ( !$recepients ) {
                return array(
                    "status"  => "failed",
                    "message" => "User does not exist.",
                );
			}
			
			// Step 4: Start mysql transaction
			$sql = "SELECT
				sp_messages.id, 
				(SELECT sp_revisions.child_val FROM sp_revisions WHERE sp_revisions.id = sp_messages.content) as content,
				sp_messages.date_created
			FROM 
				sp_messages
			WHERE 
				(SELECT sp_revisions.child_val FROM sp_revisions WHERE sp_revisions.id = sp_messages.status) = '1' 
			AND 
				sp_messages.recepient = '$recepient' AND sp_messages.sender = '$sender' ";

			// Step 5: Check last id post is set
			if( isset($_POST['lid']) ){

			// Step 6: Validate parameter
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

			// Step 7: Pass the post in variable and continuation of query
				$get_last_id = $_POST['lid'];
				$add_feeds = $get_last_id - 7;
				$sql .= " AND sp_messages.id BETWEEN $add_feeds AND ($get_last_id - 1) ";

			}

			// Step 8: Get results from database 
			$sql .= " ORDER BY sp_messages.id DESC  LIMIT 12 ";
			$result= $wpdb->get_results( $sql , OBJECT);

			// Step 9: Check if array count is 0 , return error message if true
			if (count($result) < 1) {
				return array(
					"status"  => "success",
					"message" => "No more message.",
				);
			}
				
			// Step 10: Pass the last id or the minimum id
			$last_id = min($result);

			// Step 11: Commit if no errors found
			return array(
				"status" => "success",
				"data" => array( 
					$result, 
					$last_id
				)
			);

		}
		
    }
