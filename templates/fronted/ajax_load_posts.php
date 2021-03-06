<?php if ( ! defined( 'ABSPATH' ) ) exit; 
	 $params = $_REQUEST;  
	 $category_id =( ( isset( $params["category_id"] ) && trim( $params["category_id"] ) != ""  ) ? ( $params["category_id"] ) : "" );
	 $post_search_text =( isset( $params["post_search_text"] ) ? esc_html( $params["post_search_text"] ) : "" ); 
	 $_limit_start =( isset( $params["limit_start"] ) ? intval( $params["limit_start"] ) : 0 );
	 $_limit_end = intval( $params["number_of_post_display"] );
	 $is_default_category_with_hidden = 0; 
	 $static_width = ( ( isset( $params["rcpt_image_content_width"] ) && intval( $params["rcpt_image_content_width"] ) > 0  ) ? intval($params["rcpt_image_content_width"]) : 180 );
	 $final_width = $params["rcpt_image_content_width"]; 
	 $rcpt_image_height = $params["rcpt_image_height"];   
	 $rcpt_mouse_hover_effect = $params["rcpt_mouse_hover_effect"]; 
	     
	if( $this->rcpt_getTotalPosts( $category_id, $post_search_text, 0, $is_default_category_with_hidden ) > 0 ) {
		$_category_res = $this->getCategories();
		if( count( $_category_res ) > 0 && !( sanitize_text_field( $params["hide_searchbox"] ) == 'yes' ) ) { 
			?> 
			<div class="ik-post-category"> 
				<?php if( sanitize_text_field( $params["hide_searchbox"] ) == 'no' ) { ?>
					  <input type="text" name="txtSearch" placeholder="<?php echo __( 'Search', 'richcategoryposttab' ); ?>" value="<?php echo esc_html( htmlspecialchars( stripslashes( $post_search_text ) ) ); ?>" class="ik-post-search-text"  /> 
				<?php } ?>  
				
				<span class="ik-search-button" onclick='rcpt_fillPosts( "<?php echo esc_js( $params["vcode"]."-".$this->rcpt_replace($category_id) ); ?>", "<?php echo esc_js( $category_id ); ?>", request_obj_<?php echo esc_js( $params["vcode"] ); ?>, 2)'> <img width="18px" alt="Search" height="18px" src="<?php echo rcpt_media.'images/searchicon.png'; ?>" />
				</span>
				<div class="clrb"></div>
			</div>
		 <?php
		}
	} else { echo "<input type='hidden' value='".$category_id."' class='ik-drp-post-category' />"; }
	 
	  $_total_posts = $this->rcpt_getTotalPosts( $category_id, $post_search_text, 1, $is_default_category_with_hidden );
	if( $_total_posts <= 0 ) {
		?><div class="ik-post-no-items"><?php echo __( 'No posts found.', 'richcategoryposttab' ); ?></div><?php
		die();
	} 
	$post_list = $this->getPostList( $category_id, $post_search_text, $_limit_end );	 
	 
	foreach ( $post_list as $_post ) { 
		$image  = $this->getPostImage( $_post->post_image, $final_width, $params["rcpt_image_height"] ); 
		$_author_name = esc_html($_post->display_name);
	    $_author_image = get_avatar($_post->post_author,25);
		?>
		<div style="width:<?php echo esc_attr($final_width); ?>px; " class='ikh-post-item-box pid-<?php echo esc_attr( $_post->post_id ); ?>'> 
			<div class="ikh-post-item ikh-simple"> 
			<?php
				ob_start();
				if( $params["rcpt_hide_post_image"] == "no" ) { ?>
					<div  class='ikh-image'  > 
						 <a href="<?php echo get_permalink( $_post->post_id ); ?>"> 
							<?php echo $image; ?>
						</a>   
					</div>  
				<?php } 
				$_ob_image = ob_get_clean(); 
			
			 
				ob_start();
				?>  
				<div class='ikh-content'> 
				    <div class="ikh-content-data">
					
						<div class='ik-post-name'>
							<?php if( sanitize_text_field( $params["hide_post_title"] ) =='no'){ ?> 
								<a href="<?php echo get_permalink( $_post->post_id ); ?>" style="color:<?php echo esc_attr( $params["title_text_color"] ); ?>" >
									<?php echo esc_html( $_post->post_title ); ?>
								</a>
							<?php } ?>	 
							
							<?php if( sanitize_text_field( $params["rcpt_hide_posted_date"] ) =='no'){ ?> 
								<div class='ik-post-date'>
									<i><?php echo date(get_option("date_format"),strtotime($_post->post_date)); ?></i>
								</div>
							<?php } ?>	
								
							<?php  
								if( $params["rcpt_hide_post_short_content"] == "no" ) { ?>
								<div class='ik-post-sub-content'>
									<?php
									if( strlen( strip_tags( $_post->post_content ) ) > intval( $params["rcpt_hide_post_short_content_length"] ) ) 	
										echo substr( strip_tags( $_post->post_content ), 0, $params["rcpt_hide_post_short_content_length"] ).".."; 
									else
										echo trim( strip_tags( $_post->post_content ) );
									?> 
								</div>
							<?php } ?>										
						</div>
						
						<?php if( sanitize_text_field( $params["rcpt_hide_comment_count"] ) =='no'){ ?> 
							<div class='ik-post-comment'>
								<?php 
									$_total_comments = (get_comment_count($_post->post_id)); 			
									if($_total_comments["total_comments"] > 0) {
										echo $_total_comments["total_comments"]; 
										?> <?php echo (($_total_comments["total_comments"]>1)?__( 'Comments', 'richcategoryposttab' ):__( 'Comment', 'richcategoryposttab' )); 
									}
								?>
							</div>
						<?php } ?>
						
						<?php if( sanitize_text_field( $params["rcpt_show_author_image_and_name"] ) =='yes') { ?> 
							<div class='ik-post-author'>
								<?php echo (($_author_image!==FALSE)?$_author_image:"<img src='".rcpt_media."images/user-icon.png' width='25' height='25' />"); ?> <?php echo __( 'By', 'richcategoryposttab' ); ?> <?php echo $_author_name; ?>
							</div>
						<?php } ?>	 	
						
						<?php if( $params["rcpt_read_more_link"] == "no" ) { ?>
							<div class="rcpt-read-more-link">
								<a class="lnk-post-content" href="<?php echo get_permalink( $_post->post_id ); ?>" >
									<?php echo __( 'Read More', 'richcategoryposttab' ); ?>
								</a>
							</div>
						<?php } ?>  
					</div> 
				</div>	
			 <?php
				$_ob_content = ob_get_clean(); 
			
				if($rcpt_mouse_hover_effect=='ikh-image-style-40'|| $rcpt_mouse_hover_effect=='ikh-image-style-41' ){
					echo $_ob_content;
					echo $_ob_image;
				} else {
					echo $_ob_image;
					echo $_ob_content;														
				}	
				 ?>
			<div class="clr1"></div>
			</div> 
		</div> 
		<?php 
	}
	
	 
	
	if( $params["rcpt_hide_paging"] == "no" && $params["rcpt_select_paging_type"] == "load_more_option"   && $_total_posts > sanitize_text_field( $params["number_of_post_display"] ) ) {
	
		?>	
		<div class="clr"></div>
		<div style="display:none" class='ik-post-load-more'  align="center" onclick = 'rcpt_loadMorePosts( "<?php echo esc_js( $category_id ); ?>", "<?php echo esc_js( $_limit_start+$_limit_end ); ?>", "<?php echo esc_js( $params["vcode"]."-".$this->rcpt_replace($category_id) ); ?>", "<?php echo esc_js( $_total_posts ); ?>", request_obj_<?php echo esc_js( $params["vcode"] ); ?> )'>
			<?php echo __('Load More', 'richcategoryposttab' ); ?>
		</div>
		<?php 
		
	} else if( $params["rcpt_hide_paging"] == "no" && $params["rcpt_select_paging_type"] == "next_and_previous_links" ) {
	
		?><div class="clr"></div>
		<div style="display:none" class="rcpt-simple-paging"><?php
			echo $this->displayPagination(  0, $_total_posts, $category_id, $_limit_start, $_limit_end, $params["vcode"], 2 );
		?></div><div class="clr"></div><?php
	
	} else if( $params["rcpt_hide_paging"] == "no" && $params["rcpt_select_paging_type"] == "simple_numeric_pagination" ) {
	
		?><div class="clr"></div>
		<div style="display:none" class="rcpt-simple-paging"><?php
			echo $this->displayPagination(  0, $_total_posts, $category_id, $_limit_start, $_limit_end, $params["vcode"], 1 );
		?></div><div class="clr"></div><?php	
	
	} else {
		?> <div class="clr"></div> <?php
	} 
	?><script type='text/javascript' language='javascript'><?php echo $this->rcpt_js_obj( $params ); ?></script>
	