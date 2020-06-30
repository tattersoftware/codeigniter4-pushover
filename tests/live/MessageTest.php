<?php

use CodeIgniter\Test\FeatureResponse;
use Tests\Support\DatabaseTestCase;
use Tatter\Pushover\Entities\Message;

class MessageTest extends DatabaseTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$this->config   = new \Tatter\Pushover\Config\Pushover();
		$this->pushover = service('pushover', $this->config);

		// Create a sample message
		$this->message = $this->pushover->message([
			'message'   => 'Hello world.',
			'title'     => 'Simple',
		]);
	}

	public function testSendSends()
	{
		if (empty($this->config->user) || empty($this->config->token))
		{
			$this->markTestSkipped('Unable to run live tests without credentials');
		}

		$response = new FeatureResponse($this->message->send());

		$response->assertOk();
		$response->assertStatus(200);

		$object = json_decode($response->response->getBody());
		$this->assertIsObject($object);

		$this->assertEquals(1, $object->status);
	}
}
