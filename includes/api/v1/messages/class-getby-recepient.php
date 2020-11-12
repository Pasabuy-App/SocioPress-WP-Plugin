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

		public static function catch_post(){
			$curl_user = array();

			$curl_user['recipient'] = $_POST['recipient'];
			$curl_user['sender'] = $_POST['sender'];
			$curl_user['message_type'] = $_POST['type'];
			$curl_user['offset'] = $_POST['offset'];
			$curl_user['lid'] = $_POST['lid'];

			return $curl_user;
		}

		public static function list_open(){
			global $wpdb;
            $tbl_message = SP_MESSAGES_TABLE;
			$limit = " DESC LIMIT 12 ";

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

			$user = self::catch_post();

			$sql = "SELECT
					ID,
					hash_id,
					content,
					sender,
					recipient,
					`type`,
					`status`,
					created_by,
					date_created,
					date_seen
				FROM
					$tbl_message ";

			switch ($user['message_type']) {
                case 'store':
                    $sql .= " WHERE `type` = 'store' ";
                break;

                case 'mover':
                    $sql .= " WHERE `type` = 'mover' ";
                    break;

                case 'user':
                    $sql .= " WHERE `type` = 'user' ";
                    break;
			}

			$sql .= " AND `status` = 'active'
				AND  (recipient ='{$user["sender"]}' OR sender = '{$user["sender"]}')
				AND  (recipient ='{$user["recipient"]}' OR sender = '{$user["recipient"]}')
			";

			if ($user['lid'] != null ) { // inpput the last id of the message then lid for new message
				if (empty($user['lid'])) {
					return array(
						"status"  => "unknown",
						"message" => "Please contact your administrator. Request unknown!",
					);
				}
				$sql .= " AND id > {$user["lid"]} ";
				$limit = " ASC LIMIT 1";
			}

			if ($user['offset'] != null) {
				$offsets = 12 + $user["offset"];
				$limit = " DESC LIMIT 7 OFFSET ".$offsets;
			}

			$sql .= " ORDER BY ID $limit ";

			$data = $wpdb->get_results($sql);

			return array(
				"status" => "success",
				"data" => $data
			);
		}
    }
