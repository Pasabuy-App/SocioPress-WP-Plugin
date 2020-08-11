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
                    sp_activity_revs.parent_id,
                    sp_activity.date_created,
                    MAX( IF ( sp_activity_revs.child_key = 'title', sp_activity_revs.child_val, '' ) ) AS title,
                    MAX( IF ( sp_activity_revs.child_key= 'info', sp_activity_revs.child_val, '' ) ) AS info 
                FROM
                    sp_activity_revs
                INNER JOIN sp_activity ON sp_activity.ID = sp_activity_revs.parent_id WHERE sp_activity.wpid = $id
                GROUP BY
                    sp_activity_revs.parent_id DESC LIMIT 12
                ", OBJECT);
    
                $last_id = min($result);
    
                //Step 8: Return a success message and a complete object
                return rest_ensure_response( 
                    array(
                        "status" => "success",
                        "data" => array(
                            'list' => $result,
                            'last_id' => $last_id
                        )
                    )
                );
    
            }else{
                    
                if(!is_numeric($_POST["lid"])){
                    return rest_ensure_response( 
                        array(
                            "status" => "failed",
                            "message" => "Parameters not in valid format!",
                        )
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
                    sp_activity_revs.parent_id,
                    sp_activity.date_created,
                    MAX( IF ( sp_activity_revs.child_key = 'title', sp_activity_revs.child_val, '' ) ) AS title,
                    MAX( IF ( sp_activity_revs.child_key= 'info', sp_activity_revs.child_val, '' ) ) AS info 
                FROM
                    sp_activity_revs
                INNER JOIN sp_activity ON sp_activity.ID = sp_activity_revs.parent_id WHERE sp_activity.wpid = $id 
                AND sp_activity.ID BETWEEN $add_feeds AND ( $lid - 1 )
                GROUP BY
                    sp_activity_revs.parent_id DESC
                ", OBJECT);
    
                //Step 7: Check if array count is 0 , return error message if true
                if (count($result) < 1) {
                    return rest_ensure_response( 
                        array(
                            "status" => "failed",
                            "message" => "No more posts to see",
                        )
                    );
                } else {
                    //Pass the last id
                    $last_id = min($result);
                }
                    
                //Step 8: Return a success message and a complete object
                return rest_ensure_response( 
                    array(
                        "status" => "success",
                        "data" => array(
                            'list' => $result, 
                            'last_id' => $last_id
                        )
                    )
                );
            }
    
        }

    }