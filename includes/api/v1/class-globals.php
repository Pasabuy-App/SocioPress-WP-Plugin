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
                return "false";
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
                        return "false";
                    }
                }
                $result = $wpdb->query("INSERT INTO $table_seen_post ($table_seen_post_fields) VALUES ($post_id, $wpid)");
            }

            if ($result < 1) {
                $wpdb->query("ROLLBACK");
                return "error";
            }else{
                $wpdb->query("COMMIT");
                return "true";
            }

        }


        public static function custom_get_post_meta($post_id, $wpid, $wpdb, $post_author, $post_type, $variable){
			$_data = array();

			switch ($post_type) {
				case 'status':

						// Get post Image
                            #$get_meta = get_the_post_thumbnail_url(  $post_id, 'medium'  );
                            $variable['status']['item_image'] =  get_the_post_thumbnail_url(  $post_id, 'medium'  ) == false ? '' : get_the_post_thumbnail_url(  $post_id, 'medium'  );

							// if (isset($get_meta['data'])) {
							// 	$variable['status']['item_image'] = $get_meta['data'];
							// }else{
							// 	$variable['status']['item_image'] = $get_meta;
							// }
						// End

						// Get post author avatar
							$get_avatar = get_user_meta( $post_author,  $key = 'avatar', $single = false );
							empty($get_avatar)? $variable['status']['author'] = SP_PLUGIN_URL . "assets/default-avatar.png" : $variable['status']['author'] = $get_avatar[0];
						// End

                        // Import seen
                            $seen = SP_Globals::seen_post( $wpid, $post_id);
                            if ($seen == "error") {
                                return array(
                                    "status" => 'false',
                                    "message" => "Please contact your administrator. post seen error"
                                );
                            }
                        // End

                        // Get post seen
							$count_seen = $wpdb->get_row("SELECT COUNT(wpid) as views FROM {$variable["seen"]} WHERE post_id = '$post_id'  ");
							$variable['status']['views'] = $count_seen->views;
                        // End

						$_data = $variable['status'];
					break;

                case 'pasabay':

                    $avatar = get_user_meta( $post_author,  $key = 'avatar', $single = false );

                    // Get post details
                        foreach ($variable['pasabuy'] as $key => $value) {

                            $variable['pasabuy'][$key] = get_post_meta( $post_id, $key,  $single = true );
                        }
                        $variable['pasabuy']['item_image'] =  get_the_post_thumbnail_url(  $post_id, 'medium'  ) == false ? '' : get_the_post_thumbnail_url(  $post_id, 'medium'  );

                        if ($avatar != null && $avatar != 'false') {
                            $variable['pasabuy']['author'] = $avatar[0];
                        }else{
                            $variable['pasabuy']['author'] =  SP_PLUGIN_URL . "assets/default-avatar.png";
                        }
                    // End

                    // Import seen
                        $seen = SP_Globals::seen_post( $wpid, $post_id);
                        if ($seen == "error") {
                            return array(
                                "status" => 'false',
                                "message" => "Please contact your administrator. post seen error"
                            );
                        }
                    // End

                    // Get post seen
                        $count_seen = $wpdb->get_row("SELECT COUNT(wpid) as views FROM {$variable["seen"]} WHERE post_id = '$post_id'  ");
                        $variable['pasabuy']['views'] = $count_seen->views;
                    // End

					$_data = $variable['pasabuy'];
					break;

                case 'sell':

                    $avatar = get_user_meta( $post_author,  $key = 'avatar', $single = false );

                    // Get post details
                        foreach ($variable['selling'] as $key => $value) {

                            $variable['selling'][$key] = get_post_meta( $post_id, $key,  $single = true );
                        }
                        $variable['selling']['item_image'] =  get_the_post_thumbnail_url(  $post_id, 'medium'  ) == false ? '' : get_the_post_thumbnail_url(  $post_id, 'medium'  );

                        if ($avatar != null && $avatar != 'false') {
                            $variable['selling']['author'] = $avatar[0];
                        }else{
                            $variable['selling']['author'] =  SP_PLUGIN_URL . "assets/default-avatar.png";
                        }
                    // End

                    // Import seen
                        $seen = self::seen_post( $wpid, $post_id);
                        if ($seen == "error") {
                            return array(
                                "status" => "false",
                                "message" => "Please contact your administrator. Post seen error!"
                            );
                        }
                    // End

                    // Get post seen
                        $count_seen = $wpdb->get_row("SELECT COUNT(wpid) as views FROM {$variable["seen"]} WHERE post_id = '$post_id'  ");
                        $variable['selling']['views'] = $count_seen->views;
                    // End

                    $_data = $variable['selling'];
					break;

                case 'pahatid':

                    $avatar = get_user_meta( $post_author,  $key = 'avatar', $single = false );

                    // Get post details
                        foreach ($variable['pahatid'] as $key => $value) {

                            $variable['pahatid'][$key] = get_post_meta( $post_id, $key,  $single = true );
                        }
                        $variable['pahatid']['item_image'] =  get_the_post_thumbnail_url(  $post_id, 'medium'  ) == false ? '' : get_the_post_thumbnail_url(  $post_id, 'medium'  );
                        if ($avatar != null && $avatar != 'false') {
                            $variable['pahatid']['author'] = $avatar[0];
                        }else{
                            $variable['pahatid']['author'] =  SP_PLUGIN_URL . "assets/default-avatar.png";
                        }
                    // End

                    // Import seen
                        $seen = self::seen_post( $wpid, $post_id);
                        if ($seen == "error") {
                            return array(
                                "status" => "false",
                                "message" => "Please contact your administrator. Post seen error!"
                            );
                        }
                    // End

                    // Get post seen
                        $count_seen = $wpdb->get_row("SELECT COUNT(wpid) as views FROM {$variable["seen"]} WHERE post_id = '$post_id'  ");
                        $variable['pahatid']['views'] = $count_seen->views;
                    // End

                    $_data = $variable['pahatid'];
					break;

                case 'pabili':

                    $avatar = get_user_meta( $post_author,  $key = 'avatar', $single = false );

                    // Get post details
                        foreach ($variable['pabili'] as $key => $value) {

                            $variable['pabili'][$key] = get_post_meta( $post_id, $key,  $single = true );
                        }
                        $variable['pabili']['item_image'] =  get_the_post_thumbnail_url(  $post_id, 'medium'  ) == false ? '' : get_the_post_thumbnail_url(  $post_id, 'medium'  );

                        if ($avatar != null && $avatar != 'false') {
                            $variable['pabili']['author'] = $avatar[0];
                        }else{
                            $variable['pabili']['author'] =  SP_PLUGIN_URL . "assets/default-avatar.png";
                        }
                    // End

                    // Import seen
                        $seen = self::seen_post( $wpid, $post_id);
                        if ($seen == "error") {
                            return array(
                                "status" => "false",
                                "message" => "Please contact your administrator. Post seen error!"
                            );
                        }
                    // End

                    // Get post seen
                        $count_seen = $wpdb->get_row("SELECT COUNT(wpid) as views FROM {$variable["seen"]} WHERE post_id = '$post_id'  ");
                        $variable['pabili']['views'] = $count_seen->views;
                    // End

                    $_data = $variable['pabili'];
					break;
			}

			return array(
                "status" => "true",
                "data" => $_data
            );
        }

        public static function upload_image($request, $files){
			$data = array();
			foreach ($files as $key => $value) {

				$max_img_size = DV_Library_Config::dv_get_config('max_img_size', 123);
				if (!$max_img_size) {
					return array(
						"status" => "unknown",
						"message" => "Please contact your administrator. Can't find config of img size.",
					);
				}

				//Get the directory of uploading folder
				$target_dir = wp_upload_dir();

				//Get the file extension of the uploaded image
				$file_type = strtolower(pathinfo($target_dir['path'] . '/' . basename($files[$key]['name']),PATHINFO_EXTENSION));

				if (!isset($_POST['IN'])) {
					$img_name = $files[$key]['name'];

				} else {
					$img_name = sanitize_file_name($_POST['IN']);

				}

				$completed_file_name = sha1(date("Y-m-d~h:i:s"))."-". trim($img_name," ");

				$target_file = $target_dir['path'] . '/' . basename($completed_file_name);
				$uploadOk = 1;

				$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

				$check = getimagesize($files[$key]['tmp_name']);

				if($check !== false) {
					$uploadOk = 1;

				} else {
					$uploadOk = 0;
					return array(
						"status" => "failed",
						"message" => "File is not an image.",
					);
				}

				// Check if file already exists
				if (file_exists($target_file)) {
					//  file already exists
					$uploadOk = 0;
					return array(
						"status" => "failed",
						"message" => "File is already existed.",
					);
				}

				// Check file size
				if ($files[$key]['size'] > $max_img_size) {
					// file is too large
					$uploadOk = 0;
					return array(
						"status" => "failed",
						"message" => "File is too large.",
					);
				}

				// Allow certain file formats
				if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType !=
					"jpeg"
					&& $imageFileType != "gif" ) {
					//only JPG, JPEG, PNG & GIF files are allowed
					$uploadOk = 0;
					return array(
						"status" => "failed",
						"message" => "Only JPG, JPEG, PNG & GIF files are allowed.",
					);
				}

				// Check if $uploadOk is set to 0 by an error
				if ($uploadOk == 0) {
				// file was not uploaded.
					// if everything is ok, try to upload file
						return array(
							"status" => "unknown",
							"message" => "Please contact your admnistrator. File has not uploaded! ",
						);

				} else {//

					if (move_uploaded_file($files[$key]['tmp_name'], $target_file)) {

						$pic = $files[$key];
						$file_mime = mime_content_type( $target_file);

						$upload_id = wp_insert_attachment( array(
							'guid'           => $target_file,
							'post_mime_type' => $file_mime,
							'post_title'     => preg_replace( '/\.[^.]+$/', '', $pic['name'] ),
							'post_content'   => '',
							'post_status'    => 'inherit'
						), $target_file );

						// wp_generate_attachment_metadata() won't work if you do not include this file
						require_once( ABSPATH . 'wp-admin/includes/image.php' );

						$attach_data = wp_generate_attachment_metadata( $upload_id, $target_file );

						// Generate and save the attachment metas into the database
						wp_update_attachment_metadata( $upload_id, $attach_data );

						// Show the uploaded file in browser
						wp_redirect( $target_dir['url'] . '/' . basename( $target_file ) );

						//return file path
						$data[$key] = (string)$target_dir['url'].'/'.basename($completed_file_name);
						$data[$key.'_id'] = $upload_id;


					} else {
						//there was an error uploading your file
						return array(
							"status" => "unknown",
							"message" => "Please contact your admnistrator. File has not uploaded! ",
						);
					}
				}
				// End
			}
			// End loop

			return array(
				"status" => "success",
				"data" => array($data)

			);
        }

        public static function generating_hsid($primary_key, $table_name, $column_name, $get_key = false, $lenght = 64){
            global $wpdb;

            $sql = "UPDATE  $table_name SET $column_name = concat(
                substring('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand($primary_key)*4294967296))*36+1, 1), ";

            for ($i=0; $i < $lenght ; $i++) {
                $sql .= "substring('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed:=round(rand(@seed)*4294967296))*36+1, 1),";
            }

            $sql .=" substring('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ', rand(@seed)*36+1, 1)
                )
                WHERE ID = $primary_key;";


            $results = $wpdb->query($sql);

            if ($get_key = true) {
                $key  = $wpdb->get_row("SELECT `$column_name` as `key` FROM $table_name WHERE ID = '$primary_key' ");
                return $key->key;
            }

            if ($results < 1) {
				return false;
			}else{
				if ($results == 1) {
					return true;
				}
			}
        }


    }