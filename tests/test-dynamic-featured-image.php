<?php

class DynamicFeaturedImageTest extends WP_UnitTestCase {

	private $__mockBuilder = null;
	protected $_dfi = null;

	public function setUp() 
	{
		parent::setUp();

		$this->__mockBuilder = $this->getMockBuilder( 'Dynamic_Featured_Image' );

		global $dynamic_featured_image;
		$this->_dfi = $dynamic_featured_image;
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
	
	public function tearDown() 
	{
		unset($this->_dfi);
	}
}
