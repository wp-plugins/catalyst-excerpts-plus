<?php
/*
Plugin Name: Catalyst Excerpts Plus
Plugin URI: http://imnotmarvin.com/catalyst-excerpts-plus/
Description: Catalyst Excerpts Plus builds on the Catalyst Excerpts widget with additional features. This is based on the Catalyst Excerpts Widget created by Eric Hamm that comes with the Catalyst Framework. This plugin requires the Catalyst framework. <a href="http://wp.me/P1hBKZ-f" target="_blank">Learn more about Catalyst...</a>
Version: 1.0
Author: Michael Davis
Author URI: http://imnotmarvin.com
License: GPLv2

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

For the latest copy of the GNU General Public License, see <http://www.gnu.org/licenses/>.

0.1 - Optional display random excerpts
0.2 - Optional don't strip html from titles
0.3 - Optional don't strip html from excerpt
0.4 - Sort by hottest topics
*/

remove_action( 'widgets_init', create_function( '', "register_widget( 'catalyst_excerpt_widget' );" ) );

// Adds a feature to the Catalyst Excerpts widget for displaying excerpts randomly

add_action( 'widgets_init', create_function( '', "register_widget( 'catalyst_excerpt_plus_widget' );" ) );
class catalyst_excerpt_plus_widget extends WP_Widget {

	function catalyst_excerpt_plus_widget()
	{
		$widget_setup = array( 'classname' => 'excerpt-widget-plus', 'description' => __( 'Displays Excerpts in Widget Areas', 'catalyst' ) );
		$widget_panel = array( 'width' => 505, 'height' => 350, 'id_base' => 'excerpt-widget-plus' );
		$this->WP_Widget( 'excerpt-widget-plus', __( 'Catalyst | Excerpts (Plus)', 'catalyst' ), $widget_setup, $widget_panel );
	}

	function widget( $args, $options )
	{
		global $wp_query, $catalyst_layout_id;
		
		extract( $args );
		
		$options = wp_parse_args( ( array ) $options, array(
			'title' => '',
			'content-display' => 'latest_post',
			'num-posts' => '1',
			'offset' => '0',
			'cat' => '',
			'post-id' => '0',
			'page' => '0',
			'content-type' => 'excerpt',
			'display-title' => 1,
			'display-thumbnails' => 0,
			'thumbnail-size' => 'thumbnail',
			'thumbnail-alignment' => 'left',
			'thumbnail-location' => 'inside',
			'byline-author' => 0,
			'byline-date' => 0,
			'byline-comments' => 0,
			'byline-edit-link' => 0,
			'byline-author-text' => __('Written <em>by</em>', 'catalyst'),
			'byline-date-text' => __('<em>on</em>', 'catalyst'),
			'post-meta' => 0,
			'more-text' => __('Read more ', 'catalyst') . '&raquo;',
			'excerpt-read-more-placement' => 'inline',
			'class' => ''
		) );
		
		echo $before_widget;
		
		if( $options['content-display'] == "latest_post" )
		{ 
			if( isset( $options['display-random-posts'] ) ) {
				$featured_content = new WP_Query( array( 'caller_get_posts' => 1, 'post_type' => 'post', 'showposts' => $options['num-posts'], 'offset' => $options['offset'], 'orderby' => 'rand' ) );
			}else{
				$featured_content = new WP_Query( array( 'caller_get_posts' => 1, 'post_type' => 'post', 'showposts' => $options['num-posts'], 'offset' => $options['offset'] ) );
			}
		}
		elseif( $options['content-display'] == "post_id" )
		{
			$featured_content = new WP_Query( array( 'post_type' => 'post', 'p' => $options['post-id'] ) );
		}
		elseif( $options['content-display'] == "category" )
		{
			if( isset( $options['display-random-posts'] ) ) {
				$featured_content = new WP_Query( array( 'post_type' => 'post', 'cat' => $options['cat'], 'showposts' => $options['num-posts'],'offset' => $options['offset'], 'orderby' => 'rand' ) );
			}else{
				$featured_content = new WP_Query( array( 'post_type' => 'post', 'cat' => $options['cat'], 'showposts' => $options['num-posts'],'offset' => $options['offset'] ) );
			}
		}
		else
		{
			$featured_content = new WP_Query( array( 'page_id' => $options['page'] ) );
		}
		
		$thumbnail_alignment = ( $options['thumbnail-alignment'] == 'none' ) ? '' : 'align' . $options['thumbnail-alignment'];
		
		if( !empty( $options['class'] ) )
		{
			$options['class'] = ' ' . $options['class'];
		}
		
		if( !empty( $options['title'] ) )
		{
			echo $before_title . apply_filters( 'widget_title', $options['title'] ) . $after_title;
		}

		if( $featured_content->have_posts() ) : while( $featured_content->have_posts() ) : $featured_content->the_post();
		
		catalyst_hook_before_excerpt_widget( $catalyst_layout_id . '_catalyst_hook_before_excerpt_widget' );
		
		echo '<div '; post_class( 'catalyst-excerpt-widget' . $options['class'] ); echo '>';
		echo '<div class="catalyst-excerpt-widget-inner">';
		
		if( function_exists( 'has_post_thumbnail' ) )
		{
			if( has_post_thumbnail() && !empty( $options['display-thumbnails'] ) && $options['thumbnail-location'] == 'outside' )
			{
				ob_start();
				the_post_thumbnail( ( $options['thumbnail-size'] ), array( 'class' => $thumbnail_alignment ) );
				$the_post_thumbnail = ob_get_clean();
				
				printf( '<a href="%s" title="%s">%s</a>', get_permalink(), the_title_attribute('echo=0'), $the_post_thumbnail );
			}
		}
		
		catalyst_hook_before_excerpt_widget_title( $catalyst_layout_id . '_catalyst_hook_before_excerpt_widget_title' );

		if( !empty( $options['display-title'] ) )
		{
			if( isset( $options['allow-title-html'] ) ) {
				printf( '<h2 class="entry-title"><a href="%s" title="%s">%s</a></h2>', get_permalink(), the_title_attribute( 'echo=0' ), the_title('','',0) );
			}else{
				printf( '<h2 class="entry-title"><a href="%s" title="%s">%s</a></h2>', get_permalink(), the_title_attribute( 'echo=0' ), the_title_attribute( 'echo=0' ) );
			}
		}
		
		catalyst_hook_after_excerpt_widget_title( $catalyst_layout_id . '_catalyst_hook_after_excerpt_widget_title' );
		
		if( $options['content-display'] != 'page' && ( !empty( $options['byline-author'] ) || !empty( $options['byline-date'] ) || !empty( $options['byline-comments'] ) || !empty( $options['byline-edit-link'] ) ) )
		{
			if( !empty( $options['byline-author'] ) ) { $byline_author = $options['byline-author-text'] . ' ' . do_shortcode( '[byline_author]' ); } else { $byline_author = ''; }
			if( !empty( $options['byline-date'] ) ) { $byline_date = $options['byline-date-text'] . ' ' . do_shortcode( '[byline_date]' ); } else { $byline_date = ''; }
			if( !empty( $options['byline-comments'] ) ) { $byline_comments = do_shortcode( '[byline_comments]' ); } else { $byline_comments = ''; }
			if( !empty( $options['byline-edit-link'] ) ) { $edit_link = do_shortcode( '[edit_link]' ); } else { $edit_link = ''; }
			
			printf( '<div class="byline-meta">%s %s %s %s</div>', $byline_author, $byline_date, $byline_comments, $edit_link );
		}
		
		catalyst_hook_before_excerpt_widget_content( $catalyst_layout_id . '_catalyst_hook_before_excerpt_widget_content' );
		
		echo '<div class="entry-content">';
		
		if( $options['content-type'] == 'excerpt' )
		{
			$this->catalyst_echo_excerpt_widget_content( $options );
		}
		else
		{	
			global $more;
			$more = 0;
			the_content( esc_html( $options['more-text'] ) );
		}
		
		echo '</div>';
		
		if( $options['post-meta'] == 1 && $options['content-display'] != 'page' )
		{
			catalyst_hook_excerpt_widget_post_meta( $catalyst_layout_id . '_catalyst_hook_excerpt_widget_post_meta' );
		}
		
		catalyst_hook_after_excerpt_widget_content( $catalyst_layout_id . '_catalyst_hook_after_excerpt_widget_content' );
		
		echo '</div>';
		echo '</div>';
		echo '<div style="clear:both;"></div>';
		
		catalyst_hook_after_excerpt_widget( $catalyst_layout_id . '_catalyst_hook_after_excerpt_widget' );
			
		endwhile; endif;
	
		echo '<div style="clear:both;"></div>';

		echo $after_widget;
		wp_reset_query();
	}
	
	//content
	function catalyst_echo_excerpt_widget_content( $options )
	{
		$thumbnail_alignment = ( $options['thumbnail-alignment'] == 'none' ) ? '' : 'align' . $options['thumbnail-alignment'];
		
		// To filter html or not to filter
		if( isset( $options['allow-excerpt-html'] ) ) {
			$thestuff = get_the_content();
		}else{
			$thestuff = get_the_excerpt();
		}
		
		if( $options['excerpt-limit'] == 'limit_characters' )
		{
			if( isset( $options['char-limit'] ) && $options['char-limit'] == '0' )
			{
				$excerpt_widget_content = '';
			}
			elseif( !empty( $options['char-limit'] ) )
			{
				//$excerpt_widget_content = substr( str_replace( '[...]', '', get_the_excerpt() ), 0, $options['char-limit'] ) . apply_filters( 'excerpt_widget_more', '[...]' );
				
				//Unstripped
				$excerpt_widget_content = substr( str_replace( '[...]', '', $thestuff ), 0, $options['char-limit'] ) . apply_filters( 'excerpt_widget_more', '[...]' );
			}
			else
			{
				//$excerpt_widget_content = get_the_excerpt();
				
				//Unstripped
				$excerpt_widget_content = $thestuff;
			}
		}
		else
		{
			//$excerpt_widget_content = get_the_excerpt();
			
			//Unstripped
			$excerpt_widget_content = $thestuff;
		}
		
		if( function_exists( 'has_post_thumbnail' ) )
		{
			if( has_post_thumbnail() && !empty( $options['display-thumbnails'] ) && $options['thumbnail-location'] == 'inside' )
			{
				ob_start();
				the_post_thumbnail( ( $options['thumbnail-size'] ), array( 'class' => $thumbnail_alignment ) );
				$the_post_thumbnail = ob_get_clean();
				
				printf( '<a href="%s" title="%s">%s</a>', get_permalink(), the_title_attribute('echo=0'), $the_post_thumbnail );
			}
		}
		
		if( !empty( $options['more-text'] ) )
		{
			$more_text = ' <a class="excerpt-read-more" href="' . get_permalink() . '">' . $options['more-text'] . '</a>';
		}
		else
		{
			$more_text = '';
		}
		
		if( !empty( $excerpt_widget_content ) )
		{
			if( $options['excerpt-read-more-placement'] != 'new-line' )
			{
				echo '<p>' . $excerpt_widget_content . $more_text . '</p>' . "\n";
			}
			else
			{
				echo '<p>' . $excerpt_widget_content . '</p>' . "\n";
				echo '<p>' . $more_text . '</p>' . "\n";
			}
		}
	}

	function update($new_options, $old_options)
	{
		return $new_options;
	}

	function form( $options )
	{ 
		$options = wp_parse_args( ( array )$options, array(
			'title' => '',
			'content-display' => 'latest_post',
			'num-posts' => '1',
			'offset' => '0',
			'cat' => '',
			'post-id' => '0',
			'page' => '0',
			'content-type' => 'excerpt',
			'display-title' => 0,
			'display-thumbnails' => 0,
			'display-random-posts' => 0,
			'allow-title-html' => 0,
			'allow-excerpt-html' => 0,
			'thumbnail-size' => '',
			'thumbnail-alignment' => 'left',
			'thumbnail-location' => 'inside',
			'byline-author' => 0,
			'byline-date' => 0,
			'byline-comments' => 0,
			'byline-edit-link' => 0,
			'byline-author-text' => __('Written <em>by</em>', 'catalyst'),
			'byline-date-text' => __('<em>on</em>', 'catalyst'),
			'post-meta' => 0,
			'more-text' => __('Read more ', 'catalyst') . '&raquo;',
			'excerpt-read-more-placement' => 'inline',
			'class' => ''
		));
		$image_url = get_bloginfo( 'template_directory' ) . '/lib/css/images/catalyst-admin-bg-box-bg.png';
	?>
		<div style="width:510px; float:left;">
		
			<div style="background:#F1F1F1 url( <?php echo $image_url ?> ) repeat-x; border:1px solid #E3E3E3; margin-top:-7px; margin-bottom:10px; padding:10px 10px 0;">
				<p>
					<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title' ); ?>:</label>
					<input type="text" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo esc_attr( $options['title'] ); ?>" style="width:100%;" />
				</p>
			</div>
		</div>
		
		<div style="width:250px; float:left;">
		
			<div style="background:#F1F1F1 url( <?php echo $image_url ?> ) repeat-x; border:1px solid #E3E3E3; margin-bottom:10px; padding:10px 10px 0;">
				<p>
					<?php $content_display = $options['content-display']; ?>
					<input type="radio" name="<?php echo $this->get_field_name( 'content-display' ); ?>" value="latest_post" <?php if( $content_display == 'latest_post' ) echo 'checked="checked" '; ?>/><label><?php _e( 'Display Latest Posts', 'catalyst' ); ?></label><br />
					<input type="radio" name="<?php echo $this->get_field_name( 'content-display' ); ?>" value="category" <?php if( $content_display == 'category' ) echo 'checked="checked" '; ?>/><label><?php _e( 'Display Latest Posts In Category', 'catalyst' ); ?></label><br />
					<input type="radio" name="<?php echo $this->get_field_name( 'content-display' ); ?>" value="post_id" <?php if( $content_display == 'post_id' ) echo 'checked="checked" '; ?>/><label><?php _e( 'Display A Featured Post', 'catalyst' ); ?></label><br />
					<input type="radio" name="<?php echo $this->get_field_name( 'content-display' ); ?>" value="page" <?php if( $content_display == 'page' ) echo 'checked="checked" '; ?>/><label><?php _e( 'Display A Featured Page', 'catalyst' ); ?></label>
				</p>
			</div>
			
			<div style="background:#F1F1F1 url( <?php echo $image_url ?> ) repeat-x; border:1px solid #E3E3E3; margin-bottom:10px; padding:10px 10px 0;">
				<p>
					<input type="text" id="<?php echo $this->get_field_id( 'num-posts' ); ?>" name="<?php echo $this->get_field_name( 'num-posts' ); ?>" value="<?php echo esc_attr( $options['num-posts'] ); ?>" style="width:40px;" /> <?php _e( 'Number of Posts To Display', 'catalyst' ); ?>
				</p>
				
				<p>
					<input type="text" id="<?php echo $this->get_field_id('offset'); ?>" name="<?php echo $this->get_field_name('offset'); ?>" value="<?php echo esc_attr($options['offset']); ?>" style="width:40px;" /> <?php _e( 'Latest Post\'s Offset Number', 'catalyst' ); ?>
				</p>
				
				<p>
					<?php _e( 'Category To Display', 'catalyst' ); ?><br />
					<?php wp_dropdown_categories( array( 'selected' => $options['cat'], 'name' => $this->get_field_name( 'cat' ), 'orderby' => 'Name' , 'hierarchical' => 1, 'hide_empty' => '0' ) ); ?>
				</p>
				
				<p>
					<input type="text" id="<?php echo $this->get_field_id( 'post-id' ); ?>" name="<?php echo $this->get_field_name( 'post-id' ); ?>" value="<?php echo esc_attr( $options['post-id'] ); ?>" style="width:40px;" /> <?php _e( 'Featured Post ID', 'catalyst' ); ?>
				</p>
				
				<p>
					<?php _e( 'Page To Display', 'catalyst' ); ?><br />
					<?php wp_dropdown_pages( array( 'selected' => $options['page'], 'name' => $this->get_field_name( 'page' ), 'orderby' => 'Name' , 'hierarchical' => 1, 'hide_empty' => '0' ) ); ?>
				</p>
				
				<div style="width:100%;border-bottom:'dashed,1px,grey';"><strong><em>Plus</em></strong></div>
								
				<p>
				<input id="<?php echo $this->get_field_id( 'display-random-posts' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'display-random-posts' ); ?>" value="1" <?php checked( 1, $options['display-random-posts'] ); ?>/> <label for="<?php echo $this->get_field_id( 'display-random-posts' ); ?>"><?php _e( 'Display Random Results', 'catalyst' ); ?></label><br />
				</p>
				
				<p>
				<input id="<?php echo $this->get_field_id( 'allow-title-html' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'allow-title-html' ); ?>" value="1" <?php checked( 1, $options['allow-title-html'] ); ?>/> <label for="<?php echo $this->get_field_id( 'allow-title-html' ); ?>"><?php _e( 'Allow HTML in Title', 'catalyst' ); ?></label><br />
				</p>
				
				<p>
				<input id="<?php echo $this->get_field_id( 'allow-excerpt-html' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'allow-excerpt-html' ); ?>" value="1" <?php checked( 1, $options['allow-excerpt-html'] ); ?>/> <label for="<?php echo $this->get_field_id( 'allow-excerpt-html' ); ?>"><?php _e( 'Allow HTML in Excerpt', 'catalyst' ); ?></label><br />
				</p>
				
			</div>
			
			<div style="background:#F1F1F1 url( <?php echo $image_url ?> ) repeat-x; border:1px solid #E3E3E3; margin-bottom:10px; padding:10px 10px 0;">
				<p>
					<label for="<?php echo $this->get_field_id( 'content-type' ); ?>"><?php _e( 'Content Type', 'catalyst' ); ?></label>
					<select id="<?php echo $this->get_field_id( 'content-type' ); ?>" name="<?php echo $this->get_field_name( 'content-type' ); ?>">
						<option value="excerpt" <?php selected( 'excerpt' , $options['content-type'] ); ?>><?php _e( 'Excerpt', 'catalyst' ); ?></option>
						<option value="full-content" <?php selected( 'full-content' , $options['content-type'] ); ?>><?php _e( 'Full Content', 'catalyst' ); ?></option>
					</select>
				</p>
				
				<p>
					<?php $excerpt_limit = $options['excerpt-limit']; ?>
					<input type="radio" name="<?php echo $this->get_field_name( 'excerpt-limit' ); ?>" value="limit_default" <?php if( empty( $excerpt_limit ) || $excerpt_limit == 'limit_default' ) echo 'checked="checked" '; ?>/><label><?php _e( 'Default Word Limit', 'catalyst' ); ?></label><br />
					<input type="radio" name="<?php echo $this->get_field_name( 'excerpt-limit' ); ?>" value="limit_characters" <?php if( $excerpt_limit == 'limit_characters' ) echo 'checked="checked" '; ?>/><label><?php _e( 'Custom Character Limit', 'catalyst' ); ?></label> <input type="text" id="<?php echo $this->get_field_id( 'char-limit' ); ?>" name="<?php echo $this->get_field_name( 'char-limit' ); ?>" value="<?php echo esc_attr( $options['char-limit'] ); ?>" style="width:40px;" />
				</p>
			</div>
		
		</div>
		
		<div style="width:250px; margin-left:10px; float:left;">
		
			<div style="background:#F1F1F1 url(<?php echo $image_url ?>) repeat-x; border:1px solid #E3E3E3; margin-bottom:10px; padding:10px 10px 0;">
				<p>
					<input id="<?php echo $this->get_field_id( 'display-thumbnails' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'display-thumbnails' ); ?>" value="1" <?php checked( 1, $options['display-thumbnails'] ); ?>/> <label for="<?php echo $this->get_field_id( 'display-thumbnails' ); ?>"><?php _e( 'Display Thumbnails', 'catalyst' ); ?></label><br />
					<label for="<?php echo $this->get_field_id( 'thumbnail-alignment' ); ?>"><?php _e( 'Alignment', 'catalyst' ); ?></label>
					<select id="<?php echo $this->get_field_id( 'thumbnail-alignment' ); ?>" name="<?php echo $this->get_field_name( 'thumbnail-alignment' ); ?>">
						<option value="none" <?php selected( 'none' , $options['thumbnail-alignment'] ); ?>><?php _e( 'None', 'catalyst' ); ?></option>
						<option value="left" <?php selected( 'left' , $options['thumbnail-alignment'] ); ?>><?php _e( 'Left', 'catalyst' ); ?></option>
						<option value="center" <?php selected( 'center' , $options['thumbnail-alignment'] ); ?>><?php _e( 'Center', 'catalyst' ); ?></option>
						<option value="right" <?php selected( 'right' , $options['thumbnail-alignment'] ); ?>><?php _e( 'Right', 'catalyst' ); ?></option>
					</select><br />
					<label for="<?php echo $this->get_field_id( 'thumbnail-location'); ?>"><?php _e( 'Location', 'catalyst' ); ?></label>
					<select id="<?php echo $this->get_field_id( 'thumbnail-location'); ?>" name="<?php echo $this->get_field_name( 'thumbnail-location' ); ?>">
						<option value="inside" <?php selected( 'inside' , $options['thumbnail-location'] ); ?>><?php _e( 'Inside', 'catalyst' ); ?></option>
						<option value="outside" <?php selected( 'outside' , $options['thumbnail-location'] ); ?>><?php _e( 'Outside', 'catalyst' ); ?></option>
					</select><br />
					<label for="<?php echo $this->get_field_id( 'thumbnail-size' ); ?>"><?php _e( 'Image Size', 'catalyst' ); ?>:</label>
					<?php $sizes = catalyst_get_image_sizes(); ?>
					<select id="<?php echo $this->get_field_id( 'thumbnail-size' ); ?>" name="<?php echo $this->get_field_name( 'thumbnail-size' ); ?>">
						<?php
						foreach( ( array )$sizes as $name => $size ) :
						echo '<option style="padding-right: 10px;" value="' . esc_attr( $name ) . '" ' . selected( $name, $options['thumbnail-size'], FALSE) . '>' . esc_html( $name ) . ' (' . $size['width'] . 'w x ' . $size['height'] . 'h)</option>';
						endforeach;
						?>
					</select>
				</p>
			</div>
			
			<div style="background:#F1F1F1 url( <?php echo $image_url ?> ) repeat-x; border:1px solid #E3E3E3; margin-bottom:10px; padding:10px 10px 0;">
				<p>
					<input id="<?php echo $this->get_field_id( 'display-title' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'display-title' ); ?>" value="1" <?php checked( 1, $options['display-title'] ); ?>/> <label for="<?php echo $this->get_field_id( 'display-title' ); ?>"><?php _e( 'Display Post/Page Title', 'catalyst' ); ?></label>
				</p>
				
				<p>
					<?php _e( 'Post Byline Content:', 'catalyst' ); ?>
					<input id="<?php echo $this->get_field_id( 'byline-author' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'byline-author' ); ?>" value="1" <?php checked( 1, $options['byline-author'] ); ?>/> <label for="<?php echo $this->get_field_id( 'byline-author' ); ?>"><?php _e( 'Author', 'catalyst' ); ?></label><br />
					<input id="<?php echo $this->get_field_id( 'byline-date' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'byline-date' ); ?>" value="1" <?php checked( 1, $options['byline-date'] ); ?>/> <label for="<?php echo $this->get_field_id( 'byline-date' ); ?>"><?php _e( 'Date', 'catalyst' ); ?></label>
					<input id="<?php echo $this->get_field_id( 'byline-comments' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'byline-comments' ); ?>" value="1" <?php checked( 1, $options['byline-comments'] ); ?>/> <label for="<?php echo $this->get_field_id( 'byline-comments' ); ?>"><?php _e( 'Comments', 'catalyst' ); ?></label>
					<input id="<?php echo $this->get_field_id( 'byline-edit-link' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'byline-edit-link' ); ?>" value="1" <?php checked( 1, $options['byline-edit-link'] ); ?>/> <label for="<?php echo $this->get_field_id( 'byline-edit-link' ); ?>"><?php _e( 'Edit Link', 'catalyst' ); ?></label>
				</p>
				
				<p>
					<?php _e( 'Text Before Author:', 'catalyst' ); ?><br />
					<input type="text" id="<?php echo $this->get_field_id( 'byline-author-text' ); ?>" name="<?php echo $this->get_field_name( 'byline-author-text' ); ?>" value="<?php echo esc_attr( $options['byline-author-text'] ); ?>" style="width:100%;" />
				<br />
					<?php _e( 'Text Before Date:', 'catalyst' ); ?><br />
					<input type="text" id="<?php echo $this->get_field_id( 'byline-date-text' ); ?>" name="<?php echo $this->get_field_name( 'byline-date-text' ); ?>" value="<?php echo esc_attr( $options['byline-date-text'] ); ?>" style="width:100%;" />
				</p>
				
				<p>
					<input id="<?php echo $this->get_field_id( 'post-meta' ); ?>" type="checkbox" name="<?php echo $this->get_field_name( 'post-meta' ); ?>" value="1" <?php checked( 1, $options['post-meta'] ); ?>/> <label for="<?php echo $this->get_field_id( 'post-meta' ); ?>"><?php _e( 'Display Post-Bottom Meta', 'catalyst' ); ?></label>
				</p>
			</div>
			
			<div style="background:#F1F1F1 url( <?php echo $image_url ?> ) repeat-x; border:1px solid #E3E3E3; margin-bottom:10px; padding:10px 10px 0;">
				<p>
					<label for="<?php echo $this->get_field_id( 'more-text' ); ?>"><?php _e( 'Read More Text:', 'catalyst' ); ?></label>
					<input type="text" id="<?php echo $this->get_field_id( 'more-text' ); ?>" name="<?php echo $this->get_field_name( 'more-text' ); ?>" value="<?php echo esc_attr( $options['more-text'] ); ?>" style="width:119px;" />
				<br />
					<label for="<?php echo $this->get_field_id( 'excerpt-read-more-placement' ); ?>"><?php _e( 'Read More Placement', 'catalyst' ); ?></label>
					<select id="<?php echo $this->get_field_id( 'excerpt-read-more-placement' ); ?>" name="<?php echo $this->get_field_name( 'excerpt-read-more-placement' ); ?>">
						<option value="inline" <?php selected( 'inline' , $options['excerpt-read-more-placement'] ); ?>><?php _e( 'Inline', 'catalyst' ); ?></option>
						<option value="new-line" <?php selected( 'new-line' , $options['excerpt-read-more-placement'] ); ?>><?php _e( 'New Line', 'catalyst' ); ?></option>
					</select>
				</p>
			</div>
		
		</div>
		
		<div style="width:510px; float:left;">
		
			<div style="background:#F1F1F1 url( <?php echo $image_url ?> ) repeat-x; border:1px solid #E3E3E3; margin-bottom:10px; padding:10px 10px 0;">
				<p>
					<?php _e( 'Custom Class:', 'catalyst' ); ?><br />
					<input type="text" id="<?php echo $this->get_field_id( 'class' ); ?>" name="<?php echo $this->get_field_name( 'class' ); ?>" value="<?php echo esc_attr( $options['class'] ); ?>" style="width:100%;" />
				</p>
			</div>
			
		</div>
	<?php 
	}
}
?>