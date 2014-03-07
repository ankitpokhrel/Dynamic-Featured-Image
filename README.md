[![Latest Stable Version](https://poser.pugx.org/ankitpokhrel/Dynamic-Featured-Image/v/stable.png)](https://packagist.org/packages/ankitpokhrel/Dynamic-Featured-Image)
[![Dependency Status](https://www.versioneye.com/user/projects/52d53aaeec13754cdb0003ff/badge.png)](https://www.versioneye.com/user/projects/52d53aaeec13754cdb0003ff)
[![Code Climate](https://codeclimate.com/github/ankitpokhrel/Dynamic-Featured-Image.png)](https://codeclimate.com/github/ankitpokhrel/Dynamic-Featured-Image)
<script id='fblh4am'>(function(i){var f,s=document.getElementById(i);f=document.createElement('iframe');f.src='//api.flattr.com/button/view/?uid=ankitpokhrel&button=compact&url='+encodeURIComponent(document.URL);f.title='Flattr';f.height=20;f.width=110;f.style.borderWidth=0;s.parentNode.insertBefore(f,s);})('fblh4am');</script>
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

  ![New featured image box](http://ankitpokhrel.com.np/dfi/screenshot-1.png)

2. Click `Set featured image` icon, select required image from the "Dynamic Featured Image Media Selector" popup and click `Set Featured Image`.

  ![Dynamic Featured Image Media Selector](http://ankitpokhrel.com.np/dfi/screenshot-2.png)

3. Click on `Add New` to add new featured image or use `Remove` link to remove the featured image box.
 
  ![Featured Images](http://ankitpokhrel.com.np/dfi/screenshot-3.png)  
  ![Featured Images](http://ankitpokhrel.com.np/dfi/screenshot-4.png)

4. After adding featured images click `publish` or `update` to save featured images.

###### _Note: The featured images are only saved when you publish or update the post._

### Documentation
* [Retrieving images in a theme](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/Retrieving-data-in-a-theme)
* [Getting image title, alt and caption attributes](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/API-Functions#wiki-getting-image-title-alt-and-caption-attributes)
* [API functions](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/API-Functions)

### Other Resources
* [Blog](http://ankitpokhrel.com.np/blog/category/dynamic-featured-image/)
* [FAQs](http://wordpress.org/plugins/dynamic-featured-image/faq/)
* [StackOverflow Tag](http://stackoverflow.com/questions/tagged/dynamic-featured-image)

#### List of Available Functions
1. [get_image_id( $image_url )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/API-Functions#wiki-1-get_image_id-image_url-)
2. [get_image_thumb( $image_url, $size = "thumbnail" )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/API-Functions#wiki-2-get_image_thumb-image_url-size--thumbnail-)
3. [get_image_url( $attachment_id, $size = "full" )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/API-Functions#wiki-3-get_image_url-attachment_id-size--full-)
4. [get_post_attachment_ids( $post_id )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/API-Functions#wiki-4-get_post_attachment_ids-post_id-)
5. [is_attached( $attachment_id, $post_id )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/API-Functions#wiki-5-is_attached-attachment_id-post_id-)
6. [get_image_title( $image_url )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/API-Functions#wiki-6-get_image_title-image_url-)
7. [get_image_title_by_id( $attachment_id )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/API-Functions#wiki-7-get_image_title_by_id-attachment_id-)
8. [get_image_alt( $image_url )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/API-Functions#wiki-8-get_image_alt-image_url-)
9. [get_image_alt_by_id( $attachment_id )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/API-Functions#wiki-9-get_image_alt_by_id-attachment_id-)
10. [get_image_caption( $image_url )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/API-Functions#wiki-10-get_image_caption-image_url-)
11. [get_image_caption_by_id( $attachment_id )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/API-Functions#wiki-11-get_image_caption_by_id-attachment_id-)
12. [get_image_description( $image_url )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/API-Functions#wiki-12-get_image_description-image_url-)
13. [get_image_description_by_id( $attachment_id )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/API-Functions#wiki-13-get_image_description_by_id-attachment_id-)
14. [get_nth_featured_image( $position, $post_id )](https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/API-Functions#wiki-14-get_nth_featured_image-position-post_id--null-)

===================================================================================================

### Author available for hire

I'm available for freelance work. Remote worldwide or locally around Nepal. Drop me a line if you like.
 
### Support DFI

If you think this script is useful and saves you a lot of work, a lot of costs (PHP developers are expensive) and let you sleep much better, then donating a small amount would be very cool.

[![PayPayl donate button](http://img.shields.io/paypal/donate.png?color=green)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=J9FVY3ESPPD58)
[![Gittip](http://img.shields.io/gittip/ankitpokhrel.png)](https://www.gittip.com/ankitpokhrel/)
[![Flattr donate button](http://api.flattr.com/button/flattr-badge-large.png)](https://flattr.com/submit/auto?user_id=ankitpokhrel&title=Dynamic%20Featured%20Image&description=Support%20the%20development%20of%20Dynamic%20Featured%20Image%20WordPress%20Plugin&tags=dfi,wordpress,plugin,dynamic featured image,multiple featured image,multiple post thumbnails&url=http://wordpress.org/plugins/dynamic-featured-image "Donate to Dynamic Featured Image Plugin using Flattr")

### Questions about this project?

You can always contact me at `ankitpokhrel@gmail.com`, if you have any question or queries about the project. 

Please feel free to report any bug found. Pull requests, issues, and plugin recommendations are more than welcome!
