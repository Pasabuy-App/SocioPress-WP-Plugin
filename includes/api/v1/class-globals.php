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

  	class SP_Globals {

        public static function date_stamp(){
            return date("Y-m-d h:i:s");
		}

        public static function create($table_name, $data){
            global $wpdb;

            return $wpdb->insert($table_name, $data);

        }

        public static function verify_prerequisites(){

            if(!class_exists('DV_Verification') ){
                return 'DataVice';
            }

            if(!class_exists('MP_Process') ){
                return 'MobilePOS';
            }

            return true;

        }


        public static function check_by_field($table_name, $key, $value){

            global $wpdb;

            return $wpdb->get_row("SELECT ID
                FROM $table_name
                WHERE $key LIKE '%$value%'");

        }

        public static function insert_usermeta($user_id,  $firstName, $lastName){
            global $wpdb;
            return wp_update_user([
                'ID' => $userId,
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);

        }

        public static function user_create($username,  $email){
            $user_login = wp_slash( $username );
            $user_email = wp_slash( $email );
            $userdata = compact( 'user_login', 'user_email' );
            return wp_insert_user( $userdata );

        }

        public static function validate_user(){

            //User verification
             $verified = DV_Verification::listen();

            //Convert object to array
            $array =  (array) $verified;

            // Pass request status in a variable
            $response =  $array['data']['status'];

            if ($response != 'success') {
                    return $verified;
            } else {
                    return true;
            }
        }

        public static function seen_post($wpid, $post_id){
            global $wpdb;
            $table_seen_post = SP_POST_SEEN;
            $table_seen_post_fields = SP_POST_SEEN_FIELDS;

            // Get post data
            $post = get_post( $post = $post_id, $output = OBJECT, $filter = 'raw' );

            // validate if wpid = post author
            if ($post->post_author == $wpid) {
                return false;
            }

            // Start query
            $wpdb->query("START TRANSACTION");
            $data = array();
            $get_seen_post = $wpdb->get_results("SELECT * FROM $table_seen_post WHERE post_id = $post_id ");

            if (!$get_seen_post) {
                $result = $wpdb->query("INSERT INTO $table_seen_post ($table_seen_post_fields) VALUES ($post_id, $wpid)");

            }else{

                foreach ($get_seen_post as $key => $value) {
                    if ($value->wpid == $wpid) {
                        return false;
                    }
                }
                $result = $wpdb->query("INSERT INTO $table_seen_post ($table_seen_post_fields) VALUES ($post_id, $wpid)");
            }

            if ($result == false) {
                $wpdb->query("ROLLBACK");
                return 'error';
            }else{
                $wpdb->query("COMMIT");
                return true;
            }

        }
    }