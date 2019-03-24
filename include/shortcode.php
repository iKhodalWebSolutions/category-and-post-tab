<?php 
/** 
 * Register custom post type to manage shortcode
 */
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly   
if ( ! class_exists( 'richcategoryposttabShortcode_Admin' ) ) {
	class richcategoryposttabShortcode_Admin extends richcategoryposttabLib {
	
		public $_shortcode_config = array();
		 
		/**
		 * constructor method.
		 *
		 * Register post type for tab for category and posts shortcode
		 * 
		 * @access    public
		 * @since     1.0
		 *
		 * @return    void
		 */
		public function __construct() {
			
			parent::__construct();
			
	       /**
		    * Register hooks to manage custom post type for tab for category and posts
		    */
			add_action( 'init', array( &$this, 'rcpt_registerPostType' ) );  
			//add_action( 'admin_menu', array( &$this, 'rcpt_addadminmenu' ) );  
			add_action( 'add_meta_boxes', array( &$this, 'add_richcategoryposttab_metaboxes' ) );
			add_action( 'save_post', array(&$this, 'wp_save_richcategoryposttab_meta' ), 1, 2 ); 
			add_action( 'admin_enqueue_scripts', array( $this, 'rcpt_admin_enqueue' ) ); 
			
		   /* Register hooks for displaying shortcode column. */ 
			if( isset( $_REQUEST["post_type"] ) && !empty( $_REQUEST["post_type"] ) && trim($_REQUEST["post_type"]) == "rcpt_tabs" ) {
				add_action( "manage_posts_custom_column", array( $this, 'richcategoryposttabShortcodeColumns' ), 10, 2 );
				add_filter( 'manage_posts_columns', array( $this, 'rcpt_shortcodeNewColumn' ) );
			}
			
			add_action( 'wp_ajax_rcpt_getCategoriesOnTypes',array( &$this, 'rcpt_getCategoriesOnTypes' ) ); 
			add_action( 'wp_ajax_nopriv_rcpt_getCategoriesOnTypes', array( &$this, 'rcpt_getCategoriesOnTypes' ) );
			add_action( 'wp_ajax_rcpt_getCategoriesRadioOnTypes',array( &$this, 'rcpt_getCategoriesRadioOnTypes' ) ); 
			add_action( 'wp_ajax_nopriv_rcpt_getCategoriesRadioOnTypes', array( &$this, 'rcpt_getCategoriesRadioOnTypes' ) ); 
			add_filter( 'wp_editor_settings', array( $this, 'rcpt_postbodysettings' ), 10, 2 );
		}  
		
		/**
		* Set the post body type
		*
		* @access  private
		* @since   1.0
		*
		* @return  void
		*/  
		public function rcpt_postbodysettings( $settings, $editor_id ) { 
		
			global $post; 
			
			if( $post->post_type == "rcpt_posttabs" ) {
			
				$settings = array(
						'wpautop'             => false,
						'media_buttons'       => false,
						'default_editor'      => '',
						'drag_drop_upload'    => false,
						'textarea_name'       => $editor_id,
						'textarea_rows'       => 20,
						'accordionindex'            => '',
						'accordionfocus_elements'   => ':prev,:next',
						'editor_css'          => '',
						'editor_class'        => '',
						'teeny'               => false,
						'dfw'                 => false,
						'_content_editor_dfw' => false,
						'tinymce'             => true,
						'quicktags'           => true
					);
			
			}
			
			return $settings;
			
		}
		
		/**
		* Admin menu configuration 
		*
		* @access  private
		* @since   1.0
		*
		* @return  void
		*/  
		public function rcpt_addadminmenu() { 
		
		
			add_submenu_page('edit.php?post_type=rcpt_tabs', __( 'All Tab Posts', 'richcategoryposttab' ), __( 'All Tab Posts', 'richcategoryposttab' ),  'manage_options', 'edit.php?post_type=rcpt_posttabs');
			
			add_submenu_page('edit.php?post_type=rcpt_tabs', __( 'New Tab Post', 'richcategoryposttab' ), __( 'New Tab Post', 'richcategoryposttab' ),  'manage_options', 'post-new.php?post_type=rcpt_posttabs'); 
			
			add_submenu_page('edit.php?post_type=rcpt_tabs', __( 'Tab Categories', 'richcategoryposttab' ), __( 'Tab Categories', 'richcategoryposttab' ),  'manage_options', 'edit-tags.php?taxonomy=cpt_tab_categories&post_type=rcpt_tabs'); 
			
		}
		
 	   /**
		* Register and load JS/CSS for admin widget configuration 
		*
		* @access  private
		* @since   1.0
		*
		* @return  bool|void It returns false if not valid page or display HTML for JS/CSS
		*/  
		public function rcpt_admin_enqueue() {

			if ( ! $this->validate_page() )
				return FALSE;
			
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_style( 'admin-richcategoryposttab.css', rcpt_media."css/admin-richcategoryposttab.css" );
			wp_enqueue_script( 'admin-richcategoryposttab.js', rcpt_media."js/admin-richcategoryposttab.js" ); 
			
		}		
		 
	   /**
		* Add meta boxes to display shortcode
		*
		* @access  private
		* @since   1.0
		*
		* @return  void
		*/ 
		public function add_richcategoryposttab_metaboxes() {
			
			/**
			 * Add custom fields for shortcode settings
		     */
			add_meta_box( 'wp_richcategoryposttab_fields', __( 'Category and Post Tab', 'richcategoryposttab' ),
				array( &$this, 'wp_richcategoryposttab_fields' ), 'rcpt_tabs', 'normal', 'high' );
			
			/**
			 * Display shortcode of tab for category and posts
		     */
			add_meta_box( 'wp_richcategoryposttab_shortcode', __( 'Shortcode', 'richcategoryposttab' ),
				array( &$this, 'shortcode_meta_box' ), 'rcpt_tabs', 'side' );	
		
		}  
		
	   /**
		* Validate widget or shortcode post type page
		*
		* @access  private
		* @since   1.0
		*
		* @return  bool It returns true if page is post.php or widget otherwise returns false
		*/ 
		private function validate_page() {

			if ( ( isset( $_GET['post_type'] )  && $_GET['post_type'] == 'rcpt_tabs' ) || strpos($_SERVER["REQUEST_URI"],"widgets.php") > 0  || strpos($_SERVER["REQUEST_URI"],"post.php" ) > 0 || strpos($_SERVER["REQUEST_URI"], "richcategoryposttab_settings" ) > 0  )
				return TRUE;
		
		} 			
 
	   /**
		* Display richcategoryposttab block configuration fields
		*
		* @access  private
		* @since   1.0
		*
		* @return  void Returns HTML for configuration fields 
		*/  
		public function wp_richcategoryposttab_fields() {
			
			global $post; 
			 
			foreach( $this->_config as $kw => $kw_val ) {
				$this->_shortcode_config[$kw] = get_post_meta( $post->ID, $kw, true ); 
			}
			 
			foreach ( $this->_shortcode_config as $sc_key => $sc_val ) {
				if( trim( $sc_val ) == "" )
					unset( $this->_shortcode_config[ $sc_key ] );
				else {
					if(!is_array($sc_val) && trim($sc_val) != "" ) 
						$this->_shortcode_config[ $sc_key ] = htmlspecialchars( $sc_val, ENT_QUOTES );
					else 
						$this->_shortcode_config[ $sc_key ] = $sc_val;
				}	
			}
			
			foreach( $this->_config as $kw => $kw_val ) {
				if( !is_array($this->_shortcode_config[$kw]) && trim($this->_shortcode_config[$kw]) == "" ) {
					$this->_shortcode_config[$kw] = $this->_config[$kw]["default"];
				} 
			}
			
			$this->_shortcode_config["vcode"] = get_post_meta( $post->ID, 'vcode', true );    
			 
			require( $this->getrichcategoryposttabTemplate( "admin/admin_shortcode_post_type.php" ) );
			
		}
		
	   /**
		* Display shortcode in edit mode
		*
		* @access  private
		* @since   1.0
		*
		* @param   object  $post Set of configuration data.
		* @return  void	   Displays HTML of shortcode
		*/
		public function shortcode_meta_box( $post ) {

			$richcategoryposttab_id = $post->ID;

			if ( get_post_status( $richcategoryposttab_id ) !== 'publish' ) {

				echo '<p>'.__( 'Please make the publish status to get the shortcode', 'richcategoryposttab' ).'</p>';

				return;

			}

			$richcategoryposttab_title = get_the_title( $richcategoryposttab_id );

			$shortcode = sprintf( "[%s id='%s']", 'richcategoryposttab', $richcategoryposttab_id );
			
			echo "<p class='tpp-code'>".$shortcode."</p>";
		}
				  
	   /**
		* Save tab for category and posts shortcode fields
		*
		* @access  private
		* @since   1.0 
		*
		* @param   int    	$post_id post id
		* @param   object   $post    post data object
		* @return  void
		*/ 
		function wp_save_richcategoryposttab_meta( $post_id, $post ) {
			
		/*	if( !isset($_POST['richcategoryposttab_nonce']) ) {
				return $post->ID;
			} 
			if( !wp_verify_nonce( $_POST['richcategoryposttab_nonce'], plugin_basename(__FILE__) ) ) {
				return $post->ID;
			}
			*/
			
		   /**
			* Check current user permission to edit post
			*/
			if(!current_user_can( 'edit_post', $post->ID ))
				return $post->ID;
				
			 /**
			* sanitize text fields 
			*/
			$rcpt_meta = array(); 
			
			foreach( $this->_config as $kw => $kw_val ) { 
				$_save_value =  $_POST["nm_".$kw];
				if($kw_val["type"]=="boolean"){
					$_save_value = $_POST["nm_".$kw][0];
				}
				if( $kw_val["type"]=="checkbox" && count($_POST["nm_".$kw]) > 0 ) {
					$_save_value = implode( ",", $_POST["nm_".$kw] );
				}
				$rcpt_meta[$kw] =  sanitize_text_field( $_save_value );
			}     
			 
			foreach ( $rcpt_meta as $key => $value ) {
			
			   if( $post->post_type == 'revision' ) return;
				$value = implode( ',', (array)$value );
				
				if( trim($value) == "Array" || is_array($value) )
					$value = "";
					
			   /**
				* Add or update posted data 
				*/
				if( get_post_meta( $post->ID, $key, FALSE ) ) { 
					update_post_meta( $post->ID, $key, $value );
				} else { 
					add_post_meta( $post->ID, $key, $value );
				} 
			
			}		
			
		  
		}
		
			 
	   /**
		* Register post type tab for category and posts shortcode
		*
		* @access  private
		* @since   1.0
		*
		* @return  void
		*/  
		function rcpt_registerPostType() { 
			
		   /**
			* Post type and menu labels 
			*/
			$labels = array(
				'name' => __('Category & Posts Tab View Shortcode', 'richcategoryposttab' ),
				'singular_name' => __( 'Category & Posts Tab View Shortcode', 'richcategoryposttab' ),
				'add_new' => __( 'Add New Shortcode', 'richcategoryposttab' ),
				'add_new_item' => __( 'Add New Shortcode', 'richcategoryposttab' ),
				'edit_item' => __( 'Edit', 'richcategoryposttab'  ),
				'new_item' => __( 'New', 'richcategoryposttab'  ),
				'all_items' => __( 'All', 'richcategoryposttab'  ),
				'view_item' => __( 'View', 'richcategoryposttab'  ),
				'search_items' => __( 'Search', 'richcategoryposttab'  ),
				'not_found' =>  __( 'No item found', 'richcategoryposttab'  ),
				'not_found_in_trash' => __( 'No item found in Trash', 'richcategoryposttab'  ),
				'parent_item_colon' => '',
				'menu_name' => __( 'CPT', 'richcategoryposttab'  ) 
			);
			
		   /**
			* Category and Post Tab post type registration options
			*/
			$args = array(
				'labels' => $labels,
				'public' => false,
				'publicly_queryable' => false,
				'show_ui' => true,
				'show_in_menu' => true,
				'query_var' => false,
				'rewrite' => false,
				'capability_type' => 'post',
				'menu_icon' => 'dashicons-list-view',
				'has_archive' => false,
				'hierarchical' => false,
				'menu_position' => null,
				'supports' => array( 'title' )
			);
			 
		   /**
			* Register new post type
			*/
			register_post_type( 'rcpt_tabs', $args );
			
			/**
			*  menu labels 
			*/
		/*	$labels = array(
				'name' => __('Tab Posts', 'richcategoryposttab' ),
				'singular_name' => __( 'Tab Posts', 'richcategoryposttab' ),
				'add_new' => __( 'New Tab Post', 'richcategoryposttab' ),
				'add_new_item' => __( 'New Tab Post', 'richcategoryposttab' ),
				'edit_item' => __( 'Edit', 'richcategoryposttab'  ),
				'new_item' => __( 'New', 'richcategoryposttab'  ),
				'all_items' => __( 'All', 'richcategoryposttab'  ),
				'view_item' => __( 'View', 'richcategoryposttab'  ),
				'search_items' => __( 'Search', 'richcategoryposttab'  ),
				'not_found' =>  __( 'No item found', 'richcategoryposttab'  ),
				'not_found_in_trash' => __( 'No item found in Trash', 'richcategoryposttab'  ),
				'parent_item_colon' => '',
				'menu_name' => __( 'Tab Posts', 'richcategoryposttab'  ) 
			); */
			
		   /**
			*  post type registration options
			*/
		/*	$args = array(
				'labels' => $labels,
				'public' => false,
				'publicly_queryable' => false,
				'show_ui' => true,
				'show_in_menu' => false,
				'query_var' => false,
				'rewrite' => false,
				'capability_type' => 'post',
				'menu_icon' => 'dashicons-list-view',
				'has_archive' => false,
				'hierarchical' => false,
				'menu_position' => null,
				'supports' => array(  'title','editor','thumbnail' )
			); */
			
		   /**
			* Register post type
			*/
		//	register_post_type( 'rcpt_posttabs', $args ); 	
				
		   
		   /**
			* Register category for custom post type
			*/
			
			/* $labels = array(
					'name' => _x( 'Tab Categories', 'taxonomy general name' ),
					'singular_name' => _x( 'Tab Category', 'taxonomy singular name' ),
					'search_items' => __( 'Search Categories' ),
					'all_items' => __( 'All Tab Categories' ),
					'parent_item' => array( null ),
					'parent_item_colon' => array( null ),
					'edit_item' => __( 'Edit Category' ),
					'view_item' => __( 'View Category' ),
					'update_item' => __( 'Update Category' ),
					'add_new_item' => __( 'Add New Category' ),
					'new_item_name' => __( 'New Category Name' ), 
					'not_found' => __( 'No categories found.' ),
					'no_terms' => __( 'No categories' ),
					'items_list_navigation' => __( 'Categories list navigation' ),
					'items_list' => __( 'Categories list' ),
			);

			register_taxonomy('cpt_tab_categories',array('rcpt_posttabs'),array(
				'hierarchical'=>true,
				'labels' => $labels,
				'show_ui'=>true,
				'show_admin_column'=>true,
				'query_var'=>true,
				'rewrite'=>array('slug' => 'cpt_tab_categories'),
			));	*/

		}
		
	   /**
		* Display shortcode column in tab for category and posts list
		*
		* @access  private
		* @since   1.0
		*
		* @param   string  $column  Column name
		* @param   int     $post_id Post ID
		* @return  void	   Display shortcode in column	
		*/
		public function richcategoryposttabShortcodeColumns( $column, $post_id ) { 
		
			if( $column == "shortcode" ) {
				 echo sprintf( "[%s id='%s']", 'richcategoryposttab', $post_id ); 
			}  
		
		}
		
	   /**
		* Register shortcode column
		*
		* @access  private
		* @since   1.0
		*
		* @param   array  $columns  Column list 
		* @return  array  Returns column list
		*/
		public function rcpt_shortcodeNewColumn( $columns ) {
			
			$_edit_column_list = array();	
			$_i = 0;
			
			foreach( $columns as $__key => $__value) {
					
					if($_i==2){
						$_edit_column_list['shortcode'] = __( 'Shortcode', 'richcategoryposttab' );
					}
					$_edit_column_list[$__key] = $__value;
					
					$_i++;
			}
			
			return $_edit_column_list;
		
		}
		
	} 

}

new richcategoryposttabShortcode_Admin();
 
?>