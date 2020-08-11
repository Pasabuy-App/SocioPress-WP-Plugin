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

			// Step 1: Check if ID is passed
			if ( !isset($_POST['wpid']) || !isset($_POST['snky']) || !isset($_POST['stid']) || !isset($_POST['icon']) ||  !isset($_POST['title']) ||  !isset($_POST['info'])) {
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
			
			//Step 4: Pass the fields to a variable and array
			$id = $_POST['wpid'];
			$now = current_time( 'mysql' ); 
            $date = date( 'Y-m-d H:i:s', strtotime( $now ) + 3600 ); 

			// table variable 
			$table_name_activity = SP_PREFIX.'activity';
			$table_name_activity_revs = SP_PREFIX.'activity_revs';
			
			$activity_data = array(
				'wpid' => $id,
				'stid' => $_POST['stid'],
				'icon' => $_POST['icon'],
				'date_created' => $date
			);


			
			// insert activity
			$activity_result = SP_Globals::insert($table_name_activity,  $activity_data);


			// fetch last id of activity
			$last_activity_id = $wpdb->insert_id;

			// activity_revs array data
			$activity_revs_data_title = array(
				'parent_id' => $last_activity_id, 
				'child_key'	=>	'title',
				'child_val'	=> $_POST['title'],
				'date_stamp' => $date,
			);
			
			// insert revs for title
			$activity_revs_result = SP_Globals::insert($table_name_activity_revs,  $activity_revs_data_title);

			// last  insert id for title revs
			$last_activity_revs_id_title = $wpdb->insert_id;

			// update for info id of activity table
			$update_activity_title = $wpdb->update($table_name_activity, array('title' => $last_activity_revs_id_title ), array('ID' => $last_activity_id )  );
				

			// insert revs for info array daya
			$activity_revs_data_info = array(
				'parent_id' => $last_activity_id, 
				'child_key'	=>	'info',
				'child_val'	=> $_POST['info'],
				'date_stamp' => $date,
			);
			
			// insert revs for info
			$activity_revs_result = SP_Globals::insert($table_name_activity_revs,  $activity_revs_data_info);
			
			// last  insert id for title revs
			$last_activity_revs_id_info = $wpdb->insert_id;
			
			// update for info id of activity table
			$update_activity_info = $wpdb->update($table_name_activity, array('info' => $last_activity_revs_id_info), array('ID' => $last_activity_id)  );
			

			//Step 6: Return a success message and a complete object
			if ($activity_result < 0 || $activity_revs_result < 0 || $update_activity_title < 0 || $activity_revs_result < 0 || $update_activity_info < 0) {
				return array(
					"status" => "Unknown",
					"message" => "Please contact your administrator. Activity Log Creation Failed",
				);

			}else{
				return array(
					"status" => "Success",
					"message" => "Activity Log created Successfuly",
				);

			}

		}
    }