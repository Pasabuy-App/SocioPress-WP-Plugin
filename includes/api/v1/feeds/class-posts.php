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

	class SP_Posts {


        public static function listen(){
            return rest_ensure_response(
                self:: list_open()
            );
        }

        public static function list_open(){

            // Initialize WP global variable
            global $wpdb;

			//Validate user session
			$result = SP_Globals::validate_user();

			if ($result !== true) {
				return $result;
			}

			//Check if post type filter is set
			if (!isset($_POST["pt"])) {
				return array(
					"status" => "unknown",
					"message" => "Please contact your administrator. Request unknown!",
				);
			}

			//Pass into a variable
			$post_type = $_POST["pt"];

			//Passing define variables to local variables
			$fields = POST_FIELDS;
			$table = POSTS_TABLE;
			$initial_feeds = INITIAL_FEEDS;
			$succeeding_feeds = SUCCEEDING_FEEDS;

			//Check if last ID is passed, if not, it means this is the initial feed listing

			$sql = "SELECT $fields
				FROM $table
				WHERE post_type = '$post_type'";

			if (isset($_POST['lid'])) {

				if ( !is_numeric($_POST["lid"])) {
					return array(
						"status" => "failed",
						"message" => "Parameters not in valid format!",
					);
				}

				$get_last_id = $_POST['lid'];

				//Get 5 new posts
				$add_feeds = $get_last_id - $succeeding_feeds;

				$sql .= " AND ID BETWEEN $add_feeds AND ($get_last_id - 1) ";

			}

			$sql = " ORDER BY ID DESC
			LIMIT $initial_feeds ";

			$posts = $wpdb->get_results($sql);

			return array(
				"status" => "success",
				"data" => array($posts)
			);
		}
    }
