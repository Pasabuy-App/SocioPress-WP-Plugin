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
  	class SP_Count_Post {

        public static function listen(){
            return rest_ensure_response(
                SP_Count_Post:: list_open()
            );
        }

        public static function list_open(){

            // Initialize WP global variable
			global $wpdb;

            $table_posts = WP_POSTS;

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
            if ( !isset($_POST["user_id"]) ) {
				return array(
					"status"  => "unknown",
					"message" => "Please contact your administrator. Request unknown!",
                );
            }

           // Step 4: Check if parameters passed are empty
            if ( empty($_POST["user_id"]) ) {
                return array(
                    "status"  => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }
            //$post_type = $_POST["post_type"];
            $user_id = $_POST["user_id"];

            // Step 5: Validation post using user id
            $get_id = $wpdb->get_row("SELECT ID FROM wp_posts WHERE post_author = '$user_id' ");
            if ( !$get_id ) {
                return array(
                    "status"  => "success",
                    "message" => "No post found.",
                );
            }

            // Step 6: Start mysql transaction
            $result = $wpdb->get_row("SELECT
                post.post_author AS user_id,
                COUNT( post.post_author ) AS count
            FROM
                $table_posts AS post
            WHERE
                post.post_status = 'publish'
                AND post.post_author = '$user_id'
                AND post.post_type IN ('status', 'pasabay', 'sell','pabili','pahatid')
            GROUP BY
                post.post_author
            ");

            // Step 7: Check if no result
            if (!$result) {
                return array(
                    "status"  => "success",
                    "message" => "No data found.",
                );
            }

            // Step 8: Return result
            return array(
                "status" => "success",
                "data"   => $result
            );

		}


    }
