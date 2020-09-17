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

            $sql = "SELECT
                t.hash_id as ID,
                IF (`sender` = '$wpid', `recipient`, `sender`) as `user_id`,
                date_created,
                if(date_seen is null , '', date_seen) as date_seen ,
                null as avatar,
                (SELECT rev.child_val FROM sp_revisions rev WHERE rev.parent_id = t.ID AND rev.id = t.content AND rev.child_key = 'content' AND ID = (SELECT MAX(ID) FROM sp_revisions  WHERE id = rev.id  )) as content
            FROM sp_messages t
            WHERE '$wpid'
                IN (`sender`, `recipient`)
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

            $sql .= " GROUP BY user_id DESC LIMIT $limit ";

            $message = $wpdb->get_results($sql);
            foreach ($message as $key => $value) {
                if ($value->user_id ) {
                    $wp_user = get_user_by("ID", $value->user_id);
                    $ava = isset($wp_user->user_id) ? $ava = $wp_user->user_id: $ava = SP_PLUGIN_URL . "assets/default-avatar.png";
                    $value->avatar = $ava;
                }
            }

            return array(
                "status" => "success",
                "data" => $message
            );
        }
    }