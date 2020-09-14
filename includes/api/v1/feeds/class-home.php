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

        public static function listen(){
            return rest_ensure_response(
                SP_Homefeed:: list_open()
            );
        }

        public static function list_open(){

			// Initialize WP global variable
			global $wpdb;

			$table_post = WP_POSTS;
            $table_seen_post = SP_POST_SEEN;

			// Step 1: Check if prerequisites plugin are missing
			$plugin = SP_Globals::verify_prerequisites();
			if ($plugin !== true) {
				return array(
					"status" => "unknown",
					"message" => "Please contact your administrator. ".$plugin." plugin missing!",
				);
			}


			// Step 2: Validate user
			// if (DV_Verification::is_verified() == false) {
			//  	return array(
			// 		"status" => "unknown",
			// 		"message" => "Please contact your administrator. Verification issues!",
			//  	);
			// }

			// Step 3: Start mysql transaction
			$sql = "SELECT
				post.id,
				post.post_author,
				user.display_name AS `name`,
				user.user_status AS `status`,
				post.post_title AS title,
				post.post_content AS content,
				post.post_date AS date_post,

				IF (post.post_type = 'move', 'Request', IF (post.post_type = 'sell', 'Selling', 'Status'))  AS type
			FROM
				$table_post AS post
			INNER JOIN
				wp_users AS user ON post.post_author = user.ID
			WHERE
				post.post_status = 'publish'
			AND
				post.post_type IN ('status', 'move', 'sell')  ";

			if( isset($_POST['lid']) ){

				// Step 4: Validate parameter
					if (empty($_POST['lid']) ) {
						return array(
							"status" => "failed",
							"message" => "Required fields cannot be empty.",
						);
					}
					if(!is_numeric($_POST["lid"])){
						return array(
							"status" => "failed",
							"message" => "Parameters not in valid format.",
						);
					}

				// Step 5: Pass the post in variable and continuation of query
					$get_last_id = $_POST['lid'];
					$add_feeds = $get_last_id - 7;
					$sql .= " AND post.id BETWEEN $add_feeds AND  ($get_last_id - 1) ";

			}

			// Step 6: Get results from database
			$sql .= " ORDER BY post.id DESC LIMIT 12 ";
			$result = $wpdb->get_results( $sql, OBJECT);

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

					$seen = SP_Globals::seen_post( $_POST['wpid'], $value->id);

					if ($seen === 'error') {
						return array(
							"status" => "unknown",
							"message" => "Please contact your administrator. post seen error"
						);
					}

					$avatar = get_user_meta( $value->post_author,  $key = 'avatar', $single = false );
					$count_seen = $wpdb->get_row("SELECT COUNT(wpid) as views FROM $table_seen_post WHERE post_id = $value->id  ");
					$smp;
					$image = '';

					if (!$avatar) {
						$smp = SP_PLUGIN_URL . "assets/default-avatar.png";

					}else{
						$smp = $avatar[0];

					}

					if ($get_meta) {
						$image = $var[4]['data'];
					}

					$values = array(
						'item_category' => $var[0],
						'vehicle_type' => $var[1],
						'item_price' => $var[2],
						'pickup_location' => $var[3],
						'item_image' => $image,
						'author' => $smp,
						'views' => $count_seen->views
					);

					$vars[] = array_merge((array)$value, $values);


				}else if($value->type === 'Request'){

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

					$seen = SP_Globals::seen_post( $_POST['wpid'], $value->id);

					if ($seen === 'error') {
						return array(
							"status" => "unknown",
							"message" => "Please contact your administrator. post seen error"
						);
					}

					$count_seen = $wpdb->get_row("SELECT COUNT(wpid) as views FROM $table_seen_post WHERE post_id = $value->id  ");

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
						'views' => $count_seen->views
					);


						$vars[] = array_merge((array)$value, $values);

				}elseif ($value->type === 'Status') {
					$get_meta = get_post_meta( $value->id, 'item_image',  $single = true );

					$avatar = get_user_meta( $value->post_author,  $key = 'avatar', $single = false );

					$seen = SP_Globals::seen_post( $_POST['wpid'], $value->id);

					if ($seen === 'error') {
						return array(
							"status" => "unknown",
							"message" => "Please contact your administrator. post seen error"
						);
					}

					$count_seen = $wpdb->get_row("SELECT COUNT(wpid) as views FROM $table_seen_post WHERE post_id = $value->id  ");

					$smp = '';
					if (!$avatar) {
						$smp = SP_PLUGIN_URL . "assets/default-avatar.png";
					}else{
						$smp = $avatar[0];
					}

					$image = '';
					if (!$get_meta) {
						$image =  '' ;
					}else{
						$image = $get_meta['data'];
					}
					 $values = array(
						'item_image' => $image,
						'author' => $smp,
						'views' => $count_seen->views
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
