<?php

/**
 * Add LouderVoice metabox to pages, posts, etc
 */
function ldv_init(){
  
  // Option is always there for Pages & Posts
  add_meta_box("ldv-meta", __("LouderVoice Reviews"), "ldv_metabox_setup", "post", "normal", "high");
  add_meta_box("ldv-meta", __("LouderVoice Reviews"), "ldv_metabox_setup", "page", "normal", "high");
  
  // Display on All Custom Post Types
  $customPostTypes = get_post_types( array( '_builtin' => false), 'objects');
  if(is_array( $customPostTypes )){
      foreach( $customPostTypes as $postType ){
          add_meta_box("ldv-meta", __("LouderVoice Reviews", 'ldv-reviews'), "ldv_metabox_setup", $postType->name , "normal", "high");
      }
  }
  
}
add_action("admin_init", "ldv_init");

function ldv_metabox_setup(){
    
    global $post;
    $custom = get_post_custom($post->ID);
    
    $ldv_enable_reviews         = ( isset($custom["ldv_enable_reviews"][0] ) ) ? $custom["ldv_enable_reviews"][0] : '0'; // default off
    $ldv_item                   = ( isset($custom["ldv_item"][0] ) ) ? $custom["ldv_item"][0] : get_the_title();    // default title
    $ldv_limit                  = ( isset($custom["ldv_limit"][0] ) ) ? $custom["ldv_limit"][0] : '5';              // default 5
    $ldv_tags                   = ( isset($custom["ldv_tags"][0] ) ) ? $custom["ldv_tags"][0] : '';
    $ldv_group                  = ( isset($custom["ldv_group"][0] ) ) ? $custom["ldv_group"][0] : '';
    $ldv_write                  = ( isset($custom["ldv_write"][0] ) ) ? $custom["ldv_write"][0] : '1';              // default off
    $ldv_child_write_page_url   = ( isset($custom["ldv_child_write_page_url"][0] ) ) ? $custom["ldv_child_write_page_url"][0] : '';
    $ldv_parent_write_page_url  = ( isset($custom["ldv_parent_write_page_url"][0] ) ) ? $custom["ldv_parent_write_page_url"][0] : '';
    $ldv_single_language        = ( isset($custom["ldv_single_language"][0] ) ) ? $custom["ldv_single_language"][0] : get_option('ldv_language');
    
    if( !empty( $ldv_parent_write_page_url )){
        $pageParentUrl = get_permalink( $post->post_parent );
    }
    
    // Get available LV Categories
    if(!isset( $ldvSettings )){
        $ldvSettings = new LDV_Settings();
    }
    $availableLdCategories = $ldvSettings->ldv_get_available_categories();
        
    // paging limit options
    $limits = array(
        '1', '5', '10', '15', '20'
    );                                                                                                                        
                    
        
    echo '<input type="hidden" name="ldv_meta_box_nonce" value="', wp_create_nonce(basename(__FILE__)), '" />';  


        // If this page has a parent url set, it's only used for displaying
        // the review writing form and should not display the full
        // set of LV reviews options
        if(isset( $pageParentUrl)): ?>
        
            <table class="form-table" id="ldv-meta">
                <tr>
                    <th scope="row" colspan="2">
                        <?php _e( 'This page will display a form for people to write new reviews. When published, these reviews can be viewed at the following page:', 'ldv-reviews'); ?>
                    </th>                    
                </tr>

                <tr>
                    <th scope="row">
                        <?php _e( 'Item being reviewed', 'ldv-reviews'); ?>
                    </th>
                    <td>
                        <input name="ldv_parent_write_page_url" value="<?php echo $ldv_parent_write_page_url; ?>" size="60" readonly="readonly"/>                       
                    </td>
                </tr>
            </table>

    <?php else: ?>
        <table class="form-table" id="ldv-meta">
            <tr>
                <th scope="row">
                    <label for="ldv_enable_reviews">
                        <?php _e( 'Enable Reviews?', 'ldv-reviews'); ?>
                    </label>
                </th>
                <td>
                    <input type="checkbox" id="ldv_enable_reviews" name="ldv_enable_reviews" value="1" <?php checked( $ldv_enable_reviews, '1') ?> />                
                </td>
            </tr>

            <tr class="ldv-admin-row">
                <th scope="row">
                    <label for="ldv_item">
                        <?php _e( 'What\'s being reviewed?', 'ldv-reviews'); ?>
                    </label>
                </th>
                <td>
                    <input id="ldv_item" name="ldv_item" value="<?php echo $ldv_item; ?>" size="60" />
                    <br/>
                    <small>(<?php _e( 'This could be the title of an item, business or service', 'ldv-reviews'); ?></small>
                </td>
            </tr>

            <tr class="ldv-admin-row">
                <th scope="row">
                    <label for="ldv_limit">
                        <?php _e( 'Number of reviews per page?', 'ldv-reviews'); ?>
                    </label>
                </th>
                <td>                   
                    <select name="ldv_limit" style="width: 60px">
                        <?php
                            foreach($limits as $limit){                                 
                                $selected = '';
                                if($ldv_limit == $limit){
                                    $selected = ' selected="selected" ';
                                }
                            ?>
                                <option value="<?php echo $limit; ?>" <?php echo $selected; ?>>
                                    <?php echo $limit; ?>
                                </option>
                        <?php
                            }
                        ?>
                    </select>
                </td>
            </tr>

            <tr class="ldv-admin-row">
                <th scope="row">
                    <label for="ldv_tags">
                        <?php _e( 'Tags to apply to reviews', 'ldv-reviews'); ?>
                    </label>
                </th>
                <td>
                    <input name="ldv_tags" value="<?php echo $ldv_tags; ?>" size="60" />      
                    <br/>
                    <small>
                        <?php _e('Separate tags with a comma', 'ldv-reviews'); ?>
                    </small>
                </td>
            </tr>

            <tr class="ldv-admin-row">
                <th scope="row">
                    <label for="ldv_group">
                       <?php _e( 'Review Category', 'ldv-reviews'); ?>
                    </label>
                </th>
                <td>
                    <select name="ldv_group">
                        <option value=''><?php _e( 'Choose a Category', 'ldv-reviews') ; ?></option>
                        <?php foreach($availableLdCategories as $cat => $val ): ?>
                            <?php
                                $selected = '';
                                if($val == $ldv_group){
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
            
            <tr class="ldv-admin-row" valign="top">
                <th scope="row">
                    <?php _e('Choose display language', 'ldv-reviews'); ?>
                    <br/>
                    <small>
                        (<?php _e('This will override any language set
                        on the LouderVoice settings page'); ?>)
                    </small>
                </th>          
                <td>
                    <select name="ldv_single_language" id="ldv_single_language">                                            
                        <?php 
                            // Get available LV Categories                                                         
                            $availableLvLanguages = $ldvSettings->ldv_get_available_languages();                            
                            foreach($availableLvLanguages as $lang => $val ): ?>
                            <?php
                                $selected = '';
                                if($val == $ldv_single_language){
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

            <tr class="ldv-admin-row">
                <th scope="row" colspan="2">
                    <h4>
                       <?php
                           _e('Where to allow review writing?', 'ldv-reviews');
                        ?>
                    </h4>
                    <p>
                       <?php 
                        _e( ' Do you want to allow anyone to write reviews directly on this page or do you want a separate private page for writing reviews that you invite people to?', 'ldv-reviews'); 
                        ?>
                    </p>
                </th>
            </tr>
            <tr class="ldv-admin-row">
                <th scope="row">
                    <label for="ldv_write_on">
                        <?php _e( 'Write on this page', 'ldv-reviews'); ?>
                    </label>
                </th>
                <td>
                    <input type="radio" name="ldv_write" value="1" id="ldv_write_on" <?php checked( $ldv_write , '1'); ?> />
                </td>
            </tr>
            <tr class="ldv-admin-row">
                <th scope="row">
                    <label for="ldv_write_off">
                       <?php _e( 'Create a separate page', 'ldv-reviews'); ?>
                    </label>
                </th>
                <td>
                    <input type="radio" name="ldv_write" value="0" id="ldv_write_off" <?php checked( $ldv_write, '0' ); ?> />

                    <table id="ldv-create-page" style="">
                        <?php if( empty( $ldv_child_write_page_url )): ?>
                            <tr>
                                <td>
                                    <input id="lv-post-<?php echo $post->ID; ?>" class="lv_create_child_page_btn" type="button" value="<?php _e( 'Create \'Write Reviews\' Page Now', 'ldv-reviews');?>" />
                                    <img src="<?php echo LDV_FRONT_URL; ?>admin/images/ajax-loader.gif" alt="<?php _e('Loading', 'ldv-reviews'); ?>..." id="ldv-loading-img" class="lv-hide" />
                                    <input type="hidden" name="lv_create_page_nonce" id="lv_create_page_nonce" value="<?php echo wp_create_nonce("lv-create-new-page-nonce"); ?>" />
                                    <input type="hidden" name="ldv_child_write_page_url" id="ldv_child_write_page_url" value="<?php echo $ldv_child_write_page_url; ?>" />
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <p class="lv-hide lv-message lv-success-message">
                                        <strong><?php _e('Review Page Created', 'ldv-reviews'); ?></strong><br/>
                                        <?php 
                                           _e( 'This is the URL you should send to your customers when you want them to write a review:', 'ldv-reviews');
                                        ?>
                                        <br/>
                                        <span id="ldv-create-page-child-url"></span>
                                    </p>
                                    <p class="lv-hide lv-message lv-error-message">
                                        <?php
                                            _e('There was an error while creating the page.', 'ldv-reviews'); 
                                        ?>
                                    </p>
                                </td> 
                            </tr>
                        <?php else: // Already have a child page for reviews set up => display the URL ?>
                            <tr>
                                <td>
                                    <?php 
                                           _e( 'This is the URL you should send to your customers when you want them to write a review:', 'ldv-reviews');
                                    ?>
                                    <br/>
                                    <span id="ldv-create-page-child-url">
                                        <?php echo urldecode( $ldv_child_write_page_url ); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </table>
                </td>
            </tr>


        </table>
<?php    
    endif; // test for ldv_parent_write_page_url

}




/**
 * Save all LV custom fields
 *
 * @param int $post_id
 */
function ldv_save_details( $post_id ){   
    
    // verify nonce
    if (!isset($_POST['ldv_meta_box_nonce']) || !wp_verify_nonce($_POST['ldv_meta_box_nonce'], basename(__FILE__)) ) {
        if(isset($post_id)){
            return $post_id;            
        }
        return;
    }

    if( !empty($_POST["ldv_parent_write_page_url"])){
        update_post_meta($post_id, "ldv_parent_write_page_url", $_POST["ldv_parent_write_page_url"]);
    }else{            
        update_post_meta($post_id, "ldv_enable_reviews", $_POST["ldv_enable_reviews"]);
        update_post_meta($post_id, "ldv_item",  $_POST["ldv_item"]);
        update_post_meta($post_id, "ldv_limit", $_POST["ldv_limit"]);
        update_post_meta($post_id, "ldv_tags",  $_POST["ldv_tags"]);
        update_post_meta($post_id, "ldv_group", $_POST["ldv_group"]);
        update_post_meta($post_id, "ldv_write", $_POST["ldv_write"]);
        
        update_post_meta($post_id, "ldv_single_language", $_POST["ldv_single_language"]);

        if( !empty($_POST["ldv_child_write_page_url"])){
            update_post_meta($post_id, "ldv_child_write_page_url", $_POST["ldv_child_write_page_url"]);
        }
        
        // Ensure child page url setting is reset if write
        // option is chosen
        if( $_POST['ldv_write'] == '1' ){
            update_post_meta($post_id, "ldv_child_write_page_url", '');
        }
        
        
        $this_post = array(
             'ID'               => $post_id,
             'comment_status'   => 'closed',
             'ping_status'      => 'closed' ,
        );
        wp_insert_post($my_post);
    }
            
    return $post_id;

}
add_action('save_post', 'ldv_save_details');