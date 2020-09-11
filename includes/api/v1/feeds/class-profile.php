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
			if (DV_Verification::is_verified() == false) {
				return array(
					"status" => "unknown",
					"message" => "Please contact your administrator. Verification issues!",
				);
			}

			$id = $_POST['wpid'];

			// Step 3: Start mysql transaction
			$sql ="SELECT
				post.id,
				user.display_name AS name,
				post.post_author,
				post.guid as post_link,
				post_title as title,
				post.post_content AS content,
				post.post_date AS date_post,
				IF (post.post_type = 'move', 'Request', IF (post.post_type = 'sell', 'Selling', 'Status'))  AS type
			FROM
				$table_post AS post
			INNER JOIN
				wp_users AS user ON post.post_author = user.ID
			WHERE
				post.post_status = 'publish' AND post.post_type IN ('status', 'move', 'sell')  ";


			if (isset($_POST['user_id'])) {
				if (empty($_POST['user_id']) ) {
                    return array(
                        "status" => "failed",
                        "message" => "Required fields cannot be empty.",
                    );
				}

				if ( !is_numeric($_POST["user_id"])) {
					return array(
						"status" => "failed",
						"message" => "Parameters not in valid format.",
					);
				}
				$user_id = $_POST['user_id'];
				$sql .= " AND post.post_author = $user_id ";
			}else {
				$user_id = $_POST['wpid'];

				$sql .= " AND post.post_author = $user_id ";
			}

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
						'item_category',
						'vehicle_type',
						'item_price',
						'pickup_location',
						'item_image'
					);

					$var = array();
					for ($count=0; $count < count($keys) ; $count++) {
						$var[] = $get_meta = get_post_meta( $value->id, $keys[$count],  $single = true );
					}
					$avatar = get_user_meta( $value->post_author,'avatar', $single = false );

					$smp;
					if (!$avatar) {
						$smp = SP_PLUGIN_URL . "assets/default-avatar.png";
					}else{
						$smp = $avatar[0];
					}

					if ($value->post_author != $_POST['wpid']) {
						customSetPostViews($value->id);
					}
					$post_views_count = get_post_meta( $value->id, 'post_views_count', false );


					$image = '';
					if (!$get_meta) {
						$image = '';
					}else{
						$image = $var[4]['data'];
					}

					$values = array(
						'item_category' => $var[0],
						'vehicle_type' => $var[1],
						'item_price' => $var[2],
						'pickup_location' => $var[3],
						'item_image' => $image,
						'author' => $smp,
						'views' => $post_views_count[0]
					);


						$vars[] = array_merge((array)$value, $values);


				}elseif ($value->type === 'Request'){
					$keys = array(

						'pickup_location',
						'vehicle_type',
						'drop_off_location',
						'item_image'

					);

					$var = array();
					for ($count=0; $count < count($keys) ; $count++) {
						$var[] = $get_meta = get_post_meta( $value->id, $keys[$count],  $single = true );
					}

					$avatar = get_user_meta( $value->post_author,  $key = 'avatar', $single = false );
					$smp;
					if (!$avatar) {
						$smp = SP_PLUGIN_URL . "assets/default-avatar.png";
					}else{
						$smp = $avatar[0];
					}
					if ($value->post_author != $_POST['wpid']) {
						customSetPostViews($value->id);
					}
					$post_views_count = get_post_meta( $value->id, 'post_views_count', false );

					$image = '';
					if (!$get_meta) {
						$image = '';
					}else{
						$image = $var[3]['data'];
					}

					$values = array(
						'pickup_location' => $var[0],
						'vehicle_type' => $var[1],
						'drop_off_location' => $var[2],
						'item_image' => $image,
						'author' => $smp,
						'views' => $post_views_count[0]
					);


					$vars[] = array_merge((array)$value, $values);

				}elseif ($value->type === 'Status') {
					$get_meta = get_post_meta( $value->id, 'item_image',  $single = true );

					$avatar = get_user_meta( $value->post_author,  $key = 'avatar', $single = false );

					if ($value->post_author != $_POST['wpid']) {
						customSetPostViews($value->id);
					}
					$smp;
					if (!$avatar) {
						$smp = SP_PLUGIN_URL . "assets/default-avatar.png";
					}else{
						$smp = $avatar[0];
					}
					$post_views_count = get_post_meta( $value->id, 'post_views_count', false );

					$image = '';
					if (!$get_meta) {
						$image = '';
					}else{
						$image = $get_meta['data'];
					}
					 $values = array(
						'item_image' => $image,
						'author' => $smp,
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
