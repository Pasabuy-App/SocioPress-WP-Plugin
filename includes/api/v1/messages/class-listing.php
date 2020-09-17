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
			if (DV_Verification::is_verified() == false) {
                return array(
                    "status"  => "unknown",
                    "message" => "Please contact your administrator. Verification issues!",
                );
            }

            $wpid = $_POST['wpid'];

            $sql = "SELECT t.hash_id as ID, date_created, date_seen, sender, recipient,
            (SELECT rev.child_val FROM sp_revisions rev WHERE rev.parent_id = t.ID AND rev.id = t.content AND rev.child_key = 'content' AND ID = (SELECT MAX(ID) FROM sp_revisions  WHERE id = rev.id  )) as content,
            null as avatar
            FROM
              sp_messages t INNER JOIN (
                SELECT
                  LEAST(sender, recipient) user1,
                  GREATEST(sender, recipient) user2,
                  MAX(date_created) max_created_on
                FROM
                  sp_messages mess
                WHERE sender = $wpid or recipient = $wpid AND (SELECT rev.child_val FROM sp_revisions rev WHERE rev.parent_id = mess.ID  AND rev.child_key = 'status' AND ID = (SELECT MAX(ID) FROM sp_revisions  WHERE id = rev.id  )) = 1

            ";

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

            $sql .= " GROUP BY
                LEAST(sender, recipient),
                GREATEST(sender, recipient)) M
            on t.date_created = M.max_created_on
            AND LEAST(t.sender, t.recipient)=user1
            AND GREATEST(t.sender, t.recipient)=user2
                ORDER BY t.ID DESC LIMIT $limit ";

            $message = $wpdb->get_results($sql);
            foreach ($message as $key => $value) {

                if ($value->recipient === $wpid) {
                    $wp_user = get_user_by("ID", $value->recipient);
                    $ava = isset($wp_user->avatar) ? $ava = $wp_user->avatar: $ava = SP_PLUGIN_URL . "assets/default-avatar.png";
                    $value->avatar = $ava;

                }else if($value->sender === $wpid){
                    $wp_user = get_user_by("ID", $value->sender);
                    $ava = isset($wp_user->avatar) ? $ava = $wp_user->avatar: $ava = SP_PLUGIN_URL . "assets/default-avatar.png";
                    $value->avatar = $ava;
                }
            }

            return array(
                "status" => "success",
                "data" => array(
                    "list" => $message
                )
            );
        }
    }