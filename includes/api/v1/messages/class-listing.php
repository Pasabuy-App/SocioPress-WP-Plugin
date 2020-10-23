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

            // Step 3: Validate parameter if passed
            if  ( !isset($_POST['type']) ) {
                return array(
                    "status"  => "unknown",
                    "message" => "Please contact your administrator. Request unknown!",
                );
            }

            $wpid = $_POST['wpid'];
            $type = $_POST['type'];
            //$stid = "0";

            // Step 4: Start mysql transaction
            $sql = "SELECT
                (SELECT id FROM sp_messages WHERE content = MAX(t.content)) as ID,
                (SELECT stid FROM sp_messages WHERE content = MAX(t.content)) as store_id,
                (SELECT child_val FROM tp_revisions WHERE ID = (SELECT logo FROM tp_stores WHERE ID = (SELECT stid FROM sp_messages WHERE content = MAX(t.content) ) )) as store_avatar,
                (SELECT child_val FROM tp_revisions WHERE ID = (SELECT title FROM tp_stores WHERE ID = (SELECT stid FROM sp_messages WHERE content = MAX(t.content)))) as store_name,
                (SELECT type FROM sp_messages WHERE content = MAX(t.content)) as types,
                #t.hash_id as ID,
                IF (`sender` = '$wpid', `recipient`, `sender`) as `user_id`,
                MAX(date_created) AS date_created,
                if((SELECT date_seen FROM sp_messages WHERE content = MAX(t.content)) is null , '', (SELECT date_seen FROM sp_messages WHERE content = MAX(t.content))) as date_seen ,
                null as avatar,
                null as `name`,
				(SELECT child_val FROM sp_revisions WHERE ID = MAX(t.content)) AS content,
				(SELECT sender FROM sp_messages WHERE content = MAX(t.content)) as sender_id
                #(SELECT rev.child_val FROM sp_revisions rev WHERE rev.parent_id = t.ID AND rev.id = t.content AND rev.child_key = 'content' AND ID = (SELECT MAX(ID) FROM sp_revisions  WHERE id = rev.id  )) as content
            FROM sp_messages t
            WHERE '$wpid'
                IN (`sender`, `recipient`)
            ";

            $limit = 12;

            if (isset($_POST['lid'])) {
                // if (empty($_POST['lid'])) {
                //     return array(
                //         "status"  => "unknown",
                //         "message" => "Please contact your administrator. Request unknown!",
                //     );
                // }

                $lastid = $_POST['lid'];

				$offset = 12 + $lastid;
				//$sql .= " AND mess.id < $lastid ";

				$limit = "7 OFFSET ".$offset;
            }
            // if ($type === "0"){ // for user message with store but for user
            //     if  ( !isset($_POST['stid']) ) {
            //         return array(
            //             "status"  => "unknown",
            //             "message" => "Please contact your administrator. Request unknown!",
            //         );
            //     }
            //     $stid = $_POST['stid'];
            //     if ($stid === "0"){
            //         $sql .= " AND sender = $wpid AND type != '2' ";
            //     }
            //     if ($stid !== "0"){
            //         $sql .= " AND stid != '$stid'  ";  //AND sender = $wpid AND type != '2'
            //     }
            // }
            if ($type === "0"){ 
                $sql .= " AND type = '0' ";
            }
            if ($type === "1"){ // new(mover message w/ or w/o store) old(for user message with store but for user only)
                //$sql .= " AND type NOT IN ('1') ";
                $sql .= " AND wpid = '$wpid' ";
            }
            if ($type === "2"){ // new(store message w/ or w/o mover) old(for user message with store but for store only)
                if  ( !isset($_POST['stid']) ) {
                    return array(
                        "status"  => "unknown",
                        "message" => "Please contact your administrator. Request unknown!",
                    );
                }
                $stid = $_POST['stid'];
                //$sql .= " AND type IN ('1') AND stid = '$stid' ";
                $sql .= " AND stid = '$stid' ";
            }
            if ($type === "3"){ // new(user message w/ mover only) old(for user message with mover but for user only)
                //$sql .= " AND type NOT IN ('2') ";
                $sql .= " AND wpid != '$wpid' ";
            }
            if ($type === "4"){ // new(user message with store only or store w/ mover) old(for user message with mover but for mover only)
                //$sql .= " AND type IN ('2') ";
                if  ( !isset($_POST['stid']) ) {
                    return array(
                        "status"  => "unknown",
                        "message" => "Please contact your administrator. Request unknown!",
                    );
                }
                $stid = $_POST['stid'];
                $sql .= " AND stid != '$stid' AND wpid != '$wpid' ";
            }

            $sql .= " GROUP BY user_id, type ORDER BY MAX(t.ID) DESC LIMIT $limit ";

            $message = $wpdb->get_results($sql);
            foreach ($message as $key => $value) {
                if ($value->user_id ) {
                    $wp_user = get_user_by("ID", $value->user_id);
                    //return $value->user_id;
                    $avatar = get_user_meta( $value->user_id,  $key = 'avatar', $single = false );
                    // if (!$avatar) {
					// 	$smp = SP_PLUGIN_URL . "assets/default-avatar.png";
					// }else{
					// 	$smp = $avatar[0];
					// }
                    //$ava = isset($value->user_id) ? $ava = $wp_user->avatar: $ava = SP_PLUGIN_URL . "assets/default-avatar.png";
                    //$value->avatar = $smp;
                    $value->avatar = !$avatar ? SP_PLUGIN_URL . "assets/default-avatar.png" : $avatar[0];

                    $value->name = $wp_user->display_name;
                }
            }

            return array(
                "status" => "success",
                "data" => $message
            );
        }
    }