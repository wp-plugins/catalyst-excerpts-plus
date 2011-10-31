<?php
/*
Plugin Name: Catalyst Excerpts Plus
Plugin URI: http://imnotmarvin.com/catalyst-excerpts-plus/
Description: Catalyst Excerpts Plus builds on the Catalyst Excerpts widget with additional features. This is based on the Catalyst Excerpts Widget created by Eric Hamm that comes with the Catalyst Framework. This plugin requires the Catalyst framework. <a href="http://wp.me/P1hBKZ-f" target="_blank">Learn more about Catalyst...</a>
Version: 1.2.2
Author: Michael Davis
Author URI: http://imnotmarvin.com
License: GPLv2

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

For the latest copy of the GNU General Public License, see <http://www.gnu.org/licenses/>.

1.0 - Initial release. Display random excerpts, allow html from titles, allow html from excerpt content.
1.1 - Changed thumbnail options for outside/inside to outside/inside-top/inside-bottom. This way you can control whether the thumbnail displays above the excerpt content or below it.
1.2 - Minor fixes and added Buy Me A Beer button, fixed hottest topics option, added coldest topics option, added latest tweets option and ability to rename the widget title.
1.2.1 - Quick fix to issue with widget name not showing properly.
1.2.2 - Minor code change for future update compatibility.


HOW TO RENAME WIDGET
There are cases where you may want to rename the widget created by this plugin. By default it will be displayed as "Catalyst | Excerpts (Plus)" to more closely resemble the original plugin created by Catalyst. Some people will want to see the widget's display title to be visible in the widget area admin page, but WordPress wants to display the widget name first, then the widget title. Most likely the widget title will not be visible due to the widget's long name.

Instructions to rename the widget:

// Copy and paste the following PHP function to your custom-functions.php file in the root of the dynamik folder (or functions.php in the root of your child theme if you are using a child theme):

//== Function renames the Catalyst Excerpts (Plus) widget ==//
add_action( 'widgets_init', 'cep_rename_widget' );
function cep_rename_widget() 
{
	$cep_widget_name = 'Catalyst | Excerpts (Plus)'; //Put the new name between the apostrophies ('').
	
	return $cep_widget_name;
}
//== End Function ==//

// Put the new name between the apostrophies ('') and save the file to the root of your dynamik folder. If left blank, this function will not change the name.
// That's it! Now, if you rename the widget but later upgrade the plugin, your custom name will still remain unchanged.
// If you need further assistance, see this thread in the Catalyst forum:
// http://catalysttheme.com/forum/showthread.php?4193-Catalyst-Excerpts-(Plus)

*/


function cep_get_permalink() {
	global $post;
	if ( get_post_meta($post->ID, 'pnd_title_url', true) ) {
		if ( get_post_meta($post->ID, 'pnd_new', true) ) {
			$link = get_post_meta($post->ID, 'pnd_title_url', true) . '" target="_blank';
		}else{
			$link = get_post_meta($post->ID, 'pnd_title_url', true);
		}
	}else{
		$link = get_permalink($post_id);
	}
	return $link;
}


remove_action( 'widgets_init', create_function( '', "register_widget( 'catalyst_excerpt_widget' );" ) );

// Adds a feature to the Catalyst Excerpts widget for displaying excerpts randomly

add_action( 'widgets_init', create_function( '', "register_widget( 'catalyst_excerpt_plus_widget' );" ) );
class catalyst_excerpt_plus_widget extends WP_Widget {

	function catalyst_excerpt_plus_widget()
	{
		if( function_exists('cep_rename_widget') ){
			$cep_widget_name = cep_rename_widget();
			if( $cep_widget_name == "" ) {
				$cep_widget_name = 'Catalyst | Excerpts (Plus)';
			}
		}else{
			$cep_widget_name = 'Catalyst | Excerpts (Plus)';
		}
		
		$widget_setup = array( 'classname' => 'excerpt-widget-plus', 'description' => __( 'Catalyst Excerpts (Plus) - Displays post excerpts and more within Catalyst sites', 'catalyst' ) );
		$widget_panel = array( 'width' => 505, 'height' => 350, 'id_base' => 'excerpt-widget-plus' );
		$this->WP_Widget( 'excerpt-widget-plus', __( $cep_widget_name, 'catalyst' ), $widget_setup, $widget_panel );
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
			'thumbnail-location' => 'inside-top',
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
			if( $options['display-posts'] == "random") {
				$featured_content = new WP_Query( array( 'caller_get_posts' => 1, 'post_type' => 'post', 'showposts' => $options['num-posts'], 'offset' => $options['offset'], 'orderby' => 'rand' ) );
			}
			elseif( $options['display-posts'] == "hottest") {
				$featured_content = new WP_Query( array( 'caller_get_posts' => 1, 'post_type' => 'post', 'showposts' => $options['num-posts'], 'offset' => $options['offset'], 'orderby' => 'comment_count', 'order' => 'DESC' ) );
			}
			elseif( $options['display-posts'] == "coldest") {
				$featured_content = new WP_Query( array( 'caller_get_posts' => 1, 'post_type' => 'post', 'showposts' => $options['num-posts'], 'offset' => $options['offset'], 'orderby' => 'comment_count', 'order' => 'ASC' ) );
			}
			else{
				$featured_content = new WP_Query( array( 'caller_get_posts' => 1, 'post_type' => 'post', 'showposts' => $options['num-posts'], 'offset' => $options['offset'] ) );
			}
		}
		elseif( $options['content-display'] == "post_id" )
		{
			$featured_content = new WP_Query( array( 'post_type' => 'post', 'p' => $options['post-id'] ) );
		}
		elseif( $options['content-display'] == "category" )
		{
			if( $options['display-posts'] == "random") {
				$featured_content = new WP_Query( array( 'post_type' => 'post', 'cat' => $options['cat'], 'showposts' => $options['num-posts'],'offset' => $options['offset'], 'orderby' => 'rand' ) );
			}
			elseif( $options['display-posts'] == "hottest") {
				$featured_content = new WP_Query( array( 'post_type' => 'post', 'cat' => $options['cat'], 'showposts' => $options['num-posts'],'offset' => $options['offset'], 'orderby' => 'comment_count', 'order' => 'DESC' ) );
			}
			elseif( $options['display-posts'] == "coldest") {
				$featured_content = new WP_Query( array( 'post_type' => 'post', 'cat' => $options['cat'], 'showposts' => $options['num-posts'],'offset' => $options['offset'], 'orderby' => 'comment_count', 'order' => 'ASC' ) );
			}
			else{
				$featured_content = new WP_Query( array( 'post_type' => 'post', 'cat' => $options['cat'], 'showposts' => $options['num-posts'],'offset' => $options['offset'] ) );
			}
		}
		elseif( $options['content-display'] == "tweets") 
		{
			if( !empty( $options['class'] ) )
			{
				$options['class'] = ' ' . $options['class'];
			}
			if( $options['tweet-meta-placement'] == 'inline' )
			{
				$tweetmetaplace = ' - ';
			}else{
				$tweetmetaplace = '<br />';
			}
			
			catalyst_hook_before_excerpt_widget( $catalyst_layout_id . '_catalyst_hook_before_excerpt_widget' );
			
			echo '<div '; post_class( 'catalyst-excerpt-widget' . $options['class'] ); echo '>';
			
			
			catalyst_hook_before_excerpt_widget_title( $catalyst_layout_id . '_catalyst_hook_before_excerpt_widget_title' );
			
			if( !empty( $options['title'] ) )
			{
				echo $before_title . apply_filters( 'widget_title', $options['title'] ) . $after_title;
			}
			
			catalyst_hook_after_excerpt_widget_title( $catalyst_layout_id . '_catalyst_hook_after_excerpt_widget_title' );
			
			catalyst_hook_before_excerpt_widget_content( $catalyst_layout_id . '_catalyst_hook_before_excerpt_widget_content' );
						
			$twitwrapopen = '<div class="catalyst-excerpt-widget-inner">';
			$twitwrapclose = '</div><div style="clear:both;"></div>';
			$tweetwrapopen = '<div class="entry-content"><p><span class="status">';
			$metawrapopen = '</span>' . $tweetmetaplace . '<span class="meta">';
			$metawrapclose = '</span></p>';
			$tweetwrapclose = '</div><div style="clear:both;"></div>';
			
			display_latest_tweets( $options['twitter-username'], './data/twitter.txt', $options['num-posts'], true, $twitwrapopen, $twitwrapclose, $tweetwrapopen, $metawrapopen, $metawrapclose, $tweetwrapclose );

			echo '</div><div style="clear:both;"></div>';
			
			catalyst_hook_after_excerpt_widget_content( $catalyst_layout_id . '_catalyst_hook_after_excerpt_widget_content' );
		
			echo '<div style="clear:both;"></div>';
			
			echo $after_widget;
		}
		else
		{
			$featured_content = new WP_Query( array( 'page_id' => $options['page'] ) );
		}
		
		$thumbnail_alignment = ( $options['thumbnail-alignment'] == 'none' ) ? '' : 'align' . $options['thumbnail-alignment'];
		
		if( $options['content-display'] != "tweets" ) {
		
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
					printf( '<h2 class="entry-title"><a href="%s" title="%s">%s</a></h2>', cep_get_permalink(), the_title_attribute( 'echo=0' ), the_title('','',0) );
				}else{
					printf( '<h2 class="entry-title"><a href="%s" title="%s">%s</a></h2>', cep_get_permalink(), the_title_attribute( 'echo=0' ), the_title_attribute( 'echo=0' ) );
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
			if( has_post_thumbnail() && !empty( $options['display-thumbnails'] ) && $options['thumbnail-location'] == 'inside-top' )
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
		
		if( function_exists( 'has_post_thumbnail' ) )
		{
			if( has_post_thumbnail() && !empty( $options['display-thumbnails'] ) && $options['thumbnail-location'] == 'inside-bottom' )
			{
				ob_start();
				the_post_thumbnail( ( $options['thumbnail-size'] ), array( 'class' => $thumbnail_alignment ) );
				$the_post_thumbnail = ob_get_clean();
				
				printf( '<a href="%s" title="%s">%s</a>', get_permalink(), the_title_attribute('echo=0'), $the_post_thumbnail );
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
			'twitter-username' => '',
			'tweet-meta-placement' => 'inline',
			'num-posts' => '1',
			'offset' => '0',
			'cat' => '',
			'post-id' => '0',
			'page' => '0',
			'content-type' => 'excerpt',
			'display-title' => 0,
			'display-thumbnails' => 0,
			'display-posts' => 'off',
			'allow-title-html' => 0,
			'allow-excerpt-html' => 0,
			'thumbnail-size' => '',
			'thumbnail-alignment' => 'left',
			'thumbnail-location' => 'inside-top',
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
				<p>
					<strong>
						<em>
							(Plus)
						</em>
					</strong>
					<br />
					<input id="cep-twitter-options" type="radio" name="<?php echo $this->get_field_name( 'content-display' ); ?>" value="tweets" <?php if( $content_display == 'tweets' ) echo 'checked="checked" '; ?>/>
					<label>
						<?php _e( 'Display Latest Tweets', 'catalyst' ); ?>
					</label>
					<div id="cep-twitter-options-box" style="">
						<span style="margin-left:15px;">
							<?php _e( 'Twitter Username', 'catalyst' ); ?>
						</span>
						<br />
						<span style="margin-left:15px;">
							@
							<input type="text" id="<?php echo $this->get_field_id( 'twitter-username' ); ?>" name="<?php echo $this->get_field_name( 'twitter-username' ); ?>" value="<?php echo esc_attr( $options['twitter-username'] ); ?>" style="width:110px;" />
						<span>
						<br />
						<label style="margin-left:15px;" for="<?php echo $this->get_field_id( 'tweet-meta-placement' ); ?>">
							<?php _e( 'Meta Placement', 'catalyst' ); ?>
						</label>
						<br />
						<select style="width:110px;margin-left:15px;" id="<?php echo $this->get_field_id( 'tweet-meta-placement' ); ?>" name="<?php echo $this->get_field_name( 'tweet-meta-placement' ); ?>">
							<option value="inline" <?php selected( 'inline' , $options['tweet-meta-placement'] ); ?>><?php _e( 'Inline', 'catalyst' ); ?></option>
							<option value="new-line" <?php selected( 'new-line' , $options['tweet-meta-placement'] ); ?>><?php _e( 'New Line', 'catalyst' ); ?></option>
						</select>
					</div>
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
				
				<p class="cep_select">
					<?php _e( 'Page To Display', 'catalyst' ); ?><br />
					<?php wp_dropdown_pages( array( 'selected' => $options['page'], 'name' => $this->get_field_name( 'page' ), 'orderby' => 'Name' , 'hierarchical' => 1, 'hide_empty' => '0' ) ); ?>
				</p>

				<p>
				<strong><em>(Plus)</em></strong><br />
				<label for="<?php echo $this->get_field_id( 'display-posts' ); ?>"><?php _e( 'Special Filters', 'catalyst' ); ?></label><br />
				<select id="<?php echo $this->get_field_id( 'display-posts' ); ?>" name="<?php echo $this->get_field_name( 'display-posts' ); ?>">
					<option value="off" <?php selected( 'off' , $options['display-posts'] ); ?>><?php _e( 'None', 'catalyst' ); ?></option>
					<option value="random" <?php selected( 'random' , $options['display-posts'] ); ?>><?php _e( 'Random Posts', 'catalyst' ); ?></option>
					<option value="hottest" <?php selected( 'hottest' , $options['display-posts'] ); ?>><?php _e( 'Hottest Topics', 'catalyst' ); ?></option>
					<option value="coldest" <?php selected( 'coldest' , $options['display-posts'] ); ?>><?php _e( 'Coldest Topics', 'catalyst' ); ?></option>
				</select>
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
				
			</div>
			
			<div style="background:#F1F1F1 url( <?php echo $image_url ?> ) repeat-x; border:1px solid #E3E3E3; margin-bottom:10px; padding:10px 10px 0;">				
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
						<option value="inside-top" <?php selected( 'inside-top' , $options['thumbnail-location'] ); ?>><?php _e( 'Inside - Top', 'catalyst' ); ?></option>
						<option value="inside-bottom" <?php selected( 'inside-bottom' , $options['thumbnail-location'] ); ?>><?php _e( 'Inside - Bottom', 'catalyst' ); ?></option>
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
			
			<div style="margin: 91px 0 10px 23px; position: relative; bottom: 0;">
				<p>
					<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=2U8865P2N52GA" target="_blank">
						<img src="<?php echo plugins_url('catalyst-excerpts-plus/buymebeer-200.jpg','__FILE__'); ?>" />
					</a>
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

/**
 * TWITTER FEED PARSER
 * 
 * @version     1.1.1
 * @author      Jonathan Nicol
 * @link http://f6design.com/journal/2010/10/07/display-recent-twitter-tweets-using-php/
 * 
 * Notes:
 * We employ caching because Twitter only allows their RSS feeds to be accesssed 150
 * times an hour per user client.
 * --
 * Dates can be displayed in Twitter style (e.g. "1 hour ago") by setting the 
 * $twitter_style_dates param to true.
 * 
 * Credits:
 * Hashtag/username parsing based on: http://snipplr.com/view/16221/get-twitter-tweets/
 * Feed caching: http://www.addedbytes.com/articles/caching-output-in-php/
 * Feed parsing: http://boagworld.com/forum/comments.php?DiscussionID=4639
 */


function display_latest_tweets(
	$twitter_user_id,
	$cache_file,
	$tweets_to_display = 100,
	$ignore_replies = true,
	$twitter_wrap_open = '<ul id="twitter">',
	$twitter_wrap_close = '</ul>',
	$tweet_wrap_open = '<li><span class="status">',
	$meta_wrap_open = '</span><span class="meta"> ',
	$meta_wrap_close = '</span>',
	$tweet_wrap_close = '</li>',
	$date_format = 'g:i A M jS',
	$twitter_style_dates = true)
{

	// Seconds to cache feed (1/2 hour).
	$cachetime = 60*30;
	// Time that the cache was last filled.
	$cache_file_created = ((@file_exists($cache_file))) ? @filemtime($cache_file) : 0;

	// A flag so we know if the feed was successfully parsed.
	$tweet_found = false;

	// Show file from cache if still valid.
	if (time() - $cachetime < $cache_file_created) {

		$tweet_found = true;
		// Display tweets from the cache.
		@readfile($cache_file); 

	} else {

		// Cache file not found, or old. Fetch the RSS feed from Twitter.
		$rss = @file_get_contents('http://twitter.com/statuses/user_timeline/'.$twitter_user_id.'.rss');

		if($rss) {

			// Parse the RSS feed to an XML object.
			$xml = @simplexml_load_string($rss);

			if($xml !== false) {

				// Error check: Make sure there is at least one item.
				if (count($xml->channel->item)) {

					$tweet_count = 0;

					// Start output buffering.
					ob_start();

					// Open the twitter wrapping element.
					$twitter_html = $twitter_wrap_open;

					// Iterate over tweets.
					foreach($xml->channel->item as $tweet) {
						// Twitter feeds begin with the username, "e.g. User name: Blah"
						// so we need to strip that from the front of our tweet.
						$tweet_desc = substr($tweet->description,strpos($tweet->description,":")+2);
						$tweet_desc = htmlspecialchars($tweet_desc);
						$tweet_first_char = substr($tweet_desc,0,1);

						// If we are not gnoring replies, or tweet is not a reply, process it.
						if ($tweet_first_char!='@' || $ignore_replies==false){

							$tweet_found = true;
							$tweet_count++;

							// Add hyperlink html tags to any urls, twitter ids or hashtags in the tweet.
							$tweet_desc = preg_replace('/(https?:\/\/[^\s"<>]+)/','<a href="$1">$1</a>',$tweet_desc);
							$tweet_desc = preg_replace('/(^|[\n\s])@([^\s"\t\n\r<:]*)/is', '$1<a href="http://twitter.com/$2">@$2</a>', $tweet_desc);
							$tweet_desc = preg_replace('/(^|[\n\s])#([^\s"\t\n\r<:]*)/is', '$1<a href="http://twitter.com/search?q=%23$2">#$2</a>', $tweet_desc);

							// Convert Tweet display time to a UNIX timestamp. Twitter timestamps are in UTC/GMT time.
							$tweet_time = strtotime($tweet->pubDate);       
							if ($twitter_style_dates){
								// Current UNIX timestamp.
								$current_time = time();
								$time_diff = abs($current_time - $tweet_time);
								switch ($time_diff) 
								{
									case ($time_diff < 60):
											$display_time = $time_diff.' seconds ago';                  
											break;      
									case ($time_diff >= 60 && $time_diff < 3600):
											$min = floor($time_diff/60);
											$display_time = $min.' minutes ago';                  
											break;      
									case ($time_diff >= 3600 && $time_diff < 86400):
											$hour = floor($time_diff/3600);
											$display_time = 'about '.$hour.' hour';
											if ($hour > 1){ $display_time .= 's'; }
											$display_time .= ' ago';
											break;          
									case ($time_diff >= 86400 && $time_diff < 604800):
											$day = floor($time_diff/86400);
											$display_time = 'about '.$day.' days ago';
											if ($day < 2){ $display_time = 'yesterday'; }                                           
											break;

									case ($time_diff >= 604800 && $time_diff < 2592000):
											$week = floor($time_diff/604800);
											$display_time = 'about '.$week.' weeks ago';
											if ($week < 2){ $display_time = 'last week'; }

											break;

									default:
											$display_time = date($date_format,$tweet_time);
											break;
								}
							} else {
									$display_time = date($date_format,$tweet_time);
							}

							// Render the tweet.
							$twitter_html .= $tweet_wrap_open.$tweet_desc.$meta_wrap_open.'<a href="'.$tweet->link.'">'.$display_time.'</a>'.$meta_wrap_close.$tweet_wrap_close;

						}

						// If we have processed enough tweets, stop.
						if ($tweet_count >= $tweets_to_display){
								break;
						}

					}

					// Close the twitter wrapping element.
					$twitter_html .= $twitter_wrap_close;
					echo $twitter_html;

					// Generate a new cache file.
					$file = @fopen($cache_file, 'w');

					// Save the contents of output buffer to the file, and flush the buffer. 
					@fwrite($file, ob_get_contents()); 
					@fclose($file); 
					ob_end_flush();

				}
			}
		}
	} 
	// In case the RSS feed did not parse or load correctly, show a link to the Twitter account.
	if (!$tweet_found){
			echo $twitter_wrap_open.$tweet_wrap_open.'Oops, our twitter feed is unavailable right now. '.$meta_wrap_open.'<a href="http://twitter.com/'.$twitter_user_id.'">Follow us on Twitter</a>'.$meta_wrap_close.$tweet_wrap_close.$twitter_wrap_close;
	}
}
?>