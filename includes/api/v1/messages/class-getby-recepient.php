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

			$wpid = $_POST['wpid'];
			$user_id = $_POST['user_id'];

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
			mess.id,
			( SELECT sp_revisions.child_val FROM sp_revisions WHERE sp_revisions.id = mess.content ) AS content,
			mess.date_created
		FROM
			sp_messages mess
		WHERE
			( SELECT sp_revisions.child_val FROM sp_revisions WHERE sp_revisions.id = mess.STATUS ) = '1'
			AND (mess.recipient = '$wpid'
			or mess.sender = '$wpid')

			AND (mess.recipient = '$user_id'
			or mess.sender = '$user_id') ";

			$limit = 12;

			if (isset($_POST['lid'])) {
				if (empty($_POST['lid'])) {
					return array(
						"status"  => "unknown",
						"message" => "Please contact your administrator. Request unknown!",
					);
				}

				$lastid = $_POST['lid'];
				$sql .= " AND mess.id < $lastid ";
				$limit = 7;
			}

			// Step 8: Get results from database
			$sql .= " ORDER BY mess.id DESC  LIMIT $limit  ";
			$result= $wpdb->get_results( $sql , OBJECT);

			// Step 11: Return result
			return array(
				"status" => "success",
				"data" => $result
			);
		}
    }
