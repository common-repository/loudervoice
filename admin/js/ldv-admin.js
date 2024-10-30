/*!
 * Used to handle the admin side setup of the
 * LouderVoice Wordpress plugin
 */

jQuery(document).ready(function($) {
      
   if( $('input[name="ldv_enable_reviews"]').is(':checked') ){        
       $('tr.ldv-admin-row').removeClass('lv-hide'); 
   }else{
        $('tr.ldv-admin-row').addClass('lv-hide');
   }
   // Show/hide rows if ldv enable checkbox clicked   
   $('input[name="ldv_enable_reviews"]').change(function(){
       $('tr.ldv-admin-row').slideToggle('slow');
       if($('#ldv_item').val() == ''){
           $('#ldv_item').val( $('#title').val() );
       }
   });
   
   // Create page radio buttons
   if( $('input#ldv_write_on').is(':checked')){
        $('#ldv-create-page').hide();
   }
   $('input[name="ldv_write"]').change(function(){
       $('#ldv-create-page').slideToggle('slow');
   });
   
   // Behaviour for Create Page Button
   $('input.lv_create_child_page_btn').click(function(){
       var $this = $(this);       
       $this.attr('diabled', 'disabled');
       $('#ldv-loading-img').removeClass('lv-hide');        // show ajax loading img       
       
       var postId = $this.attr('id').substr(8); // strip 'lv-post-'
       var security = $('#lv_create_page_nonce').val()  ;       
       var langSelected = $('#ldv_single_language option:selected').val();     // grab selected language  
       
       var data = {
		action: 'ldv_create_write_page',
                nonce: security,
		parent: postId,
                language: langSelected
	};
                
	
	$.post(ajaxurl, data, function(response) {
            
            response = JSON.parse( response );            
            if( response == '-1'){
                // nonce failure
                alert('There was problem completing the request.');
            }else if(response.success == true){
                $('#ldv-create-page-child-url').text( decodeURIComponent( response.childUrl ) );
                $('#ldv_child_write_page_url').val( response.childUrl );
                $('.lv-success-message').fadeIn('fast');
                $this.fadeOut('slow').remove();
            }else{
                // wp_error
                // console.log( response );
                $('.lv-error-message').fadeIn('fast');
            }
            		
	});
       
       $('#ldv-loading-img').addClass('lv-hide');
       $this.removeAttr('disabled');
   })
   
});