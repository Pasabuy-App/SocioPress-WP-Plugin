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
  	class SP_Update_Message {

          public static function listen(){
            return rest_ensure_response( 
                SP_Update_Message::list_open()
            );
          }
    
        public static function list_open(){

			// Initialize WP global variable
            global $wpdb;
            $date = SP_Globals:: date_stamp();
            $table_revs = SP_REVS_TABLE;
            $field_revs = SP_REVS_TABLE_FIELDS;
            $table_mess = SP_MESSAGES_TABLE;
            $wpid = $_POST['wpid'];
            $mess_id = $_POST['mess_id'];
            $content = $_POST['content'];

            // Step 1: Check if prerequisites plugin are missing
            $plugin = SP_Globals::verify_prerequisites();
            if ($plugin !== true) {

                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. ".$plugin." plugin missing.",
                );
            }

			//  Step 2: Validate user
			if (DV_Verification::is_verified() == false) {
                
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Verification issues.",
                );
            }

			//  Step 3: Check if required parameters are passed
            if (!isset($_POST['content']) || !isset($_POST['mess_id']) ) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step 4: Check if parameters passed are empty
            if (empty($_POST['content']) || empty($_POST['mess_id']) ) {
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }

            // Step 5: Validate message
            $validate = $wpdb->get_row("SELECT ID FROM $table_mess WHERE ID = '$mess_id' AND recepient = '$wpid' ");
            $delete = $wpdb->get_row("SELECT child_val as status FROM $table_revs WHERE ID = (SELECT status FROM $table_mess WHERE ID = '$mess_id' AND recepient = '$wpid') ");
            if ( !$validate || $delete->status === '0') {
                return array(
                        "status" => "failed",
                        "message" => "This message does not exists.",
                );
            }

            // Step 6: Query
            $wpdb->query("START TRANSACTION");
                // Insert data to mp revisions
                $wpdb->query("INSERT INTO $table_revs $field_revs VALUES ('messages', '$mess_id', 'content', '$content', '$wpid', '$date' ) ");
                $last_id = $wpdb->insert_id;
                // Update mp message content from last id
                $update_mess = $wpdb->query("UPDATE $table_mess SET `content` = $last_id WHERE ID IN ($mess_id) ");
            
            // Step 7: Check result
            if ($last_id < 1 || $update_mess < 1) {
                $wpdb->query("ROLL BACK");
                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to server."
                );
            }else{
                $wpdb->query("COMMIT");
                return array(
                    "status" => "success",
                    "message" => "Data has been updated successfully."
                );
            }
        }
    }