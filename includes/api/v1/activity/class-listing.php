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

            $table_revision = SP_REVS_TABLE;
            $table_activity = SP_ACTIVITY_TABLE;

            // Step 1: Check if prerequisites plugin are missing
            $plugin = SP_Globals::verify_prerequisites();
            if ($plugin !== true) {

                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }

			//  Step 2: Validate user
			if (DV_Verification::is_verified() == false) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Verification issues!",
                );
            }

            // Step 3: Pass the processed ids in a variable
            $id = $_POST['wpid'];
            isset($_POST['icon']) ? $icon = $_POST['icon'] : $icon = NULL;
            isset($_POST['stid']) ? $stid = $_POST['stid'] : $stid = NULL;
            isset($_POST['open']) ? $open = $_POST['open'] : $open = NULL;
            $open = $open  == '0' || $open == NULL ? NULL : ($open !== '0'? '0':'1');
            $icon = $pid  == '0' || $icon == NULL ? NULL: $icon = $icon;
            $stid = $stid  == '0' || $stid == NULL ? NULL: $stid = $stid;
            $user_id = 0;

            // Step 4: Check if the parameters is valid for icon
            if ( isset($_POST['icon']) && $_POST['icon'] != NULL && is_numeric($_POST['icon'])){
                if ($_POST['icon'] != 'warn' && $_POST['icon'] != 'info' && $_POST['icon'] != 'error' ) {
                    return array(
                        "status" => "failed",
                        "message" => "Icon is not in valid format.",
                    );
                }
            }
            
            // Step 5: Check if user or store
            if (isset($_POST['stid'])) {
                if ($stid != NULL) {
                    $user = 'stid';
                }else{
                    $user = 'wpid';
                }
            }else{
                $user = 'wpid';
            }

            $sql = "SELECT
                sp_act.ID,
                sp_act.$user,
                sp_act.icon,";

            if (isset($_POST['open'])) {
                if ($open != NULL) {
                    $sql .= "sp_act.date_open, ";
                }
            }
            
            $sql .= " ( SELECT sp_rev.child_val FROM $table_revision sp_rev WHERE sp_rev.ID = sp_act.`title` ) AS `activity_title`,
                ( SELECT sp_rev.child_val FROM $table_revision sp_rev WHERE sp_rev.ID = sp_act.`info` ) AS `activity_info`,
                sp_act.date_created 
            FROM
                $table_activity sp_act
            WHERE
                sp_act.wpid = '$id' ";

            if (isset($_POST['icon'])) {
                if ($icon != NULL) {
                    $sql .= " AND sp_act.icon = '$icon' ";
                }
            }

            if (isset($_POST['stid'])) {
                if ($stid != NULL) {
                    $sql .= " AND sp_act.stid = '$stid' ";
                }
            }

            if (isset($_POST['open'])) {
                if ($open != NULL || $open === '1') {
                    $sql .= " AND sp_act.date_open != '' ";
                }
            }

            if (!isset($_POST['lid'])){
                    
                $sql .= "GROUP BY sp_act.ID DESC  LIMIT 3 ";
                $result = $wpdb->get_results($sql, OBJECT);

                if(!$result){
                    return array(
                        "status" => "success",
                        "message" => "There is no activity found with this value.",
                    );
                }
    
				// Step 5: Pass the last id or the minimum id
                $last_id = min($result);
            }else{
                    
                if(!is_numeric($_POST["lid"])){
                    return array(
                        "status" => "failed",
                        "message" => "Parameters not in valid format!",
                    );
                }
                
                $lid = $_POST['lid'];
                $add_feeds = $lid - 7;
                $sql .= " AND sp_act.ID  BETWEEN $add_feeds AND ( $lid - 1 ) GROUP BY sp_act.ID DESC  LIMIT 3 ";
                $result = $wpdb->get_results($sql, OBJECT);
    
                //Step 7: Check if array count is 0 , return error message if true
                if (count($result) < 1) {
                    return array(
                        "status" => "success",
                        "message" => "No more activity to see.",
                    );
                } else {
                    $last_id = min($result);
                }
            }
    
            //Step 8: Return a success message and a complete object
            return array(
                "status" => "success",
                "data" => array( $result, $last_id
                )
            );

        }

    }