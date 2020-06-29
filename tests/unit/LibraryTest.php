<?php

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
	}

	public function testSendInvalidMessageFails()
	{
		$data = [
			'message'   => 'Hello world.',
			'title'     => 'Simple',
			'url'       => 'bad url',
		];
		$data = array_merge($this->config->messageDefaults, $data);
		
		$message = new Message($data);

		$this->assertFalse($message->validate());
		$this->assertNull($this->pushover->sendMessage($message));
	}
}
