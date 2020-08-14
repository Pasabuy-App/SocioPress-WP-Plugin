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
            $sender = $_POST['sender'];
            $recepient = $_POST['recepient'];

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
            if (!isset($_POST['content']) || !isset($_POST['sender']) || !isset($_POST['recepient']) ) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step 4: Check if parameters passed are empty
            if (empty($_POST['content']) || empty($_POST['sender']) || empty($_POST['recepient']) ) {
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }

            if (!is_numeric($_POST['sender']) || !is_numeric($_POST['recepient']) ) {
                return array(
                    "status" => "failed",
                    "message" => "ID is not in valid format.",
                );
            }
            // TODO : Check if the sender ID is valid
            $senders = WP_User::get_data_by( 'ID', $sender );
            $recepients = WP_User::get_data_by( 'ID', $recepient );
            if ( !$senders || !$recepients ) {
                return array(
                    "status" => "failed",
                    "message" => "User does not exist.",
                );
            }

            $child_key = array( 
                'content'     =>$_POST['content'],
                'status'    =>'1'
            );

            $wpdb->query("START TRANSACTION");
                $id = array();
                foreach ( $child_key as $key => $child_val) {
                    $insert_revs = $wpdb->query("INSERT INTO $table_revs $field_revs VALUES ('messages', '0', '$key', '$child_val', '$wpid', '$date' ) ");
                    $id[] = $wpdb->insert_id;
                }
                //return $id[0];
                $wpdb->query("INSERT INTO $table_mess $fields_mess VALUES ('$id[0]', '$sender', '$recepient', '$id[1]', '$date' ) ");
                $last_id = $wpdb->insert_id;

                $update_revs = $wpdb->query("UPDATE $table_revs SET `parent_id` = $last_id WHERE ID IN ($id[0], $id[1]) ");
            
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