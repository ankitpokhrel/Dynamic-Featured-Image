<?php
/**
 * Plugin Name: Dynamic Featured Image
 * Plugin URI: http://wordpress.org/plugins/dynamic-featured-image/
 * Description: Add multiple featured image dynamically in your wordpress posts.
 * Version: 2.0.0
 * Author: Ankit Pokhrel
 * Author URI: http://ankitpokhrel.com.np
 */
 
 /*
   Copyright (C) 2013 Ankit Pokhrel <ankitpokhrel@gmail.com, http://ankitpokhrel.com.np>,    
    
   This program is free software; you can redistribute it and/or modify
   it under the terms of the GNU General Public License as published by
   the Free Software Foundation; either version 3 of the License, or
   (at your option) any later version.
    
   This program is distributed in the hope that it will be useful,
   but WITHOUT ANY WARRANTY; without even the implied warranty of
   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
   GNU General Public License for more details.
    
   You should have received a copy of the GNU General Public License
   along with this program.  If not, see <http://www.gnu.org/licenses/>.
  */
 
 define('DYNAMIC_FEATURED_IMAGE_VERSION', '2.0.0');
 define('DOCUMENTATION_PAGE', 'https://github.com/ankitpokhrel/Dynamic-Featured-Image');

 //prevent direct access
 if ( !function_exists( 'add_action' ) ) {
    echo "Hi there!  I'm just a plugin, not much I can do when called directly.";
    exit();
 }
 
 add_action('admin_init', 'dfi_initialize_components');
 function dfi_initialize_components(){
    //enqueue styles
    wp_enqueue_style('thickbox');   
    wp_enqueue_style( 'style-dfi', plugins_url('/css/style-dfi.css', __FILE__) );
    
    //register scripts
    wp_register_script('dfi-scripts', plugins_url('/js/script-dfi.js', __FILE__), array('jquery','media-upload','thickbox'));   
   
    //enqueue scripts    
    wp_enqueue_script('thickbox');   
    wp_enqueue_script('media-models');
    wp_enqueue_script('media-upload');      
    wp_enqueue_script('dfi-scripts');
    wp_enqueue_script( 'script-dfi.js');    
 }
 
 /*
  * Add featured meta boxes dynamically 
  */
  
 add_action('add_meta_boxes', 'dfi_initialize_featured_box');
 function dfi_initialize_featured_box(){
    global $post;
    $data = get_post_custom($post->ID); 
    
    $totalFeatured = 0;
    if( isset($data['dfiFeatured'][0]) && !empty($data['dfiFeatured'][0]) ){     
     $featuredData = unserialize($data['dfiFeatured'][0]);          
     $totalFeatured = count($featuredData);      
    }
    
    $filter = array('attachment', 'revision', 'nav_menu_item'); 
    $postTypes = get_post_types();     
    $postTypes = array_diff($postTypes, $filter);
           
    if( $totalFeatured >= 1 ){
      $i = 2;                   
      foreach($featuredData as $featured){
        foreach($postTypes as $type) {         
            add_meta_box('dfiFeaturedMetaBox-'.$i, 'Featured Image ' . $i, 'dfi_featured_meta_box', $type, 'side', 'low', array($featured, $i+1));      
            add_filter( "postbox_classes_{$type}_dfiFeaturedMetaBox-".$i, 'add_metabox_classes' );                              
        }
        
        $i++;
      }
    } else {        
        foreach($postTypes as $type){
            add_meta_box( 'dfiFeaturedMetaBox', 'Featured Image 2', 'dfi_featured_meta_box', $type, 'side', 'low', array(null, null) );   
            add_filter( "postbox_classes_{$type}_dfiFeaturedMetaBox", 'add_metabox_classes' );           
        }
    }
 }

 function dfi_featured_meta_box($post, $featured){  
    $featuredImg = is_null($featured['args'][0]) ? '' : $featured['args'][0];   
    $featuredId = is_null($featured['args'][1]) ? 2 : --$featured['args'][1];
    
    $featuredImgTrimmed = $featuredImgFull = $featuredImg;
    if( !empty($featuredImg) ){
        list($featuredImgTrimmed, $featuredImgFull) = explode(',', $featuredImg); 
    }           
    
    $thumbnail = dfi_get_image_thumb( site_url() . $featuredImgFull, 'medium' );
    
    //Add a nonce field   
    wp_nonce_field( plugin_basename(__FILE__), 'dfi_fimageplug-' . $featuredId);    
 ?>   
   <a href="javascript:void(0)" class='dfiFeaturedImage' data-post-id="<?php the_ID() ?>"><?php _e('Set featured image', 'ap_dfi_dynamic-featured-image') ?></a><br/>       
   <img src="<?php if( isset($thumbnail) && !is_null($thumbnail) ) echo $thumbnail; ?>" class='dfiImg <?php if( !isset($featuredImgTrimmed) || is_null($featuredImgTrimmed) ) echo 'dfiImgEmpty' ?>'/>
   <div class='dfiLinks'>   
    <a href="javascript:void(0)" data-id='<?php echo $featuredId ?>' class='dfiAddNew'><?php _e('Add New', 'ap_dfi_dynamic-featured-image') ?></a>
    <a href="javascript:void(0)" class='dfiRemove'><?php _e('Remove', 'ap_dfi_dynamic-featured-image') ?></a>
   </div>
   <div class='dfiClearFloat'></div>
   <input type='hidden' name="dfiFeatured[]" value="<?php echo $featuredImg ?>"  class="dfiImageHolder" />
 <?php } 
 
 //handle ajax request
 add_action( 'wp_ajax_nopriv_ dfiMetaBox_callback', 'dfiMetaBox_callback' );
 add_action( 'wp_ajax_dfiMetaBox_callback', 'dfiMetaBox_callback' );
 function dfiMetaBox_callback(){
     $featuredId = isset($_POST['id']) ? (int) strip_tags( trim($_POST['id']) ) : null;
     
     if( is_null($featuredId) ) return;
     
     wp_nonce_field( plugin_basename(__FILE__), 'dfi_fimageplug-' . $featuredId );
 ?>
      <a href="javascript:void(0)" class='dfiFeaturedImage'><?php _e('Set featured image', 'ap_dfi_dynamic-featured-image') ?></a><br/>        
       <img src="" class='dfiImg dfiImgEmpty'/>
       <div class='dfiLinks'>   
        <a href="javascript:void(0)" data-id='<?php echo $featuredId ?>' class='dfiAddNew'><?php _e('Add New', 'ap_dfi_dynamic-featured-image') ?></a>
        <a href="javascript:void(0)" class='dfiRemove'><?php _e('Remove', 'ap_dfi_dynamic-featured-image') ?></a>
       </div>
       <div class='dfiClearFloat'></div>
       <input type='hidden' name="dfiFeatured[]" value="" class="dfiImageHolder" />
<?php
     die();
 }
 
 /*
  * Add custom class, featured-meta-box to meta box
  */
 
 function add_metabox_classes( $classes ) {
    array_push( $classes, 'featured-meta-box' );
    return $classes;
} 

 /*
  * Update featured images
  */
  
 add_action('save_post', 'save_dfi_featured_meta');
 function save_dfi_featured_meta( $post_id ) {
     $featuredIds = array();     
     $keys = array_keys( $_POST );    
     foreach ( $keys as $key ) {
        if ( preg_match( '/dfi_fimageplug-.$/', $key ) ) {
             $featuredIds[] = $key;
        }
     }
         
    //Verify nonce
    foreach( $featuredIds as $nonceId ) {
     if ( !wp_verify_nonce( $_POST[$nonceId], plugin_basename(__FILE__) ) ) {
       return;
     }
    }
    
    //Check autosave
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
        return;
    }
     
    //Check permission before saving data       
    if( !empty($_POST) && current_user_can('edit_posts', $post_id) ) {
      if( isset($_POST['dfiFeatured']) ){
       update_post_meta($post_id, 'dfiFeatured', $_POST['dfiFeatured']);
      }
    }
 }
 
 /* Helper functions */
 
 /*
  * Add update notice
  */
 function dfi_update_notice() {
    $info = __( ' ATTENTION! This version has some changes that will break your existing work. 
                   Please read the <a href="' . DOCUMENTATION_PAGE . '" target="_blank">DOCUMENTATION</a> properly before update.', 'dfi_text_domain' );
    echo '<div style="color:red; padding:7px 0;">' . strip_tags( $info, '<a><b><i><span>' ) . '</div>';
 }

 if( is_admin() )
    add_action( 'in_plugin_update_message-' . plugin_basename(__FILE__), 'dfi_update_notice' );
 
 /*
  * Get attachment id of the image by image url
  *
  * @return String
  */
 function dfi_get_image_id( $image_url ) {
    global $wpdb;
    $prefix = $wpdb->prefix;
    $attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM " . $prefix . "posts" . " WHERE guid= %s", $image_url ) );
    
    return empty($attachment) ? null : $attachment[0];
 }
 
 /*
  * Get image url of the image by attachment id
  *
  * @return String
  */
 function dfi_get_image_url( $attachmentId, $size = 'full' ) {
    $image_thumb = wp_get_attachment_image_src( $attachmentId, $size );
    
    return empty($image_thumb) ? null : $image_thumb[0];
 }
 
 /*
  * Get image thumbnail url of specific size by image url
  *
  * @return String
  */
 function dfi_get_image_thumb( $image_url, $size = 'thumbnail' ) {   
    $attachment_id = dfi_get_image_id( $image_url );
    $image_thumb = wp_get_attachment_image_src( $attachment_id, $size );
    
    return empty($image_thumb) ? null : $image_thumb[0];
 }
 
 /*
  * Get image title
  *
  * @return String
  */
 function dfi_get_image_title( $image_url ) {
    global $wpdb;
    $prefix = $wpdb->prefix;
    $post_title = $wpdb->get_col( $wpdb->prepare( "SELECT post_title FROM " . $prefix . "posts" . " WHERE guid = %s", $image_url ) );
   
    return empty($post_title) ? null : $post_title[0];  
 }
 
 /*
  * Get image title by id
  *
  * @return String
  */
 function dfi_get_image_title_by_id( $attachment_id ) {
    global $wpdb;
    $prefix = $wpdb->prefix;
    $post_title = $wpdb->get_col( $wpdb->prepare( "SELECT post_title FROM " . $prefix . "posts" . " WHERE ID = %d", $attachment_id ) );
   
    return empty($post_title) ? null : $post_title[0];  
 }
 
 /*
  * Get image caption
  *
  * @return String
  */
 function dfi_get_image_caption( $image_url ) {
    global $wpdb;
    $prefix = $wpdb->prefix;
    $post_caption = $wpdb->get_col( $wpdb->prepare( "SELECT post_excerpt FROM " . $prefix . "posts" . " WHERE guid = %s", $image_url ) );      
   
    return empty($post_caption) ? null : $post_caption[0];  
 }
 
 /*
  * Get image caption by id
  *
  * @return String
  */
 function dfi_get_image_caption_by_id( $attachment_id ) {
    global $wpdb;
    $prefix = $wpdb->prefix;
    $post_caption = $wpdb->get_col( $wpdb->prepare( "SELECT post_excerpt FROM " . $prefix . "posts" . " WHERE ID = %d", $attachment_id ) );      
   
    return empty($post_caption) ? null : $post_caption[0];  
 }
 
/*
 * Get image alternate text
 *
 * @return String
 */
 function dfi_get_image_alt( $image_url ) {
    global $wpdb;
    $prefix = $wpdb->prefix;
    $attachment = $wpdb->get_col( $wpdb->prepare( "SELECT ID FROM " . $prefix . "posts" . " WHERE guid = %s", $image_url ) );
   
    $alt = null;
    if( !empty($attachment) ){
        $alt = get_post_meta($attachment[0], '_wp_attachment_image_alt');
    }
    
    return ( is_null($alt) || empty($alt) ) ? null : $alt[0];
 }
 
 /*
 * Get image alternate text by attachment id
 *
 * @return String
 */
 function dfi_get_image_alt_by_id( $attachment_id ) {    
    $alt = get_post_meta($attachment_id, '_wp_attachment_image_alt');
   
    return empty($alt) ? null : $alt[0];
 }
 
 /*
  * Get all attachment ids of the post
  * 
  * @return Array
  */
  function dfi_get_post_attachment_ids( $post_id ){    
    $dfiImages = get_post_custom($post_id);
    $dfiImages = ( isset($dfiImages['dfiFeatured'][0]) ) ? @array_filter( unserialize( $dfiImages['dfiFeatured'][0] ) ) : array();
    
    $retVal = array();
    if( !empty($dfiImages) && is_array($dfiImages) ) {   
      foreach($dfiImages as $dfiImage){
        list($dfiImageTrimmed, $dfiImageFull) = explode(',', $dfiImage);    
              
        $retVal[] = dfi_get_image_id( site_url() . $dfiImageFull );     
      }
    }
    
    return $retVal;
  }
 
 /*
  * Check if the image is attached with the particular post
  * 
  * @return boolean
  */
 function dfi_is_attached( $attachment_id, $post_id ){
     $attachment_ids = dfi_get_post_attachment_ids( $post_id );
     
     return in_array($attachment_id, $attachment_ids) ? true : false;
 }
 
 /*
  * Retrieve featured images for specific post(s)
  * 
  * @return Array
  */  
 function dfi_get_featured_images($post_id = null){
    if( is_null($post_id) ){
     global $post;
     $post_id = $post->ID;
    }
    
    $dfiImages = get_post_custom($post_id);
    $dfiImages = ( isset($dfiImages['dfiFeatured'][0]) ) ? @array_filter( unserialize( $dfiImages['dfiFeatured'][0] ) ) : array();
    
    $retImages = array();
    if( !empty($dfiImages) && is_array($dfiImages) ) {
      $count = 0;
      foreach($dfiImages as $dfiImage){
        @list($dfiImageTrimmed, $dfiImageFull) = explode(',', $dfiImage);
        $retImages[$count]['thumb'] = site_url() . $dfiImageTrimmed;
        $retImages[$count]['full'] = site_url() . $dfiImageFull;
        $retImages[$count]['attachment_id'] = dfi_get_image_id( site_url() . $dfiImageFull );                   
        
        $count++;
      }
    }  
    

    return ( !empty($retImages) ) ? $retImages : null;
 }