<?php
/** 
 * Handle Admin settings page
 */
if ( !class_exists('LDV_Plugin_Admin') ) { 
    
    class LDV_Plugin_Admin {

            var $filename   = 'loudervoice-reviews/loudervoice-reviews.php';	
            var $accesslvl  = 'manage_options';
            var $adminpages = array( 'ldv_reviews_settings');

            function __construct() {
            }

            
            function register_settings_page() {                
                add_submenu_page('options-general.php', __('LouderVoice Settings', 'ldv-reviews'), __('LouderVoice Settings', 'ldv-reviews'),$this->accesslvl, 'ldv_reviews_settings', array(&$this,'ldv_reviews_settings'));					
            }

            function plugin_options_url() {
                return admin_url( 'options-general.php?page=ldv_reviews_settings' );
            }

            /**
             * Add a link to the settings page to the plugins list
             */
            function add_action_link( $links, $file ) {                
                static $this_plugin;
                if( empty($this_plugin) ) $this_plugin = $this->filename;                
                if ( $file == $this_plugin ) {
                        $settings_link = '<a href="' . $this->plugin_options_url() . '">' . __('Settings', 'ldv-reviews') . '</a>';
                        array_unshift( $links, $settings_link );
                }
                
                return $links;
            }
            
                        
            /**
             * All settings are left, except the key & fbId            
             */
            function on_deactivate(){
                
                delete_option('ldv_api_key');
                delete_option('ldv_facebook_app_id');                 
            }

            /**
             * Remove/Delete everything 
             */
            function on_uninstall(){
                // important: check if the file is the one that was registered with the uninstall hook (function)
                if ( __FILE__ != WP_UNINSTALL_PLUGIN )
                    return;

                // delete the stored settings
                delete_option('ldv_api_key');
                delete_option('ldv_facebook_app_id');   						                                        
                delete_option('ldv_css_dir' );						                                        
                delete_option('ldv_page_template' );						                                        
                delete_option('ldv_post_type' );						                                        
                delete_option('ldv_default_tags' );						                                        
                delete_option('ldv_default_group' );	                
                delete_option('ldv_excluded_page_ids');  
            }

}
            
        
}