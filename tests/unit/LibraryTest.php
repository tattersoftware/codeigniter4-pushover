<?php

use CodeIgniter\Debug\Timer;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use Tatter\Pushover\Entities\Message;
use Tatter\Pushover\Test\MockPushover;

class LibraryTest extends CIUnitTestCase
{
	use Tests\Support\MockPushoverTrait;

	public function setUp(): void
	{
		parent::setUp();

		$this->mockPushover();

		$this->message = $this->pushover->message([
			'message'   => 'Hello world.',
			'title'     => 'Simple',
		]);
	}

	public function testSendInvalidMessageFails()
	{
		$this->message->url = 'bad url';

		$this->assertFalse($this->message->validate());
		$this->assertNull($this->pushover->sendMessage($this->message));
	}

	public function testSendMessageThrottles()
	{
		$timer = new Timer();
		$timer->start('throttleTest');

		$this->pushover->sendMessage($this->message);
		$this->pushover->sendMessage($this->message);

		$this->assertCloseEnough($this->config->throttle, $timer->getElapsedTime('throttleTest'));
	}
}
