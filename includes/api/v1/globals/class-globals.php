<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/** 
        * @package datavice-wp-plugin
		* @version 0.1.0
		* This is the primary gateway of all the rest api request.
	*/
?>
<?php
  	class SP_Globals {
         
        public static function create($table_name, $data){
            global $wpdb;
        
            return $wpdb->insert($table_name, $data);
                       
        }
        
        // NOTE: unfinished
        public static function retrieve($table_name, $fields, $sort_field, $sort){
            global $wpdb;
            // fields
            $data = implode( ', ', $fields );
            
            // sort_fields
            $str_sortFiled = implode( ', ', $sort_field );
            $sorted_field = preg_replace('/[0-9,]+/', '', $str_sortFiled);
            
            // sort
            $sorted = implode( ', ', $sort );
            // $sorts = preg_replace('/[0-9,]+/', '', $str_sort);


            return $wpdb->get_results("SELECT $data FROM $table_name $sorted_field $sorted ");
        }

        /**
         * Not working 
         
            *public static function retrieveById($table_name, $fields, $id){
            *    global $wpdb;
            *    $data = implode( ', ', $fields );
            *    return $data;
            *    // return $wpdb->get_results("SELECT $data FROM $table_name WHERE id = $id ");
            *}
         */

        public static function delete($table_name , $id){
            global $wpdb;
        
            return $wpdb->delete( $table_name, array( 'id' => $id ) );

        }

        public static function update($table_name, $id, $fields){
            global $wpdb;
            
            return $wpdb->update( $table_name , $fields, array('id' => $id) );
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

        public static function signup($user_table_name, $data){
            global $wpdb;
            return $wpdb->insert($user_table_name, $data);
        }

        // new
        public static function insert($table_name,  $fields){
            global $wpdb;
            return $wpdb->insert($table_name,  $fields);
        }
        
    }
?>