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
  	class SP_Listing_Activity {
        public static function listen(){
            return rest_ensure_response( 
                SP_Listing_Activity::get_list_of_activty()
            );
        }

        public static function get_list_of_activty(){
            global $wpdb;
            if ( !isset($_POST['wpid']) || !isset($_POST['snky']) ) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown!",
                );
            }
    
            // Step 2: Check if ID is in valid format (integer)
            if (!is_numeric($_POST["wpid"])) {
                return array(
                    "status" => "failed",
                    "message" => "Please contact your administrator. ID not in valid format!",
                );
            }
    
            // Step 3: Check if ID exists
            if (!get_user_by("ID", $_POST['wpid'])) {
                return array(
                    "status" => "failed",
                    "message" => "User not found!",
                );
            }
                
            if (!isset($_POST['lid'])){
                    
                // Step 4: Pass the processed ids in a variable
                $id = $_POST['wpid'];
    
                //Step 5: Create table name for posts (bc_dv_activity)
                $table_activity = SP_PREFIX.'activity';
    
                //Step 6: Get results from database 
                $result= $wpdb->get_results("SELECT
                    sp_act.ID,
                    sp_act.wpid,
                    sp_act.icon,
                    ( SELECT sp_revisions.child_val FROM sp_revisions WHERE sp_revisions.ID = sp_act.`title` ) AS `activity_title`,
                    ( SELECT sp_revisions.child_val FROM sp_revisions WHERE sp_revisions.ID = sp_act.`info` ) AS `activity_info`,
                    sp_act.date_created 
                FROM
                    sp_activities sp_act
                WHERE
                    sp_act.wpid = 1 
                GROUP BY
                    sp_act.ID DESC 
                    LIMIT 12
                ", OBJECT);
    
                $last_id = min($result);
    
                //Step 8: Return a success message and a complete object
                return array(
                    "status" => "success",
                    "data" => array(
                        'list' => $result,
                        'last_id' => $last_id
                    )
                );
    
            }else{
                    
                if(!is_numeric($_POST["lid"])){
                    return array(
                        "status" => "failed",
                        "message" => "Parameters not in valid format!",
                    );
                }
    
                // Step 4: Pass the processed ids in a variable
                $id = $_POST['wpid'];
                $lid = $_POST['lid'];
    
                //Get 5 new posts
                $add_feeds = $lid - 5;
    
                //Step 5: Create table name for posts (bc_dv_activity)
                $table_activity = SP_PREFIX.'activity';
    
                //Step 6: Get results from database 
                $result= $wpdb->get_results("SELECT
                    ac_act.ID,
                    ac_act.wpid,
                    ac_act.icon,
                    ( SELECT sp_revisions.child_val FROM sp_revisions WHERE sp_revisions.ID = sp_activities.`title` ) AS `activity_title`,
                    ( SELECT sp_revisions.child_val FROM sp_revisions WHERE sp_revisions.ID = sp_activities.`info` ) AS `activity_info`,
                    ac_act.date_created 
                FROM
                    sp_activities ac_act 
                WHERE
                    ac_act.wpid = 1 
                BETWEEN $add_feeds AND ( $lid - 1 )
                GROUP BY
                    ac_act.ID DESC  LIMIT 12
                    ", OBJECT);
    
                //Step 7: Check if array count is 0 , return error message if true
                if (count($result) < 1) {

                    return array(
                        "status" => "failed",
                        "message" => "No more posts to see",
                    );

                } else {
                    //Pass the last id
                    $last_id = min($result);
                }
                    
                //Step 8: Return a success message and a complete object
                return array(
                    "status" => "success",
                    "data" => array(
                        'list' => $result, 
                        'last_id' => $last_id
                    )
                );
            }
    
        }

    }