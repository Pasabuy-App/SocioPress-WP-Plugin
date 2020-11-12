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
	define('WP_PREFIXS', 'wp_');

	// Wordpress Tables
	define('WP_POSTS', WP_PREFIXS.'posts');

	//Activity Configs
	define('SP_ACTIVITY_TABLE', SP_PREFIX.'activities');
	define('SP_ACTIVITY_FIELDS', '(`wpid`, `stid`, `icon`, `title`, `info`, `date_open`, `date_created`)');

	//Revisions Config
	define('SP_REVS_TABLE', SP_PREFIX.'revisions');
	define('SP_REVS_TABLE_FIELDS', '`revs_type`, `parent_id`, `child_key`, `child_val`, `created_by`, `date_created`');

	//Configs Config
	//define('SP_POSTS_TABLE', SP_PREFIX.'posts');
	define('SP_CONFIGS_TABLE', SP_PREFIX.'configs');

	//Message Configs
	define('SP_MESSAGES_TABLE', SP_PREFIX.'messages');
	define('SP_MESSAGES_FIELDS', ' `content`, `sender`, `recipient`, `type`, `created_by`  ');

	//Market Configs
	define('SP_MARKET_TABLE', SP_PREFIX.'market');

	//Reacts Config
	define('SP_REACTS_TABLE', SP_PREFIX.'reacts');

	//Reviews Config
	define('SP_REVIEWS_TABLE', SP_PREFIX.'reviews');
	define('SP_REVIEWS_FIELDS', '( wpid, created_by, date_created )');

	define('SP_POST_SEEN', SP_PREFIX.'post_seen');
	define('SP_POST_SEEN_FIELDS', '`post_id`, `wpid`');


	//Init feeds limit
	define('INITIAL_FEEDS', 12);
	define('SUCCEEDING_FEEDS', 5);






