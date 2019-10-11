<?php
/**
 * BP Attachments Admin tests.
 *
 * @package BP Attachments
 * @subpackage \tests\phpunit\testBPAttachmentsAdmin
 *
 * @since 1.0.0
 */

class BP_Attachments_Admin_UnitTestCase extends BP_UnitTestCase {
	protected $current_user;

	public function setUp() {
		$this->current_user = wp_get_current_user();
		wp_set_current_user( $this->factory()->user->create( array( 'role' => 'administrator' ) ) );
		set_current_screen( 'dashboard' );

		// is_admin() is not yet set in BP_Attachments_Component::includes().
		require_once buddypress()->attachments->path . 'bp-attachments-admin.php';

		parent::setUp();
	}

	public function tearDown() {
		wp_set_current_user( $this->current_user->ID );
		set_current_screen( 'front' );

		parent::tearDown();
	}

	public function test_bp_attachments_get_media_table() {
		$schema = bp_attachments_get_media_schema();

		$this->assertContains( 'uploads_relative_path', $schema[0] );
		$this->assertContains( 'object_type', $schema[1] );
	}
}
