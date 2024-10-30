<?php
/**
 * Ajax function to handle creation of new
 * child post/page for write only review form
 * 
 */
function ldv_create_write_page_callback(){
    
    // do security check
    check_ajax_referer( 'lv-create-new-page-nonce', 'nonce');    
    
    $parentPostId   = (int) $_POST['parent'];
    $postLang       = $_POST['language'];
    $parentPostType = get_post_type( $parentPostId );
    $parentTitle    = get_the_title( $parentPostId );
    
    $ldvSettings = new LDV_Settings();
    $languageBoilerplate = $ldvSettings->ldv_get_boilerplate_translations($parentPostId, $postLang);
    
    
    // Setup new post
    $writeReviewPost = array(   
        'comment_status'    => 'closed',
        'ping_status'       => 'closed' ,
        'post_parent'       => $parentPostId,
        'post_status'       =>  'publish',
        // 'post_title'        => __('Write your review of ') . $parentTitle ,
        // 'post_title'        => __('Write your review'),
        'post_title'        => $languageBoilerplate['title'],
        // 'post_content'      => __('We love hearing from our customers and we’d love to hear from you too. Please enter your review below'),
        'post_content'      => $languageBoilerplate['post_content'],
        'post_type'         =>  $parentPostType   
    );  
    
    $newPostId = wp_insert_post($writeReviewPost, true);    
    
    if( is_wp_error( $newPostId )){
        echo json_encode( $newPostId );        
    }else{
        
        $parentPostUrl = get_permalink( $parentPostId );        
        
        // item_url & item should point to Parent post's details
        update_post_meta($newPostId, "ldv_parent_write_page_url", $parentPostUrl);      
        update_post_meta($newPostId, "ldv_item", $parentTitle );
        
        $reviewPostUrl = get_permalink( $newPostId );
        
        echo json_encode( array(
           'success'    => true,
           'childUrl'   => $reviewPostUrl,
           'id'         => $newPostId            
        ));
        
        // Add new page to exclusion list
        if( function_exists( 'ldv_update_exclusions')){
            ldv_update_exclusions($newPostId);
        }
    }
    
    die();
}

add_action('wp_ajax_ldv_create_write_page', 'ldv_create_write_page_callback');