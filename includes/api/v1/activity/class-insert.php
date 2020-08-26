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
  	class SP_Insert_Activity {

          public static function listen(){
            return rest_ensure_response( 
                SP_Insert_Activity::insert_activty()
            );
          }
    
        public static function insert_activty(){

			// Initialize WP global variable
            global $wpdb;

            $table_revision = SP_REVS_TABLE;
            $table_revision_fields= SP_REVS_TABLE_FIELDS;
            $table_activity = SP_ACTIVITY_TABLE;
            $table_activity_fields = SP_ACTIVITY_FIELDS;

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
                    "message" => "Please contact your administrator. Verification Issues!",
                );
            }

            // Step 3: Check if required parameters are passed
            if (!isset($_POST['title']) 
                || !isset($_POST['info']) 
                || !isset($_POST['icon'])  ) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Missing paramiters!",
                );
            }

            // Step 4: Check if parameters passed are empty
            if (empty($_POST['title']) 
                || empty($_POST['info']) 
                || empty($_POST['icon'])  ) {
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }

            // Step 5: Check if parameters are valid
            if ($_POST['icon'] != 'warn' 
                && $_POST['icon'] != 'info' 
                && $_POST['icon'] != 'error' ) {
                return array(
                    "status" => "failed",
                    "message" => "Icon is not in valid format.",
                );
            }
            
            $user = SP_Insert_Activity::catch_post();
            $date = SP_Globals::date_stamp();
            $stid = 0;
            $wpid = $user['user_id'];
            
            // Step 6: Check if user or store
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
                $stid = $_POST['stid'];
                $wpid = 0;
            }
            
            // Step 7: Start mysql transaction
            $wpdb->query("START TRANSACTION");

                $wpdb->query("INSERT INTO $table_revision $table_revision_fields VALUES ('activity', 0, 'title', '{$user["activity_title"]}', '{$user["user_id"]}', '$date' ) ");
                $title_last_id = $wpdb->insert_id;

                $wpdb->query("INSERT INTO $table_revision $table_revision_fields VALUES ('activity', 0, 'info', '{$user["activity_info"]}', '{$user["user_id"]}', '$date' ) ");
                $info_last_id = $wpdb->insert_id;

                $wpdb->query("INSERT INTO $table_activity $table_activity_fields VALUES ('$wpid', '$stid', '{$user["activity_icon"]}', '$title_last_id', '$info_last_id', '{$user["user_id"]}', null, '$date' ) ");
                $activity_last_id = $wpdb->insert_id;

                $update_parent_id = $wpdb->query("UPDATE $table_revision SET `parent_id` = $activity_last_id WHERE ID IN ($title_last_id, $info_last_id) ");
            
            // Step 8: Check if any queries above failed
            if ($title_last_id < 1 || $info_last_id < 1 || $activity_last_id < 1 || $update_parent_id < 1) {
                $wpdb->query("ROLL BACK");
                return array(
                    "status" => "error",
                    "message" => "An error occured while submitting data to server."
                );
            }

            // Step 9: Commit if no errors found
            $wpdb->query("COMMIT");
            return array(
                "status" => "success",
                "message" => "Data has been added successfully."
            );

        }
        
        public static function catch_post(){

            $cur_user = array();

            $cur_user['user_id'] = $_POST['wpid'];
            $cur_user['activity_icon']  = $_POST['icon'];
            $cur_user['activity_title'] = $_POST['title'];
            $cur_user['activity_info']  = $_POST['info'];

            return  $cur_user;

        }
    }