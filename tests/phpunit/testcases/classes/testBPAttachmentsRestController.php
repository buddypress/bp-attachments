<?php
/**
 * BP Attachments Media tests.
 *
 * @package BP Attachments
 * @subpackage \tests\phpunit\testcases\classes\testBPAttachmentsMedia
 *
 * @since 1.0.0
 */

/**
 * @group rest
 */
class BP_attachments_REST_controller_UnitTestCase extends WP_Test_REST_Controller_Testcase {
	protected $current_user;
	protected $endpoint;
	protected $endpoint_url;
	protected $user;
	public $server;
	protected $bp_testcase;

	public function setUp() {
		parent::setUp();

		$this->bp_testcase  = new BP_UnitTestCase();
		$this->endpoint     = new BP_Attachments_REST_Controller();
		$this->endpoint_url = '/' . bp_rest_namespace() . '/' . bp_rest_version() . '/' . buddypress()->attachments->id;

		$this->user = $this->factory->user->create( array(
			'role'       => 'administrator',
			'user_email' => 'admin@example.com',
		) );

		if ( ! $this->server ) {
			$this->server = rest_get_server();
		}

		$this->current_user = get_current_user_id();
		$this->bp_testcase->set_current_user( $this->user );

		add_filter( 'upload_dir', array( $this, 'filter_bp_attachments_uploads_dir' ), 100, 1 );
		$this->clean_test_directory();
	}

	public function tearDown() {
		parent::tearDown();
		$this->bp_testcase->set_current_user( $this->current_user );

		remove_filter( 'upload_dir', array( $this, 'filter_bp_attachments_uploads_dir' ), 100, 1 );
	}

	private function clean_test_directory() {
		$upload_dir = bp_upload_dir();
		$test_dir   = $upload_dir['basedir'] . '/bp_attachments_tests_dir';

		if ( ! is_dir( $test_dir ) ) {
			return;
		}

		if ( file_exists( $test_dir . '/private/.htaccess' ) ) {
			unlink( $test_dir . '/private/.htaccess' );
		}

		$directories = new RecursiveDirectoryIterator( $test_dir, FilesystemIterator::SKIP_DOTS );
		foreach( new RecursiveIteratorIterator( $directories, RecursiveIteratorIterator::CHILD_FIRST ) as $i ) {
			if ( is_dir( $i->getPathname() ) ) {
				rmdir( $i->getPathname() );
			} else {
				unlink( $i->getPathname() );
			}
		}

		if ( is_dir( $test_dir ) ) {
			rmdir( $test_dir );
		}
	}

	public function filter_bp_attachments_uploads_dir( $uploads_dir = array() ) {
		foreach ( $uploads_dir as $key => $value ) {
			if ( strpos( $value, 'buddypress' ) ) {
				$uploads_dir[ $key ] = str_replace( 'buddypress', 'bp_attachments_tests_dir', $value );
			}
		}

		return $uploads_dir;
	}

	public function copy_file( $return = null, $file, $new_file ) {
		return @copy( $file['tmp_name'], $new_file );
	}

	/**
	 * @group rest_register_routes
	 */
	public function test_register_routes() {
		$routes = rest_get_server()->get_routes();
		$this->assertArrayHasKey( $this->endpoint_url, $routes );
		$this->assertCount( 2, $routes[ $this->endpoint_url ] );
		//$this->assertArrayHasKey( $this->endpoint_url . '/(?P<id>[\d]+)', $routes );
		//$this->assertCount( 3, $routes[$this->endpoint_url . '/(?P<id>[\d]+)'] );
	}

	public function test_context_param() {
		$this->markTestSkipped();
	}

	public function test_get_items() {
		$this->markTestSkipped();
	}

	public function test_get_item() {
		$this->markTestSkipped();
	}

	/**
	 * @group rest_create_item
	 * @group rest_create_media
	 */
	public function test_create_item() {
		$reset_files = $_FILES;
		$reset_post  = $_POST;
		$media_file  = BP_ATTACHMENTS_TESTS_DIR . '/assets/file-examples.com/file_example_JPG_100kB.jpg';

		$u = $this->factory->user->create( array(
			'role'       => 'subscriber',
			'user_email' => 'subscriber@example.com',
		) );

		$this->bp_testcase->set_current_user( $u );

		add_filter( 'pre_move_uploaded_file', array( $this, 'copy_file' ), 10, 3 );

		$_FILES['file'] = array(
			'tmp_name' => $media_file,
			'name'     => 'file_example_JPG_100kB.jpg',
			'type'     => 'image/jpeg',
			'error'    => 0,
			'size'     => filesize( $media_file ),
		);

		$_POST = array(
			'action' => 'bp_attachments_media_upload',
			'status' => 'public',
			'object' => 'members',
		);

		$request = new WP_REST_Request( 'POST', $this->endpoint_url );
		$request->add_header( 'content-type', 'application/x-www-form-urlencoded' );
		$request->set_body_params( $_POST );
		$request->set_file_params( $_FILES );
		$response = $this->server->dispatch( $request );

		remove_filter( 'pre_move_uploaded_file', array( $this, 'copy_file' ), 10, 3 );

		$get_data = $response->get_data();

		$this->assertSame( $get_data->name, $_FILES['file']['name'] );

		$_FILES = $reset_files;
		$_POST  = $reset_post;

		$this->bp_testcase->set_current_user( $this->user );
	}

	/**
	 * @group rest_create_item
	 * @group rest_create_directory
	 */
	public function test_create_item_directory() {
		$reset_post  = $_POST;
		$u = $this->factory->user->create( array(
			'role'       => 'subscriber',
			'user_email' => 'subscriber@example.com',
		) );

		$this->bp_testcase->set_current_user( $u );

		$_POST = array(
			'action'         => 'bp_attachments_make_directory',
			'status'         => 'public',
			'object'         => 'members',
			'directory_name' => 'My Beautiful directory',
		);

		$request = new WP_REST_Request( 'POST', $this->endpoint_url );
		$request->add_header( 'content-type', 'application/x-www-form-urlencoded' );
		$request->set_body_params( $_POST );

		$response = $this->server->dispatch( $request );

		$get_data = $response->get_data();

		$this->assertSame( $get_data->title, 'My Beautiful directory' );

		$_POST  = $reset_post;
		$this->bp_testcase->set_current_user( $this->user );
	}

	/**
	 * @group rest_create_item
	 */
	public function test_create_item_wrong_action() {
		$request = new WP_REST_Request( 'POST', $this->endpoint_url );
		$request->add_header( 'content-type', 'application/x-www-form-urlencoded' );
		$request->set_body_params( array(
			'action' => 'random',
		) );
		$response = $this->server->dispatch( $request );

		$this->assertErrorResponse( 'rest_invalid_param', $response, 400 );
	}

	public function test_update_item() {
		$this->markTestSkipped();
	}

	public function test_delete_item() {
		$this->markTestSkipped();
	}

	public function test_prepare_item() {
		$this->markTestSkipped();
	}

	public function test_get_item_schema() {
		$this->markTestSkipped();
	}
}
