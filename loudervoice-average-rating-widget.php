<?php
/*
  Plugin Name: LouderVoice Average Rating
  Plugin URI: https://www.loudervoice.com
  Description: A WordPress Widget to show your average review score and review count from LouderVoice.
  Version: 3.00
  Author: LouderVoice
  Author URI: https://www.loudervoice.com
*/

function avg_register_scripts() {
  $ldv_settings = get_option('ldv_css_dir');
  if (!$ldv_settings){
    $ldv_css_file = plugin_dir_url(__FILE__)."widgets/css/wp_plugin.css";
  }
  else{
    $ldv_css_file = $ldv_settings."wp_plugin.css";
  }
  wp_register_style('ldv_avg_widget_css', $ldv_css_file);
}
// Use wp_enqueue_scripts hook
add_action('wp_enqueue_scripts', 'avg_register_scripts');

if (!class_exists("loudervoice_average_multi")) {

  class loudervoice_average_multi extends WP_Widget {

    function __construct() {
      $widget_ops = array('classname' => 'loudervoice_average_multi', 'description' => 'Shows your average review score and review count from LouderVoice' );
      parent::__construct('ldv_average', 'LouderVoice Average', $widget_ops);
    }

    /* This is the code that gets displayed on the UI side,
     * what readers see.
     */
    function widget($args, $instance) {
      wp_enqueue_style('ldv_avg_widget_css');

      extract($args, EXTR_SKIP);

      echo $before_widget;
      $title = empty($instance['title']) ? '&nbsp;' : apply_filters('widget_title', $instance['title']);

      if (!empty($title)) {
        echo $before_title . $title . $after_title;
      }

      $customer_key = $instance['ldv_api_key'];

      // Old Customer Path was e.g. https://cdn.loudervoice.com/static/customers/therapyroom/
      // CSS URL is e.g. https://cdn.loudervoice.com/static/customers/therapyroom/css/

      $ldv_settings = get_option('ldv_css_dir');
      if (!$ldv_settings){
          $ldv_image_path = plugin_dir_url(__FILE__)."widgets/images/";
      }
      else{
        if(substr($ldv_settings, -1) == '/') {
            $ldv_settings = substr($ldv_settings, 0, -1);
        }
        $ldvurl = explode('/', $ldv_settings);
        array_pop($ldvurl);
        $ldv_image_path = implode("/",$ldvurl)."/images/";
      }

      $customer_reviews = 'https://api.loudervoice.com/v1/'.$customer_key.'/reviews/recent/?p=1&pp=5';
      $response = wp_remote_get($customer_reviews, array('sslverify' => false));
      $doc = new DOMDocument();
      $doc->loadXML($response['body']);

      $num_reviews = $doc->getElementsByTagName('count')->item(0)->nodeValue;
      $avg_rating = $doc->getElementsByTagName('avg_rating')->item(0)->nodeValue;
      $item = $doc->getElementsByTagName('item')->item(0)->nodeValue;
      $itemurl = $doc->getElementsByTagName('itemurl')->item(0)->nodeValue;
      $scaledscore = (float)(round($avg_rating*2))/2;
      $starsurl = $ldv_image_path."newstars".number_format((((float)round($avg_rating*2))/2),1).".png";

      $average_score_html = '<div itemscope itemtype="http://data-vocabulary.org/Review-aggregate"><meta itemprop="itemreviewed" content="';
      $average_score_html .= $item;
      $average_score_html .= '" />';
      $average_score_html .= '<span itemprop="rating" class="ldv-avg-rating" itemscope itemtype="http://data-vocabulary.org/Rating">';
      $average_score_html .= '<meta itemprop="average" content="';
      $average_score_html .= $avg_rating;
      $average_score_html .= '" />';
      $average_score_html .= '<meta itemprop="best" content="5" />';
      $average_score_html .= '</span>';
      $average_score_html .= '<div class="ldv-avg-star-count"><img class="ldv-avg-star" src="'.$starsurl.'" / >';
      $average_score_html .= '<span class="ldv-avg-count" itemprop="votes"> ';
      $average_score_html .= '<abbr title="'.round($avg_rating,2).'/5">';
      $average_score_html .= $num_reviews;
      $average_score_html .= '</abbr>';
      $average_score_html .= '</span> reviews</div></div>';
      $average_score_html .= '<div class="ldv-avg-readmore"><a href="' . $itemurl . '">Read Reviews</a></div>';



      echo $average_score_html;
      echo $after_widget;
    }

    function update($new_instance, $old_instance) {
      $instance = $old_instance;
      $instance['title'] = strip_tags($new_instance['title']);
      $instance['ldv_api_key'] = strip_tags($new_instance['ldv_api_key']);
      return $instance;
    }

    /* Back end, the interface shown in Appearance -> Widgets
     * administration interface.
     */
    function form($instance) {
      $instance = wp_parse_args( (array) $instance, array( 'title' => '', 'ldv_api_key' => '') );
      $title = strip_tags($instance['title']);
      $ldv_api_key = strip_tags($instance['ldv_api_key']);
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
<?php
    }
  }

  function ldv_average_init() {
    register_widget('loudervoice_average_multi');
  }
  add_action('widgets_init', 'ldv_average_init');

}

$wpdpd = new loudervoice_average_multi();

?>
