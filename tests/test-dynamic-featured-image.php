<?php

/**
 * Tests for Dynamic Featured Image Plugin
 *
 * @coversDefaultClass Dynamic_Featured_Image
 */
class Dynamic_Featured_Image_Test extends WP_UnitTestCase {

    protected $mock_builder = null;
    protected $post_id = null;
    protected $attachment_id = null;
    protected $dfi = null;
    protected $plugin_data = null;

    public function setUp() {
        parent::setUp();

        $this->mock_builder = $this->getMockBuilder( 'Dynamic_Featured_Image' );

        $this->dfi = new Dynamic_Featured_Image;

        $this->plugin_data = get_plugin_data( dirname( dirname( __FILE__ ) ) . '/dynamic-featured-image.php' );

        $this->post_id = $this->factory->post->create( [
            'post_title' => 'Dynamic Featured Image WordPress Plugin',
        ] );

        $this->attachment_id = self::create_attachment_image();
    }

    protected function create_attachment_image() {
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

        $attachment_id = wp_insert_attachment( $attachment, $filename, $this->post_id );

        // add attachment image alt.
        add_post_meta( $attachment_id, '_wp_attachment_image_alt', 'Dynamic Featured Image' );

        // add link to image.
        add_post_meta( $attachment_id, '_dfi_link_to_image', 'https://ankitpokhrel.com' );

        // set default post thumbnail.
        set_post_thumbnail( $this->post_id, $attachment_id );

        // insert featured images.
        $dfiFeatured = [
            '/2015/03/dfi-150x150.jpg,/2015/03/dfi.jpg',
            '/2015/03/dfis-150x150.jpg,/2015/03/dfis.jpg',
            'http://example.org/wp-content/uploads/2015/03/dfi-pro-150x150.jpg,http://example.org/wp-content/uploads/2015/03/dfi-pro.jpg',
        ];

        add_post_meta( $this->post_id, 'dfiFeatured', $dfiFeatured );

        return $attachment_id;
    }

    /**
     * @test
     *
     * @covers ::__construct
     * @covers ::load_plugin_textdomain
     */
    public function it_adds_required_actions_and_filters() {
        $this->assertEquals( 10, has_action( 'admin_enqueue_scripts', [ $this->dfi, 'enqueue_admin_scripts' ] ) );
        $this->assertEquals( 10, has_action( 'add_meta_boxes', [ $this->dfi, 'initialize_featured_box' ] ) );
        $this->assertEquals( 10, has_action( 'save_post', [ $this->dfi, 'save_meta' ] ) );
        $this->assertEquals( 10, has_action( 'plugins_loaded', [ $this->dfi, 'load_plugin_textdomain' ] ) );
        $this->assertEquals( 10, has_action( 'wp_ajax_dfiMetaBox_callback', [ $this->dfi, 'ajax_callback' ] ) );
        $this->assertEquals( 10, has_filter( 'attachment_fields_to_edit', [ $this->dfi, 'media_attachment_custom_fields' ] ) );
        $this->assertEquals( 10, has_filter( 'attachment_fields_to_save', [ $this->dfi, 'media_attachment_custom_fields_save' ] ) );
    }

    /**
     * @test
     *
     * @covers ::enqueue_admin_scripts
     */
    public function it_enqueue_admin_scripts() {
        $this->dfi->enqueue_admin_scripts();

        $this->assertTrue( wp_script_is( 'scripts-dfi' ) );
        $this->assertTrue( wp_style_is( 'style-dfi' ) );
    }

    /**
     * @test
     *
     * @coversNothing
     */
    public function it_sets_plugin_properties() {
        $this->assertTrue( $this->plugin_data['Name'] == 'Dynamic Featured Image' );
        $this->assertTrue( $this->plugin_data['TextDomain'] == 'dynamic-featured-image' );
        $this->assertTrue( $this->plugin_data['DomainPath'] == '/languages' );
    }

    /**
     * @test
     *
     * @covers ::update_notice
     */
    public function it_sets_update_notice() {
        $expectedOutput = '<span style="color: red; padding: 7px 0; display: block">ATTENTION! Please read the <a href="https://github.com/ankitpokhrel/Dynamic-Featured-Image/wiki/" target="_blank">DOCUMENTATION</a> properly before update.</span>';

        $this->expectOutputString( $expectedOutput );
        $this->dfi->update_notice();
    }

    /**
     * @test
     *
     * @covers ::featured_meta_box
     * @covers ::get_image_thumb
     * @covers ::get_number_translation
     * @covers ::get_featured_box
     */
    public function it_makes_featured_meta_box() {
        $featured['args'] = [ '/2015/03/dfi-150x150.jpg', 3 ];
        $post             = get_post( $this->post_id );

        $mock = $this->mock_builder
            ->setMethods( [ 'nonce_field' ] )
            ->getMock();

        $mock->expects( $this->once() )
             ->method( 'nonce_field' )
             ->with( 'dfi_fimageplug-2' )
             ->will( $this->returnValue( "<input type='hidden' id='dfi_fimageplug-2' name='dfi_fimageplug-2' value='c7ad4cc095' /><input type='hidden' name='_wp_http_referer' value='' />" ) );

        $expectedOutput = "<input type='hidden' id='dfi_fimageplug-2' name='dfi_fimageplug-2' value='c7ad4cc095' /><input type='hidden' name='_wp_http_referer' value='' /><a href='javascript:void(0)' class='dfiFeaturedImage hasFeaturedImage' title='Set Featured Image' data-post-id='" . $this->post_id . "'><span class='dashicons dashicons-camera'></span></a><br/>
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
     * @test
     *
     * @covers ::featured_meta_box
     * @covers ::get_image_thumb
     * @covers ::get_number_translation
     * @covers ::get_featured_box
     */
    public function it_makes_meta_box_when_featured_id_is_greater_than_nine() {
        $featured['args'] = [ '/2015/03/dfi-150x150.jpg', 13 ];
        $post             = get_post( $this->post_id );

        $mock = $this->mock_builder
            ->setMethods( [ 'nonce_field' ] )
            ->getMock();

        $mock->expects( $this->once() )
             ->method( 'nonce_field' )
             ->with( 'dfi_fimageplug-12' )
             ->will( $this->returnValue( "<input type='hidden' id='dfi_fimageplug-12' name='dfi_fimageplug-12' value='c7ad4cc095' /><input type='hidden' name='_wp_http_referer' value='' />" ) );

        $expectedOutput = "<input type='hidden' id='dfi_fimageplug-12' name='dfi_fimageplug-12' value='c7ad4cc095' /><input type='hidden' name='_wp_http_referer' value='' /><a href='javascript:void(0)' class='dfiFeaturedImage hasFeaturedImage' title='Set Featured Image' data-post-id='" . $this->post_id . "'><span class='dashicons dashicons-camera'></span></a><br/>
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
     * @test
     *
     * @covers ::get_image_url
     */
    public function it_gets_image_url() {
        $fullSizeImage = wp_get_attachment_image_src( $this->attachment_id, 'full' );

        $this->assertEquals( $this->dfi->get_image_url( $this->attachment_id, 'full' ), $fullSizeImage[0] );
    }

    /**
     * @test
     *
     * @covers ::get_image_thumb
     */
    public function it_gets_image_thumb() {
        $fullSizeImage = wp_get_attachment_image_src( $this->attachment_id, 'full' );
        $thumbImage    = wp_get_attachment_image_src( $this->attachment_id, 'thumbnail' );

        $mock = $this->mock_builder
            ->setMethods( [ 'get_image_id' ] )
            ->getMock();

        $mock->expects( $this->once() )
             ->method( 'get_image_id' )
             ->with( $fullSizeImage[0] )
             ->will( $this->returnValue( $this->attachment_id ) );

        $this->assertEquals( $mock->get_image_thumb( $fullSizeImage[0], 'thumbnail' ), $thumbImage[0] );
    }

    /**
     * @test
     *
     * @covers ::get_image_id
     * @covers ::get_attachment_id
     */
    public function it_gets_image_id() {
        $fullSizeImage = wp_get_attachment_image_src( $this->attachment_id, 'full' );

        $this->assertEquals( $this->dfi->get_image_id( $fullSizeImage[0] ), $this->attachment_id );
    }

    /**
     * @test
     *
     * @covers ::get_image_id
     */
    public function it_gets_image_id_with_post_id_from_posts_meta_table() {
        $image = '2015/03/dfi.jpg';

        $mock = $this->mock_builder
            ->setMethods( [ 'get_attachment_id' ] )
            ->getMock();

        $mock->expects( $this->exactly( 2 ) )
             ->method( 'get_attachment_id' )
             ->will( $this->returnValue( null ) );

        add_post_meta( $this->post_id, '_wp_attached_file', $image );

        $this->assertEquals( $mock->get_image_id( $image ), $this->post_id );
        $this->assertEquals( $mock->get_image_id( '2015/03/dfis.jpg' ), null );
    }

    /**
     * @test
     *
     * @covers ::get_image_title
     * @covers ::execute_query
     */
    public function it_gets_image_title() {
        $post          = get_post( $this->attachment_id );
        $fullSizeImage = wp_get_attachment_image_src( $this->attachment_id, 'full' );

        $this->assertEquals( $this->dfi->get_image_title( $fullSizeImage[0] ), $post->post_title );
    }

    /**
     * @test
     *
     * @covers ::get_image_title_by_id
     * @covers ::execute_query
     */
    public function it_gets_image_title_by_id() {
        $post = get_post( $this->attachment_id );
        $this->assertEquals( $this->dfi->get_image_title_by_id( $this->attachment_id ), $post->post_title );
    }

    /**
     * @test
     *
     * @covers ::get_image_caption
     * @covers ::execute_query
     */
    public function it_gets_image_caption() {
        $post          = get_post( $this->attachment_id );
        $fullSizeImage = wp_get_attachment_image_src( $this->attachment_id, 'full' );

        $this->assertEquals( $this->dfi->get_image_caption( $fullSizeImage[0] ), $post->post_excerpt );
    }

    /**
     * @test
     *
     * @covers ::get_image_caption_by_id
     * @covers ::execute_query
     */
    public function it_gets_image_caption_by_id() {
        $post = get_post( $this->attachment_id );
        $this->assertEquals( $this->dfi->get_image_caption_by_id( $this->attachment_id ), $post->post_excerpt );
    }

    /**
     * @test
     *
     * @covers ::get_image_alt
     */
    public function it_gets_image_alt() {
        $fullSizeImage = wp_get_attachment_image_src( $this->attachment_id, 'full' );
        $alt           = get_post_meta( $this->attachment_id, '_wp_attachment_image_alt', true );

        $this->assertEquals( $this->dfi->get_image_alt( $fullSizeImage[0] ), $alt );
    }

    /**
     * @test
     *
     * @covers ::get_image_alt_by_id
     */
    public function it_gets_image_alt_by_id() {
        $alt = get_post_meta( $this->attachment_id, '_wp_attachment_image_alt', true );

        $this->assertEquals( $this->dfi->get_image_alt_by_id( $this->attachment_id ), $alt );
    }

    /**
     * @test
     *
     * @covers ::get_image_description
     * @covers ::execute_query
     */
    public function it_gets_image_description() {
        $post          = get_post( $this->attachment_id );
        $fullSizeImage = wp_get_attachment_image_src( $this->attachment_id, 'full' );

        $this->assertEquals( $this->dfi->get_image_description( $fullSizeImage[0] ), $post->post_content );
    }

    /**
     * @test
     *
     * @covers ::get_image_description_by_id
     * @covers ::execute_query
     */
    public function it_gets_image_description_by_id() {
        $post = get_post( $this->attachment_id );

        $this->assertEquals( $this->dfi->get_image_description_by_id( $this->attachment_id ), $post->post_content );
    }

    /**
     * @test
     *
     * @covers ::get_link_to_image
     */
    public function it_gets_link_to_image() {
        $this->assertEquals( $this->dfi->get_link_to_image( $this->attachment_id ), 'https://ankitpokhrel.com' );
    }

    /**
     * @test
     *
     * @covers ::get_post_attachment_ids
     * @covers ::get_image_id
     * @covers ::separate
     */
    public function it_gets_post_attachment_ids() {
        $expected = [ $this->attachment_id, null, null ];

        $this->assertEquals( $expected, $this->dfi->get_post_attachment_ids( $this->post_id ) );
    }

    /**
     * @test
     *
     * @covers ::get_nth_featured_image
     * @covers ::get_featured_images
     * @covers ::get_real_post_id
     */
    public function it_gets_nth_featured_image() {
        $featuredImage2 = [
            'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfi-150x150.jpg',
            'full' => 'http://example.org/wp-content/uploads/2015/03/dfi.jpg',
            'attachment_id' => $this->attachment_id,
        ];

        $this->assertEquals( $featuredImage2, $this->dfi->get_nth_featured_image( 2, $this->post_id ) );

        // no attachment id.
        $featuredImage3 = [
            'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfis-150x150.jpg',
            'full' => 'http://example.org/wp-content/uploads/2015/03/dfis.jpg',
            'attachment_id' => null,
        ];

        $this->assertEquals( $featuredImage3, $this->dfi->get_nth_featured_image( 3, $this->post_id ) );

        // full image url and no attachment id.
        $featuredImage4 = [
            'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfi-pro-150x150.jpg',
            'full' => 'http://example.org/wp-content/uploads/2015/03/dfi-pro.jpg',
            'attachment_id' => null,
        ];

        $this->assertEquals( $featuredImage4, $this->dfi->get_nth_featured_image( 4, $this->post_id ) );

        // doesn't exist.
        $this->assertNull( $this->dfi->get_nth_featured_image( 5, $this->post_id ) );

    }

    /**
     * @test
     *
     * @covers ::get_nth_featured_image
     * @covers ::get_featured_images
     * @covers ::get_real_post_id
     */
    public function it_gets_nth_featured_image_when_post_id_is_null() {
        $expected = [
            'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfi-150x150.jpg',
            'full' => 'http://example.org/wp-content/uploads/2015/03/dfi.jpg',
            'attachment_id' => $this->attachment_id,
        ];

        $actual = null;

        query_posts( 'post_type=post' );
        if ( have_posts() ) {
            while ( have_posts() ) {
                the_post();
                $actual = $this->dfi->get_nth_featured_image( 2 );
            }
        }

        $this->assertEquals( $expected, $actual );
    }

    /**
     * @test
     *
     * @covers ::is_attached
     * @covers ::get_post_attachment_ids
     */
    public function it_checks_if_image_is_attached() {
        $this->assertTrue( $this->dfi->is_attached( $this->attachment_id, $this->post_id ) );
        $this->assertFalse( $this->dfi->is_attached( null, $this->post_id ) );
    }

    /**
     * @test
     *
     * @covers ::get_featured_images
     * @covers ::get_real_upload_path
     * @covers ::get_image_id
     * @covers ::separate
     * @covers ::get_real_post_id
     */
    public function it_gets_featured_images() {
        $expected = [
            [
                'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfi-150x150.jpg',
                'full' => 'http://example.org/wp-content/uploads/2015/03/dfi.jpg',
                'attachment_id' => $this->attachment_id,
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

        $actual = $this->dfi->get_featured_images( $this->post_id );

        $this->assertEquals( $expected, $actual );
    }

    /**
     * @test
     *
     * @covers ::get_featured_images
     * @covers ::get_real_upload_path
     * @covers ::get_image_id
     * @covers ::separate
     * @covers ::get_real_post_id
     */
    public function it_gets_featured_images_when_post_id_is_null() {
        $expected = [
            [
                'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfi-150x150.jpg',
                'full' => 'http://example.org/wp-content/uploads/2015/03/dfi.jpg',
                'attachment_id' => $this->attachment_id,
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
                $actual = $this->dfi->get_featured_images();
            }
        }

        $this->assertEquals( $expected, $actual );
    }

    /**
     * @test
     *
     * @covers ::get_all_featured_images
     * @covers ::get_featured_images
     * @covers ::get_real_post_id
     */
    public function it_gets_all_featured_images() {
        $expected = [
            [
                'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfi.jpg',
                'full' => 'http://example.org/wp-content/uploads/2015/03/dfi.jpg',
                'attachment_id' => $this->attachment_id,
            ],
            [
                'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfi-150x150.jpg',
                'full' => 'http://example.org/wp-content/uploads/2015/03/dfi.jpg',
                'attachment_id' => $this->attachment_id,
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

        $actual = $this->dfi->get_all_featured_images( $this->post_id );

        $this->assertEquals( $expected, $actual );
    }

    /**
     * @test
     *
     * @covers ::get_all_featured_images
     * @covers ::get_featured_images
     * @covers ::get_real_post_id
     */
    public function it_gets_all_featured_images_when_post_id_is_null() {
        $expected = [
            [
                'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfi.jpg',
                'full' => 'http://example.org/wp-content/uploads/2015/03/dfi.jpg',
                'attachment_id' => $this->attachment_id,
            ],
            [
                'thumb' => 'http://example.org/wp-content/uploads/2015/03/dfi-150x150.jpg',
                'full' => 'http://example.org/wp-content/uploads/2015/03/dfi.jpg',
                'attachment_id' => $this->attachment_id,
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
                $actual = $this->dfi->get_all_featured_images();
            }
        }

        $this->assertEquals( $expected, $actual );
    }

    /**
     * @test
     *
     * @covers ::add_metabox_classes
     */
    public function it_adds_metabox_classes() {
        $classes[] = 'metabox';
        $expected  = [ 'metabox', 'featured-meta-box' ];
        $actual    = $this->dfi->add_metabox_classes( $classes );

        $this->assertEquals( $expected, $actual );
    }

    /**
     * @test
     *
     * @covers ::media_attachment_custom_fields
     */
    public function it_sets_media_attachment_custom_fields() {
        $post       = get_post( $this->post_id );
        $formFields = $this->dfi->media_attachment_custom_fields( [], $post );

        $this->assertArrayHasKey( 'dfi-link-to-image', $formFields );
        $this->assertArrayHasKey( 'label', $formFields['dfi-link-to-image'] );
        $this->assertArrayHasKey( 'input', $formFields['dfi-link-to-image'] );
        $this->assertArrayHasKey( 'value', $formFields['dfi-link-to-image'] );

        $this->assertEquals( $formFields['dfi-link-to-image']['input'], 'text' );
    }

    /**
     * @test
     *
     * @covers ::media_attachment_custom_fields_save
     */
    public function it_saves_media_attachment_custom_fields() {
        $post['ID']                      = $this->post_id;
        $attachment['dfi-link-to-image'] = 'https://ankitpokhrel.com';

        $this->dfi->media_attachment_custom_fields_save( $post, $attachment );

        $this->assertEquals( $attachment['dfi-link-to-image'],
        get_post_meta( $this->post_id, '_dfi_link_to_image', true ) );
    }

    /**
     * @test
     *
     * @covers ::save_meta
     * @covers ::get_featured_images
     * @covers ::sanitize_array
     */
    public function it_saves_meta() {
        $mock = $this->mock_builder
            ->setMethods( [ 'verify_nonces' ] )
            ->getMock();

        $mock->expects( $this->once() )
             ->method( 'verify_nonces' )
             ->will( $this->returnValue( true ) );

        $user_id = $this->factory->user->create( [
            'role' => 'administrator',
        ] );
        wp_set_current_user( $user_id );

        $_POST['dfiFeatured'] = [ '/2015/03/featured-150x150.jpg,/2015/03/featured.jpg' ];
        $mock->save_meta( $this->post_id );

        $expected = [
            [
                'thumb' => 'http://example.org/wp-content/uploads/2015/03/featured-150x150.jpg',
                'full' => 'http://example.org/wp-content/uploads/2015/03/featured.jpg',
                'attachment_id' => null,
            ],
        ];

        $actual = $this->dfi->get_featured_images( $this->post_id );

        $this->assertEquals( $expected, $actual );
    }

    /**
     * @test
     *
     * @covers ::save_meta
     */
    public function it_saves_meta_when_doing_autosave() {
        define( 'DOING_AUTOSAVE', true );

        $this->assertFalse( $this->dfi->save_meta( $this->post_id ) );
    }

    public function tearDown() {
        parent::tearDown();

        unset( $this->mock_builder );
        unset( $this->post_id );
        unset( $this->attachment_id );

        unset( $this->dfi );
        unset( $this->plugin_data );
    }
}
