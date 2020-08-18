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

            $plugin = SP_Globals::verify_prerequisites();
            if ($plugin !== true) {

                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. ".$plugin." plugin missing.",
                );
            }

			if (DV_Verification::is_verified() == false) {
                
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Verification issues.",
                );
            }

            if (!isset($_POST['rid']) || !isset($_POST['rat']) || !isset($_POST['msg'])  ) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request Unknown!",
                );
            }

            if (empty($_POST['rid']) || empty($_POST['rat'])  ) {
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }

            if ( !is_numeric($_POST['rat']) ) {
                return array(
                    "status" => "failed",
                    "message" => "Invalid value for ratings.",
                );
            }

            $remarks =  trim($_POST['msg']);
            $ratings = trim($_POST['rat']);
            $ratings_recipient = $_POST['rid'];
            $wpid = $_POST['wpid'];
            $revs_type = 'reviews';
            $date = SP_Globals::date_stamp();
            
            $wpdb->query("START TRANSACTION");

                $wpdb->query("INSERT INTO $table_reviews $table_reviews_fields VALUES ('$ratings_recipient', '$wpid', '$date'  ) ");
                $parent_id = $wpdb->insert_id;

                $ratings_query = $wpdb->query("INSERT INTO $table_revision $table_revision_fields VALUES ('$revs_type', '$parent_id', 'ratings', '$ratings', '$wpid', '$date' ) ");
                
                if ( !empty($remarks) ) {
                   $wpdb->query("INSERT INTO $table_revision $table_revision_fields VALUES ('$revs_type', '$parent_id', 'remarks', '$remarks', '$wpid', '$date' ) ");
                   
                }
            
            if ( $parent_id < 1 || $ratings_query < 1){
                $wpdb->query("ROLLBACK");

                return array(
                    "status" => "error",
                    "message" => "An error occured while submitting data to server."
                );

            }else{
                $wpdb->query("COMMIT");

                return array(
                    "status" => "success",
                    "message" => "Data has been submitted successfully."
                );
            }

        }
        

    }