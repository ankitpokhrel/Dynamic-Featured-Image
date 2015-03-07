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
		$filename = 'wp-content/uploads/2013/03/dfi.jpg';
		$filetype = wp_check_filetype( basename( $filename ), null );
		$wp_upload_dir = wp_upload_dir();

		$attachment = array(
			'guid'           => $wp_upload_dir['url'] . '/' . basename( $filename ), 
			'post_mime_type' => $filetype['type'],
			'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

		return wp_insert_attachment( $attachment, $filename, $this->__post_id );
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
		$fullSizeImage = wp_get_attachment_image_src( $this->__attachment_id, 'full');
		$this->assertEquals( $this->_dfi->get_image_url( $this->__attachment_id, 'full' ), $fullSizeImage[0] );
	}

	public function testGetImageThumb()
	{		
		$fullSizeImage = wp_get_attachment_image_src( $this->__attachment_id, 'full');
		$thumbImage = wp_get_attachment_image_src( $this->__attachment_id, 'thumbnail');

		$mock = $this->__mockBuilder
					->setMethods( array('get_image_id') )
					->getMock();

		$mock->expects( $this->once() )
			->method('get_image_id')
			->with( $fullSizeImage[0] )
			->will( $this->returnValue( $this->__attachment_id ) );

		$this->assertEquals( $mock->get_image_thumb( $fullSizeImage[0], 'thumbnail' ), $thumbImage[0] );
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
