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
                self::list_open()
            );
        }
    
        public static function list_open(){

			// Initialize WP global variable
            global $wpdb;

            $table_revs = SP_REVS_TABLE;
            $field_revs = SP_REVS_TABLE_FIELDS;
            $table_mess = SP_MESSAGES_TABLE;
            $fields_mess = SP_MESSAGES_FIELDS;

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

			// Step 3: Check if required parameters are passed
            if  ( !isset($_POST['content']) || !isset($_POST['recepient']) ) {
                return array(
                    "status"  => "unknown",
                    "message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step 4: Check if parameters passed are empty
            if ( empty($_POST['content']) || empty($_POST['recepient']) ) {
                return array(
                    "status"  => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }

            // Step 5: Check if parameter is valid
            if ( !is_numeric($_POST['recepient']) ) {
                return array(
                    "status"  => "failed",
                    "message" => "ID is not in valid format.",
                );
            }

            $user = SP_Insert_Message::catch_post();
            $date = SP_Globals:: date_stamp();
            $id = array();

            // Step 6: Valdiate user using user id
            $recepients = WP_User::get_data_by( 'ID', $user['recepient'] );
            if ( !$recepients ) {
                return array(
                    "status"  => "failed",
                    "message" => "User does not exist.",
                );
            }

            // Step 7: Insert data to array
            $child_key = array( 
                'content' => $user['content'],
                'status'  => '1'
            );

            // Step 8: Start mysql transaction
            $wpdb->query("START TRANSACTION");
                
                foreach ( $child_key as $key => $child_val) { // Loop array and insert data ito mp revisions
                    $insert_revs = $wpdb->query("INSERT INTO $table_revs ($field_revs) VALUES ('messages', '0', '$key', '$child_val', '{$user["user_id"]}', '$date' ) ");
                    $id[] = $wpdb->insert_id;  // Last ID insert to Array
                }
                
                $wpdb->query("INSERT INTO $table_mess $fields_mess VALUES ('{$id[0]}', '{$user["user_id"]}', '{$user["recepient"]}', '{$id[1]}', '$date' ) "); // Insert data into mp messages
                $last_id = $wpdb->insert_id;

                $wpdb->query("UPDATE $table_mess SET `hash_id` = sha2($last_id, 256) WHERE ID = $last_id ");

                $update_revs = $wpdb->query("UPDATE $table_revs SET `parent_id` = $last_id WHERE ID IN ($id[0], $id[1]) ");// Update parent id in np revision
            
                $wpdb->query("UPDATE $table_revs SET `hash_id` = sha2($id[0], 256) WHERE ID = $id[0]");
                $wpdb->query("UPDATE $table_revs SET `hash_id` = sha2($id[1], 256) WHERE ID = $id[1]");

            // Step 9: Check if any queries above failed
            if ($insert_revs < 1 || $last_id < 1 || $update_revs < 1) {
                $wpdb->query("ROLLBACK");
                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to server."
                );
            }else{
                // Step 10: Commit if no errors found
                $wpdb->query("COMMIT");
                return array(
                    "status" => "success",
                    "message" => "Data has been added successfully."
                );
            }

            

        }

        public static function catch_post(){
            
            $cur_user = array();

            $cur_user['user_id']   = $_POST['wpid'];
            $cur_user['recepient'] = $_POST['recepient'];
            $cur_user['content']   = $_POST['content'];

            return  $cur_user;

        }
    }
