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
				IF (post.post_type = 'pasabay', 'Pasabay', IF (post.post_type = 'sell', 'Selling', IF (post.post_type = 'pabili', 'Pabili', IF (post.post_type = 'pahatid', 'Pahatid', 'Status' )) ))  AS type
			FROM
				$table_post AS post
			INNER JOIN
				wp_users AS user ON post.post_author = user.ID
			WHERE
				post.post_type IN ('status', 'pasabay', 'sell', 'pahatid', 'pabili')  ";


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

			$limit = 12;

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

				$lastid = $_POST['lid'];
				$sql .= " AND post.id < $lastid ";
				$limit = 7;

			}


			// Step 6: Get results from database
			$sql .= " ORDER BY post.id DESC LIMIT $limit ";
			$result= $wpdb->get_results( $sql, OBJECT);
			$vars = array();
			foreach ($result as $key => $value) {

				if ($value->type === 'Selling') {

					$keys = array(
						'item_category',
						'vehicle_date',
						'time_price',
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
						if (isset($var[4]['data'])){
							$image = $var[4]['data'];
						}else{
							$image = $var[4];
						}
					}

					$values = array(
						'item_category' => $var[0],
						'vehicle_date' => $var[1],
						'time_price' => $var[2],
						'pickup_location' => $var[3],
						'item_image' => $image,
						'author' => $smp,
						'views' => $count_seen->views
					);

					$vars[] = array_merge((array)$value, $values);


				}else if($value->type === 'Pasabay'){

					$keys = array(
						'pickup_location',
						'vehicle_date',
						'time_price',
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
						if (isset($var[3]['data'])){
							$image = $var[3]['data'];
						}else{
							$image = $var[3];
						}
					}

					$values = array(
						'pickup_location' => $var[0],
						'vehicle_date' => $var[1],
						'time_price' => $var[2],
						'drop_off_location' => $var[3],
						'item_image' => $image,
						'author' => $smp,
						'views' => $count_seen->views
					);


						$vars[] = array_merge((array)$value, $values);

				}else if($value->type === 'Pahatid'){

					$keys = array(
						'pickup_location',
						'vehicle_date',
						'time_price',
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
						if (isset($var[3]['data'])){
							$image = $var[3]['data'];
						}else{
							$image = $var[3];
						}
					}

					$values = array(
						'pickup_location' => $var[0],
						'vehicle_date' => $var[1],
						'time_price' => $var[2],
						'drop_off_location' => $var[3],
						'item_image' => $image,
						'author' => $smp,
						'views' => $count_seen->views
					);


						$vars[] = array_merge((array)$value, $values);

				}else if($value->type === 'Pabili'){

					$keys = array(
						'pickup_location',
						'vehicle_date',
						'time_price',
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
						if (isset($var[3]['data'])){
							$image = $var[3]['data'];
						}else{
							$image = $var[3];
						}
					}

					$values = array(
						'pickup_location' => $var[0],
						'vehicle_date' => $var[1],
						'time_price' => $var[2],
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
						if (isset($get_meta['data'])){
							$image = $get_meta['data'];
						}else{
							$image = $get_meta;
						}
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
