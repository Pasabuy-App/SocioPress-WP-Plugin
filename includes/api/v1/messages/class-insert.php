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
			// if (DV_Verification::is_verified() == false) {
            //     return array(
            //         "status"  => "unknown",
            //         "message" => "Please contact your administrator. Verification issues!",
            //     );
            // }

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

            $sender = $_POST['wpid'];
            $content = $_POST['content'];
            $recepient = $_POST['recepient'];
            $type = $_POST['type'];
            $stid = "0";
            if ($type === "1"){ //User to Store, validate sender using wpid and recipient using stid and recipient
                if (!isset($_POST['stid']) ){
                    return array(
                        "status"  => "unknown",
                        "message" => "Please contact your administrator. Request unknown!",
                    );
                }
                $type = "1";
                $stid = $_POST['stid'];
                $senders = WP_User::get_data_by( 'ID', $sender ); // validate sender using wpid
                $valstore = $wpdb->get_row("SELECT * FROM tp_stores WHERE ID = '$stid' AND created_by = '$recepient' "); // validate recipient using stid and recipient
                if (!$senders || !$valstore){
                    return array(
                        "status"  => "failed",
                        "message" => "Invalid sender or recipient.",
                    );
                }
            }
            if ($type === "2"){ //Store to user, validate store using stid and wpid and user using recipient
                if (!isset($_POST['stid']) ){
                    return array(
                        "status"  => "unknown",
                        "message" => "Please contact your administrator. Request unknown!",
                    );
                }
                $type = "1";
                $stid = $_POST['stid'];
                $recepients = WP_User::get_data_by( 'ID', $recepient ); //validate recipient using recipient
                $valstore = $wpdb->get_row("SELECT * FROM tp_stores WHERE ID = '$stid' AND created_by = '$sender' "); //validate sender using sender and store id
                if (!$recepients || !$valstore){
                    return array(
                        "status"  => "failed",
                        "message" => "Invalid sender or recipient.",
                    );
                }
            }
            if ($type === "3"){ //User to mover or mover to user, validate user using wpid and mover using recipient if have documents
                $type = "2";
                $senders = WP_User::get_data_by( 'ID', $sender );
                $recepients = WP_User::get_data_by( 'ID', $recepient );
                if (!$recepients || !$senders){
                    return array(
                        "status"  => "failed",
                        "message" => "Invalid sender or recipient.",
                    );
                }
            }
            if ($type === "0"){ //user to user
                $type = "0";
                $senders = WP_User::get_data_by( 'ID', $sender );
                $recepients = WP_User::get_data_by( 'ID', $recepient );
                if (!$senders || !$recepients){
                    return array(
                        "status"  => "failed",
                        "message" => "Invalid sender or recipient.",
                    );
                }
            }
            
            //$recepients = WP_User::get_data_by( 'ID', $recepient ); // validate recipient if user
            // if ( !$recepients || !$valstore {
            //     return array(
            //         "status"  => "failed",
            //         "message" => "Recepient does not exist.",
            //     );
            // }

            //$user = SP_Insert_Message::catch_post();
            $date = SP_Globals:: date_stamp();
            $id = array();

            // Step 6: Validate user using user id
            // TODO : If recipipent is user or mover, valdiate using wpid and if store, ude store id
            // $recepients = WP_User::get_data_by( 'ID', $recepient ); // wpid
            // $valstore = $wpdb->get_row("SELECT * FROM tp_stores WHERE ID = '$recepient' "); // store id
            // if ( !$recepients || !$valstore {
            //     return array(
            //         "status"  => "failed",
            //         "message" => "Recepient does not exist.",
            //     );
            // }
            //return 0;

            // Step 7: Insert data to array
            $child_key = array(
                'content' => $content,
                'status'  => '1'
            );

            // Step 8: Start mysql transaction
            $wpdb->query("START TRANSACTION");

                foreach ( $child_key as $key => $child_val) { // Loop array and insert data ito mp revisions
                    $insert_revs = $wpdb->query("INSERT INTO $table_revs ($field_revs) VALUES ('messages', '0', '$key', '$child_val', '$sender', '$date' ) ");
                    $id[] = $wpdb->insert_id;  // Last ID insert to Array
                }

                $wpdb->query("INSERT INTO $table_mess (content, sender, recipient, stid, type, status, date_created) VALUES ('{$id[0]}', '$sender', '$recepient', '$stid', '$type', '{$id[1]}', '$date' ) "); // Insert data into mp messages
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
