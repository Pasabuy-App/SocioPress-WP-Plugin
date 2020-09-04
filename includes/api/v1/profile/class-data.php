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
  	class SP_Profile_data {

        public static function listen(){
            return rest_ensure_response( 
                self::get_profile_data()
            );
    
        }
         
        // REST API for getting the user data
        public static function get_profile_data(){
            global $wpdb;

            // Step 1: Check if prerequisites plugin are missing
            $plugin = SP_Globals::verify_prerequisites();
            if ($plugin !== true) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. ".$plugin." plugin missing!",
                );
            }

            // Step 2: Valdiate user
            if (DV_Verification::is_verified() == false) {
                return array(
                    "status" => "unknown",
                    "message" => "Please contact your administrator. Verification issues!",
                );
            }

            // Step 3: Find user in db using wpid
            $wp_user = get_user_by("ID", $_POST['wpid']);
            $wpid = $_POST['wpid'];

            $user_address = $wpdb->get_row("SELECT
                `add`.ID,
                `add`.stid,
                IF(`add`.types = 'business', 'Business', 'Office' )as `type`,
                ( SELECT child_val FROM dv_revisions WHERE id = `add`.street ) AS street,
                ( SELECT brgy_name FROM dv_geo_brgys WHERE ID = ( SELECT child_val FROM dv_revisions WHERE id = `add`.brgy ) ) AS brgy,
                ( SELECT city_name FROM dv_geo_cities WHERE city_code = ( SELECT child_val FROM dv_revisions WHERE id = `add`.city ) ) AS city,
                ( SELECT prov_name FROM dv_geo_provinces WHERE prov_code = ( SELECT child_val FROM dv_revisions WHERE id = `add`.province ) ) AS province,
                ( SELECT country_name FROM dv_geo_countries WHERE id = ( SELECT child_val FROM dv_revisions WHERE id = `add`.country ) ) AS country,
                IF (( select child_val from dv_revisions where id = `add`.`status` ) = 1, 'Active' , 'Inactive' ) AS `status`,
                `add`.date_created
            FROM
                dv_address `add`
            WHERE wpid = $wpid"); 

            $user_contact = $wpdb->get_results("SELECT
                -- dc.ID,
                -- dc.stid,
                -- IF ( dc.`status` = 1, 'Active', 'Inactive' ) as `status`,
                -- dc.types,
                dr.child_val AS `value`
                -- dc.date_created 
            FROM
                dv_contacts dc
                INNER JOIN dv_revisions dr ON dr.ID = dc.revs 
            WHERE
                dc.`wpid` = '$wpid'");
                    
            // Step 4: Return success status and complete object.
            return array(
                "status" => "success",
                "data" => array(
                        "uname" => $wp_user->data->user_nicename,
                        "dname" => $wp_user->data->display_name,
                        "email" => $wp_user->data->user_email,
                        "role" => $wp_user->roles,
                        "date_registered" => $wp_user->user_registered,
                        "fname" => $wp_user->first_name,
                        "lname" => $wp_user->last_name,
                        "avatar" => $wp_user->avatar,
                        "contact" => $user_contact[0]->value,
                        "street" => $user_address->street,
                        "brgy"  => $user_address->brgy,
                        "city"  => $user_address->city,
                        "prov"  => $user_address->province,
                    )
            );
        }// End of function initialize()
    }// End of class