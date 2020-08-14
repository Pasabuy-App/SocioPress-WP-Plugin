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

            // if(!class_exists('MP_Process') ){
            //     return 'MobilePOS';
            // }

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
        
    }