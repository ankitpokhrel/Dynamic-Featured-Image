## Dynamic Featured Image (A WordPress Plugin)

_Dynamically adds multiple featured image (post thumbnail) functionality to posts, pages and custom post types._

### Overview
Dynamic Featured Image enables the option to have MULTIPLE featured images within a post or page. 
This is especially helpful when you use other plugins, post thumbnails or sliders that use featured images.
Why limit yourself to only one featured image if you can do some awesome stuffs with multiple featured image? 
DFI allows you to add different number of featured images to each post and page that can be collected by the various theme functions.

### Installation

  1. Unzip and upload the `dynamic-featured-images` directory to the plugin directory (`/wp-content/plugins/`) or install it from `Plugins->Add New->Upload`
  2. Activate the plugin through the `Plugins` menu in WordPress.
  3. If you don't see new featured image box, click `Screen Options` in the upper right corner of your wordpress admin and make sure that the `Featured Image 2` box is slected.

### How it works?

1. After successfull plugin activation go to `add` or `edit` page of posts or pages and you will notice a box for second featured image.

   ![Snapshot 1](http://ankitpokhrel.com.np/dfi/snapshot_1.jpg)

2. Click `Set featured image`, select required image from media popup and click `Insert into Post`.

   ![Snapshot 2](http://ankitpokhrel.com.np/dfi/snapshot_2.jpg)

3. Click on `Add New` to add new featured image or use `Remove` link to remove the featured image box.
 
   ![Snapshot 3](http://ankitpokhrel.com.np/dfi/snapshot_3.jpg)

4. After adding featured images click `publish` or `update` to save featured images.

###### _Note: The featured images are only saved when you publish or update the post._


### Retrieving Images in a Theme

* To get featured images of specific post

```
if( function_exists('dfiGetFeaturedImages') )
   $featuredImages = dfiGetFeaturedImages($postId);
```

* To get featured images in a post loop.

```
<?php 
   while ( have_posts() ) : the_post();

   if( function_exists('dfiGetFeaturedImages') ) {
       $featuredImages = dfiGetFeaturedImages();
   }
   
   endwhile;
?>
```

* The data is returned as an array that contain selected image and full size path to the selected image.

```
array
  0 => 
    array
      'thumb' => string 'http://your_site/upload_path/yourSelectedImage.jpg' (length=50)
      'full' => string 'http://your_site/upload_path/yourSelectedImage_fullSize.jpg' (length=69)
  1 => 
    array
      'thumb' => string 'http://your_site/upload_path/yourSelectedImage.jpg' (length=50)
      'full' => string 'http://your_site/upload_path/yourSelectedImage_fullSize.jpg' (length=69)
  2 => ...
```

## Helper/Utility Functions ( ver. 2+ )
1. dfi_get_image_id( $image_url )
2. dfi_get_image_thumb( $image_url, $size )
3. dfi_get_image_url( $attachmentId, $size = "full" )
4. dfi_get_image_title( $image_url )
5. dfi_get_image_title_by_id( $attachmentId )
6. dfi_get_image_caption( $image_url )
7. dfi_get_image_caption_by_id( $attachmentId )
8. dfi_get_image_alt( $image_url )
9. dfi_get_image_alt_by_id( $attachmentId )
10. dfi_get_post_attachment_ids( $postId )
11. dfi_is_attached( $attachmentId, $postId )

===================================================================================================

### Author available for hire

I'm available for freelance work. Remote worldwide or locally around Nepal. Drop me a line if you like.
 
### Donate with [PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=J9FVY3ESPPD58)

If you think this script is useful and saves you a lot of work, a lot of costs (PHP developers are expensive) and let you sleep much better, then donating a small amount would be very cool.

[![Paypal](http://ankitpokhrel.com.np/img/paypal.png)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=J9FVY3ESPPD58)

### Questions about this project?

You can always contact me at `ankitpokhrel@gmail.com`, if you have any question or queries about the project. 

Please feel free to report any bug found. Pull requests, issues, and plugin recommendations are more than welcome!
