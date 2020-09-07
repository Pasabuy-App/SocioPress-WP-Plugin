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
                self:: list_open()
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
                    "message" => "Please contact your administrator. Verification issues!",
                );
			}

			$sender = $_POST['wpid'];
			$recepient = $_POST['recepient'];

			// Step 3: Valdiate user using user id
            $recepients = WP_User::get_data_by( 'ID', $recepient );
            if ( !$recepients ) {
                return array(
                    "status"  => "failed",
                    "message" => "Recepient does not exist.",
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
				sp_messages.recipient = '$recepient' AND sp_messages.sender = '$sender' ";

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

			// Step 11: Return result
			return array(
				"status" => "success",
				"data" => $result
			);
		}
    }
