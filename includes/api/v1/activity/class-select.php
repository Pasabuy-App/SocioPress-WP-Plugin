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

			// Step 1: Check if ID is passed
			if (!isset($_POST["wpid"]) || !isset($_POST["snky"]) || !isset($_POST["atid"])) {
				return array(
					"status" => "unknown",
					"message" => "Please contact your administrator. Request unknown!",
				);
			}
			// Step 2: Check if ID and Last ID is in valid format (integer)
			if ( !is_numeric($_POST["atid"]) || !is_numeric($_POST["wpid"])  ) {
				return array(
					"status" => "failed",
					"message" => "Parameters not in valid format!",
				);
			}

			// Step 3: Check if ID exists
			if (!get_user_by("ID", $_POST['wpid'])) {
				return array(
					"status" => "failed",
					"message" => "User not found!",
				);
            }

            $activity_id = $_POST['atid'];
            $wpid = $_POST['wpid'];

            $user_date = TP_Globals::get_user_date($_POST['wpid']);

             $result = $wpdb->get_row("SELECT
                ac_act.ID,
                ac_act.wpid,
                ac_act.icon,
                ( SELECT sp_rev.child_val FROM sp_revisions sp_rev WHERE sp_rev.ID = ac_act.`title` ) AS `activity_title`,
                ( SELECT sp_rev.child_val FROM sp_revisions sp_rev WHERE sp_rev.ID = ac_act.`info` ) AS `activity_info`,
                ac_act.date_created 
            FROM
                sp_activities ac_act 
            WHERE
                ac_act.wpid = $wpid AND ac_act.ID = $activity_id
            GROUP BY
                ac_act.ID DESC 
                LIMIT 12");

            if (!$result) {
                return array(
					"status" => "unknown",
					"message" => "Please contact your administrator. Request Activity Unknown!",
				);
            }else{
                 $update_date_open = $wpdb->query("UPDATE sp_activities SET date_open = '$user_date' WHERE ID = $activity_id ");

				// step 9 : check if update for date_open is successfuly updated!
                if($update_date_open  < 1 ){
                    return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Request unknown!",
                    );

                }else{

                    // step 10 : return success result  
                    return array(
                        "status" => "success",
                        "data" => $result
                    );
                }
                
            }
            

				
        }
    }