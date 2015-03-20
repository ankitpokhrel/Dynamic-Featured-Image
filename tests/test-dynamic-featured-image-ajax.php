<?php

class DynamicFeaturedImageAjaxTest extends WP_Ajax_UnitTestCase
{

	private $__post_id = null;

	protected $_dfi = null;

	public function setUp() 
	{
		parent::setUp();

		$this->_dfi = new Dynamic_Featured_Image;

		$this->__post_id = $this->factory->post->create( array( 'post_title' => 'Dynamic Featured Image WordPress Plugin' ) );
	}

	public function testAjaxCallback()
	{
		$this->_setRole('administrator');

		$expectedOutput = "<a href='javascript:void(0)' class='dfiFeaturedImage hasFeaturedImage' title='Set Featured Image' data-post-id='" . $this->__post_id . "'><span class='dashicons dashicons-camera'></span></a><br/>
			<img src='' class='dfiImg '/>
			<div class='dfiLinks'>
				<a href='javascript:void(0)' data-id='{$this->__post_id}' data-id-local='" . ($this->__post_id + 1) . "' class='dfiAddNew dashicons dashicons-plus' title='Add New'></a>
				<a href='javascript:void(0)' class='dfiRemove dashicons dashicons-minus' title='Remove'></a>
			</div>
			<div class='dfiClearFloat'></div>
			<input type='hidden' name='dfiFeatured[]' value='/2015/03/dfi-150x150.jpg'  class='dfiImageHolder' />";

		$this->expectOutputString($expectedOutput);

		$_POST['id'] = $this->__post_id;
		$this->_handleAjax( 'dfiMetaBox_callback' );
	}

	public function tearDown()
	{
		unset($this->__post_id);
		unset($this->_dfi);
	}
}
