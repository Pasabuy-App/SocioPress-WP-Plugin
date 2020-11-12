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
  	class SP_Homefeed {

		public static function variables(){
			// Initialize WP global variable

			$variables = array();
			$variables['tbl_post'] = WP_POSTS;
			$variables['seen'] = SP_POST_SEEN;

			$variables['pabili'] = array(
				'pickup_location' => '',
				'vehicle_date' => '',
				'time_price' => '',
				'item_image' => '',
				'author' => '',
				'views' => ''
			);

			$variables['status'] = array(
				'item_image' => '',
				'author' => '',
				'views' => ''
			);

			$variables['pahatid'] = array(
				'pickup_location' => '',
				'vehicle_date' => '',
				'time_price' => '',
				'drop_off_location' => '',
				'item_image' => '',
				'author' => '',
				'views' => ''
			);

			$variables['selling'] = array(
				'item_category' => '',
				'vehicle_date' => '',
				'time_price' => '',
				'pickup_location' => '',
				'item_image' => '',
				'author' => '',
				'views' => ''
			);

			$variables['pasabuy'] = array(
				'pickup_location' => '',
				'vehicle_date' => '',
				'time_price' => '',
				'drop_off_location' => '',
				'item_image' => '',
				'author' => '',
				'views' => ''
			);

			return $variables;
		}

        public static function listen(){
            return rest_ensure_response(
                SP_Homefeed:: list_open()
            );
		}

		public static function catch_post(){
			$curl_user = array();

			isset($_POST['search']) && !empty($_POST['search'])? $curl_user['search'] =  $_POST['search'] :  $curl_user['search'] = null ;

			return $curl_user;
		}

        public static function list_open(){
			global $wpdb;

			// Get variables
			$variable = self::variables();
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

			// Step 3: Start mysql transaction
			$sql = "SELECT
				post.id,
				user.display_name AS `name`,
				post.post_author,
				post.guid as post_link,
				post.post_title AS title,
				post.post_content AS content,
				post.post_date AS date_post,
				user.user_status AS `status`,
				post.post_type AS 'type'
			FROM
				{$variable["tbl_post"]} AS post
			INNER JOIN
				wp_users AS user ON post.post_author = user.ID
			WHERE
				post.post_status = 'publish'
			AND
				post.post_type IN ('status', 'pasabay', 'sell', 'pahatid', 'pabili') ";

			if ( $user['search'] != null ) {
				$sql .= " AND post.post_type OR post.post_title OR post.post_content  LIKE '%{$user["search"]}%'  ";
			}

			$limit = " 12 OFFSET 0";

			if( isset($_POST['lid']) ){
				if ( !is_numeric($_POST["lid"])) {
					return array(
						"status" => "failed",
						"message" => "Parameters not in valid format.",
					);
				}

				$lastid = $_POST['lid'];
				$limit = " 7 OFFSET ".$lastid;
			}

			$sql .= " ORDER BY post.id DESC LIMIT ".$limit;
			$_data = $wpdb->get_results($sql);
			$post_data = array();
			// Filter data
				foreach ($_data as $key => $value) {

					$_post_data = SP_Globals::custom_get_post_meta($value->id, $_POST['wpid'], $wpdb, $value->post_author, $value->type, $variable);

					if ($_post_data['status'] == "false") {
						return array(
							"status" => false,
							"message" => $_post_data['message'],
						);
					}

					if ($value->type == "sell") {

						$value->type = "Selling";

					}else{

						$value->type = ucfirst($value->type);

					}

					$post_data[]  = array_merge((array)$value, $_post_data['data']);
				}
			// End

			return array(
				"status" => "success",
				"data" => $post_data
			);
		}
	}
