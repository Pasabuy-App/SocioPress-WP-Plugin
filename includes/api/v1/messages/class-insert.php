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

        public static function listen(WP_REST_Request $request){
            return rest_ensure_response(
                self::list_open($request)
            );
        }

        public static function catch_post(){

            $cur_user = array();

            $cur_user['type']      = $_POST['type'];
            $cur_user['odid']    = $_POST['odid'];
            $cur_user['sender']    = $_POST['sender'];
            $cur_user['recepient'] = $_POST['recepient'];
            $cur_user['content']   = $_POST['content'];
            $cur_user['wpid']     = $_POST['wpid'];

            return  $cur_user;

        }

        public static function list_open($request){
            global $wpdb;
            $tbl_message       = SP_MESSAGES_TABLE;
            $tbl_message_filed = SP_MESSAGES_FIELDS;
            $tbl_mover         = HP_MOVERS_v2;
            $tbl_store         = TP_STORES_v2;
            $files             = $request->get_file_params();

            // Note: Message attachment

            // Step 1: Check if prerequisites plugin are missing
            $plugin = SP_Globals::verify_prerequisites();
            if ($plugin !== true) {
                return array(
                    "status"  => "unknown",
                    "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }

            // Step 2: Validate
            if (DV_Verification::is_verified() == false) {
                return array(
                    "status"  => "unknown",
                    "message" => "Please contact your administrator. Verification issues!",
                );
			}

            $user = self::catch_post();

            if($user['type'] != "store" && $user['type'] != "mover" && $user['type'] != "user" ){
                return array(
                    "status" => "failed",
                    "message" => "Invalid type of message."
                );
            }

            switch ($user['type']) {
                case 'store':
                    // Check store if exists    AND `status` = 'active'

                        $check_store = $wpdb->get_row("SELECT ID FROM $tbl_store WHERE hsid IN ( '{$user["sender"]}', '{$user["recepient"]}' )   AND id IN ( SELECT MAX( id ) FROM $tbl_store s WHERE s.hsid = hsid  GROUP BY hsid )");
                        if (empty($check_store)) {
                            return array(
                                "status" => "failed",
                                "message" => "This store does not exists."
                            );
                        }
                    // End
                    break;

                case 'mover':
                    // Check mover if exists
                        $check_mover = $wpdb->get_row("SELECT * FROM $tbl_mover WHERE pubkey IN ( '{$user["sender"]}', '{$user["recepient"]}' ) AND `status` = 'active' AND id IN ( SELECT MAX( id ) FROM $tbl_mover w WHERE w.hsid = hsid  GROUP BY hsid ) ");
                        if (empty($check_mover)) {
                            return array(
                                "status" => "failed",
                                "message" => "This mover does not exists"
                            );
                        }
                    // End
                    break;
            }

            $wpdb->query("START TRANSACTION");

            $import = $wpdb->query("INSERT INTO $tbl_message ($tbl_message_filed, odid) VALUES ( '{$user["content"]}', '{$user["sender"]}', '{$user["recepient"]}', '{$user["type"]}', '{$user["wpid"]}', '{$user["odid"]}'  ) ");
            $import_id = $wpdb->insert_id;

            $hsid = SP_Globals::generating_hsid($import_id, $tbl_message, 'hash_id');

            if ($import < 1 || $hsid = false) {
                $wpdb->query("ROLLBACK");
                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to sever."
                );

            }else{
                $wpdb->query("COMMIT");
                return array(
                    "status" => "success",
                    "message" => "Data has been added successfully."
                );
            }

        }
    }
