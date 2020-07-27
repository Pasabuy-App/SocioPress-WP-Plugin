<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * @package sociopress-wp-plugin
     * @version 0.1.0
     * Here is where you add hook to WP to create our custom database if not found.
	*/
	
	function sp_dbhook_activate(){
		
		//Initializing wordpress global variable
		global $wpdb;

		//Passing from global defined variable to local variable
		$tbl_posts = POSTS_TABLE;

		//Database table creation for stores
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_posts'" ) != $tbl_posts) {
			$sql = "CREATE TABLE `".$tbl_posts."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`user_id` int(11) NOT NULL, ";
				$sql .= "`post_type` VARCHAR(50) NULL, ";
				$sql .= "`post_timestamp` datetime NOT NULL, ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}


	}

	add_action( 'activated_plugin', 'sp_dbhook_activate');