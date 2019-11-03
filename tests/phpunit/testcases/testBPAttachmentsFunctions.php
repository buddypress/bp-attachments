<?php
/**
 * BP Attachments Functions tests.
 *
 * @package BP Attachments
 * @subpackage \tests\phpunit\testcases\testBPAttachmentsFunctions
 *
 * @since 1.0.0
 */

/**
 * @group functions
 */
class BP_Attachments_Functions_UnitTestCase extends BP_UnitTestCase {
	public function test_bp_attachments_list_media_in_directory() {
		$dir = BP_ATTACHMENTS_TESTS_DIR . '/assets/public/members/1';

		$media_items = bp_attachments_list_media_in_directory( $dir );

		$expected = array(
			'file-1.txt' => 'file',
			'folder-1'   => 'directory',
		);

		$this->assertEquals( $expected, wp_list_pluck( $media_items, 'type', 'name' ) );
	}

	/**
	 * @group checktype
	 */
	public function test_bp_attachments_is_file_type_allowed() {
		$file    = BP_ATTACHMENTS_TESTS_DIR . '/assets/file-examples.com/file_example_XLS_10.xls';
		$filename = wp_basename( $file );

		$this->assertTrue( bp_attachments_is_file_type_allowed( $file, $filename ) );

		$file    = BP_ATTACHMENTS_TESTS_DIR . '/assets/file-examples.com/file_example_XLSX_10.xlsx';
		$filename = wp_basename( $file );

		$this->assertTrue( bp_attachments_is_file_type_allowed( $file, $filename ) );

		$file    = BP_ATTACHMENTS_TESTS_DIR . '/assets/file-examples.com/file-sample_100kB.doc';
		$filename = wp_basename( $file );

		$this->assertTrue( bp_attachments_is_file_type_allowed( $file, $filename ) );

		$file    = BP_ATTACHMENTS_TESTS_DIR . '/assets/file-examples.com/file-sample_100kB.docx';
		$filename = wp_basename( $file );

		$this->assertTrue( bp_attachments_is_file_type_allowed( $file, $filename ) );

		$file    = BP_ATTACHMENTS_TESTS_DIR . '/assets/public/members/1/d266a54dd51dc74f25110130d3b363d5.json';
		$filename = wp_basename( $file );

		$this->assertFalse( bp_attachments_is_file_type_allowed( $file, $filename ) );
	}
}
