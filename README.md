## Dynamic Featured Image (A WordPress Plugin)

_Dynamically adds multiple featured image (post thumbnail) functionality to posts, pages and custom post types._


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

## ShortCode and FancyBox integration ( ver. 1.2+ )

### Using shortcode
The `shortcode` to get all the image with embeded fancybox support is:
```
[dfiFeaturedImages]

//Options
width: width of the thumbnail displayed (default: 150)
height: height of the thumbnail displayed (default: 150)

//Example
[dfiFeaturedImages width="300" height="300"]
```

### Retrieving Images with Fancybox Support in a Theme

Function syntax

```
dfiDisplayFeaturedImages($postId = null, $width = 150, $height = 150)
```

Example

```
if( function_exists('dfiDisplayFeaturedImages') ){
    dfiDisplayFeaturedImages();
}
```

or you can also call the shortcode directly

```
if( function_exists('dfiDisplayFeaturedImages') ){
   echo do_shortcode('[dfiFeaturedImages]');
}
```

### Applying Fancybox Settings
The fancybox settings can be applied by directly pasting the settings code in the "Fancybox Settings" textbox but the settings needs to be in a special format (JSON).
You can get the settings for fancybox from [fancybox docs](http://fancyapps.com/fancybox/).

* Go to `Settings > DFI Settings`
* Write your settings in a textbox as shown in the image and click `Save changes`
![DFI Settings](http://ankitpokhrel.com.np/dfi/snapshot_4.jpg)

#### Required Format for Fancybox Settings
The fancybox settings should be in a valid `JSON` format but without the opening curly braces. Some examples of the valid settings are:

* Basic Settings
```
"maxWidth": 800,
"maxHeight": 600,
"fitToView": false,
"width": "70%",
"height": "70%",
"autoSize": false,
"closeClick": false,
"openEffect": "none",
"closeEffect": "none"
```

* Using Thumbnail Helper
```
"openEffect": "elastic",
"openSpeed": 150,
"closeEffect": "elastic",
"closeSpeed": 150,
"closeClick": true,
"helpers": {      
        "thumbs": {
            "width": 50,
            "height": 50
         }
 }
```

* Using Button Helper
```
"prevEffect": "none",
"nextEffect": "none",
"closeBtn": false,
"helpers": {       
      "buttons": {}
}
```

### Author available for hire

I'm available for freelance work. Remote worldwide or locally around Nepal. Drop me a line if you like.
 
### Donate with [PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=J9FVY3ESPPD58)

If you think this script is useful and saves you a lot of work, a lot of costs (PHP developers are expensive) and let you sleep much better, then donating a small amount would be very cool.

[![Paypal](http://ankitpokhrel.com.np/img/paypal.png)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=J9FVY3ESPPD58)

### Questions about this project?

You can always contact me at `ankitpokhrel@gmail.com`, if you have any question or queries about the project. 

Please feel free to report any bug found. Pull requests, issues, and plugin recommendations are more than welcome!
