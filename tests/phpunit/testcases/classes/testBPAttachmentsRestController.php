<?php
/**
 * BP Attachments Media tests.
 *
 * @package \tests\phpunit\testcases\classes\testBPAttachmentsMedia
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

	public function copy_file( $return, $file, $new_file ) {
		return @copy( $file['tmp_name'], $new_file );
	}

	/**
	 * @group rest_register_routes
	 */
	public function test_register_routes() {
		$routes = rest_get_server()->get_routes();
		$this->assertArrayHasKey( $this->endpoint_url, $routes );
		$this->assertCount( 2, $routes[ $this->endpoint_url ] );

		$this->assertArrayHasKey( $this->endpoint_url . '/(?P<id>[\S]+)', $routes );
		$this->assertCount( 2, $routes[$this->endpoint_url . '/(?P<id>[\S]+)'] );
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
		$this->markTestSkipped();

		$reset_files = $_FILES;
		$reset_post  = $_POST;
		$media_file  = BP_ATTACHMENTS_TESTS_DIR . '/assets/file-examples.com/file_example_JPG_100kB.jpg';

		$u = $this->factory->user->create( array(
			'role'       => 'administrator',
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
			'action'     => 'bp_attachments_media_upload',
			'visibility' => 'public',
			'object'     => 'members',
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
		$this->markTestSkipped();

		$reset_post  = $_POST;
		$u = $this->factory->user->create( array(
			'role'       => 'administrator',
			'user_email' => 'subscriber@example.com',
		) );

		$this->bp_testcase->set_current_user( $u );

		$_POST = array(
			'action'         => 'bp_attachments_make_directory',
			'visibility'     => 'public',
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
		$this->markTestSkipped();

		$request = new WP_REST_Request( 'POST', $this->endpoint_url );
		$request->add_header( 'content-type', 'application/x-www-form-urlencoded' );
		$request->set_body_params( array(
			'action' => 'random',
		) );
		$response = $this->server->dispatch( $request );

		$this->assertErrorResponse( 'rest_invalid_param', $response, 400 );
	}

	/**
	 * @group rest_prepare_for_fs
	 */
	public function test_prepare_item_for_filesystem() {
		$this->markTestSkipped();

		$controller = new BP_Attachments_REST_Controller();
		$request    = new WP_REST_Request( 'POST', $this->endpoint_url );
		$request->set_body_params( array(
			'action'     => 'bp_attachments_media_upload',
			'path'       => BP_ATTACHMENTS_TESTS_DIR . '/assets/public/members/1/file-1.txt',
			'mime_type'  => 'text/plain',
			'context'    => 'edit',
		) );

		$media = $controller->prepare_item_for_filesystem( $request );
		$this->assertTrue( 'text/plain' === $media->mime_type );
	}

	/**
	 * @group rest_prepare_for_fs
	 */
	public function test_prepare_item_for_filesystem_for_dir() {
		$this->markTestSkipped();

		$controller = new BP_Attachments_REST_Controller();
		$request    = new WP_REST_Request( 'POST', $this->endpoint_url );
		$request->set_body_params( array(
			'action'     => 'bp_attachments_make_directory',
			'path'       => BP_ATTACHMENTS_TESTS_DIR . '/assets/public/members/1/folder-1',
			'title'      => 'Folder 1',
			'media_type' => 'video_playlist',
			'context'    => 'edit',
		) );

		$media = $controller->prepare_item_for_filesystem( $request );
		$this->assertTrue( 'video_playlist' === $media->media_type );
	}

	public function test_update_item() {
		$this->markTestSkipped();
	}

	public function delete_tests_upload_dir( $uploads = array() ) {
		$this->markTestSkipped();

		$private_uploads = bp_attachments_get_private_uploads_dir();

		foreach ( array_keys( $private_uploads ) as $key ) {
			if ( in_array( $key, array( 'error', 'basedir', 'baseurl' ), true ) ) {
				continue;
			}

			$private_uploads[ $key ] .= '/members/' . get_current_user_id();
		}

		return array_merge( $uploads, $private_uploads );
	}

	/**
	 * @group rest_delete_item
	 */
	public function test_delete_item() {
		$this->markTestSkipped();

		$media      = new stdClass();
		$media_file = BP_ATTACHMENTS_TESTS_DIR . '/assets/file-examples.com/file_example_XLS_10.xls';

		$u = $this->factory->user->create( array(
			'role'       => 'administrator',
			'user_email' => 'subscriber@example.com',
		) );

		$this->bp_testcase->set_current_user( $u );

		add_filter( 'upload_dir', array( $this, 'delete_tests_upload_dir' ), 10, 1 );

		$uploads = wp_upload_dir( null, true );
		$media->path = $uploads['path'] . '/file_example_XLS_10.xls';

		remove_filter( 'upload_dir', array( $this, 'delete_tests_upload_dir' ), 10, 1 );

		copy( $media_file, $media->path );
		$delete = bp_attachments_create_media( $media );

		add_filter( 'bp_attachments_uploads_dir_get', array( $this, 'filter_bp_attachments_uploads_dir' ), 100, 1 );

		$request = new WP_REST_Request( 'DELETE', sprintf( $this->endpoint_url . '/%s/', $delete->id ) );
		$request->set_param( 'context', 'edit' );
		$request->set_param( 'relative_path', $uploads['subdir'] );
		$response = $this->server->dispatch( $request );

		remove_filter( 'bp_attachments_uploads_dir_get', array( $this, 'filter_bp_attachments_uploads_dir' ), 100, 1 );

		$this->assertNotInstanceOf( 'WP_Error', $response );
		$this->assertEquals( 200, $response->get_status() );

		$del_data = $response->get_data();
		$this->assertSame( 'file_example_XLS_10.xls', $del_data['previous']['name'] );
		$this->assertTrue( $del_data['deleted'] );
		$this->assertFalse( file_exists( $uploads['path'] . '/' . $delete->name ) );
	}

	/**
	 * @group rest_delete_item
	 */
	public function test_delete_directory_item() {
		$this->markTestSkipped();

		$media      = new stdClass();
		$directory  = new stdClass();
		$media_file = BP_ATTACHMENTS_TESTS_DIR . '/assets/file-examples.com/file_example_JPG_100kB.jpg';

		$u = $this->factory->user->create( array(
			'role'       => 'administrator',
			'user_email' => 'subscriber@example.com',
		) );

		$this->bp_testcase->set_current_user( $u );

		add_filter( 'upload_dir', array( $this, 'delete_tests_upload_dir' ), 10, 1 );

		$uploads = wp_upload_dir( null, true );

		remove_filter( 'upload_dir', array( $this, 'delete_tests_upload_dir' ), 10, 1 );

		$subdir  = $uploads['path'] . '/random';
		if ( ! is_dir( $subdir ) ) {
			mkdir( $subdir );
		}

		$directory->path = $subdir;
		$delete      = bp_attachments_create_media( $directory );

		$media->path = $subdir . '/file_example_JPG_100kB.jpg';
		copy( $media_file, $media->path );

		// Create the file inside the directory.
		bp_attachments_create_media( $media );

		add_filter( 'bp_attachments_uploads_dir_get', array( $this, 'filter_bp_attachments_uploads_dir' ), 100, 1 );

		$request = new WP_REST_Request( 'DELETE', sprintf( $this->endpoint_url . '/%s/', $delete->id ) );
		$request->set_param( 'context', 'edit' );
		$request->set_param( 'relative_path', $uploads['subdir'] );
		$response = $this->server->dispatch( $request );

		remove_filter( 'bp_attachments_uploads_dir_get', array( $this, 'filter_bp_attachments_uploads_dir' ), 100, 1 );

		$this->assertNotInstanceOf( 'WP_Error', $response );
		$this->assertEquals( 200, $response->get_status() );

		$del_data = $response->get_data();
		$this->assertSame( 'random', $del_data['previous']['name'] );
		$this->assertTrue( $del_data['deleted'] );
		$this->assertFalse( is_dir( $subdir ) );
	}

	public function test_prepare_item() {
		$this->markTestSkipped();
	}

	public function test_get_item_schema() {
		$this->markTestSkipped();
	}
}
