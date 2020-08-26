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
  	class SP_Insert_Reviews {
          
          public static function listen(){
            return rest_ensure_response( 
                SP_Insert_Reviews::insert_reviews()
            );
          }
    
        public static function insert_reviews(){
            
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
                    "message" => "Please contact your administrator. ".$plugin." plugin missing.",
                );
            }

            // Step 2: Validate user
			if (DV_Verification::is_verified() == false) {
                
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Verification Issues!",
                );
            }

            // Step 3: Check if required parameters are passed
            if ( !isset($_POST['rid']) 
                || !isset($_POST['rat']) 
                || !isset($_POST['msg'])  ) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request Unknown!",
                );
            }

            // Step 4: Check if parameters passed are empty
            if ( empty($_POST['rid']) 
                || empty($_POST['rat'])  ) {
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }

            // Step 5: Check if parameters is valid
            if ( !is_numeric($_POST['rat']) ) {
                return array(
                    "status" => "failed",
                    "message" => "Invalid value for ratings.",
                );
            }

            $date = SP_Globals::date_stamp();
            $remarks =  trim($_POST['msg']);
            $ratings = trim($_POST['rat']);
            $ratings_recipient = $_POST['rid'];
            $wpid = $_POST['wpid'];
            $revs_type = 'reviews';
            
            // Step 6: Query
            $wpdb->query("START TRANSACTION");

                $wpdb->query("INSERT INTO $table_reviews $table_reviews_fields VALUES ('$ratings_recipient', '$wpid', '$date'  ) "); // Insert rating into reviews
                $parent_id = $wpdb->insert_id;

                $ratings_query = $wpdb->query("INSERT INTO $table_revision $table_revision_fields VALUES ('$revs_type', '$parent_id', 'ratings', '$ratings', '$wpid', '$date' ) "); // Insert into revisions
                
                if ( !empty($remarks) ) { // Check remarks to insert into revision
                   $wpdb->query("INSERT INTO $table_revision $table_revision_fields VALUES ('$revs_type', '$parent_id', 'remarks', '$remarks', '$wpid', '$date' ) ");
                   
                }

             // Step 7: Check result if failed
            if ( $parent_id < 1 || $ratings_query < 1){
                $wpdb->query("ROLLBACK");
                return array(
                    "status" => "error",
                    "message" => "An error occured while submitting data to server."
                );

            }
            
            $wpdb->query("COMMIT");
            return array(
                "status" => "success",
                "message" => "Data has been submitted successfully."
            );

        }
        

    }