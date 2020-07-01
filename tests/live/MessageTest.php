<?php

use CodeIgniter\Test\FeatureResponse;
use Config\Services;
use Tests\Support\DatabaseTestCase;
use Tatter\Pushover\Exceptions\PushoverException;
use Tatter\Pushover\Entities\Message;
use Tatter\Pushover\Pushover;

class MessageTest extends DatabaseTestCase
{
	public function setUp(): void
	{
		parent::setUp();

		$this->config = new \Tatter\Pushover\Config\Pushover();
		$this->client = Services::curlrequest([
			'base_uri'    => $this->config->baseUrl,
			'http_errors' => false,
		], null, null, false);

		// Create a sample message
		$this->message = new Message($this->config->messageDefaults);
		$this->message->message = 'Hello world.';
		$this->message->title   = 'Simple';
	}

	public function testSendIsSuccessful()
	{
		if (empty($this->config->user) || empty($this->config->token))
		{
			$this->markTestSkipped('Unable to run live tests without credentials');
		}

		$pushover = new Pushover($this->config, $this->client);
		$result   = $pushover->sendMessage($this->message);

		$this->assertEquals(1, $result['status']);
		$this->assertNotEmpty($result['request']);
	}

	public function testSendWithAttachmentIsSuccessful()
	{
		if (empty($this->config->user) || empty($this->config->token))
		{
			$this->markTestSkipped('Unable to run live tests without credentials');
		}

		$this->message->attachment = SUPPORTPATH . 'cat.jpg';

		$pushover = new Pushover($this->config, $this->client);
		$result   = $pushover->sendMessage($this->message);

		$this->assertEquals(1, $result['status']);
		$this->assertNotEmpty($result['request']);
	}

	public function testSendThrowsExceptionOnFailure()
	{
		if (empty($this->config->user) || empty($this->config->token))
		{
			$this->markTestSkipped('Unable to run live tests without credentials');
		}

		$this->config->user = 'TotallyMadeUpToken';
		$pushover = new Pushover($this->config, $this->client);

		$this->expectException(PushoverException::class);
		$this->expectExceptionMessage(lang('Pushover.invalidStatus', [0]));

		$result = $pushover->sendMessage($this->message);
	}

	public function testSendSavesErrorsOnFailure()
	{
		if (empty($this->config->user) || empty($this->config->token))
		{
			$this->markTestSkipped('Unable to run live tests without credentials');
		}

		$this->config->user = 'TotallyMadeUpToken';
		$pushover = new Pushover($this->config, $this->client);

		try
		{
			$pushover->sendMessage($this->message);
		}
		catch (\Throwable $e)
		{			
		}

		$errors = $pushover->getErrors();
		
		$this->assertCount(2, $errors);
		$this->assertEquals(lang('Pushover.invalidStatus', [0]), $errors[1]);
	}
}
