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

        public static function catch_post(){
            $curl_user = array();

            $curl_user['user_id'] = $_POST['user_id'];
            $curl_user['message_type'] = $_POST['type'];
            isset($_POST['lid']) && !empty($_POST['lid'])? $curl_user['lid'] =  $_POST['lid'] :  $curl_user['lid'] = null ;

            return $curl_user;
        }

        public static function list_open(){
            global $wpdb;
            $tbl_message = SP_MESSAGES_TABLE;
            $limit = 12;

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

            $user = self::catch_post();

            $sql = "SELECT
                    ID,
                    hash_id,
                     MAX( content )  as content,
                    sender,
                    recipient,
                    IF (`sender` = '{$user["user_id"]}', `recipient`, `sender`) as `user_id`,
                    `type`,
                    `status`,
                    created_by,
                    (SELECT date_seen FROM $tbl_message WHERE id IN ( SELECT MAX( id ) FROM $tbl_message WHERE t.hash_id = hash_id GROUP BY sender OR recipient ) ) as date_seen,
                    MAX(date_created) AS date_created
                FROM
                    $tbl_message t
                WHERE
                    '{$user["user_id"]}' IN ( `sender`, `recipient` ) ";

            switch ($user['message_type']) {
                case 'store':
                    $sql .= " AND `type` = 'store' ";
                break;

                case 'mover':
                    $sql .= " AND `type` = 'mover' ";
                    break;

                case 'user':
                    $sql .= " AND `type` = 'user' ";
                    break;
            }

            // Set offset ID
            if ($user['lid'] != null) {
                $offset = 12 + $user['lid'];
                $limit = "7 OFFSET ".$offset;

            }

            $sql .= " GROUP BY sender, recipient, type ORDER BY MAX(ID) DESC LIMIT $limit ";

            $data = $wpdb->get_results($sql);

            foreach ($data as $key => $value) {

                if ($value->user = "type" ) {
                    if ($value->user_id ) {
                        $wp_user = get_user_by("ID", $value->user_id);

                        $avatar = get_user_meta( $value->user_id,  $key = 'avatar', $single = false );

                        $value->avatar = !$avatar ? SP_PLUGIN_URL . "assets/default-avatar.png" : $avatar[0];

                        $value->name = $wp_user->display_name;
                    }
                }
            }

            return array(
                "status" => "success",
                "data" => $data
            );
        }
    }