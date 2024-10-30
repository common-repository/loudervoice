<?php
/*
Plugin Name: LouderVoice Reviews
Plugin URI: https://www.loudervoice.com
Description: Display reviews on your pages or posts
Author: LouderVoice
Version: 3.00
Author URI: https://www.loudervoice.com/


Copyright 2012  LouderVoice (email : info@loudervoice.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
http://www.gnu.org/copyleft/gpl.html

 */
// Make sure we don't expose any info if called directly
if ( !function_exists( 'add_action' ) ) {
	echo "Not much happening here. Sorry ;)";
	exit;
}
define( 'LDV', '3.00' );
$pluginurl = plugin_dir_url(__FILE__);
if ( preg_match( '/^https/', $pluginurl ) && !preg_match( '/^https/', get_bloginfo('url') ) )
	$pluginurl = preg_replace( '/^https/', 'http', $pluginurl );

define( 'LDV_FRONT_URL', $pluginurl );
define( 'LDV_URL', plugin_dir_url(__FILE__) );
define( 'LDV_PATH', plugin_dir_path(__FILE__) );
define( 'LDV_BASENAME', dirname( plugin_basename( __FILE__ ) ) );

$ldv_plugin_location = parse_url( LDV_URL );
define( 'LDV_PLUGIN_LOCATION', $ldv_plugin_location['path']);   // used for location of Twitter receiver


// Plugin setup
require LDV_PATH.'inc/ldv-exclude.php';
require LDV_PATH.'inc/ldv-settings.php';
require LDV_PATH.'plugin-update.php';

if( is_admin() ){
     require LDV_PATH.'admin/ldv-admin-settings.php';
     require LDV_PATH.'admin/ldv-config.php';
     require LDV_PATH.'admin/ldv-post-meta.php';
     require LDV_PATH.'inc/ldv-ajax-functions.php';

}else{
    require LDV_PATH.'inc/ldv-api.php';
    require LDV_PATH.'frontend/ldv-display-reviews.php';
}


/**
 * Admin side js/css
 */
function ldv_admin_scripts(){
    wp_enqueue_script('ldv-reviews', plugins_url('/admin/js/ldv-admin.js', __FILE__), array('jquery'), LDV );
    wp_enqueue_style('ldv-reviews-admin', LDV_FRONT_URL.'/admin/css/lv-admin.css');
}
add_action( 'admin_enqueue_scripts', 'ldv_admin_scripts' );


function ldv_general_init(){
    // I18n
    load_plugin_textdomain('ldv-reviews', false, LDV_BASENAME . '/languages' );

    // exclude pages
    add_filter('get_pages', 'ldv_exclude_pages');
}
add_action('init', 'ldv_general_init');
