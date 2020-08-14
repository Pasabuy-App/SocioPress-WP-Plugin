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
  	class SP_Insert_Message {

          public static function listen(){
            return rest_ensure_response( 
                SP_Insert_Message::list_open()
            );
          }
    
        public static function list_open(){

			// Initialize WP global variable
            global $wpdb;
            $date = SP_Globals:: date_stamp();
            $table_revs = SP_REVS_TABLE;
            $field_revs = SP_REVS_TABLE_FIELDS;
            $table_mess = SP_MESSAGES_TABLE;
            $fields_mess = SP_MESSAGES_FIELDS;
            $wpid = $_POST['wpid'];
            $recepient = $_POST['recepient'];
            $content = $_POST['content'];

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

			// Step 3: Check if required parameters are passed
            if (!isset($_POST['content']) || !isset($_POST['recepient'])) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step 4: Check if parameters passed are empty
            if (empty($_POST['content']) || empty($_POST['recepient']) ) {
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }

            // Step 5: Check if parameter is valid
            if (!is_numeric($_POST['recepient']) ) {
                return array(
                    "status" => "failed",
                    "message" => "ID is not in valid format.",
                );
            }

            // Step 6: Valdiate user
            $recepients = WP_User::get_data_by( 'ID', $recepient );
            if ( !$recepients ) {
                return array(
                    "status" => "failed",
                    "message" => "User does not exist.",
                );
            }

            // Step 7: Insert data to array
            $child_key = array( 
                'content'     =>$content,
                'status'    =>'1'
            );

            // Step 8: Query
            $wpdb->query("START TRANSACTION");
                $id = array();
                // Insert data to mp revisions
                foreach ( $child_key as $key => $child_val) {
                    $insert_revs = $wpdb->query("INSERT INTO $table_revs $field_revs VALUES ('messages', '0', '$key', '$child_val', '$wpid', '$date' ) ");
                    $id[] = $wpdb->insert_id;  // Last ID insert to Array
                }
                // Insert data to mp messages
                $wpdb->query("INSERT INTO $table_mess $fields_mess VALUES ('$id[0]', '$wpid', '$recepient', '$id[1]', '$date' ) ");
                $last_id = $wpdb->insert_id;
                // Update parent id in np revision
                $update_revs = $wpdb->query("UPDATE $table_revs SET `parent_id` = $last_id WHERE ID IN ($id[0], $id[1]) ");
            
            // Step 9: Check result
            if ($insert_revs < 1 || $last_id < 1 || $update_revs < 1) {
                $wpdb->query("ROLL BACK");
                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to server."
                );
            }else{
                $wpdb->query("COMMIT");
                return array(
                    "status" => "success",
                    "message" => "Data has been submitted successfully."
                );
            }

        }
    }