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
  	class SP_Insert_Post {

        public static function listen(WP_REST_Request $request){
            return rest_ensure_response(
                self:: list_open($request)
            );
        }

        public static function list_open($request){

            // Initialize WP global variable
			global $wpdb;

            $user = self::catch_post();

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
            if ( !isset($_POST["title"])  || !isset($_POST["content"]) || !isset($_POST["type"]) ) {
				return array(
					"status" => "unknown",
					"message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step 4: Check if parameters passed are empty
            if ( empty($_POST["title"]) || empty($_POST["type"]) ) {
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }

            // Step 5: Ensure that type is correct
            if ( !($_POST["type"] === 'move')  
                && !($_POST["type"] === 'pabili') 
                && !($_POST["type"] === 'pahatid') 
                && !($_POST["type"] === 'sell') 
                && !($_POST["type"] === 'status') ) {
                return array(
                    "status" => "failed",
                    "message" => "Invalid post type.",
                );
            }

            if ($_POST['type'] === 'pahatid') {
                if ( !isset($_POST['pic_loc']) || !isset($_POST['dp_loc'])  || !isset($_POST['vhl_date']) || !isset($_POST['time_price']) ) {
                    return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Request unknown!",
                    );
                }

                if ( empty($_POST['pic_loc']) || empty($_POST['dp_loc'])  || empty($_POST['vhl_date']) || empty($_POST['time_price']) ) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty.",
                    );
                }

            }
            if ($_POST['type'] === 'pabili') {
                if ( !isset($_POST['pic_loc']) || !isset($_POST['vhl_date']) || !isset($_POST['time_price'])  ) {
                    return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Request unknown!",
                    );
                }

                if ( empty($_POST['pic_loc']) || empty($_POST['vhl_date']) || empty($_POST['time_price'])  ) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty.",
                    );
                }

            }
            if ($_POST['type'] === 'move') {
                if ( !isset($_POST['pic_loc']) || !isset($_POST['dp_loc']) || !isset($_POST['vhl_date']) || !isset($_POST['time_price'])  ) {
                    return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Request unknown!",
                    );
                }

                if (  empty($_POST['pic_loc']) || empty($_POST['dp_loc']) || empty($_POST['vhl_date']) || empty($_POST['time_price'])  ) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty.",
                    );
                }

            }elseif ($_POST['type'] === 'sell') {

                if (!isset($_POST['item_cat']) ||  !isset($_POST['vhl_date']) ||!isset($_POST['time_price']) || !isset($_POST['pic_loc']) ) {
                    return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Request unknown!",
                    );
                }

                if (empty($_POST['item_cat'])|| empty($_POST['vhl_date'])  || empty($_POST['time_price']) || empty($_POST['pic_loc']) ) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty.",
                    );
                }
            }

            if ($_POST['type'] === 'pabili') {

                // Insert WPPost
                    $insert_post = array(
                        'post_author'	=> $user["created_by"],
                        'post_title'	=> $user["title"],
                        'post_content'	=> $user["content"],
                        'post_status'	=> $user["post_status"],
                        'comment_status'=> $user["comment_status"],
                        'ping_status'	=> $user["ping_status"],
                        'post_type'		=> $user["post_type"]
                    );

                    // Step 6: Start mysql transaction
                    $result = wp_insert_post($insert_post);
                // End Insert WPPost
                $post_count = update_post_meta($result, 'post_views_count', '0'  );


                $data = array(
                    'pickup_location' => $_POST['pic_loc'], //location
                    'vehicle_date' => $_POST['vhl_date'],
                    'time_price' => $_POST['time_price'],
                );

                foreach ($data as $key => $value) {
                    $result1 = update_post_meta($result, $key, $value );
                }

                $files = $request->get_file_params();

                if (isset($files['img'])) {

                    $image = DV_Globals::upload_image( $request, $files); // Call upload image function in globals

                    if ($image['status'] === 'failed') {
                        return array(
                            "status" => $image['status'],
                            "message" => $image['message']
                        );

                    }
                    $result5 = update_post_meta($result, 'item_image', $image  );
                }
            }

            if ($_POST['type'] === 'pahatid') {

                // Insert WPPost
                    $insert_post = array(
                        'post_author'	=> $user["created_by"],
                        'post_title'	=> $user["title"],
                        'post_content'	=> $user["content"],
                        'post_status'	=> $user["post_status"],
                        'comment_status'=> $user["comment_status"],
                        'ping_status'	=> $user["ping_status"],
                        'post_type'		=> $user["post_type"]
                    );

                    // Step 6: Start mysql transaction
                    $result = wp_insert_post($insert_post);
                // End Insert WPPost
                $post_count = update_post_meta($result, 'post_views_count', '0'  );


                $data = array(
                    'pickup_location' => $_POST['pic_loc'], // pick up location
                    'drop_off_location' => $_POST['dp_loc'],// drop off location
                    'vehicle_date' => $_POST['vhl_date'],
                    'time_price' => $_POST['time_price'],
                );

                foreach ($data as $key => $value) {
                    $result1 = update_post_meta($result, $key, $value );
                }

                $files = $request->get_file_params();

                if (isset($files['img'])) {

                    $image = DV_Globals::upload_image( $request, $files); // Call upload image function in globals

                    if ($image['status'] === 'failed') {
                        return array(
                            "status" => $image['status'],
                            "message" => $image['message']
                        );

                    }
                    $result5 = update_post_meta($result, 'item_image', $image  );
                }
            }

            if ($_POST['type'] === 'move') {

                // Insert WPPost
                    $insert_post = array(
                        'post_author'	=> $user["created_by"],
                        'post_title'	=> $user["title"],
                        'post_content'	=> $user["content"],
                        'post_status'	=> $user["post_status"],
                        'comment_status'=> $user["comment_status"],
                        'ping_status'	=> $user["ping_status"],
                        'post_type'		=> $user["post_type"]
                    );

                    // Step 6: Start mysql transaction
                    $result = wp_insert_post($insert_post);
                // End Insert WPPost
                $post_count = update_post_meta($result, 'post_views_count', '0'  );


                $data = array(
                    'pickup_location' => $_POST['pic_loc'], //destination
                    //'vehicle_type' => $_POST['vhl_date'],
                    'drop_off_location' => $_POST['dp_loc'],// return place
                    'vehicle_date' => $_POST['vhl_date'],
                    'time_price' => $_POST['time_price'],
                );

                foreach ($data as $key => $value) {
                    $result1 = update_post_meta($result, $key, $value );
                }

                $files = $request->get_file_params();

                if (isset($files['img'])) {

                    $image = DV_Globals::upload_image( $request, $files); // Call upload image function in globals

                    if ($image['status'] === 'failed') {
                        return array(
                            "status" => $image['status'],
                            "message" => $image['message']
                        );

                    }
                    $result5 = update_post_meta($result, 'item_image', $image  );
                }
            }

            if ($_POST['type'] === 'sell') {

                // Insert WPPost
                    $insert_post = array(
                        'post_author'	=> $user["created_by"],
                        'post_title'	=> $user["title"],
                        'post_content'	=> $user["content"],
                        'post_status'	=> $user["post_status"],
                        'comment_status'=> $user["comment_status"],
                        'ping_status'	=> $user["ping_status"],
                        'post_type'		=> $user["post_type"]
                    );

                    // Step 6: Start mysql transaction
                    $result = wp_insert_post($insert_post);
                // End Insert WPPost
                $post_count = update_post_meta($result, 'post_views_count', '0'  );

                $data = array(
                    'item_category' => $_POST['item_cat'],
                    'vehicle_date' => $_POST['vhl_date'],
                    'time_price' => $_POST['time_price'],
                    'pickup_location' => $_POST['pic_loc']
                );

                foreach ($data as $key => $value) {
                    $result1 = update_post_meta($result, $key, $value );
                }


                $files = $request->get_file_params();

                if (isset($files['img'])) {

                     $image = DV_Globals::upload_image( $request, $files); // Call upload image function in globals

                    if ($image['status'] === 'failed') {
                        return array(
                            "status" => $image['status'],
                            "message" => $image['message']
                        );

                    }
                    $result5 = update_post_meta($result, 'item_image', $image  );
                }
            }

            if ($_POST['type'] === 'status') {

                // Insert WPPost
                    $insert_post = array(
                        'post_author'	=> $user["created_by"],
                        'post_title'	=> $user["title"],
                        'post_content'	=> $user["content"],
                        'post_status'	=> $user["post_status"],
                        'comment_status'=> $user["comment_status"],
                        'ping_status'	=> $user["ping_status"],
                        'post_type'		=> $user["post_type"]
                    );

                    // Step 6: Start mysql transaction
                    $result = wp_insert_post($insert_post);
                // End Insert WPPost
                $post_count = update_post_meta($result, 'post_views_count', '0'  );


                $files = $request->get_file_params();

                if (isset($files['img'])) {

                    $image = DV_Globals::upload_image( $request, $files); // Call upload image function in globals

                    if ($image['status'] === 'failed') {
                        return array(
                            "status" => $image['status'],
                            "message" => $image['message']
                        );

                    }
                    $result5 = update_post_meta($result, 'item_image', $image  );
                }
            }

            // Step 7: Check if any queries above failed
            if ($result < 1) {
                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to database.",
                );
            }

            // Step 8: Return result
            return array(
                "status" => "success",
                "message" => "Data has been added successfully.",
            );
		}

        // Catch Post
        public static function catch_post()
        {
            $cur_user = array();

            $cur_user['created_by']     = $_POST["wpid"];
            $cur_user['title']          = $_POST["title"];
            $cur_user['content']        = $_POST["content"];
            $cur_user['post_status']    = 'publish';
            $cur_user['comment_status'] = 'open';
            $cur_user['ping_status']    = 'open';
            $cur_user['post_type']      = $_POST["type"];

            return  $cur_user;
        }
    }
