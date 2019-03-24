<?php

/**
 * Clean data on activation / deactivation
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  
 
register_activation_hook( __FILE__, 'richcategoryposttab_activation');

function richcategoryposttab_activation() {

	if( ! current_user_can ( 'activate_plugins' ) ) {
		return;
	} 
	add_option( 'richcategoryposttab_license_status', 'invalid' );
	add_option( 'richcategoryposttab_license_key', '' ); 

}

register_uninstall_hook( __FILE__, 'richcategoryposttab_uninstall');

function richcategoryposttab_uninstall() {

	delete_option( 'richcategoryposttab_license_status' );
	delete_option( 'richcategoryposttab_license_key' ); 
	
}