[![Latest Stable Version](https://poser.pugx.org/ankitpokhrel/Dynamic-Featured-Image/v/stable.png)](https://packagist.org/packages/ankitpokhrel/Dynamic-Featured-Image)
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

   ![Snapshot 1](http://ankitpokhrel.com.np/dfi/screenshot-1.jpg)

2. Click `Set featured image`, select required image from media popup and click `Insert into Post`. Make sure `File URL` is selected in the `Link URL` section of media popup.

   ![Snapshot 2](http://ankitpokhrel.com.np/dfi/snapshot_2.jpg)

3. Click on `Add New` to add new featured image or use `Remove` link to remove the featured image box.
 
   ![Snapshot 3](http://ankitpokhrel.com.np/dfi/screenshot-3.jpg)

4. After adding featured images click `publish` or `update` to save featured images.

###### _Note: The featured images are only saved when you publish or update the post._

### Documentation
* [Retrieving images in a theme](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/Retrieving-images-in-a-theme)
* [Getting image title, alt and caption attributes](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/Available-Functions#getting-image-title-alt-and-caption-attributes)
* [Helpers/Utility functions](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/Available-Functions)

### Other Resources
* [Blog](http://ankitpokhrel.com.np/blog/category/dynamic-featured-image/)
* [FAQ](http://wordpress.org/plugins/dynamic-featured-image/faq/)

#### The List of Available Functions are:
1. [dfi_get_image_id( $image_url )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/Available-Functions#1-dfi_get_image_id-image_url-)
2. [dfi_get_image_thumb( $image_url, $size = "thumbnail" )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/Available-Functions#2-dfi_get_image_thumb-image_url-size--thumbnail-)
3. [dfi_get_image_url( $attachment_id, $size = "full" )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/Available-Functions#3-dfi_get_image_url-attachment_id-size--full-)
4. [dfi_get_post_attachment_ids( $post_id )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/Available-Functions#4-dfi_get_post_attachment_ids-post_id-)
5. [dfi_is_attached( $attachment_id, $post_id )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/Available-Functions#5-dfi_is_attached-attachment_id-post_id-)
6. [dfi_get_image_title( $image_url )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/Available-Functions#6-dfi_get_image_title-image_url-)
7. [dfi_get_image_title_by_id( $attachment_id )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/Available-Functions#7-dfi_get_image_title_by_id-attachment_id-)
8. [dfi_get_image_alt( $image_url )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/Available-Functions#8-dfi_get_image_alt-image_url-)
9. [dfi_get_image_alt_by_id( $attachment_id )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/Available-Functions#9-dfi_get_image_alt_by_id-attachment_id-)
10. [dfi_get_image_caption( $image_url )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/Available-Functions#10-dfi_get_image_caption-image_url-)
11. [dfi_get_image_caption_by_id( $attachment_id )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/Available-Functions#11-dfi_get_image_caption_by_id-attachment_id-)

===================================================================================================

### Author available for hire

I'm available for freelance work. Remote worldwide or locally around Nepal. Drop me a line if you like.
 
### Donate with [PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=J9FVY3ESPPD58)

If you think this script is useful and saves you a lot of work, a lot of costs (PHP developers are expensive) and let you sleep much better, then donating a small amount would be very cool.

[![Paypal](http://ankitpokhrel.com.np/img/paypal.png)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=J9FVY3ESPPD58)

### Questions about this project?

You can always contact me at `ankitpokhrel@gmail.com`, if you have any question or queries about the project. 

Please feel free to report any bug found. Pull requests, issues, and plugin recommendations are more than welcome!
