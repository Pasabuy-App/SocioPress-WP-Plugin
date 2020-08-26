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
  	class SP_Transactions_List_Total {

          public static function listen(){
            return rest_ensure_response( 
                SP_Transactions_List_Total::total_transactions()
            );
          }
    
        public static function total_transactions(){
            
			// Initialize WP global variable
            global $wpdb;

            $table_revision = SP_REVS_TABLE;
            $table_revision_fields= SP_REVS_TABLE_FIELDS;
            $table_reviews= SP_REVIEWS_TABLE;
            $table_reviews_fields = SP_REVIEWS_FIELDS;
            $table_orders = MP_ORDERS_TABLE;
           
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
                    "message" => "Please contact your administrator. Verification issues!",
                );
            }

            // Step 3: If uid is not set, it means we are trying to get reviews of the logged user
            isset($_POST['uid']) ? $user_id = $_POST['uid'] : $user_id = $_POST['wpid'];
            
            $date = SP_Globals::date_stamp();

            // Step 4: Start mysql transaction
            $sql =  $wpdb->prepare("SELECT COUNT(ID) as transac
            FROM $table_orders
            WHERE wpid = %d OR created_by = %d", $user_id, $user_id);

            $results = $wpdb->get_row( $sql , OBJECT );
            
            // Step 5: Check if no rows found
            if ( !($results->transac) ) {
                return array(
                    "status" => "success",
                    "message" => "This user does not have transactions yet."
                );
            }
        
            // Step 6: Return result
            return array(
                "status" => "success",
                "data" => $results
        
            );
                   
        }
        

    }