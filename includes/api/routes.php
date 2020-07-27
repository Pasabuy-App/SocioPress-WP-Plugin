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
?>

<?php

    //Require the USocketNet class which have the core function of this plguin. 
    require plugin_dir_path(__FILE__) . '/v1/users/class-auth.php'; // Example
	
	// Init check if USocketNet successfully request from wapi.
    function sociopress_route()
    {
        // Example
        register_rest_route( 'sociopress/v1/user', 'auth', array(
            'methods' => 'POST',
            'callback' => array('DV_Authenticate','initialize'),
        ));       
    }
    add_action( 'rest_api_init', 'sociopress_route' );

?>