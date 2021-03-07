<?php
/**
 * BP Attachments Admin tests.
 *
 * @package \tests\phpunit\testcases\testBPAttachmentsAdmin
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

	public function filter_bp_attachments_uploads_dir( $uploads_dir = array() ) {
		return array(
			'basedir' => str_replace( 'buddypress', 'bp_attachments_test_install_dir', $uploads_dir['basedir']  ),
			'baseurl' => str_replace( 'buddypress', 'bp_attachments_test_install_dir', $uploads_dir['baseurl']  ),
			'dir'     => 'bp_attachments_test_install_dir',
		);
	}

	private function clean_files() {
		$upload_dir = bp_upload_dir();
		$test_dir   = $upload_dir['basedir'] . '/bp_attachments_test_install_dir';

		if ( ! is_dir( $test_dir ) ) {
			return;
		}

		$this->rrmdir( $test_dir );
	}

	public function test_bp_attachments_install() {
		$this->clean_files();

		add_filter( 'bp_attachments_uploads_dir_get', array( $this, 'filter_bp_attachments_uploads_dir' ), 0, 1 );
		bp_attachments_install();

		$public_uploads = bp_attachments_get_public_uploads_dir();
		$this->assertTrue( is_dir( $public_uploads['basedir'] . '/' . $public_uploads['subdir'] ) );

		remove_filter( 'bp_attachments_uploads_dir_get', array( $this, 'filter_bp_attachments_uploads_dir' ), 0, 1 );
	}
}
