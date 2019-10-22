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

	public function setUp() {
		parent::setUp();

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
		wp_set_current_user( $this->user );
	}

	public function tearDown() {
		parent::tearDown();
		wp_set_current_user( $this->current_user );
	}

	public function test_register_routes() {
		$routes = rest_get_server()->get_routes();
		$this->assertArrayHasKey( $this->endpoint_url, $routes );
		$this->assertCount( 2, $routes[ $this->endpoint_url ] );
		$this->assertArrayHasKey( $this->endpoint_url . '/(?P<id>[\d]+)', $routes );
		$this->assertCount( 3, $routes[$this->endpoint_url . '/(?P<id>[\d]+)'] );
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

	public function test_create_item() {
		$this->markTestSkipped();
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
