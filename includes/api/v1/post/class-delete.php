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
  	class SP_Delete_Post {

        public static function listen(){
            return rest_ensure_response( 
                SP_Delete_Post:: list_open()
            );
        }
         
        public static function list_open(){
            
            // Initialize WP global variable
			global $wpdb;
			
            $post_id = $_POST["post_id"];
			
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
                    "message" => "Please contact your administrator. Verification Issues!",
                );
			}

            // Step 3: Check if required parameters are passed
            if (!isset($_POST["post_id"]) ) {
				return array(
					"status"  => "unknown",
					"message" => "Please contact your administrator. Request unknown!",
                ); 
            }

            // Step 4: Check if parameters passed are empty
            if (empty($_POST["post_id"]) ) {
                return array(
                    "status"  => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }
            
            // Step 5: Validation post
            $get_id = $wpdb->get_row("SELECT ID FROM wp_posts  WHERE ID = '$post_id' ");
            if ( !$get_id ) {
                return array(
                    "status"  => "failed",
                    "message" => "No post found.",
                );
            }
            
            $validate = $wpdb->get_row("SELECT ID FROM wp_posts  WHERE ID = '$post_id' and post_status = 'trash'");
            if ( $validate ) {
                return array(
                    "status"  => "failed",
                    "message" => "This post has already been deleted.",
                );
            }
            
            // Step 6: Query
            //$result = wp_delete_post( $get_id->ID, true); // If custom post, it change post_status to trash but if post type is post it will be deleted.
            $result = wp_trash_post( $get_id->ID); // Change post_status to trash and add revision
			
            // Step 7: Check result if failed
            if ($result < 1) {
                return array(
                    "status"  => "failed",
                    "message" => "An error occured while submitting data to database.",
                );
            }

            // Step 8: Return a success status and message 
            return array(
                "status"  => "success",
                "message" => "Data has been deleted successfully.",
            );

		}
		

    }