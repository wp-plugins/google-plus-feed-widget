<?php
/**
 * @package Google+ Feed Widget
 * @version 1.0
 */
/*
Plugin Name: Google Plus Feed Widget
Plugin URI: http://wordpress.org/extend/plugins/gplusfeed
Description: To feed your google+ stream into your blog in widget format
Author: Liz Quilty
Version: 1.0
Author URI: http://velofille.com
 */
// http://justintadlock.com/archives/2009/05/26/the-complete-guide-to-creating-widgets-in-wordpress-28
// http://azuliadesigns.com/wordpress-widgets-control-panels/
// 
//error_reporting(E_ALL);
add_action("widgets_init", array('Gplusfeed', 'register'));
register_activation_hook( __FILE__, array('Gplusfeed', 'activate'));
register_deactivation_hook( __FILE__, array('Gplusfeed', 'deactivate'));
class Gplusfeed {
  function activate(){
    $data = array( 'gplusid' => '114228869493885222559');
    if ( ! get_option('gplusfeed')){
      add_option('gplusfeed' , $data);
    } else {
      update_option('gplusfeed' , $data);
    }
  }
  function deactivate(){
    delete_option('gplusfeed');
  }
  function control(){
    $data = get_option('gplusfeed');
  ?>
  <p><label>GPlus ID<input name="gplusfeed_gplusid"
type="text" value="<?php echo $data['gplusid']; ?>" /></label></p>

  <?php
   if (isset($_POST['gplusfeed_gplusid'])){
      $data['gplusid'] = attribute_escape($_POST['gplusfeed_gplusid']);
      update_option('gplusfeed', $data);
    }
  }
  function widget($args){
    echo $args['before_widget'];
    echo $args['before_title'] . 'Google+ Feed' . $args['after_title'];

    include_once(ABSPATH.WPINC.'/feed.php');
    $gplusid = get_option('gplusfeed');
    // echo "<pre>";print_r($gplusid);echo "</pre>";
    $RSSURL = "http://plus-one-feed-generator.appspot.com/".$gplusid['gplusid'];
    $rss = fetch_feed("$RSSURL");
    if (!is_wp_error( $rss ) ) : // Checks that the object is created correctly 
    // Figure out how many total items there are, but limit it to 5. 
    $maxitems = $rss->get_item_quantity(5); 

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

function register(){
    register_sidebar_widget('Google+ Feed', array('Gplusfeed', 'widget'));
    register_widget_control('Google+ Feed', array('Gplusfeed', 'control'));
  }
}

