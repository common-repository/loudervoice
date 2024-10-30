<?php
/**
 * @package LDV
 * 
 * Structure based on Yoast's Wordpress SEO Plugin Admin Class
 */


if ( ! class_exists( 'LDV_Settings' ) ) {
	
    class LDV_Settings{
		
        public function __construct() {
            
        }
        
        
       /**
        * Return array of available lv:group categories
        * @return Array
        */
        public function ldv_get_available_categories(){
            return array(
                __('Food and Drink', 'ldv-reviews')             => 'lv:group=fooddrink',
                __('Entertainment', 'ldv-reviews')              => 'lv:group=entertainment',
                __('Travel and Accommodation', 'ldv-reviews')   => 'lv:group=travelaccommodation', 
                __('Technology', 'ldv-reviews')                 => 'lv:group=technology',
                __('Sports and Recreation', 'ldv-reviews')      => 'lv:group=sportsrecreation',
                __('Home and Garden', 'ldv-reviews')            => 'lv:group=homegarden',
                __('Kids and Family', 'ldv-reviews')            => 'lv:group=kidsfamily',
                __('Business', 'ldv-reviews')                   => 'lv:group=business'   
            );
        }
        
        
        /**
         * Return a list of supported languages. 
         * @return Array ['Language Name', 'locale'] 
         */
        public function ldv_get_available_languages(){            
            
            return array(
                __('Use WordPress default', 'ldv-reviews')  => '',
                __('English (US)', 'ldv-reviews')           => 'en_US',                
                __('English (GB)', 'ldv-reviews')           => 'en_GB',
                __('French (FR)', 'ldv-reviews')            => 'fr_FR',
                __('French (CA)', 'ldv-reviews')            => 'fr_CA',
                __('Spanish (ES)', 'ldv-reviews')           => 'es_ES', 
                __('Spanish (MX)', 'ldv-reviews')           => 'es_MX', 
                __('Italian', 'ldv-reviews')                => 'it_IT',
                __('German (DE)', 'ldv-reviews')            => 'de_DE',
                __('Russian', 'ldv-reviews')                => 'ru_RU',
                __('Portuguese', 'ldv-reviews')             => 'pt_PT',                
            );
        }
        
        
        /**
         * Bypass WP Localisation on creation of Child Write Review pages. Need to
         * set title & post content in the lanugage selected by the user.
         * 
         * @param Int $postId
         * @param $postLang [default = ''] - locale string (eg. en_US)
         * @return Array ['title' => '', 'post_content' => '']
         */
        public function ldv_get_boilerplate_translations( $postId, $postLang = ''){
                        
            $post       = get_post( $postId );
            $custom     = get_post_custom( $postId );          
            $postLang   = (string) $postLang;   // ajax

            // if child page, we need the parent page's language
            $ldv_parent_write_page_url  = ( isset($custom["ldv_parent_write_page_url"][0] ) ) ? $custom["ldv_parent_write_page_url"][0] : '';        
            if( $post->post_parent && !empty( $ldv_parent_write_page_url )){    
                $custom = get_post_custom( $post->post_parent );
            }

            // user selected display language?
            $lang   = ( isset($custom["ldv_single_language"][0] ) ) ? $custom["ldv_single_language"][0] : get_option('ldv_language');
            
            // User sent $postLang => ajax function call where the parent post
            // language option may have been changed, but post not saved. 
            // $postLang grabbed from (possibly) unsaved dropdown 
            if( !empty( $postLang )){
                $lang = $postLang;
            }
            
            // use WP locale if none set
            if( empty( $lang ) || $lang === false){
                $lang = get_locale();   
            }                       

            return $this->ldv_boilerplate_translations( $lang );            
            
        }
        
        
        /**
         * Translations used when creating a child write reviews page in a 
         * given language 
         * @param type $lang
         * @return type 
         */
        public function ldv_boilerplate_translations( $lang = 'en_US'){
            
            $availableLanguages = array(
                                        'en_US'   => array(
                                            'title'           => 'Write your review',
                                            'post_content'    => 'We love hearing from our customers and we\'d love to hear from you too. Please enter your review below',
                                            'display_credit'  => 'Powered by <a href="http://www.loudervoice.com/" target="_blank">LouderVoice Reviews</a> and <a href="http://www.louderyou.com/" target="_blank">LouderYou</a>'
                                        ),
                                        'en_GB'   => array(
                                            'title'           => 'Write your review',
                                            'post_content'    => 'We love hearing from our customers and we\'d love to hear from you too. Please enter your review below',
                                            'display_credit'  => 'Powered by <a href="http://loudervoice.co.uk/" target="_blank">LouderVoice UK Reviews</a>'
                                        ),
                                        'en_IE'   => array(
                                            'title'           => 'Write your review',
                                            'post_content'    => 'We love hearing from our customers and we\'d love to hear from you too. Please enter your review below',
                                            'display_credit'  => 'Powered by <a href="http://www.loudervoice.com/" target="_blank">LouderVoice Reviews</a> and <a href="http://www.louderyou.ie/" target="_blank">LouderYou Ireland</a>'
                                        ),
                                        'fr_FR'   => array(
                                            'title'           => 'Écrire votre avis',
                                            'post_content'    => 'Votre opinion en tant que client nous intéresse énormément. S\'il vous plait, écrivez votre avis ci-dessous',
                                            'display_credit'  => '<a href="http://fr.loudervoice.com/" target="_blank">LouderVoice France</a>'
                                        ),
                                        'fr_CH'   => array(
                                            'title'           => 'Écrire votre avis',
                                            'post_content'    => 'Votre opinion en tant que client nous intéresse énormément. S\'il vous plait, écrivez votre avis ci-dessous',
                                            'display_credit'  => '<a href="http://www.loudervoice.com/" target="_blank">LouderVoice</a> et <a href="http://www.louderyou.ch/" target="_blank">LouderYou</a>'
                                        ),
                                        'fr_CA'   => array(
                                            'title'           => 'Écrire votre avis',
                                            'post_content'    => 'Votre opinion en tant que client nous intéresse énormément. S\'il vous plait, écrivez votre avis ci-dessous',
                                            'display_credit'  => '<a href="http://www.loudervoice.com/" target="_blank">LouderVoice</a> et <a href="http://www.louderyou.com/" target="_blank">LouderYou</a>'
                                        ),
                                        'es_ES'   => array(
                                            'title'           => 'Escribe tu opinión',
                                            'post_content'    => 'Nos encanta escuchar a nuestros clientes y nos encantaría saber de usted también. Por favor, introduzca sus comentarios a continuación',
                                            'display_credit'  => '<a href="http://www.loudervoice.com/" target="_blank">LouderVoice</a> y <a href="http://www.louderyou.es/" target="_blank">LouderYou</a>'
                                        ),
                                        'es_MX'   => array(
                                            'title'           => 'Escribe tu opinión',
                                            'post_content'    => 'Nos encanta escuchar a nuestros clientes y nos encantaría saber de usted también. Por favor, introduzca sus comentarios a continuación',
                                            'display_credit'  => '<a href="http://www.loudervoice.com/" target="_blank">LouderVoice</a> y <a href="http://www.louderyou.com/" target="_blank">LouderYou</a>'
                                        ),
                                        'it_IT'   => array(
                                            'title'           => 'Scrivi tua recensione',
                                            'post_content'    => 'Ci piace sentire dai nostri clienti e ci piacerebbe sentire anche da te. Inserisci il tuo recensioni qui sotto',
                                            'display_credit'  => '<a href="http://www.loudervoice.com/" target="_blank">LouderVoice</a> e <a href="http://www.louderyou.it/" target="_blank">LouderYou</a>'
                                        ),
                                        'de_DE'   => array(
                                            'title'           => 'Eine meinung schreiben',
                                            'post_content'    => 'Wir hören gerne von unseren Kunden und wir würden uns freuen von Ihnen zu hören auch. Bitte geben Sie Ihren Beitrag unten',
                                            'display_credit'  => '<a href="http://www.loudervoice.com/" target="_blank">LouderVoice</a> und <a href="http://www.louderyou.de/" target="_blank">LouderYou</a>'
                                        ),
                                        'de_CH'   => array(
                                            'title'           => 'Eine meinung schreiben',
                                            'post_content'    => 'Wir hören gerne von unseren Kunden und wir würden uns freuen von Ihnen zu hören auch. Bitte geben Sie Ihren Beitrag unten',
                                            'display_credit'  => '<a href="http://www.loudervoice.com/" target="_blank">LouderVoice</a> und <a href="http://www.louderyou.ch/" target="_blank">LouderYou</a>'
                                        ),
                                        'ru_RU'   => array(
                                            'title'           => 'Напишите ваш отзыв',
                                            'post_content'    => 'Мы любим слышать от наших клиентов, и мы хотели бы услышать от вас. Пожалуйста, введите свой ​​отзыв',
                                            'display_credit'  => '<a href="http://www.loudervoice.com/" target="_blank">LouderVoice</a> and <a href="http://www.louderyou.com/" target="_blank">LouderYou</a>'
                                        ),
                                        'pt_PT'    => array(
                                            'title'           => 'Escreva seu comentário',
                                            'post_content'    => 'Nós adoramos ouvir de nossos clientes e nós adoraríamos ouvir de você também. Digite seu comentário abaixo',
                                            'display_credit'  => '<a href="http://www.loudervoice.com/" target="_blank">LouderVoice</a> and <a href="http://www.louderyou.com/" target="_blank">LouderYou</a>'
                                        )                
                                    );
            
            if( !empty( $lang) && array_key_exists( $lang, $availableLanguages)){
                return $availableLanguages[ $lang ];
            }
            
            return $availableLanguages['en_US'];
        }
        
    }
    
}