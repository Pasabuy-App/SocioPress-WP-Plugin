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
		//$tbl_posts = SP_POSTS_TABLE;
		$tbl_act = SP_ACT_TABLE;
		$tbl_configs = SP_CONFIGS_TABLE;
		$tbl_revs = SP_REVS_TABLE;

		//Database table creation for posts
		/*if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_posts'" ) != $tbl_posts) {
			$sql = "CREATE TABLE `".$tbl_posts."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`user_id` int(11) NOT NULL, ";
				$sql .= "`post_type` VARCHAR(50) NULL, ";
				$sql .= "`post_timestamp` datetime NOT NULL, ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}*/

		//Database table creation for activities
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_act'" ) != $tbl_act) {
			$sql = "CREATE TABLE `".$tbl_act."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
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
				$sql .= "`config_desc` varchar(255) NOT NULL COMMENT 'Config Description', ";
				$sql .= "`config_key` varchar(50) NOT NULL COMMENT 'Config KEY', ";
				$sql .= "`config_value` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Config VALUES', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}

		//Database table creation for revisions
		if($wpdb->get_var( "SHOW TABLES LIKE '$tbl_revs'" ) != $tbl_revs) {
			$sql = "CREATE TABLE `".$tbl_revs."` (";
				$sql .= "`ID` bigint(20) NOT NULL AUTO_INCREMENT, ";
				$sql .= "`revs_type` enum('none','configs','activity') NOT NULL COMMENT 'Target table', ";
				$sql .= "`parent_id` bigint(20) NOT NULL DEFAULT 0 COMMENT 'Parent ID of this Revision', ";
				$sql .= "`child_key` varchar(50) NOT NULL COMMENT 'Column name on the table', ";
				$sql .= "`child_val` longtext NOT NULL COMMENT 'Text Value of the row Key.', ";
				$sql .= "`created_by` bigint(20) NOT NULL DEFAULT 0 COMMENT 'User ID created this Revision.', ";
				$sql .= "`date_created` datetime DEFAULT NULL COMMENT 'The date this Revision is created.', ";
				$sql .= "PRIMARY KEY (`ID`) ";
				$sql .= ") ENGINE = InnoDB; ";
			$result = $wpdb->get_results($sql);
		}


	}

	add_action( 'activated_plugin', 'sp_dbhook_activate');