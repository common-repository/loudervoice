<?php
/*
Plugin Name: LouderVoice Random
Plugin URI: https://www.loudervoice.com
Description: A WordPress Widget to show a single 4/5 star random review from your latest reviews
Version: 3.00
Author: LouderVoice
Author URI: https://www.loudervoice.com
*/

function random_register_scripts() {
  $ldv_settings = get_option('ldv_css_dir');
  if (!$ldv_settings){
    $ldv_css_file = plugin_dir_url(__FILE__)."widgets/css/wp_plugin.css";
  }
  else{
    $ldv_css_file = $ldv_settings."wp_plugin.css";
  }
  wp_register_style('ldv_random_widget_css', $ldv_css_file);
}
// Use wp_enqueue_scripts hook
add_action('wp_enqueue_scripts', 'random_register_scripts');

if (!class_exists("loudervoice_random_multi")) {

	class loudervoice_random_multi extends WP_Widget {

		function __construct() {
			$widget_ops = array('classname' => 'loudervoice_random_multi', 'description' => 'Shows a single 4/5 star random review from your latest LouderVoice reviews' );
			parent::__construct('ldv_random', 'LouderVoice Random', $widget_ops);
		}

		/* This is the code that gets displayed on the UI side,
		 * what readers see.
		 */
		function widget($args, $instance) {
          wp_enqueue_style('ldv_random_widget_css');

        	extract($args, EXTR_SKIP);
			echo $before_widget;
			$title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);

			if (!empty($title)) {
				echo $before_title . $title . $after_title;
			}

        $customer_key = $instance['ldv_api_key'];
        $maxlen = $instance['ldv_review_length'];
 
        // Old Customer Path was e.g. https://cdn.loudervoice.com/static/customers/therapyroom/
        // CSS URL is e.g. https://cdn.loudervoice.com/static/customers/therapyroom/css/

        $ldv_settings = get_option('ldv_css_dir');
        if (!$ldv_settings){
            $ldv_image_path = plugin_dir_url(__FILE__)."/widgets/images/";
        }
        else{
          if(substr($ldv_settings, -1) == '/') {
              $ldv_settings = substr($ldv_settings, 0, -1);
          }
          $ldvurl = explode('/', $ldv_settings);
          array_pop($ldvurl);
          $ldv_image_path = implode("/",$ldvurl)."/images/";
        }


        $customer_reviews = 'https://api.loudervoice.com/v1/'.$customer_key.'/reviews/recent/?p=1&pp=20';
        $response = wp_remote_get($customer_reviews, array('sslverify' => false));
        $doc = new DOMDocument();
        $doc->loadXML($response['body']);

        $num_reviews = $doc->getElementsByTagName('count')->item(0)->nodeValue;
        $pages = $doc->getElementsByTagName('pages')->item(0)->nodeValue;

        $arrFeeds = array();
        foreach ($doc->getElementsByTagName('review') as $node) {
	          $itemFeed = array (
	            'id' => $node->getElementsByTagName('id')->item(0)->nodeValue,
                'item' => $node->getElementsByTagName('item')->item(0)->nodeValue,
                'description' => $node->getElementsByTagName('description')->item(0)->nodeValue,
                'itemurl' => $node->getElementsByTagName('itemurl')->item(0)->nodeValue,
                'date' => $node->getElementsByTagName('dtreviewed')->item(0)->nodeValue,
                'name' => $node->getElementsByTagName('name')->item(0)->nodeValue,
                'rating' => $node->getElementsByTagName('rating')->item(0)->nodeValue
            );
			if ($itemFeed["rating"] > 3){
                array_push($arrFeeds, $itemFeed);
			}
        }

        if (!empty($arrFeeds)){
            $review = $arrFeeds[array_rand($arrFeeds, 1)];
            $scaledscore = (float)(round($review["rating"]*2))/2;
            $starsurl = $ldv_image_path."newstars-small".number_format((((float)round($review["rating"]*2))/2),1).".png";

            $random_review = '<div class="ldv-random-author"> from '.$review["name"].'</div>';
            $random_review .= '<img class="ldv-random-star" src="'.$starsurl.'" / >';
            $details = strip_tags($review["description"]);
            if(strlen($review["description"])> $maxlen){
                $details = substr($details,0, $maxlen);
                $i = strrpos($details," ");
                $details = substr($details,0,$i);
            }
            $details = $details." ..... ";
            $random_review .= '<div class="ldv-random-description">'. $details.'</div>';
            $random_review .= '<div class="ldv-random-readmore"><a href="'.$review["itemurl"].'#lv-review-'.$review["id"].'">Read More</a></div>';

            echo $random_review;
        }
        echo $after_widget;
	}

		function update($new_instance, $old_instance) {
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
			$instance['ldv_api_key'] = strip_tags($new_instance['ldv_api_key']);
			$instance['ldv_review_length'] = strip_tags($new_instance['ldv_review_length']);
			return $instance;
		}

		/* Back end, the interface shown in Appearance -> Widgets
		 * administration interface.
		 */
		function form($instance) {
			$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'ldv_api_key' => '', 'ldv_review_length' => '' ) );
			$title = strip_tags($instance['title']);
			$ldv_api_key = strip_tags($instance['ldv_api_key']);
			$ldv_review_length = strip_tags($instance['ldv_review_length']);
			?>

<p>
<label for="<?php echo $this->get_field_id('title'); ?>">Title:
    <input
	   class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
	   name="<?php echo $this->get_field_name('title'); ?>" type="text"
	   value="<?php echo esc_attr($title); ?>"
	/>
</label>
</p>
<p>
<label for="<?php echo $this->get_field_id('ldv_api_key'); ?>">API Key:
    <input
	   class="widefat" id="<?php echo $this->get_field_id('ldv_api_key'); ?>"
	   name="<?php echo $this->get_field_name('ldv_api_key'); ?>" type="text"
	   value="<?php echo esc_attr($ldv_api_key); ?>"
	/>
</label>
</p>
<p>
<label for="<?php echo $this->get_field_id('ldv_review_length'); ?>">No. of Characters:
    <input
	   class="widefat" id="<?php echo $this->get_field_id('ldv_review_length'); ?>"
	   name="<?php echo $this->get_field_name('ldv_review_length'); ?>" type="text"
	   value="<?php echo esc_attr($ldv_review_length); ?>"
	/>
</label>
</p>
			<?php
		}
	}

	function ldv_random_init() {
		register_widget('loudervoice_random_multi');
	}
	add_action('widgets_init', 'ldv_random_init');

}

$wpdpd = new loudervoice_random_multi();

?>
