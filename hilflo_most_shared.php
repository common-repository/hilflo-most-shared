<?php
/**
 * Plugin Name: Most social share
 
 * Plugin URI: http://www.bestofcomment.fr
 
 * Description: Show the most shared article on social network
 
 * Author: Hilflo
 
 * Version: 1.0
 
 * Author URI: http://www.bestofcomment.fr
 
 * License: GPLv2 or later 
 */

class h_most_shared_widget extends WP_Widget {

	// Constructor //

		function h_most_shared_widget() {
			$widget_ops = array( 'classname' => 'h_most_shared_widget', 'description' => 'Most shared on social newtork' ); // Widget Settings
			$control_ops = array( 'id_base' => 'h_most_shared_widget' ); // Widget Control Settings
			$this->WP_Widget( 'h_most_shared_widget', 'Most shared on social network', $widget_ops, $control_ops ); // Create the widget
		}

	// Extract Args //

		function widget($args, $instance) {
			extract( $args );
			$title 		= apply_filters('widget_title', $instance['h_ms_title']); // the widget title
			$r_c_number	= $instance['h_ms_number']; // How man article to show
			$g_api_key = $instance['h_ms_g_key']; // Google api key
					function get_tweet_count( $url ) {
	$url = urlencode($url);
	    $twitterEndpoint = "http://urls.api.twitter.com/1/urls/count.json?url=%s";
	    $fileData = file_get_contents(sprintf($twitterEndpoint, $url));
	    $json = json_decode($fileData, true);
	    unset($fileData); // free memory
	    //print_r($json);
	    return $json['count'];
}

function gplus_shares($url,$g_key){
 
// G+ DATA
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://clients6.google.com/rpc?key=$g_key");
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POSTFIELDS, '[{"method":"pos.plusones.get","id":"p",
"params":{"nolog":true,"id":"' . $url . '","source":"widget","userId":"@viewer","groupId":"@self"},
"jsonrpc":"2.0","key":"p","apiVersion":"v1"}]');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-type: application/json'));
 
$result = curl_exec ($ch);
curl_close ($ch);
 
$json = json_decode($result, true);
return intval($json[0]['result']['metadata']['globalCounts']['count']);
 
}

function fb_count($url){
$fql = 'SELECT total_count
        FROM link_stat WHERE url="'.$url.'"';
$json_fb = file_get_contents('https://api.facebook.com/method/fql.query?format=json&query=' . urlencode($fql));
$data_fb = json_decode($json_fb);
$total_fb = $data_fb[0]->total_count;
return $total_fb;
}

function h_ms_social_count($url,$post_id,$g_key){
$total = gplus_shares($url,$g_key) + get_tweet_count($url) + fb_count($url);
return $total;
}
	// Before widget //

			echo $before_widget;
		

	// Title of widget //

			if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;

	// Widget output //
	if(is_single()){
	$current_post_id = $GLOBALS['post']->ID;
	update_post_meta($current_post_id,'h_ms_social_count',h_ms_social_count(get_permalink($current_post_id),get_query_var($current_post_id),$g_api_key));
	}
	if(is_category()){
	$cat = '&cat='.get_query_var('cat');
	} else {
	$cat = '';
	}
	$h_ms_query = new WP_Query('orderby=meta_value_num&orde=DESC&meta_key=h_ms_social_count'.$cat.'');
	if($h_ms_query->have_posts()):
	while ( $h_ms_query->have_posts() ) : $h_ms_query->the_post();
	 global $post;
	?>
	<p><a href="<?php the_permalink() ?>" title="<?php the_title ?>"><?php the_title() ?></a></p>
	<?php 
	endwhile;
	endif;
	?>
			
			<?php
			

	// After widget //

			echo $after_widget;
		}

	// Update Settings //

		function update($new_instance, $old_instance) {
			$instance['h_ms_title'] = strip_tags($new_instance['h_ms_title']);
			$instance['h_ms_number'] = strip_tags($new_instance['h_ms_number']);
			$instance['h_ms_g_key'] = strip_tags($new_instance['h_ms_g_key']);
			return $instance;
		}

	// Widget Control Panel //
		

		function form($instance) {
		$defaults = array( 'h_ms_title' => 'Most shared', 'r_c_number' => 5);
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id('h_ms_title'); ?>">Title:</label>
			<input class="widefat" id="<?php echo $this->get_field_id('h_ms_title'); ?>" name="<?php echo $this->get_field_name('h_ms_title'); ?>'" type="text" value="<?php echo $instance['h_ms_title']; ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('h_ms_number'); ?>">Number to show:</label>
			<select id="<?php echo $this->get_field_id('r_c_number'); ?>" name="<?php echo $this->get_field_name('h_ms_number'); ?>'">
			<option value="<?php echo $instance['h_ms_number']; ?>"><?php echo $instance['h_ms_number']; ?></option>
			<option value="5">5</option>
			<option value="10">10</option>
			<option value="15">15</option>
			<option value="20">20</option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('h_ms_g_key'); ?>">Google api key:</label>
			<input class="widefat" id="<?php echo $this->get_field_id('h_ms_g_key'); ?>" name="<?php echo $this->get_field_name('h_ms_g_key'); ?>'" type="text" value="<?php echo $instance['h_ms_g_key']; ?>" />
		</p>
        <?php }

}

add_action('widgets_init', create_function('', 'return register_widget("h_most_shared_widget");'));


?>