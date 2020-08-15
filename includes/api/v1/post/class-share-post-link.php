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
            global $wpdb;

            
			// Step1 : Check if prerequisites plugin are missing
			$plugin = SP_Globals::verify_prerequisites();
			if ($plugin !== true) {
				return array(
					 "status" => "unknown",
					 "message" => "Please contact your administrator. ".$plugin." plugin missing!",
				);
			}
			 
			// Step2 : Check if wpid and snky is valid
			if (DV_Verification::is_verified() == false) {
                return array(
                   "status" => "unknown",
                   "message" => "Please contact your administrator. Verification Issues!",
                );
           }
            
            if (!isset($_POST['pid'])) {
                return array(
					"status" => "unknown",
					"message" => "Please contact your administrator. Missing paramiters!",
			 	);
            }

            if (empty($_POST['pid'])) {
                return array(
					"status" => "failed",
					"message" => "Required fileds cannot be empty.",
			 	);
            }

            $table_post = WP_POSTS;

            $post_id = $_POST['pid'];

            $get_post =  $wpdb->get_row("SELECT * FROM $table_post WHERE ID = $post_id");

            if (empty($get_post)) {
                return array(
                    "status"  => "failed",
                    "message" => "This post does not exists."
                );
            }else{
                return array(
                    "status"  => "success",
                    "data"    => $get_post->guid
                );
            }
        }
    }