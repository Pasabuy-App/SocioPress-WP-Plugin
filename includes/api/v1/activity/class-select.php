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

			// Step 4: Pass the processed ids in a variable
			$id = $_POST['wpid'];
			$activity_id = $_POST['atid'];
			
			$now = current_time( 'mysql' ); 
            $date = date( 'Y-m-d H:i:s', strtotime( $now ) + 3600 ); 

			//Step 5: Create table name for posts (bc_posts)
			$table_activity = SP_PREFIX.'activity';
			$table_activity_revs = SP_PREFIX.'activity_revs';


			 //Step 6: Get results from database 
			$result_title = $wpdb->get_results("SELECT
					sp_activity.ID,
					sp_activity.icon,
					sp_activity.date_created,
					sp_activity_revs.child_key,
					sp_activity_revs.child_val,
					sp_activity.wpid 
				FROM
			 		sp_activity_revs
					INNER JOIN sp_activity ON sp_activity.title = sp_activity_revs.ID 
				WHERE
					sp_activity.wpid = $id 
				AND sp_activity.ID = $activity_id  LIMIT 1", OBJECT);

			$result_info = $wpdb->get_results("SELECT
					sp_activity.ID,
					sp_activity.icon,
					sp_activity.date_created,
					sp_activity_revs.child_key,
					sp_activity_revs.child_val,
					sp_activity.wpid 
				FROM
					sp_activity_revs
					INNER JOIN sp_activity ON sp_activity.info = sp_activity_revs.ID 
				WHERE
					sp_activity.wpid = $id  
					AND sp_activity.ID = $activity_id LIMIT  1 ", OBJECT);

			// step 7 : conditions if query is correct
			if( !empty( $result_title)  || !empty($result_info )){

				// step 7.1 : conditions/sanitation if acitivity log ID is correct and existing in database
				if($result_info[0]->ID != $result_title[0]->ID){
					return rest_ensure_response( 
						array(
							"status" => "unknown",
							"message" => "Please contact your administrator. Request Activity Unknown!",
						)
					);

				}else{
					// step 8 : update date_open from dv_activity log  
					$result_activty_dateOpen =  $wpdb->update($table_activity, array('date_open' => $date ), array('ID' => $activity_id )  );

					// step 9 : check if update for date_open is successfuly updated!
					if($result_activty_dateOpen < 0 ){
						return rest_ensure_response( 
							array(
								"status" => "unknown",
								"message" => "Please contact your administrator. Request unknown!",
							)
						);

					}else{

						// step 10 : return success result  
						return rest_ensure_response( 
							array(
								"status" => "success",
								"data" => array(
									'list' => array(
										'id' => $result_title[0]->ID,
										'title' => $result_title[0]->child_val,
										'info' => $result_info[0]->child_val,
										'date_created' => $result_info[0]->child_val,
									) 
								)
							)
						);
					}

				}

			}else {
				// step 10: returm failed if activity log ID is not existed in wpid activity log list
				return rest_ensure_response( 
					array(
						"status" => "failed",
						"message" => "Please contact your administrator. Theres no activity in your log with this ID",
					)
				);
				
			}
				
        }
    }