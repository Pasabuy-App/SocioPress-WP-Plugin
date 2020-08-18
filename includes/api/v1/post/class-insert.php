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

        public static function listen(){
            return rest_ensure_response( 
                SP_Insert_Post:: list_open()
            );
        }
         
        public static function list_open(){
            
            // Initialize WP global variable
			global $wpdb;
            
            $user = SP_Insert_Post::catch_post();
			
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
                        "message" => "Please contact your administrator. Verification Issues!",
                );
			}

            // Step 3: Check if required parameters are passed
            if (!isset($_POST["title"]) 
				|| !isset($_POST["content"])
				|| !isset($_POST["type"])
                ) {
				return array(
						"status" => "unknown",
						"message" => "Please contact your administrator. Request unknown!",
                ); 
            }

            // Step 4: Check if parameters passed are empty
            if (empty($_POST["title"]) 
				|| empty($_POST["content"])
				|| empty($_POST["type"])
            ) {
                return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty.",
                );
            }

            // Step 5: Ensure that type is correct
            if ( !($_POST["type"] === 'move') 
                && !($_POST["type"] === 'sell')
                && !($_POST["type"] === 'status') ) {
                return array(
                    "status" => "failed",
                    "message" => "Invalid post type.",
                );
            }
			
			$insert_post = array(
				'post_author'	=>$user["created_by"],
				'post_title'	=>$user["title"], 
				'post_content'	=>$user["content"], 
				'post_status'	=>$user["post_status"], 
				'comment_status'=>$user["comment_status"],
				'ping_status'	=>$user["ping_status"], 
				'post_type'		=>$user["post_type"]
			);

            // Step 6: Query
			$result = wp_insert_post($insert_post);
			
            // Step 7: Check result if failed
            if ($result < 1) {
                return array(
                    "status" => "failed",
                    "message" => "An error occured while submitting data to database.",
                );
            }

            // Step 8: Return a success status and message 
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
