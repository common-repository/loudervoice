<?php
/**
 * @package LDV
 * 
 * Structure based on Yoast's Wordpress SEO Plugin Admin Class
 */


if ( ! class_exists( 'LDV_Admin' ) ) {
	
	class LDV_Admin extends LDV_Plugin_Admin{

		
		function __construct() {
                    add_action( 'init', array(&$this, 'init') );
		}
		
		function init() {                                        
                    
                    add_action( 'admin_init', array(&$this, 'options_init') );
                    add_action( 'admin_menu', array(&$this, 'register_settings_page') );                        
                    add_filter( 'plugin_action_links', array(&$this, 'add_action_link'), 10, 2 );                                        
		}

		function options_init() {
                    
                    register_setting( 'ldv_options', 'ldv_api_key' );						
                    register_setting( 'ldv_options', 'ldv_facebook_app_id' );	
                    register_setting( 'ldv_options', 'ldv_css_dir' );	
                    register_setting( 'ldv_options', 'ldv_language' );	
                    register_setting( 'ldv_options', 'ldv_page_template' );	
                    register_setting( 'ldv_options', 'ldv_post_type' );	       
                    register_setting( 'ldv_options', 'ldv_default_tags'); 
                    register_setting( 'ldv_options', 'ldv_default_group');           
                    register_setting( 'ldv_options', 'ldv_position');           
                    
                    register_setting( 'ldv_options', 'ldv_excluded_page_ids');  // Track child write-only pages to exclude
		}

                                

		function ldv_reviews_settings() {           
                    
                    if (!current_user_can('manage_options')){
                        wp_die( __('You do not have sufficient permissions to access this page.', 'ldv-reviews') );
                    }
                                            
                    if(!isset($ldvSettings)){
                        $ldvSettings = new LDV_Settings();
                    }
                    ?>
                        <div class="wrap">
                            <h2><?php _e('LouderVoice Reviews', 'ldv-reviews'); ?></h2>

                            <table class="form-table">                           
                                <tr>
                                    <td colspan="2">
                                        <form method="post" action="options.php">    
                                        <?php
                                            settings_fields( 'ldv_options' );                    
                                            do_settings_fields( 'ldv_options', 'ldv_review_settings_page' );
                                        ?>
                                    </td>
                                </tr>                                                                                                      
            <!-- API Key -->
                                <tr valign="top">
                                    <th scope="row">
                                        <?php _e('API Key', 'ldv-reviews'); ?>
                                    </th>          
                                    <td>
                                        <input size="60" name="ldv_api_key" type="text" value="<?php echo get_option('ldv_api_key'); ?>" />
                                        <br/>
                                        <small>
                                            <?php _e('Don\'t have an API Key? <a href="http://www.loudervoice.com/contact/">Contact LouderVoice now</a> to get one.', 'ldv-reviews'); ?>
                                        </small>
                                    </td>
                                </tr>
            <!-- FB App ID -->                                
                                <tr valign="top">
                                    <th scope="row">
                                       <?php _e('Facebook App ID', 'ldv-reviews'); ?>
                                    </th>          
                                    <td>
                                        <input size="60" name="ldv_facebook_app_id" type="text" value="<?php echo get_option('ldv_facebook_app_id'); ?>" />
                                        <br/>
                                        <small>
                                            <?php _e('<a href="http://www.loudervoice.com/contact/">Contact LouderVoice</a> for your Facebook App ID', 'ldv-reviews'); ?>
                                        </small>
                                    </td>
                                </tr>
                                
            <!-- CSS Dir     -->                                
                                <tr valign="top">
                                    <th scope="row">
                                        <?php _e('CSS URL', 'ldv-reviews'); ?>
                                    </th>          
                                    <td>
                                        <input size="60" name="ldv_css_dir" type="text" value="<?php echo get_option('ldv_css_dir'); ?>" />                                        
                                    </td>
                                </tr>
                                
           <!-- Language --> 
                                <tr valign="top">
                                    <th scope="row">
                                        <?php _e('Choose display language', 'ldv-reviews'); ?>
                                    </th>          
                                    <td>
                                        <select name="ldv_language">                                            
                                            <?php 
                                                // Get available LV Categories                                                
                                                $availableLvLanguages = $ldvSettings->ldv_get_available_languages();
                                                $ldv_language = get_option('ldv_language');
                                                foreach($availableLvLanguages as $lang => $val ): ?>
                                                <?php
                                                    $selected = '';
                                                    if($val == $ldv_language){
                                                        $selected = ' selected="selected" ';
                                                    }
                                                ?>
                                                <option value="<?php echo $val; ?>" <?php echo $selected; ?>>
                                                    <?php echo $lang; ?>
                                                </option>                    
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                                    
                                 <tr valign="top">
                                    <th scope="row" colspan="2">
                                        <h3><?php _e('Where to enable reviews', 'ldv-reviews'); ?></h3>
                                    </th>                                              
                                </tr>       
           <!-- templates -->            
                                <tr valign="top">
                                    <th scope="row">
                                        <?php _e('Enable on All Pages with the following templates', 'ldv-reviews'); ?>:
                                    </th> 
                                    <?php
                                        $pageTemplates = get_page_templates();                                             
                                    ?>
                                    <td>
                                        <?php if( count( $pageTemplates ) > 0): ?>
                                            <?php
                                                // Which were already checked?
                                                $checkedTemplates = get_option('ldv_page_template');
                                            ?>
                                            <?php foreach ( $pageTemplates as $name => $fileName): ?>
                                                    <?php 
                                                        $checked = '';
                                                        if(is_array( $checkedTemplates) && in_array($fileName, $checkedTemplates)){
                                                            $checked = "checked = 'checked'";
                                                        }
                                                    ?>
                                                <label>                                                                                                        
                                                    <input type="checkbox" name="ldv_page_template[]" value="<?php echo $fileName; ?>" <?php echo $checked; ?>/>
                                                    <?php echo $name; ?>                                                    
                                                </label>
                                                <br/>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                                <?php _e('You have no custom page templates set up', 'ldv-reviews'); ?>.
                                        <?php endif; ?>                                        
                                    </td>
                                </tr>
            <!-- custom posts -->       
                                <tr valign="top">
                                    <th scope="row">
                                        <?php _e('Enable on following Custom Post types', 'ldv-reviews'); ?>:
                                    </th> 
                                    <?php
                                        $customPostTypes = get_post_types( array( '_builtin' => false), 'objects');                                             
                                        $checkedPostTypes = get_option('ldv_post_type');
                                    ?>
                                    <td>                                        
                                        <?php if( count( $customPostTypes ) > 0): ?>                                        
                                            <?php foreach ($customPostTypes as $customPost):                                         
                                                    $checked = '';
                                                        if(is_array( $checkedPostTypes ) && in_array($customPost->name, $checkedPostTypes)){
                                                            $checked = "checked = 'checked'";
                                                        }
                                                ?>
                                                <label>                                                    
                                                    <input type="checkbox" name="ldv_post_type[]" value="<?php echo $customPost->name; ?>" <?php echo $checked; ?>/>                                                    
                                                    <?php echo $customPost->labels->name; ?>                                                     
                                                </label>
                                                <br/>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                                <?php _e('There are no custom post types set up', 'ldv-reviews');?>.
                                        <?php endif; ?>    
                                    </td>
                                </tr>
                                
                                <tr valign="top">
                                    <th scope="row">
                                        <?php _e('Default Review Tags'); ?>
                                        <br/>
                                        <small>
                                            <?php _e('If using the options above, please enter tags to assign to all reviews. (You can override these on individual pages)', 'ldv-reviews'); ?>
                                        </small>
                                    </th>          
                                    <td>
                                        <input size="60" name="ldv_default_tags" type="text" value="<?php echo get_option('ldv_default_tags'); ?>" />
                                        <br/>
                                        <small>
                                            <?php _e('Please separate tags with a comma', 'ldv-reviews'); ?>
                                        </small>
                                    </td>
                                </tr>
                                
                                <tr>
                                    <th scope="row">
                                    <?php _e( 'Default Review Category', 'ldv-reviews'); ?>
                                        <br/>
                                        <small>
                                            <?php _e('If using the options above, please choose a Category to assign to all reviews. (You can override these on individual pages)', 'ldv-reviews'); ?>
                                        </small>
                                    </th>
                                    <td>
                                        <select name="ldv_default_group">
                                            <option value=''><?php _e( 'Choose a Category', 'ldv-reviews') ; ?></option>
                                            <?php 
                                                // Get available LV Categories                                                
                                                $availableLdCategories = $ldvSettings->ldv_get_available_categories();
                                                $ldv_default_group = get_option('ldv_default_group');
                                                foreach($availableLdCategories as $cat => $val ): ?>
                                                <?php
                                                    $selected = '';
                                                    if($val == $ldv_default_group){
                                                        $selected = ' selected="selected" ';
                                                    }
                                                ?>
                                                <option value="<?php echo $val; ?>" <?php echo $selected; ?>>
                                                    <?php echo $cat; ?>
                                                </option>                    
                                            <?php endforeach; ?>
                                        </select>
                                    </td>
                                </tr>
                              
                                
                                <tr valign="top">
                                    <th colspan="2">
                                        <h3><?php _e('Review Positioning'); ?></h3>
                                    </th>
                                </tr>
                                <tr>
                                    <th class="row">
                                        <?php _e('Where should reviews be displayed on the page'); ?>?
                                    </th>                                    
                                    <td>
                                        <?php
                                            $ldv_position = get_option('ldv_position');
                                            $ldv_position = ( empty( $ldv_position ) || $ldv_position === false ) ? 'auto' : $ldv_position;
                                            $checked = '';
                                            if( $ldv_position === 'auto'){
                                                $checked = ' checked="checked"';
                                            }
                                        ?>
                                        <ul style="list-style-type: none;">
                                            <li>
                                                <label>
                                                    <input type="radio" value="auto" name="ldv_position" id="ldv_position_content" <?php echo $checked; ?> />
                                                    <?php _e('Automatic Positioning - after page content (using the_content() hook)'); ?>
                                                </label>
                                            </li>
                                            <?php
                                                $checked = '';
                                                if( $ldv_position === 'manual'){
                                                    $checked = ' checked="checked"';
                                                }
                                            ?>
                                            <li>
                                                <label>
                                                    <input type="radio" value="manual" name="ldv_position" id="ldv_position_manual" <?php echo $checked; ?> />
                                                    <?php _e('Manual Positioning - add'); ?> <code>if(function_exists('ldv_display_reviews')){echo ldv_display_reviews(); }</code> <?php _e('to your template'); ?> 
                                                </label>
                                            </li>                                            
                                        </ul> 
                                    </td>
                                </tr> 
                                
                                 <tr valign="top">
                                    <td colspan="2">
                                        <p class="submit">
                                            <input type="submit" class="button-primary" value="<?php _e('Save Settings', 'ldv-reviews'); ?>" />
                                        </p>
                                        </form>
                                    </td>
                                </tr>
                                   
                            </table>                           
                            
                    </div>
                    <?php 
                    
		}                                                
		
	} // end class        	
        $ldv_admin = new LDV_Admin();
}   