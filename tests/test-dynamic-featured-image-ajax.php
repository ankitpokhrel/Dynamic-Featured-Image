<?php

/**
 * Test DFI ajax requests
 *
 * @group ajax
 * @coversDefaultClass Dynamic_Featured_Image
 */
class Dynamic_Featured_Image_Ajax_Test extends WP_Ajax_UnitTestCase {

    protected $post_id = null;

    public function setUp() {
        parent::setUp();

        $this->post_id = $this->factory->post->create( [
            'post_title' => 'Dynamic Featured Image WordPress Plugin',
        ] );

        $this->_setRole( 'administrator' );
    }

    /**
     * @test
     *
     * @covers ::ajax_callback
     */
    public function it_fails_on_referer_check() {
        try {
            $this->_handleAjax( 'dfiMetaBox_callback' );
        } catch ( WPAjaxDieStopException $e ) {
        }

        // it should throw exception
        $this->assertTrue( isset( $e ) );

        // exception message must be -1
        $this->assertEquals( '-1', $e->getMessage() );
    }

    /**
     * @test
     *
     * @covers ::ajax_callback
     */
    public function it_executes_ajax_callback() {

        $expectedOutput = '<a href="javascript:void(0)" class="dfiFeaturedImage" title="Set Featured Image"><span class="dashicons dashicons-camera"></span></a><br/>
                 <img src="" class="dfiImg dfiImgEmpty"/>
                 <div class="dfiLinks">
                <a href="javascript:void(0)" data-id="' . $this->post_id . '" data-id-local="' . ( $this->post_id + 1 ) . '" class="dfiAddNew dashicons dashicons-plus" title="Add New"></a>
                <a href="javascript:void(0)" class="dfiRemove dashicons dashicons-minus" title="Remove"></a>
                 </div>
                 <div class="dfiClearFloat"></div>
                 <input type="hidden" name="dfiFeatured[]" value="" class="dfiImageHolder" />';

        $expectedOutput = preg_replace( '/\s+/', '', $expectedOutput );
        $plugin_folder  = preg_replace( '/tests\/' . basename( __FILE__ ) . '/', '', plugin_basename( __FILE__ ) );

        $_POST = [
            'id' => $this->post_id,
            'security' => wp_create_nonce( $plugin_folder . 'dynamic-featured-image.php' ),
        ];

        try {
            $this->_handleAjax( 'dfiMetaBox_callback' );
        } catch ( WPAjaxDieContinueException $e ) {
        }

        // it should throw exception
        $this->assertTrue( isset( $e ) );

        // exception message must be empty
        $this->assertEquals( '', $e->getMessage() );

        $response = preg_replace( '/\s+/', '', $this->_last_response );

        // should contain expected output
        $this->assertContains( $expectedOutput, $response );
    }

    public function tearDown() {
        unset( $this->post_id );
    }
}
