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
	$(document).on('click', '.dfiAddNew', function() {
		   		
       var obj = $(this);
       var id = parseInt( $('.featured-meta-box:last').find('.dfiAddNew').attr('data-id') );
       
       var newMetaBox = obj.closest('.featured-meta-box').clone();
       newMetaBox.find('.hndle span').html('Featured Image ' + ++id);
       newMetaBox.attr('id', 'dfiFeaturedMetaBox' + "-" + id);
       newMetaBox.find('.handlediv').addClass('dfiDynamicBox');
       
       var metaBoxContentObj = newMetaBox.find('.inside');
       metaBoxContentObj.html('');
       obj.hide();
       obj.parent().append('<img src="images/wpspin_light.gif" class="dfiLoading">').hide().fadeIn(200);       

       $.ajax({
          type: 'POST',  
          url: 'admin-ajax.php',  
          data: { action: 'dfiMetaBox_callback', id: id },  
          success: function(response){            
            metaBoxContentObj.append(response);
            newMetaBox.appendTo( obj.closest('.featured-meta-box').parent() );
            
            //Add post id
            newMetaBox.find('.dfiFeaturedImage').attr('data-post-id', obj.parent().parent().find('.dfiFeaturedImage').attr('data-post-id') );
           
            var alias = obj;
            obj.parent().find('.dfiLoading').fadeOut(300, function(){ $(this).remove(); alias.fadeIn(200); });
          }
       });
       
	});
	
	/*
	 * Remove featured image meta box
	 */
	$(document).on('click', '.dfiRemove', function() {

	   if( confirm('Are you sure?') ) {

	     var dfiMetaBox = $(this).closest('.featured-meta-box');	     
	     var totalMetaBox = $('.featured-meta-box').length;
	     
	     if( totalMetaBox == 1 ) {

	           dfiMetaBox.find('.dfiImg').attr('src', '');
	           dfiMetaBox.find('.dfiImageHolder').val('');
	           dfiMetaBox.find('.dfiFeaturedImage')
	                     .removeClass('hasFeaturedImage')
	                     .show()
	                     .animate({ opacity: 1, display: 'inline-block' }, 600);	

	     } else {

		      dfiMetaBox.fadeOut(500, function(){
		        $(this).remove();  
		      });

		 }

	   }

	});
	
	/*
	 * Display media editor and allow to select featured image from the media library
	 */	
	var restore_send_to_editor = "";
	$(document).on('click', '.dfiFeaturedImage', function() {

		current = $(this);
		
		var post_id = current.attr('data-post-id');
		
		restore_send_to_editor = wp.media.editor.send.attachment;
		if( null != current){		    

		    wp.media.editor.send.attachment = function(props, attachment) {

	    		var fullSize = imgUrl = attachment.url;
	    		var imgUrlTrimmed, fullUrlTrimmed;
	    	
	    		switch( props.size ) {
	    			case 'thumbnail':
	    					imgUrl = attachment.sizes.thumbnail.url;
	    					break;

	    			case 'medium':
	    					imgUrl = attachment.sizes.medium.url;
	    					break;

	    			case 'large':
	    					imgUrl = attachment.sizes.large.url;	    					
	    		}

	    		imgUrlTrimmed = imgUrl.split('wp-content');
	        	imgUrlTrimmed = '/wp-content' + imgUrlTrimmed[1];

	        	fullUrlTrimmed = fullSize.split('wp-content');
	        	fullUrlTrimmed = '/wp-content' + fullUrlTrimmed[1];

	    		var featuredBox = current.parent(); 
	    		
	    		featuredBox.find('.fImg').attr({
	    			'src': imgUrl,
	    			'data-src': fullSize
	    		});
	    		
	    		featuredBox.find('.dfiFeaturedImage').addClass('hasFeaturedImage');
	    			
	    		var dfiFeaturedImages = [imgUrlTrimmed, fullUrlTrimmed];
	    		
	    		featuredBox.find('img').attr('src', attachment.sizes.medium.url).fadeIn(200);
	    		featuredBox.find('input.dfiImageHolder').val(dfiFeaturedImages);

			}

			wp.media.editor.open(this);
		}
		
		return false;

	});
   
	/**
	 * Enable toggle of dynamically generated featured box
	 */
	$(document).on('click', '.dfiDynamicBox', function() {
	    $(this).parent().toggleClass('closed');
	});
	
	/**
	 * Add a hover animation in image
	 */
	$(document).on({
	    mouseenter: function(){	
	        var obj = $(this).closest('.featured-meta-box');       
	        obj.find('.dfiImg').stop(true, true).animate({ opacity: 0.3 }, 300 );
	        obj.find('.hasFeaturedImage').fadeIn(200);
	    },
	    mouseleave: function(){
	        var obj = $(this);
	        obj.find('.dfiImg').stop(true, true).animate({ opacity: 1 }, 300 );
	        obj.find('.hasFeaturedImage').fadeOut(100);        	        
	    }
	}, '.featured-meta-box .inside');

});

//END