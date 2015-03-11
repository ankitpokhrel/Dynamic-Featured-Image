<?php

class DynamicFeaturedImageTest extends WP_UnitTestCase {

	private $__mockBuilder = null;
	private $__post_id = null;
	private $__attachment_id = null;

	protected $_dfi = null;
	protected $_pluginData = null;

	public function setUp() 
	{
		parent::setUp();

		$this->__mockBuilder = $this->getMockBuilder( 'Dynamic_Featured_Image' );

		global $dynamic_featured_image;
		$this->_dfi = $dynamic_featured_image;

		$this->_pluginData = get_plugin_data( dirname(dirname(__FILE__)) . '/dynamic-featured-image.php' );

		$this->__post_id = $this->factory->post->create( array( 'post_title' => 'Dynamic Featured Image WordPress Plugin' ) );
		$this->__attachment_id = self::createAttachmentImage();
	}

	private function createAttachmentImage()
	{
		$filename = 'wp-content/uploads/2015/03/dfi.jpg';
		$filetype = wp_check_filetype( basename( $filename ), null );
		$wp_upload_dir = wp_upload_dir();

		$attachment = array(
			'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ), 
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

		$attachment_id = wp_insert_attachment( $attachment, $filename, $this->__post_id );

		//add attachment image alt
		add_post_meta( $attachment_id, '_wp_attachment_image_alt', 'Dynamic Featured Image' );

		//add link to image
		add_post_meta( $attachment_id, '_dfi_link_to_image', 'http://ankitpokhrel.com.np' );

		//set default post thumbnail
		set_post_thumbnail( $this->__post_id, $attachment_id );

		//insert featured images
		$dfiFeatured = array(
				  '/2015/03/dfi-150x150.jpg,/2015/03/dfi.jpg',
				  '/2015/03/dfis-150x150.jpg,/2015/03/dfis.jpg'
				);
		add_post_meta( $this->__post_id, 'dfiFeatured', $dfiFeatured );

		return $attachment_id;
	}

	public function testConstructorAddsRequiredActionsAndFilters() 
	{
		$this->assertEquals( 10, has_action( 'admin_enqueue_scripts', array( $this->_dfi, 'enqueue_admin_scripts' ) ) );
		$this->assertEquals( 10, has_action( 'add_meta_boxes', array( $this->_dfi, 'initialize_featured_box' ) ) );
		$this->assertEquals( 10, has_action( 'save_post', array( $this->_dfi, 'save_meta' ) ) );
		$this->assertEquals( 10, has_action( 'plugins_loaded', array( $this->_dfi, 'load_plugin_textdomain' ) ) );
		$this->assertEquals( 10, has_action( 'wp_ajax_dfiMetaBox_callback', array( $this->_dfi, 'ajax_callback' ) ) );
		
		$this->assertEquals( 10, has_filter( 'attachment_fields_to_edit', array( $this->_dfi, 'media_attachment_custom_fields' ) ) );
		$this->assertEquals( 10, has_filter( 'attachment_fields_to_save', array( $this->_dfi, 'media_attachment_custom_fields_save' ) ) );
	}

	public function testEnqueueAdminScripts()
	{
		$this->_dfi->enqueue_admin_scripts();

		$this->assertTrue( wp_script_is('scripts-dfi') );
		$this->assertTrue( wp_style_is('style-dfi') );
		$this->assertTrue( wp_style_is('dashicons') );
	}

	public function testPluginProperties()
	{
		$this->assertTrue( $this->_pluginData['Name'] == 'Dynamic Featured Image' );
		$this->assertTrue( $this->_pluginData['TextDomain'] == 'dynamic-featured-image' );
		$this->assertTrue( $this->_pluginData['DomainPath'] == '/languages' );
	}

	public function testGetImageUrl()
	{		
		$fullSizeImage = wp_get_attachment_image_src( $this->__attachment_id, 'full' );
		$this->assertEquals( $this->_dfi->get_image_url( $this->__attachment_id, 'full' ), $fullSizeImage[0] );
	}

	public function testGetImageThumb()
	{		
		$fullSizeImage = wp_get_attachment_image_src( $this->__attachment_id, 'full' );
		$thumbImage = wp_get_attachment_image_src( $this->__attachment_id, 'thumbnail' );

		$mock = $this->__mockBuilder
					->setMethods( array('get_image_id') )
					->getMock();

		$mock->expects( $this->once() )
			->method('get_image_id')
			->with( $fullSizeImage[0] )
			->will( $this->returnValue( $this->__attachment_id ) );

		$this->assertEquals( $mock->get_image_thumb( $fullSizeImage[0], 'thumbnail' ), $thumbImage[0] );
	}

	public function testGetImageId()
	{
		$fullSizeImage = wp_get_attachment_image_src( $this->__attachment_id, 'full');

		$mock = $this->__mockBuilder
					->setMethods( array('_get_attachment_id') )
					->getMock();

		$mock->expects( $this->once() )
			->method('_get_attachment_id')
			->with( $fullSizeImage[0] )
			->will( $this->returnValue( $this->__attachment_id ) );

		$this->assertEquals( $mock->get_image_id($fullSizeImage[0]), $this->__attachment_id );
	}

	public function testGetImageTitle()
	{
		$post = get_post($this->__attachment_id);
		$fullSizeImage = wp_get_attachment_image_src( $this->__attachment_id, 'full' );

		$this->assertEquals( $this->_dfi->get_image_title($fullSizeImage[0]), $post->post_title );
	}

	public function testGetImageTitleById()
	{
		$post = get_post($this->__attachment_id);

		$this->assertEquals( $this->_dfi->get_image_title_by_id($this->__attachment_id), $post->post_title );
	}

	public function testGetImageCaption()
	{
		$post = get_post($this->__attachment_id);
		$fullSizeImage = wp_get_attachment_image_src( $this->__attachment_id, 'full');

		$this->assertEquals( $this->_dfi->get_image_caption($fullSizeImage[0]), $post->post_excerpt );
	}

	public function testGetImageCaptionById()
	{
		$post = get_post($this->__attachment_id);

		$this->assertEquals( $this->_dfi->get_image_caption_by_id($this->__attachment_id), $post->post_excerpt );
	}

	public function testGetImageAlt()
	{
		$fullSizeImage = wp_get_attachment_image_src( $this->__attachment_id, 'full');
		$alt = get_post_meta($this->__attachment_id, '_wp_attachment_image_alt', true);

		$this->assertEquals( $this->_dfi->get_image_alt($fullSizeImage[0]), $alt );
	}

	public function testGetImageAltById()
	{
		$alt = get_post_meta($this->__attachment_id, '_wp_attachment_image_alt', true);

		$this->assertEquals( $this->_dfi->get_image_alt_by_id($this->__attachment_id), $alt );
	}

	public function testGetImageDescription()
	{
		$post = get_post($this->__attachment_id);
		$fullSizeImage = wp_get_attachment_image_src( $this->__attachment_id, 'full');

		$this->assertEquals( $this->_dfi->get_image_description($fullSizeImage[0]), $post->post_content );
	}

	public function testGetImageDescriptionById()
	{
		$post = get_post($this->__attachment_id);

		$this->assertEquals( $this->_dfi->get_image_description_by_id($this->__attachment_id), $post->post_content );
	}

	public function testGetLinkToImage()
	{
		$this->assertEquals( $this->_dfi->get_link_to_image($this->__attachment_id), 'http://ankitpokhrel.com.np' );
	}

	public function testGetPostAttachmentIds()
	{
		$expected = array($this->__attachment_id, null);

		$this->assertEquals( $expected, $this->_dfi->get_post_attachment_ids($this->__post_id) );
	}

	public function testGetNthFeaturedImage()
	{
		$featuredImage2 = array(
				    'thumb' => "http://example.org/wp-content/uploads/2015/03/dfi-150x150.jpg",
				    'full' => "http://example.org/wp-content/uploads/2015/03/dfi.jpg",
				    'attachment_id' => $this->__attachment_id
				  );

		$this->assertEquals( $featuredImage2, $this->_dfi->get_nth_featured_image(2, $this->__post_id) );

		$featuredImage3 = array(
				    'thumb' => "http://example.org/wp-content/uploads/2015/03/dfis-150x150.jpg",
				    'full' => "http://example.org/wp-content/uploads/2015/03/dfis.jpg",
				    'attachment_id' => null
				  );

		$this->assertEquals( $featuredImage3, $this->_dfi->get_nth_featured_image(3, $this->__post_id) );

		$this->assertNull( $this->_dfi->get_nth_featured_image(4, $this->__post_id) );
	}

	public function testIsAttached()
	{
		$this->assertTrue( $this->_dfi->is_attached($this->__attachment_id, $this->__post_id) );
		$this->assertFalse( $this->_dfi->is_attached(null, $this->__post_id) );
	}

	public function testGetFeaturedImages()
	{
		$expected = array(
				array(
				  'thumb' => "http://example.org/wp-content/uploads/2015/03/dfi-150x150.jpg",
				  'full' => "http://example.org/wp-content/uploads/2015/03/dfi.jpg",
				  'attachment_id' => $this->__attachment_id
				),
				array(
				  'thumb' => "http://example.org/wp-content/uploads/2015/03/dfis-150x150.jpg",
				  'full' => "http://example.org/wp-content/uploads/2015/03/dfis.jpg",
				  'attachment_id' => null
				)
			    );

		$actual = $this->_dfi->get_featured_images( $this->__post_id );

		$this->assertEquals( $expected, $actual );
	}

	public function testGetAllFeaturedImages()
	{
		$expected = array(
				array(
				  'thumb' => "http://example.org/wp-content/uploads/2015/03/dfi.jpg",
				  'full' => "http://example.org/wp-content/uploads/2015/03/dfi.jpg",
				  'attachment_id' => $this->__attachment_id
				),
				array(
				  'thumb' => "http://example.org/wp-content/uploads/2015/03/dfi-150x150.jpg",
				  'full' => "http://example.org/wp-content/uploads/2015/03/dfi.jpg",
				  'attachment_id' => $this->__attachment_id
				),
				array(
				  'thumb' => "http://example.org/wp-content/uploads/2015/03/dfis-150x150.jpg",
				  'full' => "http://example.org/wp-content/uploads/2015/03/dfis.jpg",
				  'attachment_id' => null
				)
			    );

		$actual = $this->_dfi->get_all_featured_images( $this->__post_id );

		$this->assertEquals( $expected, $actual );
	}

	public function tearDown() 
	{
		unset($this->__mockBuilder);
		unset($this->__post_id);
		unset($this->__attachment_id);
		
		unset($this->_dfi);
		unset($this->_pluginData);
	}
}
