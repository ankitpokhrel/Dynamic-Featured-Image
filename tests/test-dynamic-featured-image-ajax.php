<?php
/**
 * Test DFI ajax requests
 *
 * @group ajax
 */
class DynamicFeaturedImageAjaxTest extends WP_Ajax_UnitTestCase
{

	private $__post_id = null;

	public function setUp()
	{
		parent::setUp();

		$this->__post_id = $this->factory->post->create( array( 'post_title' => 'Dynamic Featured Image WordPress Plugin' ) );
	}

	/**
	 * @covers Dynamic_Featured_Image::ajax_callback
	 */
	public function testAjaxCallback()
	{
		$this->_setRole('administrator');

		$expectedOutput = 'a href="javascript:void(0)" class="dfiFeaturedImage" title="Set Featured Image"><span class="dashicons dashicons-camera"></span></a><br/>
				 <img src="" class="dfiImg dfiImgEmpty"/>
				 <div class="dfiLinks">
				<a href="javascript:void(0)" data-id="' . $this->__post_id . '" data-id-local="' . ($this->__post_id + 1) . '" class="dfiAddNew dashicons dashicons-plus" title="Add New"></a>
				<a href="javascript:void(0)" class="dfiRemove dashicons dashicons-minus" title="Remove"></a>
				 </div>
				 <div class="dfiClearFloat"></div>
				 <input type="hidden" name="dfiFeatured[]" value="" class="dfiImageHolder" />';

		$_POST['id'] = $this->__post_id;
		try {
			$this->_handleAjax( 'dfiMetaBox_callback' );
		} catch ( WPAjaxDieContinueException $e ) {}

		//it should throw exception
		$this->assertTrue( isset($e) );

		//exception message must be empty
		$this->assertEquals( '', $e->getMessage() );

		//should contain expected ouptput
		$this->assertContains($expectedOutput, $this->_last_response);
	}

	public function tearDown()
	{
		unset($this->__post_id);
	}
}
