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
                self::insert_reviews()
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

            // Step 3: Check if required parameters are passed
            if ( !isset($_POST['rid']) || !isset($_POST['rat']) || !isset($_POST['msg'])  ) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step 4: Check if parameters passed are empty
            if ( empty($_POST['rid']) || empty($_POST['rat'])  ) {
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

            if ($_POST['rat'] !== '1' && $_POST['rat'] !== '2' && $_POST['rat'] !== '3' && $_POST['rat'] !== '4' && $_POST['rat'] !== '5' ) {
                return array(
                    "status" => "failed",
                    "message" => "Rate must be 1 to 5 only.",
                );
            }

            $date = SP_Globals::date_stamp();
            $remarks =  trim($_POST['msg']);
            $ratings = trim($_POST['rat']);
            $ratings_recipient = $_POST['rid'];
            $wpid = $_POST['wpid'];
            $revs_type = 'reviews';

            // Step 6: Start mysql transaction
            $wpdb->query("START TRANSACTION");

                $wpdb->query("INSERT INTO $table_reviews $table_reviews_fields VALUES ('$ratings_recipient', '$wpid', '$date'  ) "); // Insert rating into reviews
                $parent_id = $wpdb->insert_id;

                $wpdb->query("UPDATE $table_reviews SET hash_id = sha2($parent_id, 256) WHERE ID = $parent_id");

                $ratings_query = $wpdb->query("INSERT INTO $table_revision ($table_revision_fields) VALUES ('$revs_type', '$parent_id', 'ratings', '$ratings', '$wpid', '$date' ) "); // Insert into revisions
                $rev_id = $wpdb->insert_id;

                $wpdb->query("UPDATE $table_revision SET hash_id = sha2($rev_id, 256) WHERE ID = $rev_id");

                if ( !empty($remarks) ) { // Check remarks to insert into revision
                    $wpdb->query("INSERT INTO $table_revision ($table_revision_fields) VALUES ('$revs_type', '$parent_id', 'remarks', '$remarks', '$wpid', '$date' ) ");
                    $rev_id2 = $wpdb->insert_id;

                    $wpdb->query("UPDATE $table_revision SET hash_id = sha2($rev_id2, 256) WHERE ID = $rev_id2");
                }

             // Step 7: Check if any queries above failed
            if ( $parent_id < 1 || $ratings_query < 1){
                $wpdb->query("ROLLBACK");
                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to server."
                );
            }

            // Step 8: Commit if no errors found
            $wpdb->query("COMMIT");
            return array(
                "status" => "success",
                "message" => "Data has been added successfully."
            );
        }
    }