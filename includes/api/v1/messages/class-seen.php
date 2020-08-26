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
  	class SP_Seen_Message {

          public static function listen(){
            return rest_ensure_response( 
                SP_Seen_Message::list_open()
            );
          }
    
        public static function list_open(){

			// Initialize WP global variable
            global $wpdb;

            $table_mess = SP_MESSAGES_TABLE;

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

			// Step 3: Check if required parameters are passed
            if ( !isset($_POST['mess_id']) ) {
                return array(
                    "status"  => "unknown",
                    "message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step 4: Check if parameters passed are empty
            if ( empty($_POST['mess_id']) ) {
                return array(
                    "status"  => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }

            $date = SP_Globals:: date_stamp();
            $wpid = $_POST['wpid'];
            $mess_id = $_POST['mess_id'];

            // Step 5: Validate message using message id and user id
            $validate = $wpdb->get_row("SELECT ID FROM $table_mess WHERE ID = '$mess_id' AND sender = '$wpid' ");
            $delete = $wpdb->get_row("SELECT child_val as status FROM $table_revs WHERE ID = (SELECT status FROM $table_mess WHERE ID = '$mess_id' AND sender = '$wpid') ");
            if ( !$validate || $delete->status === '0') {
                return array(
                    "status"  => "success",
                    "message" => "This message does not exists.",
                );
            }

            // Step 6: Query
            $update_mess = $wpdb->query("UPDATE $table_mess SET date_seen = '$date' WHERE ID = '$mess_id' AND sender = '$wpid'");
            
            // Step 7: Check result
            if ($update_mess < 1) {
                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to server."
                );
            }

            // Step 8: Commit query
            return array(
                "status" => "success",
                "message" => "Data has been added successfully."
            );

        }
    }