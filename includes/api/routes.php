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
    require plugin_dir_path(__FILE__) . '/v1/globals/class-globals.php'; // globals
    require plugin_dir_path(__FILE__) . '/v1/profile/class-data.php'; // globals
	
	// Init check if USocketNet successfully request from wapi.
    function sociopress_route()
    {   
                   
        /*
         * ACTIVITY RESTAPI
        */
            register_rest_route( 'sociopress/v1/activity', 'insert', array(
                'methods' => 'POST',
                'callback' => array('SP_Insert_Activity','listen'),
            ));

            register_rest_route( 'sociopress/v1/activity', 'list/all', array(
                'methods' => 'POST',
                'callback' => array('SP_Insert_Activity','listen'),
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
         * PROFILE RESTAPI
        */

            register_rest_route( 'sociopress/v1/feeds', 'profile', array(
                'methods' => 'POST',
                'callback' => array('SP_Newsfeed','profile_feeds'),
            ));

            register_rest_route( 'sociopress/v1/feeds', 'p_feeds', array(
                'methods' => 'GET',
                'callback' => array('SP_Newsfeed','get_additional_feeds'),
            ));


            register_rest_route( 'sociopress/v1/feeds', 'home', array(
                'methods' => 'POST',
                'callback' => array('SP_Homefeed','home_feeds'),
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
                'callback' => array('SP_Posts', 'filter_posts'),
            ));
        

            //'callback' => array('SP_Authenticate','listen'),
        //));       
    }
    add_action( 'rest_api_init', 'sociopress_route' );
