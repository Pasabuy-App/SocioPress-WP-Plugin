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

            if(!class_exists('MP_Globals') && !class_exists('MP_Globals_v2')  ){
                return 'MobilePOS';
            }

            return true;

        }

        public static function get_post_thumbnail_id( $post_id = null ) {
            $post_id = ( null === $post_id ) ? get_the_ID() : $post_id;
            return get_post_meta( $post_id, '_thumbnail_id', true );
        }


        /**
         * Retrieve Post Thumbnail.
         *
         * @since 2.9.0
         *
         * @param int $post_id Optional. Post ID.
         * @param string $size Optional. Image size. Defaults to 'post-thumbnail'.
         * @param string|array $attr Optional. Query string or array of attributes.
         *
        */

        function get_the_post_thumbnail_url( $post = null, $size = 'post-thumbnail' ) {
    	        $post_thumbnail_id = get_post_thumbnail_id( $post );

    	        if ( ! $post_thumbnail_id ) {
    	                return false;
    	        }

    	        return wp_get_attachment_image_url( $post_thumbnail_id, $size );
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

        public static function Generate_Featured_Image( $image_url, $post_id  ){
            $upload_dir = wp_upload_dir();
            $image_data = file_get_contents($image_url);
            $filename = basename($image_url);
            if(wp_mkdir_p($upload_dir['path']))
              $file = $upload_dir['path'] . '/' . $filename;
            else
              $file = $upload_dir['basedir'] . '/' . $filename;
            file_put_contents($file, $image_data);

            $wp_filetype = wp_check_filetype($filename, null );
            $attachment = array(
                'post_mime_type' => $wp_filetype['type'],
                'post_title' => sanitize_file_name($filename),
                'post_content' => '',
                'post_status' => 'inherit'
            );
            $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
            $res1= wp_update_attachment_metadata( $attach_id, $attach_data );
            $res2= set_post_thumbnail( $post_id, $attach_id );
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