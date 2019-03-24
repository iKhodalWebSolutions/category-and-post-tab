<?php 
/*
  Plugin Name: Responsive tabs widget and shortcode wordpress plugin
  Description: Posts tab view for the widget and content block
  Author: iKhodal Web Solution
  Plugin URI: https://www.ikhodal.com/wp-category-and-post-tab
  Author URI: https://www.ikhodal.com
  Version: 2.1
  Text Domain: richcategoryposttab
*/ 
  
  
//////////////////////////////////////////////////////
// Defines the constants for use within the plugin. //
////////////////////////////////////////////////////// 
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly  


/**
*  Assets of the plugin
*/
$rcpt_plugins_url = plugins_url( "/assets/", __FILE__ );

define( 'rcpt_media', $rcpt_plugins_url ); 

/**
*  Plugin DIR
*/
$rcpt_plugin_dir = plugin_basename(dirname(__FILE__));

define( 'rcpt_plugin_dir', $rcpt_plugin_dir );  

 
/**
 * Include abstract class for common methods
 */
require_once 'include/abstract.php';


///////////////////////////////////////////////////////
// Include files for widget and shortcode management //
///////////////////////////////////////////////////////

/**
 * Register custom post type for shortcode
 */ 
require_once 'include/shortcode.php';

/**
 * Admin panel widget configuration
 */ 
require_once 'include/admin.php';

/**
 * Load Category and Post Tab on frontent pages
 */
require_once 'include/richcategoryposttab.php'; 

/**
 * Clean data on activation / deactivation
 */
require_once 'include/activation_deactivation.php';  
 