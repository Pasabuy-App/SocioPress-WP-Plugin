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
  	class SP_Mark_all_as_read_Activity {
        public static function listen(){
            return rest_ensure_response(
                self::get_list_of_activty()
            );
        }

        public static function get_list_of_activty(){
            global $wpdb;
            $table_activity = SP_ACTIVITY_TABLE;
            $date = SP_Globals::date_stamp();

            $plugin = SP_Globals::verify_prerequisites();
            if ($plugin !== true) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }

			//  Step2 : Validate user
			if (DV_Verification::is_verified() == false) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Verification issues!",
                );
            }

            $wpid = $_POST['wpid'];

            $result = $wpdb->query("UPDATE $table_activity SET `date_open` = '$date' WHERE wpid = $wpid ");

            if ($result == false) {
                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to server."
                );
            }else{
                return array(
                    "status" => "success",
                    "message" => "Data has been added successfully."
                );
            }
        }
    }