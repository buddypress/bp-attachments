<?php
/**
 * BP Attachments Functions tests.
 *
 * @package BP Attachments
 * @subpackage \tests\phpunit\testcases\testBPAttachmentsFunctions
 *
 * @since 1.0.0
 */

class BP_Attachments_Functions_UnitTestCase extends BP_UnitTestCase {
	public function test_bp_attachments_list_dir_media() {
		$dir = BP_ATTACHMENTS_TESTS_DIR . '/assets/public/members/1';

		$media_items = bp_attachments_list_dir_media( $dir );

		$expected = array(
			'file-1.txt' => 'file',
			'folder-1'   => 'dir',
		);

		$this->assertSame( $expected, wp_list_pluck( $media_items, 'type', 'name' ) );
	}
}
