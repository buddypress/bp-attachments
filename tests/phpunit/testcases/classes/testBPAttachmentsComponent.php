<?php
/**
 * BP Attachments Component tests.
 *
 * @package BP Attachments
 * @subpackage \tests\phpunit\testcases\classes\testBPAttachmentsComponent
 *
 * @since 1.0.0
 */

/**
 * @group component
 */
class BP_Attachments_Component_UnitTestCase extends BP_UnitTestCase {
	function setUp() {
		parent::setUp();

		$this->set_permalink_structure( '/%postname%/' );
	}

	function filter_bp_attachments_uploads_dir_get( $dir = array() ) {
		$dir['basedir'] = BP_ATTACHMENTS_TESTS_DIR . '/assets';

		return $dir;
	}

	public function test_parse_query() {
		add_filter( 'bp_attachments_uploads_dir_get', array( $this, 'filter_bp_attachments_uploads_dir_get' ) );

		$link = home_url( '/bp-attachments/public/members/1/view/d266a54dd51dc74f25110130d3b363d5/' );

		$this->go_to( $link );

		$media = get_queried_object();

		remove_filter( 'bp_attachments_uploads_dir_get', array( $this, 'filter_bp_attachments_uploads_dir_get' ) );

		$this->assertTrue( 'file-1.txt' === $media->name );
	}
}
