<?php
	// Exit if accessed directly
	if ( ! defined( 'ABSPATH' ) ) {
		exit;
	}

	/**
	 * @package sociopress-wp-plugin
     * @version 0.1.0
     * This is where you provide all the constant config.
	*/
	
	//Defining plugin prefix
	define('SP_PREFIX', 'sp_');
	define('WP_PREFIX', 'wp_');

	// Wordpress Tables
	define('WP_POSTS', WP_PREFIX.'posts');

	//Activity Configs
	define('SP_ACTIVITY_TABLE', SP_PREFIX.'activities');
	define('SP_ACTIVITY_FIELDS', '(wpid, stid, icon, title, info, created_by, date_open, date_created)');
	
	//Revisions Config
	define('SP_REVS_TABLE', SP_PREFIX.'revisions');
	define('SP_REVS_TABLE_FIELDS', '(revs_type, parent_id, child_key, child_val, created_by, date_created)');

	//Configs Config
	//define('SP_POSTS_TABLE', SP_PREFIX.'posts');
	define('SP_CONFIGS_TABLE', SP_PREFIX.'configs');

	//Message Configs
	define('SP_MESSAGES_TABLE', SP_PREFIX.'messages');
	define('SP_MESSAGES_FIELDS', '(content, sender, recepient, status, date_created )');

	//Market Configs
	define('SP_MARKET_TABLE', SP_PREFIX.'market');

	//Init feeds limit
	define('INITIAL_FEEDS', 12);
	define('SUCCEEDING_FEEDS', 5);

	




