<?php
/**
 * @package Google+ Feed Widget
 * @version 1.2
 */
/*
Plugin Name: Google Plus Feed Widget
Plugin URI: http://wordpress.org/extend/plugins/google-plus-feed-widget
Description:  This plugin feeds your public google+ stream into a widget, quick and easy way to get your google plus posts into your wordpress blog.  Just enable it, drag the widget where you want, and add in your google id (the number in the address bar on your google profile).  This plugin only shows PUBLIC posts, until the API arrives there is very little else I can do.
Author: Liz Quilty
Version: 1.2
Author URI: http://velofille.com/
*/

add_action( 'widgets_init', 'gplusfeed_load_widgets' );
add_filter( 'wp_feed_cache_transient_lifetime', create_function( '$a', 'return 900;' ) );


function gplusfeed_load_widgets() {
	register_widget( 'gplusfeed_Widget' );
}

class gplusfeed_Widget extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function gplusfeed_Widget() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'gplusfeed', 'description' => __('A Google+ Feed that displays a person\'s public posts in a widget.', 'gplusfeed') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'gplusfeed-widget' );

		/* Create the widget. */
		$this->WP_Widget( 'gplusfeed-widget', __('Google+ Feed', 'gplusfeed'), $widget_ops, $control_ops );
	}

	/**
	 * How to display the widget on the screen.
	 */
	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$gplusid = $instance['gplusid'];
		$rss_count = $instance['rss_count'];


		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;

		/* After widget (defined by themes). */
		echo $after_widget;

		include_once(ABSPATH.WPINC.'/feed.php');
		$RSSURL = "http://plus-one-feed-generator.appspot.com/".$gplusid;
		$rss = fetch_feed("$RSSURL");
		if (!is_wp_error( $rss ) ) : // Checks that the object is created correctly 
			// Figure out how many total items there are, but limit it to 5. 
			$maxitems = $rss->get_item_quantity($rss_count); 
	
			// Build an array of all the items, starting with element 0 (first element).
			$rss_items = $rss->get_items(0, $maxitems); 
		endif;
		?>
		 <ul>
		    <?php if ($maxitems == 0) echo '<li>No items.</li>';
		    else
		    // Loop through each feed item and display each item as a hyperlink.
		    foreach ( $rss_items as $item ) : ?>
		    <li>
		        <a href='<?php echo esc_url( $item->get_permalink() ); ?>'
		        title='<?php echo 'Posted '.$item->get_date('j F Y | g:i a'); ?>'>
		        <?php echo esc_html( $item->get_title() ); ?></a>
		    </li>
		    <?php endforeach; ?>
		</ul>
		<?


	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['gplusid'] = strip_tags( $new_instance['gplusid'] );
		$instance['rss_count'] = $new_instance['rss_count'];


		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'title' => 'Google+ Feed', 'gplusid' => '114228869493885222559', 'rss_count' => '5' );
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<!-- Title -->
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'hybrid'); ?></label>
			<input id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" value="<?php echo $instance['title']; ?>" style="width:100%;" />
		</p>

		<!-- Google Plus ID -->
		<p>
			<label for="<?php echo $this->get_field_id( 'gplusid' ); ?>"><?php _e('Your Gooogle+ ID:', 'gplusfeed'); ?></label>
			<input id="<?php echo $this->get_field_id( 'gplusid' ); ?>" name="<?php echo $this->get_field_name( 'gplusid' ); ?>" value="<?php echo $instance['gplusid']; ?>" style="width:100%;" />
		</p>

		<!-- RSS Count -->
		<p>
			<label for="<?php echo $this->get_field_id( 'rss_count' ); ?>"><?php _e('How many :', 'gplusfeed'); ?></label> 
			<input id="<?php echo $this->get_field_id( 'rss_count' ); ?>" name="<?php echo $this->get_field_name( 'rss_count' ); ?>" value="<?php echo $instance['rss_count']; ?>" style="width:100%;" />
		</p>

	<?php
	}
}

?>
