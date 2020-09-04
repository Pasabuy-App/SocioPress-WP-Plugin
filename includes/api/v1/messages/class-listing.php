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
  	class SP_Listing_Message {

        public static function listen(){
            return rest_ensure_response( 
                self::list_open()
            );
        }
    
        public static function list_open(){
            global $wpdb;
            
            $table_revs = SP_REVS_TABLE;
            $field_revs = SP_REVS_TABLE_FIELDS;
            $table_mess = SP_MESSAGES_TABLE;
            $fields_mess = SP_MESSAGES_FIELDS;
			$succeeding_feeds = SUCCEEDING_FEEDS; 

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

            $wpid = $_POST['wpid'];
            

			if(!isset($_POST['lid_sender']) && !isset($_POST['lid_recipient'])){

                $recipient = $wpdb->get_results("SELECT
                    mess.ID, 
                    (SELECT rev.child_val FROM $table_revs rev WHERE rev.id = mess.content) as content,
                    mess.date_created,
                    mess.date_seen
                FROM 
                    $table_mess mess
                WHERE 
                    (SELECT rev.child_val FROM $table_revs rev WHERE rev.id = mess.status) = '1' 
                AND  recipient = $wpid 
                GROUP BY
                    date_created
                DESC
                ");
                $last_id_recipient = min($recipient);
 
                $sender = $wpdb->get_results("SELECT
                    mess.ID , 
                    (SELECT rev.child_val FROM $table_revs rev WHERE rev.id = mess.content) as content,
                    mess.date_created,
                    mess.date_seen
                FROM 
                    $table_mess mess
                WHERE 
                    (SELECT rev.child_val FROM $table_revs rev WHERE rev.id = mess.status) = '1' 
                AND  sender = $wpid
                GROUP BY
                    date_created
                DESC
                ");

                $last_id_sender = min($sender);

                return array(
                    "status" => "success",
                    "data" => array(
                        "list" =>  array_merge($recipient, $sender),
                        "last_id_sender" => $last_id_sender->ID,
                        "last_id_recipient" => $last_id_recipient->ID
                    )
                );
            
            }else{

                if (!isset($_POST['lid_sender']) && !isset($_POST['lid_recipient'])) {
                    return array(
                        "status" => "unknown",
                        "message" => "please contact your administrator"
                    );
                }
                if (isset($_POST['lid_sender']) && isset($_POST['lid_recipient'])) {
                    $lid_sender = $_POST['lid_sender'];
                    $lid_recipient = $_POST['lid_recipient'];

                }
				$add_feeds_sender = $lid_sender - $succeeding_feeds;
				$add_feeds_recipient = $lid_recipient - $succeeding_feeds;
                

                $recipient = $wpdb->get_results("SELECT
                    mess.ID, 
                    (SELECT rev.child_val FROM $table_revs rev WHERE rev.id = mess.content) as content,
                    mess.date_created,
                    mess.date_seen
                FROM 
                    $table_mess mess
                WHERE 
                    (SELECT rev.child_val FROM $table_revs rev WHERE rev.id = mess.status) = '1' 
                AND  recipient = $wpid 
				AND hash_id BETWEEN $add_feeds_recipient AND ($lid_recipient - 1)

                GROUP BY
                    date_created
                DESC
                ");
                $last_id_recipient = min($recipient);

 
                $sender = $wpdb->get_results("SELECT
                    mess.ID, 
                    (SELECT rev.child_val FROM $table_revs rev WHERE rev.id = mess.content) as content,
                    mess.date_created,
                    mess.date_seen
                FROM 
                    $table_mess mess
                WHERE 
                    (SELECT rev.child_val FROM $table_revs rev WHERE rev.id = mess.status) = '1' 
                AND  sender = $wpid
				AND hash_id BETWEEN $add_feeds_sender AND ($lid_sender - 1)
                GROUP BY
                    date_created
                DESC
                ");

                $last_id_sender = min($sender);

                return array(
                    "status" => "success",
                    "data" => array(
                        "list" =>  array_merge($recipient, $sender),
                        "last_id_sender" => $last_id_sender->ID,
                        "last_id_recipient" => $last_id_recipient->ID
                    )
                );
            
            }
            
        }
    }