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

	//Initializing table names
	define('POSTS_TABLE', SP_PREFIX.'posts');

	//Initializing table fields to be called
	define('POST_FIELDS', 'ID, user_id, post_type');

	//Init feeds limit
	define('INITIAL_FEEDS', 12);
	define('SUCCEEDING_FEEDS', 5);


