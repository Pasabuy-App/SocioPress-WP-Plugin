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
  	class SP_Profile_data {

        public static function listen(){
            return rest_ensure_response( 
                SP_Profile_data::get_profile_data()
            );
    
        }
         
        // REST API for getting the user data
        public static function get_profile_data(){

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

            // Step3 : Find user in db using wpid
            $wp_user = get_user_by("ID", $_POST['wpid']);
                    
            // Step4 : Return success status and complete object.
            return array(
                "status" => "success",
                "data" => array(
                        "uname" => $wp_user->data->user_nicename,
                        "dname" => $wp_user->data->display_name,
                        "email" => $wp_user->data->user_email,
                        "ro" => $wp_user->roles,
                        "fn" => $wp_user->first_name,
                        "ln" => $wp_user->last_name,
                        "av" => $wp_user->avatar
                    )
            );
            
        }// End of function initialize()

    }// End of class