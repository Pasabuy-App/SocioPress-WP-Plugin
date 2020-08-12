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
	
	//Defining Global Variables
	define('SP_PREFIX', 'sp_');

	define('SP_ACTIVITY_TABLE', SP_PREFIX.'activities');
	define('SP_ACTIVITY_FIELDS', '(wpid, stid, icon, title, info, created_by, date_open, date_created)');
	
	define('SP_REVS_TABLE', SP_PREFIX.'revisions');
	define('SP_REVS_TABLE_FIELDS', '(revs_type, parent_id, child_key, child_val,  date_created)');

	//Initializing table names
	//define('SP_POSTS_TABLE', SP_PREFIX.'posts');
	define('SP_CONFIGS_TABLE', SP_PREFIX.'configs');


	//Initializing table fields to be called
	//define('POST_FIELDS', 'ID, user_id, post_type');

	//Init feeds limit
	define('INITIAL_FEEDS', 12);
	define('SUCCEEDING_FEEDS', 5);


