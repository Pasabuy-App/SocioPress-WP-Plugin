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
  	class SP_Select_Activity {
        public static function listen(){
            return rest_ensure_response( 
                SP_Select_Activity::get_activity_byid()
            );
        }
    
		public static function get_activity_byid(){
            global $wpdb;

            $table_revision = SP_REVS_TABLE;
            $table_activity = SP_ACTIVITY_TABLE;
            
            // Step1 : Check if prerequisites plugin are missing
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
                    "message" => "Please contact your administrator. Verification Issues!",
                );
            }

			// Step 3: Check if ID is passed
			if ( !isset($_POST["atid"])) {
				return array(
					"status" => "unknown",
					"message" => "Please contact your administrator. Request unknown!",
				);
            }
            
			// Step 4: Check if ID and Last ID is in valid format (integer)
			if ( !is_numeric($_POST["atid"])  ) {
				return array(
					"status" => "failed",
					"message" => "Parameters not in valid format.",
				);
			}

            $date = SP_Globals::date_stamp();
            $activity_id = $_POST['atid'];
            $user = 'wpid';
            $user_id = $_POST['wpid'];
            
            // Step 5: Check if user or store and validate parameter
            if( isset($_POST['stid']) ){
                if (empty($_POST['stid']) ) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty.",
                    );
                }
                if ( !is_numeric($_POST['stid']) ) {
                    return array(
                        "status" => "failed",
                        "message" => "ID is not in valid format.",
                    );
                }
                $user = 'stid';
                $user_id = $_POST['stid'];
            }
            
            // Step 6: Start mysql transaction
            $result = $wpdb->get_row("SELECT
                ac_act.ID,
                ac_act.$user,
                ac_act.icon,
                ( SELECT sp_rev.child_val FROM $table_revision sp_rev WHERE sp_rev.ID = ac_act.`title` ) AS `activity_title`,
                ( SELECT sp_rev.child_val FROM $table_revision sp_rev WHERE sp_rev.ID = ac_act.`info` ) AS `activity_info`,
                ac_act.date_created 
            FROM
                $table_activity ac_act 
            WHERE
                ac_act.$user = '$user_id' AND ac_act.ID = '$activity_id'
            GROUP BY
                ac_act.ID DESC 
                LIMIT 12");

            // Step 7: Check if no rows found
            if (!$result) {
                return array(
					"status" => "success",
					"message" => "There is no activity found with this value.",
                );
            }
            
            // Step 8: Insert date open 
            $update_date_open = $wpdb->query("UPDATE $table_activity SET date_open = '$date' WHERE ID = $activity_id ");

			// Step 9: Check if any queries above failed
            if($update_date_open  < 1 ){
                return array(
                    "status" => "error",
                    "message" => "An error occured while submitting data to server."
                );
            }
                
            // step 10 : Commit if no errors found 
            return array(
                "status" => "success",
                "data" => $result
            );	
        }
    }