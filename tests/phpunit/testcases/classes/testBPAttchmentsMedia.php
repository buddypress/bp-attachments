<?php
/**
 * BP Attachments Media tests.
 *
 * @package \tests\phpunit\testcases\classes\testBPAttachmentsMedia
 *
 * @since 1.0.0
 */

 /**
  * @group media_uploads
  */
class BP_Attachments_Media_UnitTestCase extends BP_UnitTestCase {
	protected $current_user;
	protected $bp_uploads;
	protected $reset_request;

	public function setUp() {
		$this->current_user = wp_get_current_user();
		wp_set_current_user( $this->factory()->user->create( array( 'role' => 'administrator' ) ) );
		$this->bp_uploads = bp_attachments_uploads_dir_get();
		$this->reset_request = $_POST;

		parent::setUp();
	}

	public function tearDown() {
		wp_set_current_user( $this->current_user->ID );
		$this->bp_uploads = array();
		$_POST = $this->reset_request;

		parent::tearDown();
	}

	public function test_bp_attachments_media_upload_dir_filter_member_public() {
		$media = new BP_Attachments_Media();
		$user_id = get_current_user_id();

		$_POST = array(
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
		$this->assertTrue( ! isset( $public_member_uploads['bp_attachments_error_code'] ) );
	}

	public function test_bp_attachments_media_upload_dir_filter_member_private_disabled() {
		$media = new BP_Attachments_Media();
		$user_id = get_current_user_id();

		add_filter( 'pre_option__bp_attachments_can_upload_privately', '__return_false' );

		$_POST = array(
			'object_id' => $user_id,
			'status'    => 'private',
		);

		$private_member_uploads = $media->upload_dir_filter();

		remove_filter( 'pre_option__bp_attachments_can_upload_privately', '__return_false' );

		$this->assertTrue( 18 === $private_member_uploads['bp_attachments_error_code'] );
	}

	public function test_bp_attachments_media_upload_dir_filter_member_private_missing_dir() {
		$media = new BP_Attachments_Media();
		$user_id = get_current_user_id();

		add_filter( 'pre_option__bp_attachments_can_upload_privately', '__return_true' );

		$_POST = array(
			'object_id' => $user_id,
			'status'    => 'private',
		);

		$private_member_uploads = $media->upload_dir_filter();

		remove_filter( 'pre_option__bp_attachments_can_upload_privately', '__return_true' );

		$this->assertTrue( 16 === $private_member_uploads['bp_attachments_error_code'] );
	}

	public function private_uploads_dir_override() {
		return trailingslashit( $this->bp_uploads['basedir'] ) . 'private';
	}

	public function test_bp_attachments_media_upload_dir_filter_member_private() {
		$media = new BP_Attachments_Media();
		$user_id = get_current_user_id();

		$_POST = array(
			'status'    => 'private',
			'object_id' => $user_id,
		);

		$subdir = '/members/' . $user_id;

		add_filter( 'pre_option__bp_attachments_can_upload_privately', '__return_true' );
		add_filter( 'bp_attachments_pre_get_private_root_dir', array( $this, 'private_uploads_dir_override' ), 10, 0 );

		$private_member_uploads = $media->upload_dir_filter();

		$expected = array(
			'basedir' => $this->bp_uploads['basedir'] . '/private',
			'path'    => $this->bp_uploads['basedir'] . '/private' . $subdir,
			'url'     => '',
			'subdir'  => $subdir,
		);
		$result = array_intersect_key( $private_member_uploads, $expected );

		remove_filter( 'pre_option__bp_attachments_can_upload_privately', '__return_true' );
		remove_filter( 'bp_attachments_pre_get_private_root_dir', array( $this, 'private_uploads_dir_override' ), 10, 0 );

		//$this->assertSame( $expected, $result );
		$this->assertTrue( ! isset( $public_member_uploads['bp_attachments_error_code'] ) );
	}

	public function test_bp_attachments_media_upload_dir_filter_member_error() {
		$this->markTestSkipped();

		$media = new BP_Attachments_Media();
		$user_id = 9999999;

		$_POST = array(
			'object_id' => $user_id,
		);

		$error_member_uploads = $media->upload_dir_filter();

		$this->assertTrue( 18 === $error_member_uploads['bp_attachments_error_code'] );
	}

	public function mmdir( $path ) {
		$base = trailingslashit( bp_upload_dir()['basedir'] );
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
		$this->markTestSkipped();

		$media      = new BP_Attachments_Media();
		$user_id    = get_current_user_id();
		$parent_dir = '/public/members/' . $user_id . '/foobar';

		$_POST = array(
			'object_id'  => $user_id,
			'parent_dir' => $parent_dir,
		);

		$missing_parent_dir_uploads = $media->upload_dir_filter();

		$this->assertTrue( 16 === $missing_parent_dir_uploads['bp_attachments_error_code'] );
	}

	public function test_bp_attachments_media_upload_dir_filter_parent_dir() {
		$this->markTestSkipped();

		$media      = new BP_Attachments_Media();
		$user_id    = get_current_user_id();
		$parent_dir = '/public/members/' . $user_id . '/foobar';

		$this->mmdir( $this->bp_uploads['basedir'] . $parent_dir );

		$_POST = array(
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
		$this->assertTrue( ! isset( $parent_dir_member_uploads['bp_attachments_error_code'] ) );

		$this->rrmdir( $this->bp_uploads['basedir'] . '/public' );
	}

	public function test_bp_attachments_media_upload_dir_filter_group_public() {
		$this->markTestSkipped();

		$media = new BP_Attachments_Media();
		$group = self::factory()->group->create_and_get();

		$_POST = array(
			'object'      => 'groups',
			'object_slug' => $group->slug,
		);

		$subdir = '/public/groups/' . $group->id;

		$public_group_uploads = $media->upload_dir_filter();
		$expected = array(
			'path'   => $this->bp_uploads['basedir'] . $subdir,
			'url'    => $this->bp_uploads['baseurl'] . $subdir,
			'subdir' => $subdir,
		);
		$result = array_intersect_key( $public_group_uploads, $expected );

		$this->assertSame( $expected, $result );
		$this->assertTrue( ! isset( $public_group_uploads['bp_attachments_error_code'] ) );
	}

	public function test_bp_attachments_media_upload_dir_filter_group_private() {
		$this->markTestSkipped();

		$media = new BP_Attachments_Media();
		$group = self::factory()->group->create_and_get( array( 'status' => 'private' ) );

		$_POST = array(
			'object'      => 'groups',
			'object_slug' => $group->slug,
		);

		$subdir = '/private/groups/' . $group->id;

		$private_group_uploads = $media->upload_dir_filter();
		$expected = array(
			'path'   => $this->bp_uploads['basedir'] . $subdir,
			'url'    => $this->bp_uploads['baseurl'] . $subdir,
			'subdir' => $subdir,
		);
		$result = array_intersect_key( $private_group_uploads, $expected );

		$this->assertSame( $expected, $result );
		$this->assertTrue( ! isset( $private_group_uploads['bp_attachments_error_code'] ) );
	}

	public function test_bp_attachments_media_upload_dir_filter_group_error() {
		$this->markTestSkipped();

		$media = new BP_Attachments_Media();

		$_POST = array(
			'object'      => 'groups',
			'object_slug' => 'unexisting-slug',
		);

		$error_group_uploads = $media->upload_dir_filter();

		$this->assertTrue( 17 === $error_group_uploads['bp_attachments_error_code'] );
	}
}
