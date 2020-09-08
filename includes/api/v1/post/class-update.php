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
  	class SP_Update_Post {

        public static function listen(WP_REST_Request $request){
            return rest_ensure_response(
                SP_Update_Post:: list_open($request)
            );
        }

        public static function list_open($request){

            // Initialize WP global variable
			global $wpdb;

            $table_posts = WP_POSTS;

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
            if ( !isset($_POST["title"])
                || !isset($_POST["content"])
                || !isset($_POST["post_id"]) ) {
				return array(
					"status" => "unknown",
					"message" => "Please contact your administrator. Request unknown!",
                );
            }

            // Step 4: Check if parameters passed are empty
            if ( empty($_POST["title"]) || empty($_POST["content"]) || empty($_POST["post_id"]) ) {
                return array(
                    "status" => "failed",
                    "message" => "Required fields cannot be empty.",
                );
            }

            $user = SP_Update_Post::catch_post();

            // Step 5: Validation post
            $get_id = $wpdb->get_row("SELECT ID, post_type FROM $table_posts  WHERE ID = '{$user["post_id"]}' ");
            if ( !$get_id ) {
                return array(
                    "status" => "success",
                    "message" => "No post found.",
                );
            }

            if ($get_id->post_type === 'move') {
                if (!isset($_POST['item_name']) || !isset($_POST['vhl_type']) || !isset($_POST['pck_loc']) || !isset($_POST['dp_loc'])  ) {
                    return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Request unknown!",
                    );
                }

                if (empty($_POST['item_name']) || empty($_POST['vhl_type']) || empty($_POST['pck_loc']) || empty($_POST['dp_loc'])  ) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty.",
                    );
                }

            }elseif ($get_id->post_type === 'sell') {

                if (!isset($_POST['item_cat']) || !isset($_POST['item_name']) || !isset($_POST['vhl_type']) || !isset($_POST['item_dec']) || !isset($_POST['item_price']) || !isset($_POST['pic_loc'])  ) {
                    return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Request unknown!",
                    );
                }

                if (empty($_POST['item_cat']) || empty($_POST['item_name']) || empty($_POST['vhl_type']) || empty($_POST['item_dec']) || empty($_POST['item_price']) || empty($_POST['pic_loc'])  ) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty.",
                    );
                }
            }


            $validate = $wpdb->get_row("SELECT ID FROM $table_posts  WHERE ID = '{$user["post_id"]}' and post_status = 'trash'");
            if ( $validate ) {
                return array(
                    "status" => "success",
                    "message" => "This post is deleted.",
                );
            }

            $update_post = array(
                'ID'            =>$user["post_id"],
                'post_title'    =>$user["title"],
                'post_content'  =>$user["content"],
                'post_status'   =>$user["post_status"]
            );

            // Step 6: Start mysql transaction
            $result = wp_update_post( $update_post );

            if ($get_id->post_type === 'move') {

                $result1 = update_post_meta($result, 'item_name', $_POST['item_name']  );
                $result2 = update_post_meta($result, 'pickup_location', $_POST['pck_loc']  );
                $result3 = update_post_meta($result, 'vehicle_type', $_POST['vhl_type']  );
                $result4 = update_post_meta($result, 'drop_off_location', $_POST['dp_loc']  );

                $files = $request->get_file_params();

                if (isset($files['img'])) {

                    $image = DV_Globals::upload_image( $request, $files); // Call upload image function in globals

                    if ($result['status'] === 'failed') {
                        return array(
                            "status" => $result['status'],
                            "message" => $result['message']
                        );

                    }
                    $result5 = update_post_meta($result, 'item_image', $image  );
                }
            }

            if ($get_id->post_type === 'sell') {
                $result1 = update_post_meta($result, 'item_name', $_POST['item_name']  );
                $result2 = update_post_meta($result, 'item_category', $_POST['item_cat']  );
                $result3 = update_post_meta($result, 'vehicle_type', $_POST['vhl_type']  );
                $result4 = update_post_meta($result, 'item_description', $_POST['item_dec']  );
                $result5 = update_post_meta($result, 'item_price', $_POST['item_price']  );
                $result6 = update_post_meta($result, 'pickup_location', $_POST['pic_loc']  );

                $files = $request->get_file_params();

                if (isset($files['img'])) {

                    $image = DV_Globals::upload_image( $request, $files); // Call upload image function in globals

                    if ($result['status'] === 'failed') {
                        return array(
                            "status" => $result['status'],
                            "message" => $result['message']
                        );

                    }
                    $result5 = update_post_meta($result, 'item_image', $image  );
                }

            }

            if ($_POST['type'] === 'status') {
                $files = $request->get_file_params();

                if (isset($files['img'])) {

                    $image = DV_Globals::upload_image( $request, $files); // Call upload image function in globals

                    if ($result['status'] === 'failed') {
                        return array(
                            "status" => $result['status'],
                            "message" => $result['message']
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
                "message" => "Data has been updated successfully.",
            );
		}

        // Catch Post
        public static function catch_post()
        {
              $cur_user = array();

			   $cur_user['post_id'] = $_POST["post_id"];
               $cur_user['created_by'] = $_POST["wpid"];
               $cur_user['title'] = $_POST["title"];
               $cur_user['content'] = $_POST["content"];
               $cur_user['post_status'] = 'publish';
               $cur_user['comment_status'] = 'open';
               $cur_user['ping_status'] = 'open';
               $cur_user['post_type'] = 'user_post';

              return  $cur_user;
        }
    }
