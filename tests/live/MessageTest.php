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

		$this->config   = new \Tatter\Pushover\Config\Pushover();
		$this->pushover = service('pushover', $this->config);

		// Create a sample message
		$this->message = $this->pushover->message([
			'message'   => 'Hello world.',
			'title'     => 'Simple',
		]);
	}

	public function testSendIsSuccessful()
	{
		if (empty($this->config->user) || empty($this->config->token))
		{
			$this->markTestSkipped('Unable to run live tests without credentials');
		}

		$result = $this->pushover->sendMessage($this->message);

		$this->assertEquals(1, $result['status']);
		$this->assertNotEmpty($result['request']);
	}

	public function testSendThrowsExceptionOnFailure()
	{
		if (empty($this->config->user) || empty($this->config->token))
		{
			$this->markTestSkipped('Unable to run live tests without credentials');
		}

		$config = new \Tatter\Pushover\Config\Pushover();
		$config->user = 'TotallyMadeUpToken';

		$pushover = new Pushover($config, Services::curlrequest(['base_uri' => $config->baseUrl]));

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

		$config = new \Tatter\Pushover\Config\Pushover();
		$config->user = 'TotallyMadeUpToken';

		$pushover = new Pushover($config, Services::curlrequest(['base_uri' => $config->baseUrl]));

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
