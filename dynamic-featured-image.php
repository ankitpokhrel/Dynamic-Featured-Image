<?php
/***
 Plugin Name: Dynamic Featured Image
 Plugin URI: http://wordpress.org/plugins/dynamic-featured-image/
 Description: Dynamically adds multiple featured image or post thumbnail functionality to your posts, pages and custom post types.
 Version: 3.1.13
 Author: Ankit Pokhrel
 Author URI: http://ankitpokhrel.com.np
 License: GPL2 or later
 License URI: http://www.gnu.org/licenses/gpl-2.0.html
 Text Domain: dynamic-featured-image
 Domain Path: /languages

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

// Avoid direct calls to this file
if ( !defined( 'ABSPATH' ) ) {
    header( 'Status: 403 Forbidden' );
    header( 'HTTP/1.1 403 Forbidden' );
    exit();
}

/**
 * Dynamic Featured Image plugin main class
 *
 * @package dynamic-featured-image
 * @author Ankit Pokhrel <ankitpokhrel@gmail.com>
 * @version 3.0.1
 */
class Dynamic_Featured_Image
{
	/**
	 * Current version of the plugin.
	 *
	 * @since 3.0.0
	 */
    const VERSION = '3.1.13';
    private $upload_dir, $upload_url, $prefix, $db, $textDomain;

	/**
	 * Constructor. Hooks all interactions to initialize the class.
	 *
	 * @since 1.0.0
	 * @access public
	 * @global object $wpdb
	 *
	 * @see	 add_action()
	 *
	 * @return	Void
	 */
    public function __construct()
    {
        $this->textDomain = 'dynamic-featured-image';

        if ( is_admin() ) {
            add_action( 'in_plugin_update_message-' . plugin_basename(__FILE__), array( $this, 'update_notice' ) );
        }

        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );
        add_action( 'add_meta_boxes',    array( $this, 'initialize_featured_box' ) );
        add_action( 'save_post', array( $this, 'save_meta' ) );
        add_action( 'plugins_loaded',    array( $this, 'load_plugin_textdomain' ) );

        //handle ajax request
        add_action( 'wp_ajax_dfiMetaBox_callback', array( $this, 'ajax_callback' ) );

        //get the site protocol
        $protocol = ( (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') ||
                        $_SERVER['SERVER_PORT'] == 443 ) ? "https://" : "http://";

        $this->upload_dir = wp_upload_dir();
        $this->upload_url = preg_replace('#^https?://#', '', $this->upload_dir['baseurl']);

        //add protocol to the upload url
        $this->upload_url = $protocol . $this->upload_url;

        global $wpdb;
        $this->db = $wpdb;

    } // END __construct()

	/**
	 * Add required admin scripts
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @see  wp_enque_style()
	 * @see  wp_register_script()
	 * @see  wp_enqueue_script()
	 *
	 * @return Void
	 */
    public function enqueue_admin_scripts()
    {
        //enqueue styles
        wp_enqueue_style( 'style-dfi', plugins_url( '/css/style-dfi.css', __FILE__ ), array(), self::VERSION );
        wp_enqueue_style( 'dashicons', plugins_url( '/css/dashicons.css', __FILE__ ), array(), self::VERSION );

        //register script
        wp_register_script( 'scripts-dfi', plugins_url( '/js/script-dfi.js', __FILE__), array( 'jquery' ), self::VERSION );

        //localize the script with required data
        wp_localize_script(
            'scripts-dfi',
            'WP_SPECIFIC',
            array(
                'upload_url' => $this->upload_url,
                'metabox_title' => __('Featured Image ', $this->textDomain),
                'mediaSelector_title' => __('Dynamic Featured Image - Media Selector', $this->textDomain),
                'mediaSelector_buttonText' => __('Set Featured Image', $this->textDomain)
            )
        );

        //enqueue scripts
        wp_enqueue_script( 'scripts-dfi' );

    } // END initialize_components()

	/**
	 * Add featured meta boxes dynamically
	 *
	 * @since 1.0.0
	 * @access public
	 * @global object $post
	 *
	 * @see  get_post_meta()
	 * @see  get_post_types()
	 * @see  add_meta_box()
	 * @see  add_filter()
	 *
	 * @return Void
	 */
    public function initialize_featured_box()
    {
        global $post;

        $featuredData = get_post_meta( $post->ID, 'dfiFeatured', true );
        $totalFeatured = count( $featuredData );

        $filter = array( 'attachment', 'revision', 'nav_menu_item' );
        $postTypes = get_post_types();
        $postTypes = array_diff( $postTypes, $filter );

        $postTypes = apply_filters('dfi_post_types', $postTypes);

        if ( !empty($featuredData) && $totalFeatured >= 1 ) {
            $i = 2;
            foreach ($featuredData as $featured) {
                self::_dfi_add_meta_box($postTypes, $featured, $i);
                $i++;
            }
        } else {
            self::_dfi_add_meta_box($postTypes);
        }

    } // END initialize_featured_box()

	/**
	 * Translates more than one digit number digit by digit.
	 * @param  Integer $number Integer to be translated
	 * @return String         Translated number
	 */
    private function _get_number_translation($number)
    {
        if ($number <= 9) {
            return __($number, $this->textDomain);
        } else {
            $pieces = str_split($number, 1);
            $buffer = '';
            foreach ($pieces as $piece) {
                $buffer .= __($piece, $this->textDomain);
            }

            return $buffer;
        }
    }

	/**
	 * adds meta boxes
	 * @param  Array $postTypes     post types to show featured image box
	 * @param  Object $featured     callback arguments
	 * @param  Integer $i           index of the featured image
	 * @return Void
	 */
    private function _dfi_add_meta_box($postTypes, $featured = null, $i = null)
    {
        if ( !is_null($i) ) {
            foreach ($postTypes as $type) {
                add_meta_box(
                    'dfiFeaturedMetaBox-' . $i,
                    __("Featured Image ", $this->textDomain) . self::_get_number_translation($i),
                    array( $this, 'featured_meta_box' ),
                    $type,
                    'side',
                    'low',
                    array( $featured, $i + 1 )
                );
                add_filter( "postbox_classes_{$type}_dfiFeaturedMetaBox-" . $i, array( $this, 'add_metabox_classes' ) );
            }

        } else {
            foreach ($postTypes as $type) {
                add_meta_box(
                    'dfiFeaturedMetaBox',
                    __( 'Featured Image ', $this->textDomain ) . __(2, $this->textDomain),
                    array( $this, 'featured_meta_box' ),
                    $type,
                    'side',
                    'low',
                    array( null, null )
                );
                add_filter( "postbox_classes_{$type}_dfiFeaturedMetaBox", array( $this, 'add_metabox_classes' ) );
            }
        }

    }

	/**
	 * Featured meta box as seen in the admin
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @see  wp_nonce_field()
	 * @see  plugin_basename()
	 *
	 * @param  Object $post global post object
	 * @param  Array $featured array containing featured image count
	 *
	 * @return Void
	 */
    public function featured_meta_box($post, $featured)
    {
        $featuredImg = $featured['args'][0];
        $featuredId    = is_null( $featured['args'][1]) ? 2 : --$featured['args'][1];

        $featuredImgTrimmed = $featuredImgFull = $featuredImg;
        if ( !is_null( $featured['args'][0] ) ) {
            @list( $featuredImgTrimmed, $featuredImgFull ) = explode( ',', $featuredImg );
        }

        try {

            $thumbnail = $this->get_image_thumb( $this->upload_url . $featuredImgFull, 'medium' );
            if ( is_null($thumbnail) ) {

                //medium sized thumbnail image is missing
                throw new Exception("Medium size image not found", 1);

            }

        } catch (Exception $e) {

            //since medium sized thumbnail image was not found,
            //let's set full image url as thumbnail
            $thumbnail = $featuredImgFull;

        }

        //Add a nonce field
        wp_nonce_field(plugin_basename(__FILE__), 'dfi_fimageplug-' . $featuredId);
        echo self::_get_featured_box($featuredImgTrimmed, $featuredImg, $featuredId, $thumbnail);

    } // END featured_meta_box()

	/**
	 * Returns featured box html content
	 * @since  3.1.0
	 * @access private
	 *
	 * @param  String $featuredImgTrimmed Medium sized image
	 * @param  String $featuredImg        Full sized image
	 * @param  String $featuredId         Attachment Id
	 * @param  String $thumbnail          Thumb sized image
	 *
	 * @return String                     Html content
	 */
    private function _get_featured_box($featuredImgTrimmed, $featuredImg, $featuredId, $thumbnail)
    {
        $hasFeaturedImage = !empty($featuredImgTrimmed) ? 'hasFeaturedImage' : '';
        $thumbnail = !is_null($thumbnail) ? $thumbnail : '';
        $dfiEmpty = is_null($featuredImgTrimmed) ? 'dfiImgEmpty' : '';

        return "<a href='javascript:void(0)' class='dfiFeaturedImage {$hasFeaturedImage}' title='". __('Set Featured Image', $this->textDomain) . "' data-post-id='" . get_the_ID() . "'><span class='dashicons dashicons-camera'></span></a><br/>
			<img src='" . $thumbnail . "' class='dfiImg {$dfiEmpty}'/>
			<div class='dfiLinks'>
				<a href='javascript:void(0)'' data-id='{$featuredId}' data-id-local='" . self::_get_number_translation( ($featuredId + 1) ) . "' class='dfiAddNew dashicons dashicons-plus' title='" . __('Add New', $this->textDomain) ."'></a>
				<a href='javascript:void(0)' class='dfiRemove dashicons dashicons-minus' title='" . __('Remove', $this->textDomain) . "'></a>
			</div>
			<div class='dfiClearFloat'></div>
			<input type='hidden' name='dfiFeatured[]' value='{$featuredImg}'  class='dfiImageHolder' />";
    }

	/**
	 * Load new featured meta box via ajax
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @see  wp_nonce_field()
	 * @see  plugin_basename()
	 *
	 * @return Void
	 */
    public function ajax_callback()
    {
        $featuredId = isset($_POST['id']) ? (int) strip_tags( trim( $_POST['id'] ) ) : null;

        if ( is_null( $featuredId ) ) {
            return;
        }

        wp_nonce_field( plugin_basename(__FILE__), 'dfi_fimageplug-' . $featuredId );
        ?>
				<a href="javascript:void(0)" class='dfiFeaturedImage' title="<?php echo __('Set Featured Image', $this->textDomain) ?>"><span class="dashicons dashicons-camera"></span></a><br/>
				 <img src="" class='dfiImg dfiImgEmpty'/>
				 <div class='dfiLinks'>
				<a href="javascript:void(0)" data-id='<?php echo $featuredId ?>' data-id-local='<?php echo self::_get_number_translation( ($featuredId + 1) ) ?>' class='dfiAddNew dashicons dashicons-plus' title="<?php echo __('Add New', $this->textDomain) ?>"></a>
				<a href="javascript:void(0)" class='dfiRemove dashicons dashicons-minus' title="<?php echo __('Remove', $this->textDomain) ?>"></a>
				 </div>
				 <div class='dfiClearFloat'></div>
				 <input type='hidden' name="dfiFeatured[]" value="" class="dfiImageHolder" />
		<?php
        die();

    } // END MetaBox_callback())

	/**
	 * Add custom class 'featured-meta-box' to meta box
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @see  add_metabox_classes
	 *
	 * @param  $classes classes to add in the meta box
	 *
	 * @return string
	 */
    public function add_metabox_classes($classes)
    {
        array_push( $classes, 'featured-meta-box' );

        return $classes;

    } // END add_metabox_classes()

	/**
	 * Update featured images in the database
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @see  wp_verify_nonce()
	 * @see  plugin_basename()
	 * @see  update_post_meta()
	 * @see  current_user_can()
	 *
	 * @param  Integer $post_id current post id
	 *
	 * @return Void
	 */
    public function save_meta($post_id)
    {
        //Check autosave
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }

        $keys = array_keys( $_POST );
        foreach ($keys as $key) {
            if ( preg_match( '/dfi_fimageplug-\d+$/', $key ) ) {
                //Verify nonce
                if ( !wp_verify_nonce( $_POST[$key], plugin_basename(__FILE__) ) ) {
                    return;
                }
            }
        }

        //Check permission before saving data
        if ( current_user_can( 'edit_posts', $post_id ) && isset( $_POST['dfiFeatured'] ) ) {
            update_post_meta( $post_id, 'dfiFeatured', $_POST['dfiFeatured'] );
        }

    } // END save_meta()

	/**
	 * Add update notice. Displayed in plugin update page.
	 *
	 * @since 2.0.0
	 * @access public
	 * @ignore
	 *
	 * @return Void
	 */
    public function update_notice()
    {
        $info = __( 'ATTENTION! Please read the <a href="https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki" target="_blank">DOCUMENTATION</a> properly before update.', $this->textDomain );
        echo '<div style="color:red; padding:7px 0;">' . strip_tags( $info, '<a><b><i><span>' ) . '</div>';

    } // END update_notice()

	/** Helper functions */

    private function execute_query($query)
    {       
        return $this->db->get_var( $query );
    }

	/**
	 * Get attachment id of the image by image url
	 *
	 * @since 3.1.7
	 * @access private
	 * @global object $wpdb
	 *
	 * @param  String $image_url url of the image
	 *
	 * @return string
	 */
    private function _get_attachment_id($image_url)
    {
        return self::execute_query($this->db->prepare( "SELECT ID FROM " . $this->db->posts . " WHERE guid = %s", $image_url ));

    } // END _get_attachment_id()

	/**
	 * Get image url of the image by attachment id
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @see  wp_get_attachment_image_src()
	 *
	 * @param  Integer $attachment_id attachment id of an image
	 * @param  String $size size of the image to fetch (thumbnail, medium, full)
	 *
	 * @return String
	 */
    public function get_image_url($attachment_id, $size = 'full')
    {
        $image_thumb = wp_get_attachment_image_src( $attachment_id, $size );

        return empty( $image_thumb ) ? null : $image_thumb[0];

    } // END get_image_url()

	/**
	 * Get image thumbnail url of specific size by image url
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @see  get_image_id()
	 * @see  wp_get_attachment_image_src()
	 *
	 * @param  String $image_url url of an image
	 * @param  String $size size of the image to fetch (thumbnail, medium, full)
	 *
	 * @return String
	 */
    public function get_image_thumb($image_url, $size = 'thumbnail')
    {
        $attachment_id = self::get_image_id( $image_url );
        $image_thumb = wp_get_attachment_image_src( $attachment_id, $size );

        return empty( $image_thumb ) ? null : $image_thumb[0];

    } // END get_image_thumb()

	/**
	 * Gets attachment id from given image url
	 * @param  String $image_url url of an image
	 * @return Integer|Null            attachment id of an image
	 *
	 * @since  2.0.0
	 * @access public
	 */
    public function get_image_id($image_url)
    {
        $attachment_id = self::_get_attachment_id( $image_url );
        if ( is_null($attachment_id) ) {
            //check if the image is edited image
            //and try to get the attachment id
            $image_url = str_replace($this->upload_url . "/", '', $image_url);
            $row = self::execute_query( $this->db->prepare( "SELECT post_id FROM " . $this->db->postmeta . " WHERE meta_value = %s", $image_url ) );
            if ( !is_null($row) ) {
                $attachment_id = $row;
            }
        }

        return $attachment_id;
    }

	/**
	 * Get image title
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param  String $image_url url of an image
	 *
	 * @return String
	 */
    public function get_image_title($image_url)
    {
        return self::execute_query( $this->db->prepare( "SELECT post_title FROM " . $this->db->posts . " WHERE guid = %s", $image_url ) );

    } // END get_image_title()

	/**
	 * Get image title by id
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param  Integer $attachment_id attachment id of an image
	 *
	 * @return String
	 */
    public function get_image_title_by_id($attachment_id)
    {
        return self::execute_query( $this->db->prepare( "SELECT post_title FROM " . $this->db->posts . " WHERE ID = %d", $attachment_id ) );

    } // END get_image_title_by_id()

	/**
	 * Get image caption
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param  String $image_url url of an image
	 *
	 * @return String
	 */
    public function get_image_caption($image_url)
    {
        return self::execute_query( $this->db->prepare("SELECT post_excerpt FROM " . $this->db->posts . " WHERE guid = %s", $image_url ) );

    } // END get_image_caption()

	/**
	 * Get image caption by id
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @param  Integer $attachment_id attachment id of an image
	 *
	 * @return String
	 */
    public function get_image_caption_by_id($attachment_id)
    {
        return self::execute_query( $this->db->prepare("SELECT post_excerpt FROM " . $this->db->posts . " WHERE ID = %d", $attachment_id) );

    } // END get_image_caption_by_id()

	/**
	 * Get image alternate text
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @see  get_post_meta()
	 *
	 * @param  String $image_url url of an image
	 *
	 * @return String
	 */
    public function get_image_alt($image_url)
    {
        $attachment = $this->db->get_col( $this->db->prepare( "SELECT ID FROM " . $this->db->posts . " WHERE guid = %s", $image_url ) );

        $alt = null;
        if ( !empty( $attachment ) ) {
            $alt = get_post_meta( $attachment[0], '_wp_attachment_image_alt' );
        }

        return ( is_null( $alt ) || empty( $alt ) ) ? null : $alt[0];

    } // END get_image_alt()

	/**
	 * Get image alternate text by attachment id
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @see  get_post_meta()
	 *
	 * @param  Integer $attachment_id attachment id of an image
	 *
	 * @return String
	 */
    public function get_image_alt_by_id($attachment_id)
    {
        $alt = get_post_meta($attachment_id, '_wp_attachment_image_alt');

        return empty($alt) ? null : $alt[0];

    } // END get_image_alt_by_id()

	/**
	 * Get image description
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @param  String $image_url url of an image
	 *
	 * @return String
	 */
    public function get_image_description($image_url)
    {
        return self::execute_query( $this->db->prepare( "SELECT post_content FROM " . $this->db->posts . " WHERE guid = %s", $image_url ) );

    } // END get_image_description()

	/**
	 * Get image description by id
	 *
	 * @since 3.0.0
	 * @access public
	 *
	 * @param  Integer $attachment_id attachment id of an image
	 *
	 * @return String
	 */
    public function get_image_description_by_id($attachment_id)
    {
        return self::execute_query( $this->db->prepare( "SELECT post_content FROM " . $this->db->posts . " WHERE ID = %d", $attachment_id ) );

    } // END get_image_description_by_id()

	/**
	 * Get all attachment ids of the post
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @see  get_post_meta()
	 *
	 * @param  Integer $post_id id of the current post
	 *
	 * @return Array
	 */
    public function get_post_attachment_ids($post_id)
    {
        $dfiImages = get_post_meta($post_id, 'dfiFeatured', true);

        $retVal = array();
        if ( !empty( $dfiImages ) && is_array( $dfiImages ) ) {
            foreach ($dfiImages as $dfiImage) {
                @list( $dfiImageTrimmed, $dfiImageFull ) = explode( ',', $dfiImage );

                $retVal[] = $this->get_image_id( $this->upload_url . $dfiImageFull );
            }
        }

        return $retVal;

    } // END get_post_attachment_ids()

	/**
	 * Fetches featured image data of nth position
	 *
	 * @since  3.0.0
	 * @access  public
	 *
	 * @see  get_featured_images()
	 *
	 * @param  Integer $position position of the featured image
	 * @param  Integer $post_id  id of the current post
	 *
	 * @return Array if found, null otherwise
	 */
    public function get_nth_featured_image($position, $post_id = null)
    {
    if ( is_null( $post_id ) ) {
        global $post;
        $post_id = $post->ID;
    }

    $featured_images = $this->get_featured_images( $post_id );

    return isset($featured_images[$position - 2 ]) ? $featured_images[$position - 2] : null;

    } // END get_nth_featured_image()

	/**
	 * Check if the image is attached with the particular post
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @see  get_post_attachment_ids()
	 *
	 * @param  $attachment_id attachment id of an image
	 * @param  $post_id id of the current post
	 *
	 * @return boolean
	 */
    public function is_attached($attachment_id, $post_id)
    {
        $attachment_ids = $this->get_post_attachment_ids( $post_id );

        return in_array( $attachment_id, $attachment_ids ) ? true : false;

    } // END is_attached()

	/**
	 * Retrieve featured images for specific post(s)
	 *
	 * @since 2.0.0
	 * @access public
	 *
	 * @see  get_post_meta()
	 *
	 * @param  Integer $post_id id of the current post
	 *
	 * @return Array
	 */
    public function get_featured_images($post_id = null)
    {
        if ( is_null( $post_id ) ) {
            global $post;
            $post_id = $post->ID;
        }

        $dfiImages = get_post_meta($post_id, 'dfiFeatured', true);

        $retImages = array();
        if ( !empty( $dfiImages ) && is_array( $dfiImages ) ) {
            $dfiImages = array_filter($dfiImages);

            $count = 0;
            foreach ($dfiImages as $dfiImage) {
                @list( $dfiImageTrimmed, $dfiImageFull ) = explode( ',', $dfiImage );

                try {

                    $retImages[$count]['thumb']            = $this->upload_url . $dfiImageTrimmed;
                    $retImages[$count]['full']            = $this->upload_url . $dfiImageFull;
                    $retImages[$count]['attachment_id']    = $this->get_image_id( $this->upload_url . $dfiImageFull );

                } catch (Exception $e) { /* Ignore the exception and continue with other featured images */ }

                $count++;
            }
        }

        return $retImages;

    } // END get_featured_images()

	/**
	 * Retrieve featured images for specific post(s) including the default Featured Image
	 *
	 * @since 3.1.7
	 * @access public
	 *
	 * @see  $this->get_featured_images()
	 *
	 * @param Integer $post_id id of the current post
	 *
	 * @return Array An array of images or an empty array on failure
	 */
    public function get_all_featured_images($post_id = null)
    {
        if ( is_null( $post_id ) ) {
            global $post;
            $post_id = $post->ID;
        }

        $thumbnail_id = get_post_thumbnail_id( $post_id );

        $featured_image_array = array();
        if ( ! empty( $thumbnail_id ) ) {
            $featured_image = array(
                'thumb' => wp_get_attachment_thumb_url( $thumbnail_id ),
                'full' => wp_get_attachment_url( $thumbnail_id ),
                'attachment_id' => $thumbnail_id
            );
            $featured_image_array[] = $featured_image;
        }

        $dfiImages = $this->get_featured_images( $post_id );

        $all_featured_images = array_merge( $featured_image_array, $dfiImages );

        return $all_featured_images;

    }

	/**
	 * Load the plugin's textdomain hooked to 'plugins_loaded'.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @see	load_plugin_textdomain()
	 * @see	plugin_basename()
	 * @action	plugins_loaded
	 *
	 * @return	Void
	 */
    public function load_plugin_textdomain()
    {
        load_plugin_textdomain(
            $this->textDomain,
            false,
            dirname( plugin_basename( __FILE__ ) ) . '/languages/'
        );

    } // END load_plugin_textdomain()

} // END class Dynamic_Featured_Image


/**
 * Instantiate the main class
 *
 * @since 1.0.0
 * @access public
 *
 * @var	object	$dynamic_featured_image holds the instantiated class {@uses Dynamic_Featured_Image}
 */
global $dynamic_featured_image;
$dynamic_featured_image = new Dynamic_Featured_Image();
