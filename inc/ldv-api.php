<?php
/**
 * Handle interaction with the LouderVoice API
 *  
 */
class LDV_Api{

    protected $apiUrl = 'https://api.loudervoice.com/js/reviews/stream/';
    protected $apiKey;
        
    public function __construct() {
        if(null === $this->apiKey ){
            $this->apiKey = get_option( 'ldv_api_key' );
        }        
    }
    
           
   /**
    * Simple PHP widget to get a list of reviews via the LouderVoice API.
    * @param $itemurl The review url, contact support if unsure
    * @param $limit How many reviews to return, default is 5
    * @param $offset The start position, default is first review
    * @return String as per API guidelines, contains HTML and HTML encoded elements
    *
    */
    function getReviews( $itemurl, $limit=5, $offset=0 ){
    
        $key = $this->getApiKey();    
        
        if ($key == '' || $itemurl == ''){
            return __('LouderVoice Widget Error: Invalid inputs supplied', 'ldv-reviews');
        }   

        $url = $this->apiUrl . $key .'/?limit='.$limit. '&offset='.$offset. '&itemurl='.$itemurl;
        $response = wp_remote_get($url, array('sslverify' => false)); 
        
        if(!is_wp_error( $response )){
            return $response['body'];
        }else{
            // Perhaps inputs were wrong, or Connection to API is down			
            return __("{$response->get_error_code()}: {$response->get_error_message()}");
        }
    }

    
    public function getApiKey(){
        return $this->apiKey;
    }
}