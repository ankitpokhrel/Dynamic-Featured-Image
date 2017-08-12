<?php

/**
 * Tests for Dynamic Featured Image Plugin
 */
class DynamicFeaturedImageTest extends WP_UnitTestCase {

    private $__mockBuilder = null;
    private $__post_id = null;
    private $__attachment_id = null;

    protected $_dfi = null;
    protected $_pluginData = null;

    public function setUp() {
        parent::setUp();

        $this->__mockBuilder = $this->getMockBuilder( 'Dynamic_Featured_Image' );

        $this->_dfi = new Dynamic_Featured_Image;

        $this->_pluginData = get_plugin_data( dirname( dirname( __FILE__ ) ) . '/dynamic-featured-image.php' );

        $this->__post_id       = $this->factory->post->create( [
            'post_title' => 'Dynamic Featured Image WordPress Plugin',
        ] );
        $this->__attachment_id = self::createAttachmentImage();
    }

    private function createAttachmentImage() {
        $filename      = 'wp-content/uploads/2015/03/dfi.jpg';
        $filetype      = wp_check_filetype( basename( $filename ), null );
        $wp_upload_dir = wp_upload_dir();
        $guid          = $wp_upload_dir['url'] . '/' . basename( $filename );

        // WordPress upload dir changes with year and month.
        // Make it same year and month for simplicity.
        $guid = str_replace( '/' . date( 'Y' ) . '/' . date( 'm' ) . '/', '/2015/03/', $guid );

        $attachment = [
            'guid' => $guid,
            'post_mime_type' => $filetype['type'],
            'post_title' => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
            'post_content' => '',
            'post_status' => 'inherit',
        ];

        $attachment_id = wp_insert_attachment( $attachment, $filename, $this->__post_id );

        // add attachment image alt
        add_post_meta( $attachment_id, '_wp_attachment_image_alt', 'Dynamic Featured Image' );

        // add link to image
        add_post_meta( $attachment_id, '_dfi_link_to_image', 'http://ankitpokhrel.com.np' );

        // set default post thumbnail
        set_post_thumbnail( $this->__post_id, $attachment_id );

        // insert featured images
        $dfiFeatured = [
            '/2015/03/dfi-150x150.jpg,/2015/03/dfi.jpg',
            '/2015/03/dfis-150x150.jpg,/2015/03/dfis.jpg',
            'http://example.org/wp-content/uploads/2015/03/dfi-pro-150x150.jpg,http://example.org/wp-content/uploads/2015/03/dfi-pro.jpg',
        ];
        add_post_meta( $this->__post_id, 'dfiFeatured', $dfiFeatured );

        return $attachment_id;
    }

    /**
     * @covers Dynamic_Featured_Image::__construct
     * @covers Dynamic_Featured_Image::load_plugin_textdomain
     */
    public function testConstructorAddsRequiredActionsAndFilters() {
        $this->assertEquals( 10, has_action( 'admin_enqueue_scripts', [ $this->_dfi, 'enqueue_admin_scripts' ] ) );
        $this->assertEquals( 10, has_action( 'add_meta_boxes', [ $this->_dfi, 'initialize_featured_box' ] ) );
        $this->assertEquals( 10, has_action( 'save_post', [ $this->_dfi, 'save_meta' ] ) );
        $this->assertEquals( 10, has_action( 'plugins_loaded', [ $this->_dfi, 'load_plugin_textdomain' ] ) );
        $this->assertEquals( 10, has_action( 'wp_ajax_dfiMetaBox_callback', [ $this->_dfi, 'ajax_callback' ] ) );

        $this->assertEquals( 10,
        has_filter( 'attachment_fields_to_edit', [ $this->_dfi, 'media_attachment_custom_fields' ] ) );
        $this->assertEquals( 10,
        has_filter( 'attachment_fields_to_save', [ $this->_dfi, 'media_attachment_custom_fields_save' ] ) );
    }

    /**
     * @covers Dynamic_Featured_Image::enqueue_admin_scripts
     */
    public function testEnqueueAdminScripts() {
        $this->_dfi->enqueue_admin_scripts();

        $this->assertTrue( wp_script_is( 'scripts-dfi' ) );
        $this->assertTrue( wp_style_is( 'style-dfi' ) );
        $this->assertTrue( wp_style_is( 'dashicons' ) );
    }

    /**
     * @coversNothing
     */
    public function testPluginProperties() {
        $this->assertTrue( $this->_pluginData['Name'] == 'Dynamic Featured Image' );
        $this->assertTrue( $this->_pluginData['TextDomain'] == 'dynamic-featured-image' );
        $this->assertTrue( $this->_pluginData['DomainPath'] == '/languages' );
    }

    /**
     * @covers Dynamic_Featured_Image::update_notice
     */
    public function testUpdateNotice() {
        $expectedOutput = '<div style="color:red; padding:7px 0;">ATTENTION! Please read the <a href="https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki" target="_blank">DOCUMENTATION</a> properly before update.</div>';

        $this->expectOutputString( $expectedOutput );
        $this->_dfi->update_notice();
    }

    /**
     * @covers Dynamic_Featured_Image::featured_meta_box
     * @covers Dynamic_Featured_Image::get_image_thumb
     * @covers Dynamic_Featured_Image::get_number_translation
     * @covers Dynamic_Featured_Image::get_featured_box
     */
    public function testFeaturedMetaBox() {
        $featured['args'] = [ '/2015/03/dfi-150x150.jpg', 3 ];
        $post             = get_post( $this->__post_id );

        $mock = $this->__mockBuilder
            ->setMethods( [ '_nonce_field' ] )
            ->getMock();

        $mock->expects( $this->once() )
             ->method( '_nonce_field' )
             ->with( 'dfi_fimageplug-2' )
             ->will( $this->returnValue( "<input type='hidden' id='dfi_fimageplug-2' name='dfi_fimageplug-2' value='c7ad4cc095' /><input type='hidden' name='_wp_http_referer' value='' />" ) );

        $expectedOutput = "<input type='hidden' id='dfi_fimageplug-2' name='dfi_fimageplug-2' value='c7ad4cc095' /><input type='hidden' name='_wp_http_referer' value='' /><a href='javascript:void(0)' class='dfiFeaturedImage hasFeaturedImage' title='Set Featured Image' data-post-id='" . $this->__post_id . "'><span class='dashicons dashicons-camera'></span></a><br/>
            <img src='' class='dfiImg '/>
            <div class='dfiLinks'>
                <a href='javascript:void(0)' data-id='2' data-id-local='3' class='dfiAddNew dashicons dashicons-plus' title='Add New'></a>
                <a href='javascript:void(0)' class='dfiRemove dashicons dashicons-minus' title='Remove'></a>
            </div>
            <div class='dfiClearFloat'></div>
            <input type='hidden' name='dfiFeatured[]' value='/2015/03/dfi-150x150.jpg'  class='dfiImageHolder' />";

        $this->expectOutputString( $expectedOutput );
        $mock->featured_meta_box( $post, $featured );
    }

    /**
     * @covers Dynamic_Featured_Image::featured_meta_box
     * @covers Dynamic_Featured_Image::get_image_thumb
     * @covers Dynamic_Featured_Image::get_number_translation
     * @covers Dynamic_Featured_Image::get_featured_box
     */
    public function testFeaturedMetaBoxWhenFeaturedIdIsGreaterThanNine() {
        $featured['args'] = [ '/2015/03/dfi-150x150.jpg', 13 ];
        $post             = get_post( $this->__post_id );

        $mock = $this->__mockBuilder
            ->setMethods( [ '_nonce_field' ] )
            ->getMock();

        $mock->expects( $this->once() )
             ->method( '_nonce_field' )
             ->with( 'dfi_fimageplug-12' )
             ->will( $this->returnValue( "<input type='hidden' id='dfi_fimageplug-12' name='dfi_fimageplug-12' value='c7ad4cc095' /><input type='hidden' name='_wp_http_referer' value='' />" ) );

        $expectedOutput = "<input type='hidden' id='dfi_fimageplug-12' name='dfi_fimageplug-12' value='c7ad4cc095' /><input type='hidden' name='_wp_http_referer' value='' /><a href='javascript:void(0)' class='dfiFeaturedImage hasFeaturedImage' title='Set Featured Image' data-post-id='" . $this->__post_id . "'><span class='dashicons dashicons-camera'></span></a><br/>
            <img src='' class='dfiImg '/>
            <div class='dfiLinks'>
                <a href='javascript:void(0)' data-id='12' data-id-local='13' class='dfiAddNew dashicons dashicons-plus' title='Add New'></a>
                <a href='javascript:void(0)' class='dfiRemove dashicons dashicons-minus' title='Remove'></a>
            </div>
            <div class='dfiClearFloat'></div>
            <input type='hidden' name='dfiFeatured[]' value='/2015/03/dfi-150x150.jpg'  class='dfiImageHolder' />";

        $this->expectOutputString( $expectedOutput );
        $mock->featured_meta_box( $post, $featured );
    }

    /**
     * @covers Dynamic_Featured_Image::get_image_url
     */
    public function testGetImageUrl() {
        $fullSizeImage = wp_get_attachment_image_src( $this->__attachment_id, 'full' );
        $this->assertEquals( $this->_dfi->get_image_url( $this->__attachment_id, 'full' ), $fullSizeImage[0] );
    }

    /**
     * @covers Dynamic_Featured_Image::get_image_thumb
     */
    public function testGetImageThumb() {
        $fullSizeImage = wp_get_attachment_image_src( $this->__attachment_id, 'full' );
        $thumbImage    = wp_get_attachment_image_src( $this->__attachment_id, 'thumbnail' );

        $mock = $this->__mockBuilder
            ->setMethods( [ 'get_image_id' ] )
            ->getMock();

        $mock->expects( $this->once() )
             ->method( 'get_image_id' )
             ->with( $fullSizeImage[0] )
             ->will( $this->returnValue( $this->__attachment_id ) );

        $this->assertEquals( $mock->get_image_thumb( $fullSizeImage[0], 'thumbnail' ), $thumbImage[0] );
    }

    /**
     * @covers Dynamic_Featured_Image::get_image_id
     * @covers Dynamic_Featured_Image::get_attachment_id
     */
    public function testGetImageId() {
        $fullSizeImage = wp_get_attachment_image_src( $this->__attachment_id, 'full' );

        $this->assertEquals( $this->_dfi->get_image_id( $fullSizeImage[0] ), $this->__attachment_id );
    }

    /**
     * @covers Dynamic_Featured_Image::get_image_id
     */
    public function testGetImageIdWithPostIdFromPostMetaTable() {
        $image = '2015/03/dfi.jpg';

        $mock = $this->__mockBuilder
            ->setMethods( [ '_get_attachment_id' ] )
            ->getMock();

        $mock->expects( $this->exactly( 2 ) )
             ->method( '_get_attachment_id' )
             ->will( $this->returnValue( null ) );

        add_post_meta( $this->__post_id, '_wp_attached_file', $image );

        $this->assertEquals( $mock->get_image_id( $image ), $this->__post_id );
        $this->assertEquals( $mock->get_image_id( '2015/03/dfis.jpg' ), null );
    }

    /**
     * @covers Dynamic_Featured_Image::get_image_title
     * @covers Dynamic_Featured_Image::execute_query
     */
    public function testGetImageTitle() {
        $post          = get_post( $this->__attachment_id );
        $fullSizeImage = wp_get_attachment_image_src( $this->__attachment_id, 'full' );

        $this->assertEquals( $this->_dfi->get_image_title( $fullSizeImage[0] ), $post->post_title );
    }

    /**
     * @covers Dynamic_Featured_Image::get_image_title_by_id
     * @covers Dynamic_Featured_Image::execute_query
     */
    public function testGetImageTitleById() {
        $post = get_post( $this->__attachment_id );
        $this->assertEquals( $this->_dfi->get_image_title_by_id( $this->__attachment_id ), $post->post_title );
    }

    /**
     * @covers Dynamic_Featured_Image::get_image_caption
     * @covers Dynamic_Featured_Image::execute_query
     */
    public function testGetImageCaption() {
        $post          = get_post( $this->__attachment_id );
        $fullSizeImage = wp_get_attachment_image_src( $this->__attachment_id, 'full' );

        $this->assertEquals( $this->_dfi->get_image_caption( $fullSizeImage[0] ), $post->post_excerpt );
    }

    /**
     * @covers Dynamic_Featured_Image::get_image_caption_by_id
     * @covers Dynamic_Featured_Image::execute_query
     */
    public function testGetImageCaptionById() {
        $post = get_post( $this->__attachment_id );
        $this->assertEquals( $this->_dfi->get_image_caption_by_id( $this->__attachment_id ), $post->post_excerpt );
    }

    /**
     * @covers Dynamic_Featured_Image::get_image_alt
     */
    public function testGetImageAlt() {
        $fullSizeImage = wp_get_attachment_image_src( $this->__attachment_id, 'full' );
        $alt           = get_post_meta( $this->__attachment_id, '_wp_attachment_image_alt', true );

        $this->assertEquals( $this->_dfi->get_image_alt( $fullSizeImage[0] ), $alt );
    }

    /**
     * @covers Dynamic_Featured_Image::get_image_alt_by_id
     */
    public function testGetImageAltById() {
        $alt = get_post_meta( $this->__attachment_id, '_wp_attachment_image_alt', true );
        $this->assertEquals( $this->_dfi->get_image_alt_by_id( $this->__attachment_id ), $alt );
    }

    /**
     * @covers Dynamic_Featured_Image::get_image_description
     * @covers Dynamic_Featured_Image::execute_query
     */
    public function testGetImageDescription() {
        $post          = get_post( $this->__attachment_id );
        $fullSizeImage = wp_get_attachment_image_src( $this->__attachment_id, 'full' );

        $this->assertEquals( $this->_dfi->get_image_description( $fullSizeImage[0] ), $post->post_content );
    }

    /**
     * @covers Dynamic_Featured_Image::get_image_description_by_id
     * @covers Dynamic_Featured_Image::execute_query
     */
    public function testGetImageDescriptionById() {
        $post = get_post( $this->__attachment_id );
        $this->assertEquals( $this->_dfi->get_image_description_by_id( $this->__attachment_id ), $post->post_content );
    }

    /**
     * @covers Dynamic_Featured_Image::get_link_to_image
     */
    public function testGetLinkToImage() {
        $this->assertEquals( $this->_dfi->get_link_to_image( $this->__attachment_id ), 'http://ankitpokhrel.com.np' );
    }

    /**
     * @covers Dynamic_Featured_Image::get_post_attachment_ids
     * @covers Dynamic_Featured_Image::get_image_id
     * @covers Dynamic_Featured_Image::separate
     */
    public function testGetPostAttachmentIds() {
        $expected = [ $this->__attachment_id, null, null ];
        $this->assertEquals( $expected, $this->_dfi->get_post_attachment_ids( $this->__post_id ) );
    }

    /**
     * @covers Dynamic_Featured_Image::get_nth_featured_image
     * @covers Dynamic_Featured_Image::get_featured_images
     */
    public function testGetNthFeaturedImage() {
        $featuredImage2 = [
            'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfi-150x150.jpg',
            'full' => 'http://example.org/wp-content/uploads/2015/03/dfi.jpg',
            'attachment_id' => $this->__attachment_id,
        ];

        $this->assertEquals( $featuredImage2, $this->_dfi->get_nth_featured_image( 2, $this->__post_id ) );

        // no attachment id
        $featuredImage3 = [
            'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfis-150x150.jpg',
            'full' => 'http://example.org/wp-content/uploads/2015/03/dfis.jpg',
            'attachment_id' => null,
        ];

        $this->assertEquals( $featuredImage3, $this->_dfi->get_nth_featured_image( 3, $this->__post_id ) );

        // full image url and no attachment id
        $featuredImage4 = [
            'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfi-pro-150x150.jpg',
            'full' => 'http://example.org/wp-content/uploads/2015/03/dfi-pro.jpg',
            'attachment_id' => null,
        ];

        $this->assertEquals( $featuredImage4, $this->_dfi->get_nth_featured_image( 4, $this->__post_id ) );

        // doesn't exist
        $this->assertNull( $this->_dfi->get_nth_featured_image( 5, $this->__post_id ) );

    }

    /**
     * @covers Dynamic_Featured_Image::get_nth_featured_image
     * @covers Dynamic_Featured_Image::get_featured_images
     */
    public function testGetNthFeaturedImageWhenPostIdIsNull() {
        $expected = [
            'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfi-150x150.jpg',
            'full' => 'http://example.org/wp-content/uploads/2015/03/dfi.jpg',
            'attachment_id' => $this->__attachment_id,
        ];

        $actual = null;

        query_posts( 'post_type=post' );
        if ( have_posts() ) {
            while ( have_posts() ) {
                the_post();
                $actual = $this->_dfi->get_nth_featured_image( 2 );
            }
        }

        $this->assertEquals( $expected, $actual );
    }

    /**
     * @covers Dynamic_Featured_Image::is_attached
     * @covers Dynamic_Featured_Image::get_post_attachment_ids
     */
    public function testIsAttached() {
        $this->assertTrue( $this->_dfi->is_attached( $this->__attachment_id, $this->__post_id ) );
        $this->assertFalse( $this->_dfi->is_attached( null, $this->__post_id ) );
    }

    /**
     * @covers Dynamic_Featured_Image::get_featured_images
     * @covers Dynamic_Featured_Image::get_real_upload_path
     * @covers Dynamic_Featured_Image::get_image_id
     * @covers Dynamic_Featured_Image::separate
     */
    public function testGetFeaturedImages() {
        $expected = [
            [
                'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfi-150x150.jpg',
                'full' => 'http://example.org/wp-content/uploads/2015/03/dfi.jpg',
                'attachment_id' => $this->__attachment_id,
            ],
            [
                'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfis-150x150.jpg',
                'full' => 'http://example.org/wp-content/uploads/2015/03/dfis.jpg',
                'attachment_id' => null,
            ],
            [
                'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfi-pro-150x150.jpg',
                'full' => 'http://example.org/wp-content/uploads/2015/03/dfi-pro.jpg',
                'attachment_id' => null,
            ],
        ];

        $actual = $this->_dfi->get_featured_images( $this->__post_id );

        $this->assertEquals( $expected, $actual );
    }

    /**
     * @covers Dynamic_Featured_Image::get_featured_images
     * @covers Dynamic_Featured_Image::get_real_upload_path
     * @covers Dynamic_Featured_Image::get_image_id
     * @covers Dynamic_Featured_Image::separate
     */
    public function testGetFeaturedImagesWhenPostIdIsNull() {
        $expected = [
            [
                'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfi-150x150.jpg',
                'full' => 'http://example.org/wp-content/uploads/2015/03/dfi.jpg',
                'attachment_id' => $this->__attachment_id,
            ],
            [
                'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfis-150x150.jpg',
                'full' => 'http://example.org/wp-content/uploads/2015/03/dfis.jpg',
                'attachment_id' => null,
            ],
            [
                'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfi-pro-150x150.jpg',
                'full' => 'http://example.org/wp-content/uploads/2015/03/dfi-pro.jpg',
                'attachment_id' => null,
            ],
        ];

        $actual = null;

        query_posts( 'post_type=post' );
        if ( have_posts() ) {
            while ( have_posts() ) {
                the_post();
                $actual = $this->_dfi->get_featured_images();
            }
        }

        $this->assertEquals( $expected, $actual );
    }

    /**
     * @covers Dynamic_Featured_Image::get_all_featured_images
     * @covers Dynamic_Featured_Image::get_featured_images
     */
    public function testGetAllFeaturedImages() {
        $expected = [
            [
                'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfi.jpg',
                'full' => 'http://example.org/wp-content/uploads/2015/03/dfi.jpg',
                'attachment_id' => $this->__attachment_id,
            ],
            [
                'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfi-150x150.jpg',
                'full' => 'http://example.org/wp-content/uploads/2015/03/dfi.jpg',
                'attachment_id' => $this->__attachment_id,
            ],
            [
                'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfis-150x150.jpg',
                'full' => 'http://example.org/wp-content/uploads/2015/03/dfis.jpg',
                'attachment_id' => null,
            ],
            [
                'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfi-pro-150x150.jpg',
                'full' => 'http://example.org/wp-content/uploads/2015/03/dfi-pro.jpg',
                'attachment_id' => null,
            ],
        ];

        $actual = $this->_dfi->get_all_featured_images( $this->__post_id );

        $this->assertEquals( $expected, $actual );
    }

    /**
     * @covers Dynamic_Featured_Image::get_all_featured_images
     * @covers Dynamic_Featured_Image::get_featured_images
     */
    public function testGetAllFeaturedImagesWhenPostIdIsNull() {
        $expected = [
            [
                'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfi.jpg',
                'full' => 'http://example.org/wp-content/uploads/2015/03/dfi.jpg',
                'attachment_id' => $this->__attachment_id,
            ],
            [
                'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfi-150x150.jpg',
                'full' => 'http://example.org/wp-content/uploads/2015/03/dfi.jpg',
                'attachment_id' => $this->__attachment_id,
            ],
            [
                'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfis-150x150.jpg',
                'full' => 'http://example.org/wp-content/uploads/2015/03/dfis.jpg',
                'attachment_id' => null,
            ],
            [
                'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfi-pro-150x150.jpg',
                'full' => 'http://example.org/wp-content/uploads/2015/03/dfi-pro.jpg',
                'attachment_id' => null,
            ],
        ];

        $actual = null;

        query_posts( 'post_type=post' );
        if ( have_posts() ) {
            while ( have_posts() ) {
                the_post();
                $actual = $this->_dfi->get_all_featured_images();
            }
        }

        $this->assertEquals( $expected, $actual );
    }

    /**
     * @covers Dynamic_Featured_Image::add_metabox_classes
     */
    public function testAddMetaBoxClasses() {
        $classes[] = 'metabox';
        $expected  = [ 'metabox', 'featured-meta-box' ];
        $actual    = $this->_dfi->add_metabox_classes( $classes );

        $this->assertEquals( $expected, $actual );
    }

    /**
     * @covers Dynamic_Featured_Image::media_attachment_custom_fields
     */
    public function testMediaAttachmentCustomFields() {
        $post       = get_post( $this->__post_id );
        $formFields = $this->_dfi->media_attachment_custom_fields( [], $post );

        $this->assertArrayHasKey( 'dfi-link-to-image', $formFields );
        $this->assertArrayHasKey( 'label', $formFields['dfi-link-to-image'] );
        $this->assertArrayHasKey( 'input', $formFields['dfi-link-to-image'] );
        $this->assertArrayHasKey( 'value', $formFields['dfi-link-to-image'] );

        $this->assertEquals( $formFields['dfi-link-to-image']['input'], 'text' );
    }

    /**
     * @covers Dynamic_Featured_Image::media_attachment_custom_fields_save
     */
    public function testMediaAttachmentCustomFieldsSave() {
        $post['ID']                      = $this->__post_id;
        $attachment['dfi-link-to-image'] = 'http://ankitpokhrel.com.np';

        $this->_dfi->media_attachment_custom_fields_save( $post, $attachment );

        $this->assertEquals( $attachment['dfi-link-to-image'],
        get_post_meta( $this->__post_id, '_dfi_link_to_image', true ) );
    }

    /**
     * @covers Dynamic_Featured_Image::save_meta
     * @covers Dynamic_Featured_Image::get_featured_images
     */
    public function testSaveMeta() {
        $mock = $this->__mockBuilder
            ->setMethods( [ '_verify_nonces' ] )
            ->getMock();

        $mock->expects( $this->once() )
             ->method( '_verify_nonces' )
             ->will( $this->returnValue( true ) );

        $user_id = $this->factory->user->create( [
            'role' => 'administrator',
        ] );
        wp_set_current_user( $user_id );

        $_POST['dfiFeatured'] = [ '/2015/03/featured-150x150.jpg,/2015/03/featured.jpg' ];
        $mock->save_meta( $this->__post_id );

        $expected = [
            [
                'thumb' => 'http://example.org/wp-content/uploads/2015/03/featured-150x150.jpg',
                'full' => 'http://example.org/wp-content/uploads/2015/03/featured.jpg',
                'attachment_id' => null,
            ],
        ];

        $actual = $this->_dfi->get_featured_images( $this->__post_id );

        $this->assertEquals( $expected, $actual );
    }

    /**
     * @covers Dynamic_Featured_Image::save_meta
     */
    public function testSaveMetaWhenDoingAutosave() {
        define( 'DOING_AUTOSAVE', true );
        $this->assertFalse( $this->_dfi->save_meta( $this->__post_id ) );
    }

    public function tearDown() {
        unset( $this->__mockBuilder );
        unset( $this->__post_id );
        unset( $this->__attachment_id );

        unset( $this->_dfi );
        unset( $this->_pluginData );
    }
}
