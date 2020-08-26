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
  	class SP_List_Reviews {
          
          public static function listen(){
            return rest_ensure_response( 
                SP_List_Reviews::list_reviews()
            );
          }
    
        public static function list_reviews(){
            
			// Initialize WP global variable
            global $wpdb;

            $table_revision = SP_REVS_TABLE;
            $table_revision_fields= SP_REVS_TABLE_FIELDS;
            $table_reviews= SP_REVIEWS_TABLE;
            $table_reviews_fields = SP_REVIEWS_FIELDS;

            // Step 1: Check if prerequisites plugin are missing
            $plugin = SP_Globals::verify_prerequisites();
            if ($plugin !== true) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }

            // Step 2: Validate user
			if (DV_Verification::is_verified() == false) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Verification Issues!",
                );
            }

            // Step 3: If uid is not set, it means we are trying to get reviews of the logged user
            isset($_POST['uid']) ? $user_id = $_POST['uid'] : $user_id = $_POST['wpid'];

            $date = SP_Globals::date_stamp();
            
            // Step 4: Start mysql transaction
            $sql =  $wpdb->prepare("SELECT
                t1.wpid,
                AVG(t2.child_val) as `ave_rating`
            FROM
                sp_reviews t1
                INNER JOIN sp_revisions t2 ON t2.parent_id = t1.ID
            WHERE t1.wpid = %d AND t2.child_key = 'ratings'", $user_id);

            $results = $wpdb->get_row( $sql , OBJECT );
            
            // Step 5: Check if no rows found
            if ($results->wpid == NULL) {
                return array(
                    "status" => "success",
                    "message" => "This user does not have reviews yet."
                );
            }
        
            // Step 6: Return result
            return array(
                "status" => "success",
                "data" => $results
        
            );
       
       
        }
        

    }