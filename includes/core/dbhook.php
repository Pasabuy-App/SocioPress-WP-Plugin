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
		$tbl_act = SP_ACTIVITY_TABLE;
		$tbl_configs = SP_CONFIGS_TABLE;
		$tbl_revs = SP_REVS_TABLE;
		$tbl_mess = SP_MESSAGES_TABLE;
		$tbl_market = SP_MARKET_TABLE;
		$tbl_reacts = SP_REACTS_TABLE;
		$tbl_reviews = SP_REVIEWS_TABLE;
		$tbl_seen_post = SP_POST_SEEN;

		// Database table creation for seen post
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_seen_post'" ) != $tbl_seen_post) {
			$sql = "CREATE TABLE `".$tbl_seen_post."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`post_id` bigint(20) NOT NULL COMMENT 'psot id', ";
				$sql .= " `wpid` bigint(20) NOT NULL COMMENT 'user who seen this post',";
				$sql .= " `date_created` datetime NOT NULL DEFAULT current_timestamp() COMMENT 'date seen createad', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}

		//Database table creation for activities
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_act'" ) != $tbl_act) {
			$sql = "CREATE TABLE `".$tbl_act."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`hash_id` varchar(255) NOT NULL , ";
				$sql .= "`wpid` bigint(20) NOT NULL DEFAULT 0 COMMENT 'User ID, 0 if Null', ";
				$sql .= "`stid` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Store ID, 0 if Null', ";
				$sql .= "`icon` enum('info','warn','error') DEFAULT 'info' COMMENT 'General type of activity.', ";
				$sql .= "`title` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Title or summary of the activity with revision ID.', ";
				$sql .= "`info` bigint(20) NOT NULL DEFAULT 0 COMMENT 'The content of this activity with revision ID.', ";
				$sql .= "`date_open` datetime DEFAULT NULL COMMENT 'If NUll, activity is still unread.', ";
				$sql .= "`date_created` datetime DEFAULT NULL COMMENT 'Date the activity log was created.', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}

		//Database table creation for configs
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_configs'" ) != $tbl_configs) {
			$sql = "CREATE TABLE `".$tbl_configs."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`hash_id` varchar(255) NOT NULL , ";
				$sql .= "`config_desc` varchar(255) NOT NULL COMMENT 'Config Description', ";
				$sql .= "`config_key` varchar(50) NOT NULL COMMENT 'Config KEY', ";
				$sql .= "`config_value` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Config VALUES', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}

		//Database table creation for messages
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_mess'" ) != $tbl_mess) {
			$sql = "CREATE TABLE `".$tbl_mess."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`hash_id` varchar(255) NOT NULL , ";
				$sql .= "`content` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Parent ID of Content revision', ";
				$sql .= "`sender` bigint(20) NOT NULL DEFAULT 0 COMMENT 'User ID of Sender', ";
				$sql .= "`recipient` bigint(20) NOT NULL DEFAULT 0 COMMENT 'User ID of Recepient', ";
				$sql .= "`stid` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Store ID, if 0 User', ";
				$sql .= "`type` enum('0','1','2') NOT NULL DEFAULT '0' COMMENT '0 User, 1 Store, 2 Mover', ";
				$sql .= "`status` bigint(20) NOT NULL DEFAULT 0 COMMENT '1 active or 0 inactive, use to delete.', ";
				$sql .= "`date_created` datetime DEFAULT NULL COMMENT 'The date this message is created.', ";
				$sql .= "`date_seen` datetime DEFAULT NULL COMMENT 'The date this message is seen.', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}

		//Database table creation for revisions
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_revs'" ) != $tbl_revs) {
			$sql = "CREATE TABLE `".$tbl_revs."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`hash_id` varchar(255) NOT NULL , ";
				$sql .= "`revs_type` enum('none','configs','activity','messages', 'reviews') NOT NULL COMMENT 'Target table', ";
				$sql .= "`parent_id` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Parent ID of this Revision', ";
				$sql .= "`child_key` varchar(50) NOT NULL COMMENT 'Column name on the table', ";
				$sql .= "`child_val` longtext NOT NULL COMMENT 'Text Value of the row Key.', ";
				$sql .= "`created_by` bigint(20) NOT NULL DEFAULT 0 COMMENT 'User ID created this Revision.', ";
				$sql .= "`date_created` datetime DEFAULT NULL COMMENT 'The date this Revision is created.', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}

		//Database table creation for market
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_market'" ) != $tbl_market) {
			$sql = "CREATE TABLE `".$tbl_market."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`hash_id` varchar(255) NOT NULL , ";
				$sql .= "`post_id` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Id of this post with revision ID', ";
				$sql .= "`stage` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Stage of this post with revision ID', ";
				$sql .= "`created_by` bigint(20) NOT NULL DEFAULT 0 COMMENT 'User ID created this Revision.', ";
				$sql .= "`date_created` datetime DEFAULT NULL COMMENT 'The date this Revision is created.', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}

		//REACTS ON POST = SECOND WAVE
		//Database table creation for reacts
		// if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_reacts'" ) != $tbl_reacts) {
		// 	$sql = "CREATE TABLE `".$tbl_reacts."` (";
		// 		$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
		// 		$sql .= "`post_id` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Post id from WP_Post', ";
		// 		$sql .= "`status` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Status of this react with revision ID', ";
		// 		$sql .= "`react` bigint(20) NOT NULL DEFAULT 0 COMMENT 'React type on post with revision ID', ";
		// 		$sql .= "`created_by` bigint(20) NOT NULL DEFAULT 0 COMMENT 'User ID created this Revision.', ";
		// 		$sql .= "`date_created` datetime DEFAULT NULL COMMENT 'The date this Revision is created.', ";
		// 		$sql .= "PRIMARY KEY (`ID`) ";
		// 		$sql .= ") ENGINE = InnoDB; ";
		// 	$result = $wpdb->get_results($sql);
		// }

		//Database table creation for market
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_reviews'" ) != $tbl_reviews) {
			$sql = "CREATE TABLE `".$tbl_reviews."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`hash_id` varchar(255) NOT NULL , ";
				$sql .= "`wpid` bigint(20) NOT NULL DEFAULT 0 COMMENT 'User recipient of this review with revision ID', ";
				$sql .= "`created_by` bigint(20) NOT NULL DEFAULT 0 COMMENT 'User ID who created this review with Revision id.', ";
				$sql .= "`date_created` datetime DEFAULT NULL COMMENT 'The date this Revision is created.', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}
	}
	add_action( 'activated_plugin', 'sp_dbhook_activate');