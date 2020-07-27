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
  	class SP_Activity {

        public static function activity_create(){

			// Initialize WP global variable
			global $wpdb;

			// Step 1: Check if ID is passed
			if ( !isset($_POST['wpid']) || !isset($_POST['snky']) || !isset($_POST['stid']) || !isset($_POST['icon']) ||  !isset($_POST['title']) ||  !isset($_POST['info'])) {
				return rest_ensure_response( 
					array(
						"status" => "unknown",
						"message" => "Please contact your administrator. Request unknown!",
					)
				);
			}

			// Step 2: Check if ID is in valid format (integer)
			if (!is_numeric($_POST["wpid"])) {
				return rest_ensure_response( 
					array(
						"status" => "failed",
						"message" => "Please contact your administrator. ID not in valid format!",
					)
				);
			}

			// Step 3: Check if ID exists
			if (!get_user_by("ID", $_POST['wpid'])) {
				return rest_ensure_response( 
					array(
						"status" => "failed",
						"message" => "User not found!",
					)
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
				return rest_ensure_response( 
					array(
						"status" => "Unknown",
						"message" => "Please contact your administrator. Activity Log Creation Failed",
					)
				);

			}else{
				return rest_ensure_response( 
					array(
						"status" => "Success",
						"message" => "Activity Log created Successfuly",
					)
				);

			}

		}

		public static function get_activity(){
			global $wpdb;
			if ( !isset($_GET['wpid']) || !isset($_GET['snky']) ) {
				return rest_ensure_response( 
					array(
						"status" => "unknown",
						"message" => "Please contact your administrator. Request unknown!",
					)
				);
			}

			// Step 2: Check if ID is in valid format (integer)
			if (!is_numeric($_GET["wpid"])) {
				return rest_ensure_response( 
					array(
						"status" => "failed",
						"message" => "Please contact your administrator. ID not in valid format!",
					)
				);
			}

			// Step 3: Check if ID exists
			if (!get_user_by("ID", $_GET['wpid'])) {
				return rest_ensure_response( 
					array(
						"status" => "failed",
						"message" => "User not found!",
					)
				);
            }
            
            if (!isset($_GET['lid'])){
                
                // Step 4: Pass the processed ids in a variable
                $id = $_GET['wpid'];

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
                
            	if(!is_numeric($_GET["lid"])){
					return rest_ensure_response( 
						array(
							"status" => "failed",
							"message" => "Parameters not in valid format!",
						)
					);

				}

                // Step 4: Pass the processed ids in a variable
                $id = $_GET['wpid'];
                $lid = $_GET['lid'];

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

		public static function get_activity_feed(){
			global $wpdb;

			if ( !isset($_GET['wpid']) || !isset($_GET['snky']) || !isset($_GET['lid']) ) {
				return rest_ensure_response( 
					array(
						"status" => "unknown",
						"message" => "Please contact your administrator. Request unknown!",
					)
				);
			}
			// Step 2: Check if ID is in valid format (integer)
			if (!is_numeric($_GET["wpid"])) {
				return rest_ensure_response( 
					array(
						"status" => "failed",
						"message" => "Please contact your administrator. ID not in valid format!",
					)
				);
			}

			// Step 3: Check if ID exists
			if (!get_user_by("ID", $_GET['wpid'])) {
				return rest_ensure_response( 
					array(
						"status" => "failed",
						"message" => "User not found!",
					)
				);
			}

		}

		public static function get_activity_byid(){
			global $wpdb;

			// Step 1: Check if ID is passed
			if (!isset($_GET["wpid"]) || !isset($_GET["snky"]) || !isset($_GET["atid"])) {
				return rest_ensure_response( 
					array(
						"status" => "unknown",
						"message" => "Please contact your administrator. Request unknown!",
					)
				);
			}
			// Step 2: Check if ID and Last ID is in valid format (integer)
			if ( !is_numeric($_GET["atid"]) || !is_numeric($_GET["wpid"])  ) {
				return rest_ensure_response( 
					array(
						"status" => "failed",
						"message" => "Parameters not in valid format!",
					)
				);
			}

			// Step 3: Check if ID exists
			if (!get_user_by("ID", $_GET['wpid'])) {
				return rest_ensure_response( 
					array(
						"status" => "failed",
						"message" => "User not found!",
					)
				);
            }

			// Step 4: Pass the processed ids in a variable
			$id = $_GET['wpid'];
			$activity_id = $_GET['atid'];
			
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