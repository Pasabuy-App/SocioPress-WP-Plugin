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
  	class SP_Newsfeed {

        public static function listen(){
            return rest_ensure_response(
                SP_Newsfeed:: list_open()
            );
        }

        public static function list_open(){

			// Initialize WP global variable
			global $wpdb;

			$table_post = WP_POSTS;

			// Step 1: Check if prerequisites plugin are missing
			$plugin = SP_Globals::verify_prerequisites();
			if ($plugin !== true) {
				return array(
					"status" => "unknown",
					"message" => "Please contact your administrator. ".$plugin." plugin missing!",
				);
			 }

			// Step 2: Validate user
		/* 	if (DV_Verification::is_verified() == false) {
				return array(
					"status" => "unknown",
					"message" => "Please contact your administrator. Verification issues!",
				);
			} */

			$id = $_POST['wpid'];

			// Step 3: Start mysql transaction
			$sql ="SELECT
				post.id, post.post_content AS content, post.post_date AS date_created,
				IF (post.post_type = 'move', 'Request', IF (post.post_type = 'sell', 'Selling', 'Status'))  AS type
			FROM
				$table_post AS post
			WHERE
				post.post_author = $id
			AND
				post.post_status = 'publish' AND post.post_type IN ('status', 'move', 'sell')  ";

			if( isset($_POST['lid']) ){

				// Step 4: Validate parameter
                if (empty($_POST['lid']) ) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty.",
                    );
                }
				if ( !is_numeric($_POST["lid"])) {
					return array(
						"status" => "failed",
						"message" => "Parameters not in valid format.",
					);
				}

				// Step 5: Pass the post in variable and continuation of query
				$get_last_id = $_POST['lid'];
				$add_feeds = $get_last_id - 7;
				$sql .= " AND  post.id BETWEEN $add_feeds  AND  ($get_last_id - 1) ";

			}

			// Step 6: Get results from database
			$sql .= " ORDER BY post.id DESC LIMIT 12 ";
			$result= $wpdb->get_results( $sql, OBJECT);
			$vars = array();
			foreach ($result as $key => $value) {

				if ($value->type === 'Selling') {
					$keys = array(
						'item_name',
						'item_category',
						'vehicle_type',
						'item_description',
						'item_price',
						'pickup_location'
					);

					$var = array();
					for ($count=0; $count < count($keys) ; $count++) {
						$var[] = $get_meta = get_post_meta( $value->id, $keys[$count],  $single = true );
					}

					$avatar = get_user_meta( $_POST['wpid'],  $key = 'avatar', $single = false );
					customSetPostViews($value->id);

					$values = array(
						'item_name' => $var[0],
						'item_category' => $var[1],
						'vehicle_type' => $var[2],
						'item_description' => $var[3],
						'item_price' => $var[4],
						'pickup_location' => $var[5],
						'author' => $avatar[0],
						'views' => $post_views_count[0]
					);


						$vars[] = array_merge((array)$value, $values);


				}else{
					$keys = array(
						'item_name',
						'pickup_location',
						'vehicle_type',
						'drop_off_location'
					);

					$var = array();
					for ($count=0; $count < count($keys) ; $count++) {
						$var[] = $get_meta = get_post_meta( $value->id, $keys[$count],  $single = true );
					}

					$avatar = get_user_meta( $_POST['wpid'],  $key = 'avatar', $single = false );

					customSetPostViews($value->id);

					$values = array(
						'item_name' => $var[0],
						'pickup_location' => $var[1],
						'vehicle_type' => $var[2],
						'drop_off_location' => $var[3],
						'author' => $avatar[0],
						'views' => $post_views_count[0]
					);


						$vars[] = array_merge((array)$value, $values);

				}
			}




			// Step 9: Return result
			return array(
				"status" => "success",
				"data" => $vars
			);
		}
    }
