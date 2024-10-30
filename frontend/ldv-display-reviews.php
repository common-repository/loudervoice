<?php
/*
 *
 */

/**
 * Used for auto-display of reviews after the_content
 *
 * @param String $content
 * @return String
 */
function ldv_display_reviews_action( $content ){
    // check if it's set to auto-display,
    $position = get_option('ldv_position');
    $position = (empty($position) || $position === false) ? 'auto' : $position;
    if( $position === 'auto'){
        return ldv_display_reviews($content);
    }
    return $content;
}
add_action('the_content', 'ldv_display_reviews_action');


function ldv_display_reviews( $content = ''){
    global $post, $ldvApi;
    $custom = get_post_custom($post->ID);

    $ldvApi = new LDV_Api();

    // check we have an api key
    $apiKey = $ldvApi->getApiKey();
    if(empty( $apiKey) || $apiKey === false){
        return $content;
    }

    $ldv_enable_reviews         = ( isset($custom["ldv_enable_reviews"][0] ) ) ? $custom["ldv_enable_reviews"][0] : false; // default off
    $ldv_parent_write_page_url  = ( isset($custom["ldv_parent_write_page_url"][0] ) ) ? $custom["ldv_parent_write_page_url"][0] : '';
    $pageTemplates              = get_option('ldv_page_template');
    $postTypes                  = get_option('ldv_post_type');

    // is it a mass-enabled template?
    if(is_array( $pageTemplates )){
        $displayOnTemplate = ldv_display_on_template( $pageTemplates );
    }else{
        $displayOnTemplate = false;
    }

    // Show on this post type?
    $displayOnPostType = ldv_display_on_post_type( $postTypes );

    // Reviews not enabled
    if( ( ! $ldv_enable_reviews && empty( $ldv_parent_write_page_url ) && ( (!$displayOnTemplate ) && (!$displayOnPostType )) ) ){
        return $content;
    }

    // Grab Tags & Group (use default if needed).
    $defaultTags    = get_option('ldv_default_tags');
    $defaultGroup   = get_option('ldv_default_group');
    $ldv_tags       = ( !empty($custom["ldv_tags"][0] ) ) ? $custom["ldv_tags"][0]   : $defaultTags;
    $ldv_group      = ( !empty($custom["ldv_group"][0] ) ) ? $custom["ldv_group"][0] : $defaultGroup;

    // Item & read/write settings
    $ldv_item_url               = get_permalink();
    $ldv_item                   = ( !empty($custom["ldv_item"][0] ) ) ? $custom["ldv_item"][0]   : get_the_title( $post->ID );
    $ldv_limit                  = ( !empty($custom["ldv_limit"][0] ) ) ? $custom["ldv_limit"][0] : '5';              // default 5
    $ldv_write                  = ( !empty($custom["ldv_write"][0] ) ) ? $custom["ldv_write"][0] : '1';
    $ldv_child_write_page_url   = ( !empty($custom["ldv_child_write_page_url"][0] ) ) ? $custom["ldv_child_write_page_url"][0] : '';

    if( !empty( $ldv_parent_write_page_url )){
        $parentCustom   = get_post_custom($post->post_parent );
        // Set the item_url to the parent, not the child for write only display reviews
        // Updated to dynamically generate URL. Avoids problems if permalinks are updated
        $ldv_item_url = get_permalink( $post->post_parent );

        $ldv_item     = ( !empty($parentCustom["ldv_item"][0] ) ) ? $parentCustom["ldv_item"][0]   : get_the_title( $post->post_parent );

         // Update to match child page tags with Parent tags
        $ldv_tags       = ( !empty($parentCustom["ldv_tags"][0] ) ) ? $parentCustom["ldv_tags"][0]   : $defaultTags;
        $ldv_group      = ( !empty($parentCustom["ldv_group"][0] ) ) ? $parentCustom["ldv_group"][0] : $defaultGroup;
    }


    // separate tags from group with comma only if tags !empty
    $ldv_tags = ( !empty( $ldv_tags ) ) ? $ldv_tags . ', ' : $ldv_tags;

    // Get Reader/writer postitioning
    $writerOnly = 'false';
    $readerOnly = 'false';
    if( !empty( $ldv_child_write_page_url ) ){
        $writerOnly = 'false';
        $readerOnly = 'true';
    }elseif( !empty( $ldv_parent_write_page_url )){
        $writerOnly = 'true';
        $readerOnly = 'false';
    }

    // Get HTML reviews from LV API
    // don't need them for write-only pages
    $apiHtmlReviews = false;
    if( empty( $ldv_parent_write_page_url ) ){
        $apiHtmlReviews = $ldvApi->getReviews($ldv_item_url, $ldv_limit );
    }

    $ldv_tags = prepareTags( $ldv_tags );

    $args = array(
        'apiKey'    => $apiKey,
        'itemName'  => $ldv_item,
        'itemUrl'   => $ldv_item_url,
        'tags'      => $ldv_tags,
        'group'     => $ldv_group,
        'write'     => $ldv_write,
        'fbAppId'   => get_option('ldv_facebook_app_id'),
        'css'       => get_option('ldv_css_dir'),
        'limit'     => $ldv_limit,
        'writerOnly'=> $writerOnly,
        'readerOnly'=> $readerOnly,
    );

    // Get JS/CSS/HTML to append to $content
    $ldvReviews = ldv_get_display_script($apiHtmlReviews, $args);

    return $content . $ldvReviews;
}


/**
 * Trim spaces from tags and replace white-space
 * with a "+"
 *
 * @param String $tags
 * @return String $preparedTags
 */
function prepareTags( $tags ){

    $tagArray = explode( ',' , $tags );

    if( is_array( $tagArray )){
        $trimmed = array();
        foreach( $tagArray as $tag ){
            $trimmed[] = str_replace(' ', '+', trim( $tag ) );
        }
        return implode(',', $trimmed);
    }
    return $tags;
}


/**
 * Test if reviews are globally enabled on this
 * page template
 *
 * @global Object $post
 * @param Array $pageTemplates
 * @return boolean $displayOnTemplate
 */
function ldv_display_on_template( $pageTemplates ){
    global $post;

    $custom_fields      = get_post_custom_values('_wp_page_template', $post->ID);
    $current_template   = $custom_fields[0];
    $displayOnTemplate  = false;
    if( in_array($current_template, $pageTemplates) ){
        $displayOnTemplate = true;
    }

    return $displayOnTemplate;
}

/**
 * Test if reviews are globally enabled on this post type
 *
 * @global Object $post
 * @param Array $displayOnPostTypes - array of enabled custom post types
 * @return boolean $displayOnTemplate
 */
function ldv_display_on_post_type( $displayOnPostTypes ){
    global $post;

    if(!is_array( $displayOnPostTypes )){
        return false;
    }

    $postType = get_post_type( $post );

    if( in_array($postType, $displayOnPostTypes) ){
        return true;
    }
    return false;
}

/**
 * Set up LV Embed script for the post
 *
 * @param String $apiHtmlReviews
 * @param Mixed $args
 * @return String $html
 */
function ldv_get_display_script( $apiHtmlReviews, $args ){

    $language = ldv_get_language();
    $languageScript = (!empty( $language['lv_language_js']) ) ? '<script type="text/javascript" src="'. $language['lv_language_js'] .'"></script>' : '';

    $html = '
      <!--start_raw-->
        <div id="fb-root"></div>
        <div id="lv_reviews">' . $apiHtmlReviews . '</div>
        <div><script src="https://connect.facebook.net/'. $language['locale'] .'/all.js"></script>
        '. $languageScript . '
        <script type="text/javascript" src="https://cdn.loudervoice.com/static/js/apiv13-min.js"></script>
        <script type="text/javascript" charset="utf-8" defer="defer">
            var loudervoice = new LouderVoice();
            loudervoice.reviews("#lv_reviews", {language: "'. $language['short'] . '",
                                                key: "'. $args['apiKey'] . '",
                                                SERVER: "https://api.loudervoice.com/",
                                                itemurl: "'. $args['itemUrl'] . '",
                                                limit: '. $args['limit'] . ',
                                                item: "'. $args['itemName'] . '",
                                                tags: "'. $args['tags'] . $args['group']. '",
                                                authMethods: {facebook: false, openid: false, gauth: false, twitter: false},
                                                writerOnly: '. $args['writerOnly']. ',
                                                readerOnly: '. $args['readerOnly']. ',
                                                allowAnonymous: true,
                                                defaultAnonymousName: "LouderVoice Reviewer",
                                                lv_receiver: "'. LDV_PLUGIN_LOCATION . 'frontend/lv_receiver_apiupg.htm",
                                                tweetThis: true,
                                                facebookLike: true,
                                                facebook: {
                                                    appid: "'. $args['fbAppId'] . '",
                                                    key: "'. $args['fbAppId'] . '"
                                                },
                                                css: ["'. $args['css'] . 'jquery-ui-1.7.1.custom.css",
                                                      "'. $args['css'] . 'widget.css" ],
                                                activeTab:1
                                                }
                                            );
                                   </script>
                               </div>
                               <small>'. $language['display_credit'] .'</small>
     <!--end_raw-->';

    return $html;
}

/**
 * Check if language used is one
 * of those supported by LV
 *
 * @return Array [short, locale, lv_language_js]
 */
function ldv_get_language(){

    global $post;
    $custom = get_post_custom( $post->ID );

    // if child page, we need the parent page's language
    $ldv_parent_write_page_url  = ( isset($custom["ldv_parent_write_page_url"][0] ) ) ? $custom["ldv_parent_write_page_url"][0] : '';
    if( $post->post_parent && !empty( $ldv_parent_write_page_url )){
        $custom = get_post_custom( $post->post_parent );
    }

    // user selected display language?
    $lang   = ( isset($custom["ldv_single_language"][0] ) ) ? $custom["ldv_single_language"][0] : get_option('ldv_language');

    if( empty( $lang ) || $lang === false){
        $lang = get_locale();   // use WP locale if none set
    }


    $jsi18n = 'https://api.loudervoice.com/jsi18n/conf/?language=';
    $ldvSettings = new LDV_Settings();
    $ldvAvailableLanguages = $ldvSettings->ldv_get_available_languages();

    $ldvBoilerplate = $ldvSettings->ldv_boilerplate_translations( $lang );


    // see if the language set is one of the LV supported languages
    foreach( $ldvAvailableLanguages as $ldvLangText => $ldvLocale ){
        if( $ldvLocale === $lang ){
            // extract language short code
            $shortLang = substr( $lang, 0, 2 );
            return array(
                'short'             => $shortLang,
                'lv_language_js'    => $jsi18n . $shortLang,
                'locale'            => $lang,
                'display_credit'       => $ldvBoilerplate['display_credit']
            );
        }
    }

    // Default
    return array(
                'short'             => '',
                'locale'            => $lang,       // defaults to en_US
                'lv_language_js'    => '',
                'display_credit'       => $ldvBoilerplate['display_credit']
            );
}
