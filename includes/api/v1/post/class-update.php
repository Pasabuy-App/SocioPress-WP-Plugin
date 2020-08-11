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

        public static function listen(){
            return rest_ensure_response( 
                SP_Update_Post:: list_open()
            );
        }
         
        public static function list_open(){
            
            // Initialize WP global variable
			global $wpdb;
			
            $user = SP_Update_Post::catch_post();
			
            // Step1 : Check if prerequisites plugin are missing
            $plugin = SP_Globals::verify_prerequisites();
            if ($plugin !== true) {
                return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }
            // Step2 : Check if wpid and snky is valid
            if (DV_Verification::is_verified() == false) {
                return array(
                        "status" => "unknown",
                        "message" => "Please contact your administrator. Verification issues!",
                );
			}

            // Step3 : Sanitize all request
            if (!isset($_POST["title"]) 
                || !isset($_POST["content"])
                ) {
				return array(
						"status" => "unknown",
						"message" => "Please contact your administrator. Request unknown!",
                ); 
            }

            // Step4 : Sanitize all variable is empty
            if (empty($_POST["title"]) 
                || empty($_POST["content"])
            ) {
                return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty.",
                );
            }
			
            // Step6 : Validation of post id
            $get_id = $wpdb->get_row("SELECT ID FROM wp_posts  WHERE ID = '{$user["post_id"]}' ");
            if ( !$get_id ) {
                return array(
                        "status" => "failed",
                        "message" => "No post found.",
                );
            }
            
            $update_post = array( 
                'ID' => $user["post_id"], 
                'post_title'=>$user["title"], 
                'post_content'=>$user["content"], 
                'post_status'=>$user["post_status"] 
            );

            // Step6 : Query
            $result = wp_update_post( $update_post );
			
            // Step7 : Check result if failed
            if ($result < 1) {
                return array(
                        "status" => "failed",
                        "message" => "An error occured while submitting data to database.",
                );
            }
            // Step8 : Return a success status and message 
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
