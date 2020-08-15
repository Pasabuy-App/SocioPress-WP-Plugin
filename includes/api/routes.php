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

    //Require the USocketNet class which have the core function of this plguin. 
    // Activty Folder
    require plugin_dir_path(__FILE__) . '/v1/activity/class-insert.php'; // home feeds
    require plugin_dir_path(__FILE__) . '/v1/activity/class-listing.php'; // home feeds
    require plugin_dir_path(__FILE__) . '/v1/activity/class-select.php'; // home feeds

    require plugin_dir_path(__FILE__) . '/v1/feeds/class-profile.php'; // profile feeds
    require plugin_dir_path(__FILE__) . '/v1/feeds/class-home.php'; // home feeds
    require plugin_dir_path(__FILE__) . '/v1/feeds/class-posts.php'; // user posts feeds
    require plugin_dir_path(__FILE__) . '/v1/profile/class-data.php'; // globals

    // Post Folder
    require plugin_dir_path(__FILE__) . '/v1/post/class-insert.php';
    require plugin_dir_path(__FILE__) . '/v1/post/class-update.php';
    require plugin_dir_path(__FILE__) . '/v1/post/class-delete.php';
    require plugin_dir_path(__FILE__) . '/v1/post/class-count.php';
    require plugin_dir_path(__FILE__) . '/v1/post/class-share-post.php';
    require plugin_dir_path(__FILE__) . '/v1/post/class-share-post-link.php';

    // Messagegs Folder
    require plugin_dir_path(__FILE__) . '/v1/messages/class-insert.php';
    require plugin_dir_path(__FILE__) . '/v1/messages/class-update.php';
    require plugin_dir_path(__FILE__) . '/v1/messages/class-seen.php';
    require plugin_dir_path(__FILE__) . '/v1/messages/class-delete.php';
    require plugin_dir_path(__FILE__) . '/v1/messages/class-getby-recepient.php';

    // User Authentication
    require plugin_dir_path(__FILE__) . '/v1/users/class-auth.php';
    
    require plugin_dir_path(__FILE__) . '/v1/class-globals.php'; // globals

    // Init check if USocketNet successfully request from wapi.
    function sociopress_route()
    {   
        /*
         * AUTHENTICATION RESTAPI
        */
        register_rest_route( 'sociopress/v1/users', 'auth', array(
            'methods' => 'POST',
            'callback' => array('SP_Authenticate','listen'),
        ));
                   
        /*
         * ACTIVITY RESTAPI
        */
            register_rest_route( 'sociopress/v1/activity', 'insert', array(
                'methods' => 'POST',
                'callback' => array('SP_Insert_Activity','listen'),
            ));

            register_rest_route( 'sociopress/v1/activity', 'list/all', array(
                'methods' => 'POST',
                'callback' => array('SP_Listing_Activity','listen'),
            ));

            register_rest_route( 'sociopress/v1/activity', 'select', array(
                'methods' => 'POST',
                'callback' => array('SP_Select_Activity','listen'),
            ));


        /*
         * PROFILE RESTAPI
        */

            register_rest_route( 'sociopress/v1/profile', 'data', array(
                'methods' => 'POST',
                'callback' => array('SP_Profile_data','listen'),
            ));

        /*
         * FEEDS RESTAPI
        */

            register_rest_route( 'sociopress/v1/feeds', 'profile', array(
                'methods' => 'POST',
                'callback' => array('SP_Newsfeed','listen'),
            ));

            register_rest_route( 'sociopress/v1/feeds', 'p_feeds', array(
                'methods' => 'GET',
                'callback' => array('SP_Newsfeed','get_additional_feeds'),
            ));


            register_rest_route( 'sociopress/v1/feeds', 'home', array(
                'methods' => 'POST',
                'callback' => array('SP_Homefeed','listen'),
            ));
        
            register_rest_route( 'sociopress/v1/feeds', 'get_act_addfeed', array(
                'methods' => 'GET',
                'callback' => array('SP_Activity','get_activity_feed'),
            ));

            register_rest_route( 'sociopress/v1/feeds', 'get_act_byid', array(
                'methods' => 'GET',
                'callback' => array('SP_Activity','get_activity_byid'),
            ));

            register_rest_route( 'sociopress/v1/feeds', 'posts', array(
                'methods' => 'POST',
                'callback' => array('SP_Posts', 'listen'),
            ));

        /*
         * POST RESTAPI
        */
    
            register_rest_route( 'sociopress/v1/post', 'insert', array(
                'methods' => 'POST',
                'callback' => array('SP_Insert_Post','listen'),
            ));

            register_rest_route( 'sociopress/v1/post', 'update', array(
                'methods' => 'POST',
                'callback' => array('SP_Update_Post','listen'),
            ));

            register_rest_route( 'sociopress/v1/post', 'delete', array(
                'methods' => 'POST',
                'callback' => array('SP_Delete_Post','listen'),
            ));

            register_rest_route( 'sociopress/v1/post/user', 'count', array(
                'methods' => 'POST',
                'callback' => array('SP_Count_Post','listen'),
            ));

            register_rest_route( 'sociopress/v1/post', 'share', array(
                'methods' => 'POST',
                'callback' => array('SP_Share_Post','listen'),
            ));

        /*
         * MESSAGES RESTAPI
        */
    
        register_rest_route( 'sociopress/v1/messages', 'insert', array(
            'methods' => 'POST',
            'callback' => array('SP_Insert_Message','listen'),
        ));
        
        register_rest_route( 'sociopress/v1/messages', 'update', array(
            'methods' => 'POST',
            'callback' => array('SP_Update_Message','listen'),
        ));
        
        register_rest_route( 'sociopress/v1/messages', 'seen', array(
            'methods' => 'POST',
            'callback' => array('SP_Seen_Message','listen'),
        ));
        
        register_rest_route( 'sociopress/v1/messages', 'delete', array(
            'methods' => 'POST',
            'callback' => array('SP_Delete_Message','listen'),
        ));
        
        register_rest_route( 'sociopress/v1/messages/get', 'recepient', array(
            'methods' => 'POST',
            'callback' => array('SP_GetBy_Recepient','listen'),
        ));
        
    }
    add_action( 'rest_api_init', 'sociopress_route' );
