/*
 * Script for dynamic featured image plugin
 * 
 * Copyright (c) 2013, Ankit Pokhrel <ankitpokhrel@gmail.com, http://ankitpokhrel.com.np>
 */
jQuery(document).ready(function($){
	var current = null;
	
	/*
	 * Add new meta box
	 */
	$(document).on('click', '.dfiAddNew', function(){	   		
       var obj = $(this);
       var id = parseInt( $('.featured-meta-box:last').find('.dfiAddNew').attr('data-id') );
       
       var newMetaBox = obj.closest('.featured-meta-box').clone();
       newMetaBox.find('.hndle span').html('Featured Image ' + ++id);
       newMetaBox.attr('id', 'dfiFeaturedMetaBox' + "-" + id);
       newMetaBox.find('.handlediv').addClass('dfiDynamicBox');
       
       var metaBoxContentObj = newMetaBox.find('.inside');
       metaBoxContentObj.html('');
       
       obj.append('<img src="images/wpspin_light.gif" class="dfiLoading">').hide().fadeIn(200);
       $.ajax({
          type: 'POST',  
          url: 'admin-ajax.php',  
          data: { action: 'dfiMetaBox_callback', id: id },  
          success: function(response){
            metaBoxContentObj.append(response);
            newMetaBox.appendTo( obj.closest('.featured-meta-box').parent() );
            
            obj.parent().find('.dfiLoading').fadeOut(300, function(){ $(this).remove(); });
          }
       });
       
	});
	
	/*
	 * Remove featured image meta box
	 */
	$(document).on('click', '.dfiRemove', function(){
	   if( confirm('Are you sure?') )
		  $(this).closest('.featured-meta-box').remove();
	});
	
	/*
	 * Select featured image from media library
	 */
	
	var restore_send_to_editor = "";
	$(document).on('click', '.dfiFeaturedImage', function() {		
		current = $(this);
		restore_send_to_editor = window.send_to_editor;
		if( null != current){
		    media_uploader();
		    tb_show('', 'media-upload.php?type=image&amp;TB_iframe=true');
		}
		return false;
	});
	 
	/*
	 * Allow access to media uploader
	 */
    function media_uploader(){
       	window.send_to_editor = function(html){	    
	   
    		var fullSize = $('img', html).parent().attr('href');		
    		var imgurl = $('img', html).attr('src');
    		
    		imgUrlTrimmed = imgurl.split('wp-content');
    		imgUrlTrimmed = '/wp-content' + imgUrlTrimmed[1];
    		
    		fullUrlTrimmed = fullSize.split('wp-content');
    		fullUrlTrimmed = '/wp-content' + fullUrlTrimmed[1];
    		
    		var featuredBox = current.parent();
    		
    		featuredBox.find('.fImg').attr({
    			'src': imgurl,
    			'data-src': fullSize
    		});
    			
    		var dfiFeaturedImages = [imgUrlTrimmed, fullUrlTrimmed];
    			
    		featuredBox.find('img').attr('src', imgurl).fadeIn(200);
    		featuredBox.find('input.dfiImageHolder').val(dfiFeaturedImages);
    		tb_remove();
    		window.send_to_editor = restore_send_to_editor;
	 } 
   }
   
	/*
	 * Enable toggle of dynamically generated featured box
	 */
	$(document).on('click', '.dfiDynamicBox', function(){
	    $(this).parent().toggleClass('closed');
	});
	
});