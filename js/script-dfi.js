/**
 * @file script-dfi.js
 *
 * Script for dynamic featured image plugin.
 *
 * Copyright (c) 2013, Ankit Pokhrel <ankitpokhrel@gmail.com, http://ankitpokhrel.com.np>
 */

jQuery(document).ready(function($) {
    var current = null;

    /**
     * Add new meta box
     */
    $(document).on('click', '.dfiAddNew', function() {

        var obj = $(this),
            id = parseInt( $('.featured-meta-box:last').find('.dfiAddNew').data('id'), 10 ),
            idLocal = $('.featured-meta-box:last').find('.dfiAddNew').attr('data-id-local'),
            newMetaBox = obj.closest('.featured-meta-box').clone();

        newMetaBox.find('.hndle span').html( WP_SPECIFIC.metabox_title + " " + idLocal );
        newMetaBox.attr('id', 'dfiFeaturedMetaBox' + "-" + (++id) );
        newMetaBox.find('.handlediv').addClass('dfiDynamicBox');

        var metaBoxContentObj = newMetaBox.find('.inside');
        metaBoxContentObj.html('');
        obj.hide();
        obj.parent().append('<span class="dfiLoading"></span>').hide().fadeIn(200);

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

    /**
     * Remove featured image meta box
     */
    $(document).on('click', '.dfiRemove', function() {

        if( confirm('Are you sure?') ) {

            var dfiMetaBox = $(this).closest('.featured-meta-box'),
                totalMetaBox = $('.featured-meta-box').length;

            if( totalMetaBox === 1 ) {

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

    /**
     * Display custom media uploader and allow to
     * select featured image from the media library
     */
    $(document).on('click', '.dfiFeaturedImage', function() {

        current = $(this);

        if( null !== current) {

            var dfi_uploader = wp.media({

                title: WP_SPECIFIC.mediaSelector_title,
                button: {
                    text: WP_SPECIFIC.mediaSelector_buttonText
                },
                multiple: false,

            }).on('select', function() {

                var attachment = dfi_uploader.state().get('selection').first().toJSON(),
                    fullSize = attachment.url,
                    imgUrl = (typeof attachment.sizes.thumbnail === "undefined") ? fullSize : attachment.sizes.thumbnail.url,
                    imgUrlTrimmed, fullUrlTrimmed;

                imgUrlTrimmed = imgUrl.replace(WP_SPECIFIC.upload_url, "");
                fullUrlTrimmed = fullSize.replace(WP_SPECIFIC.upload_url, "");

                var featuredBox = current.parent();

                featuredBox.find('.fImg').attr({
                    'src': imgUrl,
                    'data-src': fullSize
                });

                featuredBox.find('.dfiFeaturedImage').addClass('hasFeaturedImage');

                var dfiFeaturedImages = [imgUrlTrimmed, fullUrlTrimmed];

                /**
                 * Check if medium sized image exists
                 * @type object
                 */
                var medium = attachment.url;
                if( typeof attachment.sizes.medium !== "undefined" ) {
                    medium = attachment.sizes.medium.url;
                }

                featuredBox.find('img').attr('src', medium).fadeIn(200);
                featuredBox.find('input.dfiImageHolder').val(dfiFeaturedImages);

            }).open();  
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
