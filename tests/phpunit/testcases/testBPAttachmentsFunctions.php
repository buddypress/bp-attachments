<?php
/**
 * BP Attachments Functions tests.
 *
 * @package \tests\phpunit\testcases\testBPAttachmentsFunctions
 *
 * @since 1.0.0
 */

/**
 * @group functions
 */
class BP_Attachments_Functions_UnitTestCase extends BP_UnitTestCase {
	public function filter_bp_attachments_uploads_dir_get( $dir = array() ) {
		if ( is_array( $dir ) ) {
			$dir['basedir'] = BP_ATTACHMENTS_TESTS_DIR . '/assets';
		}

		return $dir;
	}

	/**
	 * @group bp_attachments_list_media_in_directory
	 */
	public function test_bp_attachments_list_media_in_directory() {
		$dir = BP_ATTACHMENTS_TESTS_DIR . '/assets/public/members/1';

		add_filter( 'bp_attachments_uploads_dir_get', array( $this, 'filter_bp_attachments_uploads_dir_get' ) );

		$media_items = bp_attachments_list_media_in_directory( $dir );

		remove_filter( 'bp_attachments_uploads_dir_get', array( $this, 'filter_bp_attachments_uploads_dir_get' ) );

		$expected = array(
			'file-1.txt' => 'file',
			'folder-1'   => 'directory',
		);

		$this->assertEquals( $expected, wp_list_pluck( $media_items, 'type', 'name' ) );
	}

	public function override_allowed_media_types() {
		return array(
			'document' => array( 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' ),
		);
	}

	/**
	 * @group checktype
	 */
	public function test_bp_attachments_is_file_type_allowed() {
		$file    = BP_ATTACHMENTS_TESTS_DIR . '/assets/file-examples.com/file_example_JPG_100kB.jpg';
		$filename = wp_basename( $file );

		$this->assertTrue( bp_attachments_is_file_type_allowed( $file, $filename ) );
		$this->assertTrue( bp_attachments_is_file_type_allowed( $file, $filename, 'image' ) );
		$this->assertFalse( bp_attachments_is_file_type_allowed( $file, $filename, 'video' ) );

		$file    = BP_ATTACHMENTS_TESTS_DIR . '/assets/file-examples.com/file-sample_100kB.doc';
		$filename = wp_basename( $file );

		$this->assertFalse( bp_attachments_is_file_type_allowed( $file, $filename ) );

		add_filter( 'pre_option__bp_attachments_allowed_media_types', array( $this, 'override_allowed_media_types' ), 10, 0 );

		$file    = BP_ATTACHMENTS_TESTS_DIR . '/assets/file-examples.com/file-sample_100kB.docx';
		$filename = wp_basename( $file );

		$this->assertTrue( bp_attachments_is_file_type_allowed( $file, $filename ) );

		remove_filter( 'pre_option__bp_attachments_allowed_media_types', array( $this, 'override_allowed_media_types' ), 10, 0 );

		$file    = BP_ATTACHMENTS_TESTS_DIR . '/assets/public/members/1/d266a54dd51dc74f25110130d3b363d5.json';
		$filename = wp_basename( $file );

		$this->assertFalse( bp_attachments_is_file_type_allowed( $file, $filename ) );
	}

	public function get_public_uploads() {
		add_filter( 'upload_dir', 'bp_attachments_get_public_uploads_dir', 10, 1 );
		$directory = wp_upload_dir( 'delete_directory_test', true, true );
		remove_filter( 'upload_dir', 'bp_attachments_get_public_uploads_dir', 10, 1 );

		return $directory;
	}

	/**
	 * @group delete_directory
	 */
	public function test_bp_attachments_delete_directory() {
		$this->markTestSkipped();

		$directory = $this->get_public_uploads();

		$this->assertFalse( bp_attachments_delete_directory( $directory['path'] ) );

		$object_dir = trailingslashit( $directory['path'] ) . 'members';
		if ( ! is_dir( $object_dir ) ) {
			mkdir( $object_dir );
		}

		$this->assertFalse( bp_attachments_delete_directory( $object_dir ) );

		$item_dir = trailingslashit( $object_dir ) . '99';
		if ( ! is_dir( $item_dir ) ) {
			mkdir( $item_dir );
		}

		$this->assertFalse( bp_attachments_delete_directory( $item_dir ) );

		$sub_dir = trailingslashit( $item_dir ) . 'random';
		if ( ! is_dir( $sub_dir ) ) {
			mkdir( $sub_dir );
		}

		$this->assertTrue( bp_attachments_delete_directory( $sub_dir ) );
		$this->assertFalse( is_dir( $sub_dir ) );

		// Clean
		rmdir( $item_dir );
		rmdir( $object_dir );
		rmdir( $directory['path'] );
	}

	/**
	 * @group delete_directory
	 */
	public function test_bp_attachments_delete_directory_multiple_files() {
		$this->markTestSkipped();

		$directory = $this->get_public_uploads();

		$type_dir = $directory['path'];
		if ( ! is_dir( $type_dir ) ) {
			mkdir( $type_dir );
		}

		$object_dir = trailingslashit( $type_dir ) . 'members';
		if ( ! is_dir( $object_dir ) ) {
			mkdir( $object_dir );
		}

		$item_dir = trailingslashit( $object_dir ) . '999';
		if ( ! is_dir( $item_dir ) ) {
			mkdir( $item_dir );
		}

		$sub_dir = trailingslashit( $item_dir ) . 'foobar';
		if ( ! is_dir( $sub_dir ) ) {
			mkdir( $sub_dir );
		}

		$media_files = array(
			BP_ATTACHMENTS_TESTS_DIR . '/assets/file-examples.com/file_example_XLSX_10.xlsx',
			BP_ATTACHMENTS_TESTS_DIR . '/assets/file-examples.com/file-sample_100kB.docx',
		);

		$media = array();

		foreach ( $media_files as $media_file ) {
			$media_item           = new stdClass();
			$media_item->path     = $sub_dir . '/' . wp_basename( $media_file );
			$media_item->owner_id = 999;

			copy( $media_file, $media_item->path );
			$media[] = bp_attachments_create_media( $media_item );
		}

		$revisions_dir = '._revisions_' . $media[0]->id;
		copy( $media_files[0], $sub_dir . '/' . $revisions_dir . '/' . wp_basename( $media_files[0] ) );

		$this->assertTrue( bp_attachments_delete_directory( $sub_dir ) );
		$this->assertFalse( is_dir( $sub_dir ) );

		// Clean
		rmdir( $item_dir );
		rmdir( $object_dir );
		rmdir( $directory['path'] );
	}
}
