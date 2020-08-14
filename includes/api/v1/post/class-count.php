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
			
            $post_type = $_POST["post_type"];
            $user_id = $_POST["user_id"];
            $table_posts = 'wp_posts';
			
            // Step1 : Check if prerequisites plugin are missing
            $plugin = SP_Globals::verify_prerequisites();
            if ($plugin !== true) {
                return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }

            // Step 2: Validate user
            if (DV_Verification::is_verified() == false) {
                return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Verification issues!",
                );
			}

            // Step 3: Check if required parameters are passed
            if (!isset($_POST["user_id"]) ) {
				return array(
						"status" => "unknown",
						"message" => "Please contact your administrator. Request unknown!",
                ); 
            }

           // Step 4: Check if parameters passed are empty
            if (empty($_POST["user_id"]) ) {
                return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty.",
                );
            }
			
            // Step5 : Validation post
            $get_id = $wpdb->get_row("SELECT ID FROM wp_posts WHERE ID = '$user_id' ");
            if ( !$get_id ) {
                return array(
                        "status" => "failed",
                        "message" => "No post found.",
                );
            }
            
            // Step6 : Query
            $result = $wpdb->get_results("SELECT
                wp_pos.post_author AS user_id,
                COUNT(wp_pos.post_author) AS count
            FROM
                $table_posts AS wp_pos
            WHERE 
                wp_pos.post_status = 'publish' and wp_pos.post_author = '$user_id'
            GROUP BY 
                wp_pos.post_author
            ");

            /* Select Query
            $result = $wpdb->get_results("SELECT
                wp_posts.id, 
                wp_posts.post_author, 
                wp_posts.post_date, 
                wp_posts.post_title, 
	            wp_posts.post_content,
	            wp_posts.post_type
            FROM
                wp_posts
            ");*/
            
            // Step 7: Check if no result
            if (!$result)
            {
                return array(
                        "status" => "failed",
                        "message" => "No results found.",
                );
            }
            
            // Step8 : Return Result 
            return array(
                    "status" => "success",
                    "data" => $result
            );

		}
		

    }
