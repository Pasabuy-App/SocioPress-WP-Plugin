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
  	class SP_Share_Post_link {

        public static function listen(){
            return rest_ensure_response( 
                SP_Share_Post_link:: list_open()
            );
        }
         
        public static function list_open(){

            // Initialize WP global variable
            global $wpdb;
            
            $table_post = WP_POSTS;
            
			// Step 1: Check if prerequisites plugin are missing
			$plugin = SP_Globals::verify_prerequisites();
			if ($plugin !== true) {
				return array(
					 "status" => "unknown",
					 "message" => "Please contact your administrator. ".$plugin." plugin missing!",
				);
			}
			 
			// Step 2: Check if wpid and snky is valid
			if (DV_Verification::is_verified() == false) {
                return array(
                   "status" => "unknown",
                   "message" => "Please contact your administrator. Verification Issues!",
                );
            }
            
           // Step 3: Check if required parameters are passed
            if ( !isset($_POST['pid']) ) {
                return array(
					"status" => "unknown",
					"message" => "Please contact your administrator. Missing paramiters!",
			 	);
            }

            // Step 4: Check if parameters passed are empty
            if ( empty($_POST['pid']) ) {
                return array(
					"status" => "failed",
					"message" => "Required fileds cannot be empty.",
			 	);
            }

            // Step 5: Store post to variable
            $post_id = $_POST['pid'];

            // Step 6: Query
            $get_post =  $wpdb->get_row("SELECT * FROM $table_post WHERE ID = $post_id");

            // Step 7: Check result
            if ( !$get_post ) {
                return array(
                    "status"  => "success",
                    "message" => "This post does not exists."
                );
            }

            // Step 8: Return result
            return array(
                "status"  => "success",
                "data"    => $get_post->guid
            );
        }
    }