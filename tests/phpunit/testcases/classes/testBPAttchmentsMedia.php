<?php
/**
 * BP Attachments Media tests.
 *
 * @package BP Attachments
 * @subpackage \tests\phpunit\testcases\classes\testBPAttachmentsMedia
 *
 * @since 1.0.0
 */

class BP_Attachments_Media_UnitTestCase extends BP_UnitTestCase {
	protected $current_user;
	protected $bp_uploads;
	protected $reset_request;

	public function setUp() {
		$this->current_user = wp_get_current_user();
		wp_set_current_user( $this->factory()->user->create( array( 'role' => 'administrator' ) ) );
		$this->bp_uploads = bp_attachments_uploads_dir_get();
		$this->reset_request = $_REQUEST;

		parent::setUp();
	}

	public function tearDown() {
		wp_set_current_user( $this->current_user->ID );
		$this->bp_uploads = array();
		$_REQUEST = $this->reset_request;

		parent::tearDown();
	}

	public function test_bp_attachments_media_upload_dir_filter_member_public() {
		$media = new BP_Attachments_Media();
		$user_id = get_current_user_id();

		$_REQUEST = array(
			'status'    => 'public',
			'object_id' => $user_id,
		);

		$subdir = '/public/members/' . $user_id;

		$public_member_uploads = $media->upload_dir_filter();
		$expected = array(
			'path'   => $this->bp_uploads['basedir'] . $subdir,
			'url'    => $this->bp_uploads['baseurl'] . $subdir,
			'subdir' => $subdir,
		);
		$result = array_intersect_key( $public_member_uploads, $expected );

		$this->assertSame( $expected, $result );
		$this->assertFalse( $public_member_uploads['error'] );
	}

	public function test_bp_attachments_media_upload_dir_filter_member_private() {
		$media = new BP_Attachments_Media();
		$user_id = get_current_user_id();

		$_REQUEST = array(
			'object_id' => $user_id,
		);

		$subdir = '/private/members/' . $user_id;

		$private_member_uploads = $media->upload_dir_filter();
		$expected = array(
			'path'   => $this->bp_uploads['basedir'] . $subdir,
			'url'    => $this->bp_uploads['baseurl'] . $subdir,
			'subdir' => $subdir,
		);
		$result = array_intersect_key( $private_member_uploads, $expected );

		$this->assertSame( $expected, $result );
		$this->assertFalse( $private_member_uploads['error'] );
	}

	public function test_bp_attachments_media_upload_dir_filter_member_error() {
		$media = new BP_Attachments_Media();
		$user_id = 9999999;

		$_REQUEST = array(
			'object_id' => $user_id,
		);

		$error_member_uploads = $media->upload_dir_filter();

		$this->assertTrue( false !== $error_member_uploads['error'] );
	}

	public function mmdir( $path ) {
		$base = trailingslashit( wp_get_upload_dir()['basedir'] );
		$absolute_path = str_replace( $base, '', $path );

		$dirs      = explode( '/', $absolute_path );
		$increment = '';

		foreach ( $dirs as $dir ) {
			$increment .= '/' . $dir;

			if ( is_dir( $base . $increment ) ) {
				continue;
			}

			mkdir( $base . $increment );
		}
	}

	public function test_bp_attachments_media_upload_dir_filter_missing_parent_dir() {
		$media      = new BP_Attachments_Media();
		$user_id    = get_current_user_id();
		$parent_dir = '/public/members/' . $user_id . '/foobar';

		$_REQUEST = array(
			'object_id'  => $user_id,
			'parent_dir' => $parent_dir,
		);

		$missing_parent_dir_uploads = $media->upload_dir_filter();

		$this->assertTrue( false !== $missing_parent_dir_uploads['error'] );
	}

	public function test_bp_attachments_media_upload_dir_filter_parent_dir() {
		$media      = new BP_Attachments_Media();
		$user_id    = get_current_user_id();
		$parent_dir = '/public/members/' . $user_id . '/foobar';

		$this->mmdir( $this->bp_uploads['basedir'] . $parent_dir );

		$_REQUEST = array(
			'object_id'  => $user_id,
			'parent_dir' => $parent_dir,
		);

		$parent_dir_member_uploads = $media->upload_dir_filter();
		$expected = array(
			'path'   => $this->bp_uploads['basedir'] . $parent_dir,
			'url'    => $this->bp_uploads['baseurl'] . $parent_dir,
			'subdir' => $parent_dir,
		);
		$result = array_intersect_key( $parent_dir_member_uploads, $expected );

		$this->assertSame( $expected, $result );
		$this->assertFalse( $parent_dir_member_uploads['error'] );

		$this->rrmdir( $this->bp_uploads['basedir'] . '/public' );
	}

	public function test_bp_attachments_media_upload_dir_filter_group_public() {
		$media = new BP_Attachments_Media();
		$group_id = self::factory()->group->create();

		$_REQUEST = array(
			'object'    => 'groups',
			'object_id' => $group_id,
		);

		$subdir = '/public/groups/' . $group_id;

		$public_group_uploads = $media->upload_dir_filter();
		$expected = array(
			'path'   => $this->bp_uploads['basedir'] . $subdir,
			'url'    => $this->bp_uploads['baseurl'] . $subdir,
			'subdir' => $subdir,
		);
		$result = array_intersect_key( $public_group_uploads, $expected );

		$this->assertSame( $expected, $result );
		$this->assertFalse( $public_group_uploads['error'] );
	}

	public function test_bp_attachments_media_upload_dir_filter_group_private() {
		$media = new BP_Attachments_Media();
		$group_id = self::factory()->group->create( array( 'status' => 'private' ) );

		$_REQUEST = array(
			'object'    => 'groups',
			'object_id' => $group_id,
		);

		$subdir = '/private/groups/' . $group_id;

		$private_group_uploads = $media->upload_dir_filter();
		$expected = array(
			'path'   => $this->bp_uploads['basedir'] . $subdir,
			'url'    => $this->bp_uploads['baseurl'] . $subdir,
			'subdir' => $subdir,
		);
		$result = array_intersect_key( $private_group_uploads, $expected );

		$this->assertSame( $expected, $result );
		$this->assertFalse( $private_group_uploads['error'] );
	}

	public function test_bp_attachments_media_upload_dir_filter_group_error() {
		$media = new BP_Attachments_Media();
		$group_id = 9999999;

		$_REQUEST = array(
			'object'    => 'groups',
			'object_id' => $group_id,
		);

		$error_group_uploads = $media->upload_dir_filter();

		$this->assertTrue( false !== $error_group_uploads['error'] );
	}
}
