<?php

class DynamicFeaturedImageTest extends WP_UnitTestCase {

	private $__mockBuilder = null;
	protected $_dfi = null;
	protected $_pluginData = null;

	public function setUp() 
	{
		parent::setUp();

		$this->__mockBuilder = $this->getMockBuilder( 'Dynamic_Featured_Image' );

		global $dynamic_featured_image;
		$this->_dfi = $dynamic_featured_image;

		$this->_pluginData = get_plugin_data( dirname(dirname(__FILE__)) . '/dynamic-featured-image.php' );
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

	public function tearDown() 
	{
		unset($this->_dfi);
	}
}
