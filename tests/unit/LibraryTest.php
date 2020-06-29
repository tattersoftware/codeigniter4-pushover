<?php

use CodeIgniter\Debug\Timer;
use CodeIgniter\Test\CIUnitTestCase;
use Config\Services;
use Tatter\Pushover\Exceptions\PushoverException;
use Tatter\Pushover\Entities\Message;
use Tatter\Pushover\Test\MockPushover;

class LibraryTest extends CIUnitTestCase
{
	use Tests\Support\MockPushoverTrait;

	public function setUp(): void
	{
		parent::setUp();

		$this->mockPushover();

		// Create a sample message
		$this->message = $this->pushover->message([
			'message'   => 'Hello world.',
			'title'     => 'Simple',
		]);
	}

	public function testMessageReturnsMessage()
	{
		$result = $this->pushover->message();

		$this->assertInstanceOf(Message::class, $result);
	}

	public function testMessageUsesParameters()
	{
		$message = $this->pushover->message(['title' => 'zoinks']);

		$this->assertEquals('zoinks', $message->title);
	}

	public function testSendInvalidMessageFails()
	{
		$this->message->url = 'bad url';
		$this->assertFalse($this->message->validate());

		$this->expectException(PushoverException::class);
		$this->expectExceptionMessage(lang('Pushover.invalidMessage'));

		$this->pushover->sendMessage($this->message);
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
