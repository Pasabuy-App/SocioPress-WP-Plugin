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
  	class SP_Transactions_List {
          public static function listen(){
            return rest_ensure_response( 
                SP_Transactions_List::list_transactions()
            );
          }
    
        public static function list_transactions(){
            
            //Pending:
            //Pending:
       
        }
        

    }