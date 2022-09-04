<?php
/**
 * BP Attachments Component tests.
 *
 * @package \tests\phpunit\testcases\classes\testBPAttachmentsComponent
 *
 * @since 1.0.0
 */

/**
 * @group component
 */
class BP_Attachments_Component_UnitTestCase extends BP_UnitTestCase {
	protected $current_user_id;

	public function setUp() {
		parent::setUp();

		$this->set_permalink_structure( '/%postname%/' );
		$this->current_user_id = get_current_user_id();
	}

	public function tearDown() {
		parent::tearDown();

		wp_set_current_user( $this->current_user_id );
	}

	public function filter_bp_attachments_uploads_dir_get( $dir = array() ) {
		if ( is_array( $dir ) ) {
			$dir['basedir'] = BP_ATTACHMENTS_TESTS_DIR . '/assets';
		}

		return $dir;
	}

	public function test_parse_query() {
		add_filter( 'bp_attachments_uploads_dir_get', array( $this, 'filter_bp_attachments_uploads_dir_get' ) );
		wp_set_current_user( 1 );

		$username = wp_get_current_user()->user_nicename;

		$link = home_url( '/bp-attachments/public/members/' . $username . '/view/d266a54dd51dc74f25110130d3b363d5/' );

		$this->go_to( $link );

		$media = wp_cache_get( 'public/members/1/d266a54dd51dc74f25110130d3b363d5', 'bp_attachments');

		remove_filter( 'bp_attachments_uploads_dir_get', array( $this, 'filter_bp_attachments_uploads_dir_get' ) );

		$this->assertTrue( 'file-1.txt' === $media->name );
	}
}
